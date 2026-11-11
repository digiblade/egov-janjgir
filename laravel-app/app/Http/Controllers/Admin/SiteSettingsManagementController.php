<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\SiteSetting;
use App\Support\PublicSiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SiteSettingsManagementController extends Controller
{
    /** @var list<string> */
    private const SETTING_KEYS = ['site.identity', 'site.seo', 'site.footer', 'site.carousel'];

    public function edit(): View
    {
        $settings = SiteSetting::query()
            ->whereIn('setting_key', self::SETTING_KEYS)
            ->where('is_sensitive', false)
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting) => [$setting->setting_key => $setting->value ?? []])
            ->all();

        $values = array_replace_recursive([
            'site.identity' => ['title_en' => config('app.name'), 'title_hi' => '', 'department_en' => '', 'department_hi' => '', 'logo_media_id' => null, 'favicon_media_id' => null],
            'site.seo' => ['title_en' => '', 'title_hi' => '', 'description_en' => '', 'description_hi' => ''],
            'site.footer' => ['note_en' => '', 'note_hi' => '', 'copyright_en' => '', 'copyright_hi' => '', 'facebook' => '', 'instagram' => '', 'youtube' => '', 'x' => ''],
            'site.carousel' => ['default_language' => 'en', 'autoplay' => true, 'interval_ms' => 7000],
        ], $settings);

        return view('admin.settings.edit', compact('values'));
    }

    public function update(SiteSettingsRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $values = [
            'site.identity' => [
                'title_en' => trim($input['site_title_en']),
                'title_hi' => trim($input['site_title_hi']),
                'department_en' => trim($input['department_name_en'] ?? ''),
                'department_hi' => trim($input['department_name_hi'] ?? ''),
                'logo_media_id' => $input['logo_media_id'] ?? null,
                'favicon_media_id' => $input['favicon_media_id'] ?? null,
            ],
            'site.seo' => [
                'title_en' => trim($input['seo_title_en'] ?? ''),
                'title_hi' => trim($input['seo_title_hi'] ?? ''),
                'description_en' => trim($input['seo_description_en'] ?? ''),
                'description_hi' => trim($input['seo_description_hi'] ?? ''),
            ],
            'site.footer' => [
                'note_en' => trim($input['footer_note_en'] ?? ''),
                'note_hi' => trim($input['footer_note_hi'] ?? ''),
                'copyright_en' => trim($input['copyright_en'] ?? ''),
                'copyright_hi' => trim($input['copyright_hi'] ?? ''),
                'facebook' => trim($input['social_facebook'] ?? ''),
                'instagram' => trim($input['social_instagram'] ?? ''),
                'youtube' => trim($input['social_youtube'] ?? ''),
                'x' => trim($input['social_x'] ?? ''),
            ],
            'site.carousel' => [
                'default_language' => $input['default_language'],
                'autoplay' => (bool) ($input['carousel_autoplay'] ?? false),
                'interval_ms' => (int) $input['carousel_interval_ms'],
            ],
        ];

        DB::transaction(function () use ($values, $request): void {
            foreach ($values as $key => $value) {
                SiteSetting::query()->updateOrCreate(
                    ['setting_key' => $key],
                    ['value' => $value, 'is_sensitive' => false, 'updated_by' => $request->user()->id],
                );
            }
        });
        app(PublicSiteSettings::class)->forget();

        return to_route('admin.settings.index')->with('status', __('ui.admin_content.saved'));
    }
}
