@extends('layouts.admin', ['pageTitle' => $isEditing ? __('ui.admin_content.edit_faq') : __('ui.admin_content.create_faq')])

@section('content')
    <x-admin.back-link :href="route('admin.faqs.index')" />
    <header class="admin-page-heading"><p class="eyebrow"><a href="{{ route('admin.faqs.index') }}">{{ __('ui.admin_content.faqs') }}</a></p><h1>{{ $isEditing ? __('ui.admin_content.edit_faq') : __('ui.admin_content.create_faq') }}</h1></header>
    @if ($errors->any())<div class="form-error-summary" role="alert" tabindex="-1"><h2>{{ __('ui.admin.login_errors') }}</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="admin-form-card" method="POST" action="{{ $isEditing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">@csrf @if ($isEditing) @method('PUT') @endif
        <div class="admin-form-grid">
            <div class="form-field"><label for="category_id">{{ __('ui.admin_content.category') }}</label><select id="category_id" name="category_id" required><option value="">{{ __('ui.admin_content.select_category') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $faq->category_id) === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>@error('category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="display_order">{{ __('ui.admin_content.display_order') }}</label><input id="display_order" name="display_order" type="number" min="0" max="100000" value="{{ old('display_order', $faq->display_order ?? 0) }}" required>@error('display_order')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="question_en">{{ __('ui.admin_content.question_en') }}</label><input id="question_en" name="question_en" value="{{ old('question_en', $faq->question_en) }}" maxlength="500" lang="en" required>@error('question_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="question_hi">{{ __('ui.admin_content.question_hi') }}</label><input id="question_hi" name="question_hi" value="{{ old('question_hi', $faq->question_hi) }}" maxlength="500" lang="hi" required>@error('question_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="answer_en">{{ __('ui.admin_content.answer_en') }}</label><textarea id="answer_en" name="answer_en" rows="7" maxlength="50000" lang="en" required>{{ old('answer_en', $faq->answer_en) }}</textarea>@error('answer_en')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field form-field--wide"><label for="answer_hi">{{ __('ui.admin_content.answer_hi') }}</label><textarea id="answer_hi" name="answer_hi" rows="7" maxlength="50000" lang="hi" required>{{ old('answer_hi', $faq->answer_hi) }}</textarea>@error('answer_hi')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        @include('admin.shared.resource-lifecycle-fields', ['item' => $faq, 'feature' => 'faqs'])
        <div class="admin-form-actions"><a class="button button--secondary" href="{{ route('admin.faqs.index') }}">{{ __('ui.admin_content.cancel') }}</a><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
