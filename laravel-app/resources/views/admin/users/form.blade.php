@extends('layouts.admin', ['pageTitle' => $isEditing ? __('admin.users.edit_title') : __('admin.users.create_title')])

@section('content')
    <x-admin.back-link :href="route('admin.users.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.users.index') }}">{{ __('admin.users.title') }}</a></p><h1>{{ $isEditing ? __('admin.users.edit_title') : __('admin.users.create_title') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert"><h2>{{ __('ui.admin_content.validation_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field"><label for="name">{{ __('admin.users.name') }}</label><input id="name" name="name" value="{{ old('name', $managedUser->name) }}" maxlength="120" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="email">{{ __('admin.users.email') }}</label><input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" maxlength="254" autocomplete="email" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
            @unless ($isEditing)
                <div class="form-field"><label for="password">{{ __('admin.users.password') }}</label><input id="password" name="password" type="password" autocomplete="new-password" required><p class="field-help">{{ __('admin.users.password_help') }}</p>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="password_confirmation">{{ __('admin.users.password_confirmation') }}</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>@error('password_confirmation')<p class="field-error">{{ $message }}</p>@enderror</div>
            @endunless
        </div>
        <section class="admin-permissions" aria-labelledby="user-roles-heading">
            <h2 id="user-roles-heading">{{ __('admin.users.roles') }}</h2>
            @can('users.assign')
                <input type="hidden" name="roles_present" value="1">
                @php($selectedRoleIds = collect(old('roles', $managedUser->exists ? $managedUser->roles->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all())
                <div class="admin-permission-grid">
                    @foreach ($roles as $role)
                        <label class="admin-permission-option"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array((int) $role->id, $selectedRoleIds, true))><span>{{ $role->name }}</span></label>
                    @endforeach
                </div>
                @error('roles')<p class="field-error">{{ $message }}</p>@enderror
            @else
                <p>{{ __('admin.users.no_role_assignment') }}</p>
            @endcan
        </section>
        <section class="admin-permissions" aria-labelledby="user-permissions-heading">
            <h2 id="user-permissions-heading">{{ __('admin.users.direct_permissions') }}</h2>
            @can('users.assign')
                @if ($isEditing && (int) auth()->id() === (int) $managedUser->id)
                    <p class="field-help">{{ __('admin.users.self_permissions_locked') }}</p>
                    @forelse (($directPermissions ?? []) as $permissionName)
                        <code class="permission-name">{{ $permissionName }}</code>
                    @empty
                        <p>{{ __('admin.users.no_direct_permissions') }}</p>
                    @endforelse
                @else
                    <p class="field-help">{{ __('admin.users.direct_permissions_help') }}</p>
                    <input type="hidden" name="permissions_present" value="1">
                    @php($selectedPermissionNames = collect(old('permissions', $directPermissions ?? []))->all())
                    <div class="admin-permission-grid">
                        @foreach ($permissionGroups as $group => $permissions)
                            <fieldset class="admin-permission-group">
                                <legend>{{ __('admin.roles.group', ['name' => $group]) }}</legend>
                                @foreach ($permissions as $permission)
                                    <label class="admin-permission-option"><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, $selectedPermissionNames, true))><span>{{ $permission->name }}</span></label>
                                @endforeach
                            </fieldset>
                        @endforeach
                    </div>
                    @error('permissions')<p class="field-error">{{ $message }}</p>@enderror
                    @error('permissions.*')<p class="field-error">{{ $message }}</p>@enderror
                @endif
            @else
                <p>{{ __('admin.users.no_permission_assignment') }}</p>
            @endcan
        </section>
        @if ($isEditing && $managedUser->mustChangePassword())<p class="field-help">{{ __('admin.users.must_change_password') }}</p>@endif
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.users.index') }}">{{ __('admin.users.cancel') }}</a><button class="button button--primary" type="submit">{{ $isEditing ? __('admin.users.save') : __('admin.users.create_action') }}</button></div>
    </form>
@endsection
