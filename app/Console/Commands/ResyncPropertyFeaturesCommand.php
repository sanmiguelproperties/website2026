<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResyncPropertyFeaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easybroker:resync-features 
                            {--dry-run : Solo mostrar lo que se haría, sin hacer cambios}
                            {--force-all : Forzar la re-sincronización incluso si ya tienen features con categoría}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-sincroniza los features de las propiedades existentes usando el raw_payload almacenado';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $forceAll = $this->option('force-all');

        $this->info($dryRun ? '🔍 Modo dry-run activado (no se harán cambios)' : '🚀 Iniciando re-sincronización de features...');
        $this->newLine();

        // Obtener propiedades con raw_payload
        $properties = Property::whereNotNull('raw_payload')
            ->whereNotNull('easybroker_public_id')
            ->get();

        $this->info("📦 Propiedades a procesar: {$properties->count()}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($properties->count());
        $progressBar->start();

        $stats = [
            'processed' => 0,
            'updated' => 0,
            'features_created' => 0,
            'relations_created' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        foreach ($properties as $property) {
            $progressBar->advance();
            $stats['processed']++;

            try {
                $payload = $property->raw_payload;
                
                if (!is_array($payload)) {
                    $stats['skipped']++;
                    continue;
                }

                $features = $payload['features'] ?? [];
                
                if (empty($features)) {
                    $stats['skipped']++;
                    continue;
                }

                // Verificar si ya tiene features con categoría (skip si no es force)
                if (!$forceAll && !$dryRun) {
                    $hasFeatureWithCategory = $property->features()
                        ->whereNotNull('category')
                        ->exists();
                    
                    if ($hasFeatureWithCategory) {
                        $stats['skipped']++;
                        continue;
                    }
                }

                if (!$dryRun) {
                    $featureIds = [];

                    foreach ($features as $featureData) {
                        $featureName = null;
                        $featureCategory = null;

                        if (is_array($featureData)) {
                            $featureName = $featureData['name'] ?? null;
                            $featureCategory = $featureData['category'] ?? null;
                        } elseif (is_string($featureData)) {
                            $featureName = $featureData;
                        }

                        if (empty($featureName)) {
                            continue;
                        }

                        $feature = Feature::firstOrCreate(
                            [
                                'name' => $featureName,
                                'category' => $featureCategory,
                            ],
                            [
                                'locale' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );

                        if ($feature->wasRecentlyCreated) {
                            $stats['features_created']++;
                        }

                        $featureIds[] = $feature->id;
                    }

                    // Contar relaciones nuevas
                    $existingRelations = $property->features()->count();
                    $property->features()->sync($featureIds);
                    $newRelations = $property->features()->count() - $existingRelations;
                    if ($newRelations > 0) {
                        $stats['relations_created'] += $newRelations;
                    }
                }

                $stats['updated']++;

            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("Error en propiedad {$property->easybroker_public_id}: {$e->getMessage()}");
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('📊 Resumen de la re-sincronización:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Propiedades procesadas', $stats['processed']],
                ['Propiedades actualizadas', $stats['updated']],
                ['Propiedades omitidas', $stats['skipped']],
                ['Features creados', $stats['features_created']],
                ['Errores', $stats['errors']],
            ]
        );

        // Mostrar totales finales
        $this->newLine();
        $this->info('📈 Totales en la base de datos:');
        $this->table(
            ['Tabla', 'Total'],
            [
                ['Features', Feature::count()],
                ['Features con categoría', Feature::whereNotNull('category')->count()],
                ['Relaciones property_feature', DB::table('property_feature')->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
