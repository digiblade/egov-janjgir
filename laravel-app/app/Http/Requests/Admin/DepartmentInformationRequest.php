<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $imageRule = Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']);

        return [
            'title_en' => ['required', 'string', 'max:255'], 'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string', 'max:20000'], 'description_hi' => ['required', 'string', 'max:20000'],
            'mission_en' => ['nullable', 'string', 'max:10000'], 'mission_hi' => ['nullable', 'string', 'max:10000'],
            'objectives_en' => ['nullable', 'string', 'max:10000'], 'objectives_hi' => ['nullable', 'string', 'max:10000'],
            'image_media_id' => ['nullable', 'integer', $imageRule],
        ];
    }
}
