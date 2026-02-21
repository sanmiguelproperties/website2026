<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixStorageUrlsCommand extends Command
{
    protected $signature = 'storage:fix-urls
                            {--dry-run : Muestra los cambios sin aplicarlos}
                            {--old-url= : URL antigua a reemplazar (ej: https://old-domain.com)}';

    protected $description = 'Corrige las URLs de media_assets locales para que coincidan con APP_URL actual';

    public function handle(): int
    {
        $appUrl = rtrim(config('app.url'), '/');
        $storageBaseUrl = $appUrl . '/storage';
        $dryRun = $this->option('dry-run');
        $oldUrl = $this->option('old-url');

        $this->info('');
        $this->info('╔═══════════════════════════════════════════════════╗');
        $this->info('║       CORRECCIÓN DE URLs DE MEDIA ASSETS          ║');
        $this->info('╚═══════════════════════════════════════════════════╝');
        $this->info('');
        $this->line("  APP_URL actual:     {$appUrl}");
        $this->line("  Storage base URL:   {$storageBaseUrl}");
        if ($dryRun) {
            $this->warn('  MODO: Dry-run (no se aplicarán cambios)');
        }
        $this->info('');

        // Buscar media assets locales (con storage_path) cuya URL no coincide con APP_URL actual
        $query = MediaAsset::whereNotNull('storage_path')
            ->where('storage_path', '!=', '');

        if ($oldUrl) {
            // Si se especificó una URL antigua, buscar solo esas
            $query->where('url', 'like', rtrim($oldUrl, '/') . '%');
        } else {
            // Buscar todas las URLs locales que no empiezan con APP_URL actual
            $query->where(function ($q) use ($appUrl) {
                $q->where('url', 'not like', $appUrl . '%')
                  ->orWhereNull('url')
                  ->orWhere('url', '');
            });
        }

        $assetsToFix = $query->get();

        if ($assetsToFix->isEmpty()) {
            $this->info('  ✅ No hay media assets que necesiten corrección de URL');
            $this->info('');
            return self::SUCCESS;
        }

        $this->warn("  Se encontraron {$assetsToFix->count()} media assets para corregir:");
        $this->info('');

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($assetsToFix as $asset) {
            $oldAssetUrl = $asset->url;
            $newUrl = $storageBaseUrl . '/' . ltrim($asset->storage_path, '/');

            // Verificar que el archivo físico existe
            $fileExists = Storage::disk('public')->exists($asset->storage_path);

            $statusIcon = $fileExists ? '✅' : '⚠️ ';
            $fileStatus = $fileExists ? '' : ' (archivo NO encontrado)';

            if ($dryRun) {
                $this->line("  {$statusIcon} ID {$asset->id}:{$fileStatus}");
                $this->line("     Antes:   {$oldAssetUrl}");
                $this->line("     Después: {$newUrl}");
                $this->line('');
                $fixedCount++;
            } else {
                try {
                    $asset->update(['url' => $newUrl]);
                    $this->line("  {$statusIcon} ID {$asset->id}: URL actualizada{$fileStatus}");
                    $fixedCount++;
                } catch (\Exception $e) {
                    $this->error("  ❌ ID {$asset->id}: Error - {$e->getMessage()}");
                    $errorCount++;
                }
            }
        }

        $this->info('');
        $this->warn('═══ RESUMEN ═══');
        $action = $dryRun ? 'Se corregirían' : 'Corregidos';
        $this->line("  {$action}: {$fixedCount} media assets");
        if ($errorCount > 0) {
            $this->error("  Errores: {$errorCount}");
        }

        if ($dryRun && $fixedCount > 0) {
            $this->info('');
            $this->warn('  Para aplicar los cambios, ejecuta sin --dry-run:');
            $this->line('  php artisan storage:fix-urls');
        }

        $this->info('');
        return self::SUCCESS;
    }
}
