<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $sections = AboutSection::query()
            ->publiclyVisible()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->map(fn (AboutSection $section): array => [
                'key' => $section->id,
                'title' => $section->localized('title'),
                'content' => $section->localized('content'),
            ])
            ->filter(fn (array $section): bool => filled($section['title']) && filled($section['content']))
            ->values();

        return view('site.about', [
            'sections' => $sections,
            'pageTitle' => __('ui.navigation.about'),
            'metaDescription' => __('ui.site.about_meta_description'),
        ]);
    }
}
