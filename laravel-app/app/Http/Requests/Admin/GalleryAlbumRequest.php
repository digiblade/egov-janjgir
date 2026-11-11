<?php

namespace App\Http\Requests\Admin;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GalleryAlbumRequest extends FormRequest
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
        $album = $this->route('galleryAlbum');
        $categoryRule = Rule::exists('gallery_categories', 'id')->whereNull('deleted_at');
        if (! $album || (int) $this->input('gallery_category_id') !== (int) $album->gallery_category_id) {
            $categoryRule->where('is_active', true);
        }

        $rules = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('gallery_albums', 'slug')->ignore($album?->id)],
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:30000'],
            'description_hi' => ['nullable', 'string', 'max:30000'],
            'event_date' => ['nullable', 'date'],
            'gallery_category_id' => ['required', 'integer', $categoryRule],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
            'status' => ['required', Rule::in(GalleryAlbum::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'images_present' => ['sometimes', 'in:1'],
            'images' => ['sometimes', 'array', 'max:80'],
            'images.*.id' => ['nullable', 'integer', Rule::exists('gallery_images', 'id')->where(fn ($query) => $query->where('gallery_album_id', $album?->id ?? 0))],
            'images.*.media_asset_id' => ['nullable', 'integer', 'distinct', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])],
            'images.*.caption_en' => ['nullable', 'string', 'max:255'],
            'images.*.caption_hi' => ['nullable', 'string', 'max:255'],
            'images.*.alt_text_en' => ['nullable', 'string', 'max:255'],
            'images.*.alt_text_hi' => ['nullable', 'string', 'max:255'],
            'images.*.display_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'images.*.is_active' => ['sometimes', 'boolean'],
            'images.*.remove' => ['sometimes', 'boolean'],
        ];

        if ($this->exists('cover_media_id')) {
            $rules['cover_media_id'] = ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])];
        }
        if ($this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        }
        if ($this->filled('published_at')) {
            $rules['expires_at'][] = 'after_or_equal:published_at';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('images', []) as $index => $image) {
                if (! is_array($image)) {
                    continue;
                }

                $hasExistingId = filled($image['id'] ?? null);
                $hasAsset = filled($image['media_asset_id'] ?? null);
                $hasMetadata = collect(['caption_en', 'caption_hi', 'alt_text_en', 'alt_text_hi'])
                    ->contains(fn (string $key): bool => filled($image[$key] ?? null));

                if (! $hasExistingId && ! $hasAsset && $hasMetadata) {
                    $validator->errors()->add("images.{$index}.media_asset_id", __('ui.admin_content.gallery_image_needs_asset'));
                }

                if (! $hasExistingId && ! $hasAsset && ($image['remove'] ?? false)) {
                    $validator->errors()->add("images.{$index}.remove", __('ui.admin_content.gallery_image_remove_invalid'));
                }

                $album = $this->route('galleryAlbum');
                if ($album && $hasExistingId && $hasAsset && ! ($image['remove'] ?? false)) {
                    $existing = GalleryImage::query()->where('gallery_album_id', $album->id)->find((int) $image['id']);
                    if ($existing && (int) $image['media_asset_id'] !== (int) $existing->media_asset_id
                        && GalleryImage::withTrashed()->where('gallery_album_id', $album->id)
                            ->where('media_asset_id', (int) $image['media_asset_id'])
                            ->where('id', '<>', $existing->id)->exists()) {
                        $validator->errors()->add("images.{$index}.media_asset_id", __('ui.admin_content.gallery_image_already_in_album'));
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'cover_media_id.exists' => __('validation.public_media_asset'),
            'images.*.media_asset_id.exists' => __('validation.public_media_asset'),
        ];
    }
}
