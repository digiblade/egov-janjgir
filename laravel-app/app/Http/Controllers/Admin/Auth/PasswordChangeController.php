<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function edit(): View
    {
        return view('admin.auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(14)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $request->user()->forceFill([
            'password' => $validator->validated()['password'],
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('status', __('ui.admin.password_changed'));
    }
}
