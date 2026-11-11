@extends('layouts.public')

@section('content')
    <section class="page-hero page-hero--small">
        <div class="site-container">
            <a class="breadcrumb-link" href="{{ route('notices.index') }}">{{ __('ui.site.back_to_notices') }}</a>
            <p class="eyebrow">{{ __('ui.site.notices') }}</p>
            <h1>{{ $notice->localized('title') }}</h1>
            @if ($notice->published_at)<p class="page-date"><time datetime="{{ $notice->published_at->toDateString() }}">{{ $notice->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</time></p>@endif
        </div>
    </section>
    <article class="site-container content-page article-page">
        @if ($notice->localized('description'))<div class="article-copy">{{ $notice->localized('description') }}</div>@endif
        @if ($notice->attachment?->visibility === 'public')
            <a class="button button--primary" href="{{ route('media.public', $notice->attachment) }}">{{ __('ui.actions.download') }}<span aria-hidden="true">↓</span></a>
        @endif
        @if (\App\Support\SafeUrl::normalize($notice->external_url))
            <a class="text-link" href="{{ \App\Support\SafeUrl::normalize($notice->external_url) }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.read_more') }}<span aria-hidden="true">↗</span><span class="sr-only">{{ __('ui.site.opens_new_tab') }}</span></a>
        @endif
    </article>
@endsection
