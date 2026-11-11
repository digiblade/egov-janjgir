@extends('layouts.admin', ['pageTitle' => __('admin.users.title')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('admin.users.title') }}</h1></div>
        @can('users.create')<a class="button button--primary" href="{{ route('admin.users.create') }}">{{ __('admin.users.create_action') }}</a>@endcan
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <form class="admin-filter" method="GET" action="{{ route('admin.users.index') }}">
        <label for="user-q">{{ __('admin.users.search') }}</label><input id="user-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="user-status">{{ __('admin.users.status') }}</label><select id="user-status" name="status"><option value="">{{ __('admin.users.all_statuses') }}</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.users.active') }}</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('admin.users.inactive') }}</option></select>
        <label for="user-role">{{ __('admin.users.role') }}</label><select id="user-role" name="role"><option value="">{{ __('admin.users.all_roles') }}</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((string) ($filters['role'] ?? '') === (string) $role->id)>{{ $role->name }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('admin.users.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('admin.users.title') }}</caption><thead><tr><th scope="col">{{ __('admin.users.name') }}</th><th scope="col">{{ __('admin.users.email') }}</th><th scope="col">{{ __('admin.users.roles') }}</th><th scope="col">{{ __('admin.users.status') }}</th><th scope="col">{{ __('admin.users.created_at') }}</th><th scope="col">{{ __('admin.users.actions') }}</th></tr></thead><tbody>
        @forelse ($users as $managedUser)
            @php($isProtected = $managedUser->hasRole($protectedRoleName))
            <tr>
                <td><strong>{{ $managedUser->name }}</strong>@if ($isProtected)<small>{{ __('admin.users.protected') }}</small>@elseif ((int) $managedUser->id === (int) auth()->id())<small>{{ __('admin.users.self_account') }}</small>@endif</td>
                <td>{{ $managedUser->email }}</td>
                <td>{{ $managedUser->roles->pluck('name')->join(', ') ?: '—' }}</td>
                <td><x-admin.active-status :active="$managedUser->is_active" />@if ($managedUser->mustChangePassword())<small>{{ __('admin.users.must_change_password') }}</small>@endif</td>
                <td>{{ $managedUser->created_at?->format('d M Y') ?? '—' }}</td>
                <td class="admin-row-actions">
                    @can('users.edit')@unless ($isProtected)<a href="{{ route('admin.users.edit', $managedUser) }}">{{ __('admin.users.edit') }}</a>@endunless @endcan
                    @can('users.disable')@unless ($isProtected || (int) $managedUser->id === (int) auth()->id())
                        <form method="POST" action="{{ route('admin.users.status', $managedUser) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}"><button class="text-button" type="submit">{{ $managedUser->is_active ? __('admin.users.disable') : __('admin.users.enable') }}</button></form>
                    @endunless @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6">{{ __('admin.users.empty') }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $users->links() }}</div>
@endsection
