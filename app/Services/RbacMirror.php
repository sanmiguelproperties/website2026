<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Support\RoleName;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

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
        $targetRole = $this->firstOrCreateRole($role->name, $targetGuard);

        $this->synchronizeCounterpartRolePermissions($role, $targetRole, $targetGuard);
        $this->forgetCache();
    }

    public function mirrorRoleUpdated(Role $role, ?string $oldName = null, ?string $oldGuard = null): void
    {
        $targetGuard = $this->otherGuard($role->guard_name);
        $lookupName = $oldName ?? $role->name;
        $counterpart = $this->findRoleByName($lookupName, $targetGuard);
        if (! $counterpart) {
            $counterpart = $this->firstOrCreateRole($lookupName, $targetGuard);
        }
        $normalizedName = RoleName::normalize($role->name);
        if ($counterpart->name !== $normalizedName) {
            $counterpart->name = $normalizedName;
            $counterpart->save();
        }

        // Si faltaba el rol espejo, dejarlo con los mismos permisos por nombre.
        $this->synchronizeCounterpartRolePermissions($role, $counterpart, $targetGuard);

        $this->forgetCache();
    }

    public function mirrorRoleDeleted(Role $role): void
    {
        $targetGuard = $this->otherGuard($role->guard_name);
        $counterpart = $this->findRoleByName($role->name, $targetGuard);
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
        return $this->firstOrCreateRole($sourceRole->name, $targetGuard);
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
        $names = collect(RoleName::normalizeMany($sourceRoles->pluck('name')));

        $result = collect();
        foreach ($names as $name) {
            $role = $this->findRoleByName($name, $targetGuard);
            if (! $role && $createMissing) {
                $role = $this->firstOrCreateRole($name, $targetGuard);
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
        $names = collect(RoleName::normalizeMany($roleNames));

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
                $role = $this->firstOrCreateRole($name, $guard);
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
     * @return array{permissions_created:int,roles_created:int,roles_synced:int,roles_renamed:int,roles_merged:int}
     */
    public function repairUsingUnion(): array
    {
        $normalization = app(RoleNameNormalizer::class)->normalizeExistingRoles();
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
                $role = $this->firstOrCreateRole($name, $guard);
                if ($role->wasRecentlyCreated) {
                    $rolesCreated++;
                }
            }
        }

        $rolesSynced = 0;
        foreach ($roleNames as $roleName) {
            $unionPermissionNames = collect();

            foreach (self::BOTH_GUARDS as $guard) {
                $role = $this->findRoleByName($roleName, $guard);
                if (!$role) {
                    continue;
                }
                $unionPermissionNames = $unionPermissionNames->merge(
                    $role->permissions()->pluck('name')
                );
            }

            $unionPermissionNames = $unionPermissionNames->unique()->values();

            foreach (self::BOTH_GUARDS as $guard) {
                $role = $this->findRoleByName($roleName, $guard);
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
            'roles_renamed' => $normalization['roles_renamed'],
            'roles_merged' => $normalization['roles_merged'],
        ];
    }

    private function findRoleByName(string $name, string $guard): ?Role
    {
        return Role::query()
            ->where('guard_name', $guard)
            ->whereRaw('LOWER(TRIM(name)) = ?', [RoleName::normalize($name)])
            ->first();
    }

    private function firstOrCreateRole(string $name, string $guard): Role
    {
        return $this->findRoleByName($name, $guard) ?? Role::create([
            'name' => RoleName::normalize($name),
            'guard_name' => $guard,
        ]);
    }
}
