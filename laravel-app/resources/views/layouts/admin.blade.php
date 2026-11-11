<!doctype html>
<html lang="{{ app()->getLocale() === 'hi' ? 'hi-IN' : 'en-IN' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102d55">
    <title>{{ $pageTitle ?? __('ui.admin.dashboard_title') }} — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="admin-body" data-dismiss-label="{{ __('ui.admin_content.dismiss_notification') }}">
    <a class="skip-link" href="#admin-main">{{ __('ui.accessibility.main_content') }}</a>
    <header class="admin-topbar">
        <a class="admin-topbar__brand" href="{{ route('admin.dashboard') }}">
            <span class="brand__mark" aria-hidden="true">J</span>
            <span>{{ __('ui.admin.workspace') }}</span>
        </a>
        <div class="admin-topbar__actions">
            <span class="admin-topbar__user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="button button--secondary" type="submit">{{ __('ui.admin.logout') }}</button>
            </form>
        </div>
    </header>
    <div class="admin-shell" x-data="portalSidebar" x-bind:class="collapsed ? 'admin-shell--collapsed' : ''">
        <aside class="admin-sidebar" aria-label="{{ __('ui.admin.workspace') }}">
            <button class="admin-sidebar__toggle" type="button" aria-controls="admin-navigation" x-bind:aria-expanded="!collapsed" x-on:click="toggle()">
                <span aria-hidden="true">☰</span>
                <span class="sr-only" x-show="!collapsed">{{ __('ui.admin_content.collapse_sidebar') }}</span>
                <span class="sr-only" x-show="collapsed">{{ __('ui.admin_content.expand_sidebar') }}</span>
            </button>
            <nav id="admin-navigation" class="admin-sidebar__nav" aria-label="{{ __('ui.admin.workspace') }}" x-show="!collapsed">
                @foreach ($adminNavigation as $item)
                    <a class="admin-sidebar__link" href="{{ $item['url'] }}" @if (request()->routeIs($item['active_pattern'])) aria-current="page" @endif>{{ __('ui.admin_content.'.$item['label']) }}</a>
                @endforeach
                <a class="admin-sidebar__public" href="{{ route('home') }}">{{ __('ui.site.public_site') }}<span aria-hidden="true">↗</span></a>
            </nav>
        </aside>
        <main id="admin-main" class="admin-main">
            @yield('content')
        </main>
    </div>
    <div class="admin-loading-skeleton" aria-hidden="true">
        <div class="admin-loading-skeleton__panel"><span></span><span></span><span></span><span></span></div>
    </div>
    <p class="sr-only" role="status" aria-live="polite" data-admin-loading-message hidden>{{ __('ui.admin_content.loading') }}</p>
</body>
</html>
