<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\RbacMirror;
use App\Support\RoleName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RbacRepairMirrorsCommand extends Command
{
    protected $signature = 'rbac:repair-mirrors {--dry-run : Solo muestra diferencias, no aplica cambios}';

    protected $description = 'Repara roles/permisos para mantener espejo entre guards web y api';

    public function handle(): int
    {
        [$missingPermissions, $missingRoles, $driftedRoles, $nonNormalizedRoles, $duplicatedRoles] = $this->analyzeState();

        $this->info('Estado RBAC web/api:');
        $this->line("- permisos faltantes en algun guard: {$missingPermissions}");
        $this->line("- roles faltantes en algun guard: {$missingRoles}");
        $this->line("- roles con permisos desalineados: {$driftedRoles}");
        $this->line("- roles con nombre no normalizado: {$nonNormalizedRoles}");
        $this->line("- roles duplicados por capitalizacion: {$duplicatedRoles}");

        if ((bool) $this->option('dry-run')) {
            $this->comment('Modo dry-run: no se aplicaron cambios.');
            return self::SUCCESS;
        }

        /** @var RbacMirror $mirror */
        $mirror = app(RbacMirror::class);

        $stats = DB::transaction(static fn () => $mirror->repairUsingUnion());

        $this->info('Cambios aplicados:');
        $this->line("- permissions_created: {$stats['permissions_created']}");
        $this->line("- roles_created: {$stats['roles_created']}");
        $this->line("- roles_synced: {$stats['roles_synced']}");
        $this->line("- roles_renamed: {$stats['roles_renamed']}");
        $this->line("- roles_merged: {$stats['roles_merged']}");

        [$missingPermissionsAfter, $missingRolesAfter, $driftedRolesAfter, $nonNormalizedRolesAfter, $duplicatedRolesAfter] = $this->analyzeState();

        $this->info('Estado final:');
        $this->line("- permisos faltantes en algun guard: {$missingPermissionsAfter}");
        $this->line("- roles faltantes en algun guard: {$missingRolesAfter}");
        $this->line("- roles con permisos desalineados: {$driftedRolesAfter}");
        $this->line("- roles con nombre no normalizado: {$nonNormalizedRolesAfter}");
        $this->line("- roles duplicados por capitalizacion: {$duplicatedRolesAfter}");

        return self::SUCCESS;
    }

    /**
     * @return array{int,int,int,int,int}
     */
    private function analyzeState(): array
    {
        $guards = ['web', 'api'];

        $permissionNames = Permission::query()
            ->whereIn('guard_name', $guards)
            ->pluck('name')
            ->unique()
            ->values();

        $missingPermissions = 0;
        foreach ($permissionNames as $name) {
            foreach ($guards as $guard) {
                $exists = Permission::query()
                    ->where('name', $name)
                    ->where('guard_name', $guard)
                    ->exists();

                if (!$exists) {
                    $missingPermissions++;
                }
            }
        }

        $roleNames = Role::query()
            ->whereIn('guard_name', $guards)
            ->pluck('name')
            ->map(fn (string $name) => RoleName::normalize($name))
            ->unique()
            ->values();

        $roles = Role::query()
            ->whereIn('guard_name', $guards)
            ->get(['name', 'guard_name']);
        $nonNormalizedRoles = $roles
            ->filter(fn (Role $role) => $role->name !== RoleName::normalize($role->name))
            ->count();
        $duplicatedRoles = $roles
            ->groupBy(fn (Role $role) => $role->guard_name.'|'.RoleName::normalize($role->name))
            ->filter(fn ($group) => $group->count() > 1)
            ->sum(fn ($group) => $group->count() - 1);

        $missingRoles = 0;
        $driftedRoles = 0;

        foreach ($roleNames as $name) {
            $roleWeb = Role::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name])->where('guard_name', 'web')->first();
            $roleApi = Role::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name])->where('guard_name', 'api')->first();

            if (!$roleWeb || !$roleApi) {
                $missingRoles += (!$roleWeb ? 1 : 0) + (!$roleApi ? 1 : 0);
                continue;
            }

            $webPerms = $roleWeb->permissions()->pluck('name')->sort()->values()->all();
            $apiPerms = $roleApi->permissions()->pluck('name')->sort()->values()->all();

            if ($webPerms !== $apiPerms) {
                $driftedRoles++;
            }
        }

        return [$missingPermissions, $missingRoles, $driftedRoles, $nonNormalizedRoles, $duplicatedRoles];
    }
}
