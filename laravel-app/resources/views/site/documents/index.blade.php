@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ __('ui.site.official_updates') }}</p><h1>{{ __('ui.catalog.documents') }}</h1></div></section>
    <div class="site-container content-page">
        <form action="{{ route('documents.index') }}" method="GET" class="filter-form">
            <div class="filter-form__search"><label for="resource-q">{{ __('ui.catalog.search') }}</label><input id="resource-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="120"></div>
            <div><label for="resource-category">{{ __('ui.catalog.category') }}</label><select id="resource-category" name="category"><option value="">{{ __('ui.catalog.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->localized('name') }}</option>@endforeach</select></div>
            <div><label for="resource-type">{{ __('ui.catalog.file_type') }}</label><select id="resource-type" name="type"><option value="">{{ __('ui.catalog.all_types') }}</option>@foreach ($types as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ strtoupper(str($type)->after('/')->value()) }}</option>@endforeach</select></div>
            <div><label for="resource-from">{{ __('ui.catalog.from') }}</label><input id="resource-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}"></div>
            <div><label for="resource-to">{{ __('ui.catalog.to') }}</label><input id="resource-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}"></div>
            <button class="button button--primary" type="submit">{{ __('ui.catalog.filter') }}</button><a class="text-link" href="{{ route('documents.index') }}">{{ __('ui.catalog.clear') }}</a>
        </form>
        <div class="resource-list">@forelse ($documents as $document)<article class="listing-card"><p class="eyebrow">{{ $document->category->localized('name') }} · {{ strtoupper(str($document->file->mime_type)->after('/')->value()) }}</p><h2><a class="content-title-link" href="{{ route('documents.show', $document) }}">{{ $document->localized('title') }}</a></h2>@if ($document->localized('description'))<p>{{ $document->localized('description') }}</p>@endif<p class="resource-meta">{{ __('ui.catalog.version') }} {{ $document->version }} · {{ __('ui.catalog.file_size') }} {{ number_format($document->file->size_bytes / 1048576, 2) }} MB @if ($document->published_at) · {{ __('ui.catalog.published') }} {{ $document->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }} @endif</p><a class="text-link" href="{{ route('media.public', $document->file) }}">{{ __('ui.catalog.download') }} <span aria-hidden="true">↓</span></a></article>@empty<p class="empty-state">{{ __('ui.catalog.empty') }}</p>@endforelse</div>
        {{ $documents->links() }}
    </div>
@endsection
