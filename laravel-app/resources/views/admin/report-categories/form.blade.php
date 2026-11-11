@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_category') : __('ui.admin_content.create_category')])

@section('content')
    <x-admin.back-link :href="route('admin.report-categories.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.report-categories.index') }}">{{ __('ui.admin_content.report_categories') }}</a></p><h1>{{ $isEditing ? __('ui.admin_content.edit_category') : __('ui.admin_content.create_category') }}</h1></header>
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.report-categories.update', $category) : route('admin.report-categories.store') }}">@csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field"><label for="name_en">{{ __('ui.admin_content.title_en') }}</label><input id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}" required maxlength="255" lang="en">@error('name_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="name_hi">{{ __('ui.admin_content.title_hi') }}</label><input id="name_hi" name="name_hi" value="{{ old('name_hi', $category->name_hi) }}" required maxlength="255" lang="hi">@error('name_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="slug">{{ __('ui.admin_content.slug') }}</label><input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="180" pattern="[A-Za-z0-9_-]*">@error('slug')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="icon">{{ __('ui.admin_content.icon') }}</label><input id="icon" name="icon" value="{{ old('icon', $category->icon) }}" maxlength="100">@error('icon')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="display_order">{{ __('ui.admin_content.display_order') }}</label><input id="display_order" name="display_order" type="number" min="0" max="100000" value="{{ old('display_order', $category->display_order ?? 0) }}" required>@error('display_order')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="description_en">{{ __('ui.admin_content.description_en') }}</label><textarea id="description_en" name="description_en" rows="4" maxlength="10000" lang="en">{{ old('description_en', $category->description_en) }}</textarea>@error('description_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="description_hi">{{ __('ui.admin_content.description_hi') }}</label><textarea id="description_hi" name="description_hi" rows="4" maxlength="10000" lang="hi">{{ old('description_hi', $category->description_hi) }}</textarea>@error('description_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <label class="remember-field form-field--wide" for="is_active"><input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $category->is_active ?? true))><span>{{ __('ui.admin_content.active') }}</span></label>
        </div>
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.report-categories.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
