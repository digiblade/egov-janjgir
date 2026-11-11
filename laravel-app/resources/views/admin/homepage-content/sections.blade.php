@extends('layouts.admin', ['pageTitle' => __('ui.admin_content.homepage_sections')])

@section('content')
    <x-admin.back-link :href="route('admin.homepage.index')" />
    <header class="admin-page-heading">
        <p class="eyebrow">{{ __('ui.admin.workspace') }}</p>
        <h1>{{ __('ui.admin_content.homepage_sections') }}</h1>
        <p>{{ __('ui.admin_content.homepage_sections_help') }}</p>
        <nav class="admin-row-actions" aria-label="{{ __('ui.admin_content.homepage_tools') }}">
            <a href="{{ route('admin.department.edit') }}">{{ __('ui.admin_content.department_information') }}</a>
            <a href="{{ route('admin.quick-links.index') }}">{{ __('ui.admin_content.quick-links') }}</a>
            <a href="{{ route('admin.updates.index') }}">{{ __('ui.admin_content.updates') }}</a>
        </nav>
    </header>

    @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())
        <div class="form-error-summary" role="alert">
            <h2>{{ __('ui.admin.login_errors') }}</h2>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form class="admin-form-card" method="POST" action="{{ route('admin.homepage.update') }}">
        @csrf @method('PUT')
        <div class="admin-table-wrap">
            <table class="admin-table homepage-sections-table">
                <caption class="sr-only">{{ __('ui.admin_content.homepage_sections') }}</caption>
                <thead>
                    <tr>
                        <th scope="col">{{ __('ui.admin_content.section') }}</th>
                        <th scope="col">{{ __('ui.admin_content.active') }}</th>
                        <th scope="col">{{ __('ui.admin_content.display_order') }}</th>
                        <th scope="col">{{ __('ui.admin_content.update_sources') }}</th>
                    </tr>
                </thead>
                <tbody x-data="portalSortableRows" @dragover.prevent="over($event)" @drop.prevent="syncOrder()">
                    @foreach ($sections as $index => $section)
                        <tr draggable="true" @dragstart="start($event)" @dragend="end()">
                            <td>
                                <span class="sortable-row-controls">
                                    <button type="button" class="text-button sortable-grip" aria-label="{{ __('ui.admin_content.drag_to_reorder', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}" title="{{ __('ui.admin_content.drag_to_reorder', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}">⠿</button>
                                    <button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_section_up', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}" @click="move($event.currentTarget.closest('tr'), -1)">↑</button>
                                    <button type="button" class="text-button" data-sort-move aria-label="{{ __('ui.admin_content.move_section_down', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}" @click="move($event.currentTarget.closest('tr'), 1)">↓</button>
                                    <span>{{ __('ui.admin_content.section_names.'.$section->section_key) }}</span>
                                </span>
                                <input type="hidden" name="sections[{{ $index }}][key]" value="{{ $section->section_key }}">
                                <details class="homepage-section-localization">
                                    <summary>{{ __('ui.admin_content.localized_section_copy') }}</summary>
                                    <div class="homepage-section-localization__fields">
                                        @foreach (['label_en', 'label_hi', 'title_en', 'title_hi'] as $field)
                                            <div class="form-field">
                                                <label for="sections_{{ $index }}_{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label>
                                                <input id="sections_{{ $index }}_{{ $field }}" name="sections[{{ $index }}][{{ $field }}]" value="{{ old("sections.{$index}.{$field}", $section->$field) }}" maxlength="{{ str_starts_with($field, 'label') ? 120 : 180 }}" @if (str_ends_with($field, '_hi')) lang="hi" @endif>
                                                @error("sections.{$index}.{$field}")<p class="field-error">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                        @foreach (['description_en', 'description_hi'] as $field)
                                            <div class="form-field form-field--wide">
                                                <label for="sections_{{ $index }}_{{ $field }}">{{ __('ui.admin_content.'.$field) }}</label>
                                                <textarea id="sections_{{ $index }}_{{ $field }}" name="sections[{{ $index }}][{{ $field }}]" rows="3" maxlength="2000" @if (str_ends_with($field, '_hi')) lang="hi" @endif>{{ old("sections.{$index}.{$field}", $section->$field) }}</textarea>
                                                @error("sections.{$index}.{$field}")<p class="field-error">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td><x-admin.active-status :active="(bool) old('sections.'.$index.'.is_enabled', $section->is_enabled)" /> <input aria-label="{{ __('ui.admin_content.enable_section', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}" name="sections[{{ $index }}][is_enabled]" type="checkbox" value="1" @checked(old("sections.{$index}.is_enabled", $section->is_enabled))></td>
                            <td><input data-display-order aria-label="{{ __('ui.admin_content.order_section', ['section' => __('ui.admin_content.section_names.'.$section->section_key)]) }}" name="sections[{{ $index }}][display_order]" type="number" min="0" max="100000" value="{{ old("sections.{$index}.display_order", $section->display_order) }}"></td>
                            <td>
                                @if ($section->section_key === 'updates')
                                    <input type="hidden" name="sections[{{ $index }}][settings][sources_present]" value="1">
                                    @foreach ($updateSources as $source => $label)
                                        <label class="remember-field"><input type="checkbox" name="sections[{{ $index }}][settings][sources][]" value="{{ $source }}" @checked(in_array($source, old("sections.{$index}.settings.sources", $section->settings['sources'] ?? array_keys($updateSources)), true))><span>{{ __('ui.admin_content.update_source_names.'.$source) }}</span></label>
                                    @endforeach
                                @else
                                    <span aria-hidden="true">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-form-actions"><button class="button button--primary" type="submit">{{ __('ui.admin_content.save') }}</button></div>
    </form>
@endsection
