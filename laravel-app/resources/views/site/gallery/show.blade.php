@extends('layouts.public')

@section('content')
    @php($galleryImages = $galleryAlbum->visibleImages->filter(fn ($image) => $image->media?->visibility === 'public')->values())

    <section class="page-hero page-hero--small">
        <div class="site-container">
            <p class="eyebrow">{{ $galleryAlbum->category->localized('name') }}</p>
            <h1>{{ $galleryAlbum->localized('title') }}</h1>
            @if ($galleryAlbum->event_date)<p>{{ __('ui.catalog.event_date') }}: {{ $galleryAlbum->event_date->locale(app()->getLocale())->translatedFormat('d M Y') }}</p>@endif
        </div>
    </section>

    <div class="site-container content-page" x-data="galleryLightbox" data-image-count="{{ $galleryImages->count() }}">
        @if ($galleryAlbum->localized('description'))<p class="detail-copy">{{ $galleryAlbum->localized('description') }}</p>@endif

        <div class="gallery-grid">
            @forelse ($galleryImages as $index => $image)
                <figure class="gallery-photo">
                    <button class="gallery-photo__trigger" type="button" aria-controls="gallery-lightbox" aria-label="{{ __('ui.accessibility.open_gallery_image', ['number' => $index + 1]) }}" @click="open({{ $index }}, $event)">
                        <img src="{{ route('media.public', $image->media) }}" alt="{{ $image->localized('alt_text') ?: $image->localized('caption') ?: '' }}" loading="lazy">
                    </button>
                    @if ($image->localized('caption'))<figcaption>{{ $image->localized('caption') }}</figcaption>@endif
                </figure>
            @empty
                <p class="empty-state">{{ __('ui.catalog.empty') }}</p>
            @endforelse
        </div>

        @if ($galleryImages->isNotEmpty())
            <dialog id="gallery-lightbox" class="gallery-lightbox" x-ref="dialog" aria-modal="true" aria-labelledby="gallery-lightbox-title" @cancel.prevent="close()" @close="restoreFocus()" @keydown.left.prevent="previous()" @keydown.right.prevent="next()" @click.self="close()">
                <div class="gallery-lightbox__panel">
                    <h2 class="sr-only" id="gallery-lightbox-title">{{ __('ui.accessibility.gallery_lightbox_title', ['album' => $galleryAlbum->localized('title')]) }}</h2>
                    <div class="gallery-lightbox__toolbar">
                        <p class="sr-only" aria-live="polite"><span x-text="active + 1"></span> {{ __('ui.accessibility.of') }} {{ $galleryImages->count() }}</p>
                        @if ($galleryImages->count() > 1)
                            <button class="icon-button" type="button" aria-label="{{ __('ui.accessibility.previous_image') }}" @click="previous()"><span aria-hidden="true">←</span></button>
                        @endif
                        <button class="icon-button" type="button" x-ref="closeButton" aria-label="{{ __('ui.accessibility.close_lightbox') }}" @click="close()"><span aria-hidden="true">×</span></button>
                        @if ($galleryImages->count() > 1)
                            <button class="icon-button" type="button" aria-label="{{ __('ui.accessibility.next_image') }}" @click="next()"><span aria-hidden="true">→</span></button>
                        @endif
                    </div>
                    @foreach ($galleryImages as $index => $image)
                        <figure class="gallery-lightbox__image" x-show="active === {{ $index }}" @if ($index > 0) x-cloak @endif>
                            <img src="{{ route('media.public', $image->media) }}" alt="{{ $image->localized('alt_text') ?: $image->localized('caption') ?: '' }}">
                            @if ($image->localized('caption'))<figcaption>{{ $image->localized('caption') }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            </dialog>
        @endif

        <p><a class="text-link" href="{{ route('gallery.index') }}">← {{ __('ui.catalog.back') }}</a></p>
    </div>
@endsection
