<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoticeRequest;
use App\Models\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NoticeManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'in:'.implode(',', Notice::STATUSES)]]);
        $notices = Notice::query()->with('lastEditor')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search->where('title_en', 'like', '%'.$term.'%')->orWhere('title_hi', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.notices.index', compact('notices', 'filters'));
    }

    public function create(): View
    {
        return view('admin.notices.form', ['notice' => new Notice(), 'isEditing' => false]);
    }

    public function store(NoticeRequest $request): RedirectResponse
    {
        $attributes = $this->prepare($request->validated());
        if (filled($attributes['attachment_media_id'] ?? null)) {
            abort_unless($request->user()->can('notices.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status']);
        $notice = Notice::query()->create($attributes);
        $notice->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.notices.edit', $notice)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Notice $notice): View
    {
        return view('admin.notices.form', ['notice' => $notice, 'isEditing' => true]);
    }

    public function update(NoticeRequest $request, Notice $notice): RedirectResponse
    {
        $attributes = $this->prepare($request->validated(), $notice);
        if ((string) ($attributes['attachment_media_id'] ?? '') !== (string) ($notice->attachment_media_id ?? '')) {
            abort_unless($request->user()->can('notices.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status'], $notice->status, $attributes, $notice);
        $notice->update($attributes);
        $notice->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.notices.edit', $notice)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, Notice $notice): RedirectResponse
    {
        $notice->forceFill(['updated_by' => $request->user()->id])->save();
        $notice->delete();

        return to_route('admin.notices.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function prepare(array $attributes, ?Notice $notice = null): array
    {
        $slug = $attributes['slug'] ?? '';
        $attributes['slug'] = $this->uniqueSlug($slug !== '' ? $slug : ($notice?->slug ?: Str::slug($attributes['title_en'])), $notice?->id);
        $attributes['is_pinned'] = (bool) ($attributes['is_pinned'] ?? false);

        if ($attributes['status'] === 'published' && empty($attributes['published_at'])) {
            $attributes['published_at'] = Carbon::now();
        }

        return $attributes;
    }

    private function uniqueSlug(string $base, ?int $ignoreId): string
    {
        $candidate = $base;
        $suffix = 2;

        while (Notice::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?Notice $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('notices.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
