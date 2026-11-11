<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryAlbumRequest;
use App\Models\GalleryAlbum;
use App\Models\GalleryCategory;
use App\Models\GalleryImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GalleryAlbumManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', GalleryAlbum::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:gallery_categories,id'],
        ]);
        $albums = GalleryAlbum::query()->with(['category', 'coverImage', 'lastEditor'])->withCount('images')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('title_en', 'like', '%'.$term.'%')
                ->orWhere('title_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('gallery_category_id', $categoryId))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();
        $categories = GalleryCategory::query()->orderBy('display_order')->orderBy('name_en')->get();

        return view('admin.gallery.index', compact('albums', 'categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.gallery.form', [
            'album' => new GalleryAlbum(['status' => 'draft', 'is_active' => auth()->user()->can('gallery.publish')]),
            'categories' => GalleryCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
            'images' => collect(),
        ]);
    }

    public function store(GalleryAlbumRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $imageRows = $attributes['images'] ?? [];
        $managingImages = array_key_exists('images_present', $attributes);
        unset($attributes['images'], $attributes['images_present']);

        if (! $managingImages && $imageRows !== []) {
            abort(403);
        }
        if (! empty($attributes['cover_media_id']) || $this->hasAssignedImage($imageRows)) {
            abort_unless($request->user()->can('gallery.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status']);
        if ($attributes['is_active']) {
            abort_unless($request->user()->can('gallery.publish'), 403);
        }
        $this->assertHasPublicImage($imageRows, $managingImages, null, $attributes['status']);

        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['title_en']));
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);

        $album = DB::transaction(function () use ($request, $attributes, $imageRows, $managingImages): GalleryAlbum {
            $album = GalleryAlbum::query()->create($attributes);
            $album->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

            if ($managingImages) {
                $this->syncImages($album, $imageRows, $request->user()->id);
            }

            return $album;
        });

        return to_route('admin.gallery.edit', $album)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(GalleryAlbum $galleryAlbum): View
    {
        $galleryAlbum->load(['category', 'coverImage', 'images.media', 'images.lastEditor']);
        $categories = GalleryCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $galleryAlbum->gallery_category_id))
            ->orderBy('display_order')->get();

        return view('admin.gallery.form', [
            'album' => $galleryAlbum,
            'categories' => $categories,
            'isEditing' => true,
            'images' => $galleryAlbum->images,
        ]);
    }

    public function update(GalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $attributes = $request->validated();
        $imageRows = $attributes['images'] ?? [];
        $managingImages = array_key_exists('images_present', $attributes);
        unset($attributes['images'], $attributes['images_present']);

        if (array_key_exists('cover_media_id', $attributes)
            && empty($attributes['cover_media_id'])
            && $galleryAlbum->coverImage?->visibility !== 'public') {
            unset($attributes['cover_media_id']);
        }
        if (! $managingImages && $imageRows !== []) {
            abort(403);
        }
        $coverChanged = array_key_exists('cover_media_id', $attributes)
            && (string) ($attributes['cover_media_id'] ?? '') !== (string) ($galleryAlbum->cover_media_id ?? '');
        if ($coverChanged || $managingImages) {
            abort_unless($request->user()->can('gallery.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status'], $galleryAlbum->status, $attributes, $galleryAlbum);
        if ((bool) $attributes['is_active'] !== (bool) $galleryAlbum->is_active) {
            abort_unless($request->user()->can('gallery.publish'), 403);
        }
        $this->assertHasPublicImage($imageRows, $managingImages, $galleryAlbum, $attributes['status']);

        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $galleryAlbum->slug, $galleryAlbum->id);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);

        DB::transaction(function () use ($request, $galleryAlbum, $attributes, $imageRows, $managingImages): void {
            $galleryAlbum->update($attributes);
            $galleryAlbum->forceFill(['updated_by' => $request->user()->id])->save();

            if ($managingImages) {
                $this->syncImages($galleryAlbum, $imageRows, $request->user()->id);
            }
        });

        return to_route('admin.gallery.edit', $galleryAlbum)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $galleryAlbum->forceFill(['updated_by' => $request->user()->id])->save();
        $galleryAlbum->delete();

        return to_route('admin.gallery.index')->with('status', __('ui.admin_content.deleted'));
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function syncImages(GalleryAlbum $album, array $rows, int $userId): void
    {
        $submittedIds = [];

        foreach ($rows as $row) {
            $rowId = isset($row['id']) ? (int) $row['id'] : null;
            $existing = $rowId ? GalleryImage::query()->where('gallery_album_id', $album->id)->find($rowId) : null;

            if (($row['remove'] ?? false) && $existing) {
                $existing->forceFill(['updated_by' => $userId])->save();
                $existing->delete();
                continue;
            }

            if ($rowId && ! $existing) {
                // The FormRequest scopes IDs to this album; this second check
                // protects against stale concurrent submissions.
                continue;
            }

            $mediaId = filled($row['media_asset_id'] ?? null)
                ? (int) $row['media_asset_id']
                : ($existing?->media_asset_id ?? null);
            if (! $mediaId) {
                continue;
            }

            $attributes = [
                'caption_en' => $row['caption_en'] ?? null,
                'caption_hi' => $row['caption_hi'] ?? null,
                'alt_text_en' => $row['alt_text_en'] ?? null,
                'alt_text_hi' => $row['alt_text_hi'] ?? null,
                'display_order' => isset($row['display_order']) ? (int) $row['display_order'] : ($existing?->display_order ?? 0),
                'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : ($existing?->is_active ?? true),
            ];

            if ($existing) {
                $existing->update(['media_asset_id' => $mediaId, ...$attributes]);
                $existing->forceFill(['updated_by' => $userId])->save();
                $submittedIds[] = $existing->id;
                continue;
            }

            $image = GalleryImage::withTrashed()->firstOrNew([
                'gallery_album_id' => $album->id,
                'media_asset_id' => $mediaId,
            ]);
            if ($image->exists && $image->trashed()) {
                $image->restore();
            }
            $image->fill($attributes);
            $image->forceFill([
                'gallery_album_id' => $album->id,
                'media_asset_id' => $mediaId,
                'created_by' => $image->created_by ?? $userId,
                'updated_by' => $userId,
            ])->save();
            $submittedIds[] = $image->id;
        }

        $album->images()->whereNotIn('id', $submittedIds)->each(function (GalleryImage $image) use ($userId): void {
            $image->forceFill(['updated_by' => $userId])->save();
            $image->delete();
        });
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function hasAssignedImage(array $rows): bool
    {
        foreach ($rows as $row) {
            if (filled($row['media_asset_id'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function assertHasPublicImage(array $rows, bool $managingImages, ?GalleryAlbum $album, string $status): void
    {
        if (! in_array($status, ['published', 'scheduled'], true)) {
            return;
        }

        if (! $managingImages && $album?->visibleImages()->exists()) {
            return;
        }

        foreach ($rows as $row) {
            if (($row['remove'] ?? false) || ! ($row['is_active'] ?? true)) {
                continue;
            }

            if (filled($row['media_asset_id'] ?? null)
                && \App\Models\MediaAsset::query()->whereKey($row['media_asset_id'])->where('visibility', 'public')->exists()) {
                return;
            }

            $id = $row['id'] ?? null;
            if ($id && GalleryImage::query()->where('gallery_album_id', $album?->id)->whereKey($id)->where('is_active', true)->whereHas('media', fn ($query) => $query->where('visibility', 'public'))->exists()) {
                return;
            }
        }

        throw ValidationException::withMessages(['images' => __('ui.admin_content.gallery_album_needs_public_image')]);
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'gallery-album';
        $candidate = $base;
        $suffix = 2;
        while (GalleryAlbum::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?GalleryAlbum $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('gallery.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
