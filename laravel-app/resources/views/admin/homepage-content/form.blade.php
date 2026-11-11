@php($label = __('ui.admin_content.'.$type))
@extends('layouts.admin', ['pageTitle' => $editing ? __('ui.admin_content.edit_item', ['item' => $label]) : __('ui.admin_content.create_item')])
@section('content')
    <x-admin.back-link :href="route('admin.'.$type.'.index')" />
    @php($publishPermission = $type === 'banners' ? 'banners.publish' : ($type === 'kpis' ? 'kpis.publish' : 'homepage.publish'))
    <header class="admin-page-heading"><p class="eyebrow">{{ $label }}</p><h1>{{ $editing ? __('ui.admin_content.edit_item', ['item' => $label]) : __('ui.admin_content.create_item') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $editing ? route('admin.'.$type.'.update', $item->id) : route('admin.'.$type.'.store') }}" @if ($type === 'kpis') x-data="portalKpiPreview" x-init="refresh()" @endif>
        @csrf @if ($editing) @method('PUT') @endif
        <div class="admin-form-grid">
            @if ($type === 'banners')
                @foreach (['title_en', 'title_hi', 'subtitle_en', 'subtitle_hi'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label>@if (str_starts_with($field, 'subtitle'))<textarea id="{{ $field }}" name="{{ $field }}" rows="3">{{ old($field, $item->$field) }}</textarea>@else<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item->$field) }}" maxlength="255">@endif @error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                <p class="field-help form-field--wide">{{ __('ui.admin_content.banner_image_help') }}</p>
                @can('banners.upload')<x-admin.media-picker name="desktop_image_media_id" :label="__('ui.admin_content.desktop_image')" :selected-id="$item->desktop_image_media_id" accept="image" />
                    <x-admin.media-picker name="mobile_image_media_id" :label="__('ui.admin_content.mobile_image')" :selected-id="$item->mobile_image_media_id" accept="image" />
                @else<input type="hidden" name="desktop_image_media_id" value="{{ $item->desktop_image_media_id }}"><input type="hidden" name="mobile_image_media_id" value="{{ $item->mobile_image_media_id }}">@endcan
                @if ($item->desktopImage && $item->desktopImage->visibility === 'public')<div class="form-field form-field--wide"><p>{{ __('ui.admin_content.current_banner_preview') }}</p><img src="{{ route('media.public', $item->desktopImage) }}" alt="{{ __('ui.admin_content.current_banner_preview') }}" loading="lazy" style="display:block;width:100%;max-height:20rem;object-fit:cover;border-radius:.5rem"></div>@endif
                @foreach (['cta_text_en', 'cta_text_hi', 'cta_url'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $field === 'cta_url' && ! $item->$field ? '/about' : $item->$field) }}" maxlength="2048">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                <p class="field-help form-field--wide">{{ __('ui.admin_content.banner_about_link_help') }}</p>
                <div class="form-field"><label for="text_alignment">{{ __('ui.admin_content.text_alignment') }}</label><select id="text_alignment" name="text_alignment">@foreach (['left', 'center', 'right'] as $value)<option value="{{ $value }}" @selected(old('text_alignment', $item->text_alignment ?: 'left') === $value)>{{ __('ui.admin_content.alignments.'.$value) }}</option>@endforeach</select>@error('text_alignment')<p class="field-error">{{ $message }}</p>@enderror</div>
                @foreach (['text_color', 'overlay_color'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" type="color" value="{{ old($field, $item->$field ?: ($field === 'text_color' ? '#FFFFFF' : '#000000')) }}">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                <div class="form-field"><label for="overlay_opacity">{{ __('ui.admin_content.overlay_opacity') }}</label><input id="overlay_opacity" name="overlay_opacity" type="number" min="0" max="85" value="{{ old('overlay_opacity', $item->overlay_opacity ?? 35) }}">@error('overlay_opacity')<p class="field-error">{{ $message }}</p>@enderror</div>
                @foreach (['starts_at', 'ends_at'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" type="datetime-local" value="{{ old($field, $item->$field?->format('Y-m-d\\TH:i')) }}">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @include('admin.homepage-content.shared-order-active', ['publishPermission' => $publishPermission])
            @elseif ($type === 'kpis')
                @foreach (['title_en', 'title_hi', 'description_en', 'description_hi', 'value', 'prefix', 'suffix', 'icon'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item->$field) }}" @if ($field === 'value') type="number" step="0.01" min="0" @else maxlength="255" @endif x-on:input="refresh()">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @foreach (['background_color', 'text_color', 'icon_color'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" type="color" value="{{ old($field, $item->$field ?: '#FFFFFF') }}" x-on:input="refresh()">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                <div class="kpi-live-preview form-field--wide">
                    <p class="eyebrow">{{ __('ui.admin_content.preview') }}</p>
                    <p class="field-help">{{ __('ui.admin_content.kpi_contrast_help') }}</p>
                    <article class="kpi-preview-card" x-bind:style="`background-color:${background};color:${textColor}`">
                        <span class="kpi-preview-card__icon" x-bind:style="`color:${iconColor}`" x-text="icon"></span>
                        <p x-text="title"></p><strong><span x-text="prefix"></span><span x-text="value"></span><span x-text="suffix"></span></strong>
                        <small x-text="description"></small>
                    </article>
                </div>
                @include('admin.homepage-content.shared-order-active', ['publishPermission' => $publishPermission])
            @elseif ($type === 'quick-links')
                @foreach (['title_en', 'title_hi'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item->$field) }}" maxlength="255" @if (str_ends_with($field, '_hi')) lang="hi" @endif required>@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @foreach (['description_en', 'description_hi'] as $field)<div class="form-field form-field--wide"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><textarea id="{{ $field }}" name="{{ $field }}" rows="3" maxlength="2000" @if (str_ends_with($field, '_hi')) lang="hi" @endif>{{ old($field, $item->$field) }}</textarea>@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @foreach (['icon', 'url'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item->$field) }}" maxlength="2048">@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @foreach (['is_external', 'opens_in_new_tab'] as $field)<div class="form-field"><label class="remember-field" for="{{ $field }}"><input id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1" @checked(old($field, $item->$field))><span>{{ __('ui.admin_content.'.$field) }}</span></label></div>@endforeach
                @include('admin.homepage-content.shared-order-active', ['publishPermission' => $publishPermission])
            @else
                @foreach (['title_en', 'title_hi', 'summary_en', 'summary_hi', 'url'] as $field)<div class="form-field"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label>@if (str_starts_with($field, 'summary'))<textarea id="{{ $field }}" name="{{ $field }}" rows="3">{{ old($field, $item->$field) }}</textarea>@else<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $item->$field) }}" maxlength="2048">@endif @error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                @php($feature = 'homepage')
                @include('admin.shared.resource-lifecycle-fields', ['item' => $item])
            @endif
        </div>
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.'.$type.'.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
