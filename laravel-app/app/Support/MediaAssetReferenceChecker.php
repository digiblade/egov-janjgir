<?php

namespace App\Support;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MediaAssetReferenceChecker
{
    /** @var array<string, list<string>> */
    private const REFERENCES = [
        'department_information' => ['image_media_id'],
        'banners' => ['desktop_image_media_id', 'mobile_image_media_id'],
        'notices' => ['attachment_media_id'],
        'announcements' => ['featured_image_media_id', 'attachment_media_id'],
        'documents' => ['media_asset_id'],
        'document_versions' => ['media_asset_id'],
        'training_resources' => ['media_asset_id', 'thumbnail_media_id'],
        'reports' => ['media_asset_id'],
        'team_members' => ['photograph_media_id'],
        'gallery_categories' => ['cover_media_id'],
        'gallery_albums' => ['cover_media_id'],
        'gallery_images' => ['media_asset_id'],
    ];

    public function isReferenced(MediaAsset $asset): bool
    {
        foreach (self::REFERENCES as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column) && DB::table($table)->where($column, $asset->getKey())->exists()) {
                    return true;
                }
            }
        }

        return $this->isUsedByPublicSiteSettings($asset);
    }

    private function isUsedByPublicSiteSettings(MediaAsset $asset): bool
    {
        if (! Schema::hasTable('site_settings')) {
            return false;
        }

        $values = DB::table('site_settings')
            ->where('setting_key', 'site.identity')
            ->pluck('value');

        foreach ($values as $value) {
            $settings = is_array($value) ? $value : json_decode((string) $value, true);

            if (! is_array($settings)) {
                continue;
            }

            foreach (['logo_media_id', 'favicon_media_id'] as $key) {
                if (isset($settings[$key]) && (int) $settings[$key] === (int) $asset->getKey()) {
                    return true;
                }
            }
        }

        return false;
    }
}
