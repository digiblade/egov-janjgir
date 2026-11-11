@extends('layouts.admin-auth', ['pageTitle' => __('ui.deployment.migration_title')])

@section('content')
    <main class="auth-card" id="main-content">
        <p class="eyebrow">{{ __('ui.deployment.migration_label') }}</p>
        <h1>{{ __('ui.deployment.migration_title') }}</h1>
        <p>{{ __('ui.deployment.migration_description') }}</p>
        <form method="POST" action="{{ route('deployment.migrate.store') }}">
            @csrf
            <div class="form-field">
                <label for="deployment-key">{{ __('ui.deployment.migration_key') }}</label>
                <input id="deployment-key" name="key" type="password" inputmode="text" autocomplete="off" required minlength="64" maxlength="64">
                @error('key')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button button--primary" type="submit">{{ __('ui.deployment.migration_submit') }}</button>
        </form>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
    </main>
@endsection
