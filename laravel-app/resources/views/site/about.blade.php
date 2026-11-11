@extends('layouts.public')

@section('content')
    <section class="page-hero page-hero--small">
        <div class="site-container">
            <p class="eyebrow">{{ __('ui.site.district') }} · {{ __('ui.site.government') }}</p>
            <h1>{{ __('ui.navigation.about') }}</h1>
        </div>
    </section>

    <div class="site-container content-page">
        @if ($sections->isNotEmpty())
        <div class="about-layout">
            <nav class="about-index" aria-label="{{ __('ui.site.about_contents') }}">
                <h2>{{ __('ui.site.about_contents') }}</h2>
                <ol>
                    @foreach ($sections as $section)
                        <li><a href="#about-section-{{ $section['key'] }}"><span aria-hidden="true">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>{{ $section['title'] }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <div class="about-page">
                @foreach ($sections as $section)
                    <section class="about-section" aria-labelledby="about-section-{{ $section['key'] }}">
                        <div class="about-section__heading">
                            <span class="about-section__number" aria-hidden="true">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 id="about-section-{{ $section['key'] }}">{{ $section['title'] }}</h2>
                        </div>
                        <div class="about-section__content">
                            {!! \Illuminate\Support\Str::markdown($section['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
        @else
            <p class="about-empty-state" role="status">{{ __('ui.site.about_empty') }}</p>
        @endif
    </div>
@endsection
