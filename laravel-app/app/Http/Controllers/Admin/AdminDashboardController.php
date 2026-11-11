<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Document;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\HomepageUpdate;
use App\Models\Kpi;
use App\Models\MediaAsset;
use App\Models\Notice;
use App\Models\Report;
use App\Models\SupportRequest;
use App\Models\TeamMember;
use App\Models\TrainingResource;
use App\Models\User;
use App\Support\AdminNavigationBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $cards = collect([
            ['permission' => 'notices.view', 'label' => 'notices', 'model' => Notice::class],
            ['permission' => 'documents.view', 'label' => 'documents', 'model' => Document::class],
            ['permission' => 'training.view', 'label' => 'training', 'model' => TrainingResource::class],
            ['permission' => 'team.view', 'label' => 'team', 'model' => TeamMember::class],
            ['permission' => 'reports.view', 'label' => 'reports', 'model' => Report::class],
            ['permission' => 'gallery.view', 'label' => 'gallery_images', 'model' => GalleryImage::class],
            ['permission' => 'banners.view', 'label' => 'banners', 'model' => Banner::class],
            ['permission' => 'kpis.view', 'label' => 'kpis', 'model' => Kpi::class],
        ])->filter(fn (array $card): bool => $user->can($card['permission']))
            ->map(fn (array $card): array => [
                'label' => __('ui.admin.dashboard_counts.'.$card['label']),
                'count' => $card['model']::query()->count(),
            ])->values();

        $pendingSupport = $user->can('support.view')
            ? SupportRequest::query()
                ->whereIn('status', ['new', 'in_progress'])
                ->whereNull('archived_at')
                ->when(! $user->can('support.assign'), fn ($query) => $query->where('assigned_to', $user->id))
                ->count()
            : null;
        if ($pendingSupport !== null) {
            $cards->push([
                'label' => __('ui.admin.dashboard_counts.pending_support'),
                'count' => $pendingSupport,
            ]);
        }

        $statusSummary = $this->statusSummary($user);
        $pendingPublication = $this->pendingPublication($user);
        $activity = $user->can('audit-logs.view')
            ? AuditLog::query()->with('actor:id,name')->latest('occurred_at')->limit(8)->get()
            : collect();
        $recentUploads = $user->can('media.view')
            ? MediaAsset::query()
                ->with('uploader:id,name')
                ->when(! $user->can('media.view_private'), fn ($query) => $query->where(fn ($visibility) => $visibility
                    ->where('visibility', 'public')
                    ->orWhere('uploaded_by', $user->id)))
                ->latest('created_at')->limit(8)->get()
            : collect();
        $quickActions = collect(app(AdminNavigationBuilder::class)->for($user))
            ->reject(fn (array $item): bool => $item['key'] === 'dashboard')
            ->take(8);

        return view('admin.dashboard', compact(
            'cards', 'pendingSupport', 'statusSummary', 'pendingPublication', 'activity', 'recentUploads', 'quickActions',
        ));
    }

    /** @return list<array{module: string, statuses: array<string, int>}> */
    private function statusSummary(User $user): array
    {
        $modules = [
            ['permission' => 'banners.view', 'label' => 'banners', 'model' => Banner::class, 'active_field' => 'is_active'],
            ['permission' => 'kpis.view', 'label' => 'kpis', 'model' => Kpi::class, 'active_field' => 'is_active'],
            ['permission' => 'notices.view', 'label' => 'notices', 'model' => Notice::class],
            ['permission' => 'announcements.view', 'label' => 'announcements', 'model' => Announcement::class],
            ['permission' => 'documents.view', 'label' => 'documents', 'model' => Document::class],
            ['permission' => 'training.view', 'label' => 'training', 'model' => TrainingResource::class],
            ['permission' => 'faqs.view', 'label' => 'faqs', 'model' => Faq::class],
            ['permission' => 'reports.view', 'label' => 'reports', 'model' => Report::class],
            ['permission' => 'homepage.view', 'label' => 'updates', 'model' => HomepageUpdate::class],
        ];

        return collect($modules)->filter(fn (array $module): bool => $user->can($module['permission']))
            ->map(fn (array $module): array => [
                'module' => __('ui.admin.dashboard_counts.'.$module['label']),
                'statuses' => isset($module['active_field'])
                    ? $module['model']::query()
                        ->selectRaw($module['active_field'].' as active, COUNT(*) AS total')
                        ->groupBy($module['active_field'])
                        ->pluck('total', 'active')
                        ->mapWithKeys(fn ($count, $active): array => [((bool) $active ? 'published' : 'draft') => (int) $count])
                        ->all()
                    : $module['model']::query()
                        ->selectRaw('status, COUNT(*) AS total')
                        ->groupBy('status')
                        ->pluck('total', 'status')
                        ->map(fn ($count): int => (int) $count)
                        ->all(),
            ])->values()->all();
    }

    /** @return list<array{module: string, title: string, status: string, updated_at: mixed}> */
    private function pendingPublication(User $user): array
    {
        $modules = [
            ['permission' => 'banners.view', 'label' => 'banners', 'model' => Banner::class, 'active_field' => 'is_active'],
            ['permission' => 'kpis.view', 'label' => 'kpis', 'model' => Kpi::class, 'active_field' => 'is_active'],
            ['permission' => 'notices.view', 'label' => 'notices', 'model' => Notice::class],
            ['permission' => 'announcements.view', 'label' => 'announcements', 'model' => Announcement::class],
            ['permission' => 'documents.view', 'label' => 'documents', 'model' => Document::class],
            ['permission' => 'training.view', 'label' => 'training', 'model' => TrainingResource::class],
            ['permission' => 'reports.view', 'label' => 'reports', 'model' => Report::class],
            ['permission' => 'homepage.view', 'label' => 'updates', 'model' => HomepageUpdate::class],
        ];

        return collect($modules)->filter(fn (array $module): bool => $user->can($module['permission']))
            ->flatMap(function (array $module): array {
                /** @var Model $model */
                $model = new $module['model'];
                $items = $model->newQuery()
                    ->where(function ($query) use ($module): void {
                        if (isset($module['active_field'])) {
                            $query->where($module['active_field'], false);

                            return;
                        }

                        $query->where('status', 'draft')
                            ->orWhere(function ($scheduled): void {
                                $scheduled->where('status', 'scheduled')->where('published_at', '>', now());
                            });
                    })
                    ->orderBy('updated_at')
                    ->limit(3)
                    ->get();

                return $items->map(fn (Model $item): array => [
                    'module' => __('ui.admin.dashboard_counts.'.$module['label']),
                    'title' => method_exists($item, 'localized') ? $item->localized('title') : class_basename($item),
                    'status' => isset($module['active_field']) ? 'draft' : (string) $item->status,
                    'updated_at' => $item->updated_at,
                ])->all();
            })
            ->sortBy('updated_at')
            ->take(10)
            ->values()
            ->all();
    }
}
