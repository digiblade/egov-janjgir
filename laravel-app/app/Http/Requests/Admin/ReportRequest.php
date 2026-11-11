<?php

namespace App\Http\Requests\Admin;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if ($this->input('status') === 'published' && ! $this->filled('published_at')) {
            $report = $this->route('report');
            $publishedAt = $report?->status === 'published' && $report->published_at
                ? $report->published_at->format('Y-m-d\\TH:i')
                : now()->format('Y-m-d\\TH:i');
            $this->merge(['published_at' => $publishedAt]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $report = $this->route('report');
        $category = Rule::exists('report_categories', 'id')->whereNull('deleted_at');

        if (! $report || (int) $this->input('category_id') !== (int) $report->category_id) {
            $category->where('is_active', true);
        }

        $rules = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('reports', 'slug')->ignore($report?->id)],
            'category_id' => ['required', 'integer', $category],
            'media_asset_id' => ['required', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])],
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:50000'],
            'description_hi' => ['nullable', 'string', 'max:50000'],
            'report_period' => ['nullable', 'string', 'max:100'],
            'report_date' => ['nullable', 'date'],
            'office_block' => ['nullable', 'string', 'max:120'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(Report::STATUSES)],
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
