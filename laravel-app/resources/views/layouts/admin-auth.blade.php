<!doctype html>
<html lang="{{ app()->getLocale() === 'hi' ? 'hi-IN' : 'en-IN' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102d55">
    <title>{{ $pageTitle ?? __('ui.admin.workspace') }} — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="admin-auth-body">
    <a class="skip-link" href="#main-content">{{ __('ui.accessibility.main_content') }}</a>
    <header class="admin-auth-header">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand__mark" aria-hidden="true">J</span>
            <span class="brand__copy">
                <span class="brand__eyebrow">{{ __('ui.admin.workspace') }}</span>
                <span class="brand__name">{{ config('app.name') }}</span>
            </span>
        </a>
        <form class="language-switch" method="POST" action="{{ route('language.update', app()->getLocale() === 'hi' ? 'en' : 'hi') }}">
            @csrf
            <span>{{ __('ui.language.label') }}</span>
            <button type="submit" lang="{{ app()->getLocale() === 'hi' ? 'en' : 'hi' }}">{{ app()->getLocale() === 'hi' ? __('ui.language.english') : __('ui.language.hindi') }}</button>
        </form>
    </header>
    <main id="main-content" class="admin-auth-main">@yield('content')</main>
    <footer class="admin-auth-footer">
        <a href="{{ route('home') }}">{{ __('ui.navigation.home') }}</a>
        <span>{{ config('app.name') }}</span>
    </footer>
</body>
</html>
