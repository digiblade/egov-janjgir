<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportCategoryRequest;
use App\Models\ReportCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportCategoryManagementController extends Controller
{
    public function index(): View
    {
        $categories = ReportCategory::query()->with('lastEditor')->withCount('reports')->orderBy('display_order')->orderBy('id')->paginate(20);

        return view('admin.report-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.report-categories.form', ['category' => new ReportCategory(['is_active' => true]), 'isEditing' => false]);
    }

    public function store(ReportCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = ReportCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.report-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(ReportCategory $reportCategory): View
    {
        return view('admin.report-categories.form', ['category' => $reportCategory, 'isEditing' => true]);
    }

    public function update(ReportCategoryRequest $request, ReportCategory $reportCategory): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $reportCategory->slug, $reportCategory->id);
        $reportCategory->update($attributes);
        $reportCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.report-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, ReportCategory $reportCategory): RedirectResponse
    {
        if ($reportCategory->reports()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $reportCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $reportCategory->delete();

        return to_route('admin.report-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'report-category';
        $candidate = $base;
        $suffix = 2;
        while (ReportCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
