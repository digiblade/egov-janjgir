<?php

namespace App\Http\Requests\Admin;

use App\Models\TrainingResource;
use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrainingResourceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if ($this->input('status') === 'published' && ! $this->filled('published_at')) {
            $resource = $this->route('trainingResource');
            $publishedAt = $resource?->status === 'published' && $resource->published_at
                ? $resource->published_at->format('Y-m-d\\TH:i')
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
        $resource = $this->route('trainingResource');
        $categoryRule = Rule::exists('training_categories', 'id')->whereNull('deleted_at');
        if (! $resource || (int) $this->input('category_id') !== (int) $resource->category_id) {
            $categoryRule->where('is_active', true);
        }
        $rules = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('training_resources', 'slug')->ignore($resource?->id)],
            'category_id' => ['required', 'integer', $categoryRule],
            'resource_type' => ['required', Rule::in(TrainingResource::TYPES)],
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:50000'],
            'description_hi' => ['nullable', 'string', 'max:50000'],
            'media_asset_id' => ['nullable', 'required_without:external_url', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'video/mp4'])],
            'thumbnail_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])],
            'external_url' => ['nullable', 'required_without:media_asset_id', 'string', 'max:2048', function (string $attribute, mixed $value, $fail): void {
                if ($value !== null && $value !== '' && SafeUrl::normalize($value) === null) {
                    $fail(__('validation.safe_url'));
                }
            }],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'training_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(TrainingResource::STATUSES)],
        ];

        if ($this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        } elseif ($this->input('status') === 'published') {
            $rules['published_at'][] = 'before_or_equal:now';
        }
        if ($this->filled('published_at')) {
            $rules['expires_at'][] = 'after_or_equal:published_at';
        }

        return $rules;
    }
}
