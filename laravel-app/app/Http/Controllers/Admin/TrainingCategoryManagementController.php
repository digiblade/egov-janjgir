<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrainingCategoryRequest;
use App\Models\TrainingCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrainingCategoryManagementController extends Controller
{
    public function index(): View
    {
        $categories = TrainingCategory::query()->with('lastEditor')->withCount('resources')->orderBy('display_order')->orderBy('id')->paginate(20);

        return view('admin.training-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.training-categories.form', ['category' => new TrainingCategory(['is_active' => true]), 'isEditing' => false]);
    }

    public function store(TrainingCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = TrainingCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.training-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(TrainingCategory $trainingCategory): View
    {
        return view('admin.training-categories.form', ['category' => $trainingCategory, 'isEditing' => true]);
    }

    public function update(TrainingCategoryRequest $request, TrainingCategory $trainingCategory): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $trainingCategory->slug, $trainingCategory->id);
        $trainingCategory->update($attributes);
        $trainingCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.training-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, TrainingCategory $trainingCategory): RedirectResponse
    {
        if ($trainingCategory->resources()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $trainingCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $trainingCategory->delete();

        return to_route('admin.training-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'training-category';
        $candidate = $base;
        $suffix = 2;
        while (TrainingCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
