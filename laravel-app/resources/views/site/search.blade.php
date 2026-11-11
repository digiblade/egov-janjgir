@extends('layouts.public')

@section('content')
    <section class="page-hero">
        <div class="site-container">
            <p class="eyebrow">{{ __('ui.actions.search') }}</p>
            <h1>{{ __('ui.site.search_results') }}</h1>
            @if ($term !== '')<p>{{ __('ui.site.search_for', ['term' => $term]) }}</p>@endif
        </div>
    </section>

    <div class="site-container content-page">
        <form action="{{ route('search') }}" method="GET" role="search" class="site-search site-search--page">
            <label class="sr-only" for="search-query">{{ __('ui.site.search_label') }}</label>
            <input id="search-query" name="q" type="search" value="{{ $term }}" maxlength="120" required>
            <button class="button button--primary" type="submit">{{ __('ui.actions.search') }}</button>
        </form>

        @if ($term === '')
            <p class="empty-state">{{ __('ui.site.search_start') }}</p>
        @elseif ($hasResults)
            @foreach ($groups as $group)
                @if ($group['results']->isNotEmpty())
                    <section class="search-results-group" aria-labelledby="results-{{ $loop->index }}">
                        <h2 id="results-{{ $loop->index }}">{{ $group['title'] }}</h2>
                        @foreach ($group['results'] as $result)
                            <article class="search-result">
                                <h3>@if ($result['url'])<a class="content-title-link" href="{{ $result['url'] }}">{{ $result['title'] }}</a>@else{{ $result['title'] }}@endif</h3>
                                @if ($result['summary'])<p>{{ $result['summary'] }}</p>@endif
                                @if ($result['published_at'])<time datetime="{{ $result['published_at']->toDateString() }}">{{ $result['published_at']->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>@endif
                            </article>
                        @endforeach
                        {{ $group['results']->links() }}
                    </section>
                @endif
            @endforeach
        @else
            <p class="empty-state">{{ __('ui.site.no_matching_content') }}</p>
        @endif
    </div>
@endsection
