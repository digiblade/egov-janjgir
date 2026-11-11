@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.faqs')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.faqs') }}</h1></div>
        <div class="admin-heading-actions">
            @can('faqs.create')<a class="button button--secondary" href="{{ route('admin.faq-categories.index') }}">{{ __('ui.admin_content.faq_categories') }}</a>@endcan
            @can('faqs.create')<a class="button button--primary" href="{{ route('admin.faqs.create') }}">{{ __('ui.admin_content.create_faq') }}</a>@endcan
        </div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="faqs" permission="faqs.edit" />
    <form class="admin-filter-form" method="GET" action="{{ route('admin.faqs.index') }}">
        <label for="faq-q">{{ __('ui.admin_content.search') }}</label><input id="faq-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="faq-category">{{ __('ui.admin_content.category') }}</label><select id="faq-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>
        <label for="faq-status">{{ __('ui.admin_content.status') }}</label><select id="faq-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (\App\Models\Faq::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.faqs') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.question_en') }}</th><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($faqs as $faq)<tr @can('faqs.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="faqs" permission="faqs.edit" :id="$faq->id" :label="$faq->localized('question')" /><strong>{{ $faq->localized('question') }}</strong></td><td>{{ $faq->category?->localized('name') }}</td><td>{{ __('ui.admin_content.statuses.'.$faq->status) }}</td><td><x-admin.active-status :active="$faq->isPubliclyVisibleNow()" /></td><td>{{ $faq->updated_at?->format('d M Y H:i') }}</td><td class="admin-row-actions">@can('faqs.edit')<a href="{{ route('admin.faqs.edit', $faq) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('faqs.delete')<form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>@empty<tr><td colspan="6">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $faqs->links() }}</div>
@endsection
