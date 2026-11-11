@extends('layouts.public')

@section('content')
    <section class="page-hero page-hero--small">
        <div class="site-container">
            <p class="eyebrow">{{ __('ui.site.latest') }}</p>
            <h1>{{ __('ui.site.announcements') }}</h1>
        </div>
    </section>
    <div class="site-container content-page">
        <form action="{{ route('announcements.index') }}" method="GET" role="search" class="site-search site-search--page">
            <label class="sr-only" for="announcement-query">{{ __('ui.site.search_label') }}</label>
            <input id="announcement-query" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="120">
            <button class="button button--primary" type="submit">{{ __('ui.actions.search') }}</button>
        </form>
        <div class="listing-stack">
            @forelse ($announcements as $announcement)
                <article class="listing-card">
                    @if ($announcement->featuredImage?->visibility === 'public')
                        <img class="listing-card__image" src="{{ route('media.public', $announcement->featuredImage) }}" alt="" loading="lazy">
                    @endif
                    @if ($announcement->published_at)<time datetime="{{ $announcement->published_at->toDateString() }}">{{ $announcement->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>@endif
                    <h2><a class="content-title-link" href="{{ route('announcements.show', $announcement) }}">{{ $announcement->localized('title') }}</a></h2>
                    @if ($announcement->localized('description'))<p>{{ $announcement->localized('description') }}</p>@endif
                    <a class="text-link" href="{{ route('announcements.show', $announcement) }}">{{ __('ui.actions.read_more') }}<span aria-hidden="true">→</span></a>
                </article>
            @empty
                <p class="empty-state">{{ __('ui.site.no_announcements') }}</p>
            @endforelse
        </div>
        {{ $announcements->links() }}
    </div>
@endsection
