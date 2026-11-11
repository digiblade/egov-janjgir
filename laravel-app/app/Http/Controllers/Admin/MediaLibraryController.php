<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Http\Requests\Admin\MediaVisibilityRequest;
use App\Models\MediaAsset;
use App\Models\Document;
use App\Support\MediaImageOptimizer;
use App\Support\MediaAssetReferenceChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MediaLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:160'],
            'visibility' => ['nullable', 'in:private,public'],
            'type' => ['nullable', 'in:image,document,video'],
        ]);
        $user = $request->user();

        $assets = MediaAsset::query()
            ->with('uploader')
            ->where('storage_disk', config('portal.media.disk', 'local'))
            ->when(! $user->can('media.view_private'), fn ($query) => $query->where(function ($privateQuery) use ($user): void {
                $privateQuery->where('visibility', 'public')->orWhere('uploaded_by', $user->id);
            }))
            ->when($filters['visibility'] ?? null, fn ($query, $visibility) => $query->where('visibility', $visibility))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where('original_filename', 'like', '%'.$term.'%'))
            ->when($filters['type'] ?? null, function ($query, string $type): void {
                match ($type) {
                    'image' => $query->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']),
                    'video' => $query->where('mime_type', 'like', 'video/%'),
                    default => $query->whereNotIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->where('mime_type', 'not like', 'video/%'),
                };
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.media.index', compact('assets', 'filters'));
    }

    public function store(MediaUploadRequest $request, MediaImageOptimizer $imageOptimizer): RedirectResponse
    {
        $uploads = $request->file('files') ?: [$request->file('file')];
        $diskName = (string) config('portal.media.disk', 'local');
        abort_unless($diskName === 'local', 500, 'Media uploads must use the private local disk.');
        $saved = [];

        try {
            foreach ($uploads as $upload) {
                $saved[] = $this->storeUploadedFile($request, $upload, $imageOptimizer, $diskName);
            }

            if ($request->boolean('create_documents')) {
                DB::transaction(function () use ($request, $saved): void {
                    $displayOrder = ((int) Document::query()->max('display_order')) + 10;

                    foreach ($saved as $asset) {
                        $title = trim((string) pathinfo($asset->original_filename, PATHINFO_FILENAME));
                        $title = mb_substr($title !== '' ? $title : __('ui.admin_content.bulk_document_fallback_title'), 0, 255);
                        $baseSlug = rtrim(mb_substr(Str::slug($title) ?: 'bulk-document', 0, 160), '-') ?: 'bulk-document';
                        $slug = $baseSlug;
                        $suffix = 2;

                        while (Document::withTrashed()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug.'-'.$suffix++;
                        }

                        $document = Document::query()->create([
                            'slug' => $slug,
                            'category_id' => (int) $request->validated('document_category_id'),
                            'media_asset_id' => $asset->id,
                            'title_en' => $title,
                            'title_hi' => $title,
                            'version' => '1.0',
                            'display_order' => $displayOrder,
                            'status' => 'draft',
                        ]);
                        $document->forceFill([
                            'uploaded_by' => $request->user()->id,
                            'created_by' => $request->user()->id,
                            'updated_by' => $request->user()->id,
                        ])->save();
                        $displayOrder += 10;
                    }
                });

                return to_route('admin.documents.index')->with('status', __('ui.admin_content.bulk_documents_created', ['count' => count($saved)]));
            }
        } catch (Throwable $exception) {
            foreach ($saved as $asset) {
                Storage::disk($diskName)->delete($asset->storage_key);
                $asset->delete();
            }

            throw $exception;
        }

        if (count($saved) > 1) {
            return to_route('admin.media.index')->with('status', __('ui.media.uploaded_multiple_private', ['count' => count($saved)]));
        }

        return to_route('admin.media.index')->with('status', __('ui.media.uploaded_private'));
    }

    private function storeUploadedFile(Request $request, \Illuminate\Http\UploadedFile $upload, MediaImageOptimizer $imageOptimizer, string $diskName): MediaAsset
    {
        $extension = strtolower($upload->getClientOriginalExtension());
        $filename = Str::random(48).'.'.$extension;

        $optimizedPath = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            ? $imageOptimizer->optimize($upload)
            : null;
        $sourceFile = is_string($optimizedPath) ? new File($optimizedPath) : $upload;
        try {
            $storedKey = Storage::disk($diskName)->putFileAs('media', $sourceFile, $filename);
        } finally {
            if (is_string($optimizedPath) && is_file($optimizedPath)) {
                @unlink($optimizedPath);
            }
        }

        abort_unless(is_string($storedKey) && $storedKey !== '', 500, __('ui.media.upload_failed'));

        try {
            $disk = Storage::disk($diskName);
            $storagePath = $disk->path($storedKey);
            $checksum = hash_file('sha256', $storagePath);
            abort_unless(is_string($checksum), 500, __('ui.media.upload_failed'));
            $asset = new MediaAsset([
                'original_filename' => $this->safeOriginalFilename($upload->getClientOriginalName()),
                'storage_disk' => $diskName,
                'storage_key' => $storedKey,
                'mime_type' => strtolower((string) $upload->getMimeType()),
                'size_bytes' => (int) $disk->size($storedKey),
                'checksum_sha256' => $checksum,
                'visibility' => 'private',
            ]);
            $asset->forceFill(['uploaded_by' => $request->user()->id])->save();
        } catch (Throwable $exception) {
            Storage::disk($diskName)->delete($storedKey);
            throw $exception;
        }

        return $asset;
    }

    public function picker(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:160'],
            'type' => ['nullable', 'in:image,document,video'],
        ]);

        $assets = MediaAsset::query()
            ->where('visibility', 'public')
            ->where('storage_disk', config('portal.media.disk', 'local'))
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'video/mp4'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where('original_filename', 'like', '%'.$term.'%'))
            ->when($filters['type'] ?? null, function ($query, string $type): void {
                match ($type) {
                    'image' => $query->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']),
                    'video' => $query->where('mime_type', 'like', 'video/%'),
                    default => $query->whereNotIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->where('mime_type', 'not like', 'video/%'),
                };
            })
            ->latest('id')
            ->limit(100)
            ->get(['id', 'original_filename', 'mime_type', 'size_bytes']);

        return response()->json(['data' => $assets]);
    }

    public function downloadPrivate(Request $request, MediaAsset $mediaAsset): StreamedResponse
    {
        $this->authorizePrivateAsset($request, $mediaAsset);
        abort_unless($mediaAsset->storage_disk === config('portal.media.disk', 'local'), 404);
        abort_unless($this->isSafeStorageKey($mediaAsset->storage_key), 404);
        abort_unless(Storage::disk('local')->exists($mediaAsset->storage_key), 404);

        return Storage::disk('local')->download($mediaAsset->storage_key, $this->safeOriginalFilename($mediaAsset->original_filename), [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function updateVisibility(MediaVisibilityRequest $request, MediaAsset $mediaAsset, MediaAssetReferenceChecker $references): RedirectResponse
    {
        $this->authorizePrivateAsset($request, $mediaAsset);
        abort_unless($request->user()->can('media.publish'), 403);
        $visibility = $request->validated('visibility');
        abort_if(
            $mediaAsset->visibility === 'public' && $visibility === 'private' && $references->isReferenced($mediaAsset),
            409,
            __('ui.media.file_in_use'),
        );

        $mediaAsset->forceFill(['visibility' => $visibility])->save();

        return to_route('admin.media.index')->with('status', __('ui.media.visibility_updated'));
    }

    public function destroy(Request $request, MediaAsset $mediaAsset, MediaAssetReferenceChecker $references): RedirectResponse
    {
        abort_unless($request->user()->can('media.delete'), 403);
        abort_unless($mediaAsset->storage_disk === config('portal.media.disk', 'local'), 404);

        $assetId = (int) $mediaAsset->getKey();
        $storageKey = $mediaAsset->storage_key;
        abort_unless($this->isSafeStorageKey($storageKey), 404);
        DB::transaction(function () use ($request, $references, $assetId): void {
            $lockedAsset = MediaAsset::query()->lockForUpdate()->findOrFail($assetId);
            $this->authorizePrivateAsset($request, $lockedAsset);
            abort_if($references->isReferenced($lockedAsset), 409, __('ui.media.file_in_use'));
            $lockedAsset->delete();
        });

        $disk = Storage::disk('local');
        if ($disk->exists($storageKey) && ! $disk->delete($storageKey)) {
            Log::warning('Unlinked private media file remains for cleanup.', [
                'media_asset_id' => $assetId,
            ]);

            return back()->withErrors(['media' => __('ui.media.delete_failed')]);
        }

        return to_route('admin.media.index')->with('status', __('ui.media.deleted'));
    }

    private function authorizePrivateAsset(Request $request, MediaAsset $asset): void
    {
        if ($asset->visibility === 'private') {
            abort_unless(
                $request->user()->can('media.view_private') || (int) $asset->uploaded_by === (int) $request->user()->id,
                404,
            );
        }
    }

    private function safeOriginalFilename(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[\x00-\x1f\x7f]/u', '', $filename) ?? '';
        $filename = trim($filename, " .\t\n\r\0\x0B");

        return mb_substr($filename !== '' ? $filename : 'download', 0, 255);
    }

    private function isSafeStorageKey(string $key): bool
    {
        return str_starts_with($key, 'media/')
            && ! str_contains($key, '..')
            && ! str_contains($key, '\\')
            && preg_match('/[\x00-\x1f\x7f]/', $key) !== 1;
    }
}
