@extends('layouts.admin', ['pageTitle' => __('ui.admin.dashboard_title')])

@section('content')
    <header class="admin-page-heading">
        <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
        <h1>{{ __('ui.admin.dashboard_title') }}</h1>
        <p>{{ __('ui.admin.dashboard_welcome', ['name' => auth()->user()->name]) }}</p>
    </header>

    @if ($cards->isNotEmpty())
        <section class="dashboard-grid" aria-label="{{ __('ui.admin.dashboard_metrics') }}">
            @foreach ($cards as $card)
                <article class="dashboard-card">
                    <h2>{{ $card['label'] }}</h2>
                    <p>{{ number_format($card['count']) }}</p>
                </article>
            @endforeach
        </section>
    @endif

    <div class="dashboard-columns">
        <section class="admin-form-card" aria-labelledby="dashboard-actions-heading">
            <h2 id="dashboard-actions-heading">{{ __('ui.admin.dashboard_quick_actions') }}</h2>
            @if ($quickActions->isNotEmpty())
                <ul class="admin-module-list">
                    @foreach ($quickActions as $module)
                        <li><a href="{{ $module['url'] }}">{{ __('ui.admin_content.'.$module['label']) }} <span aria-hidden="true">→</span></a></li>
                    @endforeach
                </ul>
            @else
                <p>{{ __('ui.admin.no_modules') }}</p>
            @endif
            <a class="button button--secondary" href="{{ route('home') }}">{{ __('ui.site.public_site') }} <span aria-hidden="true">↗</span></a>
        </section>

        @if ($statusSummary !== [])
            <section class="admin-form-card" aria-labelledby="dashboard-status-heading">
                <h2 id="dashboard-status-heading">{{ __('ui.admin.dashboard_status_summary') }}</h2>
                <div class="admin-table-wrap"><table class="admin-table">
                    <caption class="sr-only">{{ __('ui.admin.dashboard_status_summary') }}</caption>
                    <thead><tr><th scope="col">{{ __('ui.admin.dashboard_module') }}</th><th scope="col">{{ __('ui.admin.dashboard_draft') }}</th><th scope="col">{{ __('ui.admin.dashboard_published') }}</th><th scope="col">{{ __('ui.admin.dashboard_scheduled') }}</th><th scope="col">{{ __('ui.admin.dashboard_archived') }}</th></tr></thead>
                    <tbody>
                        @foreach ($statusSummary as $row)
                            <tr><th scope="row">{{ $row['module'] }}</th><td>{{ number_format($row['statuses']['draft'] ?? 0) }}</td><td>{{ number_format($row['statuses']['published'] ?? 0) }}</td><td>{{ number_format($row['statuses']['scheduled'] ?? 0) }}</td><td>{{ number_format($row['statuses']['archived'] ?? 0) }}</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            </section>
        @endif

        @if ($pendingPublication !== [])
            <section class="admin-form-card" aria-labelledby="dashboard-pending-heading">
                <h2 id="dashboard-pending-heading">{{ __('ui.admin.dashboard_pending_publication') }}</h2>
                <ul class="dashboard-list">
                    @foreach ($pendingPublication as $item)
                        <li><span>{{ $item['title'] }}</span><span class="dashboard-list__meta">{{ $item['module'] }} · {{ __('ui.admin.dashboard_statuses.'.$item['status']) }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @can('support.view')
            <section class="admin-form-card" aria-labelledby="dashboard-support-heading">
                <h2 id="dashboard-support-heading">{{ __('ui.admin.dashboard_support_queue') }}</h2>
                <p class="dashboard-highlight">{{ number_format($pendingSupport) }}</p>
                <a class="text-link" href="{{ route('admin.support.index') }}">{{ __('ui.admin.dashboard_open_support') }}</a>
            </section>
        @endcan

        @can('media.view')
            <section class="admin-form-card" aria-labelledby="dashboard-uploads-heading">
                <h2 id="dashboard-uploads-heading">{{ __('ui.admin.dashboard_recent_uploads') }}</h2>
                @if ($recentUploads->isNotEmpty())
                    <ul class="dashboard-list">
                        @foreach ($recentUploads as $asset)
                            <li><span>{{ $asset->original_filename }}</span><span class="dashboard-list__meta">{{ $asset->uploader?->name ?? __('ui.admin.dashboard_unknown_uploader') }} · {{ $asset->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</span></li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ __('ui.admin.dashboard_no_uploads') }}</p>
                @endif
            </section>
        @endcan

        @can('audit-logs.view')
            <section class="admin-form-card" aria-labelledby="dashboard-activity-heading">
                <h2 id="dashboard-activity-heading">{{ __('ui.admin.dashboard_recent_activity') }}</h2>
                @if ($activity->isNotEmpty())
                    <div class="admin-table-wrap"><table class="admin-table">
                        <caption class="sr-only">{{ __('ui.admin.dashboard_recent_activity') }}</caption>
                        <thead><tr><th scope="col">{{ __('ui.admin.dashboard_when') }}</th><th scope="col">{{ __('ui.admin.dashboard_actor') }}</th><th scope="col">{{ __('ui.admin.dashboard_action') }}</th><th scope="col">{{ __('ui.admin.dashboard_record') }}</th></tr></thead>
                        <tbody>@foreach ($activity as $entry)<tr><td>{{ $entry->occurred_at->timezone(config('app.timezone'))->format('d M H:i') }}</td><td>{{ $entry->actor?->name ?? __('ui.admin.dashboard_system') }}</td><td><code>{{ $entry->action }}</code></td><td>{{ $entry->subject_type ? class_basename($entry->subject_type) : '—' }}{{ $entry->subject_id ? ' #'.$entry->subject_id : '' }}</td></tr>@endforeach</tbody>
                    </table></div>
                    <a class="text-link" href="{{ route('admin.audit-logs.index') }}">{{ __('ui.admin.dashboard_view_activity') }}</a>
                @else
                    <p>{{ __('ui.admin.dashboard_no_activity') }}</p>
                @endif
            </section>
        @endcan
    </div>
@endsection
