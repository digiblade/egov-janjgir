<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamCategory;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TeamMemberManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', TeamMember::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:team_categories,id'],
        ]);
        $members = TeamMember::query()->with(['category', 'photograph', 'lastEditor'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('full_name_en', 'like', '%'.$term.'%')
                ->orWhere('full_name_hi', 'like', '%'.$term.'%')
                ->orWhere('designation_en', 'like', '%'.$term.'%')
                ->orWhere('designation_hi', 'like', '%'.$term.'%')
                ->orWhere('slug', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('team_category_id', $category))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();
        $categories = TeamCategory::query()->orderBy('display_order')->orderBy('name_en')->get();

        return view('admin.team.index', compact('members', 'categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.team.form', [
            'member' => new TeamMember(['status' => 'draft', 'is_active' => true]),
            'categories' => TeamCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        if (($attributes['email_public'] ?? false) || ($attributes['contact_number_public'] ?? false)) {
            abort_unless($request->user()->can('team.publish'), 403);
        }
        if (! empty($attributes['photograph_media_id'])) {
            abort_unless($request->user()->can('team.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status']);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: Str::slug($attributes['full_name_en']));
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $member = TeamMember::query()->create($attributes);
        $member->forceFill([
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ])->save();

        return to_route('admin.team.edit', $member)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(TeamMember $teamMember): View
    {
        $teamMember->load(['category', 'photograph']);
        $categories = TeamCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $teamMember->team_category_id))
            ->orderBy('display_order')->get();

        return view('admin.team.form', [
            'member' => $teamMember,
            'categories' => $categories,
            'isEditing' => true,
        ]);
    }

    public function update(TeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $attributes = $request->validated();
        $publicContactChanged = (bool) $attributes['email_public'] !== (bool) $teamMember->email_public
            || (bool) $attributes['contact_number_public'] !== (bool) $teamMember->contact_number_public
            || ($teamMember->email_public && array_key_exists('email', $attributes) && $attributes['email'] !== $teamMember->email)
            || ($teamMember->contact_number_public && array_key_exists('contact_number', $attributes) && $attributes['contact_number'] !== $teamMember->contact_number);
        if ($publicContactChanged) {
            abort_unless($request->user()->can('team.publish'), 403);
        }
        $photoChanged = (string) ($attributes['photograph_media_id'] ?? '') !== (string) ($teamMember->photograph_media_id ?? '');
        if ($photoChanged) {
            abort_unless($request->user()->can('team.upload'), 403);
        }
        $this->ensurePublishPermission($request, $attributes['status'], $teamMember->status, $attributes, $teamMember);
        $attributes['slug'] = $this->uniqueSlug($attributes['slug'] ?: $teamMember->slug, $teamMember->id);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $teamMember->update($attributes);
        $teamMember->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.team.edit', $teamMember)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $teamMember->forceFill(['updated_by' => $request->user()->id])->save();
        $teamMember->delete();

        return to_route('admin.team.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'team-member';
        $candidate = $base;
        $suffix = 2;
        while (TeamMember::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?TeamMember $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && ($this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at) || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at))));

        if ($publicationTransition) {
            abort_unless($request->user()->can('team.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
