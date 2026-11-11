@props(['href', 'label' => null])

<a class="button button--secondary admin-back-link" href="{{ $href }}">
    <span aria-hidden="true">←</span>
    {{ $label ?? __('ui.admin_content.back_to_list') }}
</a>
