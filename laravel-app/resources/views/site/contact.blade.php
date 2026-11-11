@extends('layouts.public')

@section('content')
    <section class="page-hero">
        <div class="site-container">
            <p class="eyebrow">{{ __('ui.site.government') }}</p>
            <h1>{{ __('ui.contact.title') }}</h1>
            <p>{{ __('ui.contact.intro') }}</p>
        </div>
    </section>

    <section class="site-container contact-layout section-space" aria-labelledby="contact-heading">
        <div class="contact-card">
            <h2 id="contact-heading">{{ __('ui.contact.office_heading') }}</h2>
            @if ($contact)
                @if ($contact->localized('department_name'))
                    <h3>{{ $contact->localized('department_name') }}</h3>
                @endif
                @if ($contact->localized('office_address'))
                    <p class="contact-card__address">{{ $contact->localized('office_address') }}</p>
                @endif
                @if ($contact->support_email)
                    <p><strong>{{ __('ui.contact.email') }}:</strong> <a href="mailto:{{ $contact->support_email }}">{{ $contact->support_email }}</a></p>
                @endif
                @if ($contact->contact_number)
                    <p><strong>{{ __('ui.contact.phone') }}:</strong> <a href="tel:{{ preg_replace('/[^+0-9]/', '', $contact->contact_number) }}">{{ $contact->contact_number }}</a></p>
                @endif
                @if ($contact->localized('office_hours'))
                    <p><strong>{{ __('ui.contact.hours') }}:</strong> {{ $contact->localized('office_hours') }}</p>
                @endif
                @if ($contact->map_url)
                    <a class="button button--secondary" href="{{ $contact->map_url }}" target="_blank" rel="noopener noreferrer">{{ __('ui.contact.open_map') }}</a>
                @endif
            @else
                <p>{{ __('ui.contact.details_pending') }}</p>
            @endif
        </div>

        <div class="contact-card">
            <h2>{{ __('ui.contact.form_heading') }}</h2>
            <p class="privacy-note">{{ __('ui.contact.privacy_notice') }}</p>
            @if (session('support_reference'))
                <div class="form-success" role="status" tabindex="-1">
                    {{ __('ui.contact.success', ['reference' => session('support_reference')]) }}
                </div>
            @endif
            <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
                @csrf
                <div class="honeypot" aria-hidden="true">
                    <label for="website">{{ __('ui.contact.website') }}</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <label for="full_name">{{ __('ui.contact.full_name') }} <span aria-hidden="true">*</span></label>
                <input id="full_name" name="full_name" value="{{ old('full_name') }}" required maxlength="160" autocomplete="name" @error('full_name') aria-invalid="true" aria-describedby="full-name-error" @enderror>
                @error('full_name')<p class="field-error" id="full-name-error">{{ $message }}</p>@enderror

                <label for="email">{{ __('ui.contact.email') }} <span aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror

                <label for="mobile_number">{{ __('ui.contact.mobile') }}</label>
                <input id="mobile_number" name="mobile_number" type="tel" value="{{ old('mobile_number') }}" maxlength="40" autocomplete="tel">

                <label for="category">{{ __('ui.contact.category') }}</label>
                <select id="category" name="category">
                    <option value="">{{ __('ui.contact.category_optional') }}</option>
                    @foreach (__('ui.contact.categories') as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <label for="subject">{{ __('ui.contact.subject') }} <span aria-hidden="true">*</span></label>
                <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="200" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                @error('subject')<p class="field-error" id="subject-error">{{ $message }}</p>@enderror

                <label for="message">{{ __('ui.contact.message') }} <span aria-hidden="true">*</span></label>
                <textarea id="message" name="message" rows="6" required maxlength="10000" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                @error('message')<p class="field-error" id="message-error">{{ $message }}</p>@enderror

                <button class="button button--primary" type="submit">{{ __('ui.contact.submit') }}</button>
            </form>
        </div>
    </section>
@endsection
