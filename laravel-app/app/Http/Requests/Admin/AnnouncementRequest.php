<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AnnouncementRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if ($this->input('status') === 'published' && ! $this->filled('published_at')) {
            $announcement = $this->route('announcement');
            $publishedAt = $announcement?->status === 'published' && $announcement->published_at
                ? $announcement->published_at->format('Y-m-d\\TH:i')
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
        $announcement = $this->route('announcement');
        $base = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('announcements', 'slug')->ignore($announcement?->id)],
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:20000'],
            'description_hi' => ['nullable', 'string', 'max:20000'],
            'featured_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])],
            'attachment_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(Announcement::STATUSES)],
        ];

        if ($this->input('status') === 'scheduled') {
            $base['published_at'] = ['required', 'date', 'after:now'];
        }
        if ($this->filled('published_at')) {
            $base['expires_at'][] = 'after_or_equal:published_at';
        }

        return $base;
    }
}
