<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqCategoryRequest;
use App\Models\FaqCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqCategoryManagementController extends Controller
{
    public function index(): View
    {
        $categories = FaqCategory::query()->with('lastEditor')->withCount('faqs')->orderBy('display_order')->orderBy('id')->paginate(20);

        return view('admin.faq-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.faq-categories.form', ['category' => new FaqCategory(['is_active' => true]), 'isEditing' => false]);
    }

    public function store(FaqCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = FaqCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.faq-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(FaqCategory $faqCategory): View
    {
        return view('admin.faq-categories.form', ['category' => $faqCategory, 'isEditing' => true]);
    }

    public function update(FaqCategoryRequest $request, FaqCategory $faqCategory): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $faqCategory->slug, $faqCategory->id);
        $faqCategory->update($attributes);
        $faqCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.faq-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, FaqCategory $faqCategory): RedirectResponse
    {
        if ($faqCategory->faqs()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $faqCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $faqCategory->delete();

        return to_route('admin.faq-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'faq-category';
        $candidate = $base;
        $suffix = 2;
        while (FaqCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
