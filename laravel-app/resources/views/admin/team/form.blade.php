@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_team_member') : __('ui.admin_content.create_team_member')])

@section('content')
    <x-admin.back-link :href="route('admin.team.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.team.index') }}">{{ __('ui.admin_content.team') }}</a></p><h1>{{ $isEditing ? __('ui.admin_content.edit_team_member') : __('ui.admin_content.create_team_member') }}</h1></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.team.update', $member) : route('admin.team.store') }}">@csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field"><label for="full_name_en">{{ __('ui.admin_content.full_name_en') }}</label><input id="full_name_en" name="full_name_en" value="{{ old('full_name_en', $member->full_name_en) }}" required maxlength="255">@error('full_name_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="full_name_hi">{{ __('ui.admin_content.full_name_hi') }}</label><input id="full_name_hi" name="full_name_hi" value="{{ old('full_name_hi', $member->full_name_hi) }}" required maxlength="255" lang="hi">@error('full_name_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="designation_en">{{ __('ui.admin_content.designation_en') }}</label><input id="designation_en" name="designation_en" value="{{ old('designation_en', $member->designation_en) }}" required maxlength="255">@error('designation_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="designation_hi">{{ __('ui.admin_content.designation_hi') }}</label><input id="designation_hi" name="designation_hi" value="{{ old('designation_hi', $member->designation_hi) }}" required maxlength="255" lang="hi">@error('designation_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="team_category_id">{{ __('ui.admin_content.team_category') }}</label><select id="team_category_id" name="team_category_id" required><option value="">{{ __('ui.admin_content.select_category') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('team_category_id', $member->team_category_id) === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>@error('team_category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="slug">{{ __('ui.admin_content.slug') }}</label><input id="slug" name="slug" value="{{ old('slug', $member->slug) }}" maxlength="180" pattern="[A-Za-z0-9_-]*">@error('slug')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="registration_number">{{ __('ui.admin_content.registration_number') }}</label><input id="registration_number" name="registration_number" value="{{ old('registration_number', $member->registration_number) }}" maxlength="120">@error('registration_number')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="office_block">{{ __('ui.admin_content.office_block') }}</label><input id="office_block" name="office_block" value="{{ old('office_block', $member->office_block) }}" maxlength="120">@error('office_block')<p class="field-error">{{ $message }}</p>@enderror</div>
            @can('team.upload')<x-admin.media-picker name="photograph_media_id" :label="__('ui.admin_content.photo_media_id')" :selected-id="$member->photograph_media_id" accept="image" />@else<div class="form-field"><label for="photograph_media_id">{{ __('ui.admin_content.photo_media_id') }}</label><input id="photograph_media_id" type="number" value="{{ $member->photograph_media_id }}" disabled><input type="hidden" name="photograph_media_id" value="{{ $member->photograph_media_id }}"></div>@endcan
            @include('admin.shared.resource-lifecycle-fields', ['item' => $member, 'feature' => 'team'])
            <label class="remember-field form-field--wide" for="is_active"><input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $member->is_active ?? true))><span>{{ __('ui.admin_content.active') }}</span></label>
            <div class="form-field form-field--wide"><label for="responsibilities_en">{{ __('ui.admin_content.responsibilities_en') }}</label><textarea id="responsibilities_en" name="responsibilities_en" rows="5" maxlength="20000">{{ old('responsibilities_en', $member->responsibilities_en) }}</textarea>@error('responsibilities_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="responsibilities_hi">{{ __('ui.admin_content.responsibilities_hi') }}</label><textarea id="responsibilities_hi" name="responsibilities_hi" rows="5" maxlength="20000" lang="hi">{{ old('responsibilities_hi', $member->responsibilities_hi) }}</textarea>@error('responsibilities_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        <p class="field-help">{{ __('ui.admin_content.media_help') }}</p>

        <details class="admin-sensitive-fields">
            <summary>{{ __('ui.admin_content.staff_contact_fields') }}</summary>
            <p class="field-help">{{ __('ui.admin_content.staff_contact_help') }}</p>
            <div class="admin-form-grid">
                <div class="form-field"><label for="email">{{ __('ui.admin_content.contact_email') }}</label><input id="email" name="email" type="email" value="{{ old('email', $member->email) }}" maxlength="254" autocomplete="off">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="contact_number">{{ __('ui.admin_content.contact_number') }}</label><input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number', $member->contact_number) }}" maxlength="32" autocomplete="off">@error('contact_number')<p class="field-error">{{ $message }}</p>@enderror</div>
                @can('team.publish')
                    <label class="remember-field" for="email_public"><input id="email_public" name="email_public" type="checkbox" value="1" @checked(old('email_public', $member->email_public ?? false))><span>{{ __('ui.admin_content.email_public') }}</span></label>
                    <label class="remember-field" for="contact_number_public"><input id="contact_number_public" name="contact_number_public" type="checkbox" value="1" @checked(old('contact_number_public', $member->contact_number_public ?? false))><span>{{ __('ui.admin_content.contact_number_public') }}</span></label>
                @else
                    <input type="hidden" name="email_public" value="{{ (int) ($member->email_public ?? false) }}">
                    <input type="hidden" name="contact_number_public" value="{{ (int) ($member->contact_number_public ?? false) }}">
                    <p class="field-help form-field--wide">{{ __('ui.admin_content.contact_public_permission_required') }}</p>
                @endcan
            </div>
        </details>

        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.team.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
