<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\ContactDetail;
use App\Models\DepartmentInformation;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryAlbum;
use App\Models\GalleryCategory;
use App\Models\GalleryImage;
use App\Models\HomepageSection;
use App\Models\HomepageUpdate;
use App\Models\Kpi;
use App\Models\MediaAsset;
use App\Models\Notice;
use App\Models\QuickLink;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\SiteSetting;
use App\Models\SupportRequest;
use App\Models\TeamCategory;
use App\Models\TeamMember;
use App\Models\TrainingCategory;
use App\Models\TrainingResource;
use App\Models\User;
use App\Observers\AuditedModelObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ([
            Announcement::class,
            Banner::class,
            ContactDetail::class,
            DepartmentInformation::class,
            Document::class,
            DocumentCategory::class,
            DocumentVersion::class,
            Faq::class,
            FaqCategory::class,
            GalleryAlbum::class,
            GalleryCategory::class,
            GalleryImage::class,
            HomepageSection::class,
            HomepageUpdate::class,
            Kpi::class,
            MediaAsset::class,
            Notice::class,
            QuickLink::class,
            Report::class,
            ReportCategory::class,
            Role::class,
            SiteSetting::class,
            SupportRequest::class,
            TeamCategory::class,
            TeamMember::class,
            TrainingCategory::class,
            TrainingResource::class,
            User::class,
        ] as $model) {
            $model::observe(AuditedModelObserver::class);
        }
    }
}
