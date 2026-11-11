<?php

namespace App\Http\Requests\Admin;

use App\Models\GalleryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if (! $this->exists('is_active')) {
            $this->merge(['is_active' => false]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $category = $this->route('galleryCategory');
        $rules = [
            'name_en' => ['required', 'string', 'max:255'],
            'name_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'description_hi' => ['nullable', 'string', 'max:10000'],
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('gallery_categories', 'slug')->ignore($category?->id)],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
        ];

        if ($this->exists('cover_media_id')) {
            $rules['cover_media_id'] = ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return ['cover_media_id.exists' => __('validation.public_media_asset')];
    }
}
