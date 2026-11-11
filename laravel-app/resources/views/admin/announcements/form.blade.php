@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_announcement') : __('ui.admin_content.create_announcement')])
@section('content')
    <x-admin.back-link :href="route('admin.announcements.index')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin_content.announcements') }}</p><h1>{{ $isEditing ? __('ui.admin_content.edit_announcement') : __('ui.admin_content.create_announcement') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        @include('admin.shared.bilingual-content-fields', ['item' => $announcement])
        <div class="admin-form-grid">
            @can('announcements.upload')<x-admin.media-picker name="featured_image_media_id" :label="__('ui.admin_content.featured_image_media_id')" :selected-id="$announcement->featured_image_media_id" accept="image" /><x-admin.media-picker name="attachment_media_id" :label="__('ui.admin_content.attachment_media_id')" :selected-id="$announcement->attachment_media_id" accept="document" />@else<div class="form-field"><label for="featured_image_media_id">{{ __('ui.admin_content.featured_image_media_id') }}</label><input id="featured_image_media_id" type="number" value="{{ $announcement->featured_image_media_id }}" disabled><input type="hidden" name="featured_image_media_id" value="{{ $announcement->featured_image_media_id }}"></div><div class="form-field"><label for="attachment_media_id">{{ __('ui.admin_content.attachment_media_id') }}</label><input id="attachment_media_id" type="number" value="{{ $announcement->attachment_media_id }}" disabled><input type="hidden" name="attachment_media_id" value="{{ $announcement->attachment_media_id }}"></div>@endcan
        </div>
        <p class="field-help">{{ __('ui.admin_content.media_help') }}</p>
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.announcements.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
