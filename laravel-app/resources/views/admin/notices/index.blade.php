@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.notices')])
@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.notices') }}</h1></div>
        @can('notices.create')<a class="button button--primary" href="{{ route('admin.notices.create') }}">{{ __('ui.admin_content.create_notice') }}</a>@endcan
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="notices" permission="notices.edit" />
    <form class="admin-filter" method="GET" action="{{ route('admin.notices.index') }}">
        <label for="notice-q">{{ __('ui.admin_content.search') }}</label><input id="notice-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="notice-status">{{ __('ui.admin_content.status') }}</label><select id="notice-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (['draft', 'published', 'scheduled', 'archived'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.notices') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.title') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.published_at') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($notices as $notice)
            <tr @can('notices.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="notices" permission="notices.edit" :id="$notice->id" :label="$notice->localized('title')" /><strong>{{ $notice->localized('title') }}</strong><small>{{ $notice->slug }}</small></td><td>{{ __('ui.admin_content.statuses.'.$notice->status) }}</td><td><x-admin.active-status :active="$notice->isPubliclyVisibleNow()" /></td><td>{{ $notice->published_at?->format('d M Y H:i') ?? '—' }}</td><td>{{ $notice->updated_at?->format('d M Y H:i') ?? '—' }}</td><td class="admin-row-actions">@can('notices.edit')<a href="{{ route('admin.notices.edit', $notice) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('notices.delete')<form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>
        @empty
            <tr><td colspan="6">{{ __('ui.admin_content.empty') }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $notices->links() }}</div>
@endsection
