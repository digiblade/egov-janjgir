@props(['scope', 'permission'])
@php($formId = 'reorder-'.$scope)
@can($permission)
    <form id="{{ $formId }}" method="POST" action="{{ route('admin.reorder', $scope) }}" class="admin-reorder-toolbar">
        @csrf
        <button class="button button--secondary" type="submit">{{ __('ui.admin_content.save_order') }}</button>
    </form>
@endcan
