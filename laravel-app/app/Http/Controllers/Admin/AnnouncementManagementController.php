<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AnnouncementManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'in:'.implode(',', Announcement::STATUSES)]]);
        $announcements = Announcement::query()->with('lastEditor')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search->where('title_en', 'like', '%'.$term.'%')->orWhere('title_hi', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.announcements.index', compact('announcements', 'filters'));
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['announcement' => new Announcement(), 'isEditing' => false]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $attributes = $this->prepare($request->validated());
        if (filled($attributes['featured_image_media_id'] ?? null) || filled($attributes['attachment_media_id'] ?? null)) {
            abort_unless($request->user()->can('announcements.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status']);
        $announcement = Announcement::query()->create($attributes);
        $announcement->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.announcements.edit', $announcement)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['announcement' => $announcement, 'isEditing' => true]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $attributes = $this->prepare($request->validated(), $announcement);
        $mediaChanged = (string) ($attributes['featured_image_media_id'] ?? '') !== (string) ($announcement->featured_image_media_id ?? '')
            || (string) ($attributes['attachment_media_id'] ?? '') !== (string) ($announcement->attachment_media_id ?? '');
        if ($mediaChanged) {
            abort_unless($request->user()->can('announcements.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status'], $announcement->status, $attributes, $announcement);
        $announcement->update($attributes);
        $announcement->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.announcements.edit', $announcement)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->forceFill(['updated_by' => $request->user()->id])->save();
        $announcement->delete();

        return to_route('admin.announcements.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function prepare(array $attributes, ?Announcement $announcement = null): array
    {
        $slug = $attributes['slug'] ?? '';
        $attributes['slug'] = $this->uniqueSlug($slug !== '' ? $slug : ($announcement?->slug ?: Str::slug($attributes['title_en'])), $announcement?->id);

        if ($attributes['status'] === 'published' && empty($attributes['published_at'])) {
            $attributes['published_at'] = Carbon::now();
        }

        return $attributes;
    }

    private function uniqueSlug(string $base, ?int $ignoreId): string
    {
        $candidate = $base;
        $suffix = 2;

        while (Announcement::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?Announcement $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('announcements.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
