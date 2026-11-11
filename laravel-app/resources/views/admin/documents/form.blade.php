@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_document') : __('ui.admin_content.create_document')])
@section('content')
    <x-admin.back-link :href="route('admin.documents.index')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin_content.documents') }}</p><h1>{{ $isEditing ? __('ui.admin_content.edit_document') : __('ui.admin_content.create_document') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.documents.update', $document) : route('admin.documents.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        @include('admin.shared.resource-content-fields', ['item' => $document])
        <div class="admin-form-grid">
            <div class="form-field"><label for="category_id">{{ __('ui.admin_content.category') }}</label><select id="category_id" name="category_id" required><option value="">{{ __('ui.admin_content.select_category') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $document->category_id) === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>@error('category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            @can('documents.upload')<x-admin.media-picker name="media_asset_id" :label="__('ui.admin_content.file_media_id')" :selected-id="$document->media_asset_id" :allow-private-selected="$document->status === 'draft' && $document->file?->visibility === 'private'" accept="document" />@else<div class="form-field"><label for="media_asset_id">{{ __('ui.admin_content.file_media_id') }}</label><input id="media_asset_id" type="number" value="{{ $document->media_asset_id }}" disabled><input type="hidden" name="media_asset_id" value="{{ $document->media_asset_id }}"></div>@endcan
            @if ($document->status === 'draft' && $document->file?->visibility === 'private')<p class="field-help form-field--wide">{{ __('ui.admin_content.private_bulk_draft_help') }}</p>@endif
            <div class="form-field"><label for="version">{{ __('ui.admin_content.version') }}</label><input id="version" name="version" value="{{ old('version', $document->version ?? '1.0') }}" maxlength="50" required>@error('version')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="reference_number">{{ __('ui.admin_content.reference') }}</label><input id="reference_number" name="reference_number" value="{{ old('reference_number', $document->reference_number) }}" maxlength="150">@error('reference_number')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="issuing_authority">{{ __('ui.admin_content.authority') }}</label><input id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority', $document->issuing_authority) }}" maxlength="255">@error('issuing_authority')<p class="field-error">{{ $message }}</p>@enderror</div>
            @if ($isEditing)<div class="form-field form-field--wide"><label for="version_change_notes">{{ __('ui.admin_content.version_change_notes') }}</label><textarea id="version_change_notes" name="version_change_notes" rows="3" maxlength="2000">{{ old('version_change_notes') }}</textarea><p class="field-help">{{ __('ui.admin_content.version_history_help') }}</p>@error('version_change_notes')<p class="field-error">{{ $message }}</p>@enderror</div>@endif
        </div>
        <p class="field-help">{{ __('ui.admin_content.media_help') }}</p>
        @include('admin.shared.resource-lifecycle-fields', ['item' => $document, 'feature' => 'documents'])
        @if ($isEditing && $document->versions->isNotEmpty())<section class="admin-history"><h2>{{ __('ui.admin_content.previous_versions') }}</h2><ul>@foreach ($document->versions as $version)<li>{{ __('ui.admin_content.version') }} {{ $version->version }} · {{ $version->created_at?->format('d M Y H:i') }} @if ($version->change_notes) — {{ $version->change_notes }} @endif</li>@endforeach</ul></section>@endif
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.documents.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
