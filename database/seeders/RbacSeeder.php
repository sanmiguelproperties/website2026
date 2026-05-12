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

        foreach ($guards as $guard) {
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
