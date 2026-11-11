<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DeploymentMigrationController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\InitialSuperAdminController;
use App\Http\Controllers\Admin\Auth\PasswordChangeController;
use App\Http\Controllers\Admin\ContactDetailManagementController;
use App\Http\Controllers\Admin\DepartmentInformationManagementController;
use App\Http\Controllers\Admin\HomepageContentManagementController;
use App\Http\Controllers\Admin\HomepageSectionManagementController;
use App\Http\Controllers\Admin\AuditLogManagementController;
use App\Http\Controllers\Admin\SiteSettingsManagementController;
use App\Http\Controllers\Admin\SupportManagementController;
use App\Http\Controllers\Admin\AnnouncementManagementController;
use App\Http\Controllers\Admin\AboutSectionManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DocumentCategoryManagementController;
use App\Http\Controllers\Admin\DocumentManagementController;
use App\Http\Controllers\Admin\DisplayOrderController;
use App\Http\Controllers\Admin\FaqCategoryManagementController;
use App\Http\Controllers\Admin\FaqManagementController;
use App\Http\Controllers\Admin\GalleryAlbumManagementController;
use App\Http\Controllers\Admin\GalleryCategoryManagementController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\NoticeManagementController;
use App\Http\Controllers\Admin\ReportCategoryManagementController;
use App\Http\Controllers\Admin\ReportManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\TeamCategoryManagementController;
use App\Http\Controllers\Admin\TeamMemberManagementController;
use App\Http\Controllers\Admin\TrainingCategoryManagementController;
use App\Http\Controllers\Admin\TrainingResourceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Middleware\EnforceActiveUser;
use App\Http\Controllers\Site\AnnouncementController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\DirectoryController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\NoticeController;
use App\Http\Controllers\Site\PublicMediaController;
use App\Http\Controllers\Site\ResourceController;
use App\Http\Controllers\Site\SearchController;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\RedirectController;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/Public/Default.aspx', [RedirectController::class, '__invoke'])
    ->defaults('destination', '/')
    ->defaults('status', 301);
Route::get('/Public/Training/Default.aspx', [RedirectController::class, '__invoke'])
    ->defaults('destination', '/training')
    ->defaults('status', 301);
