@extends('layouts.admin', ['pageTitle' => $isEditing ? __('admin.roles.edit_title') : __('admin.roles.create_title')])

@section('content')
    <x-admin.back-link :href="route('admin.roles.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.roles.index') }}">{{ __('admin.roles.title') }}</a></p><h1>{{ $isEditing ? __('admin.roles.edit_title') : __('admin.roles.create_title') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert"><h2>{{ __('ui.admin_content.validation_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        @php($isBuiltInAdmin = $isEditing && $role->name === $builtInAdminName)
        <div class="form-field"><label for="name">{{ __('admin.roles.name') }}</label><input id="name" name="name" value="{{ old('name', $role->name) }}" maxlength="120" required @if ($isBuiltInAdmin) readonly @endif>@if ($isBuiltInAdmin)<p class="field-help">{{ __('admin.roles.built_in_admin_help') }}</p>@endif @error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
        <section class="admin-permissions" aria-labelledby="permissions-heading"><h2 id="permissions-heading">{{ __('admin.roles.permissions') }}</h2>
            @php($selectedPermissionIds = collect(old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all())
            @foreach ($permissionGroups as $group => $permissions)
                <fieldset class="admin-permission-group"><legend>{{ __('admin.roles.group', ['name' => str_replace('-', ' ', ucfirst($group))]) }}</legend><div class="admin-permission-grid">
                    @foreach ($permissions as $permission)
                        <label class="admin-permission-option"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array((int) $permission->id, $selectedPermissionIds, true))><span>{{ $permission->name }}</span></label>
                    @endforeach
                </div></fieldset>
            @endforeach
            @error('permissions')<p class="field-error">{{ $message }}</p>@enderror
        </section>
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.roles.index') }}">{{ __('admin.roles.cancel') }}</a><button class="button button--primary" type="submit">{{ $isEditing ? __('admin.roles.save') : __('admin.roles.create_action') }}</button></div>
    </form>
@endsection
