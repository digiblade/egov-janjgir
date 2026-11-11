<!doctype html>
<html lang="{{ app()->getLocale() === 'hi' ? 'hi-IN' : 'en-IN' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102d55">
    @php($siteIdentity = $publicSiteSettings['site.identity'] ?? [])
    @php($siteSeo = $publicSiteSettings['site.seo'] ?? [])
    @php($siteFooter = $publicSiteSettings['site.footer'] ?? [])
    @php($localizedSiteName = ($siteIdentity['title_'.app()->getLocale()] ?? '') ?: config('app.name'))
    @php($localizedDepartmentName = ($siteIdentity['department_'.app()->getLocale()] ?? '') ?: __('ui.site.district'))
    <meta name="description" content="{{ $metaDescription ?? ($siteSeo['description_'.app()->getLocale()] ?? $localizedSiteName) }}">
    <title>{{ $pageTitle ?? ((($siteSeo['title_'.app()->getLocale()] ?? '') ?: $localizedSiteName)) }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="site-body">
    <a class="skip-link" href="#main-content">{{ __('ui.accessibility.main_content') }}</a>

    <header class="site-header">
        <div class="utility-bar">
            <div class="site-container utility-bar__inner">
                <p></p>
                <form class="language-switch" method="POST" action="{{ route('language.update', app()->getLocale() === 'hi' ? 'en' : 'hi') }}">
                    @csrf
                    <span>{{ __('ui.language.label') }}</span>
                    <button type="submit" lang="{{ app()->getLocale() === 'hi' ? 'en' : 'hi' }}">
                        {{ app()->getLocale() === 'hi' ? __('ui.language.english') : __('ui.language.hindi') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="site-container brand-row">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ config('app.name') }} — {{ __('ui.navigation.home') }}">
                <span class="brand__mark" aria-hidden="true">J</span>
                <span class="brand__copy">
                    <span class="brand__eyebrow">{{ $localizedDepartmentName }}</span>
                <span class="brand__name">{{ $localizedSiteName }}</span>
                </span>
            </a>
            <a class="staff-link" href="{{ url('/admin/login') }}">{{ __('ui.navigation.staff_login') }}<span aria-hidden="true">↗</span></a>
        </div>

        <nav class="primary-nav" aria-label="{{ __('ui.site.primary_navigation') }}">
            <div class="site-container primary-nav__inner">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>{{ __('ui.navigation.home') }}</a>
                <a href="{{ route('about') }}" @if (request()->routeIs('about')) aria-current="page" @endif>{{ __('ui.navigation.about') }}</a>
                <a href="{{ route('documents.index') }}">{{ __('ui.navigation.documents') }}</a>
                <a href="{{ route('training.index') }}">{{ __('ui.navigation.training') }}</a>
                <a href="{{ route('team.index') }}">{{ __('ui.navigation.team') }}</a>
                <a href="{{ route('reports.index') }}">{{ __('ui.navigation.reports') }}</a>
                <a href="{{ route('gallery.index') }}">{{ __('ui.navigation.gallery') }}</a>
                <a href="{{ route('contact.show') }}">{{ __('ui.navigation.contact') }}</a>
            </div>
        </nav>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-container site-footer__inner">
            <div>
                <p class="site-footer__name">{{ $localizedSiteName }}</p>
                <p>{{ $siteFooter['note_'.app()->getLocale()] ?? __('ui.site.footer_note') }}</p>
            </div>
            <nav aria-label="{{ __('ui.site.footer_navigation') }}">
                <a href="{{ route('home') }}">{{ __('ui.navigation.home') }}</a>
                <a href="{{ route('about') }}">{{ __('ui.navigation.about') }}</a>
                <a href="{{ route('documents.index') }}">{{ __('ui.navigation.documents') }}</a>
                <a href="{{ route('faqs.index') }}">{{ __('ui.catalog.faqs') }}</a>
                <a href="{{ route('contact.show') }}">{{ __('ui.navigation.contact') }}</a>
            </nav>
            @php($socialLinks = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'x' => 'X'])
            <nav class="site-footer__social" aria-label="{{ __('ui.admin_content.social_links') }}">
                @foreach ($socialLinks as $key => $label)
                    @php($socialUrl = \App\Support\SafeUrl::normalize($siteFooter[$key] ?? null))
                    @if ($socialUrl)<a href="{{ $socialUrl }}" target="_blank" rel="noopener noreferrer">{{ $label }}<span class="sr-only"> ({{ __('ui.site.opens_new_tab') }})</span></a>@endif
                @endforeach
            </nav>
            <p class="site-footer__copyright">{{ $siteFooter['copyright_'.app()->getLocale()] ?? ('© '.now()->year.' '.$localizedSiteName) }}</p>
        </div>
    </footer>
</body>
</html>
