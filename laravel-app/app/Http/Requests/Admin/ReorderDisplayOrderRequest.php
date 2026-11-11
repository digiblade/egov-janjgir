<?php

namespace App\Http\Requests\Admin;

use App\Support\DisplayOrderResources;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderDisplayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $scope = (string) $this->route('scope');
        if (! in_array($scope, DisplayOrderResources::scopes(), true)) {
            return false;
        }

        return $this->user()?->can(DisplayOrderResources::get($scope)['permission']) ?? false;
    }

    public function rules(): array
    {
        $scope = (string) $this->route('scope');
        $resource = DisplayOrderResources::get($scope);
        $model = $resource['model'];
        $table = (new $model())->getTable();

        $rules = [
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists($table, 'id')->whereNull('deleted_at')],
        ];

        return $rules;
    }
}
