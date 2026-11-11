<?php

namespace Database\Seeders;

use App\Models\PortalSetup;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class InitialAdminAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('portal.seed_accounts.admin_password');
        $superAdminPassword = config('portal.seed_accounts.super_admin_password');

        if (blank($adminPassword) && blank($superAdminPassword)) {
            $this->command?->info('Initial admin accounts skipped; configure both seed passwords in .env to create them.');

            return;
        }

        $emails = ['superadmin@nerotech.co.in', 'admin@nerotech.co.in'];
        $setup = PortalSetup::query()->whereKey(1)->first();
        $existingCount = User::query()->whereIn('email', $emails)->count();
        if ($setup?->completed_at !== null && $existingCount === count($emails)) {
            $this->command?->info('Initial administrator setup is already complete; existing accounts were left unchanged.');

            return;
        }

        $validated = Validator::make([
            'admin_password' => $adminPassword,
            'super_admin_password' => $superAdminPassword,
        ], [
            'admin_password' => ['required', 'string', Password::min(14)->mixedCase()->numbers()->symbols()],
            'super_admin_password' => ['required', 'string', Password::min(14)->mixedCase()->numbers()->symbols()],
        ])->validate();

        if ($existingCount > 0) {
            throw new RuntimeException('An initial administrator email already exists. The seeder will not overwrite existing accounts.');
        }

        [$superAdmin, $admin] = DB::transaction(function () use ($validated): array {
            $setup = PortalSetup::query()->whereKey(1)->lockForUpdate()->first();
            if (! $setup || $setup->completed_at !== null) {
                throw new RuntimeException('Initial administrator accounts can only be seeded before portal setup is completed.');
            }

            $superAdmin = User::query()->create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@nerotech.co.in',
                'password' => $validated['super_admin_password'],
            ]);
            $superAdmin->forceFill(['must_change_password' => true, 'is_active' => true])->save();
            $superAdmin->assignRole(config('portal.roles.super_admin', 'Super Admin'));

            $admin = User::query()->create([
                'name' => 'Administrator',
                'email' => 'admin@nerotech.co.in',
                'password' => $validated['admin_password'],
            ]);
            $admin->forceFill(['must_change_password' => true, 'is_active' => true])->save();
            $admin->assignRole(config('portal.roles.admin', 'Admin'));

            $setup->forceFill([
                'completed_at' => now(),
                'completed_by' => $superAdmin->getKey(),
            ])->save();

            return [$superAdmin, $admin];
        }, attempts: 3);

        $audit = app(AuditLogger::class);
        $audit->record('setup.super_admin_initialized', $superAdmin, ['account', 'role'], (int) $superAdmin->getKey());
        $audit->record('setup.initial_admin_created', $admin, ['account', 'role'], (int) $superAdmin->getKey());

        $this->command?->info('Initial super-admin and admin accounts created. Both accounts must change their passwords at first sign-in.');
    }
}
