@extends('layouts.admin-auth', ['pageTitle' => __('ui.admin.login_title')])

@section('content')
    <section class="login-card" aria-labelledby="login-title">
        <div class="login-card__heading">
            <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
            <h1 id="login-title">{{ __('ui.admin.login_title') }}</h1>
            <p>{{ __('ui.admin.login_description') }}</p>
        </div>

        @if ($errors->any())
            <div class="form-error-summary" role="alert" tabindex="-1" aria-labelledby="login-errors-heading">
                <h2 id="login-errors-heading">{{ __('ui.admin.login_errors') }}</h2>
                <ul>
                    @if ($errors->has('email'))<li><a href="#email">{{ $errors->first('email') }}</a></li>@endif
                    @if ($errors->has('password'))<li><a href="#password">{{ $errors->first('password') }}</a></li>@endif
                    @if ($errors->has('remember'))<li><a href="#remember">{{ $errors->first('remember') }}</a></li>@endif
                </ul>
            </div>
        @endif

        <form class="login-form" method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <div class="form-field">
                <label for="email">{{ __('ui.admin.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" required autofocus aria-describedby="email-error">
                @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="password">{{ __('ui.admin.password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required aria-describedby="password-error">
                @error('password')<p id="password-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <label class="remember-field" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <span>{{ __('ui.admin.remember') }}</span>
            </label>
            <button class="button button--primary login-submit" type="submit">{{ __('ui.admin.sign_in') }}</button>
        </form>
    </section>
@endsection
