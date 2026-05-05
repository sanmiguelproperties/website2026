<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guards = config('rbac.guards', ['web', 'api']);
        $permissions = config('rbac.permissions', []);
        $roles = config('rbac.roles', []);
        $roleNames = array_keys($roles);

        foreach ($guards as $guard) {
            Role::where('guard_name', $guard)
                ->whereNotIn('name', $roleNames)
                ->get()
                ->each(fn (Role $role) => $role->delete());

            Permission::where('guard_name', $guard)
                ->whereNotIn('name', $permissions)
                ->get()
                ->each(fn (Permission $permission) => $permission->delete());

            foreach ($permissions as $name) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        foreach ($guards as $guard) {
            $allGuardPermissions = Permission::where('guard_name', $guard)->get();

            foreach ($roles as $name => $definition) {
                $role = Role::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);

                $rolePermissions = ($definition['permissions'] ?? []) === '*'
                    ? $allGuardPermissions
                    : Permission::where('guard_name', $guard)
                        ->whereIn('name', $definition['permissions'] ?? [])
                        ->get();

                $role->syncPermissions($rolePermissions);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
