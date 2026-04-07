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
        // Siempre cargar colores globales + defaults globales
        $globalConfig = self::getActiveForView('global');
        $globalColors = self::mergeWithDefaults('global', (array) ($globalConfig?->colors ?? []));

        // Si es la vista global, solo devolver esos colores
        if ($viewSlug === 'global') {
            return $globalColors;
        }

        // Cargar colores especificos de la vista + defaults de la vista
        $viewConfig = self::getActiveForView($viewSlug);
        $viewColors = self::mergeWithDefaults($viewSlug, (array) ($viewConfig?->colors ?? []));

        // Merge: los colores de la vista sobrescriben los globales
        return array_replace_recursive($globalColors, $viewColors);
    }

    /**
     * Mezclar defaults de una vista con colores concretos (los concretos sobrescriben defaults)
     */
    public static function mergeWithDefaults(string $viewSlug, array $colors = []): array
    {
        $defaults = self::getDefaultColorsForView($viewSlug);

        if (empty($defaults)) {
            return $colors;
        }

        return array_replace_recursive($defaults, $colors);
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
                'background_scrolled' => 'rgba(255,255,255,0.95)',
                'background_top' => 'transparent',
                'shadow' => '0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10)',
                'logo_gradient_from' => '#D1A054',
                'logo_gradient_to' => '#768D59',
                'brand_text_scrolled' => '#0f172a',
                'brand_text_top' => '#ffffff',
                'nav_text_scrolled' => '#334155',
                'nav_text_top' => 'rgba(255,255,255,0.9)',
                'nav_text_top_hover' => '#ffffff',
                'nav_hover_bg' => 'rgba(15,23,42,0.05)',
                'cta_button_from' => '#D1A054',
                'cta_button_to' => '#768D59',
                'cta_ring' => 'rgba(209,160,84,0.2)',
                'nav_hover' => '#D1A054',
                'dropdown_bg' => '#ffffff',
                'dropdown_border' => '#e2e8f0',
                'dropdown_shadow' => '0 25px 50px -12px rgba(0,0,0,0.25)',
                'dropdown_title' => '#0f172a',
                'dropdown_text' => '#334155',
                'dropdown_text_hover' => '#0f172a',
                'dropdown_hover_bg' => '#f8fafc',
                'dropdown_icon' => '#64748b',
                'dropdown_icon_hover_bg' => '#f1f5f9',
                'dropdown_icon_hover' => '#334155',
                'dropdown_tag_bg' => '#ffffff',
                'dropdown_tag_border' => '#e2e8f0',
                'dropdown_tag_text' => '#475569',
                'dropdown_tag_border_hover' => '#cbd5e1',
                'dropdown_tag_text_hover' => '#0f172a',
                'lang_text_scrolled' => '#334155',
                'lang_border_scrolled' => '#cbd5e1',
                'lang_text_top' => 'rgba(255,255,255,0.9)',
                'lang_border_top' => 'rgba(255,255,255,0.3)',
                'phone_text_scrolled' => '#475569',
                'phone_text_top' => 'rgba(255,255,255,0.9)',
                'favorites_bg_scrolled' => '#ffffff',
                'favorites_border_scrolled' => '#cbd5e1',
                'favorites_text_scrolled' => '#334155',
                'favorites_bg_top' => 'rgba(255,255,255,0.1)',
                'favorites_bg_top_hover' => 'rgba(255,255,255,0.2)',
                'favorites_border_top' => 'rgba(255,255,255,0.3)',
                'favorites_text_top' => '#ffffff',
                'mobile_toggle_text_scrolled' => '#0f172a',
                'mobile_toggle_text_top' => '#ffffff',
                'mobile_toggle_hover_bg' => 'rgba(15,23,42,0.1)',
                'mobile_panel_bg' => '#ffffff',
                'mobile_panel_border' => '#f1f5f9',
                'mobile_link_text' => '#334155',
                'mobile_link_hover_bg' => '#f8fafc',
                'mobile_section_bg' => 'rgba(248,250,252,0.6)',
                'mobile_section_border' => '#f1f5f9',
                'mobile_section_hover_bg' => 'rgba(241,245,249,0.7)',
                'mobile_section_title' => '#1e293b',
                'mobile_section_title_muted' => '#64748b',
                'mobile_menu_icon_active' => '#D1A054',
            ],
            'footer' => [
                'background' => '#1C1C1C',
                'accent_from' => '#D1A054',
                'accent_to' => '#768D59',
                'pattern' => 'rgba(255,255,255,0.03)',
                'divider' => 'rgba(255,255,255,0.1)',
                'text_primary' => '#ffffff',
                'text_secondary' => '#94a3b8',
                'text_muted' => '#64748b',
                'newsletter_badge_from' => 'rgba(209,160,84,0.2)',
                'newsletter_badge_to' => 'rgba(118,141,89,0.2)',
                'newsletter_badge_text' => '#768D59',
                'newsletter_title_from' => '#D1A054',
                'newsletter_title_to' => '#768D59',
                'newsletter_button_from' => '#D1A054',
                'newsletter_button_to' => '#768D59',
                'input_bg' => 'rgba(255,255,255,0.05)',
                'input_border' => 'rgba(255,255,255,0.1)',
                'input_text' => '#ffffff',
                'input_placeholder' => '#64748b',
                'social_bg' => 'rgba(255,255,255,0.05)',
                'social_text' => '#cbd5e1',
                'social_hover_bg' => 'rgba(255,255,255,0.1)',
                'social_hover_text' => '#ffffff',
                'link_text' => '#94a3b8',
                'link_hover' => '#ffffff',
                'copyright_text' => '#64748b',
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
                'body_bg' => '#f8fafc',
                'body_text' => '#0f172a',
                'text_white' => '#ffffff',
                'text_white_90' => 'rgba(255,255,255,0.9)',
                'text_white_80' => 'rgba(255,255,255,0.8)',
                'text_white_70' => 'rgba(255,255,255,0.7)',
                'text_white_60' => 'rgba(255,255,255,0.6)',
                'bg_white_5' => 'rgba(255,255,255,0.05)',
                'bg_white_10' => 'rgba(255,255,255,0.1)',
                'bg_white_20' => 'rgba(255,255,255,0.2)',
                'border_white_10' => 'rgba(255,255,255,0.1)',
                'border_white_20' => 'rgba(255,255,255,0.2)',
                'border_white_30' => 'rgba(255,255,255,0.3)',
                'slate_900' => '#0f172a',
                'slate_800' => '#1e293b',
                'slate_700' => '#334155',
                'slate_600' => '#475569',
                'slate_500' => '#64748b',
                'slate_400' => '#94a3b8',
                'slate_300' => '#cbd5e1',
                'slate_200' => '#e2e8f0',
                'slate_100' => '#f1f5f9',
                'slate_50' => '#f8fafc',
                'slate_50_60' => 'rgba(248,250,252,0.6)',
                'slate_100_70' => 'rgba(241,245,249,0.7)',
                'back_to_top_from' => '#D1A054',
                'back_to_top_to' => '#768D59',
                'back_to_top_ring' => 'rgba(209,160,84,0.2)',
                'preloader_bg' => '#ffffff',
                'preloader_track' => '#e2e8f0',
                'preloader_border_1' => '#D1A054',
                'preloader_border_2' => '#768D59',
                'scrollbar_track' => '#f1f5f9',
                'scrollbar_from' => '#D1A054',
                'scrollbar_to' => '#768D59',
                'scrollbar_hover_from' => '#A52A2A',
                'scrollbar_hover_to' => '#768D59',
                'glass_bg' => 'rgba(255,255,255,0.8)',
                'swiper_bullet' => 'rgba(255,255,255,0.5)',
                'swiper_nav_bg' => 'rgba(0,0,0,0.3)',
                'swiper_nav_text' => '#ffffff',
                'card_hover_shadow' => '0 25px 50px -12px rgba(0,0,0,0.25)',
                'skeleton_from' => '#f0f0f0',
                'skeleton_mid' => '#e0e0e0',
                'skeleton_to' => '#f0f0f0',
                'favorites_active_text' => '#e11d48',
                'favorites_active_bg' => '#ffe4e6',
                'favorites_active_border' => '#fecdd3',
                'favorites_active_ring' => '#ffe4e6',
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
                'placeholder_from' => '#0f172a',
                'placeholder_to' => '#334155',
                'placeholder_text' => 'rgba(255,255,255,0.5)',
                'overlay_from' => 'rgba(28,28,28,0.7)',
                'overlay_via' => 'rgba(28,28,28,0.4)',
                'overlay_to' => 'rgba(28,28,28,0.8)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_dot' => '#768D59',
                'subtitle_text' => 'rgba(255,255,255,0.8)',
                'search_icon' => 'rgba(255,255,255,0.5)',
                'search_bg' => 'rgba(255,255,255,0.1)',
                'search_input_bg' => 'rgba(255,255,255,0.1)',
                'search_input_border' => 'rgba(255,255,255,0.1)',
                'search_input_text' => '#ffffff',
                'search_input_placeholder' => 'rgba(255,255,255,0.5)',
                'search_focus' => '#D1A054',
                'quick_filter_bg' => 'rgba(255,255,255,0.1)',
                'quick_filter_border' => 'rgba(255,255,255,0.1)',
                'quick_filter_text' => 'rgba(255,255,255,0.8)',
                'quick_filter_hover_bg' => 'rgba(255,255,255,0.2)',
                'scroll_text' => 'rgba(255,255,255,0.6)',
                'scroll_text_hover' => '#ffffff',
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
                'title' => '#ffffff',
                'text' => 'rgba(255,255,255,0.8)',
                'highlight_from' => '#768D59',
                'highlight_to' => '#D1A054',
                'stat_value' => '#ffffff',
                'stat_label' => 'rgba(255,255,255,0.6)',
                'btn_primary_from' => '#768D59',
                'btn_primary_to' => '#768D59',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'btn_secondary_border' => 'rgba(255,255,255,0.2)',
                'btn_secondary_text' => '#ffffff',
                'btn_secondary_hover_bg' => 'rgba(255,255,255,0.2)',
                'decor' => 'rgba(118,141,89,0.2)',
                'decor_border' => 'rgba(255,255,255,0.1)',
            ],
            'cta_rent' => [
                'overlay_from' => 'rgba(28,28,28,0.95)',
                'overlay_via' => 'rgba(28,28,28,0.8)',
                'overlay_to' => 'transparent',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#D1A054',
                'title' => '#ffffff',
                'text' => 'rgba(255,255,255,0.8)',
                'highlight_from' => '#D1A054',
                'highlight_to' => '#D1A054',
                'btn_primary_from' => '#D1A054',
                'btn_primary_to' => '#D1A054',
                'btn_secondary_bg' => 'rgba(255,255,255,0.1)',
                'btn_secondary_border' => 'rgba(255,255,255,0.2)',
                'btn_secondary_text' => '#ffffff',
                'btn_secondary_hover_bg' => 'rgba(255,255,255,0.2)',
                'check_color' => '#D1A054',
                'feature_text' => 'rgba(255,255,255,0.8)',
                'decor' => 'rgba(209,160,84,0.2)',
                'decor_border' => 'rgba(255,255,255,0.1)',
            ],
            'process' => [
                'bg' => '#1C1C1C',
                'glow1' => 'rgba(209,160,84,0.2)',
                'glow2' => 'rgba(118,141,89,0.2)',
                'pattern' => 'rgba(255,255,255,0.05)',
                'badge_bg' => 'rgba(255,255,255,0.1)',
                'badge_text' => '#768D59',
                'title' => '#ffffff',
                'highlight_from' => '#D1A054',
                'highlight_to' => '#768D59',
                'subtitle' => '#979790',
                'step1_from' => '#D1A054',
                'step1_to' => '#D1A054',
                'step2_from' => '#A52A2A',
                'step2_to' => '#A52A2A',
                'step3_from' => '#A52A2A',
                'step3_to' => '#A52A2A',
                'step4_from' => '#768D59',
                'step4_to' => '#768D59',
                'step_title' => '#ffffff',
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
                'image_caption_title' => '#ffffff',
                'image_caption_subtitle' => 'rgba(255,255,255,0.7)',
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
                'privacy_check_border' => '#cbd5e1',
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
                'filter_label' => '#334155',
                'filter_label_muted' => '#64748b',
                'filter_results_text' => '#475569',
                'filter_count_bg' => '#ffffff',
                'filter_count_text' => '#4f46e5',
                'filter_clear_bg' => '#ffffff',
                'filter_clear_border' => '#e2e8f0',
                'filter_clear_text' => '#334155',
                'filter_clear_hover_bg' => '#f8fafc',
                'filter_divider' => '#f1f5f9',
                'input_bg' => '#FFFAF5',
                'input_border' => '#e2e8f0',
                'input_text' => '#1C1C1C',
                'modal_backdrop' => 'rgba(0,0,0,0.5)',
                'modal_bg' => '#ffffff',
                'modal_header_bg_from' => '#f8fafc',
                'modal_header_bg_to' => '#ffffff',
                'modal_header_border' => '#e2e8f0',
                'modal_title' => '#0f172a',
                'modal_subtitle' => '#64748b',
                'modal_close_icon' => '#64748b',
                'modal_close_hover_bg' => '#f1f5f9',
                'modal_footer_bg' => '#f8fafc',
                'modal_footer_border' => '#e2e8f0',
                'modal_clear_bg' => '#ffffff',
                'modal_clear_border' => '#cbd5e1',
                'modal_clear_text' => '#334155',
                'modal_clear_hover_bg' => '#f1f5f9',
                'tag_type_bg' => '#e0e7ff',
                'tag_type_text' => '#4338ca',
                'tag_type_remove_hover' => '#312e81',
                'tag_operation_bg' => '#d1fae5',
                'tag_operation_text' => '#047857',
                'tag_operation_remove_hover' => '#064e3b',
                'tag_bedrooms_bg' => '#dbeafe',
                'tag_bedrooms_text' => '#1d4ed8',
                'tag_bedrooms_remove_hover' => '#1e3a8a',
                'tag_bathrooms_bg' => '#cffafe',
                'tag_bathrooms_text' => '#0e7490',
                'tag_bathrooms_remove_hover' => '#164e63',
                'tag_price_bg' => '#fef3c7',
                'tag_price_text' => '#b45309',
                'tag_price_remove_hover' => '#78350f',
                'tag_city_bg' => '#f3e8ff',
                'tag_city_text' => '#7e22ce',
                'tag_city_remove_hover' => '#581c87',
                'mobile_count_bg' => '#ef4444',
                'mobile_count_text' => '#ffffff',
                'option_inactive_bg' => '#f1f5f9',
                'option_inactive_text' => '#334155',
                'option_inactive_hover_bg' => '#e2e8f0',
                'option_type_active_bg' => '#eef2ff',
                'option_type_active_text' => '#4338ca',
                'option_type_active_ring' => '#6366f1',
                'option_operation_active_bg' => '#ecfdf5',
                'option_operation_active_text' => '#047857',
                'option_operation_active_ring' => '#10b981',
                'option_feature_active_bg' => '#6366f1',
                'option_feature_active_text' => '#ffffff',
                'option_feature_active_ring' => '#4f46e5',
                'skeleton_bg' => '#ffffff',
                'skeleton_border' => '#f1f5f9',
                'empty_icon_bg' => '#f1f5f9',
                'empty_icon' => '#94a3b8',
                'empty_title' => '#0f172a',
                'empty_text' => '#475569',
                'pagination_border' => '#e2e8f0',
                'pagination_bg' => '#ffffff',
                'pagination_text' => '#475569',
                'pagination_hover_bg' => '#f8fafc',
                'pagination_ellipsis' => '#94a3b8',
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
                'fav_btn_border' => '#e2e8f0',
                'fav_btn_icon' => '#5B5B5B',
                'image_overlay' => 'rgba(0,0,0,0.5)',
            ],

            // === COLORES DE DETALLE DE PROPIEDAD ===
            'property_detail' => [
                'price_from' => '#D1A054',
                'price_to' => '#768D59',
                'badge_sale' => '#768D59',
                'badge_sale_from' => '#768D59',
                'badge_sale_to' => '#768D59',
                'badge_rent' => '#D1A054',
                'badge_rent_from' => '#D1A054',
                'badge_rent_to' => '#D1A054',
                'feature_icon' => '#D1A054',
                'section_title' => '#0f172a',
                'subsection_title' => '#0f172a',
                'title' => '#0f172a',
                'body_text' => '#334155',
                'muted_text' => '#475569',
                'subtle_text' => '#64748b',
                'meta_text' => '#475569',
                'meta_icon' => '#475569',
                'meta_divider' => '#cbd5e1',
                'breadcrumb_link' => '#475569',
                'breadcrumb_link_hover' => '#0f172a',
                'breadcrumb_separator' => '#94a3b8',
                'breadcrumb_current' => '#0f172a',
                'panel_bg' => '#ffffff',
                'panel_border' => '#e2e8f0',
                'panel_shadow' => '0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10)',
                'card_bg' => '#f8fafc',
                'card_border' => '#f1f5f9',
                'chip_bg' => '#f1f5f9',
                'chip_text' => '#475569',
                'map_bg' => '#f8fafc',
                'map_border' => '#f1f5f9',
                'note_bg' => '#f8fafc',
                'note_border' => '#f1f5f9',
                'note_label' => '#475569',
                'note_text' => '#334155',
                'source_notice_bg' => '#fef3c7',
                'source_notice_border' => '#fcd34d',
                'source_notice_label' => '#92400e',
                'source_notice_text' => '#78350f',
                'error_bg' => '#fef2f2',
                'error_border' => '#fecaca',
                'error_title' => '#881337',
                'error_text' => '#9f1239',
                'error_secondary_bg' => '#ffffff',
                'error_secondary_border' => '#fecaca',
                'error_secondary_text' => '#be123c',
                'error_secondary_hover_bg' => '#fff1f2',
                'share_button_bg' => 'rgba(255,255,255,0.9)',
                'share_button_border' => '#e2e8f0',
                'share_button_text' => '#0f172a',
                'share_button_hover_bg' => '#ffffff',
                'call_button_bg' => '#ffffff',
                'call_button_border' => '#e2e8f0',
                'call_button_text' => '#334155',
                'call_button_icon' => '#334155',
                'call_button_hover_bg' => '#f8fafc',
                'favorite_button_bg' => '#ffffff',
                'favorite_button_border' => '#e2e8f0',
                'favorite_button_text' => '#475569',
                'favorite_button_hover_bg' => '#f8fafc',
                'feature_tag_bg' => '#f1f5f9',
                'feature_tag_text' => '#475569',
                'empty_state_bg' => '#f8fafc',
                'empty_state_border' => '#f1f5f9',
                'empty_state_text' => '#334155',
                'contact_button_from' => '#D1A054',
                'contact_button_to' => '#768D59',
                'contact_button_text' => '#ffffff',
                'decor_glow_from' => 'rgba(209,160,84,.35)',
                'decor_glow_to' => 'rgba(118,141,89,.35)',
                'decor_pattern_dot' => 'rgba(15,23,42,0.06)',
                'type_badge_bg' => 'rgba(255,255,255,0.85)',
                'type_badge_text' => '#1C1C1C',
            ],
            'gallery' => [
                'panel_bg' => '#ffffff',
                'panel_border' => '#e2e8f0',
                'shell_bg' => '#f1f5f9',
                'count_badge_bg' => 'rgba(15,23,42,.55)',
                'count_badge_text' => 'rgba(255,255,255,0.95)',
                'thumbs_strip_border' => '#f1f5f9',
                'thumbnail_bg' => '#f8fafc',
                'thumbnail_border' => '#e2e8f0',
                'thumbnail_border_active' => '#D1A054',
                'fullscreen_bg' => 'rgba(28,28,28,0.95)',
                'nav_button_bg' => 'rgba(255,255,255,0.1)',
                'nav_button_hover' => 'rgba(255,255,255,0.2)',
            ],
            'agent_card' => [
                'panel_bg' => '#ffffff',
                'panel_border' => '#e2e8f0',
                'border' => '#e2e8f0',
                'title' => '#0f172a',
                'name_color' => '#0f172a',
                'subtitle_color' => '#475569',
                'avatar_bg' => '#f8fafc',
                'avatar_border' => '#e2e8f0',
                'avatar_icon' => '#94a3b8',
                'mls_card_bg' => '#f8fafc',
                'mls_card_border' => '#f1f5f9',
                'mls_name' => '#0f172a',
                'mls_office' => '#475569',
                'contact_link' => '#334155',
                'contact_link_hover' => '#0f172a',
                'contact_unavailable' => '#475569',
                'phone_icon' => '#768D59',
                'email_icon' => '#D1A054',
                'whatsapp_button_from' => '#22c55e',
                'whatsapp_button_to' => '#16a34a',
                'whatsapp_button_text' => '#ffffff',
                'primary_badge_from' => '#D1A054',
                'primary_badge_to' => '#768D59',
                'primary_badge_text' => '#ffffff',
            ],

            // === COLORES DE PÁGINA DE CONTACTO ===
            'contact_page' => [
                'hero_bg_from' => '#1C1C1C',
                'hero_bg_via' => '#D1A054',
                'hero_bg_to' => '#768D59',
                'hero_pattern_dot' => 'rgba(255,255,255,0.1)',
                'hero_badge_bg' => 'rgba(255,255,255,0.1)',

                'section_bg' => '#ffffff',
                'section_title' => '#1C1C1C',
                'section_text' => '#475569',

                'card_bg_from' => '#f8fafc',
                'card_bg_to' => '#ffffff',
                'card_border' => '#e2e8f0',
                'card_title' => '#1C1C1C',
                'card_value' => '#D1A054',
                'card_value_phone' => '#D1A054',
                'card_value_whatsapp' => '#22c55e',
                'card_value_email' => '#D1A054',

                // Legacy single-color keys kept for compatibility with older configs
                'info_icon_phone' => '#768D59',
                'info_icon_email' => '#D1A054',
                'info_icon_phone_from' => '#768D59',
                'info_icon_phone_to' => '#768D59',
                'info_icon_whatsapp_from' => '#22c55e',
                'info_icon_whatsapp_to' => '#16a34a',
                'info_icon_email_from' => '#D1A054',
                'info_icon_email_to' => '#D1A054',

                'social_title' => '#1C1C1C',
                'social_bg' => '#f1f5f9',
                'social_icon' => '#475569',

                'form_bg_from' => '#ffffff',
                'form_bg_to' => '#f8fafc',
                'form_border' => '#e2e8f0',
                'form_focus_border' => '#D1A054',
                'form_title' => '#1C1C1C',
                'form_subtitle' => '#475569',
                'alert_success_bg' => '#d1fae5',
                'alert_success_text' => '#065f46',
                'alert_error_bg' => '#fee2e2',
                'alert_error_text' => '#991b1b',
                'label' => '#334155',
                'required_mark' => '#ef4444',
                'input_bg' => '#f8fafc',
                'input_border' => '#e2e8f0',
                'input_text' => '#1C1C1C',
                'checkbox_border' => '#cbd5e1',
                'checkbox_accent' => '#D1A054',
                'privacy_text' => '#475569',
                'submit_button_from' => '#D1A054',
                'submit_button_to' => '#768D59',

                'faq_bg_from' => '#f8fafc',
                'faq_bg_to' => '#ffffff',
                'faq_title' => '#1C1C1C',
                'faq_border' => '#e2e8f0',
                'faq_question' => '#1C1C1C',
                'faq_icon' => '#D1A054',
                'faq_answer' => '#475569',
                'faq_item_bg' => '#ffffff',
                'faq_item_open_from' => 'rgba(209,160,84,0.05)',
                'faq_item_open_to' => 'rgba(118,141,89,0.05)',
            ],

            // === COLORES DE PÁGINA NOSOTROS ===
            'about_page' => [
                'hero_bg_from' => '#1C1C1C',
                'hero_bg_via' => 'rgba(209,160,84,0.95)',
                'hero_bg_to' => '#768D59',
                'hero_pattern_dot' => 'rgba(255,255,255,0.10)',
                'hero_badge_bg' => 'rgba(255,255,255,0.12)',
                'hero_badge_text' => 'rgba(255,255,255,0.9)',
                'hero_title' => '#ffffff',
                'hero_highlight_from' => 'rgba(52,211,153,1)',
                'hero_highlight_to' => 'rgba(34,211,238,1)',
                'hero_subtitle' => 'rgba(255,255,255,0.8)',
                'hero_secondary_cta_bg' => 'rgba(255,255,255,0.10)',
                'hero_secondary_cta_text' => 'rgba(255,255,255,0.95)',
                'hero_secondary_cta_border' => 'rgba(255,255,255,0.2)',

                'summary_section_bg' => '#ffffff',
                'summary_badge_bg' => 'rgba(209,160,84,0.08)',
                'summary_badge_text' => '#D1A054',
                'summary_media_border' => '#e2e8f0',
                'summary_media_box_bg_from' => 'rgba(248,250,252,1)',
                'summary_media_box_bg_to' => 'rgba(255,255,255,1)',
                'summary_direct_label' => '#64748b',

                'values_section_bg_from' => 'rgba(248,250,252,1)',
                'values_section_bg_to' => 'rgba(255,255,255,1)',
                'values_badge_bg' => 'rgba(118,141,89,0.10)',
                'values_badge_text' => 'rgba(5,150,105,1)',
                'value_card_border' => '#e2e8f0',
                'value_card_bg_from' => 'rgba(255,255,255,1)',
                'value_card_bg_to' => 'rgba(248,250,252,1)',

                'timeline_section_bg' => '#ffffff',
                'timeline_line' => '#e2e8f0',
                'timeline_dot_active' => '#D1A054',
                'timeline_card_border' => '#e2e8f0',
                'timeline_card_bg_from' => 'rgba(255,255,255,1)',
                'timeline_card_bg_to' => 'rgba(248,250,252,1)',
                'timeline_year' => '#D1A054',

                'team_section_bg_from' => 'rgba(248,250,252,1)',
                'team_section_bg_to' => 'rgba(255,255,255,1)',
                'team_badge_bg' => 'rgba(147,51,234,0.10)',
                'team_badge_text' => 'rgba(147,51,234,1)',
                'section_title' => '#1C1C1C',
                'body_text' => '#475569',
                'team_card_border' => '#e2e8f0',
                'team_card_bg' => '#ffffff',
                'team_name' => '#1C1C1C',
                'team_role' => '#5B5B5B',

                'cta_section_bg' => '#ffffff',
                'cta_box_border' => '#e2e8f0',
                'cta_box_bg_from' => 'rgba(209,160,84,0.08)',
                'cta_box_bg_to' => 'rgba(118,141,89,0.10)',
                'cta_secondary_btn_border' => '#e2e8f0',
                'cta_secondary_btn_text' => 'rgba(30,41,59,1)',
                'cta_secondary_btn_bg' => 'rgba(255,255,255,0.7)',

                'value_icon_1' => '#D1A054',
                'value_icon_2' => '#768D59',
                'value_icon_3' => '#A52A2A',
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


