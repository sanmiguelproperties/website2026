<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacGrantAdminAllCommand extends Command
{
    protected $signature = 'rbac:grant-admin-all
                            {--dry-run : Solo muestra lo que se haria, sin aplicar cambios}
                            {--replace-existing : Reemplaza todos los roles por admin(web/api)}
                            {--chunk=200 : Tamano de lote para procesar usuarios}';

    protected $description = 'Asigna rol admin (web y api) a todos los usuarios existentes';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $replaceExisting = (bool) $this->option('replace-existing');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $adminWeb = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $adminApi = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $totalUsers = User::query()->count();
        if ($totalUsers === 0) {
            $this->warn('No hay usuarios para procesar.');
            return self::SUCCESS;
        }

        $this->info('Iniciando asignacion masiva de admin...');
        $this->line("- usuarios totales: {$totalUsers}");
        $this->line("- modo: ".($dryRun ? 'dry-run' : 'ejecucion real'));
        $this->line('- estrategia: '.($replaceExisting ? 'reemplazar roles existentes' : 'agregar admin sin eliminar roles'));

        $processed = 0;
        $updated = 0;
        $alreadyComplete = 0;
        $missingWebBefore = 0;
        $missingApiBefore = 0;

        User::query()
            ->select(['id', 'name', 'email'])
            ->with(['roles:id'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($users) use (
                $dryRun,
                $replaceExisting,
                $adminWeb,
                $adminApi,
                &$processed,
                &$updated,
                &$alreadyComplete,
                &$missingWebBefore,
                &$missingApiBefore
            ) {
                foreach ($users as $user) {
                    $processed++;

                    $hasWebAdmin = $user->roles->contains('id', $adminWeb->id);
                    $hasApiAdmin = $user->roles->contains('id', $adminApi->id);

                    if (!$hasWebAdmin) {
                        $missingWebBefore++;
                    }
                    if (!$hasApiAdmin) {
                        $missingApiBefore++;
                    }

                    if ($hasWebAdmin && $hasApiAdmin && !$replaceExisting) {
                        $alreadyComplete++;
                        continue;
                    }

                    if ($dryRun) {
                        $updated++;
                        continue;
                    }

                    if ($replaceExisting) {
                        $user->roles()->sync([$adminWeb->id, $adminApi->id]);
                    } else {
                        $user->roles()->syncWithoutDetaching([$adminWeb->id, $adminApi->id]);
                    }

                    $updated++;
                }
            });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Resumen:');
        $this->line("- procesados: {$processed}");
        $this->line("- faltaba admin web antes: {$missingWebBefore}");
        $this->line("- faltaba admin api antes: {$missingApiBefore}");
        $this->line("- usuarios ya completos: {$alreadyComplete}");
        $this->line('- usuarios '.($dryRun ? 'a actualizar' : 'actualizados').": {$updated}");

        if ($dryRun) {
            $this->comment('No se aplicaron cambios porque se ejecuto con --dry-run.');
        } else {
            $this->info('Asignacion masiva completada.');
        }

        return self::SUCCESS;
    }
}
