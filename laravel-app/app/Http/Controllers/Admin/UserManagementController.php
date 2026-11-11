<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStatusRequest;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Support\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive'],
            'role' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);

        $users = User::query()->with('roles')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($filters['role'] ?? null, fn ($query, $roleId) => $query->whereHas('roles', fn ($roles) => $roles->whereKey($roleId)))
            ->orderBy('name')->paginate(20)->withQueryString();

        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get();
        $protectedRoleName = config('portal.roles.super_admin', 'Super Admin');

        return view('admin.users.index', compact('users', 'roles', 'filters', 'protectedRoleName'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => new User(['is_active' => true]),
            'roles' => $this->assignableRoles()->get(),
            'permissionGroups' => $this->assignablePermissionGroups(),
            'isEditing' => false,
        ]);
    }

    public function store(UserStoreRequest $request, AuditLogger $audit): RedirectResponse
    {
        $attributes = $request->validated();
        $roleIds = $attributes['roles'] ?? [];
        $permissionNames = array_key_exists('permissions_present', $attributes) ? ($attributes['permissions'] ?? []) : null;
        unset($attributes['roles']);
        unset($attributes['permissions_present'], $attributes['permissions']);

        if ($roleIds !== []) {
            abort_unless($request->user()->can('users.assign'), 403);
            $roles = $this->assignableRoles()->whereIn('id', $roleIds)->get();
            abort_unless($roles->count() === count($roleIds), 422);
        } else {
            $roles = collect();
        }

        if ($permissionNames !== null) {
            abort_unless($request->user()->can('users.assign'), 403);
        }

        $managedUser = DB::transaction(function () use ($attributes, $roles, $permissionNames): User {
            $managedUser = User::query()->create([
                'name' => trim($attributes['name']),
                'email' => mb_strtolower(trim($attributes['email'])),
                'password' => $attributes['password'],
            ]);
            $managedUser->forceFill(['must_change_password' => true, 'is_active' => true])->save();
            $managedUser->syncRoles($roles);
            if ($permissionNames !== null) {
                $managedUser->syncPermissions($permissionNames);
            }

            return $managedUser;
        });
        if ($roleIds !== []) {
            $audit->record('users.roles_synced', $managedUser, ['roles']);
        }
        if ($permissionNames !== null) {
            $audit->record('users.permissions_synced', $managedUser, ['permissions']);
        }

        return to_route('admin.users.edit', $managedUser)->with('status', __('admin.users.saved'));
    }

    public function edit(User $user): View
    {
        abort_if($this->isProtectedSuperAdmin($user), 403);

        return view('admin.users.form', [
            'managedUser' => $user->load('roles'),
            'roles' => $this->assignableRoles()->get(),
            'permissionGroups' => $this->assignablePermissionGroups(),
            'directPermissions' => $user->getDirectPermissions()->pluck('name')->all(),
            'isEditing' => true,
        ]);
    }

    public function update(UserUpdateRequest $request, User $user, AuditLogger $audit): RedirectResponse
    {
        abort_if($this->isProtectedSuperAdmin($user), 403);
        $attributes = $request->validated();
        $roleIds = array_key_exists('roles_present', $attributes) ? ($attributes['roles'] ?? []) : null;
        $permissionNames = array_key_exists('permissions_present', $attributes) ? ($attributes['permissions'] ?? []) : null;
        unset($attributes['roles_present']);
        unset($attributes['roles']);
        unset($attributes['permissions_present'], $attributes['permissions']);
        $roles = collect();

        if ($roleIds !== null) {
            abort_unless($request->user()->can('users.assign'), 403);
            $roles = $this->assignableRoles()->whereIn('id', $roleIds)->get();
            abort_unless($roles->count() === count($roleIds), 422);
            if ((int) $request->user()->id === (int) $user->id) {
                $currentRoleIds = $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                $requestedRoleIds = array_map('intval', $roleIds);
                sort($requestedRoleIds);
                abort_unless($currentRoleIds === $requestedRoleIds, 403);
            }
        }

        if ($permissionNames !== null) {
            abort_unless($request->user()->can('users.assign'), 403);
            if ((int) $request->user()->id === (int) $user->id) {
                $currentPermissions = $user->getDirectPermissions()->pluck('name')->sort()->values()->all();
                $requestedPermissions = array_values(array_unique($permissionNames));
                sort($requestedPermissions);
                abort_unless($currentPermissions === $requestedPermissions, 403);
            }
        }

        DB::transaction(function () use ($attributes, $roleIds, $roles, $permissionNames, $user): void {
            $emailChanged = mb_strtolower(trim($attributes['email'])) !== mb_strtolower($user->email);
            $user->fill([
                'name' => trim($attributes['name']),
                'email' => mb_strtolower(trim($attributes['email'])),
            ]);
            if ($emailChanged) {
                $user->email_verified_at = null;
            }
            $user->save();

            if ($roleIds !== null) {
                $user->syncRoles($roles);
            }
            if ($permissionNames !== null) {
                $user->syncPermissions($permissionNames);
            }
        });
        if ($roleIds !== null) {
            $audit->record('users.roles_synced', $user, ['roles']);
        }
        if ($permissionNames !== null) {
            $audit->record('users.permissions_synced', $user, ['permissions']);
        }

        return to_route('admin.users.edit', $user)->with('status', __('admin.users.saved'));
    }

    public function updateStatus(UserStatusRequest $request, User $user, AuditLogger $audit): RedirectResponse
    {
        abort_if($this->isProtectedSuperAdmin($user), 403);
        abort_if((int) $request->user()->id === (int) $user->id && ! $request->boolean('is_active'), 403);

        $user->forceFill(['is_active' => $request->boolean('is_active')])->save();
        $audit->record('users.status_changed', $user, ['is_active']);

        return to_route('admin.users.index')->with('status', __('admin.users.status_saved'));
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Role> */
    private function assignableRoles()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '<>', config('portal.roles.super_admin', 'Super Admin'))
            ->orderBy('name');
    }

    private function isProtectedSuperAdmin(User $user): bool
    {
        return $user->hasRole(config('portal.roles.super_admin', 'Super Admin'));
    }

    /** @return array<string, \Illuminate\Support\Collection<int, Permission>> */
    private function assignablePermissionGroups(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', RolePermissionSeeder::assignablePermissionNames())
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => explode('.', $permission->name, 2)[0])
            ->all();
    }
}
