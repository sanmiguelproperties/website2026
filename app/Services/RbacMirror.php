<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RbacMirror
{
    private const GUARD_WEB = 'web';
    private const GUARD_API = 'api';
    private const BOTH_GUARDS = [self::GUARD_WEB, self::GUARD_API];

    private function otherGuard(string $guard): string
    {
        return $guard === self::GUARD_WEB ? self::GUARD_API : self::GUARD_WEB;
    }

    private function forgetCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function mirrorPermissionCreated(Permission $permission): void
    {
        $targetGuard = $this->otherGuard($permission->guard_name);
        Permission::firstOrCreate([
            'name' => $permission->name,
            'guard_name' => $targetGuard,
        ]);
        $this->forgetCache();
    }

    public function mirrorPermissionUpdated(Permission $permission, ?string $oldName = null, ?string $oldGuard = null): void
    {
        $targetGuard = $this->otherGuard($permission->guard_name);
        $lookupName = $oldName ?? $permission->name;
        $counterpart = Permission::where('name', $lookupName)->where('guard_name', $targetGuard)->first();
        if (!$counterpart) {
            $counterpart = Permission::firstOrCreate([
                'name' => $lookupName,
                'guard_name' => $targetGuard,
            ]);
        }
        if ($counterpart->name !== $permission->name) {
            $counterpart->name = $permission->name;
            $counterpart->save();
        }

        // Mantener sincronizadas las relaciones permiso-rol en ambos guards.
        $sourceRoles = $permission->roles()->get();
        $targetRoles = $this->mapRolesToGuardByName($sourceRoles, $targetGuard, true);
        $counterpart->roles()->sync($targetRoles->pluck('id')->all());

        $this->forgetCache();
    }

    public function mirrorPermissionDeleted(Permission $permission): void
    {
        $targetGuard = $this->otherGuard($permission->guard_name);
        $counterpart = Permission::where('name', $permission->name)->where('guard_name', $targetGuard)->first();
        if ($counterpart) {
            $counterpart->delete();
        }
        $this->forgetCache();
    }

    public function mirrorRoleCreated(Role $role): void
    {
        $targetGuard = $this->otherGuard($role->guard_name);
        $targetRole = Role::firstOrCreate([
            'name' => $role->name,
            'guard_name' => $targetGuard,
        ]);

        $this->synchronizeCounterpartRolePermissions($role, $targetRole, $targetGuard);
        $this->forgetCache();
    }

    public function mirrorRoleUpdated(Role $role, ?string $oldName = null, ?string $oldGuard = null): void
    {
        $targetGuard = $this->otherGuard($role->guard_name);
        $lookupName = $oldName ?? $role->name;
        $counterpart = Role::where('name', $lookupName)->where('guard_name', $targetGuard)->first();
        if (!$counterpart) {
            $counterpart = Role::firstOrCreate([
                'name' => $lookupName,
                'guard_name' => $targetGuard,
            ]);
        }
        if ($counterpart->name !== $role->name) {
            $counterpart->name = $role->name;
            $counterpart->save();
        }

        // Si faltaba el rol espejo, dejarlo con los mismos permisos por nombre.
        $this->synchronizeCounterpartRolePermissions($role, $counterpart, $targetGuard);

        $this->forgetCache();
    }

    public function mirrorRoleDeleted(Role $role): void
    {
        $targetGuard = $this->otherGuard($role->guard_name);
        $counterpart = Role::where('name', $role->name)->where('guard_name', $targetGuard)->first();
        if ($counterpart) {
            $counterpart->delete();
        }
        $this->forgetCache();
    }

    public function attachPermissions(Role $sourceRole, Collection $sourcePermissions): void
    {
        $targetGuard = $this->otherGuard($sourceRole->guard_name);
        $targetRole = $this->ensureRoleMirror($sourceRole, $targetGuard);
        $targetPerms = $this->mapPermissionsToGuardByName($sourcePermissions, $targetGuard, true);
        if ($targetPerms->isNotEmpty()) {
            $targetRole->givePermissionTo($targetPerms->all());
        }
        $this->forgetCache();
    }

    public function syncPermissions(Role $sourceRole, Collection $sourcePermissions): void
    {
        $targetGuard = $this->otherGuard($sourceRole->guard_name);
        $targetRole = $this->ensureRoleMirror($sourceRole, $targetGuard);
        $targetPerms = $this->mapPermissionsToGuardByName($sourcePermissions, $targetGuard, true);
        $targetRole->syncPermissions($targetPerms->all());
        $this->forgetCache();
    }

    public function detachPermissions(Role $sourceRole, Collection $sourcePermissions): void
    {
        $targetGuard = $this->otherGuard($sourceRole->guard_name);
        $targetRole = $this->ensureRoleMirror($sourceRole, $targetGuard);
        $targetPerms = $this->mapPermissionsToGuardByName($sourcePermissions, $targetGuard, false);
        if ($targetPerms->isNotEmpty()) {
            $targetRole->revokePermissionTo($targetPerms->all());
        }
        $this->forgetCache();
    }

    private function ensureRoleMirror(Role $sourceRole, string $targetGuard): Role
    {
        return Role::firstOrCreate([
            'name' => $sourceRole->name,
            'guard_name' => $targetGuard,
        ]);
    }

    private function synchronizeCounterpartRolePermissions(Role $sourceRole, Role $targetRole, string $targetGuard): void
    {
        $sourcePerms = $sourceRole->permissions()->get();
        $targetPerms = $this->mapPermissionsToGuardByName($sourcePerms, $targetGuard, true);
        $targetRole->syncPermissions($targetPerms->all());
    }

    private function mapPermissionsToGuardByName(Collection $sourcePermissions, string $targetGuard, bool $createMissing = false): Collection
    {
        $names = $sourcePermissions->pluck('name')->unique()->values();
        $existing = Permission::whereIn('name', $names)->where('guard_name', $targetGuard)->get()->keyBy('name');

        $result = collect();
        foreach ($names as $name) {
            $perm = $existing->get($name);
            if (!$perm && $createMissing) {
                $perm = Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $targetGuard,
                ]);
            }
            if ($perm) {
                $result->push($perm);
            }
        }

        return $result;
    }

    private function mapRolesToGuardByName(Collection $sourceRoles, string $targetGuard, bool $createMissing = false): Collection
    {
        $names = $sourceRoles->pluck('name')->unique()->values();
        $existing = Role::whereIn('name', $names)->where('guard_name', $targetGuard)->get()->keyBy('name');

        $result = collect();
        foreach ($names as $name) {
            $role = $existing->get($name);
            if (!$role && $createMissing) {
                $role = Role::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $targetGuard,
                ]);
            }
            if ($role) {
                $result->push($role);
            }
        }

        return $result;
    }

    /**
     * Sincroniza roles por nombre para ambos guards (web y api) en el usuario dado.
     * Reemplaza TODOS los roles del usuario por el conjunto provisto, duplicándolos en ambos guards.
     *
     * Ejemplo: ['admin', 'editor'] => el usuario quedará con:
     * - roles 'admin' y 'editor' en guard 'web'
     * - roles 'admin' y 'editor' en guard 'api'
     *
     * Nota: Crea automáticamente los roles faltantes en el guard destino.
     */
    public function syncUserRolesBothGuardsByNames(User $user, array $roleNames): void
    {
        $names = collect($roleNames)
            ->map(fn($v) => trim((string) $v))
            ->filter(fn($v) => $v !== '')
            ->unique()
            ->values();

        // Si la lista viene vacía, se eliminan todos los roles (en ambos guards)
        if ($names->isEmpty()) {
            $user->roles()->sync([]);
            $this->forgetCache();
            return;
        }

        $guards = ['web', 'api'];
        $roleIds = [];

        foreach ($names as $name) {
            foreach ($guards as $guard) {
                $role = Role::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
                $roleIds[] = $role->id;
            }
        }

        // Sincronizar directamente la relación para incluir roles de ambos guards
        $user->roles()->sync($roleIds);

        $this->forgetCache();
    }

    /**
     * Repara datos legados para dejar web/api con los mismos nombres de roles/permisos
     * y las mismas relaciones rol-permiso (usando la union de ambos guards).
     *
     * @return array{permissions_created:int,roles_created:int,roles_synced:int}
     */
    public function repairUsingUnion(): array
    {
        $permissionNames = Permission::whereIn('guard_name', self::BOTH_GUARDS)
            ->pluck('name')
            ->unique()
            ->values();

        $permissionsCreated = 0;
        foreach ($permissionNames as $name) {
            foreach (self::BOTH_GUARDS as $guard) {
                $permission = Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
                if ($permission->wasRecentlyCreated) {
                    $permissionsCreated++;
                }
            }
        }

        $roleNames = Role::whereIn('guard_name', self::BOTH_GUARDS)
            ->pluck('name')
            ->unique()
            ->values();

        $rolesCreated = 0;
        foreach ($roleNames as $name) {
            foreach (self::BOTH_GUARDS as $guard) {
                $role = Role::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
                if ($role->wasRecentlyCreated) {
                    $rolesCreated++;
                }
            }
        }

        $rolesSynced = 0;
        foreach ($roleNames as $roleName) {
            $unionPermissionNames = collect();

            foreach (self::BOTH_GUARDS as $guard) {
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if (!$role) {
                    continue;
                }
                $unionPermissionNames = $unionPermissionNames->merge(
                    $role->permissions()->pluck('name')
                );
            }

            $unionPermissionNames = $unionPermissionNames->unique()->values();

            foreach (self::BOTH_GUARDS as $guard) {
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if (!$role) {
                    continue;
                }

                $targetPermissions = Permission::where('guard_name', $guard)
                    ->whereIn('name', $unionPermissionNames)
                    ->get();

                $role->syncPermissions($targetPermissions->all());
                $rolesSynced++;
            }
        }

        $this->forgetCache();

        return [
            'permissions_created' => $permissionsCreated,
            'roles_created' => $rolesCreated,
            'roles_synced' => $rolesSynced,
        ];
    }
}
