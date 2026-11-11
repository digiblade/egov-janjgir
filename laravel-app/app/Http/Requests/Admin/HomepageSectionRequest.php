<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomepageSectionRequest extends FormRequest
{
    private const KEYS = ['banners', 'department', 'notices', 'announcements', 'updates', 'quick_links', 'statistics'];
    private const UPDATE_SOURCES = ['notices', 'documents', 'reports', 'training', 'announcements'];

    protected function prepareForValidation(): void
    {
        $sections = $this->input('sections');
        if (is_array($sections)) {
            foreach ($sections as $key => $section) {
                if (is_array($section) && ! array_key_exists('is_enabled', $section)) {
                    $sections[$key]['is_enabled'] = false;
                }

                if (is_array($section)
                    && ($section['key'] ?? null) === 'updates'
                    && isset($section['settings']['sources_present'])
                    && ! array_key_exists('sources', $section['settings'] ?? [])) {
                    $sections[$key]['settings']['sources'] = [];
                }

                if (is_array($sections[$key] ?? null)) {
                    unset($sections[$key]['settings']['sources_present']);
                }
            }
            $this->merge(['sections' => $sections]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('homepage.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'max:'.count(self::KEYS)],
            'sections.*' => ['required', 'array'],
            'sections.*.key' => ['required', 'string', Rule::in(self::KEYS)],
            'sections.*.is_enabled' => ['required', 'boolean'],
            'sections.*.display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'sections.*.label_en' => ['nullable', 'string', 'max:120'],
            'sections.*.label_hi' => ['nullable', 'string', 'max:120'],
            'sections.*.title_en' => ['nullable', 'string', 'max:180'],
            'sections.*.title_hi' => ['nullable', 'string', 'max:180'],
            'sections.*.description_en' => ['nullable', 'string', 'max:2000'],
            'sections.*.description_hi' => ['nullable', 'string', 'max:2000'],
            'sections.*.settings' => ['sometimes', 'array'],
            'sections.*.settings.sources' => ['sometimes', 'array', 'max:'.count(self::UPDATE_SOURCES)],
            'sections.*.settings.sources.*' => ['required', 'string', 'distinct', Rule::in(self::UPDATE_SOURCES)],
        ];
    }
}
