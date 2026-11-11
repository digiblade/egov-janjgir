@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_about_section') : __('ui.admin_content.create_about_section')])

@section('content')
    <x-admin.back-link :href="route('admin.about-sections.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.about-sections.index') }}">{{ __('ui.admin_content.about_sections') }}</a></p><h1>{{ $isEditing ? __('ui.admin_content.edit_about_section') : __('ui.admin_content.create_about_section') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.about-sections.update', $section) : route('admin.about-sections.store') }}">
        @csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field form-field--wide"><label for="title_en">{{ __('ui.admin_content.title_en') }}</label><input id="title_en" name="title_en" value="{{ old('title_en', $section->title_en) }}" maxlength="255" lang="en" required>@error('title_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="title_hi">{{ __('ui.admin_content.title_hi') }}</label><input id="title_hi" name="title_hi" value="{{ old('title_hi', $section->title_hi) }}" maxlength="255" lang="hi" required>@error('title_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="content_en">{{ __('ui.admin_content.content_en') }}</label><textarea id="content_en" name="content_en" rows="9" maxlength="50000" lang="en" required>{{ old('content_en', $section->content_en) }}</textarea><p class="field-help">{{ __('ui.admin_content.markdown_help') }}</p>@error('content_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="content_hi">{{ __('ui.admin_content.content_hi') }}</label><textarea id="content_hi" name="content_hi" rows="9" maxlength="50000" lang="hi" required>{{ old('content_hi', $section->content_hi) }}</textarea><p class="field-help">{{ __('ui.admin_content.markdown_help') }}</p>@error('content_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        @include('admin.shared.resource-lifecycle-fields', ['item' => $section, 'feature' => 'about'])
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.about-sections.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
