@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.site_settings')])

@section('content')
    <x-admin.back-link :href="route('admin.dashboard')" :label="__('ui.admin_content.back_to_admin')" />
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.site_settings') }}</h1><p>{{ __('ui.admin_content.settings_help') }}</p></header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="form-error-summary" role="alert"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <section class="settings-section" aria-labelledby="identity-heading">
            <h2 id="identity-heading">{{ __('ui.admin_content.site_identity') }}</h2>
            <div class="admin-form-grid">
                <div class="form-field"><label for="site_title_en">{{ __('ui.admin_content.site_title_en') }}</label><input id="site_title_en" name="site_title_en" value="{{ old('site_title_en', $values['site.identity']['title_en']) }}" maxlength="180" required>@error('site_title_en')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="site_title_hi">{{ __('ui.admin_content.site_title_hi') }}</label><input id="site_title_hi" name="site_title_hi" value="{{ old('site_title_hi', $values['site.identity']['title_hi']) }}" maxlength="180" lang="hi" required>@error('site_title_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="department_name_en">{{ __('ui.admin_content.department_name_en') }}</label><input id="department_name_en" name="department_name_en" value="{{ old('department_name_en', $values['site.identity']['department_en']) }}" maxlength="180"></div>
                <div class="form-field"><label for="department_name_hi">{{ __('ui.admin_content.department_name_hi') }}</label><input id="department_name_hi" name="department_name_hi" value="{{ old('department_name_hi', $values['site.identity']['department_hi']) }}" maxlength="180" lang="hi"></div>
                <x-admin.media-picker name="logo_media_id" :label="__('ui.admin_content.logo_media_id')" :selected-id="$values['site.identity']['logo_media_id']" accept="image" />
                <x-admin.media-picker name="favicon_media_id" :label="__('ui.admin_content.favicon_media_id')" :selected-id="$values['site.identity']['favicon_media_id']" accept="image" />
            </div>
        </section>
        <section class="settings-section" aria-labelledby="seo-heading">
            <h2 id="seo-heading">{{ __('ui.admin_content.seo_settings') }}</h2><p>{{ __('ui.admin_content.seo_help') }}</p>
            <div class="admin-form-grid">
                <div class="form-field"><label for="seo_title_en">{{ __('ui.admin_content.seo_title_en') }}</label><input id="seo_title_en" name="seo_title_en" value="{{ old('seo_title_en', $values['site.seo']['title_en']) }}" maxlength="70"></div>
                <div class="form-field"><label for="seo_title_hi">{{ __('ui.admin_content.seo_title_hi') }}</label><input id="seo_title_hi" name="seo_title_hi" value="{{ old('seo_title_hi', $values['site.seo']['title_hi']) }}" maxlength="70" lang="hi"></div>
                <div class="form-field"><label for="seo_description_en">{{ __('ui.admin_content.seo_description_en') }}</label><textarea id="seo_description_en" name="seo_description_en" rows="3" maxlength="180">{{ old('seo_description_en', $values['site.seo']['description_en']) }}</textarea></div>
                <div class="form-field"><label for="seo_description_hi">{{ __('ui.admin_content.seo_description_hi') }}</label><textarea id="seo_description_hi" name="seo_description_hi" rows="3" maxlength="180" lang="hi">{{ old('seo_description_hi', $values['site.seo']['description_hi']) }}</textarea></div>
            </div>
        </section>
        <section class="settings-section" aria-labelledby="footer-heading">
            <h2 id="footer-heading">{{ __('ui.admin_content.footer_settings') }}</h2>
            <div class="admin-form-grid">
                <div class="form-field"><label for="footer_note_en">{{ __('ui.admin_content.footer_note_en') }}</label><textarea id="footer_note_en" name="footer_note_en" rows="3" maxlength="1200">{{ old('footer_note_en', $values['site.footer']['note_en']) }}</textarea></div>
                <div class="form-field"><label for="footer_note_hi">{{ __('ui.admin_content.footer_note_hi') }}</label><textarea id="footer_note_hi" name="footer_note_hi" rows="3" maxlength="1200" lang="hi">{{ old('footer_note_hi', $values['site.footer']['note_hi']) }}</textarea></div>
                <div class="form-field"><label for="copyright_en">{{ __('ui.admin_content.copyright_en') }}</label><input id="copyright_en" name="copyright_en" value="{{ old('copyright_en', $values['site.footer']['copyright_en']) }}" maxlength="200"></div>
                <div class="form-field"><label for="copyright_hi">{{ __('ui.admin_content.copyright_hi') }}</label><input id="copyright_hi" name="copyright_hi" value="{{ old('copyright_hi', $values['site.footer']['copyright_hi']) }}" maxlength="200" lang="hi"></div>
                <div class="form-field"><label for="social_facebook">{{ __('ui.admin_content.social_facebook') }}</label><input id="social_facebook" name="social_facebook" inputmode="url" value="{{ old('social_facebook', $values['site.footer']['facebook']) }}" maxlength="2048">@error('social_facebook')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="social_instagram">{{ __('ui.admin_content.social_instagram') }}</label><input id="social_instagram" name="social_instagram" inputmode="url" value="{{ old('social_instagram', $values['site.footer']['instagram']) }}" maxlength="2048">@error('social_instagram')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="social_youtube">{{ __('ui.admin_content.social_youtube') }}</label><input id="social_youtube" name="social_youtube" inputmode="url" value="{{ old('social_youtube', $values['site.footer']['youtube']) }}" maxlength="2048">@error('social_youtube')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="social_x">{{ __('ui.admin_content.social_x') }}</label><input id="social_x" name="social_x" inputmode="url" value="{{ old('social_x', $values['site.footer']['x']) }}" maxlength="2048">@error('social_x')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
        </section>
        <section class="settings-section" aria-labelledby="carousel-heading">
            <h2 id="carousel-heading">{{ __('ui.admin_content.carousel_settings') }}</h2>
            <div class="admin-form-grid">
                <div class="form-field"><label for="default_language">{{ __('ui.admin_content.default_language') }}</label><select id="default_language" name="default_language"><option value="en" @selected(old('default_language', $values['site.carousel']['default_language']) === 'en')>{{ __('ui.language.english') }}</option><option value="hi" @selected(old('default_language', $values['site.carousel']['default_language']) === 'hi')>{{ __('ui.language.hindi') }}</option></select></div>
                <div class="form-field"><label class="remember-field" for="carousel_autoplay"><input id="carousel_autoplay" name="carousel_autoplay" type="checkbox" value="1" @checked(old('carousel_autoplay', $values['site.carousel']['autoplay']))><span>{{ __('ui.admin_content.carousel_autoplay') }}</span></label></div>
                <div class="form-field"><label for="carousel_interval_ms">{{ __('ui.admin_content.carousel_interval_ms') }}</label><input id="carousel_interval_ms" name="carousel_interval_ms" type="number" min="4000" max="20000" step="500" value="{{ old('carousel_interval_ms', $values['site.carousel']['interval_ms']) }}" required>@error('carousel_interval_ms')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
        </section>
        <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
