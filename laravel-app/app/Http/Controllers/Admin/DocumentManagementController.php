<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', Document::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:document_categories,id'],
        ]);

        $documents = Document::query()->with(['category', 'file', 'lastEditor'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('title_en', 'like', '%'.$term.'%')
                ->orWhere('title_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();

        $categories = DocumentCategory::query()->orderBy('display_order')->orderBy('name_en')->get();
        $bulkCategories = DocumentCategory::query()->where('is_active', true)->orderBy('display_order')->orderBy('name_en')->get();

        return view('admin.documents.index', compact('documents', 'categories', 'bulkCategories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.documents.form', [
            'document' => new Document(['status' => 'draft', 'version' => '1.0']),
            'categories' => DocumentCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(DocumentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('documents.upload'), 403);
        $attributes = $request->validated();
        unset($attributes['version_change_notes']);
        $this->ensurePublishPermission($request, $attributes['status']);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['title_en']));
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);

        $document = DB::transaction(function () use ($request, $attributes): Document {
            $document = Document::query()->create($attributes);
            $document->forceFill([
                'uploaded_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ])->save();

            return $document;
        });

        return to_route('admin.documents.edit', $document)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Document $document): View
    {
        $document->load(['category', 'file', 'versions.file', 'versions.uploader']);
        $categories = DocumentCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $document->category_id))
            ->orderBy('display_order')->get();

        return view('admin.documents.form', compact('document', 'categories') + ['isEditing' => true]);
    }

    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $attributes = $request->validated();
        $changeNotes = $attributes['version_change_notes'] ?? null;
        unset($attributes['version_change_notes']);

        $mediaChanged = (int) $attributes['media_asset_id'] !== (int) $document->media_asset_id;
        if ($mediaChanged) {
            abort_unless($request->user()->can('documents.upload'), 403);
        }

        $this->ensurePublishPermission($request, $attributes['status'], $document->status, $attributes, $document);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $document->slug, $document->id);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);

        DB::transaction(function () use ($request, $document, $attributes, $changeNotes, $mediaChanged): void {
            $versionChanged = $attributes['version'] !== $document->version;
            if ($versionChanged || $mediaChanged) {
                DocumentVersion::query()->firstOrCreate(
                    ['document_id' => $document->id, 'version' => $document->version],
                    [
                        'media_asset_id' => $document->media_asset_id,
                        'change_notes' => $changeNotes,
                        'uploaded_by' => $document->uploaded_by,
                        'created_at' => $document->updated_at ?? now(),
                    ],
                );
            }

            $document->update($attributes);
            $document->forceFill([
                'updated_by' => $request->user()->id,
                ...($mediaChanged ? ['uploaded_by' => $request->user()->id] : []),
            ])->save();
        });

        return to_route('admin.documents.edit', $document)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $document->forceFill(['updated_by' => $request->user()->id])->save();
        $document->delete();

        return to_route('admin.documents.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'document';
        $candidate = $base;
        $suffix = 2;

        while (Document::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?Document $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('documents.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
