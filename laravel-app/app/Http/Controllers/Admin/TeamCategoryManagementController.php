<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamCategoryRequest;
use App\Models\TeamCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamCategoryManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $categories = TeamCategory::query()->with('lastEditor')->withCount('members')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('name_en', 'like', '%'.$term.'%')
                ->orWhere('name_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->orderBy('display_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.team-categories.index', compact('categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.team-categories.form', ['category' => new TeamCategory(['is_active' => true]), 'isEditing' => false]);
    }

    public function store(TeamCategoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['name_en']));
        $category = TeamCategory::query()->create($attributes);
        $category->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.team-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function edit(TeamCategory $teamCategory): View
    {
        return view('admin.team-categories.form', ['category' => $teamCategory, 'isEditing' => true]);
    }

    public function update(TeamCategoryRequest $request, TeamCategory $teamCategory): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $teamCategory->slug, $teamCategory->id);
        $teamCategory->update($attributes);
        $teamCategory->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.team-categories.index')->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, TeamCategory $teamCategory): RedirectResponse
    {
        if ($teamCategory->members()->withTrashed()->exists()) {
            return back()->withErrors(['category' => __('ui.admin_content.category_in_use')]);
        }

        $teamCategory->forceFill(['updated_by' => $request->user()->id])->save();
        $teamCategory->delete();

        return to_route('admin.team-categories.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'team-category';
        $candidate = $base;
        $suffix = 2;
        while (TeamCategory::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
