@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_notice') : __('ui.admin_content.create_notice')])
@section('content')
    <x-admin.back-link :href="route('admin.notices.index')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin_content.notices') }}</p><h1>{{ $isEditing ? __('ui.admin_content.edit_notice') : __('ui.admin_content.create_notice') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.notices.update', $notice) : route('admin.notices.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        @include('admin.shared.bilingual-content-fields', ['item' => $notice])
        <div class="admin-form-grid">
            <div class="form-field"><label for="notice_type">{{ __('ui.admin_content.notice_type') }}</label><input id="notice_type" name="notice_type" value="{{ old('notice_type', $notice->notice_type ?? 'general') }}" maxlength="40" required>@error('notice_type')<p class="field-error">{{ $message }}</p>@enderror</div>
            @can('notices.upload')<x-admin.media-picker name="attachment_media_id" :label="__('ui.admin_content.attachment_media_id')" :selected-id="$notice->attachment_media_id" accept="attachment" />@else<div class="form-field"><label for="attachment_media_id">{{ __('ui.admin_content.attachment_media_id') }}</label><input id="attachment_media_id" type="number" value="{{ $notice->attachment_media_id }}" disabled><input type="hidden" name="attachment_media_id" value="{{ $notice->attachment_media_id }}"></div>@endcan
            <div class="form-field form-field--wide"><label for="external_url">{{ __('ui.admin_content.external_url') }}</label><input id="external_url" name="external_url" type="text" inputmode="url" value="{{ old('external_url', $notice->external_url) }}" maxlength="2048" placeholder="https://">@error('external_url')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label class="remember-field" for="is_pinned"><input id="is_pinned" name="is_pinned" type="checkbox" value="1" @checked(old('is_pinned', $notice->is_pinned ?? false))><span>{{ __('ui.admin_content.pin_notice') }}</span></label></div>
        </div>
        <p id="media-help" class="field-help">{{ __('ui.admin_content.media_help') }}</p>
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.notices.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
