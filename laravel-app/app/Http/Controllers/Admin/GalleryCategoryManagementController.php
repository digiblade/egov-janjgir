<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryCategoryRequest;
use App\Models\GalleryCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GalleryCategoryManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $categories = GalleryCategory::query()->with('lastEditor')->withCount('albums')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('name_en', 'like', '%'.$term.'%')
                ->orWhere('name_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->orderBy('display_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.gallery-categories.index', compact('categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.gallery-categories.form', ['category' => new GalleryCategory(['is_active' => true]), 'isEditing' => false]);
    }

    public function store(GalleryCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        if (! empty($attributes['cover_media_id'])) {
            abort_unless($request->user()->can('gallery.upload'), 403);
        }
        if ($attributes['is_active']) {
            abort_unless($request->user()->can('gallery.publish'), 403);
        }
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = GalleryCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.gallery-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(GalleryCategory $galleryCategory): View
    {
        $galleryCategory->load('coverImage');

        return view('admin.gallery-categories.form', ['category' => $galleryCategory, 'isEditing' => true]);
    }

    public function update(GalleryCategoryRequest $request, GalleryCategory $galleryCategory): RedirectResponse
    {
        $attributes = $request->validated();
        if (array_key_exists('cover_media_id', $attributes)
            && empty($attributes['cover_media_id'])
            && $galleryCategory->coverImage?->visibility !== 'public') {
            unset($attributes['cover_media_id']);
        }
        $coverChanged = array_key_exists('cover_media_id', $attributes)
            && (string) ($attributes['cover_media_id'] ?? '') !== (string) ($galleryCategory->cover_media_id ?? '');
        if ($coverChanged) {
            abort_unless($request->user()->can('gallery.upload'), 403);
        }
        if ((bool) $attributes['is_active'] !== (bool) $galleryCategory->is_active) {
            abort_unless($request->user()->can('gallery.publish'), 403);
        }
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $galleryCategory->slug, $galleryCategory->id);
        $galleryCategory->update($attributes);
        $galleryCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.gallery-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, GalleryCategory $galleryCategory): RedirectResponse
    {
        if ($galleryCategory->albums()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $galleryCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $galleryCategory->delete();

        return to_route('admin.gallery-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'gallery-category';
        $candidate = $base;
        $suffix = 2;
        while (GalleryCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
