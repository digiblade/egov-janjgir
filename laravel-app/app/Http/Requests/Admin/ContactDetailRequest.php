<?php

namespace App\Http\Requests\Admin;

use App\Support\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'department_name_en' => ['nullable', 'string', 'max:180'],
            'department_name_hi' => ['nullable', 'string', 'max:180'],
            'office_address_en' => ['nullable', 'string', 'max:5000'],
            'office_address_hi' => ['nullable', 'string', 'max:5000'],
            'support_email' => ['nullable', 'email:rfc', 'max:254'],
            'contact_number' => ['nullable', 'string', 'max:40', 'regex:/^[+0-9() .-]+$/'],
            'office_hours_en' => ['nullable', 'string', 'max:500'],
            'office_hours_hi' => ['nullable', 'string', 'max:500'],
            'map_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, $fail): void {
                if ($value !== null && $value !== '' && SafeUrl::normalize($value) === null) {
                    $fail(__('validation.safe_url'));
                }
            }],
        ];
    }
}
