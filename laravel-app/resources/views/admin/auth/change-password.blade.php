@extends('layouts.admin-auth', ['pageTitle' => __('ui.admin.change_password_title')])

@section('content')
    <section class="login-card" aria-labelledby="change-password-title">
        <div class="login-card__heading">
            <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
            <h1 id="change-password-title">{{ __('ui.admin.change_password_title') }}</h1>
            <p>{{ __('ui.admin.change_password_description') }}</p>
        </div>

        @if ($errors->any())
            <div class="form-error-summary" role="alert" tabindex="-1" aria-labelledby="password-errors-heading">
                <h2 id="password-errors-heading">{{ __('ui.admin.setup_errors') }}</h2>
                <ul>
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <p class="form-success" role="status">{{ session('status') }}</p>
        @endif

        <form class="login-form" method="POST" action="{{ route('admin.password.change.update') }}">
            @csrf
            @method('PUT')
            <div class="form-field">
                <label for="current_password">{{ __('ui.admin.current_password') }}</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
            </div>
            <div class="form-field">
                <label for="password">{{ __('ui.admin.new_password') }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <p>{{ __('ui.admin.setup_password_hint') }}</p>
            </div>
            <div class="form-field">
                <label for="password_confirmation">{{ __('ui.admin.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="button button--primary login-submit" type="submit">{{ __('ui.admin.change_password_submit') }}</button>
        </form>

        <form method="POST" action="{{ route('admin.logout') }}" class="password-change-logout">
            @csrf
            <button class="text-link" type="submit">{{ __('ui.admin.logout') }}</button>
        </form>
    </section>
@endsection
