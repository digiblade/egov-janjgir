@props([
    'name',
    'label',
    'selectedId' => null,
    'accept' => 'all',
    'errorName' => null,
    'oldName' => null,
    'allowPrivateSelected' => false,
])

@php
    $allowedTypes = match ($accept) {
        'image' => ['image/jpeg', 'image/png', 'image/webp'],
        'document' => ['application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'attachment' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'video' => ['video/mp4'],
        default => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'video/mp4'],
    };
    $mediaOptions = \App\Models\MediaAsset::query()
        ->where('visibility', 'public')
        ->where('storage_disk', config('portal.media.disk', 'local'))
        ->whereIn('mime_type', $allowedTypes)
        ->latest('id')
        ->limit(100)
        ->get(['id', 'original_filename', 'mime_type', 'size_bytes', 'visibility']);
    if ($selectedId && ! $mediaOptions->contains('id', (int) $selectedId)) {
        $selectedAsset = \App\Models\MediaAsset::query()
            ->whereKey($selectedId)
            ->where('visibility', 'public')
            ->where('storage_disk', config('portal.media.disk', 'local'))
            ->whereIn('mime_type', $allowedTypes)
            ->first(['id', 'original_filename', 'mime_type', 'size_bytes', 'uploaded_by', 'visibility']);
        if ($selectedAsset) {
            $mediaOptions->prepend($selectedAsset);
        }
    }
    if ($allowPrivateSelected && $selectedId && ! $mediaOptions->contains('id', (int) $selectedId)) {
        $privateSelected = \App\Models\MediaAsset::query()
            ->whereKey($selectedId)
            ->where('visibility', 'private')
            ->where('storage_disk', config('portal.media.disk', 'local'))
            ->whereIn('mime_type', $allowedTypes)
            ->first(['id', 'original_filename', 'mime_type', 'size_bytes', 'uploaded_by', 'visibility']);
        if ($privateSelected) {
            $privateSelected->setAttribute('is_private_draft', true);
            if (! auth()->user()?->can('media.view_private') && (int) $privateSelected->uploaded_by !== (int) auth()->id()) {
                $privateSelected->setAttribute('is_hidden_private_draft', true);
                $privateSelected->setAttribute('original_filename', __('ui.media.existing_private_draft'));
                $privateSelected->setAttribute('mime_type', '');
                $privateSelected->setAttribute('size_bytes', 0);
            }
            $mediaOptions->prepend($privateSelected);
        }
    }
    $currentValue = (string) old($oldName ?? $name, $selectedId ?? '');
    $choiceIdPrefix = 'media-choice-'.md5($name);
@endphp

<div class="form-field" x-data="portalMediaPreview" x-init="refresh()">
    <label for="{{ $name }}">{{ $label }}</label>
    @if (auth()->user()?->can('media.view'))
        @if (in_array($accept, ['image', 'attachment'], true))
            @if ($mediaOptions->isNotEmpty())
            <input class="media-picker-search" type="search" x-model="query" autocomplete="off" placeholder="{{ $accept === 'image' ? __('ui.media.search_images') : __('ui.media.search_assets') }}" aria-label="{{ $accept === 'image' ? __('ui.media.search_images') : __('ui.media.search_assets') }}">
            <div class="media-picker-options" role="radiogroup" aria-label="{{ $label }}">
                <label class="media-picker-option" for="{{ $choiceIdPrefix }}-none">
                    <input id="{{ $choiceIdPrefix }}-none" type="radio" name="{{ $name }}" value="" @checked($currentValue === '') @change="refresh()">
                    <span class="media-picker-option__empty">{{ $accept === 'image' ? __('ui.media.no_asset_selected') : __('ui.media.no_attachment_selected') }}</span>
                </label>
                @foreach ($mediaOptions as $asset)
                    @php($isPublicImage = $asset->visibility === 'public' && str_starts_with($asset->mime_type, 'image/'))
                    <label class="media-picker-option" for="{{ $choiceIdPrefix }}-{{ $asset->id }}" data-search="{{ mb_strtolower($asset->original_filename) }}" x-show="matches($el.dataset.search || '')">
                        <input id="{{ $choiceIdPrefix }}-{{ $asset->id }}" type="radio" name="{{ $name }}" value="{{ $asset->id }}" @checked($currentValue === (string) $asset->id) @change="refresh()" @if ($isPublicImage) data-preview-url="{{ route('media.public', $asset) }}" data-preview-alt="{{ $asset->original_filename }}" @endif>
                        @if ($isPublicImage)
                            <img class="media-picker-option__thumbnail" src="{{ route('media.public', $asset) }}" alt="" loading="lazy">
                        @else
                            <span class="media-picker-option__file" aria-hidden="true">{{ strtoupper(pathinfo($asset->original_filename, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                        @endif
                        <span class="media-picker-option__name">
                            @if ($asset->is_hidden_private_draft ?? false)
                                #{{ $asset->id }} — {{ __('ui.media.existing_private_draft') }}
                            @else
                                #{{ $asset->id }} — {{ $asset->original_filename }}
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="field-help" x-show="!hasMatchingAsset()" x-cloak>{{ $accept === 'image' ? __('ui.media.no_matching_images') : __('ui.media.no_matching_assets') }}</p>
            @else
                <p class="field-help">{{ $accept === 'image' ? __('ui.media.no_public_images') : __('ui.media.no_public_attachments') }}</p>
            @endif
        @else
            <select id="{{ $name }}" name="{{ $name }}" @change="refresh()">
                <option value="">{{ __('ui.media.choose_asset') }}</option>
                @foreach ($mediaOptions as $asset)
                    <option value="{{ $asset->id }}" @selected($currentValue === (string) $asset->id)>
                        @if ($asset->is_hidden_private_draft ?? false)
                            #{{ $asset->id }} — {{ __('ui.media.existing_private_draft') }}
                        @else
                            #{{ $asset->id }} — {{ $asset->original_filename }} ({{ $asset->mime_type }}, {{ number_format($asset->size_bytes / 1024) }} KB)@if ($asset->is_private_draft ?? false) — {{ __('ui.media.private_draft_asset') }}@endif
                        @endif
                    </option>
                @endforeach
            </select>
        @endif
        @if (in_array($accept, ['image', 'attachment'], true))
            <div class="media-picker-preview" aria-live="polite">
                <img x-show="src" x-bind:src="src" x-bind:alt="alt" loading="lazy" x-cloak>
                <span x-show="!src">{{ __('ui.media.preview_empty') }}</span>
            </div>
        @endif
        <small><a href="{{ route('admin.media.index') }}" target="_blank" rel="noopener">{{ __('ui.media.open_library') }}</a></small>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="number" min="1" value="{{ old($oldName ?? $name, $selectedId) }}" inputmode="numeric">
        <small>{{ __('ui.media.asset_id_hint') }}</small>
    @endif
    @if ($errorName)
        @error($errorName)<p class="field-error">{{ $message }}</p>@enderror
    @else
        @error($name)<p class="field-error">{{ $message }}</p>@enderror
    @endif
</div>
