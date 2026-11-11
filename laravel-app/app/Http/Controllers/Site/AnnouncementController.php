<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $announcements = Announcement::query()
            ->publiclyVisible()
            ->with('featuredImage')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($searchQuery) use ($term): void {
                    $searchQuery->where('title_en', 'like', '%'.$term.'%')
                        ->orWhere('title_hi', 'like', '%'.$term.'%')
                        ->orWhere('description_en', 'like', '%'.$term.'%')
                        ->orWhere('description_hi', 'like', '%'.$term.'%');
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('site.announcements.index', compact('announcements', 'filters'));
    }

    public function show(Announcement $announcement): View
    {
        abort_unless(Announcement::query()->publiclyVisible()->whereKey($announcement->getKey())->exists(), 404);
        $announcement->load(['featuredImage', 'attachment']);

        return view('site.announcements.show', compact('announcement'));
    }
}
