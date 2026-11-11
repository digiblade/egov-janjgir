@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.reports')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.reports') }}</h1></div>
        <div class="admin-heading-actions">
            @can('reports.create')<a class="button button--secondary" href="{{ route('admin.report-categories.index') }}">{{ __('ui.admin_content.report_categories') }}</a>@endcan
            @can('reports.create')<a class="button button--primary" href="{{ route('admin.reports.create') }}">{{ __('ui.admin_content.create_report') }}</a>@endcan
        </div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="reports" permission="reports.edit" />
    <form class="admin-filter-form" method="GET" action="{{ route('admin.reports.index') }}">
        <label for="report-q">{{ __('ui.admin_content.search') }}</label><input id="report-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="report-category">{{ __('ui.admin_content.category') }}</label><select id="report-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>
        <label for="report-period">{{ __('ui.catalog.period') }}</label><select id="report-period" name="period"><option value="">{{ __('ui.catalog.all_periods') }}</option>@foreach ($periods as $period)<option value="{{ $period }}" @selected(($filters['period'] ?? '') === $period)>{{ $period }}</option>@endforeach</select>
        <label for="report-office">{{ __('ui.admin_content.office_block') }}</label><select id="report-office" name="office"><option value="">{{ __('ui.catalog.all_offices') }}</option>@foreach ($offices as $office)<option value="{{ $office }}" @selected(($filters['office'] ?? '') === $office)>{{ $office }}</option>@endforeach</select>
        <label for="report-from">{{ __('ui.catalog.from') }}</label><input id="report-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        <label for="report-to">{{ __('ui.catalog.to') }}</label><input id="report-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        <label for="report-status">{{ __('ui.admin_content.status') }}</label><select id="report-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (\App\Models\Report::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.reports') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.title') }}</th><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.report_period') }}</th><th scope="col">{{ __('ui.admin_content.report_date') }}</th><th scope="col">{{ __('ui.admin_content.office_block') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($reports as $report)<tr @can('reports.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="reports" permission="reports.edit" :id="$report->id" :label="$report->localized('title')" /><strong>{{ $report->localized('title') }}</strong><small>{{ $report->file?->original_filename }}</small></td><td>{{ $report->category?->localized('name') }}</td><td>{{ $report->report_period }}</td><td>{{ $report->report_date?->format('d M Y') }}</td><td>{{ $report->office_block }}</td><td>{{ __('ui.admin_content.statuses.'.$report->status) }}</td><td><x-admin.active-status :active="$report->isPubliclyVisibleNow()" /></td><td class="admin-row-actions">@can('reports.edit')<a href="{{ route('admin.reports.edit', $report) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('reports.delete')<form method="POST" action="{{ route('admin.reports.destroy', $report) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>@empty<tr><td colspan="8">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $reports->links() }}</div>
@endsection
