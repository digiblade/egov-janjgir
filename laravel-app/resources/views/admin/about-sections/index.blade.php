@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.about_sections')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.about_sections') }}</h1><p>{{ __('ui.admin_content.about_sections_help') }}</p></div>
        @can('about.create')<a class="button button--primary" href="{{ route('admin.about-sections.create') }}">{{ __('ui.admin_content.create_about_section') }}</a>@endcan
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="about-sections" permission="about.edit" />
    <form class="admin-filter" method="GET" action="{{ route('admin.about-sections.index') }}">
        <label for="about-q">{{ __('ui.admin_content.search') }}</label><input id="about-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="about-status">{{ __('ui.admin_content.status') }}</label><select id="about-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (\App\Models\AboutSection::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table">
        <caption class="sr-only">{{ __('ui.admin_content.about_sections') }}</caption>
        <thead><tr><th scope="col">{{ __('ui.admin_content.section_title') }}</th><th scope="col">{{ __('ui.admin_content.display_order') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead>
        <tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
            @forelse ($sections as $section)
                <tr @can('about.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan>
                    <td><x-admin.reorder-control scope="about-sections" permission="about.edit" :id="$section->id" :label="$section->localized('title')" /><strong>{{ $section->localized('title') }}</strong><small>{{ $section->title_en }} · {{ $section->title_hi }}</small></td>
                    <td>{{ $section->display_order }}</td><td>{{ __('ui.admin_content.statuses.'.$section->status) }}</td><td><x-admin.active-status :active="$section->isPubliclyVisibleNow()" /></td><td>{{ $section->updated_at?->format('d M Y H:i') }}</td>
                    <td class="admin-row-actions">@can('about.edit')<a href="{{ route('admin.about-sections.edit', $section) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('about.delete')<form method="POST" action="{{ route('admin.about-sections.destroy', $section) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('ui.admin_content.about_sections_empty') }}</td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="admin-pagination">{{ $sections->links() }}</div>
@endsection
