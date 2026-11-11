@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.documents')])
@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.documents') }}</h1></div>
        <div class="admin-heading-actions">@if (auth()->user()->can('documents.create') && auth()->user()->can('documents.upload'))<a class="button button--primary" href="{{ route('admin.documents.create') }}">{{ __('ui.admin_content.create_document') }}</a>@endif @can('documents.view')<a class="button button--secondary" href="{{ route('admin.document-categories.index') }}">{{ __('ui.admin_content.categories') }}</a>@endcan</div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="documents" permission="documents.edit" />
    @if ($errors->has('category'))<p class="form-error-summary" role="alert">{{ $errors->first('category') }}</p>@endif
    @foreach ($errors->getMessages() as $field => $messages)@if ($field === 'document_category_id' || $field === 'files' || str_starts_with($field, 'files.'))<p class="form-error-summary" role="alert">{{ $messages[0] }}</p>@endif @endforeach
    @if (auth()->user()->can('media.upload') && auth()->user()->can('documents.create') && auth()->user()->can('documents.upload') && auth()->user()->can('documents.edit'))
        <section class="admin-form-card document-bulk-upload" aria-labelledby="document-bulk-upload-heading">
            <h2 id="document-bulk-upload-heading">{{ __('ui.admin_content.bulk_upload_documents') }}</h2>
            <p class="field-help">{{ __('ui.admin_content.bulk_upload_documents_help') }}</p>
            <form class="media-upload-form" method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="create_documents" value="1">
                <div class="form-field"><label for="bulk-document-category">{{ __('ui.admin_content.category') }}</label><select id="bulk-document-category" name="document_category_id" required><option value="">{{ __('ui.admin_content.select_category') }}</option>@foreach ($bulkCategories as $category)<option value="{{ $category->id }}" @selected((string) old('document_category_id') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>@error('document_category_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="form-field"><label for="document-bulk-files">{{ __('ui.media.files') }}</label><input id="document-bulk-files" type="file" name="files[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple required aria-describedby="document-bulk-files-help"><small id="document-bulk-files-help">{{ __('ui.media.bulk_upload_help', ['count' => 10, 'size' => ceil(config('portal.media.max_upload_kb', 20480) / 1024)]) }}</small></div>
                <button class="button button--primary" type="submit">{{ __('ui.media.upload_files_privately') }}</button>
            </form>
        </section>
    @endif
    <form class="admin-filter" method="GET" action="{{ route('admin.documents.index') }}">
        <label for="document-q">{{ __('ui.admin_content.search') }}</label><input id="document-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="document-status">{{ __('ui.admin_content.status') }}</label><select id="document-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (['draft', 'published', 'scheduled', 'archived'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <label for="document-category">{{ __('ui.admin_content.category') }}</label><select id="document-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.documents') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.title') }}</th><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.version') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($documents as $document)
            <tr @can('documents.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="documents" permission="documents.edit" :id="$document->id" :label="$document->localized('title')" /><strong>{{ $document->localized('title') }}</strong><small>{{ $document->slug }}</small></td><td>{{ $document->category?->localized('name') ?? '—' }}</td><td>{{ __('ui.admin_content.statuses.'.$document->status) }}</td><td><x-admin.active-status :active="$document->isPubliclyVisibleNow()" /></td><td>{{ $document->version }}</td><td>{{ $document->updated_at?->format('d M Y H:i') ?? '—' }}</td><td class="admin-row-actions">@can('documents.edit')<a href="{{ route('admin.documents.edit', $document) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('documents.delete')<form method="POST" action="{{ route('admin.documents.destroy', $document) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>
        @empty<tr><td colspan="7">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="admin-pagination">{{ $documents->links() }}</div>
@endsection
