<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\DepartmentInformation;
use App\Models\HomepageSection;
use App\Models\Kpi;
use App\Models\Notice;
use App\Models\QuickLink;
use App\Support\HomepageUpdatesFeed;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(HomepageUpdatesFeed $updatesFeed): View
    {
        $sections = HomepageSection::query()
            ->orderBy('display_order')
            ->get()
            ->keyBy('section_key');
        $sectionOrder = $sections->mapWithKeys(fn (HomepageSection $section): array => [$section->section_key => $section->display_order]);
        $bannerSectionEnabled = $sections->get('banners')?->is_enabled ?? true;
        $updatesSection = $sections->get('updates');
        $updateSources = is_array($updatesSection?->settings)
            ? ($updatesSection->settings['sources'] ?? ['notices', 'documents', 'reports', 'training', 'announcements'])
            : ['notices', 'documents', 'reports', 'training', 'announcements'];

        return view('site.home', [
            'department' => DepartmentInformation::query()->with('image')->first(),
            'sectionOrder' => $sectionOrder,
            'banners' => $bannerSectionEnabled ? Banner::query()
                ->currentlyActive()
                ->whereHas('desktopImage', fn ($query) => $query->where('visibility', 'public'))
                ->with(['desktopImage', 'mobileImage'])
                ->orderBy('display_order')
                ->orderByDesc('id')
                ->get() : collect(),
            'notices' => Notice::query()
                ->publiclyVisible()
                ->with('attachment')
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(),
            'announcements' => Announcement::query()
                ->publiclyVisible()
                ->with('featuredImage')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(4)
                ->get(),
            'updates' => $updatesFeed->latest($updateSources),
            'quickLinks' => QuickLink::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get(),
            'kpis' => Kpi::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get(),
            'sections' => $sections,
        ]);
    }
}
