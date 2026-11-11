@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.gallery_albums')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow">{{ __('ui.admin.workspace') }}</p><h1>{{ __('ui.admin_content.gallery_albums') }}</h1></div>
        <div class="admin-heading-actions"><a class="button button--secondary" href="{{ route('admin.gallery-categories.index') }}">{{ __('ui.admin_content.gallery_categories') }}</a>@can('gallery.create')<a class="button button--primary" href="{{ route('admin.gallery.create') }}">{{ __('ui.admin_content.create_gallery_album') }}</a>@endcan</div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="gallery-albums" permission="gallery.edit" />
    <form class="admin-filter" method="GET" action="{{ route('admin.gallery.index') }}">
        <label for="gallery-q">{{ __('ui.admin_content.search') }}</label><input id="gallery-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120">
        <label for="gallery-status">{{ __('ui.admin_content.status') }}</label><select id="gallery-status" name="status"><option value="">{{ __('ui.admin_content.all_statuses') }}</option>@foreach (['draft', 'published', 'scheduled', 'archived'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>@endforeach</select>
        <label for="gallery-category">{{ __('ui.admin_content.category') }}</label><select id="gallery-category" name="category"><option value="">{{ __('ui.admin_content.all_categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->localized('name') }}</option>@endforeach</select>
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.gallery_albums') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.title') }}</th><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.gallery_image_count') }}</th><th scope="col">{{ __('ui.admin_content.status') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($albums as $album)
            <tr @can('gallery.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="gallery-albums" permission="gallery.edit" :id="$album->id" :label="$album->localized('title')" /><strong>{{ $album->localized('title') }}</strong><small>{{ $album->slug }}</small></td><td>{{ $album->category?->localized('name') ?? '—' }}</td><td>{{ $album->visible_images_count }}</td><td>{{ __('ui.admin_content.statuses.'.$album->status) }}</td><td><x-admin.active-status :active="$album->isPubliclyVisibleNow()" /></td><td>{{ $album->updated_at?->format('d M Y H:i') ?? '—' }}</td><td class="admin-row-actions">@can('gallery.edit')<a href="{{ route('admin.gallery.edit', $album) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('gallery.delete')<form method="POST" action="{{ route('admin.gallery.destroy', $album) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>
        @empty<tr><td colspan="7">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse
    </tbody></table></div><div class="admin-pagination">{{ $albums->links() }}</div>
@endsection
