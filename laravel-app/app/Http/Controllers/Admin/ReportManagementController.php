<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReportManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', Report::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:report_categories,id'],
            'period' => ['nullable', 'string', 'max:100'],
            'office' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $reports = Report::query()->with(['category', 'file', 'lastEditor'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('title_en', 'like', '%'.$term.'%')
                ->orWhere('title_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['period'] ?? null, fn ($query, $period) => $query->where('report_period', $period))
            ->when($filters['office'] ?? null, fn ($query, $office) => $query->where('office_block', $office))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('report_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('report_date', '<=', $date))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();
        $categories = ReportCategory::query()->orderBy('display_order')->orderBy('name_en')->get();
        $periods = Report::query()->whereNotNull('report_period')->distinct()->orderByDesc('report_period')->pluck('report_period');
        $offices = Report::query()->whereNotNull('office_block')->distinct()->orderBy('office_block')->pluck('office_block');

        return view('admin.reports.index', compact('reports', 'categories', 'periods', 'offices', 'filters'));
    }

    public function create(): View
    {
        return view('admin.reports.form', [
            'report' => new Report(['status' => 'draft']),
            'categories' => ReportCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(ReportRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('reports.upload'), 403);
        $attributes = $request->validated();
        $this->ensurePublishPermission($request, $attributes['status']);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['title_en']));
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $report = Report::query()->create($attributes);
        $report->forceFill([
            'uploaded_by' => $request->user()->id,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ])->save();

        return to_route('admin.reports.edit', $report)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Report $report): View
    {
        $report->load(['category', 'file']);
        $categories = ReportCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $report->category_id))
            ->orderBy('display_order')->get();

        return view('admin.reports.form', compact('report', 'categories') + ['isEditing' => true]);
    }

    public function update(ReportRequest $request, Report $report): RedirectResponse
    {
        $attributes = $request->validated();
        $mediaChanged = (int) $attributes['media_asset_id'] !== (int) $report->media_asset_id;
        if ($mediaChanged) {
            abort_unless($request->user()->can('reports.upload'), 403);
        }

        $this->ensurePublishPermission($request, $attributes['status'], $report->status, $attributes, $report);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $report->slug, $report->id);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $report->update($attributes);
        $report->forceFill([
            'updated_by' => $request->user()->id,
            ...($mediaChanged ? ['uploaded_by' => $request->user()->id] : []),
        ])->save();

        return to_route('admin.reports.edit', $report)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, Report $report): RedirectResponse
    {
        $report->forceFill(['updated_by' => $request->user()->id])->save();
        $report->delete();

        return to_route('admin.reports.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'report';
        $candidate = $base;
        $suffix = 2;
        while (Report::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?Report $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && (
                    $this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at)
                    || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at)
                )));

        if ($publicationTransition) {
            abort_unless($request->user()->can('reports.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
