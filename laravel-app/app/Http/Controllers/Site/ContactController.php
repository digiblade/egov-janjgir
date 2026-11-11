<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactDetail;
use App\Models\SupportRequest;
use App\Support\SafeUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $contact = ContactDetail::query()->where('singleton_key', 'main')->first();

        if ($contact && $contact->map_url) {
            $contact->map_url = SafeUrl::normalize($contact->map_url);
        }

        return view('site.contact', compact('contact'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'mobile_number' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $reference = 'SUP-'.now()->format('Ymd').'-'.Str::upper(Str::random(10));

        SupportRequest::query()->create([
            'reference_number' => $reference,
            'full_name' => trim($validated['full_name']),
            'email' => mb_strtolower(trim($validated['email'])),
            'mobile_number' => isset($validated['mobile_number']) ? trim($validated['mobile_number']) : null,
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'category' => isset($validated['category']) ? trim($validated['category']) : null,
            'status' => 'new',
            'submitted_at' => now(),
        ]);

        return redirect()->route('contact.show')->with('support_reference', $reference);
    }
}
