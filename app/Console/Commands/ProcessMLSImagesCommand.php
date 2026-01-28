<?php

namespace App\Console\Commands;

use App\Jobs\DownloadPropertyImageJob;
use App\Models\MediaAsset;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMLSImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mls:process-images
                            {--force : Forzar reprocesamiento de imágenes ya descargadas}
                            {--limit=50 : Número máximo de propiedades a procesar}
                            {--property-id= : ID de una propiedad específica}';

    /**
     * The console command description.
     */
    protected $description = 'Procesa la descarga de imágenes de propiedades MLS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        $propertyId = $this->option('property-id');

        $this->info('=== Procesando imágenes MLS ===');
        $this->info('Modo: ' . ($force ? 'Forzado' : 'Normal'));
        $this->info('Límite: ' . $limit);

        // Obtener propiedades MLS con imágenes pendientes
        $query = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->with(['mediaAssets' => function ($query) {
                $query->wherePivot('role', 'image');
            }]);

        if ($propertyId) {
            $query->where('id', $propertyId);
        }

        $properties = $query->limit($limit)->get();

        if ($properties->isEmpty()) {
            $this->warn('No se encontraron propiedades MLS para procesar.');
            return Command::SUCCESS;
        }

        $this->info('Propiedades encontradas: ' . $properties->count());

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($properties as $property) {
            $this->line("Procesando propiedad #{$property->id} (MLS: {$property->mls_public_id})");

            try {
                // Verificar si tiene imágenes ya vinculadas
                $hasLinkedImages = $property->mediaAssets()
                    ->wherePivot('role', 'image')
                    ->exists();

                // Si no tiene imágenes vinculadas o force=true, buscar imágenes del payload
                if (!$hasLinkedImages || $force) {
                    $rawPayload = $property->raw_payload;
                    $photos = $rawPayload['photos'] ?? $rawPayload['images'] ?? [];

                    if (empty($photos)) {
                        $this->warn("  - No hay imágenes en el payload");
                        $skipped++;
                        continue;
                    }

                    $this->info("  - Imágenes encontradas: " . count($photos));

                    foreach ($photos as $index => $photo) {
                        $url = is_string($photo) ? $photo : ($photo['url'] ?? $photo['src'] ?? null);

                        if (!$url) {
                            continue;
                        }

                        // Verificar si ya existe el MediaAsset
                        $existingMedia = MediaAsset::where('url', $url)->first();

                        if ($existingMedia && !$force) {
                            // Ya existe, vincular si no está vinculado
                            if (!$property->mediaAssets()->where('media_asset_id', $existingMedia->id)->exists()) {
                                $property->mediaAssets()->attach($existingMedia->id, [
                                    'role' => 'image',
                                    'title' => is_array($photo) ? ($photo['title'] ?? null) : null,
                                    'position' => $index,
                                    'source_url' => $url,
                                    'raw_payload' => json_encode($photo),
                                ]);

                                if ($index === 0) {
                                    $property->update(['cover_media_asset_id' => $existingMedia->id]);
                                }

                                $this->info("    - Vinculado existente: {$url}");
                            } else {
                                $this->line("    - Ya vinculado: {$url}");
                            }
                            $skipped++;
                        } else {
                            // Dispatch job para descargar
                            $mediaData = [
                                'url' => $url,
                                'title' => is_array($photo) ? ($photo['title'] ?? $photo['alt'] ?? null) : null,
                                'alt' => is_array($photo) ? ($photo['alt'] ?? null) : null,
                                'position' => $index,
                            ];

                            DownloadPropertyImageJob::dispatch($property->id, $mediaData);
                            $this->info("    - Job dispatchado: {$url}");
                            $processed++;
                        }
                    }
                } else {
                    $this->line("  - Ya tiene imágenes vinculadas");
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $this->error("  - Error: " . $e->getMessage());
                Log::error('Error procesando imágenes MLS', [
                    'property_id' => $property->id,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->info('=== Resumen ===');
        $this->info("Jobs dispatchados: {$processed}");
        $this->info("Omitidos: {$skipped}");
        $this->info("Errores: {$errors}");

        if ($processed > 0) {
            $this->info('Los jobs están siendo procesados en la cola mls-images.');
            $this->info('Para ver los logs: php artisan queue:work --queue=mls-images');
        }

        return Command::SUCCESS;
    }
}
