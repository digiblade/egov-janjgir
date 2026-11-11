<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /** @var array<string, list<string>> */
    private const FEATURE_ACTIONS = [
        'homepage' => ['view', 'create', 'edit', 'delete', 'publish'],
        'about' => ['view', 'create', 'edit', 'delete', 'publish'],
        'banners' => ['view', 'create', 'edit', 'delete', 'upload', 'publish'],
        'kpis' => ['view', 'create', 'edit', 'delete', 'publish'],
        'notices' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'export'],
        'announcements' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'export'],
        'documents' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'export', 'assign'],
        'training' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'export', 'assign'],
        'faqs' => ['view', 'create', 'edit', 'delete', 'publish'],
        'team' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'assign'],
        'reports' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'export', 'assign'],
        'gallery' => ['view', 'create', 'edit', 'delete', 'upload', 'publish', 'assign'],
        'contact' => ['view', 'edit'],
        'support' => ['view', 'edit', 'assign', 'export'],
        'users' => ['view', 'create', 'edit', 'disable', 'assign'],
        'roles' => ['view', 'create', 'edit', 'delete', 'assign'],
        'settings' => ['view', 'edit'],
        'media' => ['view', 'upload', 'publish', 'delete', 'view_private'],
        'audit-logs' => ['view', 'export'],
    ];

    /** @return list<string> */
    public static function permissionNames(): array
    {
        return collect(['dashboard.view'])
            ->merge(collect(self::FEATURE_ACTIONS)->flatMap(
                fn (array $actions, string $feature): array => array_map(
                    fn (string $action): string => "{$feature}.{$action}",
                    $actions,
                ),
            ))
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<string> */
    public static function assignablePermissionNames(): array
    {
        return collect(self::permissionNames())
            ->reject(function (string $permission): bool {
                [$feature] = explode('.', $permission, 2);

                return in_array($feature, ['users', 'roles', 'settings', 'audit-logs'], true)
                    || $permission === 'media.view_private';
            })
            ->values()
            ->all();
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::permissionNames())
            ->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $superAdmin = Role::findOrCreate(config('portal.roles.super_admin', 'Super Admin'), 'web');
        $admin = Role::findOrCreate(config('portal.roles.admin', 'Admin'), 'web');

        $superAdmin->syncPermissions($permissions);

        $adminPermissions = $permissions->filter(function (Permission $permission): bool {
            [$feature] = explode('.', $permission->name, 2);

            return $feature === 'dashboard'
                || in_array($feature, [
                    'homepage', 'about', 'banners', 'kpis', 'notices', 'announcements',
                    'documents', 'training', 'faqs', 'team', 'reports', 'gallery', 'contact', 'support', 'media',
                ], true);
        });

        $admin->syncPermissions($adminPermissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
