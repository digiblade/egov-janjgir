<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $faq = $this->route('faq');
        $category = Rule::exists('faq_categories', 'id')->whereNull('deleted_at');

        if (! $faq || (int) $this->input('category_id') !== (int) $faq->category_id) {
            $category->where('is_active', true);
        }

        $rules = [
            'category_id' => ['required', 'integer', $category],
            'question_en' => ['required', 'string', 'max:500'],
            'question_hi' => ['required', 'string', 'max:500'],
            'answer_en' => ['required', 'string', 'max:50000'],
            'answer_hi' => ['required', 'string', 'max:50000'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(Faq::STATUSES)],
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
