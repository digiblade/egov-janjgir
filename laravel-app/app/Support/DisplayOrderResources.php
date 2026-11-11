<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\AboutSection;
use App\Models\Banner;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryAlbum;
use App\Models\GalleryCategory;
use App\Models\HomepageUpdate;
use App\Models\Kpi;
use App\Models\Notice;
use App\Models\QuickLink;
use App\Models\Report;
use App\Models\TeamMember;
use App\Models\TeamCategory;
use App\Models\TrainingResource;
use App\Models\TrainingCategory;
use App\Models\ReportCategory;

final class DisplayOrderResources
{
    /** @var array<string, array{model: class-string, permission: string, module: string}> */
    private const RESOURCES = [
        'banners' => ['model' => Banner::class, 'permission' => 'banners.edit', 'module' => 'banners'],
        'about-sections' => ['model' => AboutSection::class, 'permission' => 'about.edit', 'module' => 'about'],
        'kpis' => ['model' => Kpi::class, 'permission' => 'kpis.edit', 'module' => 'kpis'],
        'quick-links' => ['model' => QuickLink::class, 'permission' => 'homepage.edit', 'module' => 'homepage'],
        'updates' => ['model' => HomepageUpdate::class, 'permission' => 'homepage.edit', 'module' => 'homepage'],
        'notices' => ['model' => Notice::class, 'permission' => 'notices.edit', 'module' => 'notices'],
        'announcements' => ['model' => Announcement::class, 'permission' => 'announcements.edit', 'module' => 'announcements'],
        'documents' => ['model' => Document::class, 'permission' => 'documents.edit', 'module' => 'documents'],
        'document-categories' => ['model' => DocumentCategory::class, 'permission' => 'documents.edit', 'module' => 'documents'],
        'training' => ['model' => TrainingResource::class, 'permission' => 'training.edit', 'module' => 'training'],
        'training-categories' => ['model' => TrainingCategory::class, 'permission' => 'training.edit', 'module' => 'training'],
        'faqs' => ['model' => Faq::class, 'permission' => 'faqs.edit', 'module' => 'faqs'],
        'faq-categories' => ['model' => FaqCategory::class, 'permission' => 'faqs.edit', 'module' => 'faqs'],
        'reports' => ['model' => Report::class, 'permission' => 'reports.edit', 'module' => 'reports'],
        'report-categories' => ['model' => ReportCategory::class, 'permission' => 'reports.edit', 'module' => 'reports'],
        'team' => ['model' => TeamMember::class, 'permission' => 'team.edit', 'module' => 'team'],
        'team-categories' => ['model' => TeamCategory::class, 'permission' => 'team.edit', 'module' => 'team'],
        'gallery-albums' => ['model' => GalleryAlbum::class, 'permission' => 'gallery.edit', 'module' => 'gallery'],
        'gallery-categories' => ['model' => GalleryCategory::class, 'permission' => 'gallery.edit', 'module' => 'gallery'],
    ];

    /** @return array{model: class-string, permission: string, module: string} */
    public static function get(string $scope): array
    {
        abort_unless(isset(self::RESOURCES[$scope]), 404);

        return self::RESOURCES[$scope];
    }

    /** @return list<string> */
    public static function scopes(): array
    {
        return array_keys(self::RESOURCES);
    }
}
