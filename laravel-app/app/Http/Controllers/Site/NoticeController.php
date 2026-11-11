<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:40'],
        ]);

        $notices = Notice::query()
            ->publiclyVisible()
            ->with('attachment')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($searchQuery) use ($term): void {
                    $searchQuery->where('title_en', 'like', '%'.$term.'%')
                        ->orWhere('title_hi', 'like', '%'.$term.'%')
                        ->orWhere('description_en', 'like', '%'.$term.'%')
                        ->orWhere('description_hi', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('notice_type', $type))
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $types = Notice::query()
            ->publiclyVisible()
            ->whereNotNull('notice_type')
            ->distinct()
            ->orderBy('notice_type')
            ->pluck('notice_type');

        return view('site.notices.index', compact('notices', 'types', 'filters'));
    }

    public function show(Notice $notice): View
    {
        abort_unless(Notice::query()->publiclyVisible()->whereKey($notice->getKey())->exists(), 404);
        $notice->load('attachment');

        return view('site.notices.show', compact('notice'));
    }
}
