<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FrontendColorSetting extends Model
{
    /**
     * Cache key prefix for active colors
     */
    const CACHE_KEY_PREFIX = 'frontend_colors_';
    
    /**
     * Cache TTL in seconds (1 hour)
     */
    const CACHE_TTL = 3600;

    protected $fillable = [
        'name',
        'description',
        'view_slug',
        'colors',
        'is_active',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener las vistas disponibles con sus grupos de colores
     */
    public static function getAvailableViews(): array
    {
        return [
            'global' => [
                'name' => 'Global (Compartido)',
                'description' => 'Colores compartidos en todas las páginas (header, footer, UI)',
                'groups' => ['primary', 'header', 'footer', 'ui', 'pagination'],
            ],
            'home' => [
                'name' => 'Página de Inicio',
                'description' => 'Colores específicos del home',
                'groups' => ['hero', 'stats', 'services', 'cta_sale', 'cta_rent', 'properties', 'process', 'testimonials', 'about', 'contact'],
            ],
            'properties' => [
                'name' => 'Listado de Propiedades',
                'description' => 'Colores para la página de propiedades',
                'groups' => ['property_cards', 'filters', 'properties'],
            ],
            'property-detail' => [
                'name' => 'Detalle de Propiedad',
                'description' => 'Colores para la vista de detalle de propiedad',
                'groups' => ['property_detail', 'gallery', 'agent_card'],
            ],
            'contact' => [
                'name' => 'Página de Contacto',
                'description' => 'Colores para la página de contacto',
                'groups' => ['contact_page'],
            ],
            'about' => [
                'name' => 'Página Nosotros',
                'description' => 'Colores para la página about',
                'groups' => ['about_page'],
            ],
            'mls-offices' => [
                'name' => 'Agencias MLS',
                'description' => 'Colores para la página de agencias y detalle de agencia',
                'groups' => ['property_cards', 'filters', 'properties'],
            ],
            'mls-agents' => [
                'name' => 'Agentes MLS',
                'description' => 'Colores para la página de agentes y detalle de agente',
                'groups' => ['property_cards', 'filters', 'properties'],
            ],
        ];
    }

    /**
     * Obtener la configuración activa para una vista específica
     */
    public static function getActiveForView(string $viewSlug): ?self
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $viewSlug;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($viewSlug) {
            return static::where('view_slug', $viewSlug)
                        ->where('is_active', true)
                        ->first();
        });
    }

    /**
     * Obtener la configuración activa (legacy - para compatibilidad)
     */
    public static function getActive(): ?self
    {
        return self::getActiveForView('global');
    }

    /**
     * Obtener colores combinados (global + vista específica)
     */
    public static function getMergedColorsForView(string $viewSlug): array
    {
        // Siempre cargar colores globales
        $globalConfig = self::getActiveForView('global');
        $globalColors = $globalConfig?->colors ?? [];
        
        // Si es la vista global, solo devolver esos colores
        if ($viewSlug === 'global') {
            return $globalColors;
        }
        
        // Cargar colores específicos de la vista
        $viewConfig = self::getActiveForView($viewSlug);
        $viewColors = $viewConfig?->colors ?? [];
        
        // Merge: los colores de la vista sobrescriben los globales
        return array_replace_recursive($globalColors, $viewColors);
    }

    /**
     * Activar esta configuración de colores (solo para su vista)
     */
    public function activate(): bool
    {
        // Desactivar todas las configuraciones de la misma vista
        static::where('view_slug', $this->view_slug)
              ->where('is_active', true)
              ->update(['is_active' => false]);

        // Activar esta configuración
        $this->update(['is_active' => true]);

        // Limpiar cache de esta vista
        self::clearCacheForView($this->view_slug);

        return true;
    }

    /**
     * Limpiar el cache de una vista específica
     */
    public static function clearCacheForView(string $viewSlug): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $viewSlug);
    }

    /**
     * Limpiar todo el cache de colores
     */
    public static function clearCache(): void
    {
        foreach (array_keys(self::getAvailableViews()) as $viewSlug) {
            self::clearCacheForView($viewSlug);
        }
    }

    /**
     * Obtener un color específico por su path (dot notation)
     * Ejemplo: getColor('primary.from') devuelve '#D1A054'
     */
    public function getColor(string $path, ?string $default = null): ?string
    {
        $colors = $this->colors ?? [];
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
     * Obtener todos los colores como variable CSS
     */
    public function getCssVariables(): array
    {
        $colors = $this->colors ?? [];
        $cssVars = [];

        $this->flattenColors($colors, '', $cssVars);

        return $cssVars;
    }

    /**
     * Aplanar el array de colores para generar variables CSS
     */
    protected function flattenColors(array $colors, string $prefix, array &$result): void
    {
        foreach ($colors as $key => $value) {
            $varName = $prefix ? "{$prefix}-{$key}" : $key;
            
            if (is_array($value)) {
                $this->flattenColors($value, $varName, $result);
            } else {
                $result["--fe-{$varName}"] = $value;
            }
        }
    }

    /**
     * Generar el bloque de CSS con todas las variables
     */
    public function generateCss(): string
    {
        $variables = $this->getCssVariables();
        
        if (empty($variables)) {
            return '';
        }

        $css = ":root {\n";
        foreach ($variables as $var => $value) {
            $css .= "  {$var}: {$value};\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Obtener los colores por defecto para una vista específica
     */
    public static function getDefaultColorsForView(string $viewSlug): array
    {
        $allDefaults = self::getDefaultColors();
        $viewConfig = self::getAvailableViews()[$viewSlug] ?? null;
        
        if (!$viewConfig) {
            return [];
        }
        
        $viewColors = [];
        foreach ($viewConfig['groups'] as $group) {
            if (isset($allDefaults[$group])) {
                $viewColors[$group] = $allDefaults[$group];
            }
        }
        
        return $viewColors;
    }

    /**
     * Obtener los colores por defecto del sistema (todos)
     */
    public static function getDefaultColors(): array
    {
        return [
            // === COLORES GLOBALES ===
            // Paleta de marca: #1C1C1C, #D1A054, #FFFAF5, #A52A2A, #768D59, #5B5B5B, #979790
            'primary' => [
                'from' => '#D1A054',
                'to' => '#768D59',
            ],
            'header' => [
                'logo_gradient_from' => '#D1A054',
                'logo_gradient_to' => '#768D59',
                'cta_button_from' => '#D1A054',
                'cta_button_to' => '#768D59',
                'nav_hover' => '#D1A054',
                'mobile_menu_icon_active' => '#D1A054',
            ],
            'footer' => [
                'background' => '#1C1C1C',
                'accent_from' => '#D1A054',
                'accent_to' => '#768D59',
                'newsletter_badge_from' => 'rgba(209,160,84,0.2)',
                'newsletter_badge_to' => 'rgba(118,141,89,0.2)',
                'newsletter_badge_text' => '#768D59',
                'newsletter_title_from' => '#D1A054',
                'newsletter_title_to' => '#768D59',
                'newsletter_button_from' => '#D1A054',
                'newsletter_button_to' => '#768D59',
                'social_facebook_hover' => '#D1A054',
                'social_instagram_from' => '#A52A2A',
                'social_instagram_to' => '#D1A054',
                'social_twitter_hover' => '#5B5B5B',
                'social_whatsapp_hover' => '#768D59',
                'social_linkedin_hover' => '#5B5B5B',
                'link_arrow_1' => '#D1A054',
                'link_arrow_2' => '#768D59',
                'contact_phone_icon' => '#768D59',
                'contact_email_icon' => '#D1A054',
                'contact_location_icon' => '#D1A054',
                'contact_hours_icon' => '#768D59',
            ],
            'ui' => [
                'back_to_top_from' => '#D1A054',
                'back_to_top_to' => '#768D59',
                'preloader_border_1' => '#D1A054',
                'preloader_border_2' => '#768D59',
                'scrollbar_from' => '#D1A054',
                'scrollbar_to' => '#768D59',
                'scrollbar_hover_from' => '#A52A2A',
                'scrollbar_hover_to' => '#768D59',
            ],
            'pagination' => [
                'active_from' => '#D1A054',
                'active_to' => '#768D59',
                'hover_bg' => '#FFFAF5',
            ],

            // === COLORES DE HOME ===
            'hero' => [
                'title_gradient_from' => '#D1A054',
                'title_gradient_via' => '#FFFAF5',
                'title_gradient_to' => '#768D59',
                'overlay_from' => 'rgba(28,28,28,0.7)',
                'overlay_via' => 'rgba(28,28,28,0.4)',
                'overlay_to' => 'rgba(28,28,28,0.8)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_dot' => '#768D59',
                'search_bg' => 'rgba(255,255,255,0.1)',
                'search_focus' => '#D1A054',
            ],
            'stats' => [
                'bg' => '#FFFAF5',
                'border' => '#f1f5f9',
                'text' => '#5B5B5B',
                'properties_from' => '#D1A054',
                'properties_to' => '#D1A054',
                'experience_from' => '#768D59',
                'experience_to' => '#768D59',
                'clients_from' => '#A52A2A',
                'clients_to' => '#A52A2A',
                'zones_from' => '#5B5B5B',
                'zones_to' => '#979790',
            ],
            'services' => [
                'bg' => '#FFFAF5',
                'badge_bg' => 'rgba(209,160,84,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#1C1C1C',
                'subtitle' => '#5B5B5B',
                'card_bg_from' => '#FFFAF5',
                'card_bg_to' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_title' => '#1C1C1C',
                'card_text' => '#5B5B5B',
                'feature1_from' => '#D1A054',
                'feature1_to' => '#D1A054',
                'feature1_glow' => 'rgba(209,160,84,0.05)',
                'feature2_from' => '#768D59',
                'feature2_to' => '#768D59',
                'feature2_glow' => 'rgba(118,141,89,0.05)',
                'feature3_from' => '#A52A2A',
                'feature3_to' => '#A52A2A',
                'feature3_glow' => 'rgba(165,42,42,0.05)',
                'feature4_from' => '#5B5B5B',
                'feature4_to' => '#979790',
                'feature4_glow' => 'rgba(91,91,91,0.05)',
                'feature5_from' => '#A52A2A',
                'feature5_to' => '#D1A054',
                'feature5_glow' => 'rgba(165,42,42,0.05)',
                'feature6_from' => '#768D59',
                'feature6_to' => '#D1A054',
                'feature6_glow' => 'rgba(118,141,89,0.05)',
            ],
            'cta_sale' => [
                'overlay_from' => 'rgba(28,28,28,0.95)',
                'overlay_via' => 'rgba(28,28,28,0.8)',
                'overlay_to' => 'transparent',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#768D59',
                'highlight_from' => '#768D59',
                'highlight_to' => '#D1A054',
                'btn_primary_from' => '#768D59',
                'btn_primary_to' => '#768D59',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'decor' => 'rgba(118,141,89,0.2)',
            ],
            'cta_rent' => [
                'overlay_from' => 'rgba(28,28,28,0.95)',
                'overlay_via' => 'rgba(28,28,28,0.8)',
                'overlay_to' => 'transparent',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#D1A054',
                'highlight_from' => '#D1A054',
                'highlight_to' => '#D1A054',
                'btn_primary_from' => '#D1A054',
                'btn_primary_to' => '#D1A054',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'check_color' => '#D1A054',
                'decor' => 'rgba(209,160,84,0.2)',
            ],
            'process' => [
                'bg' => '#1C1C1C',
                'glow1' => 'rgba(209,160,84,0.2)',
                'glow2' => 'rgba(118,141,89,0.2)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#768D59',
                'highlight_from' => '#D1A054',
                'highlight_to' => '#768D59',
                'subtitle' => '#979790',
                'step1_from' => '#D1A054',
                'step1_to' => '#D1A054',
                'step2_from' => '#768D59',
                'step2_to' => '#768D59',
                'step3_from' => '#A52A2A',
                'step3_to' => '#A52A2A',
                'step4_from' => '#5B5B5B',
                'step4_to' => '#979790',
                'card_bg' => 'rgba(91,91,91,0.3)',
                'card_border' => 'rgba(151,151,144,0.3)',
                'card_text' => '#979790',
            ],
            'testimonials' => [
                'bg' => '#FFFAF5',
                'badge_bg' => 'rgba(209,160,84,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#1C1C1C',
                'subtitle' => '#5B5B5B',
                'card_bg_from' => '#FFFAF5',
                'card_bg_to' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_text' => '#5B5B5B',
                'stars' => '#D1A054',
                'quote1' => 'rgba(209,160,84,0.1)',
                'quote2' => 'rgba(118,141,89,0.1)',
                'quote3' => 'rgba(165,42,42,0.1)',
                'avatar1_from' => '#D1A054',
                'avatar1_to' => '#768D59',
                'avatar2_from' => '#A52A2A',
                'avatar2_to' => '#D1A054',
                'avatar3_from' => '#5B5B5B',
                'avatar3_to' => '#979790',
                'name' => '#1C1C1C',
                'role' => '#5B5B5B',
            ],
            'about' => [
                'bg_from' => '#FFFAF5',
                'bg_to' => 'rgba(209,160,84,0.05)',
                'image_overlay' => 'rgba(28,28,28,0.6)',
                'card_bg' => '#ffffff',
                'card_title' => '#1C1C1C',
                'card_text' => '#5B5B5B',
                'badge_bg' => 'rgba(209,160,84,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#1C1C1C',
                'text' => '#5B5B5B',
                'value_title' => '#1C1C1C',
                'value_text' => '#5B5B5B',
                'value1_bg' => 'rgba(209,160,84,0.1)',
                'value1_icon' => '#D1A054',
                'value2_bg' => 'rgba(118,141,89,0.1)',
                'value2_icon' => '#768D59',
                'value3_bg' => 'rgba(165,42,42,0.1)',
                'value3_icon' => '#A52A2A',
            ],
            'contact' => [
                'bg' => '#FFFAF5',
                'badge_bg' => 'rgba(209,160,84,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#1C1C1C',
                'text' => '#5B5B5B',
                'method_bg' => '#FFFAF5',
                'method_title' => '#1C1C1C',
                'method_text' => '#5B5B5B',
                'phone_from' => '#768D59',
                'phone_to' => '#768D59',
                'whatsapp_from' => '#768D59',
                'whatsapp_to' => '#768D59',
                'email_from' => '#D1A054',
                'email_to' => '#D1A054',
                'form_bg_from' => '#FFFAF5',
                'form_bg_to' => '#ffffff',
                'form_border' => '#f1f5f9',
                'label' => '#1C1C1C',
                'input_bg' => '#ffffff',
                'input_border' => '#e2e8f0',
                'input_text' => '#1C1C1C',
                'privacy_text' => '#5B5B5B',
                'link' => '#D1A054',
            ],

            // === COLORES DE PROPIEDADES ===
            'property_cards' => [
                'price_from' => '#D1A054',
                'price_to' => '#768D59',
                'sale_badge' => '#768D59',
                'rent_badge' => '#D1A054',
                'favorite_hover' => '#A52A2A',
                'title_hover' => '#D1A054',
            ],
            'filters' => [
                'active_from' => '#D1A054',
                'active_to' => '#768D59',
                'focus_border' => '#D1A054',
                'focus_ring' => 'rgba(209,160,84,0.2)',
            ],
            'properties' => [
                'bg_from' => '#FFFAF5',
                'bg_to' => '#ffffff',
                'badge_bg' => 'rgba(209,160,84,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#1C1C1C',
                'subtitle' => '#5B5B5B',
                'filter_bg' => '#ffffff',
                'filter_border' => '#e2e8f0',
                'filter_icon' => '#979790',
                'filter_clear' => '#5B5B5B',
                'filter_divider' => '#f1f5f9',
                'input_bg' => '#FFFAF5',
                'input_border' => '#e2e8f0',
                'input_text' => '#1C1C1C',
                'tag_active_from' => '#D1A054',
                'tag_active_to' => '#768D59',
                'tag_inactive_bg' => '#f1f5f9',
                'tag_inactive_text' => '#5B5B5B',
                'tag_inactive_hover' => '#e2e8f0',
                'card_bg' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_title' => '#1C1C1C',
                'card_location' => '#5B5B5B',
                'card_meta' => '#5B5B5B',
                'card_divider' => '#f1f5f9',
                'type_badge_bg' => 'rgba(255,255,255,0.9)',
                'type_badge_text' => '#1C1C1C',
                'sale_badge' => '#768D59',
                'rent_badge' => '#D1A054',
                'fav_btn_bg' => 'rgba(255,255,255,0.9)',
                'fav_btn_icon' => '#5B5B5B',
            ],

            // === COLORES DE DETALLE DE PROPIEDAD ===
            'property_detail' => [
                'price_from' => '#D1A054',
                'price_to' => '#768D59',
                'badge_sale' => '#768D59',
                'badge_rent' => '#D1A054',
                'feature_icon' => '#D1A054',
                'section_title' => '#1C1C1C',
                'contact_button_from' => '#D1A054',
                'contact_button_to' => '#768D59',
            ],
            'gallery' => [
                'thumbnail_border_active' => '#D1A054',
                'fullscreen_bg' => 'rgba(28,28,28,0.95)',
                'nav_button_bg' => 'rgba(255,255,255,0.1)',
                'nav_button_hover' => 'rgba(255,255,255,0.2)',
            ],
            'agent_card' => [
                'border' => '#e2e8f0',
                'name_color' => '#1C1C1C',
                'phone_icon' => '#768D59',
                'email_icon' => '#D1A054',
                'whatsapp_button_from' => '#768D59',
                'whatsapp_button_to' => '#768D59',
            ],

            // === COLORES DE PÁGINA DE CONTACTO ===
            'contact_page' => [
                'hero_bg_from' => '#D1A054',
                'hero_bg_to' => '#768D59',
                'form_border' => '#e2e8f0',
                'form_focus_border' => '#D1A054',
                'submit_button_from' => '#D1A054',
                'submit_button_to' => '#768D59',
                'info_icon_phone' => '#768D59',
                'info_icon_email' => '#D1A054',
                'info_icon_location' => '#D1A054',
                'map_marker' => '#D1A054',
            ],

            // === COLORES DE PÁGINA NOSOTROS ===
            'about_page' => [
                'hero_bg_from' => '#D1A054',
                'hero_bg_to' => '#768D59',
                'section_title' => '#1C1C1C',
                'team_card_border' => '#e2e8f0',
                'team_name' => '#1C1C1C',
                'team_role' => '#5B5B5B',
                'value_icon_1' => '#D1A054',
                'value_icon_2' => '#768D59',
                'value_icon_3' => '#A52A2A',
                'timeline_line' => '#e2e8f0',
                'timeline_dot_active' => '#D1A054',
            ],
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Limpiar cache al actualizar
        static::updated(function ($model) {
            self::clearCacheForView($model->view_slug);
        });

        // Limpiar cache al eliminar
        static::deleted(function ($model) {
            self::clearCacheForView($model->view_slug);
        });
    }
}
