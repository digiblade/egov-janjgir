<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutSectionRequest;
use App\Models\AboutSection;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AboutSectionManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', AboutSection::STATUSES)],
        ]);

        $sections = AboutSection::query()->with('lastEditor')
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($search) => $search
                ->where('title_en', 'like', '%'.$term.'%')
                ->orWhere('title_hi', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('display_order')->orderBy('id')
            ->paginate(20)->withQueryString();

        return view('admin.about-sections.index', compact('sections', 'filters'));
    }

    public function create(): View
    {
        return view('admin.about-sections.form', [
            'section' => new AboutSection([
                'status' => 'draft',
                'display_order' => ((int) AboutSection::query()->max('display_order')) + 10,
            ]),
            'isEditing' => false,
        ]);
    }

    public function store(AboutSectionRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $attributes = $request->validated();
        $this->authorizePublication($request, $attributes['status']);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $section = AboutSection::query()->create($attributes);
        $section->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();
        $auditLogger->record('about.created', $section, array_keys($attributes));

        return to_route('admin.about-sections.edit', $section)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(AboutSection $section): View
    {
        return view('admin.about-sections.form', compact('section') + ['isEditing' => true]);
    }

    public function update(AboutSectionRequest $request, AboutSection $section, AuditLogger $auditLogger): RedirectResponse
    {
        $attributes = $request->validated();
        $this->authorizePublication($request, $attributes['status'], $section->status, $attributes, $section);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $section->fill($attributes);
        $changed = array_keys($section->getDirty());
        $section->forceFill(['updated_by' => $request->user()->id])->save();
        if ($changed !== []) {
            $auditLogger->record('about.updated', $section, $changed);
        }

        return to_route('admin.about-sections.edit', $section)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, AboutSection $section, AuditLogger $auditLogger): RedirectResponse
    {
        $auditLogger->record('about.deleted', $section, ['deleted_at']);
        $section->forceFill(['updated_by' => $request->user()->id])->save();
        $section->delete();

        return to_route('admin.about-sections.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function authorizePublication(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?AboutSection $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && (
                    $this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at)
                    || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at)
                )));

        if ($publicationTransition) {
            abort_unless($request->user()->can('about.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
