<?php

namespace App\Services;

use App\Models\FrontendColorSetting;
use Illuminate\Support\Collection;

class FrontendColorService
{
    /**
     * Obtener la configuración de colores activa para una vista
     */
    public function getActiveColorsForView(string $viewSlug): ?FrontendColorSetting
    {
        return FrontendColorSetting::getActiveForView($viewSlug);
    }

    /**
     * Obtener la configuración de colores activa (legacy - global)
     */
    public function getActiveColors(): ?FrontendColorSetting
    {
        return FrontendColorSetting::getActiveForView('global');
    }

    /**
     * Obtener todas las configuraciones de colores
     */
    public function getAllColorSettings(): Collection
    {
        return FrontendColorSetting::orderBy('view_slug')->orderBy('name')->get();
    }

    /**
     * Obtener configuraciones de colores para una vista específica
     */
    public function getColorSettingsForView(string $viewSlug): Collection
    {
        return FrontendColorSetting::where('view_slug', $viewSlug)
                                   ->orderBy('name')
                                   ->get();
    }

    /**
     * Obtener todas las configuraciones agrupadas por vista
     */
    public function getAllGroupedByView(): array
    {
        $configs = FrontendColorSetting::all();
        $grouped = [];
        
        foreach (FrontendColorSetting::getAvailableViews() as $slug => $info) {
            $viewConfigs = $configs->where('view_slug', $slug);
            $grouped[$slug] = [
                'info' => $info,
                'configs' => $viewConfigs->values(),
                'active' => $viewConfigs->where('is_active', true)->first(),
            ];
        }
        
        return $grouped;
    }

    /**
     * Obtener una configuración específica
     */
    public function getColorSetting(int $id): ?FrontendColorSetting
    {
        return FrontendColorSetting::find($id);
    }

    /**
     * Crear una nueva configuración de colores
     */
    public function createColorSetting(array $data): FrontendColorSetting
    {
        $viewSlug = $data['view_slug'] ?? 'global';
        
        // Si es la primera configuración para esta vista, activarla automáticamente
        if (FrontendColorSetting::where('view_slug', $viewSlug)->count() === 0) {
            $data['is_active'] = true;
        }

        // Si no se proporcionan colores, usar los por defecto para esa vista
        if (!isset($data['colors']) || empty($data['colors'])) {
            $data['colors'] = FrontendColorSetting::getDefaultColorsForView($viewSlug);
        }

        return FrontendColorSetting::create($data);
    }

    /**
     * Actualizar una configuración de colores
     */
    public function updateColorSetting(int $id, array $data): bool
    {
        $setting = FrontendColorSetting::find($id);
        if (!$setting) {
            return false;
        }

        // No permitir cambiar el view_slug
        unset($data['view_slug']);

        // Merge colores existentes con los nuevos (actualización parcial)
        if (isset($data['colors']) && is_array($data['colors'])) {
            $existingColors = $setting->colors ?? [];
            $data['colors'] = $this->mergeColorsDeep($existingColors, $data['colors']);
        }

        $setting->update($data);
        return true;
    }

    /**
     * Eliminar una configuración de colores
     */
    public function deleteColorSetting(int $id): bool
    {
        $setting = FrontendColorSetting::find($id);
        if (!$setting) {
            return false;
        }

        // No permitir eliminar la configuración activa
        if ($setting->is_active) {
            return false;
        }

        $setting->delete();
        return true;
    }

    /**
     * Activar una configuración de colores
     */
    public function activateColorSetting(int $id): bool
    {
        $setting = FrontendColorSetting::find($id);
        if (!$setting) {
            return false;
        }

        return $setting->activate();
    }

