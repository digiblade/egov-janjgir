<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentInformationRequest;
use App\Models\DepartmentInformation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentInformationManagementController extends Controller
{
    public function edit(): View
    {
        $department = DepartmentInformation::query()->firstOrNew(['singleton_key' => 'department']);

        return view('admin.homepage-content.department', compact('department'));
    }

    public function update(DepartmentInformationRequest $request): RedirectResponse
    {
        $department = DepartmentInformation::query()->firstOrNew(['singleton_key' => 'department']);
        $department->fill($request->validated());
        $department->forceFill(['updated_by' => $request->user()->id]);
        if (! $department->exists) {
            $department->forceFill(['created_by' => $request->user()->id]);
        }
        $department->save();

        return to_route('admin.department.edit')->with('status', __('ui.admin_content.saved'));
    }
}
