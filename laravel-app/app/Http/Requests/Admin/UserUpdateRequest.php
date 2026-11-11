<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Database\Seeders\RolePermissionSeeder;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($managedUser?->id)],
            'roles_present' => ['sometimes', 'in:1'],
            'roles' => ['sometimes', 'array', 'max:20'],
            'roles.*' => ['required', 'integer', 'distinct', Rule::exists('roles', 'id')->where(fn ($query) => $query
                ->where('guard_name', 'web')
                ->where('name', '<>', config('portal.roles.super_admin', 'Super Admin')))],
            'permissions_present' => ['sometimes', 'in:1'],
            'permissions' => ['sometimes', 'array', 'max:100'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::in(RolePermissionSeeder::assignablePermissionNames())],
        ];
    }
}
