<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['sometimes', 'array', 'max:200'],
            'permissions.*' => ['required', 'integer', 'distinct', Rule::exists('permissions', 'id')->where('guard_name', 'web')],
        ];
    }
}
