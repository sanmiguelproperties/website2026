<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageDiagnosticCommand extends Command
{
    protected $signature = 'storage:diagnostic';
    protected $description = 'Diagnostica la configuración de storage, symlinks y rutas de imágenes';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║        DIAGNÓSTICO DE STORAGE & RUTAS DE IMÁGENES       ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');

        // 1. Variables de entorno
        $this->warn('═══ 1. VARIABLES DE ENTORNO ═══');
        $appUrl = config('app.url');
        $assetUrl = config('app.asset_url');
        $filesystemDisk = config('filesystems.default');
        $publicDiskUrl = config('filesystems.disks.public.url');

        $this->line("  APP_URL          = {$appUrl}");
        $this->line("  ASSET_URL        = " . ($assetUrl ?: '(no definida)'));
        $this->line("  FILESYSTEM_DISK  = {$filesystemDisk}");
        $this->line("  Public disk URL  = {$publicDiskUrl}");
        $this->info('');

        // 2. Rutas del sistema
        $this->warn('═══ 2. RUTAS DEL SISTEMA ═══');
        $basePath = base_path();
        $publicPath = public_path();
        $storagePath = storage_path();
        $storageAppPublic = storage_path('app/public');
        $publicStorageLink = public_path('storage');

        $this->line("  base_path()           = {$basePath}");
        $this->line("  public_path()         = {$publicPath}");
        $this->line("  storage_path()        = {$storagePath}");
        $this->line("  storage/app/public    = {$storageAppPublic}");
        $this->line("  public/storage (link) = {$publicStorageLink}");
        $this->info('');

        // 3. Verificar directorios
        $this->warn('═══ 3. VERIFICACIÓN DE DIRECTORIOS ═══');
        $checks = [
            'public/' => $publicPath,
            'storage/' => $storagePath,
            'storage/app/public/' => $storageAppPublic,
        ];

        foreach ($checks as $label => $path) {
            $exists = is_dir($path);
            $writable = $exists ? is_writable($path) : false;
            $status = $exists
                ? ($writable ? '✅ Existe y es escribible' : '⚠️  Existe pero NO es escribible')
                : '❌ NO existe';
            $this->line("  {$label} → {$status}");
        }
        $this->info('');

        // 4. Verificar symlink
        $this->warn('═══ 4. VERIFICACIÓN DEL SYMLINK (storage:link) ═══');
        $symlinkExists = file_exists($publicStorageLink);
        $isSymlink = is_link($publicStorageLink);
        $symlinkTarget = $isSymlink ? readlink($publicStorageLink) : null;

        if (!$symlinkExists) {
            $this->error('  ❌ El symlink public/storage NO EXISTE');
            $this->line('     Ejecuta: php artisan storage:link');
        } elseif ($isSymlink) {
            $this->line("  ✅ Symlink existe: public/storage → {$symlinkTarget}");
            // Verificar que el target existe
            $targetExists = is_dir($symlinkTarget) || is_dir(realpath($publicStorageLink));
            if ($targetExists) {
                $this->line('  ✅ El destino del symlink existe y es accesible');
            } else {
                $this->error('  ❌ El destino del symlink NO existe o NO es accesible');
                $this->line("     Target: {$symlinkTarget}");
                $this->line("     Realpath: " . (realpath($publicStorageLink) ?: 'FALSE'));
            }
        } else {
            // Es un directorio real, no un symlink
            $this->warn('  ⚠️  public/storage existe pero NO es un symlink (es un directorio real)');
            $this->line('     Esto puede causar problemas. Considera eliminarlo y ejecutar: php artisan storage:link');
        }
        $this->info('');

        // 5. Verificar que se puede generar URL de storage correctamente
        $this->warn('═══ 5. GENERACIÓN DE URLs DE STORAGE ═══');
        $testPath = 'test-image.jpg';
        $generatedUrl = Storage::disk('public')->url($testPath);
        $this->line("  Storage::disk('public')->url('{$testPath}')");
        $this->line("  → {$generatedUrl}");

        // Verificar que la URL empiece con APP_URL
        if (str_starts_with($generatedUrl, $appUrl)) {
            $this->line('  ✅ La URL generada empieza con APP_URL correctamente');
        } else {
            $this->error('  ❌ La URL generada NO empieza con APP_URL');
            $this->line("     Esperado: {$appUrl}/storage/{$testPath}");
            $this->line("     Obtenido: {$generatedUrl}");
        }
        $this->info('');

        // 6. Verificar carpetas de imágenes MLS
        $this->warn('═══ 6. CONTENIDO DE STORAGE PÚBLICO ═══');
        $publicDiskPath = storage_path('app/public');
        if (is_dir($publicDiskPath)) {
            $dirs = array_filter(glob($publicDiskPath . '/*'), 'is_dir');
            if (empty($dirs)) {
                $this->line('  (Sin directorios en storage/app/public/)');
            } else {
                foreach ($dirs as $dir) {
                    $dirName = basename($dir);
                    $fileCount = count(glob($dir . '/*'));
                    $subDirCount = count(array_filter(glob($dir . '/*'), 'is_dir'));
                    $this->line("  📁 {$dirName}/ → {$fileCount} elementos, {$subDirCount} subdirectorios");
                }
            }
        } else {
            $this->error('  ❌ storage/app/public/ no existe');
        }
        $this->info('');

        // 7. Verificar media_assets en BD
        $this->warn('═══ 7. MEDIA ASSETS EN BASE DE DATOS ═══');
        try {
            $totalMedia = \App\Models\MediaAsset::count();
            $withStoragePath = \App\Models\MediaAsset::whereNotNull('storage_path')
                ->where('storage_path', '!=', '')
                ->count();
            $withUrl = \App\Models\MediaAsset::whereNotNull('url')
                ->where('url', '!=', '')
                ->count();

            $this->line("  Total media assets:     {$totalMedia}");
            $this->line("  Con storage_path:       {$withStoragePath}");
            $this->line("  Con URL:                {$withUrl}");

            // Verificar URLs con el dominio correcto
            if ($withUrl > 0) {
                $correctUrlCount = \App\Models\MediaAsset::where('url', 'like', $appUrl . '%')->count();
                $wrongUrlCount = $withUrl - $correctUrlCount;
                $externalUrlCount = \App\Models\MediaAsset::where('url', 'not like', '%/storage/%')
                    ->whereNotNull('url')
                    ->where('url', '!=', '')
                    ->count();

                $this->line("  URLs con APP_URL actual: {$correctUrlCount}");
                $this->line("  URLs externas (MLS):     {$externalUrlCount}");
                $localWrong = $wrongUrlCount - $externalUrlCount;
                if ($localWrong > 0) {
                    $this->error("  ⚠️  URLs locales con dominio incorrecto: {$localWrong}");

                    // Mostrar ejemplo
                    $example = \App\Models\MediaAsset::where('url', 'not like', $appUrl . '%')
                        ->where('url', 'like', '%/storage/%')
                        ->first();
                    if ($example) {
                        $this->line("     Ejemplo: {$example->url}");
                        $this->line("     Esperado: {$appUrl}/storage/{$example->storage_path}");
                    }
                    $this->info('');
                    $this->warn('     Para corregir ejecuta: php artisan storage:fix-urls');
                } else {
                    $this->line('  ✅ Todas las URLs locales tienen el dominio correcto');
                }
            }
        } catch (\Exception $e) {
            $this->error("  Error al consultar BD: {$e->getMessage()}");
        }
        $this->info('');

        // 8. Verificar archivos físicos de MediaAssets descargados
        $this->warn('═══ 8. VERIFICACIÓN DE ARCHIVOS FÍSICOS ═══');
        try {
            $downloadedAssets = \App\Models\MediaAsset::whereNotNull('storage_path')
                ->where('storage_path', '!=', '')
                ->limit(20)
                ->get();

            $existCount = 0;
            $missingCount = 0;
            $missingExamples = [];

            foreach ($downloadedAssets as $asset) {
                if (Storage::disk('public')->exists($asset->storage_path)) {
                    $existCount++;
                } else {
                    $missingCount++;
                    if (count($missingExamples) < 3) {
                        $missingExamples[] = $asset->storage_path;
                    }
                }
            }

            $total = $existCount + $missingCount;
            $this->line("  Verificados (muestra): {$total}");
            $this->line("  ✅ Archivos encontrados: {$existCount}");
            if ($missingCount > 0) {
                $this->error("  ❌ Archivos faltantes: {$missingCount}");
                foreach ($missingExamples as $ex) {
                    $this->line("     - {$ex}");
                }
            } else {
                $this->line("  ✅ Todos los archivos verificados existen");
            }
        } catch (\Exception $e) {
            $this->error("  Error: {$e->getMessage()}");
        }
        $this->info('');

        // Resumen
        $this->warn('═══ RESUMEN DE ACCIONES NECESARIAS ═══');
        $actions = [];

        if (!$symlinkExists) {
            $actions[] = 'Crear symlink: php artisan storage:link';
        }

        if (!str_starts_with($generatedUrl, $appUrl)) {
            $actions[] = 'Verificar APP_URL en .env del servidor';
        }

        if (empty($actions)) {
            $this->info('  ✅ Todo parece estar configurado correctamente');
        } else {
            foreach ($actions as $i => $action) {
                $num = $i + 1;
                $this->line("  {$num}. {$action}");
            }
        }

        $this->info('');
        return self::SUCCESS;
    }
}