Route::get('/search', SearchController::class)->name('search');
Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
Route::get('/notices/{notice:slug}', [NoticeController::class, 'show'])->name('notices.show');
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [AnnouncementController::class, 'show'])->name('announcements.show');
Route::get('/documents', [ResourceController::class, 'documents'])->name('documents.index');
Route::get('/documents/{document:slug}', [ResourceController::class, 'document'])->name('documents.show');
Route::get('/training', [ResourceController::class, 'training'])->name('training.index');
Route::get('/training/{trainingResource:slug}', [ResourceController::class, 'resource'])->name('training.show');
Route::get('/faqs', [ResourceController::class, 'faqs'])->name('faqs.index');
Route::get('/reports', [ResourceController::class, 'reports'])->name('reports.index');
Route::get('/reports/{report:slug}', [ResourceController::class, 'report'])->name('reports.show');
Route::get('/team', [DirectoryController::class, 'index'])->name('team.index');
Route::get('/team/{teamMember:slug}', [DirectoryController::class, 'show'])->name('team.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{galleryAlbum:slug}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::get('/media/{mediaAsset}', PublicMediaController::class)
    ->whereNumber('mediaAsset')
    ->name('media.public');

Route::post('/language/{locale}', [LanguageController::class, 'update'])
    ->whereIn('locale', ['en', 'hi'])
    ->name('language.update');

Route::prefix('deployment/migrate')->name('deployment.migrate.')->group(function (): void {
    Route::get('/', [DeploymentMigrationController::class, 'create'])->name('create');
    Route::post('/', [DeploymentMigrationController::class, 'store'])
        ->middleware('throttle:5,60')
        ->name('store');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/initialize', [InitialSuperAdminController::class, 'create'])->name('initialize');
        Route::post('/initialize', [InitialSuperAdminController::class, 'store'])
            ->middleware('throttle:5,60')
            ->name('initialize.store');
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');
    });

    Route::middleware(['auth', EnforceActiveUser::class, 'password.change.required'])->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
        Route::post('/reorder/{scope}', [DisplayOrderController::class, 'update'])
            ->where('scope', 'banners|about-sections|kpis|quick-links|updates|notices|announcements|documents|document-categories|training|training-categories|faqs|faq-categories|reports|report-categories|team|team-categories|gallery-albums|gallery-categories')
            ->name('reorder');
        Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change');
        Route::put('/password/change', [PasswordChangeController::class, 'update'])
            ->middleware('throttle:5,15')
            ->name('password.change.update');
        Route::prefix('banners')->name('banners.')->group(function (): void {
            Route::get('/', [HomepageContentManagementController::class, 'index'])->middleware('can:banners.view')->name('index');
            Route::get('/create', [HomepageContentManagementController::class, 'create'])->middleware('can:banners.create')->name('create');
            Route::post('/', [HomepageContentManagementController::class, 'store'])->middleware('can:banners.create')->name('store');
            Route::get('/{item}/edit', [HomepageContentManagementController::class, 'edit'])->middleware('can:banners.edit')->name('edit');
            Route::put('/{item}', [HomepageContentManagementController::class, 'update'])->middleware('can:banners.edit')->name('update');
            Route::delete('/{item}', [HomepageContentManagementController::class, 'destroy'])->middleware('can:banners.delete')->name('destroy');
        });
        Route::prefix('kpis')->name('kpis.')->group(function (): void {
            Route::get('/', [HomepageContentManagementController::class, 'index'])->middleware('can:kpis.view')->name('index');
            Route::get('/create', [HomepageContentManagementController::class, 'create'])->middleware('can:kpis.create')->name('create');
            Route::post('/', [HomepageContentManagementController::class, 'store'])->middleware('can:kpis.create')->name('store');
            Route::get('/{item}/edit', [HomepageContentManagementController::class, 'edit'])->middleware('can:kpis.edit')->name('edit');
            Route::put('/{item}', [HomepageContentManagementController::class, 'update'])->middleware('can:kpis.edit')->name('update');
            Route::delete('/{item}', [HomepageContentManagementController::class, 'destroy'])->middleware('can:kpis.delete')->name('destroy');
        });
        Route::prefix('quick-links')->name('quick-links.')->group(function (): void {
            Route::get('/', [HomepageContentManagementController::class, 'index'])->middleware('can:homepage.view')->name('index');
            Route::get('/create', [HomepageContentManagementController::class, 'create'])->middleware('can:homepage.create')->name('create');
            Route::post('/', [HomepageContentManagementController::class, 'store'])->middleware('can:homepage.create')->name('store');
            Route::get('/{item}/edit', [HomepageContentManagementController::class, 'edit'])->middleware('can:homepage.edit')->name('edit');
            Route::put('/{item}', [HomepageContentManagementController::class, 'update'])->middleware('can:homepage.edit')->name('update');
            Route::delete('/{item}', [HomepageContentManagementController::class, 'destroy'])->middleware('can:homepage.delete')->name('destroy');
        });
        Route::prefix('updates')->name('updates.')->group(function (): void {
            Route::get('/', [HomepageContentManagementController::class, 'index'])->middleware('can:homepage.view')->name('index');
            Route::get('/create', [HomepageContentManagementController::class, 'create'])->middleware('can:homepage.create')->name('create');
            Route::post('/', [HomepageContentManagementController::class, 'store'])->middleware('can:homepage.create')->name('store');
            Route::get('/{item}/edit', [HomepageContentManagementController::class, 'edit'])->middleware('can:homepage.edit')->name('edit');
            Route::put('/{item}', [HomepageContentManagementController::class, 'update'])->middleware('can:homepage.edit')->name('update');
            Route::delete('/{item}', [HomepageContentManagementController::class, 'destroy'])->middleware('can:homepage.delete')->name('destroy');
        });
        Route::get('/homepage', [HomepageSectionManagementController::class, 'index'])->middleware('can:homepage.view')->name('homepage.index');
        Route::put('/homepage', [HomepageSectionManagementController::class, 'update'])->middleware('can:homepage.edit')->name('homepage.update');
        Route::get('/department', [DepartmentInformationManagementController::class, 'edit'])->middleware('can:homepage.view')->name('department.edit');
        Route::put('/department', [DepartmentInformationManagementController::class, 'update'])->middleware('can:homepage.edit')->name('department.update');
        Route::prefix('about-sections')->name('about-sections.')->group(function (): void {
            Route::get('/', [AboutSectionManagementController::class, 'index'])->middleware('can:about.view')->name('index');
            Route::get('/create', [AboutSectionManagementController::class, 'create'])->middleware('can:about.create')->name('create');
            Route::post('/', [AboutSectionManagementController::class, 'store'])->middleware('can:about.create')->name('store');
            Route::get('/{section}/edit', [AboutSectionManagementController::class, 'edit'])->middleware('can:about.edit')->name('edit');
            Route::put('/{section}', [AboutSectionManagementController::class, 'update'])->middleware('can:about.edit')->name('update');
            Route::delete('/{section}', [AboutSectionManagementController::class, 'destroy'])->middleware('can:about.delete')->name('destroy');
        });
        Route::prefix('notices')->name('notices.')->group(function (): void {
            Route::get('/', [NoticeManagementController::class, 'index'])->middleware('can:notices.view')->name('index');
            Route::get('/create', [NoticeManagementController::class, 'create'])->middleware('can:notices.create')->name('create');
            Route::post('/', [NoticeManagementController::class, 'store'])->middleware('can:notices.create')->name('store');
            Route::get('/{notice}/edit', [NoticeManagementController::class, 'edit'])->middleware('can:notices.edit')->name('edit');
            Route::put('/{notice}', [NoticeManagementController::class, 'update'])->middleware('can:notices.edit')->name('update');
            Route::delete('/{notice}', [NoticeManagementController::class, 'destroy'])->middleware('can:notices.delete')->name('destroy');
        });
        Route::prefix('announcements')->name('announcements.')->group(function (): void {
            Route::get('/', [AnnouncementManagementController::class, 'index'])->middleware('can:announcements.view')->name('index');
            Route::get('/create', [AnnouncementManagementController::class, 'create'])->middleware('can:announcements.create')->name('create');
            Route::post('/', [AnnouncementManagementController::class, 'store'])->middleware('can:announcements.create')->name('store');
            Route::get('/{announcement}/edit', [AnnouncementManagementController::class, 'edit'])->middleware('can:announcements.edit')->name('edit');
            Route::put('/{announcement}', [AnnouncementManagementController::class, 'update'])->middleware('can:announcements.edit')->name('update');
            Route::delete('/{announcement}', [AnnouncementManagementController::class, 'destroy'])->middleware('can:announcements.delete')->name('destroy');
        });
        Route::prefix('documents')->name('documents.')->group(function (): void {
            Route::get('/', [DocumentManagementController::class, 'index'])->middleware('can:documents.view')->name('index');
            Route::get('/create', [DocumentManagementController::class, 'create'])->middleware(['can:documents.create', 'can:documents.upload'])->name('create');
            Route::post('/', [DocumentManagementController::class, 'store'])->middleware(['can:documents.create', 'can:documents.upload'])->name('store');
            Route::get('/{document}/edit', [DocumentManagementController::class, 'edit'])->middleware('can:documents.edit')->name('edit');
            Route::put('/{document}', [DocumentManagementController::class, 'update'])->middleware('can:documents.edit')->name('update');
            Route::delete('/{document}', [DocumentManagementController::class, 'destroy'])->middleware('can:documents.delete')->name('destroy');
        });
        Route::prefix('document-categories')->name('document-categories.')->group(function (): void {
            Route::get('/', [DocumentCategoryManagementController::class, 'index'])->middleware('can:documents.view')->name('index');
            Route::get('/create', [DocumentCategoryManagementController::class, 'create'])->middleware('can:documents.create')->name('create');
            Route::post('/', [DocumentCategoryManagementController::class, 'store'])->middleware('can:documents.create')->name('store');
            Route::get('/{documentCategory}/edit', [DocumentCategoryManagementController::class, 'edit'])->middleware('can:documents.edit')->name('edit');
            Route::put('/{documentCategory}', [DocumentCategoryManagementController::class, 'update'])->middleware('can:documents.edit')->name('update');
            Route::delete('/{documentCategory}', [DocumentCategoryManagementController::class, 'destroy'])->middleware('can:documents.delete')->name('destroy');
        });
        Route::prefix('training')->name('training.')->group(function (): void {
            Route::get('/', [TrainingResourceManagementController::class, 'index'])->middleware('can:training.view')->name('index');
            Route::get('/create', [TrainingResourceManagementController::class, 'create'])->middleware('can:training.create')->name('create');
            Route::post('/', [TrainingResourceManagementController::class, 'store'])->middleware('can:training.create')->name('store');
            Route::get('/{trainingResource}/edit', [TrainingResourceManagementController::class, 'edit'])->middleware('can:training.edit')->name('edit');
            Route::put('/{trainingResource}', [TrainingResourceManagementController::class, 'update'])->middleware('can:training.edit')->name('update');
            Route::delete('/{trainingResource}', [TrainingResourceManagementController::class, 'destroy'])->middleware('can:training.delete')->name('destroy');
        });
        Route::prefix('training-categories')->name('training-categories.')->group(function (): void {
            Route::get('/', [TrainingCategoryManagementController::class, 'index'])->middleware('can:training.view')->name('index');
            Route::get('/create', [TrainingCategoryManagementController::class, 'create'])->middleware('can:training.create')->name('create');
            Route::post('/', [TrainingCategoryManagementController::class, 'store'])->middleware('can:training.create')->name('store');
            Route::get('/{trainingCategory}/edit', [TrainingCategoryManagementController::class, 'edit'])->middleware('can:training.edit')->name('edit');
            Route::put('/{trainingCategory}', [TrainingCategoryManagementController::class, 'update'])->middleware('can:training.edit')->name('update');
            Route::delete('/{trainingCategory}', [TrainingCategoryManagementController::class, 'destroy'])->middleware('can:training.delete')->name('destroy');
        });
        Route::prefix('faqs')->name('faqs.')->group(function (): void {
            Route::get('/', [FaqManagementController::class, 'index'])->middleware('can:faqs.view')->name('index');
            Route::get('/create', [FaqManagementController::class, 'create'])->middleware('can:faqs.create')->name('create');
            Route::post('/', [FaqManagementController::class, 'store'])->middleware('can:faqs.create')->name('store');
            Route::get('/{faq}/edit', [FaqManagementController::class, 'edit'])->middleware('can:faqs.edit')->name('edit');
            Route::put('/{faq}', [FaqManagementController::class, 'update'])->middleware('can:faqs.edit')->name('update');
            Route::delete('/{faq}', [FaqManagementController::class, 'destroy'])->middleware('can:faqs.delete')->name('destroy');
        });
        Route::prefix('faq-categories')->name('faq-categories.')->group(function (): void {
            Route::get('/', [FaqCategoryManagementController::class, 'index'])->middleware('can:faqs.view')->name('index');
            Route::get('/create', [FaqCategoryManagementController::class, 'create'])->middleware('can:faqs.create')->name('create');
            Route::post('/', [FaqCategoryManagementController::class, 'store'])->middleware('can:faqs.create')->name('store');
            Route::get('/{faqCategory}/edit', [FaqCategoryManagementController::class, 'edit'])->middleware('can:faqs.edit')->name('edit');
            Route::put('/{faqCategory}', [FaqCategoryManagementController::class, 'update'])->middleware('can:faqs.edit')->name('update');
            Route::delete('/{faqCategory}', [FaqCategoryManagementController::class, 'destroy'])->middleware('can:faqs.delete')->name('destroy');
        });
        Route::prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', [ReportManagementController::class, 'index'])->middleware('can:reports.view')->name('index');
            Route::get('/create', [ReportManagementController::class, 'create'])->middleware(['can:reports.create', 'can:reports.upload'])->name('create');
            Route::post('/', [ReportManagementController::class, 'store'])->middleware(['can:reports.create', 'can:reports.upload'])->name('store');
            Route::get('/{report}/edit', [ReportManagementController::class, 'edit'])->middleware('can:reports.edit')->name('edit');
            Route::put('/{report}', [ReportManagementController::class, 'update'])->middleware('can:reports.edit')->name('update');
            Route::delete('/{report}', [ReportManagementController::class, 'destroy'])->middleware('can:reports.delete')->name('destroy');
        });
        Route::prefix('report-categories')->name('report-categories.')->group(function (): void {
            Route::get('/', [ReportCategoryManagementController::class, 'index'])->middleware('can:reports.view')->name('index');
            Route::get('/create', [ReportCategoryManagementController::class, 'create'])->middleware('can:reports.create')->name('create');
            Route::post('/', [ReportCategoryManagementController::class, 'store'])->middleware('can:reports.create')->name('store');
            Route::get('/{reportCategory}/edit', [ReportCategoryManagementController::class, 'edit'])->middleware('can:reports.edit')->name('edit');
            Route::put('/{reportCategory}', [ReportCategoryManagementController::class, 'update'])->middleware('can:reports.edit')->name('update');
            Route::delete('/{reportCategory}', [ReportCategoryManagementController::class, 'destroy'])->middleware('can:reports.delete')->name('destroy');
        });
        Route::prefix('team')->name('team.')->group(function (): void {
            Route::get('/', [TeamMemberManagementController::class, 'index'])->middleware('can:team.view')->name('index');
            Route::get('/create', [TeamMemberManagementController::class, 'create'])->middleware('can:team.create')->name('create');
            Route::post('/', [TeamMemberManagementController::class, 'store'])->middleware('can:team.create')->name('store');
            Route::get('/{teamMember}/edit', [TeamMemberManagementController::class, 'edit'])->middleware('can:team.edit')->name('edit');
            Route::put('/{teamMember}', [TeamMemberManagementController::class, 'update'])->middleware('can:team.edit')->name('update');
            Route::delete('/{teamMember}', [TeamMemberManagementController::class, 'destroy'])->middleware('can:team.delete')->name('destroy');
        });
        Route::prefix('team-categories')->name('team-categories.')->group(function (): void {
            Route::get('/', [TeamCategoryManagementController::class, 'index'])->middleware('can:team.view')->name('index');
            Route::get('/create', [TeamCategoryManagementController::class, 'create'])->middleware('can:team.create')->name('create');
            Route::post('/', [TeamCategoryManagementController::class, 'store'])->middleware('can:team.create')->name('store');
            Route::get('/{teamCategory}/edit', [TeamCategoryManagementController::class, 'edit'])->middleware('can:team.edit')->name('edit');
            Route::put('/{teamCategory}', [TeamCategoryManagementController::class, 'update'])->middleware('can:team.edit')->name('update');
            Route::delete('/{teamCategory}', [TeamCategoryManagementController::class, 'destroy'])->middleware('can:team.delete')->name('destroy');
        });
        Route::prefix('gallery')->name('gallery.')->group(function (): void {
            Route::get('/', [GalleryAlbumManagementController::class, 'index'])->middleware('can:gallery.view')->name('index');
            Route::get('/create', [GalleryAlbumManagementController::class, 'create'])->middleware('can:gallery.create')->name('create');
            Route::post('/', [GalleryAlbumManagementController::class, 'store'])->middleware('can:gallery.create')->name('store');
            Route::get('/{galleryAlbum}/edit', [GalleryAlbumManagementController::class, 'edit'])->middleware('can:gallery.edit')->name('edit');
            Route::put('/{galleryAlbum}', [GalleryAlbumManagementController::class, 'update'])->middleware('can:gallery.edit')->name('update');
            Route::delete('/{galleryAlbum}', [GalleryAlbumManagementController::class, 'destroy'])->middleware('can:gallery.delete')->name('destroy');
        });
        Route::prefix('gallery-categories')->name('gallery-categories.')->group(function (): void {
            Route::get('/', [GalleryCategoryManagementController::class, 'index'])->middleware('can:gallery.view')->name('index');
            Route::get('/create', [GalleryCategoryManagementController::class, 'create'])->middleware('can:gallery.create')->name('create');
            Route::post('/', [GalleryCategoryManagementController::class, 'store'])->middleware('can:gallery.create')->name('store');
            Route::get('/{galleryCategory}/edit', [GalleryCategoryManagementController::class, 'edit'])->middleware('can:gallery.edit')->name('edit');
            Route::put('/{galleryCategory}', [GalleryCategoryManagementController::class, 'update'])->middleware('can:gallery.edit')->name('update');
            Route::delete('/{galleryCategory}', [GalleryCategoryManagementController::class, 'destroy'])->middleware('can:gallery.delete')->name('destroy');
        });
        Route::prefix('users')->name('users.')->group(function (): void {
            Route::get('/', [UserManagementController::class, 'index'])->middleware('can:users.view')->name('index');
            Route::get('/create', [UserManagementController::class, 'create'])->middleware('can:users.create')->name('create');
            Route::post('/', [UserManagementController::class, 'store'])->middleware('can:users.create')->name('store');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->middleware('can:users.edit')->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->middleware('can:users.edit')->name('update');
            Route::put('/{user}/status', [UserManagementController::class, 'updateStatus'])->middleware('can:users.disable')->name('status');
        });
        Route::prefix('roles')->name('roles.')->group(function (): void {
            Route::get('/', [RoleManagementController::class, 'index'])->middleware('can:roles.view')->name('index');
            Route::get('/create', [RoleManagementController::class, 'create'])->middleware('can:roles.create')->name('create');
            Route::post('/', [RoleManagementController::class, 'store'])->middleware('can:roles.create')->name('store');
            Route::get('/{role}/edit', [RoleManagementController::class, 'edit'])->middleware('can:roles.edit')->name('edit');
            Route::put('/{role}', [RoleManagementController::class, 'update'])->middleware('can:roles.edit')->name('update');
            Route::delete('/{role}', [RoleManagementController::class, 'destroy'])->middleware('can:roles.delete')->name('destroy');
        });
        Route::prefix('media')->name('media.')->middleware('can:media.view')->group(function (): void {
            Route::get('/', [MediaLibraryController::class, 'index'])->name('index');
            Route::post('/', [MediaLibraryController::class, 'store'])->middleware('can:media.upload')->name('store');
            Route::get('/picker', [MediaLibraryController::class, 'picker'])->name('picker');
            Route::get('/{mediaAsset}/download', [MediaLibraryController::class, 'downloadPrivate'])->whereNumber('mediaAsset')->name('download-private');
            Route::put('/{mediaAsset}/visibility', [MediaLibraryController::class, 'updateVisibility'])->whereNumber('mediaAsset')->middleware('can:media.publish')->name('visibility');
            Route::delete('/{mediaAsset}', [MediaLibraryController::class, 'destroy'])->whereNumber('mediaAsset')->middleware('can:media.delete')->name('destroy');
        });
        Route::prefix('support')->name('support.')->group(function (): void {
            Route::get('/', [SupportManagementController::class, 'index'])->middleware('can:support.view')->name('index');
            Route::get('/{supportRequest}', [SupportManagementController::class, 'show'])->middleware('can:support.view')->name('show');
            Route::put('/{supportRequest}', [SupportManagementController::class, 'update'])->middleware('can:support.edit')->name('update');
            Route::put('/{supportRequest}/assignment', [SupportManagementController::class, 'assign'])->middleware('can:support.assign')->name('assign');
            Route::post('/{supportRequest}/notes', [SupportManagementController::class, 'storeNote'])->middleware('can:support.edit')->name('notes.store');
            Route::put('/{supportRequest}/archive', [SupportManagementController::class, 'archive'])->middleware('can:support.edit')->name('archive');
        });
        Route::get('/contact', [ContactDetailManagementController::class, 'edit'])->middleware('can:contact.view')->name('contact.index');
        Route::put('/contact', [ContactDetailManagementController::class, 'update'])->middleware('can:contact.edit')->name('contact.update');
        Route::get('/settings', [SiteSettingsManagementController::class, 'edit'])->middleware('can:settings.view')->name('settings.index');
        Route::put('/settings', [SiteSettingsManagementController::class, 'update'])->middleware('can:settings.edit')->name('settings.update');
        Route::prefix('audit-logs')->name('audit-logs.')->group(function (): void {
            Route::get('/', [AuditLogManagementController::class, 'index'])
                ->middleware('can:audit-logs.view')
                ->name('index');
        });
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
