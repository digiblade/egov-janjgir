<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\HomepageUpdate;
use App\Models\Kpi;
use App\Models\QuickLink;
use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class HomepageContentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['is_active', 'is_external', 'opens_in_new_tab'] as $flag) {
            if (! $this->exists($flag)) {
                $this->merge([$flag => false]);
            }
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $type = explode('.', (string) $this->route()?->getName())[1] ?? '';
        $imageRule = Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']);

        $rules = match ($type) {
            'banners' => [
                'title_en' => ['nullable', 'string', 'max:255'], 'title_hi' => ['nullable', 'string', 'max:255'],
                'subtitle_en' => ['nullable', 'string', 'max:1000'], 'subtitle_hi' => ['nullable', 'string', 'max:1000'],
                'desktop_image_media_id' => ['required', 'integer', clone $imageRule],
                'mobile_image_media_id' => ['nullable', 'integer', clone $imageRule],
                'cta_text_en' => ['nullable', 'string', 'max:120'], 'cta_text_hi' => ['nullable', 'string', 'max:120'],
                'cta_url' => ['nullable', 'string', 'max:2048', fn ($attribute, $value, $fail) => $value && SafeUrl::normalize($value) === null ? $fail(__('validation.safe_url')) : null],
                'text_alignment' => ['required', Rule::in(['left', 'center', 'right'])],
                'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'overlay_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'overlay_opacity' => ['required', 'integer', 'min:0', 'max:85'], 'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
                'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'], 'is_active' => ['required', 'boolean'],
            ],
            'kpis' => [
                'title_en' => ['required', 'string', 'max:255'], 'title_hi' => ['required', 'string', 'max:255'],
                'description_en' => ['nullable', 'string', 'max:255'], 'description_hi' => ['nullable', 'string', 'max:255'],
                'value' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
                'prefix' => ['nullable', 'string', 'max:20'], 'suffix' => ['nullable', 'string', 'max:20'],
                'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
                'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'icon_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'display_order' => ['required', 'integer', 'min:0', 'max:100000'], 'is_active' => ['required', 'boolean'],
            ],
            'quick-links' => [
                'title_en' => ['required', 'string', 'max:255'], 'title_hi' => ['required', 'string', 'max:255'],
                'description_en' => ['nullable', 'string', 'max:2000'], 'description_hi' => ['nullable', 'string', 'max:2000'],
                'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'url' => ['required', 'string', 'max:2048', fn ($attribute, $value, $fail) => SafeUrl::normalize($value) === null ? $fail(__('validation.safe_url')) : null],
                'is_external' => ['required', 'boolean'], 'opens_in_new_tab' => ['required', 'boolean'],
                'display_order' => ['required', 'integer', 'min:0', 'max:100000'], 'is_active' => ['required', 'boolean'],
            ],
            'updates' => [
                'title_en' => ['required', 'string', 'max:255'], 'title_hi' => ['required', 'string', 'max:255'],
                'summary_en' => ['nullable', 'string', 'max:1000'], 'summary_hi' => ['nullable', 'string', 'max:1000'],
                'url' => ['nullable', 'string', 'max:2048', fn ($attribute, $value, $fail) => $value && SafeUrl::normalize($value) === null ? $fail(__('validation.safe_url')) : null],
                'status' => ['required', Rule::in(HomepageUpdate::STATUSES)], 'published_at' => ['nullable', 'date'],
                'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'], 'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            ],
            default => [],
        };

        if ($type === 'updates' && $this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        }

        return $rules;
    }
}
