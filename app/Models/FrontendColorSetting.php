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
     * Ejemplo: getColor('primary.from') devuelve '#4f46e5'
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
            'primary' => [
                'from' => '#4f46e5',
                'to' => '#10b981',
            ],
            'header' => [
                'logo_gradient_from' => '#4f46e5',
                'logo_gradient_to' => '#10b981',
                'cta_button_from' => '#4f46e5',
                'cta_button_to' => '#10b981',
                'nav_hover' => '#4f46e5',
                'mobile_menu_icon_active' => '#4f46e5',
            ],
            'footer' => [
                'background' => '#0f172a',
                'accent_from' => '#4f46e5',
                'accent_to' => '#10b981',
                'newsletter_badge_from' => 'rgba(79,70,229,0.2)',
                'newsletter_badge_to' => 'rgba(16,185,129,0.2)',
                'newsletter_badge_text' => '#34d399',
                'newsletter_title_from' => '#818cf8',
                'newsletter_title_to' => '#34d399',
                'newsletter_button_from' => '#4f46e5',
                'newsletter_button_to' => '#10b981',
                'social_facebook_hover' => '#4f46e5',
                'social_instagram_from' => '#9333ea',
                'social_instagram_to' => '#ec4899',
                'social_twitter_hover' => '#0ea5e9',
                'social_whatsapp_hover' => '#22c55e',
                'social_linkedin_hover' => '#2563eb',
                'link_arrow_1' => '#6366f1',
                'link_arrow_2' => '#10b981',
                'contact_phone_icon' => '#10b981',
                'contact_email_icon' => '#6366f1',
                'contact_location_icon' => '#6366f1',
                'contact_hours_icon' => '#10b981',
            ],
            'ui' => [
                'back_to_top_from' => '#4f46e5',
                'back_to_top_to' => '#10b981',
                'preloader_border_1' => '#4f46e5',
                'preloader_border_2' => '#10b981',
                'scrollbar_from' => '#6366f1',
                'scrollbar_to' => '#10b981',
                'scrollbar_hover_from' => '#4f46e5',
                'scrollbar_hover_to' => '#059669',
            ],
            'pagination' => [
                'active_from' => '#4f46e5',
                'active_to' => '#10b981',
                'hover_bg' => '#f1f5f9',
            ],

            // === COLORES DE HOME ===
            'hero' => [
                'title_gradient_from' => '#818cf8',
                'title_gradient_via' => '#c084fc',
                'title_gradient_to' => '#34d399',
                'overlay_from' => 'rgba(0,0,0,0.6)',
                'overlay_via' => 'rgba(0,0,0,0.4)',
                'overlay_to' => 'rgba(0,0,0,0.7)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_dot' => '#10b981',
                'search_bg' => 'rgba(255,255,255,0.1)',
                'search_focus' => '#818cf8',
            ],
            'stats' => [
                'bg' => '#ffffff',
                'border' => '#f1f5f9',
                'text' => '#475569',
                'properties_from' => '#4f46e5',
                'properties_to' => '#818cf8',
                'experience_from' => '#059669',
                'experience_to' => '#34d399',
                'clients_from' => '#9333ea',
                'clients_to' => '#c084fc',
                'zones_from' => '#d97706',
                'zones_to' => '#fbbf24',
            ],
            'services' => [
                'bg' => '#ffffff',
                'badge_bg' => '#eef2ff',
                'badge_text' => '#4f46e5',
                'title' => '#0f172a',
                'subtitle' => '#475569',
                'card_bg_from' => '#f8fafc',
                'card_bg_to' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_title' => '#0f172a',
                'card_text' => '#475569',
                'feature1_from' => '#4f46e5',
                'feature1_to' => '#818cf8',
                'feature1_glow' => 'rgba(79, 70, 229, 0.05)',
                'feature2_from' => '#059669',
                'feature2_to' => '#34d399',
                'feature2_glow' => 'rgba(5, 150, 105, 0.05)',
                'feature3_from' => '#9333ea',
                'feature3_to' => '#c084fc',
                'feature3_glow' => 'rgba(147, 51, 234, 0.05)',
                'feature4_from' => '#f59e0b',
                'feature4_to' => '#fbbf24',
                'feature4_glow' => 'rgba(245, 158, 11, 0.05)',
                'feature5_from' => '#f43f5e',
                'feature5_to' => '#fb7185',
                'feature5_glow' => 'rgba(244, 63, 94, 0.05)',
                'feature6_from' => '#06b6d4',
                'feature6_to' => '#22d3ee',
                'feature6_glow' => 'rgba(6, 182, 212, 0.05)',
            ],
            'cta_sale' => [
                'overlay_from' => 'rgba(49,46,129,0.95)',
                'overlay_via' => 'rgba(49,46,129,0.8)',
                'overlay_to' => 'transparent',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#34d399',
                'highlight_from' => '#34d399',
                'highlight_to' => '#22d3ee',
                'btn_primary_from' => '#10b981',
                'btn_primary_to' => '#06b6d4',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'decor' => 'rgba(16,185,129,0.2)',
            ],
            'cta_rent' => [
                'overlay_from' => 'rgba(15,23,42,0.95)',
                'overlay_via' => 'rgba(15,23,42,0.8)',
                'overlay_to' => 'transparent',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#fbbf24',
                'highlight_from' => '#fbbf24',
                'highlight_to' => '#fb923c',
                'btn_primary_from' => '#f59e0b',
                'btn_primary_to' => '#f97316',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'check_color' => '#fbbf24',
                'decor' => 'rgba(245,158,11,0.2)',
            ],
            'process' => [
                'bg' => '#0f172a',
                'glow1' => 'rgba(79,70,229,0.2)',
                'glow2' => 'rgba(16,185,129,0.2)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#34d399',
                'highlight_from' => '#818cf8',
                'highlight_to' => '#34d399',
                'subtitle' => '#94a3b8',
                'step1_from' => '#4f46e5',
                'step1_to' => '#818cf8',
                'step2_from' => '#9333ea',
                'step2_to' => '#c084fc',
                'step3_from' => '#0891b2',
                'step3_to' => '#22d3ee',
                'step4_from' => '#059669',
                'step4_to' => '#34d399',
                'card_bg' => 'rgba(30,41,59,0.5)',
                'card_border' => 'rgba(51,65,85,0.5)',
                'card_text' => '#94a3b8',
            ],
            'testimonials' => [
                'bg' => '#ffffff',
                'badge_bg' => '#fffbeb',
                'badge_text' => '#d97706',
                'title' => '#0f172a',
                'subtitle' => '#475569',
                'card_bg_from' => '#f8fafc',
                'card_bg_to' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_text' => '#475569',
                'stars' => '#fbbf24',
                'quote1' => '#e0e7ff',
                'quote2' => '#d1fae5',
                'quote3' => '#f3e8ff',
                'avatar1_from' => '#4f46e5',
                'avatar1_to' => '#10b981',
                'avatar2_from' => '#9333ea',
                'avatar2_to' => '#ec4899',
                'avatar3_from' => '#f59e0b',
                'avatar3_to' => '#f97316',
                'name' => '#0f172a',
                'role' => '#64748b',
            ],
            'about' => [
                'bg_from' => '#f8fafc',
                'bg_to' => 'rgba(238,242,255,0.3)',
                'image_overlay' => 'rgba(15,23,42,0.6)',
                'card_bg' => '#ffffff',
                'card_title' => '#0f172a',
                'card_text' => '#475569',
                'badge_bg' => '#e0e7ff',
                'badge_text' => '#4f46e5',
                'title' => '#0f172a',
                'text' => '#475569',
                'value_title' => '#0f172a',
                'value_text' => '#475569',
                'value1_bg' => '#e0e7ff',
                'value1_icon' => '#4f46e5',
                'value2_bg' => '#d1fae5',
                'value2_icon' => '#059669',
                'value3_bg' => '#f3e8ff',
                'value3_icon' => '#9333ea',
            ],
            'contact' => [
                'bg' => '#ffffff',
                'badge_bg' => '#eef2ff',
                'badge_text' => '#4f46e5',
                'title' => '#0f172a',
                'text' => '#475569',
                'method_bg' => '#f8fafc',
                'method_title' => '#0f172a',
                'method_text' => '#475569',
                'phone_from' => '#10b981',
                'phone_to' => '#059669',
                'whatsapp_from' => '#22c55e',
                'whatsapp_to' => '#16a34a',
                'email_from' => '#6366f1',
                'email_to' => '#4f46e5',
                'form_bg_from' => '#f8fafc',
                'form_bg_to' => '#ffffff',
                'form_border' => '#f1f5f9',
                'label' => '#334155',
                'input_bg' => '#ffffff',
                'input_border' => '#e2e8f0',
                'input_text' => '#0f172a',
                'privacy_text' => '#475569',
                'link' => '#4f46e5',
            ],

            // === COLORES DE PROPIEDADES ===
            'property_cards' => [
                'price_from' => '#4f46e5',
                'price_to' => '#10b981',
                'sale_badge' => '#10b981',
                'rent_badge' => '#f59e0b',
                'favorite_hover' => '#f43f5e',
                'title_hover' => '#4f46e5',
            ],
            'filters' => [
                'active_from' => '#4f46e5',
                'active_to' => '#10b981',
                'focus_border' => '#6366f1',
                'focus_ring' => 'rgba(99,102,241,0.2)',
            ],
            'properties' => [
                'bg_from' => '#f8fafc',
                'bg_to' => '#ffffff',
                'badge_bg' => '#eef2ff',
                'badge_text' => '#4f46e5',
                'title' => '#0f172a',
                'subtitle' => '#475569',
                'filter_bg' => '#ffffff',
                'filter_border' => '#e2e8f0',
                'filter_icon' => '#94a3b8',
                'filter_clear' => '#475569',
                'filter_divider' => '#f1f5f9',
                'input_bg' => '#f8fafc',
                'input_border' => '#e2e8f0',
                'input_text' => '#0f172a',
                'tag_active_from' => '#4f46e5',
                'tag_active_to' => '#10b981',
                'tag_inactive_bg' => '#f1f5f9',
                'tag_inactive_text' => '#475569',
                'tag_inactive_hover' => '#e2e8f0',
                'card_bg' => '#ffffff',
                'card_border' => '#f1f5f9',
                'card_title' => '#0f172a',
                'card_location' => '#64748b',
                'card_meta' => '#475569',
                'card_divider' => '#f1f5f9',
                'type_badge_bg' => 'rgba(255,255,255,0.9)',
                'type_badge_text' => '#0f172a',
                'sale_badge' => '#10b981',
                'rent_badge' => '#f59e0b',
                'fav_btn_bg' => 'rgba(255,255,255,0.9)',
                'fav_btn_icon' => '#475569',
            ],

            // === COLORES DE DETALLE DE PROPIEDAD ===
            'property_detail' => [
                'price_from' => '#4f46e5',
                'price_to' => '#10b981',
                'badge_sale' => '#10b981',
                'badge_rent' => '#f59e0b',
                'feature_icon' => '#4f46e5',
                'section_title' => '#1e293b',
                'contact_button_from' => '#4f46e5',
                'contact_button_to' => '#10b981',
            ],
            'gallery' => [
                'thumbnail_border_active' => '#4f46e5',
                'fullscreen_bg' => 'rgba(0,0,0,0.95)',
                'nav_button_bg' => 'rgba(255,255,255,0.1)',
                'nav_button_hover' => 'rgba(255,255,255,0.2)',
            ],
            'agent_card' => [
                'border' => '#e2e8f0',
                'name_color' => '#1e293b',
                'phone_icon' => '#10b981',
                'email_icon' => '#4f46e5',
                'whatsapp_button_from' => '#22c55e',
                'whatsapp_button_to' => '#16a34a',
            ],

            // === COLORES DE PÁGINA DE CONTACTO ===
            'contact_page' => [
                'hero_bg_from' => '#4f46e5',
                'hero_bg_to' => '#10b981',
                'form_border' => '#e2e8f0',
                'form_focus_border' => '#4f46e5',
                'submit_button_from' => '#4f46e5',
                'submit_button_to' => '#10b981',
                'info_icon_phone' => '#10b981',
                'info_icon_email' => '#4f46e5',
                'info_icon_location' => '#f59e0b',
                'map_marker' => '#4f46e5',
            ],

            // === COLORES DE PÁGINA NOSOTROS ===
            'about_page' => [
                'hero_bg_from' => '#4f46e5',
                'hero_bg_to' => '#10b981',
                'section_title' => '#1e293b',
                'team_card_border' => '#e2e8f0',
                'team_name' => '#1e293b',
                'team_role' => '#64748b',
                'value_icon_1' => '#4f46e5',
                'value_icon_2' => '#10b981',
                'value_icon_3' => '#f59e0b',
                'timeline_line' => '#e2e8f0',
                'timeline_dot_active' => '#4f46e5',
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
