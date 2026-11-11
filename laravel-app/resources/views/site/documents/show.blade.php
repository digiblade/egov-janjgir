@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ $document->category->localized('name') }}</p><h1>{{ $document->localized('title') }}</h1></div></section>
    <article class="site-container content-page detail-page">
        @if ($document->localized('description'))<div class="detail-copy">{{ $document->localized('description') }}</div>@endif
        <dl class="detail-meta"><div><dt>{{ __('ui.catalog.version') }}</dt><dd>{{ $document->version }}</dd></div>@if ($document->reference_number)<div><dt>{{ __('ui.catalog.reference') }}</dt><dd>{{ $document->reference_number }}</dd></div>@endif @if ($document->issuing_authority)<div><dt>{{ __('ui.catalog.authority') }}</dt><dd>{{ $document->issuing_authority }}</dd></div>@endif @if ($document->published_at)<div><dt>{{ __('ui.catalog.published') }}</dt><dd>{{ $document->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</dd></div>@endif</dl>
        @if ($document->file->mime_type === 'application/pdf')
            <section class="pdf-preview" aria-labelledby="document-preview-heading">
                <h2 id="document-preview-heading">{{ __('ui.catalog.preview') }}</h2>
                <iframe src="{{ route('media.public', $document->file) }}?preview=1" title="{{ __('ui.catalog.pdf_preview_title', ['title' => $document->localized('title')]) }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
                <p>{{ __('ui.catalog.pdf_preview_unavailable') }} <a href="{{ route('media.public', $document->file) }}">{{ __('ui.catalog.download') }}</a></p>
            </section>
        @endif
        <a class="button button--primary" href="{{ route('media.public', $document->file) }}">{{ __('ui.catalog.download') }}</a>
        @php($publicVersions = $document->versions->filter(fn ($version) => $version->file && $version->file->visibility === 'public'))
        @if ($publicVersions->isNotEmpty())<section class="detail-section"><h2>{{ __('ui.catalog.previous_versions') }}</h2><ul class="resource-links">@foreach ($publicVersions as $version)<li><a href="{{ route('media.public', $version->file) }}">{{ __('ui.catalog.version') }} {{ $version->version }}</a>@if ($version->change_notes) — {{ $version->change_notes }}@endif</li>@endforeach</ul></section>@endif
        <p><a class="text-link" href="{{ route('documents.index') }}">← {{ __('ui.catalog.back') }}</a></p>
    </article>
@endsection
