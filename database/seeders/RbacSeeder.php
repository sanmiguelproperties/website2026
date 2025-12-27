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
        // Limpia la caché de permisos/roles
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Guards a usar
        $guards = ['web', 'api'];

        // Permisos de ejemplo
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'settings.manage',
            'view.all.media',
        ];

        // Crear permisos para cada guard
        foreach ($guards as $guard) {
            foreach ($permissions as $name) {
                Permission::firstOrCreate([
                    'name'       => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        // Crear roles para cada guard
        foreach ($guards as $guard) {
            $admin  = Role::firstOrCreate(['name' => 'admin',  'guard_name' => $guard]);
            $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
            $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);

            // Relaciones rol-permisos para este guard
            $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

            $editor->syncPermissions(Permission::where('guard_name', $guard)->whereIn('name', [
                'posts.view', 'posts.create', 'posts.edit',
            ])->get());

            $viewer->syncPermissions(Permission::where('guard_name', $guard)->whereIn('name', [
                'posts.view', 'users.view',
            ])->get());
        }

        // Limpia caché nuevamente por seguridad
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}