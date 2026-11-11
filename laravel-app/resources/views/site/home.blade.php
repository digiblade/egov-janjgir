@extends('layouts.public')

@section('content')
    @php($locale = app()->getLocale())
    @php($siteCarousel = $publicSiteSettings['site.carousel'] ?? [])

    @if ($banners->isNotEmpty())
        <section class="hero" aria-label="{{ __('ui.site.featured_resources') }}">
            <div class="hero__frame" x-data="portalCarousel" data-slide-count="{{ $banners->count() }}" data-autoplay="{{ ($siteCarousel['autoplay'] ?? true) ? 'true' : 'false' }}" data-interval-ms="{{ (int) ($siteCarousel['interval_ms'] ?? 7000) }}" role="region" aria-roledescription="carousel" aria-label="{{ __('ui.accessibility.carousel_controls') }}" tabindex="0" @focusin="pause()" @mouseenter="pause()" @keydown.left.prevent="previous()" @keydown.right.prevent="next()" @pointerdown="pointerStart($event)" @pointerup="pointerEnd($event)" @pointercancel="pointerCancel()" :aria-live="paused ? 'polite' : 'off'">
                @foreach ($banners as $index => $banner)
                    @php($ctaUrl = \App\Support\SafeUrl::normalize($banner->cta_url) ?: route('about'))
                    <article class="hero-slide" style="{{ $banner->presentationStyle() }}" x-show="active === {{ $index }}" @if ($index > 0) x-cloak @endif role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} / {{ $banners->count() }}">
                        <picture class="hero-slide__picture" aria-hidden="true">
                            @if ($banner->mobileImage?->visibility === 'public')
                                <source media="(max-width: 700px)" srcset="{{ route('media.public', $banner->mobileImage) }}">
                            @endif
                            <img class="hero-slide__image" src="{{ route('media.public', $banner->desktopImage) }}" alt="" fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}" @if ($index > 0) loading="lazy" @endif>
                        </picture>
                        <div class="hero-slide__shade" aria-hidden="true"></div>
                        <div class="site-container hero-slide__content hero-slide__content--{{ in_array($banner->text_alignment, ['left', 'center', 'right'], true) ? $banner->text_alignment : 'left' }}">
                            @if ($banner->localized('title'))
                                <h1>{{ $banner->localized('title') }}</h1>
                            @endif
                            @if ($banner->localized('subtitle'))
                                <p>{{ $banner->localized('subtitle') }}</p>
                            @endif
                            @if ($banner->localized('cta_text') && $ctaUrl)
                                <a class="button button--light" href="{{ $ctaUrl }}">{{ $banner->localized('cta_text') }}<span aria-hidden="true">→</span></a>
                            @endif
                        </div>
                    </article>
                @endforeach

                @if ($banners->count() > 1)
                    <div class="hero-controls site-container" role="group" aria-label="{{ __('ui.accessibility.carousel_controls') }}">
                        <div class="hero-controls__arrows">
                            <button type="button" class="icon-button" @click="previous()" aria-label="{{ __('ui.accessibility.previous_slide') }}"><span aria-hidden="true">←</span></button>
                            <button type="button" class="icon-button" @click="next()" aria-label="{{ __('ui.accessibility.next_slide') }}"><span aria-hidden="true">→</span></button>
                        </div>
                        <div class="hero-controls__dots" role="group" aria-label="{{ __('ui.accessibility.choose_slide') }}">
                            @foreach ($banners as $index => $banner)
                                <button type="button" class="carousel-dot" :aria-current="active === {{ $index }} ? 'true' : 'false'" aria-label="{{ __('ui.accessibility.slide_number', ['number' => $index + 1]) }}" @click="goTo({{ $index }})"></button>
                            @endforeach
                        </div>
                        <button type="button" class="button button--pause" x-show="!paused" @click="pause()">{{ __('ui.accessibility.pause_slideshow') }}</button>
                        <button type="button" class="button button--pause" x-show="paused && !reducedMotion" @click="resume()">{{ __('ui.accessibility.resume_slideshow') }}</button>
                    </div>
                @endif
            </div>
        </section>
    @else
        @php($showDepartmentIntro = $department && ($sections->get('department')?->is_enabled ?? true))
        @php($departmentSection = $sections->get('department'))
        <section class="intro-hero intro-hero--welcome">
            <div class="site-container intro-hero__inner">
                <div class="intro-hero__copy">
                    <p class="eyebrow">{{ $departmentSection?->localized('label') ?: __('ui.site.district') }}</p>
                    <h1>{{ $departmentSection?->localized('title') ?: ($showDepartmentIntro ? ($department->localized('title') ?: config('app.name')) : config('app.name')) }}</h1>
                    @if ($departmentSection?->localized('description'))
                        <div class="department-rich-text">{!! \Illuminate\Support\Str::markdown($departmentSection->localized('description'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    @elseif ($showDepartmentIntro && $department->localized('description'))
                        <div class="department-rich-text">{!! \Illuminate\Support\Str::markdown($department->localized('description'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    @else
                        <p>{{ __('ui.site.footer_note') }}</p>
                    @endif
                    <a class="welcome-hero__action" href="{{ route('documents.index') }}">{{ __('ui.actions.browse_documents') }}<span aria-hidden="true">→</span></a>
                </div>
                <div class="welcome-hero__art" aria-hidden="true">
                    <div class="welcome-hero__seal"><x-icon name="building" /></div>
                    <div class="welcome-hero__arch"><span></span><span></span><span></span></div>
                    <div class="welcome-hero__accent"></div>
                </div>
            </div>
        </section>
    @endif

    <section class="search-band" aria-label="{{ __('ui.actions.search') }}">
        <div class="site-container search-band__inner">
            <form action="{{ url('/search') }}" method="GET" role="search" class="site-search">
                <label class="sr-only" for="site-search">{{ __('ui.site.search_label') }}</label>
                <input id="site-search" name="q" type="search" value="{{ request('q') }}" placeholder="{{ __('ui.site.search_hint') }}" autocomplete="off">
                <button class="button button--primary" type="submit"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>{{ __('ui.actions.search') }}</button>
            </form>
            <p>{{ __('ui.site.search_scope') }}</p>
        </div>
    </section>

    <div class="site-container home-content">
        @if (($sections->get('quick_links')?->is_enabled ?? true) && $quickLinks->isNotEmpty())
            @php($quickLinkSection = $sections->get('quick_links'))
            <section class="section-block quick-links-section portal-quick-links" style="order: {{ $sectionOrder->get('quick_links', 50) }}" aria-labelledby="quick-links-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ $quickLinkSection?->localized('label') ?: __('ui.site.useful_destinations') }}</p>
                        <h2 id="quick-links-heading">{{ $quickLinkSection?->localized('title') ?: __('ui.site.quick_links') }}</h2>
                        @if ($quickLinkSection?->localized('description'))<p class="section-heading__description">{{ $quickLinkSection->localized('description') }}</p>@endif
                    </div>
                </div>
                <nav class="portal-shortcuts" aria-label="{{ $quickLinkSection?->localized('title') ?: __('ui.site.quick_access') }}">
                    @foreach ($quickLinks as $quickLink)
                        @php($quickLinkUrl = \App\Support\SafeUrl::normalize($quickLink->url))
                        @if ($quickLinkUrl)
                            <a class="portal-shortcut" href="{{ $quickLinkUrl }}" @if ($quickLink->opens_in_new_tab) target="_blank" rel="noopener noreferrer" @endif>
                                <span class="portal-shortcut__icon"><x-icon :name="$quickLink->icon ?: 'arrow-up-right'" /></span>
                                <span class="portal-shortcut__copy"><strong>{{ $quickLink->localized('title') ?: __('ui.site.empty_content') }}</strong>@if ($quickLink->localized('description'))<small>{{ $quickLink->localized('description') }}</small>@endif</span>
                                <span class="portal-shortcut__arrow" aria-hidden="true">↗</span>
                                @if ($quickLink->is_external)<span class="sr-only">{{ __('ui.site.opens_new_tab') }}</span>@endif
                            </a>
                        @endif
                    @endforeach
                </nav>
            </section>
        @endif

        @if ($department && ($sections->get('department')?->is_enabled ?? true))
            @php($departmentSection = $sections->get('department'))
            <section class="department-section section-block" style="order: {{ $sectionOrder->get('department', 10) }}" aria-labelledby="department-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ $departmentSection?->localized('label') ?: __('ui.site.department') }}</p>
                        <h2 id="department-heading">{{ $departmentSection?->localized('title') ?: ($department->localized('title') ?: config('app.name')) }}</h2>
                        @if ($departmentSection?->localized('description'))<p class="section-heading__description">{{ $departmentSection->localized('description') }}</p>@endif
                    </div>
                </div>
                @if ($department->localized('description'))
                    <div class="department-section__description department-rich-text">{!! \Illuminate\Support\Str::markdown($department->localized('description'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                @endif
                @if ($department->image?->visibility === 'public')
                    <img class="department-section__image" src="{{ route('media.public', $department->image) }}" alt="" loading="lazy">
                @endif
                @if ($department->localized('mission'))
                    <div class="department-section__mission">
                        <h3>{{ __('ui.site.mission') }}</h3>
                        <div class="department-rich-text">{!! \Illuminate\Support\Str::markdown($department->localized('mission'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </div>
                @endif
                @if ($department->localized('objectives'))
                    <div class="department-section__mission">
                        <h3>{{ __('ui.site.objectives') }}</h3>
                        <div class="department-rich-text">{!! \Illuminate\Support\Str::markdown($department->localized('objectives'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </div>
                @endif
            </section>
        @endif

        @if ($sections->get('statistics')?->is_enabled ?? true)
            @php($statisticsSection = $sections->get('statistics'))
            <section class="section-block statistics-section" style="order: {{ min($sectionOrder->get('notices', 20), $sectionOrder->get('announcements', 30)) - 1 }}" aria-labelledby="statistics-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ $statisticsSection?->localized('label') ?: __('ui.site.at_a_glance') }}</p>
                        <h2 id="statistics-heading">{{ $statisticsSection?->localized('title') ?: __('ui.site.statistics') }}</h2>
                        @if ($statisticsSection?->localized('description'))<p class="section-heading__description">{{ $statisticsSection->localized('description') }}</p>@endif
                    </div>
                </div>
                <div class="kpi-grid">
                    @foreach ($kpis as $kpi)
                        <article class="kpi-card" style="{{ $kpi->presentationStyle() }}">
                            <x-icon :name="$kpi->icon ?: 'chart'" class="kpi-card__icon" />
                            @if ($kpi->localized('title'))<h3>{{ $kpi->localized('title') }}</h3>@endif
                            <p class="kpi-card__value"><span>{{ $kpi->prefix }}</span>{{ number_format((float) $kpi->value, fmod((float) $kpi->value, 1.0) === 0.0 ? 0 : 2) }}<span>{{ $kpi->suffix }}</span></p>
                            @if ($kpi->localized('description'))<p>{{ $kpi->localized('description') }}</p>@endif
                        </article>
                    @endforeach
                    @if ($kpis->isEmpty())<p class="empty-state kpi-empty-state">{{ __('ui.site.no_statistics') }}</p>@endif
                </div>
            </section>
        @endif

        <div class="home-grid" style="order: {{ min($sectionOrder->get('notices', 20), $sectionOrder->get('announcements', 30)) }}">
            @if ($sections->get('notices')?->is_enabled ?? true)
                @php($noticeSection = $sections->get('notices'))
                <section class="section-block notice-section" style="order: {{ $sectionOrder->get('notices', 20) }}" aria-labelledby="notices-heading">
                    <div class="section-heading section-heading--compact">
                        <div>
                            <p class="eyebrow">{{ $noticeSection?->localized('label') ?: __('ui.site.official_updates') }}</p>
                            <h2 id="notices-heading">{{ $noticeSection?->localized('title') ?: __('ui.site.notices') }}</h2>
                            @if ($noticeSection?->localized('description'))<p class="section-heading__description">{{ $noticeSection->localized('description') }}</p>@endif
                        </div>
                            <a class="text-link" href="{{ route('notices.index') }}">{{ __('ui.actions.view_all') }}<span aria-hidden="true">→</span></a>
                    </div>
                    @forelse ($notices as $notice)
                        <article class="notice-item">
                            <div class="notice-item__meta">
                                <time datetime="{{ $notice->published_at?->toDateString() }}">{{ $notice->published_at?->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>
                                @if ($notice->is_pinned || $notice->priority === 'high')
                                    <span class="status-tag">{{ __('ui.site.notice_priority') }}</span>
                                @endif
                            </div>
                            <h3><a class="content-title-link" href="{{ route('notices.show', $notice) }}">{{ $notice->localized('title') ?: __('ui.site.empty_content') }}</a></h3>
                            @if ($notice->localized('description'))
                                <p>{{ $notice->localized('description') }}</p>
                            @endif
                            @if ($notice->attachment?->visibility === 'public')
                                <a class="text-link" href="{{ route('media.public', $notice->attachment) }}">{{ __('ui.actions.download') }}<span aria-hidden="true">↓</span></a>
                            @endif
                        </article>
                    @empty
                        <p class="empty-state">{{ __('ui.site.no_notices') }}</p>
                    @endforelse
                </section>
            @endif

            @if ($sections->get('announcements')?->is_enabled ?? true)
                @php($announcementSection = $sections->get('announcements'))
                <section class="section-block announcement-section" style="order: {{ $sectionOrder->get('announcements', 30) }}" aria-labelledby="announcements-heading">
                    <div class="section-heading section-heading--compact">
                        <div>
                            <p class="eyebrow">{{ $announcementSection?->localized('label') ?: __('ui.site.latest') }}</p>
                            <h2 id="announcements-heading">{{ $announcementSection?->localized('title') ?: __('ui.site.announcements') }}</h2>
                            @if ($announcementSection?->localized('description'))<p class="section-heading__description">{{ $announcementSection->localized('description') }}</p>@endif
                        </div>
                    </div>
                    @forelse ($announcements as $announcement)
                        <article class="announcement-item">
                            <time datetime="{{ $announcement->published_at?->toDateString() }}">{{ $announcement->published_at?->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>
                            <h3><a class="content-title-link" href="{{ route('announcements.show', $announcement) }}">{{ $announcement->localized('title') ?: __('ui.site.empty_content') }}</a></h3>
                            @if ($announcement->localized('description'))
                                <p>{{ $announcement->localized('description') }}</p>
                            @endif
                            @if ($announcement->featuredImage?->visibility === 'public')
                                <img class="announcement-item__image" src="{{ route('media.public', $announcement->featuredImage) }}" alt="" loading="lazy">
                            @endif
                        </article>
                    @empty
                        <p class="empty-state">{{ __('ui.site.no_announcements') }}</p>
                    @endforelse
                </section>
            @endif
        </div>

        @if ($sections->get('updates')?->is_enabled ?? true)
            @php($updatesSectionCopy = $sections->get('updates'))
            <section class="section-block updates-section" style="order: {{ $sectionOrder->get('updates', 40) }}" aria-labelledby="updates-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ $updatesSectionCopy?->localized('label') ?: __('ui.site.recent_activity') }}</p>
                        <h2 id="updates-heading">{{ $updatesSectionCopy?->localized('title') ?: __('ui.site.updates') }}</h2>
                        @if ($updatesSectionCopy?->localized('description'))<p class="section-heading__description">{{ $updatesSectionCopy->localized('description') }}</p>@endif
                    </div>
                </div>
                @forelse ($updates as $update)
                    <article class="update-row">
                        <time class="update-row__date" datetime="{{ $update->published_at?->toDateString() }}">{{ $update->published_at?->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>
                        <div>
                            <h3>{{ $update->localized('title') ?: __('ui.site.empty_content') }}</h3>
                            @if ($update->localized('summary'))<p>{{ $update->localized('summary') }}</p>@endif
                        </div>
                        @if (\App\Support\SafeUrl::normalize($update->url))<a class="text-link" href="{{ \App\Support\SafeUrl::normalize($update->url) }}">{{ __('ui.actions.read_more') }}<span aria-hidden="true">→</span></a>@endif
                    </article>
                @empty
                    <p class="empty-state">{{ __('ui.site.no_updates') }}</p>
                @endforelse
            </section>
        @endif

    </div>
@endsection
