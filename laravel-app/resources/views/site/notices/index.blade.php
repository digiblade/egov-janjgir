@extends('layouts.public')

@section('content')
    <section class="page-hero page-hero--small">
        <div class="site-container">
            <p class="eyebrow">{{ __('ui.site.official_updates') }}</p>
            <h1>{{ __('ui.site.notices') }}</h1>
        </div>
    </section>
    <div class="site-container content-page">
        <form action="{{ route('notices.index') }}" method="GET" class="filter-form">
            <div class="filter-form__search">
                <label for="notice-query">{{ __('ui.site.search_label') }}</label>
                <input id="notice-query" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="120">
            </div>
            <div>
                <label for="notice-type">{{ __('ui.site.filter_by_type') }}</label>
                <select id="notice-type" name="type">
                    <option value="">{{ __('ui.site.all_types') }}</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ str($type)->replace(['-', '_'], ' ')->title() }}</option>
                    @endforeach
                </select>
            </div>
            <button class="button button--primary" type="submit">{{ __('ui.actions.search') }}</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['type'] ?? '') !== '')
                <a class="text-link" href="{{ route('notices.index') }}">{{ __('ui.site.clear_filters') }}</a>
            @endif
        </form>

        <div class="listing-stack">
            @forelse ($notices as $notice)
                <article class="listing-card">
                    <div class="notice-item__meta">
                        @if ($notice->published_at)<time datetime="{{ $notice->published_at->toDateString() }}">{{ $notice->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</time>@endif
                        @if ($notice->is_pinned || $notice->priority === 'high')<span class="status-tag">{{ __('ui.site.notice_priority') }}</span>@endif
                        <span>{{ str($notice->notice_type)->replace(['-', '_'], ' ')->title() }}</span>
                    </div>
                    <h2><a class="content-title-link" href="{{ route('notices.show', $notice) }}">{{ $notice->localized('title') }}</a></h2>
                    @if ($notice->localized('description'))<p>{{ $notice->localized('description') }}</p>@endif
                    <a class="text-link" href="{{ route('notices.show', $notice) }}">{{ __('ui.actions.read_more') }}<span aria-hidden="true">→</span></a>
                </article>
            @empty
                <p class="empty-state">{{ __('ui.site.no_notices') }}</p>
            @endforelse
        </div>
        {{ $notices->links() }}
    </div>
@endsection
