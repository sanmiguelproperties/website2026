<?php

namespace App\Console\Commands;

use App\Jobs\DownloadPropertyImageJob;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMLSImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mls:sync-images
                            {--limit=50 : Número máximo de propiedades a procesar}
                            {--offset=0 : Número de propiedades a saltar (para paginación)}
                            {--property-id= : ID de una propiedad específica}
                            {--force : Forzar re-descarga de imágenes existentes}';

    /**
     * The console command description.
     */
    protected $description = 'Sincroniza imágenes de propiedades MLS obteniendo el detalle completo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $propertyId = $this->option('property-id');
        $force = $this->option('force');

        $this->info('=== Sincronizando imágenes MLS ===');
        $this->info('Límite: ' . $limit);
        $this->info('Offset: ' . $offset);
        $this->info('Modo: ' . ($force ? 'Forzado' : 'Normal'));

        // Obtener propiedades MLS
        $query = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->offset($offset);

        if ($propertyId) {
            $query->where('id', $propertyId);
        }

        $properties = $query->limit($limit)->orderBy('id')->get();

        if ($properties->isEmpty()) {
            $this->warn('No se encontraron propiedades MLS.');
            return Command::SUCCESS;
        }

        $this->info('Propiedades encontradas: ' . $properties->count());

        // Usar el MLSSyncService para obtener detalles
        $syncService = new \App\Services\MLSSyncService();

        $processed = 0;
        $errors = 0;
        $imagesDispatched = 0;

        foreach ($properties as $property) {
            $this->line("Procesando propiedad #{$property->id} (MLS: {$property->mls_public_id})");

            try {
                // Obtener el detalle de la propiedad
                $detailData = $syncService->fetchPropertyDetail($property->mls_public_id);

                if (!$detailData) {
                    $this->error("  - Error al obtener detalle");
                    $errors++;
                    continue;
                }

                // Verificar si hay fotos
                $photos = $detailData['photos'] ?? [];
                if (empty($photos)) {
                    $this->warn("  - No hay fotos en el detalle");
                    continue;
                }

                $this->info("  - Fotos encontradas: " . count($photos));

                // Verificar imágenes ya vinculadas
                $existingImages = $property->mediaAssets()
                    ->wherePivot('role', 'image')
                    ->pluck('url')
                    ->toArray();

                foreach ($photos as $index => $photo) {
                    $url = is_string($photo) ? $photo : ($photo['url'] ?? null);

                    if (!$url) {
                        continue;
                    }

                    // Verificar si ya está vinculada
                    if (!$force && in_array($url, $existingImages)) {
                        $this->line("    - Ya existe: " . basename($url));
                        continue;
                    }

                    // Dispatch job para descargar
                    $mediaData = [
                        'url' => $url,
                        'title' => is_array($photo) ? ($photo['title'] ?? null) : null,
                        'alt' => is_array($photo) ? ($photo['alt'] ?? null) : null,
                        'position' => $index,
                    ];

                    DownloadPropertyImageJob::dispatch($property->id, $mediaData);
                    $imagesDispatched++;
                    $this->info("    - Job dispatchado: " . basename($url));
                }

                // Actualizar el raw_payload con los datos completos
                $property->update([
                    'raw_payload' => array_merge($property->raw_payload ?: [], $detailData),
                    'last_synced_at' => now(),
                ]);

                $processed++;

            } catch (\Throwable $e) {
                $this->error("  - Error: " . $e->getMessage());
                Log::error('Error sincronizando imágenes MLS', [
                    'property_id' => $property->id,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->info('=== Resumen ===');
        $this->info("Propiedades procesadas: {$processed}");
        $this->info("Jobs de imágenes dispatchados: {$imagesDispatched}");
        $this->info("Errores: {$errors}");

        if ($imagesDispatched > 0) {
            $this->info('Los jobs están siendo procesados en la cola mls-images.');
            $this->info('Para ver logs: php artisan queue:work --queue=mls-images');
        }

        return Command::SUCCESS;
    }
}
