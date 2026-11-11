<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PublicSiteSettings
{
    private const CACHE_KEY = 'public-site-settings-v2';

    /** @var list<string> */
    private const PUBLIC_KEYS = ['site.identity', 'site.seo', 'site.footer', 'site.carousel'];

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return SiteSetting::query()
                ->whereIn('setting_key', self::PUBLIC_KEYS)
                ->where('is_sensitive', false)
                ->get()
                ->mapWithKeys(fn (SiteSetting $setting) => [$setting->setting_key => $setting->value ?? []])
                ->all();
        });
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
