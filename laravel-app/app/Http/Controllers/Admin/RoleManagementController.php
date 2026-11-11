<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleStoreRequest;
use App\Http\Requests\Admin\RoleUpdateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Support\AuditLogger;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->where('guard_name', 'web')->with('permissions')->withCount('users')->orderBy('name')->paginate(20);
        $protectedRoleName = config('portal.roles.super_admin', 'Super Admin');
        $builtInAdminName = config('portal.roles.admin', 'Admin');

        return view('admin.roles.index', compact('roles', 'protectedRoleName', 'builtInAdminName'));
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role(['guard_name' => 'web']),
            'permissionGroups' => $this->permissionGroups(),
            'isEditing' => false,
            'isProtected' => false,
            'builtInAdminName' => config('portal.roles.admin', 'Admin'),
        ]);
    }

    public function store(RoleStoreRequest $request, AuditLogger $audit): RedirectResponse
    {
        $attributes = $request->validated();
        $name = trim($attributes['name']);
        $this->assertRoleNameIsNotReserved($name);
        $permissionIds = array_map('intval', $attributes['permissions'] ?? []);
        if ($permissionIds !== []) {
            abort_unless($request->user()->can('roles.assign'), 403);
        }
        $permissions = Permission::query()->where('guard_name', 'web')->whereIn('id', $permissionIds)->get();
        abort_unless($permissions->count() === count(array_unique($permissionIds)), 422);

        $role = DB::transaction(function () use ($name, $permissions): Role {
            $role = Role::query()->create(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);

            return $role;
        });
        $audit->record('roles.permissions_synced', $role, ['permissions']);

        return to_route('admin.roles.edit', $role)->with('status', __('admin.roles.saved'));
    }

    public function edit(Role $role): View
    {
        $this->ensureRoleCanBeEdited($role);
        $role->load('permissions');

        return view('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'isEditing' => true,
            'isProtected' => false,
            'builtInAdminName' => config('portal.roles.admin', 'Admin'),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role, AuditLogger $audit): RedirectResponse
    {
        $this->ensureRoleCanBeEdited($role);
        $attributes = $request->validated();
        $name = trim($attributes['name']);
        $this->assertRoleNameIsNotReserved($name, $role);
        $permissionIds = array_map('intval', $attributes['permissions'] ?? []);
        $currentPermissionIds = $role->permissions()->pluck('permissions.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $requestedPermissionIds = array_values(array_unique($permissionIds));
        sort($requestedPermissionIds);
        if ($currentPermissionIds !== $requestedPermissionIds) {
            abort_unless($request->user()->can('roles.assign'), 403);
        }
        $permissions = Permission::query()->where('guard_name', 'web')->whereIn('id', $permissionIds)->get();
        abort_unless($permissions->count() === count(array_unique($permissionIds)), 422);

        DB::transaction(function () use ($role, $name, $permissions): void {
            $role->name = $name;
            $role->save();
            $role->syncPermissions($permissions);
        });
        $audit->record('roles.permissions_synced', $role, ['permissions']);

        return to_route('admin.roles.edit', $role)->with('status', __('admin.roles.saved'));
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('roles.delete'), 403);
        abort_unless($role->guard_name === 'web', 404);
        abort_if($this->isProtectedSuperAdmin($role), 403);

        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => __('admin.roles.in_use')]);
        }

        $role->delete();

        return to_route('admin.roles.index')->with('status', __('admin.roles.deleted'));
    }

    /** @return array<string, \Illuminate\Support\Collection<int, Permission>> */
    private function permissionGroups(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => explode('.', $permission->name, 2)[0])
            ->all();
    }

    private function ensureRoleCanBeEdited(Role $role): void
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if($this->isProtectedSuperAdmin($role), 403);
    }

    private function isReservedRole(Role $role): bool
    {
        return in_array($role->name, [
            config('portal.roles.super_admin', 'Super Admin'),
            config('portal.roles.admin', 'Admin'),
        ], true);
    }

    private function isProtectedSuperAdmin(Role $role): bool
    {
        return $role->name === config('portal.roles.super_admin', 'Super Admin');
    }

    private function assertRoleNameIsNotReserved(string $name, ?Role $currentRole = null): void
    {
        $superAdminName = config('portal.roles.super_admin', 'Super Admin');
        $adminName = config('portal.roles.admin', 'Admin');
        $isExistingAdminRoleName = $currentRole?->name === $adminName && $name === $adminName;

        if ($name === $superAdminName || ($name === $adminName && ! $isExistingAdminRoleName)) {
            throw ValidationException::withMessages(['name' => __('admin.roles.reserved_name')]);
        }
    }
}
