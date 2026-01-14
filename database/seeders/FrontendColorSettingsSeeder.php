<?php

namespace Database\Seeders;

use App\Models\FrontendColorSetting;
use Illuminate\Database\Seeder;

class FrontendColorSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las vistas disponibles
        $availableViews = FrontendColorSetting::getAvailableViews();

        foreach ($availableViews as $viewSlug => $viewInfo) {
            // Verificar si ya existe una configuración para esta vista
            $exists = FrontendColorSetting::where('view_slug', $viewSlug)->exists();
            
            if (!$exists) {
                FrontendColorSetting::create([
                    'name' => "Default {$viewInfo['name']}",
                    'description' => $viewInfo['description'],
                    'view_slug' => $viewSlug,
                    'colors' => FrontendColorSetting::getDefaultColorsForView($viewSlug),
                    'is_active' => true,
                ]);

                $this->command->info("Configuración de colores creada para vista: {$viewSlug}");
            } else {
                $this->command->warn("Ya existe configuración para vista: {$viewSlug}");
            }
        }

        $this->command->info('Seeder de colores del frontend completado.');
    }
}
