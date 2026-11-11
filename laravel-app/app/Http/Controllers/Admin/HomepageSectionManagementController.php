<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSectionRequest;
use App\Models\HomepageSection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class HomepageSectionManagementController extends Controller
{
    /** @var array<string, string> */
    private const SECTIONS = [
        'banners' => 'banners', 'department' => 'department', 'notices' => 'notices',
        'announcements' => 'announcements', 'updates' => 'updates', 'quick_links' => 'quick_links', 'statistics' => 'statistics',
    ];

    private const UPDATE_SOURCES = [
        'notices' => 'notices',
        'documents' => 'documents',
        'reports' => 'reports',
        'training' => 'training',
        'announcements' => 'announcements',
    ];

    public function index(): View
    {
        $existing = HomepageSection::query()->get()->keyBy('section_key');
        $sections = collect(self::SECTIONS)->map(fn (string $label, string $key): HomepageSection => $existing->get($key) ?? new HomepageSection([
            'section_key' => $key, 'is_enabled' => true, 'display_order' => array_search($key, array_keys(self::SECTIONS), true) * 10,
        ]))->sortBy('display_order')->values();

        return view('admin.homepage-content.sections', [
            'sections' => $sections,
            'updateSources' => self::UPDATE_SOURCES,
        ]);
    }

    public function update(HomepageSectionRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->validated('sections') as $section) {
                $record = HomepageSection::query()->firstOrNew(['section_key' => $section['key']]);
                $attributes = [
                    'is_enabled' => (bool) $section['is_enabled'],
                    'display_order' => (int) $section['display_order'],
                    'label_en' => trim($section['label_en'] ?? ''),
                    'label_hi' => trim($section['label_hi'] ?? ''),
                    'title_en' => trim($section['title_en'] ?? ''),
                    'title_hi' => trim($section['title_hi'] ?? ''),
                    'description_en' => trim($section['description_en'] ?? ''),
                    'description_hi' => trim($section['description_hi'] ?? ''),
                ];
                if (($section['key'] ?? null) === 'updates' && array_key_exists('settings', $section)) {
                    $attributes['settings'] = ['sources' => array_values($section['settings']['sources'] ?? [])];
                }
                $record->fill($attributes);
                $record->forceFill(['updated_by' => $request->user()->id])->save();
            }
        });

        return to_route('admin.homepage.index')->with('status', __('ui.admin_content.saved'));
    }
}
