@props(['active'])

<span class="status-pill status-pill--{{ $active ? 'active' : 'inactive' }}">
    {{ $active ? __('ui.admin_content.active') : __('ui.admin_content.inactive') }}
</span>