    /**
     * Generar CSS con las variables de colores para una vista específica
     */
    public function generateCssForView(string $viewSlug): string
    {
        $colors = FrontendColorSetting::getMergedColorsForView($viewSlug);
        
        if (empty($colors)) {
            // Si no hay configuración, generar CSS con colores por defecto
            $globalDefaults = FrontendColorSetting::getDefaultColorsForView('global');
            $viewDefaults = FrontendColorSetting::getDefaultColorsForView($viewSlug);
            $colors = array_replace_recursive($globalDefaults, $viewDefaults);
        }

        if (empty($colors)) {
            return '';
        }

        $css = ":root {\n";
        foreach ($colors as $group => $groupColors) {
            if (!is_array($groupColors)) continue;
            foreach ($groupColors as $key => $value) {
                $css .= "  --fe-{$group}-{$key}: {$value};\n";
            }
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Generar CSS (legacy - para compatibilidad)
     */
    public function generateCss(): string
    {
        return $this->generateCssForView('global');
    }

    /**
     * Obtener un color específico de la configuración activa
     */
    public function getColor(string $path, ?string $default = null, string $viewSlug = 'global'): ?string
    {
        $colors = FrontendColorSetting::getMergedColorsForView($viewSlug);
        
        if (empty($colors)) {
            // Buscar en los colores por defecto
            $globalDefaults = FrontendColorSetting::getDefaultColorsForView('global');
            $viewDefaults = FrontendColorSetting::getDefaultColorsForView($viewSlug);
            $colors = array_replace_recursive($globalDefaults, $viewDefaults);
        }
        
        $keys = explode('.', $path);
        $value = $colors;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return is_string($value) ? $value : $default;
    }

    /**
     * Obtener colores de un grupo específico
     */
    public function getColorGroup(string $group, string $viewSlug = 'global'): array
    {
        $colors = FrontendColorSetting::getMergedColorsForView($viewSlug);
        
        if (empty($colors)) {
            $globalDefaults = FrontendColorSetting::getDefaultColorsForView('global');
            $viewDefaults = FrontendColorSetting::getDefaultColorsForView($viewSlug);
            $colors = array_replace_recursive($globalDefaults, $viewDefaults);
        }

        return $colors[$group] ?? [];
    }

    /**
     * Restablecer colores a los valores por defecto
     */
    public function resetToDefaults(int $id): bool
    {
        $setting = FrontendColorSetting::find($id);
        if (!$setting) {
            return false;
        }

        $setting->update([
            'colors' => FrontendColorSetting::getDefaultColorsForView($setting->view_slug)
        ]);

        return true;
    }

    /**
     * Duplicar una configuración de colores
     */
    public function duplicateColorSetting(int $id, string $newName): ?FrontendColorSetting
    {
        $original = FrontendColorSetting::find($id);
        if (!$original) {
            return null;
        }

        return FrontendColorSetting::create([
            'name' => $newName,
            'description' => "Copia de {$original->name}",
            'view_slug' => $original->view_slug,
            'colors' => $original->colors,
            'is_active' => false,
        ]);
    }

    /**
     * Limpiar el cache de colores
     */
    public function clearCache(): void
    {
        FrontendColorSetting::clearCache();
    }

    /**
     * Limpiar el cache de una vista específica
     */
    public function clearCacheForView(string $viewSlug): void
    {
        FrontendColorSetting::clearCacheForView($viewSlug);
    }

    /**
     * Exportar configuración de colores como JSON
     */
    public function exportColors(int $id): ?array
    {
        $setting = FrontendColorSetting::find($id);
        if (!$setting) {
            return null;
        }

        return [
            'name' => $setting->name,
            'description' => $setting->description,
            'view_slug' => $setting->view_slug,
            'colors' => $setting->colors,
            'exported_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Importar configuración de colores desde JSON
     */
    public function importColors(array $data): ?FrontendColorSetting
    {
        if (!isset($data['name']) || !isset($data['colors'])) {
            return null;
        }

        $viewSlug = $data['view_slug'] ?? 'global';

        // Verificar si el nombre ya existe para esa vista
        $existingName = $data['name'];
        $counter = 1;
        while (FrontendColorSetting::where('name', $existingName)
                                   ->where('view_slug', $viewSlug)
                                   ->exists()) {
            $existingName = $data['name'] . " ({$counter})";
            $counter++;
        }

        return FrontendColorSetting::create([
            'name' => $existingName,
            'description' => $data['description'] ?? "Importado el " . now()->format('d/m/Y H:i'),
            'view_slug' => $viewSlug,
            'colors' => $data['colors'],
            'is_active' => false,
        ]);
    }

    /**
     * Merge profundo de arrays de colores
     */
    protected function mergeColorsDeep(array $original, array $updates): array
    {
        $merged = $original;

        foreach ($updates as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeColorsDeep($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Obtener la lista de grupos de colores disponibles para una vista
     */
    public function getColorGroupsForView(string $viewSlug): array
    {
        $allGroups = $this->getColorGroups();
        $viewConfig = FrontendColorSetting::getAvailableViews()[$viewSlug] ?? null;
        
        if (!$viewConfig) {
            return [];
        }
        
        $viewGroups = [];
        foreach ($viewConfig['groups'] as $group) {
            if (isset($allGroups[$group])) {
                $viewGroups[$group] = $allGroups[$group];
            }
        }
        
        return $viewGroups;
    }

    /**
     * Obtener la lista de todos los grupos de colores disponibles
     */
    public function getColorGroups(): array
    {
        return [
            // Globales
            'primary' => 'Colores Primarios',
            'header' => 'Header',
            'footer' => 'Footer',
            'ui' => 'Elementos UI',
            'pagination' => 'Paginación',
            
            // Home
            'hero' => 'Sección Hero',
            'stats' => 'Estadísticas',
            'services' => 'Servicios/Características',
            'cta_sale' => 'CTA Venta',
            'cta_rent' => 'CTA Renta',
            'process' => 'Proceso de Compra',
            'testimonials' => 'Testimonios',
            'about' => 'Sobre Nosotros',
            'contact' => 'Contacto',
            'properties' => 'Sección Propiedades',
            
            // Página de Propiedades
            'property_cards' => 'Tarjetas de Propiedades',
            'filters' => 'Filtros',
            
            // Detalle de propiedad
            'property_detail' => 'Detalle de Propiedad',
            'gallery' => 'Galería',
            'agent_card' => 'Tarjeta de Agente',
            
            // Página de contacto
            'contact_page' => 'Página de Contacto',
            
            // Página nosotros
            'about_page' => 'Página Nosotros',
        ];
    }

    /**
     * Obtener las vistas disponibles
     */
    public function getAvailableViews(): array
    {
        return FrontendColorSetting::getAvailableViews();
    }

    /**
     * Validar estructura de colores para una vista
     */
    public function validateColors(array $colors, string $viewSlug = 'global'): array
    {
        $errors = [];
        $defaults = FrontendColorSetting::getDefaultColorsForView($viewSlug);

        foreach ($defaults as $group => $groupColors) {
            if (!isset($colors[$group])) {
                continue; // Grupo es opcional
            }

            if (!is_array($colors[$group])) {
                $errors[] = "El grupo '{$group}' debe ser un array";
                continue;
            }

            foreach ($groupColors as $colorKey => $defaultValue) {
                if (isset($colors[$group][$colorKey])) {
                    $value = $colors[$group][$colorKey];
                    if (!is_string($value) || empty($value)) {
                        $errors[] = "El color '{$group}.{$colorKey}' debe ser una cadena no vacía";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Detectar la vista actual basándose en la ruta
     */
    public function detectCurrentView(?string $routeName = null): string
    {
        if (!$routeName) {
            $routeName = request()->route()?->getName();
        }

        return match($routeName) {
            'home', 'welcome' => 'home',
            'properties', 'properties.index' => 'properties',
            'properties.show', 'property.show' => 'property-detail',
            'contact' => 'contact',
            'about' => 'about',
            default => 'global',
        };
    }
}
