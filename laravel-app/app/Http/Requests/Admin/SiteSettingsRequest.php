<?php

namespace App\Http\Requests\Admin;

use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $imageRule = Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']);

        return [
            'site_title_en' => ['required', 'string', 'max:180'],
            'site_title_hi' => ['required', 'string', 'max:180'],
            'department_name_en' => ['nullable', 'string', 'max:180'],
            'department_name_hi' => ['nullable', 'string', 'max:180'],
            'logo_media_id' => ['nullable', 'integer', clone $imageRule],
            'favicon_media_id' => ['nullable', 'integer', clone $imageRule],
            'seo_title_en' => ['nullable', 'string', 'max:70'],
            'seo_title_hi' => ['nullable', 'string', 'max:70'],
            'seo_description_en' => ['nullable', 'string', 'max:180'],
            'seo_description_hi' => ['nullable', 'string', 'max:180'],
            'footer_note_en' => ['nullable', 'string', 'max:1200'],
            'footer_note_hi' => ['nullable', 'string', 'max:1200'],
            'copyright_en' => ['nullable', 'string', 'max:200'],
            'copyright_hi' => ['nullable', 'string', 'max:200'],
            'social_facebook' => ['nullable', 'string', 'max:2048', $this->safeUrlRule()],
            'social_instagram' => ['nullable', 'string', 'max:2048', $this->safeUrlRule()],
            'social_youtube' => ['nullable', 'string', 'max:2048', $this->safeUrlRule()],
            'social_x' => ['nullable', 'string', 'max:2048', $this->safeUrlRule()],
            'default_language' => ['required', Rule::in(['en', 'hi'])],
            'carousel_autoplay' => ['sometimes', 'boolean'],
            'carousel_interval_ms' => ['required', 'integer', 'min:4000', 'max:20000'],
        ];
    }

    private function safeUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, $fail): void {
            if ($value !== null && $value !== '' && SafeUrl::normalize($value) === null) {
                $fail(__('validation.safe_url'));
            }
        };
    }
}
