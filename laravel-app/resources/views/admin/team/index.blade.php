@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.team')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.team') }}</h1></div>
        <div class="admin-heading-actions">
            @can('team.view')<a class="button button--secondary" href="{{ route('admin.team-categories.index') }}">{{ __('ui.admin_content.team_categories') }}</a>@endcan
            @can('team.create')<a class="button button--primary" href="{{ route('admin.team.create') }}">{{ __('ui.admin_content.create_team_member') }}</a>@endcan
        </div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="team" permission="team.edit" />

    <form class="admin-filter" method="GET" action="{{ route('admin.team.index') }}">
        <label for="team-q">{{ __('ui.admin_content.search') }}</label><input id="team-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="team-status">{{ __('ui.admin_content.status') }}</label><select id="team-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (\App\Models\TeamMember::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <label for="team-category">{{ __('ui.admin_content.category') }}</label><select id="team-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>

    <div class="admin-table-wrap"><table class="admin-table">
        <caption class="sr-only">{{ __('ui.admin_content.team') }}</caption>
        <thead><tr><th scope="col">{{ __('ui.admin_content.full_name_en') }}</th><th scope="col">{{ __('ui.admin_content.designation_en') }}</th><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead>
        <tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">@forelse ($members as $member)
            <tr @can('team.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="team" permission="team.edit" :id="$member->id" :label="$member->localized('full_name')" /><strong>{{ $member->localized('full_name') }}</strong><small>{{ $member->slug }}</small></td><td>{{ $member->localized('designation') }}</td><td>{{ $member->category?->localized('name') ?? '—' }}</td><td>{{ __('ui.admin_content.statuses.'.$member->status) }}</td><td><x-admin.active-status :active="$member->isPubliclyVisibleNow()" /></td><td class="admin-row-actions">
                @can('team.edit')<a href="{{ route('admin.team.edit', $member) }}">{{ __('ui.admin_content.edit') }}</a>@endcan
                @can('team.delete')<form method="POST" action="{{ route('admin.team.destroy', $member) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan
            </td></tr>
        @empty<tr><td colspan="6">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse</tbody>
    </table></div>
    <div class="admin-pagination">{{ $members->links() }}</div>
@endsection
