<?php

namespace App\Console\Commands;

use App\Services\RbacMirror;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacRepairMirrorsCommand extends Command
{
    protected $signature = 'rbac:repair-mirrors {--dry-run : Solo muestra diferencias, no aplica cambios}';

    protected $description = 'Repara roles/permisos para mantener espejo entre guards web y api';

    public function handle(): int
    {
        [$missingPermissions, $missingRoles, $driftedRoles] = $this->analyzeState();

        $this->info('Estado RBAC web/api:');
        $this->line("- permisos faltantes en algun guard: {$missingPermissions}");
        $this->line("- roles faltantes en algun guard: {$missingRoles}");
        $this->line("- roles con permisos desalineados: {$driftedRoles}");

        if ((bool) $this->option('dry-run')) {
            $this->comment('Modo dry-run: no se aplicaron cambios.');
            return self::SUCCESS;
        }

        /** @var RbacMirror $mirror */
        $mirror = app(RbacMirror::class);

        $stats = DB::transaction(static fn () => $mirror->repairUsingUnion());

        [$missingPermissionsAfter, $missingRolesAfter, $driftedRolesAfter] = $this->analyzeState();

        $this->info('Cambios aplicados:');
        $this->line("- permissions_created: {$stats['permissions_created']}");
        $this->line("- roles_created: {$stats['roles_created']}");
        $this->line("- roles_synced: {$stats['roles_synced']}");

        $this->info('Estado final:');
        $this->line("- permisos faltantes en algun guard: {$missingPermissionsAfter}");
        $this->line("- roles faltantes en algun guard: {$missingRolesAfter}");
        $this->line("- roles con permisos desalineados: {$driftedRolesAfter}");

        return self::SUCCESS;
    }

    /**
     * @return array{int,int,int}
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
            ->unique()
            ->values();

        $missingRoles = 0;
        $driftedRoles = 0;

        foreach ($roleNames as $name) {
            $roleWeb = Role::query()->where('name', $name)->where('guard_name', 'web')->first();
            $roleApi = Role::query()->where('name', $name)->where('guard_name', 'api')->first();

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

        return [$missingPermissions, $missingRoles, $driftedRoles];
    }
}
