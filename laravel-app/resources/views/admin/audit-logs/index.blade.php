@extends('layouts.admin', ['pageTitle' => __('admin.audit_logs.title')])

@section('content')
    <header class="admin-page-heading"><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('admin.audit_logs.title') }}</h1></header>
    <form class="admin-filter" method="GET" action="{{ route('admin.audit-logs.index') }}">
        <label for="audit-module">{{ __('admin.audit_logs.module') }}</label><select id="audit-module" name="module"><option value="">{{ __('admin.audit_logs.all_modules') }}</option>@foreach ($modules as $module)<option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ __('admin.audit_logs.modules.'.$module) }}</option>@endforeach</select>
        <label for="audit-action">{{ __('admin.audit_logs.action') }}</label><select id="audit-action" name="action"><option value="">{{ __('admin.audit_logs.all_actions') }}</option>@foreach ($actions as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>@endforeach</select>
        <label for="audit-type">{{ __('admin.audit_logs.subject_type') }}</label><select id="audit-type" name="subject_type"><option value="">{{ __('admin.audit_logs.all_types') }}</option>@foreach ($subjectTypes as $type)<option value="{{ $type }}" @selected(($filters['subject_type'] ?? '') === $type)>{{ class_basename($type) }}</option>@endforeach</select>
        <label for="audit-subject">{{ __('admin.audit_logs.subject_id') }}</label><input id="audit-subject" name="subject_id" value="{{ $filters['subject_id'] ?? '' }}" maxlength="64">
        <label for="audit-actor">{{ __('admin.audit_logs.actor_id') }}</label><input id="audit-actor" name="actor_id" type="number" min="1" value="{{ $filters['actor_id'] ?? '' }}">
        <label for="audit-from">{{ __('admin.audit_logs.from') }}</label><input id="audit-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        <label for="audit-to">{{ __('admin.audit_logs.to') }}</label><input id="audit-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        <button class="button button--secondary" type="submit">{{ __('admin.audit_logs.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('admin.audit_logs.title') }}</caption><thead><tr><th scope="col">{{ __('admin.audit_logs.when') }}</th><th scope="col">{{ __('admin.audit_logs.actor') }}</th><th scope="col">{{ __('admin.audit_logs.module') }}</th><th scope="col">{{ __('admin.audit_logs.action') }}</th><th scope="col">{{ __('admin.audit_logs.subject') }}</th><th scope="col">{{ __('admin.audit_logs.changed_fields') }}</th></tr></thead><tbody>
        @forelse ($logs as $log)
            <tr><td>{{ $log->occurred_at->timezone(config('app.timezone'))->format('d M Y H:i:s') }}</td><td>{{ $log->actor?->name ?? __('admin.audit_logs.system_or_removed') }}</td><td>{{ __('admin.audit_logs.modules.'.$log->module) }}</td><td><code>{{ $log->action }}</code></td><td>{{ $log->subject_type ? class_basename($log->subject_type) : '—' }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</td><td>{{ implode(', ', $log->changed_fields ?? []) ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="6">{{ __('admin.audit_logs.empty') }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $logs->links() }}</div>
@endsection
