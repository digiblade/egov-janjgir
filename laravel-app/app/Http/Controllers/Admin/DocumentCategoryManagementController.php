<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentCategoryRequest;
use App\Models\DocumentCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryManagementController extends Controller
{
    public function index(): View
    {
        $categories = DocumentCategory::query()->with(['parent', 'lastEditor'])->withCount('documents')->orderBy('display_order')->orderBy('id')->paginate(20);

        return view('admin.document-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.document-categories.form', [
            'category' => new DocumentCategory(['is_active' => true]),
            'parents' => DocumentCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(DocumentCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = DocumentCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.document-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(DocumentCategory $documentCategory): View
    {
        $category = $documentCategory;
        $parents = DocumentCategory::query()->where('is_active', true)->where('id', '<>', $category->id)->orderBy('display_order')->get();

        return view('admin.document-categories.form', compact('category', 'parents') + ['isEditing' => true]);
    }

    public function update(DocumentCategoryRequest $request, DocumentCategory $documentCategory): RedirectResponse
    {
        $attributes = $request->validated();
        if (! empty($attributes['parent_id']) && $this->wouldCreateCycle((int) $attributes['parent_id'], $documentCategory->id)) {
            return back()->withErrors(['parent_id' => __('ui.admin_content.category_cycle')])->withInput();
        }
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $documentCategory->slug, $documentCategory->id);
        $documentCategory->update($attributes);
        $documentCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.document-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, DocumentCategory $documentCategory): RedirectResponse
    {
        if ($documentCategory->documents()->withTrashed()->exists() || $documentCategory->children()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $documentCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $documentCategory->delete();

        return to_route('admin.document-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function wouldCreateCycle(int $parentId, int $categoryId): bool
    {
        $ancestor = DocumentCategory::withTrashed()->find($parentId);
        while ($ancestor) {
            if ($ancestor->id === $categoryId) {
                return true;
            }
            $ancestor = $ancestor->parent()->withTrashed()->first();
        }

        return false;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'document-category';
        $candidate = $base;
        $suffix = 2;
        while (DocumentCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
