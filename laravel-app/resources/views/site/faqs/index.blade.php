@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ __('ui.site.featured_resources') }}</p><h1>{{ __('ui.catalog.faqs') }}</h1></div></section>
    <div class="site-container content-page faq-groups">
        <form action="{{ route('faqs.index') }}" method="GET" role="search" class="filter-form">
            <div><label for="faq-q">{{ __('ui.catalog.search') }}</label><input id="faq-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="120"></div>
            <div><label for="faq-category">{{ __('ui.catalog.category') }}</label><select id="faq-category" name="category"><option value="">{{ __('ui.catalog.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->localized('name') }}</option>@endforeach</select></div>
            <button class="button button--primary" type="submit">{{ __('ui.catalog.filter') }}</button><a class="text-link" href="{{ route('faqs.index') }}">{{ __('ui.catalog.clear') }}</a>
        </form>
        @php($hasFaqs = $faqs->isNotEmpty())
        @forelse ($categories as $category)
            @php($categoryFaqs = $faqs->getCollection()->where('category_id', $category->id))
            @if ($categoryFaqs->isNotEmpty())<section class="faq-group"><h2>{{ $category->localized('name') }}</h2><div class="faq-list">@foreach ($categoryFaqs as $faq)<details class="faq-item" id="faq-{{ $faq->id }}"><summary>{{ $faq->localized('question') }}</summary><div class="faq-item__answer">{{ $faq->localized('answer') }}</div></details>@endforeach</div></section>@endif
        @empty
            <p class="empty-state">{{ __('ui.catalog.faqs_empty') }}</p>
        @endforelse
        @if (! $hasFaqs && $categories->isNotEmpty())<p class="empty-state">{{ __('ui.catalog.faqs_empty') }}</p>@endif
        {{ $faqs->links() }}
    </div>
@endsection
