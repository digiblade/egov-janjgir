@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_gallery_album') : __('ui.admin_content.create_gallery_album')])

@section('content')
    <x-admin.back-link :href="route('admin.gallery.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.gallery.index') }}">{{ __('ui.admin_content.gallery_albums') }}</a></p><h1>{{ $isEditing ? __('ui.admin_content.edit_gallery_album') : __('ui.admin_content.create_gallery_album') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.gallery.update', $album) : route('admin.gallery.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field"><label for="title_en">{{ __('ui.admin_content.title_en') }}</label><input id="title_en" name="title_en" value="{{ old('title_en', $album->title_en) }}" required maxlength="255">@error('title_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="title_hi">{{ __('ui.admin_content.title_hi') }}</label><input id="title_hi" name="title_hi" value="{{ old('title_hi', $album->title_hi) }}" required maxlength="255" lang="hi">@error('title_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="slug">{{ __('ui.admin_content.slug') }}</label><input id="slug" name="slug" value="{{ old('slug', $album->slug) }}" maxlength="180" pattern="[A-Za-z0-9_-]*">@error('slug')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="gallery_category_id">{{ __('ui.admin_content.category') }}</label><select id="gallery_category_id" name="gallery_category_id" required><option value="">{{ __('ui.admin_content.select_category') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('gallery_category_id', $album->gallery_category_id) === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>@error('gallery_category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="event_date">{{ __('ui.admin_content.event_date') }}</label><input id="event_date" name="event_date" type="date" value="{{ old('event_date', $album->event_date?->format('Y-m-d')) }}">@error('event_date')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="display_order">{{ __('ui.admin_content.display_order') }}</label><input id="display_order" name="display_order" type="number" min="0" max="100000" value="{{ old('display_order', $album->display_order ?? 0) }}" required>@error('display_order')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="description_en">{{ __('ui.admin_content.description_en') }}</label><textarea id="description_en" name="description_en" rows="5" maxlength="30000">{{ old('description_en', $album->description_en) }}</textarea>@error('description_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="description_hi">{{ __('ui.admin_content.description_hi') }}</label><textarea id="description_hi" name="description_hi" rows="5" maxlength="30000" lang="hi">{{ old('description_hi', $album->description_hi) }}</textarea>@error('description_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            @can('gallery.upload')
                @if ($album->coverImage && $album->coverImage->visibility !== 'public')<div class="form-field"><p class="field-help">{{ __('ui.admin_content.private_image_retained') }} {{ __('ui.admin_content.media_asset_id') }}: {{ $album->cover_media_id }}</p></div>@endif
                <x-admin.media-picker name="cover_media_id" :label="__('ui.admin_content.cover_media_id')" :selected-id="$album->coverImage?->visibility === 'public' ? $album->cover_media_id : null" accept="image" />
            @else
                <div class="form-field"><label for="cover_media_id">{{ __('ui.admin_content.cover_media_id') }}</label>
                <input id="cover_media_id" type="number" value="{{ $album->cover_media_id }}" disabled></div>
            @endcan
            @include('admin.shared.resource-lifecycle-fields', ['item' => $album, 'feature' => 'gallery'])
            <label class="remember-field" for="is_active">@can('gallery.publish')<input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $album->is_active ?? false))>@else<input id="is_active" type="checkbox" @checked(old('is_active', $album->is_active ?? false)) disabled><input type="hidden" name="is_active" value="{{ old('is_active', $album->is_active ?? false) ? 1 : 0 }}">@endcan<span>{{ __('ui.admin_content.active') }}</span></label>
        </div>
        <p class="field-help">{{ __('ui.admin_content.media_help') }}</p>

        <section class="admin-gallery-images" aria-labelledby="gallery-images-heading">
            <h2 id="gallery-images-heading">{{ __('ui.admin_content.gallery_images') }}</h2>
            @can('gallery.upload')
                <p class="field-help">{{ __('ui.admin_content.gallery_image_help') }}</p>
                <input type="hidden" name="images_present" value="1">
                @php($rowCount = max(6, $images->count() + 3))
                <div class="admin-gallery-image-list" x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
                    @for ($index = 0; $index < $rowCount; $index++)
                        @php($image = $images->get($index))
                        @php($publicAsset = $image?->media?->visibility === 'public')
                        <fieldset class="admin-gallery-image-row" draggable="true" @dragstart="start($event)" @dragend="end()">
                            <legend>{{ __('ui.admin_content.gallery_images') }} {{ $index + 1 }} <span class="sortable-row-controls"><button type="button" class="text-button sortable-grip" aria-label="{{ __('ui.admin_content.drag_to_reorder_item', ['item' => __('ui.admin_content.gallery_images').' '.($index + 1)]) }}">⠿</button><button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_item_up', ['item' => __('ui.admin_content.gallery_images').' '.($index + 1)]) }}" @click="move($event.currentTarget.closest('fieldset'), -1)">↑</button><button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_item_down', ['item' => __('ui.admin_content.gallery_images').' '.($index + 1)]) }}" @click="move($event.currentTarget.closest('fieldset'), 1)">↓</button></span></legend>
                            @if ($image)<input type="hidden" name="images[{{ $index }}][id]" value="{{ $image->id }}">@endif
                            <div class="admin-form-grid">
                                @if ($image && ! $publicAsset)
                                    <div class="form-field"><p class="field-help">{{ __('ui.admin_content.private_image_retained') }} {{ __('ui.admin_content.media_asset_id') }}: {{ $image->media_asset_id }}</p></div>
                                    <x-admin.media-picker :name="'images['.$index.'][media_asset_id]'" :error-name="'images.'.$index.'.media_asset_id'" :old-name="'images.'.$index.'.media_asset_id'" :label="__('ui.admin_content.public_replacement_media_id')" accept="image" />
                                @else
                                    <x-admin.media-picker :name="'images['.$index.'][media_asset_id]'" :error-name="'images.'.$index.'.media_asset_id'" :old-name="'images.'.$index.'.media_asset_id'" :label="__('ui.admin_content.gallery_image_media_id')" :selected-id="$image?->media_asset_id" accept="image" />
                                @endif
                                <div class="form-field"><label for="image-{{ $index }}-order">{{ __('ui.admin_content.gallery_image_order') }}</label><input id="image-{{ $index }}-order" data-display-order name="images[{{ $index }}][display_order]" type="number" min="0" max="100000" value="{{ old("images.{$index}.display_order", $image?->display_order ?? $index) }}"></div>
                                <div class="form-field"><label for="image-{{ $index }}-caption-en">{{ __('ui.admin_content.image_caption_en') }}</label><input id="image-{{ $index }}-caption-en" name="images[{{ $index }}][caption_en]" value="{{ old("images.{$index}.caption_en", $image?->caption_en) }}" maxlength="255"></div>
                                <div class="form-field"><label for="image-{{ $index }}-caption-hi">{{ __('ui.admin_content.image_caption_hi') }}</label><input id="image-{{ $index }}-caption-hi" name="images[{{ $index }}][caption_hi]" value="{{ old("images.{$index}.caption_hi", $image?->caption_hi) }}" maxlength="255" lang="hi"></div>
                                <div class="form-field"><label for="image-{{ $index }}-alt-en">{{ __('ui.admin_content.image_alt_en') }}</label><input id="image-{{ $index }}-alt-en" name="images[{{ $index }}][alt_text_en]" value="{{ old("images.{$index}.alt_text_en", $image?->alt_text_en) }}" maxlength="255"></div>
                                <div class="form-field"><label for="image-{{ $index }}-alt-hi">{{ __('ui.admin_content.image_alt_hi') }}</label><input id="image-{{ $index }}-alt-hi" name="images[{{ $index }}][alt_text_hi]" value="{{ old("images.{$index}.alt_text_hi", $image?->alt_text_hi) }}" maxlength="255" lang="hi"></div>
                                <label class="remember-field" for="image-{{ $index }}-active"><input type="hidden" name="images[{{ $index }}][is_active]" value="0"><input id="image-{{ $index }}-active" name="images[{{ $index }}][is_active]" type="checkbox" value="1" @checked(old("images.{$index}.is_active", $image?->is_active ?? true))><span>{{ __('ui.admin_content.active') }}</span></label>
                                @if ($image)<label class="remember-field" for="image-{{ $index }}-remove"><input type="hidden" name="images[{{ $index }}][remove]" value="0"><input id="image-{{ $index }}-remove" name="images[{{ $index }}][remove]" type="checkbox" value="1"><span>{{ __('ui.admin_content.remove_image') }}</span></label>@endif
                            </div>
                        </fieldset>
                    @endfor
                </div>
            @else
                <p class="field-help">{{ __('ui.admin_content.media_help') }}</p>
                @if ($images->isNotEmpty())<ul class="admin-readonly-image-list">@foreach ($images as $image)<li>{{ __('ui.admin_content.media_asset_id') }} {{ $image->media_asset_id }} — {{ $image->media?->visibility === 'public' ? __('ui.admin_content.public_media') : __('ui.admin_content.private_media') }} @if ($image->localized('caption')) — {{ $image->localized('caption') }}@endif</li>@endforeach</ul>@else<p>{{ __('ui.admin_content.empty') }}</p>@endif
            @endcan
        </section>

        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.gallery.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
