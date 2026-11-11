@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ $trainingResource->category->localized('name') }}</p><h1>{{ $trainingResource->localized('title') }}</h1></div></section>
    <article class="site-container content-page detail-page">@if ($trainingResource->localized('description'))<div class="detail-copy">{{ $trainingResource->localized('description') }}</div>@endif
        <dl class="detail-meta"><div><dt>{{ __('ui.catalog.resource_type') }}</dt><dd>{{ str($trainingResource->resource_type)->replace('_', ' ')->title() }}</dd></div>@if ($trainingResource->training_date)<div><dt>{{ __('ui.catalog.training_date') }}</dt><dd>{{ $trainingResource->training_date->locale(app()->getLocale())->translatedFormat('d M Y') }}</dd></div>@endif @if ($trainingResource->duration_seconds)<div><dt>{{ __('ui.catalog.duration') }}</dt><dd>{{ gmdate('H:i:s', $trainingResource->duration_seconds) }}</dd></div>@endif</dl>
        @if ($videoEmbedUrl)
            <div class="video-frame"><iframe src="{{ $videoEmbedUrl }}" title="{{ $trainingResource->localized('title') }}" loading="lazy" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
        @elseif ($trainingResource->file?->visibility === 'public' && $trainingResource->file->mime_type === 'video/mp4')
            <video class="training-video" controls preload="metadata" playsinline aria-label="{{ $trainingResource->localized('title') }}"><source src="{{ route('media.public', $trainingResource->file) }}" type="video/mp4">{{ __('ui.catalog.video_unsupported') }}</video>
        @endif
        @if ($trainingResource->file?->visibility === 'public')<a class="button button--primary" href="{{ route('media.public', $trainingResource->file) }}">{{ __('ui.catalog.download') }}</a>@elseif ($externalUrl)<a class="button button--primary" href="{{ $externalUrl }}" target="_blank" rel="noopener noreferrer">{{ __('ui.catalog.open_resource') }} <span aria-hidden="true">↗</span></a>@endif
        <p><a class="text-link" href="{{ route('training.index') }}">← {{ __('ui.catalog.back') }}</a></p>
    </article>
@endsection
