@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.support_title')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.support_title') }}</h1></div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <form class="admin-filter admin-filter--wrap" method="GET" action="{{ route('admin.support.index') }}">
        <label for="support-q">{{ __('ui.admin_content.search') }}</label><input id="support-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="support-status">{{ __('ui.admin_content.status') }}</label>
        <select id="support-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (\App\Models\SupportRequest::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.support_statuses.'.$status) }}</option>@endforeach</select>
        <label for="support-category">{{ __('ui.admin_content.category') }}</label>
        <select id="support-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select>
        <label for="support-from">{{ __('ui.admin_content.support_from') }}</label><input id="support-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        <label for="support-to">{{ __('ui.admin_content.support_to') }}</label><input id="support-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        @can('support.assign')
            <label for="support-assignee">{{ __('ui.admin_content.support_assignee') }}</label>
            <select id="support-assignee" name="assigned_to"><option value="">{{ __('ui.admin_content.support_all_assignees') }}</option>@foreach ($assignees as $assignee)<option value="{{ $assignee->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $assignee->id)>{{ $assignee->name }}</option>@endforeach</select>
        @endcan
        <label class="remember-field" for="archived"><input id="archived" name="archived" type="checkbox" value="1" @checked(($filters['archived'] ?? '0') === '1')><span>{{ __('ui.admin_content.support_archived_only') }}</span></label>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.support_title') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.support_reference') }}</th><th scope="col">{{ __('ui.admin_content.support_requester') }}</th><th scope="col">{{ __('ui.admin_content.support_subject') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.support_assignee') }}</th><th scope="col">{{ __('ui.admin_content.support_received') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody>
        @forelse ($requests as $supportRequest)
            <tr><td>{{ $supportRequest->reference_number }}@if ($supportRequest->archived_at)<small>{{ __('ui.admin_content.support_filter_archived') }}</small>@endif</td><td><strong>{{ $supportRequest->full_name }}</strong><small>{{ $supportRequest->email }}</small></td><td>{{ $supportRequest->subject }}@if ($supportRequest->category)<small>{{ $supportRequest->category }}</small>@endif</td><td>{{ __('ui.admin_content.support_statuses.'.$supportRequest->status) }}</td><td>{{ $supportRequest->assignee?->name ?? __('ui.admin_content.support_unassigned') }}</td><td>{{ $supportRequest->submitted_at?->format('d M Y H:i') }}</td><td><a href="{{ route('admin.support.show', $supportRequest) }}">{{ __('ui.admin_content.view') }}</a></td></tr>
        @empty
            <tr><td colspan="7">{{ __('ui.admin_content.empty') }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $requests->links() }}</div>
@endsection
