<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\PortalSetup;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Support\AuditLogger;

class InitialSuperAdminController extends Controller
{
    public function create(): View
    {
        $this->ensureSetupIsAvailable();

        return view('admin.auth.initialize');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->ensureSetupIsAvailable();

        $request->merge([
            'email' => is_string($request->input('email')) ? mb_strtolower(trim($request->input('email'))) : $request->input('email'),
            'admin_email' => is_string($request->input('admin_email')) ? mb_strtolower(trim($request->input('admin_email'))) : $request->input('admin_email'),
        ]);

        $configuredKeyHash = (string) config('portal.setup_key_hash');
        $submittedKey = (string) $request->input('setup_key', '');

        if (
            strlen($submittedKey) !== 64
            || preg_match('/\A[a-fA-F0-9]{64}\z/', $submittedKey) !== 1
            || ! hash_equals($configuredKeyHash, hash('sha256', $submittedKey))
        ) {
            return back()
                ->withErrors(['setup_key' => __('ui.admin.setup_invalid_key')])
                ->withInput($request->only('name', 'email', 'admin_name', 'admin_email'));
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(14)->mixedCase()->numbers()->symbols()],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'string', 'email', 'max:254', 'different:email', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Password::min(14)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('name', 'email', 'admin_name', 'admin_email'));
        }

        $validated = $validator->validated();

        try {
            [$user, $adminUser] = DB::transaction(function () use ($validated): array {
                $setup = PortalSetup::query()->whereKey(1)->lockForUpdate()->firstOrFail();

                abort_if($setup->completed_at !== null, 404);

                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);
                $user->forceFill(['must_change_password' => true])->save();

                $user->assignRole(config('portal.roles.super_admin', 'Super Admin'));

                $adminUser = User::query()->create([
                    'name' => trim($validated['admin_name']),
                    'email' => $validated['admin_email'],
                    'password' => $validated['admin_password'],
                ]);
                $adminUser->forceFill(['must_change_password' => true])->save();
                $adminUser->assignRole(config('portal.roles.admin', 'Admin'));

                $setup->forceFill([
                    'completed_at' => now(),
                    'completed_by' => $user->getKey(),
                ])->save();

                return [$user, $adminUser];
            }, attempts: 3);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $audit->record('setup.super_admin_initialized', $user, ['account', 'role'], (int) $user->getKey());
        $audit->record('setup.initial_admin_created', $adminUser, ['account', 'role'], (int) $adminUser->getKey());

        return redirect()->route('admin.dashboard');
    }

    private function ensureSetupIsAvailable(): void
    {
        $configuredKeyHash = (string) config('portal.setup_key_hash');

        // Require a 32-byte random key encoded as 64 hexadecimal characters.
        abort_unless(preg_match('/\A[a-fA-F0-9]{64}\z/', $configuredKeyHash) === 1, 404);
        abort_unless(PortalSetup::query()->whereKey(1)->whereNull('completed_at')->exists(), 404);
    }
}
