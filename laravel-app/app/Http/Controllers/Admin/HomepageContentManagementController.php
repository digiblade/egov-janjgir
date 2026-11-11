<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageContentRequest;
use App\Models\Banner;
use App\Models\HomepageUpdate;
use App\Models\Kpi;
use App\Models\QuickLink;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class HomepageContentManagementController extends Controller
{
    private const MODELS = [
        'banners' => Banner::class,
        'kpis' => Kpi::class,
        'quick-links' => QuickLink::class,
        'updates' => HomepageUpdate::class,
    ];

    public function index(Request $request): View
    {
        $type = $this->type($request);
        $model = $this->model($type);
        $items = $model::query()->orderBy('display_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.homepage-content.index', compact('items', 'type'));
    }

    public function create(Request $request): View
    {
        $type = $this->type($request);
        $item = new ($this->model($type))();
        if ($type === 'banners') {
            $item->cta_url = '/about';
            $item->cta_text_en = 'Learn more';
            $item->cta_text_hi = 'और जानें';
        }

        return view('admin.homepage-content.form', compact('item', 'type') + ['editing' => false]);
    }

    public function store(HomepageContentRequest $request): RedirectResponse
    {
        $type = $this->type($request);
        $attributes = $request->validated();
        if ($type === 'banners') {
            abort_unless($request->user()->can('banners.upload'), 403);
        }
        $this->authorizePublication($request, $type, $attributes['is_active'] ?? false, $attributes['status'] ?? null, attributes: $attributes);
        $model = $this->model($type);
        $item = $model::query()->create($attributes);
        $item->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route("admin.{$type}.edit", $item)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Request $request, int $item): View
    {
        $type = $this->type($request);
        $item = $this->findItem($type, $item);

        return view('admin.homepage-content.form', compact('item', 'type') + ['editing' => true]);
    }

    public function update(HomepageContentRequest $request, int $item): RedirectResponse
    {
        $type = $this->type($request);
        $item = $this->findItem($type, $item);
        $attributes = $request->validated();
        if ($type === 'banners' && ((int) ($attributes['desktop_image_media_id'] ?? 0) !== (int) $item->desktop_image_media_id || (int) ($attributes['mobile_image_media_id'] ?? 0) !== (int) ($item->mobile_image_media_id ?? 0))) {
            abort_unless($request->user()->can('banners.upload'), 403);
        }
        $active = $attributes['is_active'] ?? false;
        $status = $attributes['status'] ?? null;
        $currentStatus = $item instanceof HomepageUpdate ? $item->status : null;
        $this->authorizePublication($request, $type, $active, $status, $currentStatus, $item->is_active ?? false, $attributes, $item);
        $item->update($attributes);
        $item->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route("admin.{$type}.edit", $item)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $type = $this->type($request);
        $item = $this->findItem($type, $item);
        $item->forceFill(['updated_by' => $request->user()->id])->save();
        $item->delete();

        return to_route("admin.{$type}.index")->with('status', __('ui.admin_content.deleted'));
    }

    private function model(string $type): string
    {
        abort_unless(isset(self::MODELS[$type]), 404);

        return self::MODELS[$type];
    }

    private function type(Request $request): string
    {
        $parts = explode('.', (string) $request->route()?->getName());
        $type = $parts[1] ?? '';
        $this->model($type);

        return $type;
    }

    private function findItem(string $type, int $id): Model
    {
        $model = $this->model($type);

        return $model::query()->findOrFail($id);
    }

    private function authorizePublication(Request $request, string $type, bool $active, ?string $status, ?string $currentStatus = null, bool $currentActive = false, array $attributes = [], ?Model $current = null): void
    {
        $permission = match ($type) {
            'banners' => 'banners.publish', 'kpis' => 'kpis.publish', default => 'homepage.publish',
        };
        $needsPermission = $active !== $currentActive;
        if ($type === 'updates') {
            $publishing = in_array($status, ['published', 'scheduled'], true);
            $wasPublished = in_array($currentStatus, ['published', 'scheduled'], true);
            $datesChanged = $current && (
                $this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at)
                || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at)
            );
            $needsPermission = ($status !== $currentStatus && ($publishing || $wasPublished)) || ($publishing && $datesChanged);
        }

        if ($needsPermission) {
            abort_unless($request->user()->can($permission), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
