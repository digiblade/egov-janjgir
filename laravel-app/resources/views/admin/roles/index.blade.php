@extends('layouts.admin', ['pageTitle' => __('admin.roles.title')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row"><div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('admin.roles.title') }}</h1></div>@can('roles.create')<a class="button button--primary" href="{{ route('admin.roles.create') }}">{{ __('admin.roles.create_action') }}</a>@endcan</header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('admin.roles.title') }}</caption><thead><tr><th scope="col">{{ __('admin.roles.name') }}</th><th scope="col">{{ __('admin.roles.permissions') }}</th><th scope="col">{{ __('admin.roles.users') }}</th><th scope="col">{{ __('admin.roles.actions') }}</th></tr></thead><tbody>
        @forelse ($roles as $role)
            @php($isProtected = $role->name === $protectedRoleName)
            @php($isBuiltInAdmin = $role->name === $builtInAdminName)
            <tr><td><strong>{{ $role->name }}</strong>@if ($isProtected)<small>{{ __('admin.roles.locked') }}</small>@endif</td><td>{{ $role->permissions->count() }}</td><td>{{ $role->users_count }}</td><td class="admin-row-actions">@can('roles.edit')@unless ($isProtected)<a href="{{ route('admin.roles.edit', $role) }}">{{ __('admin.roles.edit') }}</a>@endunless @endcan @can('roles.delete')@unless ($isProtected || $isBuiltInAdmin || $role->users_count > 0)<form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm(@js(__('admin.roles.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('admin.roles.delete') }}</button></form>@endunless @endcan</td></tr>
        @empty
            <tr><td colspan="4">{{ __('admin.roles.empty') }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $roles->links() }}</div>
@endsection
