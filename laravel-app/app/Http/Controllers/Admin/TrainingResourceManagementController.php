<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrainingResourceRequest;
use App\Models\TrainingCategory;
use App\Models\TrainingResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TrainingResourceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', TrainingResource::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:training_categories,id'],
            'type' => ['nullable', 'in:'.implode(',', TrainingResource::TYPES)],
        ]);

        $resources = TrainingResource::query()->with(['category', 'file', 'lastEditor'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('title_en', 'like', '%'.$term.'%')
                ->orWhere('title_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('resource_type', $type))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();

        $categories = TrainingCategory::query()->orderBy('display_order')->orderBy('name_en')->get();

        return view('admin.training.index', compact('resources', 'categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.training.form', [
            'resource' => new TrainingResource(['status' => 'draft', 'resource_type' => 'manual']),
            'categories' => TrainingCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(TrainingResourceRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        if (! empty($attributes['media_asset_id']) || ! empty($attributes['thumbnail_media_id'])) {
            abort_unless($request->user()->can('training.upload'), 403);
        }

        $this->ensurePublishPermission($request, $attributes['status']);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['title_en']));
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);

        $resource = new TrainingResource($attributes);
        $resource->forceFill([
            ...((! empty($attributes['media_asset_id']) || ! empty($attributes['thumbnail_media_id'])) ? ['uploaded_by' => $request->user()->id] : []),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ])->save();

        return to_route('admin.training.edit', $resource)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(TrainingResource $trainingResource): View
    {
        $trainingResource->load(['category', 'file', 'thumbnail']);
        $categories = TrainingCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $trainingResource->category_id))
            ->orderBy('display_order')->get();

        return view('admin.training.form', [
            'resource' => $trainingResource,
            'categories' => $categories,
            'isEditing' => true,
        ]);
    }

    public function update(TrainingResourceRequest $request, TrainingResource $trainingResource): RedirectResponse
    {
        $attributes = $request->validated();
        $mediaChanged = (string) ($attributes['media_asset_id'] ?? '') !== (string) ($trainingResource->media_asset_id ?? '');
        $thumbnailChanged = (string) ($attributes['thumbnail_media_id'] ?? '') !== (string) ($trainingResource->thumbnail_media_id ?? '');
        if ($mediaChanged || $thumbnailChanged) {
            abort_unless($request->user()->can('training.upload'), 403);
        }

        $this->ensurePublishPermission($request, $attributes['status'], $trainingResource->status, $attributes, $trainingResource);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $trainingResource->slug, $trainingResource->id);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $trainingResource->update($attributes);
        $trainingResource->forceFill([
            'updated_by' => $request->user()->id,
            ...(($mediaChanged || $thumbnailChanged) ? ['uploaded_by' => $request->user()->id] : []),
        ])->save();

        return to_route('admin.training.edit', $trainingResource)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, TrainingResource $trainingResource): RedirectResponse
    {
        $trainingResource->forceFill(['updated_by' => $request->user()->id])->save();
        $trainingResource->delete();

        return to_route('admin.training.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'training-resource';
        $candidate = $base;
        $suffix = 2;

        while (TrainingResource::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?TrainingResource $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('training.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
