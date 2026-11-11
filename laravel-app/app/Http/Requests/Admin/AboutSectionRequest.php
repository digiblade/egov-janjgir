<?php

namespace App\Http\Requests\Admin;

use App\Models\AboutSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AboutSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'content_en' => ['required', 'string', 'max:50000'],
            'content_hi' => ['required', 'string', 'max:50000'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(AboutSection::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];

        if ($this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        }
        if ($this->filled('published_at')) {
            $rules['expires_at'][] = 'after_or_equal:published_at';
        }

        return $rules;
    }
}
