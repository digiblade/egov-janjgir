@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.gallery_categories')])

@section('content')
    <header class="admin-page-heading admin-page-heading--row">
        <div><p class="eyebrow"><a href="{{ route('admin.gallery.index') }}">{{ __('ui.admin_content.gallery') }}</a></p><h1>{{ __('ui.admin_content.gallery_categories') }}</h1></div>
        <div class="admin-heading-actions"><a class="button button--secondary" href="{{ route('admin.gallery.index') }}">{{ __('ui.admin_content.gallery_albums') }}</a>@can('gallery.create')<a class="button button--primary" href="{{ route('admin.gallery-categories.create') }}">{{ __('ui.admin_content.create_category') }}</a>@endcan</div>
    </header>
    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    <x-admin.reorder-toolbar scope="gallery-categories" permission="gallery.edit" />
    @if ($errors->has('category'))<p class="form-error-summary" role="alert">{{ $errors->first('category') }}</p>@endif
    <form class="admin-filter" method="GET" action="{{ route('admin.gallery-categories.index') }}"><label for="gallery-category-q">{{ __('ui.admin_content.search') }}</label><input id="gallery-category-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120"><button class="button button--secondary" type="submit">{{ __('ui.admin_content.filter') }}</button></form>
    <div class="admin-table-wrap"><table class="admin-table"><caption class="sr-only">{{ __('ui.admin_content.gallery_categories') }}</caption><thead><tr><th scope="col">{{ __('ui.admin_content.category') }}</th><th scope="col">{{ __('ui.admin_content.gallery_album_count') }}</th><th scope="col">{{ __('ui.admin_content.active') }}</th><th scope="col">{{ __('ui.admin_content.updated_at') }}</th><th scope="col">{{ __('ui.admin_content.actions') }}</th></tr></thead><tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
        @forelse ($categories as $category)
            <tr @can('gallery.edit') draggable="true" @dragstart="start($event)" @dragend="end()" @endcan><td><x-admin.reorder-control scope="gallery-categories" permission="gallery.edit" :id="$category->id" :label="$category->localized('name')" /><strong>{{ $category->localized('name') }}</strong><small>{{ $category->slug }}</small></td><td>{{ $category->albums_count }}</td><td><x-admin.active-status :active="$category->is_active" /></td><td>{{ $category->updated_at?->format('d M Y H:i') ?? '—' }}</td><td class="admin-row-actions">@can('gallery.edit')<a href="{{ route('admin.gallery-categories.edit', $category) }}">{{ __('ui.admin_content.edit') }}</a>@endcan @can('gallery.delete')<form method="POST" action="{{ route('admin.gallery-categories.destroy', $category) }}" onsubmit="return confirm(@js(__('ui.admin_content.delete_confirm'))) ">@csrf @method('DELETE')<button class="text-button text-button--danger" type="submit">{{ __('ui.admin_content.delete') }}</button></form>@endcan</td></tr>
        @empty<tr><td colspan="5">{{ __('ui.admin_content.empty') }}</td></tr>@endforelse
    </tbody></table></div><div class="admin-pagination">{{ $categories->links() }}</div>
@endsection
