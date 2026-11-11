@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.support_detail_title')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow"><a href="{{ route('admin.support.index') }}">{{ __('ui.admin_content.support_title') }}</a></p><h1>{{ $supportRequest->subject }}</h1><p>{{ $supportRequest->reference_number }}</p></div>
        <div class="admin-heading-actions">
            <a class="button button--secondary" href="{{ route('admin.support.index') }}">{{ __('ui.admin_content.cancel') }}</a>
            @if (! $supportRequest->archived_at)
                <form method="POST" action="{{ route('admin.support.archive', $supportRequest) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('PUT')<button class="button button--secondary" type="submit">{{ __('ui.admin_content.support_archive') }}</button></form>
            @endif
        </div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <div class="support-detail-grid">
        <section class="admin-form-card" aria-labelledby="support-message-heading">
            <h2 id="support-message-heading">{{ __('ui.admin_content.support_message') }}</h2>
            <dl class="detail-meta"><div><dt>{{ __('ui.admin_content.support_requester') }}</dt><dd>{{ $supportRequest->full_name }}</dd></div><div><dt>{{ __('ui.contact.email') }}</dt><dd><a href="mailto:{{ $supportRequest->email }}">{{ $supportRequest->email }}</a></dd></div>@if ($supportRequest->mobile_number)<div><dt>{{ __('ui.contact.phone') }}</dt><dd>{{ $supportRequest->mobile_number }}</dd></div>@endif<div><dt>{{ __('ui.admin_content.support_received') }}</dt><dd>{{ $supportRequest->submitted_at?->format('d M Y H:i') }}</dd></div>@if ($supportRequest->category)<div><dt>{{ __('ui.admin_content.category') }}</dt><dd>{{ $supportRequest->category }}</dd></div>@endif</dl>
            <div class="support-message">{{ $supportRequest->message }}</div>
        </section>
        @can('support.edit')
            <form class="admin-form-card" method="POST" action="{{ route('admin.support.update', $supportRequest) }}">
                @csrf @method('PUT')
                <h2>{{ __('ui.admin_content.support_update') }}</h2>
                <div class="form-field"><label for="status">{{ __('ui.admin_content.status') }}</label><select id="status" name="status" required>@foreach (\App\Models\SupportRequest::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $supportRequest->status) === $status)>{{ __('ui.admin_content.support_statuses.'.$status) }}</option>@endforeach</select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
            </form>
        @endcan
        @can('support.assign')
            <form class="admin-form-card" method="POST" action="{{ route('admin.support.assign', $supportRequest) }}">
                @csrf @method('PUT')
                <h2>{{ __('ui.admin_content.support_assignee') }}</h2>
                <div class="form-field"><label for="assigned_to">{{ __('ui.admin_content.support_assignee') }}</label><select id="assigned_to" name="assigned_to"><option value="">{{ __('ui.admin_content.support_unassigned') }}</option>@foreach ($assignees as $assignee)<option value="{{ $assignee->id }}" @selected((string) old('assigned_to', $supportRequest->assigned_to) === (string) $assignee->id)>{{ $assignee->name }}</option>@endforeach</select>@error('assigned_to')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
            </form>
        @endcan
    </div>
    <section class="admin-history" aria-labelledby="support-notes-heading">
        <h2 id="support-notes-heading">{{ __('ui.admin_content.support_notes') }}</h2>
        @forelse ($supportRequest->notes as $note)
            <article class="support-note"><p>{{ $note->note }}</p><small>{{ $note->author?->name ?? __('ui.admin_content.system') }} · {{ $note->created_at?->format('d M Y H:i') }}</small></article>
        @empty
            <p>{{ __('ui.admin_content.support_no_notes') }}</p>
        @endforelse
    </section>
    @can('support.edit')
        <form class="admin-form-card support-note-form" method="POST" action="{{ route('admin.support.notes.store', $supportRequest) }}">
            @csrf
            <h2>{{ __('ui.admin_content.support_add_note') }}</h2>
            <div class="form-field"><label for="note">{{ __('ui.admin_content.support_note') }}</label><textarea id="note" name="note" rows="4" maxlength="10000" required>{{ old('note') }}</textarea>@error('note')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.support_add_note') }}</button></div>
        </form>
    @endcan
@endsection
