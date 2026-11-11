@props(['scope', 'permission', 'id', 'label'])
@php($formId = 'reorder-'.$scope)
@can($permission)
    <input type="hidden" form="{{ $formId }}" name="ids[]" value="{{ $id }}">
    <span class="sortable-row-controls">
        <button type="button" class="text-button sortable-grip" aria-label="{{ __('ui.admin_content.drag_to_reorder_item', ['item' => $label]) }}" title="{{ __('ui.admin_content.drag_to_reorder_item', ['item' => $label]) }}">⠿</button>
        <button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_item_up', ['item' => $label]) }}" @click="move($event.currentTarget.closest('tr'), -1)">↑</button>
        <button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_item_down', ['item' => $label]) }}" @click="move($event.currentTarget.closest('tr'), 1)">↓</button>
    </span>
@endcan
