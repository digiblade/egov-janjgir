@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.contact_details')])

@section('content')
    <x-admin.back-link :href="route('admin.dashboard')" :label="__('ui.admin_content.back_to_admin')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.contact_details_title') }}</h1><p>{{ __('ui.admin_content.contact_details_help') }}</p></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ route('admin.contact.update') }}">
        @csrf @method('PUT')
        <div class="admin-form-grid">
            <div class="form-field"><label for="department_name_en">{{ __('ui.admin_content.department_name_en') }}</label><input id="department_name_en" name="department_name_en" value="{{ old('department_name_en', $contact->department_name_en) }}" maxlength="180">@error('department_name_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="department_name_hi">{{ __('ui.admin_content.department_name_hi') }}</label><input id="department_name_hi" name="department_name_hi" value="{{ old('department_name_hi', $contact->department_name_hi) }}" maxlength="180" lang="hi">@error('department_name_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="office_address_en">{{ __('ui.admin_content.office_address_en') }}</label><textarea id="office_address_en" name="office_address_en" rows="4" maxlength="5000">{{ old('office_address_en', $contact->office_address_en) }}</textarea>@error('office_address_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="office_address_hi">{{ __('ui.admin_content.office_address_hi') }}</label><textarea id="office_address_hi" name="office_address_hi" rows="4" maxlength="5000" lang="hi">{{ old('office_address_hi', $contact->office_address_hi) }}</textarea>@error('office_address_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="support_email">{{ __('ui.admin_content.support_email') }}</label><input id="support_email" name="support_email" type="email" value="{{ old('support_email', $contact->support_email) }}" maxlength="254">@error('support_email')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="contact_number">{{ __('ui.contact.phone') }}</label><input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number', $contact->contact_number) }}" maxlength="40">@error('contact_number')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="office_hours_en">{{ __('ui.admin_content.office_hours_en') }}</label><input id="office_hours_en" name="office_hours_en" value="{{ old('office_hours_en', $contact->office_hours_en) }}" maxlength="500">@error('office_hours_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="office_hours_hi">{{ __('ui.admin_content.office_hours_hi') }}</label><input id="office_hours_hi" name="office_hours_hi" value="{{ old('office_hours_hi', $contact->office_hours_hi) }}" maxlength="500" lang="hi">@error('office_hours_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="map_url">{{ __('ui.admin_content.map_url') }}</label><input id="map_url" name="map_url" inputmode="url" value="{{ old('map_url', $contact->map_url) }}" maxlength="2048">@error('map_url')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
