<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\RoleNameNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class EmergencySuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $guards = ['web', 'api'];

        // Limpia caché de permisos/roles de Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(RoleNameNormalizer::class)->normalizeExistingRoles();

        $email = 'gusgusnoriega@gmail.com';
        $name = 'Gustavo Noriega';

        /**
         * Puedes definir esta variable en tu .env:
         * EMERGENCY_SUPERADMIN_PASSWORD=TuPasswordTemporal123!
         *
         * Si no existe, usará este valor por defecto.
         * Luego puedes cambiarla manualmente al ingresar.
         */
        $password = env('EMERGENCY_SUPERADMIN_PASSWORD', '852456357');

        foreach ($guards as $guard) {
            // Crear rol si no existe
            $role = Role::firstOrCreate([
                'name' => 'super-admin',
                'guard_name' => $guard,
            ]);

            // Tomar todos los permisos del guard correspondiente
            $permissions = Permission::query()
                ->where('guard_name', $guard)
                ->get();

            // Asignar todos los permisos actuales al rol
            $role->syncPermissions($permissions);
        }

        // Crear o actualizar usuario de emergencia
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // Asignar ambos roles
        foreach ($guards as $guard) {
            $role = Role::where('name', 'super-admin')
                ->where('guard_name', $guard)
                ->first();

            if ($role && !$user->hasRole('super-admin', $guard)) {
                $user->assignRole($role);
            }
        }

        // Limpia caché otra vez por seguridad
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($this->command) {
            $this->command->info('Seeder de emergencia ejecutado correctamente.');
            $this->command->info("Usuario listo: {$email}");
            $this->command->warn("Password usada: {$password}");
        }
    }
}
