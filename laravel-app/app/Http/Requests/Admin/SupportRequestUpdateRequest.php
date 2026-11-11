<?php

namespace App\Http\Requests\Admin;

use App\Models\SupportRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupportRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(SupportRequest::STATUSES)],
        ];
    }
}
