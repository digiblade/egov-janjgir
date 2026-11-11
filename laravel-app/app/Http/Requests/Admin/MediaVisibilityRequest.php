<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.publish') ?? false;
    }

    public function rules(): array
    {
        return [
            'visibility' => ['required', Rule::in(['private', 'public'])],
        ];
    }
}
