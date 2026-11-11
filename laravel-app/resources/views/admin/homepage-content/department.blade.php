@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.department_information')])
@section('content')
    <x-admin.back-link :href="route('admin.homepage.index')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin.homepage') }}</p><h1>{{ __('ui.admin_content.department_information') }}</h1><p>{{ __('ui.admin_content.department_information_help') }}</p></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ route('admin.department.update') }}">@csrf @method('PUT')
        <div class="admin-form-grid">
        @foreach (['title_en', 'title_hi', 'description_en', 'description_hi', 'mission_en', 'mission_hi', 'objectives_en', 'objectives_hi'] as $field)
            <div class="form-field {{ str_starts_with($field, 'description') || str_starts_with($field, 'mission') || str_starts_with($field, 'objectives') ? 'form-field--wide' : '' }}"><label for="{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label>@if (str_starts_with($field, 'title'))<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $department->$field) }}" maxlength="255" @required(str_starts_with($field, 'title'))>@else
                <div x-data="portalRichText" class="rich-text-editor">
                    <div class="rich-text-toolbar" role="toolbar" aria-label="{{ __('ui.admin_content.formatting_tools') }}">
                        <button type="button" class="button button--secondary" @click="wrap('**')" aria-label="{{ __('ui.admin_content.bold') }}"><strong>B</strong></button>
                        <button type="button" class="button button--secondary" @click="wrap('*')" aria-label="{{ __('ui.admin_content.italic') }}"><em>I</em></button>
                        <button type="button" class="button button--secondary" @click="prefix('- ')" aria-label="{{ __('ui.admin_content.bullet_list') }}">{{ __('ui.admin_content.bullet_list') }}</button>
                        <button type="button" class="button button--secondary" @click="link()" aria-label="{{ __('ui.admin_content.insert_link') }}">{{ __('ui.admin_content.insert_link') }}</button>
                    </div>
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="6" aria-describedby="{{ $field }}-format-help">{{ old($field, $department->$field) }}</textarea>
                </div>
                <p class="field-help" id="{{ $field }}-format-help">{{ __('ui.admin_content.markdown_help') }}</p>
            @endif @error($field)<p class="field-error">{{ $message }}</p>@enderror</div>
        @endforeach
        @can('media.view')<x-admin.media-picker name="image_media_id" :label="__('ui.admin_content.department_image')" :selected-id="$department->image_media_id" accept="image" />@else<input type="hidden" name="image_media_id" value="{{ $department->image_media_id }}">@endcan
        </div><div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
