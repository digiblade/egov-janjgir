<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactDetailRequest;
use App\Models\ContactDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactDetailManagementController extends Controller
{
    public function edit(): View
    {
        $contact = ContactDetail::query()->firstOrNew(['singleton_key' => 'main']);

        return view('admin.contact.edit', compact('contact'));
    }

    public function update(ContactDetailRequest $request): RedirectResponse
    {
        $contact = ContactDetail::query()->firstOrNew(['singleton_key' => 'main']);
        $contact->fill($request->validated());
        $contact->updated_by = $request->user()->id;
        $contact->save();

        return to_route('admin.contact.index')->with('status', __('ui.admin_content.saved'));
    }
}
