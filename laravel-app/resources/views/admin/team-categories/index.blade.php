@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.team_categories')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow"><a href="{{ route('admin.team.index') }}">{{ __('ui.admin_content.team') }}</a></p><h1>{{ __('ui.admin_content.team_categories') }}</h1></div>
        @can('team.create')<a class="button button--primary" href="{{ route('admin.team-categories.create') }}">{{ __('ui.admin_content.create_team_category') }}</a>@endcan
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="team-categories" permission="team.edit" />
    @if ($errors->has('category'))<p class="form-error-summary" role="alert">{{ $errors->first('category') }}</p>@endif

    <form class="admin-filter" method="GET" action="{{ route('admin.team-categories.index') }}">
        <label for="team-category-q">{{ __('ui.admin_content.search') }}</label>
        <input id="team-category-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>

    <div class="admin-table-wrap"><table class="admin-table">
        <caption class="sr-only">{{ __('ui.admin_content.team_categories') }}</caption>
        <thead><tr><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.team_member_count') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead>
        <tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">@forelse ($categories as $category)
            <tr @can('team.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="team-categories" permission="team.edit" :id="$category->id" :label="$category->localized('name')" /><strong>{{ $category->localized('name') }}</strong><small>{{ $category->slug }}</small></td><td>{{ $category->members_count }}</td><td><x-admin.active-status :active="$category->is_active" /></td><td class="admin-row-actions">
                @can('team.edit')<a href="{{ route('admin.team-categories.edit', $category) }}">{{ __('ui.admin_content.edit') }}</a>@endcan
                @can('team.delete')<form method="POST" action="{{ route('admin.team-categories.destroy', $category) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan
            </td></tr>
        @empty<tr><td colspan="4">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse</tbody>
    </table></div>
    <div class="admin-pagination">{{ $categories->links() }}</div>
@endsection
