@extends('layouts.admin-auth', ['pageTitle' => __('ui.admin.setup_title')])

@section('content')
    <section class="login-card" aria-labelledby="setup-title">
        <div class="login-card__heading">
            <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
            <h1 id="setup-title">{{ __('ui.admin.setup_title') }}</h1>
            <p>{{ __('ui.admin.setup_description') }}</p>
        </div>

        @if ($errors->any())
            <div class="form-error-summary" role="alert" tabindex="-1" aria-labelledby="setup-errors-heading">
                <h2 id="setup-errors-heading">{{ __('ui.admin.setup_errors') }}</h2>
                <ul>
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form class="login-form" method="POST" action="{{ route('admin.initialize.store') }}">
            @csrf
            <div class="form-field">
                <label for="setup_key">{{ __('ui.admin.setup_key') }}</label>
                <input id="setup_key" name="setup_key" type="password" autocomplete="off" required aria-describedby="setup-key-hint">
                <p id="setup-key-hint">{{ __('ui.admin.setup_key_hint') }}</p>
            </div>
            <div class="form-field">
                <label for="name">{{ __('ui.admin.name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
            </div>
            <div class="form-field">
                <label for="email">{{ __('ui.admin.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" maxlength="254" required>
            </div>
            <div class="form-field">
                <label for="password">{{ __('ui.admin.password') }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <p>{{ __('ui.admin.setup_password_hint') }}</p>
            </div>
            <div class="form-field">
                <label for="password_confirmation">{{ __('ui.admin.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <fieldset class="setup-secondary-admin">
                <legend>{{ __('ui.admin.setup_initial_admin') }}</legend>
                <p>{{ __('ui.admin.setup_initial_admin_hint') }}</p>
                <div class="form-field">
                    <label for="admin_name">{{ __('ui.admin.name') }}</label>
                    <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}" autocomplete="name" maxlength="120" required>
                </div>
                <div class="form-field">
                    <label for="admin_email">{{ __('ui.admin.email') }}</label>
                    <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" autocomplete="username" inputmode="email" maxlength="254" required>
                </div>
                <div class="form-field">
                    <label for="admin_password">{{ __('ui.admin.setup_admin_password') }}</label>
                    <input id="admin_password" name="admin_password" type="password" autocomplete="new-password" required>
                    <p>{{ __('ui.admin.setup_password_hint') }}</p>
                </div>
                <div class="form-field">
                    <label for="admin_password_confirmation">{{ __('ui.admin.password_confirmation') }}</label>
                    <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" autocomplete="new-password" required>
                </div>
            </fieldset>
            <button class="button button--primary login-submit" type="submit">{{ __('ui.admin.setup_submit') }}</button>
        </form>
    </section>
@endsection
