@extends('layouts.public')

@section('content')
    <section class="page-hero page-hero--small">
        <div class="site-container">
            <a class="breadcrumb-link" href="{{ route('announcements.index') }}">{{ __('ui.site.back_to_announcements') }}</a>
            <p class="eyebrow">{{ __('ui.site.announcements') }}</p>
            <h1>{{ $announcement->localized('title') }}</h1>
            @if ($announcement->published_at)<p class="page-date"><time datetime="{{ $announcement->published_at->toDateString() }}">{{ $announcement->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</time></p>@endif
        </div>
    </section>
    <article class="site-container content-page article-page">
        @if ($announcement->featuredImage?->visibility === 'public')
            <img class="article-image" src="{{ route('media.public', $announcement->featuredImage) }}" alt="" fetchpriority="high">
        @endif
        @if ($announcement->localized('description'))<div class="article-copy">{{ $announcement->localized('description') }}</div>@endif
        @if ($announcement->attachment?->visibility === 'public')
            <a class="button button--primary" href="{{ route('media.public', $announcement->attachment) }}">{{ __('ui.actions.download') }}<span aria-hidden="true">↓</span></a>
        @endif
    </article>
@endsection
