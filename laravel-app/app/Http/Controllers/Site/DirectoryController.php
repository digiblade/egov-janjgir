<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\TeamCategory;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'string', 'max:100'], 'office' => ['nullable', 'string', 'max:120']]);
        $members = TeamMember::query()->visibleInDirectory()->with(['category', 'photograph'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($s) => $s->where('full_name_en', 'like', "%{$term}%")->orWhere('full_name_hi', 'like', "%{$term}%")->orWhere('designation_en', 'like', "%{$term}%")->orWhere('designation_hi', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['office'] ?? null, fn ($q, $office) => $q->where('office_block', $office))
            ->orderBy('display_order')->orderBy('full_name_en')->paginate(12)->withQueryString();
        $categories = TeamCategory::query()->active()->orderBy('display_order')->get();
        $offices = TeamMember::query()->visibleInDirectory()->whereNotNull('office_block')->distinct()->orderBy('office_block')->pluck('office_block');

        return view('site.team.index', compact('members', 'categories', 'offices', 'filters'));
    }

    public function show(TeamMember $teamMember): View
    {
        abort_unless(TeamMember::query()->visibleInDirectory()->whereKey($teamMember->getKey())->exists(), 404);
        $teamMember->load(['category', 'photograph']);

        return view('site.team.show', compact('teamMember'));
    }
}
