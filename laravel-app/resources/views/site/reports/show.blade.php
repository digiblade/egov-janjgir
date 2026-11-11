@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ $report->category->localized('name') }}</p><h1>{{ $report->localized('title') }}</h1></div></section>
    <article class="site-container content-page detail-page">@if ($report->localized('description'))<div class="detail-copy">{{ $report->localized('description') }}</div>@endif
        <dl class="detail-meta">@if ($report->report_period)<div><dt>{{ __('ui.catalog.period') }}</dt><dd>{{ $report->report_period }}</dd></div>@endif @if ($report->report_date)<div><dt>{{ __('ui.admin_content.report_date') }}</dt><dd>{{ $report->report_date->locale(app()->getLocale())->translatedFormat('d M Y') }}</dd></div>@endif @if ($report->office_block)<div><dt>{{ __('ui.catalog.office') }}</dt><dd>{{ $report->office_block }}</dd></div>@endif @if ($report->issuing_authority)<div><dt>{{ __('ui.catalog.authority') }}</dt><dd>{{ $report->issuing_authority }}</dd></div>@endif @if ($report->published_at)<div><dt>{{ __('ui.catalog.published') }}</dt><dd>{{ $report->published_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</dd></div>@endif</dl>
        @if ($report->file->mime_type === 'application/pdf')
            <section class="pdf-preview" aria-labelledby="report-preview-heading">
                <h2 id="report-preview-heading">{{ __('ui.catalog.preview') }}</h2>
                <iframe src="{{ route('media.public', $report->file) }}?preview=1" title="{{ __('ui.catalog.pdf_preview_title', ['title' => $report->localized('title')]) }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
                <p>{{ __('ui.catalog.pdf_preview_unavailable') }} <a href="{{ route('media.public', $report->file) }}">{{ __('ui.catalog.download') }}</a></p>
            </section>
        @endif
        <a class="button button--primary" href="{{ route('media.public', $report->file) }}">{{ __('ui.catalog.download') }}</a><p><a class="text-link" href="{{ route('reports.index') }}">← {{ __('ui.catalog.back') }}</a></p>
    </article>
@endsection
