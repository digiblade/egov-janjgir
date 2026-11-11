@extends('layouts.admin', ['pageTitle' => __('ui.media.title')])

@section('content')
    <header class="admin-page-heading">
        <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
        <h1>{{ __('ui.media.title') }}</h1>
        <p>{{ __('ui.media.description') }}</p>
    </header>

    @if (session('status'))<p class="form-success" role="status">{{ session('status') }}</p>@endif
    @if ($errors->has('media'))<p class="field-error" role="alert">{{ $errors->first('media') }}</p>@endif

    @can('media.upload')
        <section class="admin-form-card media-upload-card" aria-labelledby="media-upload-heading">
            <div><h2 id="media-upload-heading">{{ __('ui.media.upload_heading') }}</h2><p>{{ __('ui.media.upload_private_note') }}</p></div>
            <form class="media-upload-form" method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field">
                    <label for="media-file">{{ __('ui.media.file') }}</label>
                    <input id="media-file" name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.mp4" required>
                    @error('file')<p class="field-error">{{ $message }}</p>@enderror
                    <small>{{ __('ui.media.max_sizes', ['documents' => ceil(config('portal.media.max_upload_kb', 20480) / 1024), 'video' => ceil(config('portal.media.max_video_upload_kb', 51200) / 1024)]) }}</small>
                </div>
                <button class="button button--primary" type="submit">{{ __('ui.media.upload') }}</button>
            </form>
        </section>
    @endcan

    <form class="admin-filter admin-filter--wrap" method="GET" action="{{ route('admin.media.index') }}">
        <label for="media-q">{{ __('ui.actions.search') }}</label>
        <input id="media-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="160">
        <label for="media-visibility">{{ __('ui.media.visibility') }}</label>
        <select id="media-visibility" name="visibility">
            <option value="">{{ __('ui.admin_content.all') }}</option>
            @foreach (['private', 'public'] as $visibility)
                <option value="{{ $visibility }}" @selected(($filters['visibility'] ?? '') === $visibility)>{{ __('ui.media.'.$visibility) }}</option>
            @endforeach
        </select>
        <label for="media-type">{{ __('ui.media.type') }}</label>
        <select id="media-type" name="type">
            <option value="">{{ __('ui.admin_content.all') }}</option>
            @foreach (['image', 'document', 'video'] as $type)
                <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ __('ui.media.'.$type) }}</option>
            @endforeach
        </select>
        <button class="button button--secondary" type="submit">{{ __('ui.actions.filter') }}</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <caption class="sr-only">{{ __('ui.media.title') }}</caption>
            <thead><tr>
                <th scope="col">{{ __('ui.media.file') }}</th>
                <th scope="col">{{ __('ui.media.metadata') }}</th>
                <th scope="col">{{ __('ui.media.visibility') }}</th>
                <th scope="col">{{ __('ui.media.uploaded_by') }}</th>
                <th scope="col">{{ __('ui.admin_content.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr>
                        <td>
                            @if ($asset->visibility === 'public' && str_starts_with($asset->mime_type, 'image/'))
                                <img class="media-thumb" src="{{ route('media.public', $asset) }}" alt="" loading="lazy">
                            @endif
                            <strong>{{ $asset->original_filename }}</strong><small>#{{ $asset->id }}</small>
                        </td>
                        <td>{{ $asset->mime_type }}<small>{{ number_format($asset->size_bytes / 1024) }} KB</small></td>
                        <td><span class="status-pill status-pill--{{ $asset->visibility }}">{{ __('ui.media.'.$asset->visibility) }}</span></td>
                        <td>{{ $asset->uploader?->name ?? __('ui.media.unknown_uploader') }}<small>{{ $asset->created_at?->format('d M Y H:i') }}</small></td>
                        <td class="admin-row-actions">
                            @if ($asset->visibility === 'public')
                                <a href="{{ route('media.public', $asset) }}" target="_blank" rel="noopener">{{ __('ui.media.open_file') }}</a>
                            @elseif (auth()->user()->can('media.view_private') || (int) $asset->uploaded_by === (int) auth()->id())
                                <a href="{{ route('admin.media.download-private', $asset) }}" download>{{ __('ui.media.download_private') }}</a>
                            @endif

                            @can('media.publish')
                                @if ($asset->visibility === 'public' || auth()->user()->can('media.view_private') || (int) $asset->uploaded_by === (int) auth()->id())
                                    <form method="POST" action="{{ route('admin.media.visibility', $asset) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="visibility" value="{{ $asset->visibility === 'public' ? 'private' : 'public' }}">
                                        <button class="text-button" type="submit">{{ $asset->visibility === 'public' ? __('ui.media.make_private') : __('ui.media.publish') }}</button>
                                    </form>
                                @endif
                            @endcan

                            @can('media.delete')
                                @if ($asset->visibility === 'public' || auth()->user()->can('media.view_private') || (int) $asset->uploaded_by === (int) auth()->id())
                                    <form method="POST" action="{{ route('admin.media.destroy', $asset) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">
                                        @csrf @method('DELETE')
                                        <button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">{{ __('ui.media.no_assets') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $assets->links() }}</div>
@endsection
