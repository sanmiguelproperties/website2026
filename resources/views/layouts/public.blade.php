<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @php
        $pageData = $pageData ?? null;
        $currentLocale = app()->getLocale() === 'en' ? 'en' : 'es';
        $txt = static fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($currentLocale === 'en' ? $en : $es);
        $pageEntity = $pageData?->entity;
        $seoTitleOverride = trim((string) ($seoTitleOverride ?? ''));
        $seoDescriptionOverride = trim((string) ($seoDescriptionOverride ?? ''));
        $seoKeywordsOverride = trim((string) ($seoKeywordsOverride ?? ''));

        $seoTitle = $pageEntity && method_exists($pageEntity, 'metaTitle')
            ? ($pageEntity->metaTitle($currentLocale) ?: ($pageEntity->title($currentLocale) ?: $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties')))
            : $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties');
        if ($seoTitleOverride !== '') {
            $seoTitle = $seoTitleOverride;
        }

        $seoDescription = $pageEntity && method_exists($pageEntity, 'metaDescription')
            ? ($pageEntity->metaDescription($currentLocale) ?: $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.'))
            : $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.');
        if ($seoDescriptionOverride !== '') {
            $seoDescription = $seoDescriptionOverride;
        }

        $seoKeywords = $currentLocale === 'en'
            ? ($pageEntity->meta_keywords_en ?? null)
            : ($pageEntity->meta_keywords_es ?? null);
        if ($seoKeywordsOverride !== '') {
            $seoKeywords = $seoKeywordsOverride;
        }
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $seoDescription }}">
    @if(!empty($seoKeywords))
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $seoTitle)</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Frontend Color Variables (Dynamic from Database) -->
    @php
        $pageData = $pageData ?? null;
        $settings = $settings ?? [];
        $frontendColorService = app(\App\Services\FrontendColorService::class);
        // Detectar la vista actual basándose en la ruta
        $currentView = $frontendColorService->detectCurrentView();
        // Generar CSS con colores combinados (global + vista específica)
        $frontendCss = $frontendColorService->generateCssForView($currentView);

        // CMS Site Settings (logos, contacto, etc.) - disponibles en todo el layout
        $cmsSettings = \App\Models\CmsSiteSetting::getAllCached();
        $siteLogo = $cmsSettings->firstWhere('setting_key', 'site_logo');
        $siteLogoDark = $cmsSettings->firstWhere('setting_key', 'site_logo_dark');
        $siteLogoUrl = $siteLogo?->mediaAsset?->serving_url ?? $siteLogo?->mediaAsset?->url;
        $siteLogoDarkUrl = $siteLogoDark?->mediaAsset?->serving_url ?? $siteLogoDark?->mediaAsset?->url;
        $siteName = $cmsSettings->firstWhere('setting_key', 'site_name')?->value(app()->getLocale())
            ?? $cmsSettings->firstWhere('setting_key', 'site_name')?->value_es
            ?? $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties');

        $currentLocale = app()->getLocale() === 'en' ? 'en' : 'es';
        $pageEntity = $pageData?->entity;
        $seoTitleOverride = trim((string) ($seoTitleOverride ?? ''));
        $seoDescriptionOverride = trim((string) ($seoDescriptionOverride ?? ''));
        $seoKeywordsOverride = trim((string) ($seoKeywordsOverride ?? ''));
        $pageTitle = $pageEntity && method_exists($pageEntity, 'title')
            ? $pageEntity->title($currentLocale)
            : null;
        $seoTitle = $pageEntity && method_exists($pageEntity, 'metaTitle')
            ? ($pageEntity->metaTitle($currentLocale) ?: $pageTitle ?: $siteName)
            : ($pageTitle ?: $siteName);
        if ($seoTitleOverride !== '') {
            $seoTitle = $seoTitleOverride;
        }

        $seoDefaultDescription = $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.');

        $seoDescription = $pageEntity && method_exists($pageEntity, 'metaDescription')
            ? ($pageEntity->metaDescription($currentLocale) ?: $seoDefaultDescription)
            : $seoDefaultDescription;
        if ($seoDescriptionOverride !== '') {
            $seoDescription = $seoDescriptionOverride;
        }

        $seoKeywords = $currentLocale === 'en'
            ? ($pageEntity->meta_keywords_en ?? null)
            : ($pageEntity->meta_keywords_es ?? null);
        if ($seoKeywordsOverride !== '') {
            $seoKeywords = $seoKeywordsOverride;
        }

        $pageFields = ($pageData && method_exists($pageData, 'allFields'))
            ? $pageData->allFields($currentLocale)
            : [];

        $pageTranslations = [];
        foreach ($pageFields as $fieldKey => $fieldValue) {
            if (str_starts_with($fieldKey, 'i18n_') && filled($fieldValue)) {
                $translationKey = str_replace('_', '.', substr($fieldKey, 5));
                $pageTranslations[$translationKey] = $fieldValue;
            }
        }

        $contactSettings = $settings ?? \App\Services\CmsService::settings('contact', $currentLocale);
        $publicContact = [
            'phone' => $contactSettings['contact_phone'] ?? '+52 55 1234 5678',
            'email' => $contactSettings['contact_email'] ?? 'info@sanmiguelproperties.com',
            'whatsapp' => $contactSettings['contact_whatsapp'] ?? '+525512345678',
        ];

        $globalTranslations = [
            'common.details' => $txt('i18n_common_details', 'Ver detalles', 'View details'),
            'common.properties' => $txt('i18n_common_properties', 'Propiedad', 'Property'),
            'common.available' => $txt('i18n_common_available', 'Propiedad disponible', 'Available property'),
            'common.sale' => $txt('i18n_common_sale', 'En venta', 'For sale'),
            'common.rent' => $txt('i18n_common_rent', 'En renta', 'For rent'),
            'common.locationAvailable' => $txt('i18n_common_locationAvailable', 'Ubicacion disponible', 'Location available'),
            'common.consultPrice' => $txt('i18n_common_consultPrice', 'Consultar precio', 'Ask for price'),
            'common.operation' => $txt('i18n_common_operation', 'Operacion', 'Operation'),
            'common.updated' => $txt('i18n_common_updated', 'Actualizado', 'Updated'),
            'common.siteName' => $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties'),
            'favorites.add' => $txt('i18n_favorites_add', 'Agregar a favoritas', 'Add to favorites'),
            'favorites.remove' => $txt('i18n_favorites_remove', 'Quitar de favoritas', 'Remove from favorites'),
            'favorites.nav' => $txt('i18n_favorites_nav', 'Favoritas', 'Favorites'),
            'property.unknownError' => $txt('i18n_property_unknownError', 'Error inesperado', 'Unexpected error'),
            'property.networkError' => $txt('i18n_property_networkError', 'Error de red al cargar la propiedad.', 'Network error while loading the property.'),
            'property.noDescription' => $txt('i18n_property_noDescription', 'Sin descripcion.', 'No description available.'),
            'property.noFeatures' => $txt('i18n_property_noFeatures', 'Sin caracteristicas', 'No features'),
            'property.noTags' => $txt('i18n_property_noTags', 'Sin etiquetas', 'No tags'),
            'property.noExtraInfo' => $txt('i18n_property_noExtraInfo', 'No hay informacion adicional registrada para esta propiedad.', 'No additional information available for this property.'),
            'property.operationAsk' => $txt('i18n_property_operationAsk', 'Consultar disponibilidad', 'Check availability'),
            'property.contactUnavailable' => $txt('i18n_property_contactUnavailable', 'Contacto no disponible', 'Contact not available'),
            'property.copiedLink' => $txt('i18n_property_copiedLink', 'Enlace copiado al portapapeles', 'Link copied to clipboard'),
            'property.missingId' => $txt('i18n_property_missingId', 'No se recibio el ID de la propiedad.', 'Property ID was not provided.'),
            'contact.requiredFields' => $txt('i18n_contact_requiredFields', 'Por favor completa todos los campos requeridos.', 'Please complete all required fields.'),
            'contact.acceptPrivacy' => $txt('i18n_contact_acceptPrivacy', 'Debes aceptar la politica de privacidad.', 'You must accept the privacy policy.'),
            'contact.submitSuccess' => $txt('i18n_contact_submitSuccess', 'Mensaje enviado con exito. Nos pondremos en contacto contigo pronto.', 'Message sent successfully. We will contact you soon.'),
            'contact.submitError' => $txt('i18n_contact_submitError', 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.', 'There was an error sending your message. Please try again.'),
            'contact.connectionError' => $txt('i18n_contact_connectionError', 'Error de conexion. Por favor verifica tu internet e intenta de nuevo.', 'Connection error. Please check your internet and try again.'),
        ];
    @endphp
    <style id="frontend-color-variables">
        /* Vista actual: {{ $currentView }} */
        {!! $frontendCss !!}
    </style>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Ubuntu', 'Cantarell', 'Noto Sans', 'Helvetica Neue', 'Arial', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#faf6ee',
                            100: '#f5eddd',
                            200: '#ebdabb',
                            300: '#e0c899',
                            400: '#d9b477',
                            500: '#D1A054',
                            600: '#b8883f',
                            700: '#9a7035',
                            800: '#7c5a2b',
                            900: '#5e4420',
                            950: '#3f2d15',
                        },
                        accent: {
                            50: '#f3f6ef',
                            100: '#e4eadb',
                            200: '#c9d5b7',
                            300: '#aec093',
                            400: '#92a876',
                            500: '#768D59',
                            600: '#627748',
                            700: '#4e5f3a',
                            800: '#3a472b',
                            900: '#262f1d',
                            950: '#131810',
                        },
                        // Override built-in indigo/emerald to brand colors
                        indigo: {
                            50: '#faf6ee',
                            100: '#f5eddd',
                            200: '#ebdabb',
                            300: '#e0c899',
                            400: '#d9b477',
                            500: '#D1A054',
                            600: '#b8883f',
                            700: '#9a7035',
                            800: '#7c5a2b',
                            900: '#5e4420',
                            950: '#3f2d15',
                        },
                        emerald: {
                            50: '#f3f6ef',
                            100: '#e4eadb',
                            200: '#c9d5b7',
                            300: '#aec093',
                            400: '#92a876',
                            500: '#768D59',
                            600: '#627748',
                            700: '#4e5f3a',
                            800: '#3a472b',
                            900: '#262f1d',
                            950: '#131810',
                        }
                    },
                    boxShadow: {
                        'soft': '0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10)',
                        'glow': '0 0 40px rgba(209, 160, 84, 0.15)',
                        'glow-accent': '0 0 40px rgba(118, 141, 89, 0.15)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideDown: {
                            '0%': { opacity: '0', transform: 'translateY(-10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    }
                }
            }
        }
    </script>

    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Custom Styles (Using Dynamic Variables) -->
    <style>
        /* Scrollbar personalizado - Usa variables CSS dinámicas */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--fe-ui-scrollbar_track, #f1f5f9);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--fe-ui-scrollbar_from, #D1A054), var(--fe-ui-scrollbar_to, #768D59));
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--fe-ui-scrollbar_hover_from, #A52A2A), var(--fe-ui-scrollbar_hover_to, #768D59));
        }

        /* Gradient text helper - Usa variables CSS dinámicas */
        .text-gradient {
            background: linear-gradient(135deg, var(--fe-primary-from, #D1A054) 0%, var(--fe-primary-to, #768D59) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Gradient border helper - Usa variables CSS dinámicas */
        .border-gradient {
            border: 2px solid transparent;
            background: linear-gradient(var(--fe-ui-body_bg, #ffffff), var(--fe-ui-body_bg, #ffffff)) padding-box, linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59)) border-box;
        }

        /* Glass effect */
        .glass {
            background: var(--fe-ui-glass_bg, rgba(255,255,255,0.8));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Swiper custom styles - Usa variables CSS dinámicas */
        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: var(--fe-ui-swiper_bullet, rgba(255,255,255,0.5));
            opacity: 1;
        }
        .swiper-pagination-bullet-active {
            background: linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));
        }
        .swiper-button-next,
        .swiper-button-prev {
            color: var(--fe-ui-swiper_nav_text, #ffffff);
            background: var(--fe-ui-swiper_nav_bg, rgba(0,0,0,0.3));
            padding: 30px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));
        }
        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 20px;
            font-weight: bold;
        }

        /* Hover effects for cards */
        .property-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--fe-ui-card_hover_shadow, 0 25px 50px -12px rgba(0,0,0,0.25));
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(
                90deg,
                var(--fe-ui-skeleton_from, #f0f0f0) 25%,
                var(--fe-ui-skeleton_mid, #e0e0e0) 50%,
                var(--fe-ui-skeleton_to, #f0f0f0) 75%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Filter chip active state - Usa variables CSS dinámicas */
        .filter-chip.active {
            background-color: var(--fe-buttons-secondary_bg, #768D59);
            color: var(--fe-buttons-secondary_text, #ffffff);
        }
        
        /* Filter tag states for properties section */
        .filter-tag-active {
            background-color: var(--fe-buttons-secondary_bg, #768D59);
            color: var(--fe-buttons-secondary_text, #ffffff);
        }
        .filter-tag-inactive {
            background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9);
            color: var(--fe-properties-tag_inactive_text, #5B5B5B);
        }
        .filter-tag-inactive:hover {
            background-color: var(--fe-properties-tag_inactive_hover, #e2e8f0);
        }

        /* Hero overlay gradient */
        .hero-overlay {
            background: linear-gradient(
                135deg,
                var(--fe-hero-overlay_from, rgba(209,160,84,0.85)) 0%,
                var(--fe-hero-overlay_to, rgba(118,141,89,0.75)) 100%
            );
        }

        /* Utility overrides to make Tailwind color tokens administrable */
        .text-white { color: var(--fe-ui-text_white, #ffffff) !important; }
        .text-white\/90 { color: var(--fe-ui-text_white_90, rgba(255,255,255,0.9)) !important; }
        .text-white\/80 { color: var(--fe-ui-text_white_80, rgba(255,255,255,0.8)) !important; }
        .text-white\/70 { color: var(--fe-ui-text_white_70, rgba(255,255,255,0.7)) !important; }
        .text-white\/60 { color: var(--fe-ui-text_white_60, rgba(255,255,255,0.6)) !important; }
        .text-slate-900 { color: var(--fe-ui-slate_900, #0f172a) !important; }
        .text-slate-800 { color: var(--fe-ui-slate_800, #1e293b) !important; }
        .text-slate-700 { color: var(--fe-ui-slate_700, #334155) !important; }
        .text-slate-600 { color: var(--fe-ui-slate_600, #475569) !important; }
        .text-slate-500 { color: var(--fe-ui-slate_500, #64748b) !important; }
        .text-slate-400 { color: var(--fe-ui-slate_400, #94a3b8) !important; }
        .text-slate-300 { color: var(--fe-ui-slate_300, #cbd5e1) !important; }
        .bg-white\/5 { background-color: var(--fe-ui-bg_white_5, rgba(255,255,255,0.05)) !important; }
        .bg-white\/10 { background-color: var(--fe-ui-bg_white_10, rgba(255,255,255,0.1)) !important; }
        .bg-white\/20 { background-color: var(--fe-ui-bg_white_20, rgba(255,255,255,0.2)) !important; }
        .bg-slate-100 { background-color: var(--fe-ui-slate_100, #f1f5f9) !important; }
        .bg-slate-50 { background-color: var(--fe-ui-slate_50, #f8fafc) !important; }
        .bg-slate-50\/60 { background-color: var(--fe-ui-slate_50_60, rgba(248,250,252,0.6)) !important; }
        .border-slate-300 { border-color: var(--fe-ui-slate_300, #cbd5e1) !important; }
        .border-slate-200 { border-color: var(--fe-ui-slate_200, #e2e8f0) !important; }
        .border-slate-100 { border-color: var(--fe-ui-slate_100, #f1f5f9) !important; }
        .border-white\/10 { border-color: var(--fe-ui-border_white_10, rgba(255,255,255,0.1)) !important; }
        .border-white\/20 { border-color: var(--fe-ui-border_white_20, rgba(255,255,255,0.2)) !important; }
        .border-white\/30 { border-color: var(--fe-ui-border_white_30, rgba(255,255,255,0.3)) !important; }
        .hover\:text-white:hover { color: var(--fe-ui-text_white, #ffffff) !important; }
        .hover\:bg-white:hover { background-color: var(--fe-ui-text_white, #ffffff) !important; }
        .hover\:bg-white\/20:hover { background-color: var(--fe-ui-bg_white_20, rgba(255,255,255,0.2)) !important; }
        .hover\:bg-slate-50:hover { background-color: var(--fe-ui-slate_50, #f8fafc) !important; }
        .hover\:bg-slate-100:hover { background-color: var(--fe-ui-slate_100, #f1f5f9) !important; }
        .hover\:bg-slate-100\/70:hover { background-color: var(--fe-ui-slate_100_70, rgba(241,245,249,0.7)) !important; }

        /* Clases de utilidad para usar variables CSS dinámicas */
        
        /* Gradientes primarios */
        .bg-fe-gradient-primary {
            background: none;
            background-color: var(--fe-buttons-primary_bg, #D1A054);
            color: var(--fe-buttons-primary_text, #ffffff);
        }

        a[style*="linear-gradient"][style*="--fe-primary-from"],
        button[style*="linear-gradient"][style*="--fe-primary-from"],
        a[style*="linear-gradient"][style*="--fe-header-cta_button_from"],
        button[style*="linear-gradient"][style*="--fe-header-cta_button_from"],
        a[style*="linear-gradient"][style*="--fe-contact_page-submit_button_from"],
        button[style*="linear-gradient"][style*="--fe-contact_page-submit_button_from"],
        a[style*="linear-gradient"][style*="--fe-property_detail-contact_button_from"],
        button[style*="linear-gradient"][style*="--fe-property_detail-contact_button_from"],
        a[style*="linear-gradient"][style*="--fe-cta_rent-btn_primary_from"],
        button[style*="linear-gradient"][style*="--fe-cta_rent-btn_primary_from"] {
            background: none !important;
            background-color: var(--fe-buttons-primary_bg, #D1A054) !important;
            color: var(--fe-buttons-primary_text, #ffffff) !important;
            border-color: var(--fe-buttons-primary_border, var(--fe-buttons-primary_bg, #D1A054)) !important;
        }

        a[style*="linear-gradient"][style*="--fe-primary-from"]:hover,
        button[style*="linear-gradient"][style*="--fe-primary-from"]:hover,
        a[style*="linear-gradient"][style*="--fe-header-cta_button_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-header-cta_button_from"]:hover,
        a[style*="linear-gradient"][style*="--fe-contact_page-submit_button_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-contact_page-submit_button_from"]:hover,
        a[style*="linear-gradient"][style*="--fe-property_detail-contact_button_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-property_detail-contact_button_from"]:hover,
        a[style*="linear-gradient"][style*="--fe-cta_rent-btn_primary_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-cta_rent-btn_primary_from"]:hover {
            background-color: var(--fe-buttons-primary_hover_bg, var(--fe-buttons-primary_bg, #D1A054)) !important;
        }

        a[style*="linear-gradient"][style*="--fe-cta_sale-btn_primary_from"],
        button[style*="linear-gradient"][style*="--fe-cta_sale-btn_primary_from"],
        a[style*="linear-gradient"][style*="--fe-footer-newsletter_button_from"],
        button[style*="linear-gradient"][style*="--fe-footer-newsletter_button_from"],
        a[style*="linear-gradient"][style*="--fe-pagination-active_from"],
        button[style*="linear-gradient"][style*="--fe-pagination-active_from"] {
            background: none !important;
            background-color: var(--fe-buttons-secondary_bg, #768D59) !important;
            color: var(--fe-buttons-secondary_text, #ffffff) !important;
            border-color: var(--fe-buttons-secondary_border, var(--fe-buttons-secondary_bg, #768D59)) !important;
        }

        a[style*="linear-gradient"][style*="--fe-cta_sale-btn_primary_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-cta_sale-btn_primary_from"]:hover,
        a[style*="linear-gradient"][style*="--fe-footer-newsletter_button_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-footer-newsletter_button_from"]:hover,
        a[style*="linear-gradient"][style*="--fe-pagination-active_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-pagination-active_from"]:hover {
            background-color: var(--fe-buttons-secondary_hover_bg, var(--fe-buttons-secondary_bg, #768D59)) !important;
        }

        a[style*="linear-gradient"][style*="--fe-agent_card-whatsapp_button_from"],
        button[style*="linear-gradient"][style*="--fe-agent_card-whatsapp_button_from"] {
            background: none !important;
            background-color: var(--fe-buttons-success_bg, #22c55e) !important;
            color: var(--fe-buttons-success_text, #ffffff) !important;
        }

        a[style*="linear-gradient"][style*="--fe-agent_card-whatsapp_button_from"]:hover,
        button[style*="linear-gradient"][style*="--fe-agent_card-whatsapp_button_from"]:hover {
            background-color: var(--fe-buttons-success_hover_bg, var(--fe-buttons-success_bg, #22c55e)) !important;
        }

        [data-favorites-count][style*="linear-gradient"][style*="--fe-primary-from"] {
            background: none !important;
            background-color: var(--fe-buttons-badge_bg, #D1A054) !important;
            color: var(--fe-buttons-badge_text, #ffffff) !important;
        }

        #backToTop {
            background: none !important;
            background-color: var(--fe-buttons-secondary_bg, #768D59) !important;
            color: var(--fe-buttons-secondary_text, #ffffff) !important;
        }

        #backToTop:hover {
            background-color: var(--fe-buttons-secondary_hover_bg, var(--fe-buttons-secondary_bg, #768D59)) !important;
        }
        
        /* Gradiente para títulos hero */
        .text-fe-hero-gradient {
            background: linear-gradient(to right, var(--fe-hero-title_gradient_from, #D1A054), var(--fe-hero-title_gradient_via, #FFFAF5), var(--fe-hero-title_gradient_to, #768D59));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Colores para badges de venta/renta */
        .bg-fe-sale-badge {
            background-color: var(--fe-property_cards-sale_badge, #768D59);
        }
        .bg-fe-rent-badge {
            background-color: var(--fe-property_cards-rent_badge, #D1A054);
        }
        
        /* Precio de propiedades */
        .text-fe-price-gradient {
            background: linear-gradient(to right, var(--fe-property_cards-price_from, #D1A054), var(--fe-property_cards-price_to, #768D59));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Focus en inputs */
        .focus-fe-primary:focus {
            border-color: var(--fe-filters-focus_border, #D1A054);
            box-shadow: 0 0 0 3px var(--fe-filters-focus_ring, rgba(209,160,84,0.2));
        }

        /* Line clamp (Tailwind CDN no incluye plugin por defecto) */
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .rich-content {
            line-height: 1.7;
            color: inherit;
            word-break: break-word;
        }
        .rich-content > :first-child {
            margin-top: 0;
        }
        .rich-content > :last-child {
            margin-bottom: 0;
        }
        .rich-content p,
        .rich-content ul,
        .rich-content ol,
        .rich-content blockquote,
        .rich-content pre,
        .rich-content table {
            margin: 0.75rem 0;
        }
        .rich-content ul,
        .rich-content ol {
            padding-left: 1.25rem;
        }
        .rich-content ul {
            list-style: disc;
        }
        .rich-content ol {
            list-style: decimal;
        }
        .rich-content h1,
        .rich-content h2,
        .rich-content h3,
        .rich-content h4,
        .rich-content h5,
        .rich-content h6 {
            margin: 0.9rem 0 0.45rem;
            line-height: 1.35;
            font-weight: 700;
            color: inherit;
        }
        .rich-content a {
            color: var(--fe-links-primary, #2563eb);
            text-decoration: underline;
            text-underline-offset: 2px;
            transition: color .2s ease;
        }
        .rich-content a:hover {
            color: var(--fe-links-primary_hover, #1d4ed8);
        }
        .rich-content blockquote {
            border-left: 3px solid #cbd5e1;
            padding-left: 0.85rem;
            color: #475569;
        }
        .rich-content code {
            background-color: #f1f5f9;
            border-radius: 0.35rem;
            padding: 0.1rem 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.92em;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased" style="background-color: var(--fe-ui-body_bg, #f8fafc); color: var(--fe-ui-body_text, #0f172a);">
    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 z-[9999] flex items-center justify-center transition-opacity duration-500" style="background-color: var(--fe-ui-preloader_bg, #ffffff);">
        <div class="relative">
            <div class="w-16 h-16 border-4 rounded-full" style="border-color: var(--fe-ui-preloader_track, #e2e8f0);"></div>
            <div class="absolute top-0 left-0 w-16 h-16 border-4 rounded-full animate-spin" style="border-color: transparent; border-top-color: var(--fe-ui-preloader_border_1, #D1A054); border-right-color: var(--fe-ui-preloader_border_2, #768D59);"></div>
        </div>
    </div>

    <!-- Header -->
    @include('components.public.header')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.public.footer')

    <!-- Back to Top Button -->
    <button id="backToTop" class="fixed bottom-8 right-8 z-50 hidden w-12 h-12 rounded-full text-white shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-ui-back_to_top_from, #D1A054), var(--fe-ui-back_to_top_to, #768D59)); --tw-ring-color: var(--fe-ui-back_to_top_ring, rgba(209,160,84,0.2));" aria-label="{{ $txt('layout_back_to_top_aria', 'Volver arriba', 'Back to top') }}">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <!-- Swiper.js -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Alpine.js para interactividad -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Main Scripts -->
    <script>
        window.__PUBLIC_LOCALE__ = @json($currentLocale);
        window.__PUBLIC_CONTACT__ = @json($publicContact);
        window.__PUBLIC_TRANSLATIONS__ = @json($globalTranslations);
        window.__PUBLIC_PAGE_TRANSLATIONS__ = @json($pageTranslations);

        window.publicT = function (key, fallback = '') {
            if (window.__PUBLIC_PAGE_TRANSLATIONS__ && Object.prototype.hasOwnProperty.call(window.__PUBLIC_PAGE_TRANSLATIONS__, key)) {
                return window.__PUBLIC_PAGE_TRANSLATIONS__[key];
            }
            if (window.__PUBLIC_TRANSLATIONS__ && Object.prototype.hasOwnProperty.call(window.__PUBLIC_TRANSLATIONS__, key)) {
                return window.__PUBLIC_TRANSLATIONS__[key];
            }
            return fallback;
        };

        (function () {
            const ALLOWED_TAGS = new Set([
                'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
                'ul', 'ol', 'li', 'blockquote',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'a', 'span', 'div', 'code', 'pre'
            ]);
            const GLOBAL_ALLOWED_ATTRIBUTES = new Set(['class', 'style', 'title', 'href', 'target', 'rel']);
            const ALLOWED_STYLE_PROPERTIES = new Set([
                'color', 'background-color',
                'font-weight', 'font-style', 'font-size', 'line-height', 'letter-spacing',
                'text-align', 'text-decoration',
                'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'border', 'border-color', 'border-radius'
            ]);
            const ALLOWED_REL_TOKENS = new Set(['noopener', 'noreferrer', 'nofollow', 'ugc', 'sponsored']);

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function sanitizeUrl(url) {
                const raw = String(url ?? '').trim();
                if (!raw) return '#';

                if (raw.startsWith('#') || raw.startsWith('/') || raw.startsWith('./') || raw.startsWith('../')) {
                    return raw;
                }

                const lower = raw.toLowerCase();
                if (lower.startsWith('http://') || lower.startsWith('https://') || lower.startsWith('mailto:') || lower.startsWith('tel:')) {
                    return raw;
                }

                return '#';
            }

            function sanitizeRel(rel) {
                const tokens = String(rel ?? '')
                    .toLowerCase()
                    .split(/\s+/)
                    .filter(Boolean)
                    .filter((token) => ALLOWED_REL_TOKENS.has(token));

                return Array.from(new Set(tokens)).join(' ');
            }

            function sanitizeStyle(styleValue) {
                const declarations = String(styleValue ?? '').split(';');
                const sanitized = [];

                for (const declaration of declarations) {
                    if (!declaration.includes(':')) continue;

                    const idx = declaration.indexOf(':');
                    const prop = declaration.slice(0, idx).trim().toLowerCase();
                    const value = declaration.slice(idx + 1).trim();
                    const valueLower = value.toLowerCase();

                    if (!prop || !value) continue;
                    if (!ALLOWED_STYLE_PROPERTIES.has(prop)) continue;
                    if (valueLower.includes('expression(') || valueLower.includes('javascript:') || valueLower.includes('vbscript:') || valueLower.includes('url(')) continue;

                    const cleanValue = value.replace(/[<>]/g, '').trim();
                    if (!cleanValue) continue;

                    sanitized.push(`${prop}: ${cleanValue}`);
                }

                return sanitized.join('; ');
            }

            function sanitizeElement(element) {
                const tag = element.tagName.toLowerCase();

                if (!ALLOWED_TAGS.has(tag)) {
                    const parent = element.parentNode;
                    if (!parent) {
                        element.remove();
                        return false;
                    }

                    while (element.firstChild) {
                        parent.insertBefore(element.firstChild, element);
                    }
                    parent.removeChild(element);
                    return false;
                }

                for (const attr of Array.from(element.attributes)) {
                    const name = attr.name.toLowerCase();
                    const value = attr.value;

                    if (name.startsWith('on')) {
                        element.removeAttribute(attr.name);
                        continue;
                    }

                    if (!GLOBAL_ALLOWED_ATTRIBUTES.has(name)) {
                        element.removeAttribute(attr.name);
                        continue;
                    }

                    if (tag !== 'a' && (name === 'href' || name === 'target' || name === 'rel')) {
                        element.removeAttribute(attr.name);
                        continue;
                    }

                    if (name === 'href') {
                        element.setAttribute(attr.name, sanitizeUrl(value));
                        continue;
                    }

                    if (name === 'target') {
                        if (value !== '_blank' && value !== '_self') {
                            element.removeAttribute(attr.name);
                        }
                        continue;
                    }

                    if (name === 'rel') {
                        const safeRel = sanitizeRel(value);
                        if (!safeRel) {
                            element.removeAttribute(attr.name);
                        } else {
                            element.setAttribute(attr.name, safeRel);
                        }
                        continue;
                    }

                    if (name === 'style') {
                        const safeStyle = sanitizeStyle(value);
                        if (!safeStyle) {
                            element.removeAttribute(attr.name);
                        } else {
                            element.setAttribute(attr.name, safeStyle);
                        }
                        continue;
                    }
                }

                if (tag === 'a' && element.getAttribute('target') === '_blank') {
                    const relSet = new Set(
                        (element.getAttribute('rel') || '')
                            .split(/\s+/)
                            .filter(Boolean)
                    );
                    relSet.add('noopener');
                    relSet.add('noreferrer');
                    element.setAttribute('rel', Array.from(relSet).join(' '));
                }

                return true;
            }

            function sanitizeNodeTree(root) {
                const children = Array.from(root.childNodes);
                for (const node of children) {
                    if (node.nodeType === Node.COMMENT_NODE) {
                        node.remove();
                        continue;
                    }

                    if (node.nodeType !== Node.ELEMENT_NODE) continue;

                    const keepNode = sanitizeElement(node);
                    if (keepNode) {
                        sanitizeNodeTree(node);
                    }
                }
            }

            function sanitizeRichHtml(input, fallback = '') {
                const source = String(input ?? '').trim();
                const fallbackText = String(fallback ?? '');

                if (!source) {
                    return fallbackText ? escapeHtml(fallbackText) : '';
                }

                if (!/<\s*\/?\s*[a-z][^>]*>/i.test(source)) {
                    return escapeHtml(source).replace(/\r?\n/g, '<br>');
                }

                const template = document.createElement('template');
                template.innerHTML = source;
                sanitizeNodeTree(template.content);

                const html = template.innerHTML.trim();
                if (!html) {
                    return fallbackText ? escapeHtml(fallbackText) : '';
                }

                return html;
            }

            function renderRichText(target, input, fallback = '') {
                if (!(target instanceof Element)) return;
                target.innerHTML = sanitizeRichHtml(input, fallback);
                target.classList.add('rich-content');
            }

            window.publicSanitizeRichHtml = sanitizeRichHtml;
            window.publicRenderRichText = renderRichText;
        })();

        (function () {
            const locale = window.__PUBLIC_LOCALE__ || 'es';
            const nativeFetch = window.fetch.bind(window);

            window.fetch = function (input, init = {}) {
                let nextInput = input;
                let nextInit = init || {};

                try {
                    const base = window.location.origin;
                    const rawUrl = typeof input === 'string' ? input : (input instanceof Request ? input.url : null);
                    if (rawUrl) {
                        const parsed = new URL(rawUrl, base);
                        if (parsed.origin === base && parsed.pathname.startsWith('/api/')) {
                            if (!parsed.searchParams.has('locale') && !parsed.searchParams.has('lang')) {
                                parsed.searchParams.set('locale', locale);
                            }

                            if (input instanceof Request) {
                                nextInput = new Request(parsed.toString(), input);
                            } else {
                                nextInput = parsed.toString();
                            }

                            const headers = new Headers(nextInit.headers || (input instanceof Request ? input.headers : undefined));
                            if (!headers.has('X-Locale')) {
                                headers.set('X-Locale', locale);
                            }
                            nextInit.headers = headers;
                        }
                    }
                } catch (_error) {
                    // noop
                }

                return nativeFetch(nextInput, nextInit);
            };
        })();
        // Preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        });

        // Back to Top Button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('hidden');
                backToTop.classList.add('animate-fade-in');
            } else {
                backToTop.classList.add('hidden');
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Price formatter helper:
        // $12,345,678 MXN
        // $12,345,678.90 USD
        function normalizeDisplayCurrencyCode(currencyCode) {
            const code = String(currencyCode || '').trim().toUpperCase();
            if (code === 'MXN' || code === 'USD') return code;
            return code || 'MXN';
        }

        function toDisplayAmount(amount) {
            if (amount === null || amount === undefined || amount === '') return null;
            if (typeof amount === 'number') return Number.isFinite(amount) ? amount : null;

            const normalized = String(amount).replace(/,/g, '').trim();
            const parsed = Number.parseFloat(normalized);
            return Number.isFinite(parsed) ? parsed : null;
        }

        function formatDisplayPrice(amount, currencyCode = 'MXN') {
            const numeric = toDisplayAmount(amount);
            if (numeric === null) return '';

            const rounded = Math.round((numeric + Number.EPSILON) * 100) / 100;
            const code = normalizeDisplayCurrencyCode(currencyCode);
            const hasCents = Math.abs(rounded - Math.trunc(rounded)) > 0.00001;
            const fixed = rounded.toFixed(hasCents ? 2 : 0);
            const [integer, decimals] = fixed.split('.');
            const integerWithThousands = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const decimalSuffix = decimals ? `.${decimals}` : '';
            const symbol = (code === 'MXN' || code === 'USD') ? '$' : '';

            return `${symbol}${integerWithThousands}${decimalSuffix} ${code}`.trim();
        }

        function formatCurrency(amount, currency = 'MXN') {
            return formatDisplayPrice(amount, currency);
        }

        window.formatDisplayPrice = formatDisplayPrice;
        window.formatCurrency = formatCurrency;

        // API Helper
        const API = {
            baseUrl: '/api',
            async get(endpoint, params = {}) {
                const url = new URL(this.baseUrl + endpoint, window.location.origin);
                Object.keys(params).forEach(key => {
                    if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
                        url.searchParams.append(key, params[key]);
                    }
                });
                
                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            }
        };

        (function () {
            const STORAGE_KEY = 'smp.favorite_property_ids';

            function toPositiveInt(value) {
                const parsed = Number.parseInt(String(value ?? ''), 10);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
            }

            function normalizeIds(ids) {
                const source = Array.isArray(ids) ? ids : [];
                const result = [];
                const seen = new Set();

                source.forEach((id) => {
                    const numericId = toPositiveInt(id);
                    if (!numericId || seen.has(numericId)) return;
                    seen.add(numericId);
                    result.push(numericId);
                });

                return result;
            }

            function readIds() {
                try {
                    const raw = window.localStorage.getItem(STORAGE_KEY);
                    if (!raw) return [];
                    return normalizeIds(JSON.parse(raw));
                } catch (_error) {
                    return [];
                }
            }

            function writeIds(ids) {
                const normalized = normalizeIds(ids);
                try {
                    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));
                } catch (_error) {
                    // noop
                }

                window.dispatchEvent(new CustomEvent('public:favorites-changed', {
                    detail: { ids: normalized },
                }));

                return normalized;
            }

            function getAddLabel() {
                return window.publicT ? window.publicT('favorites.add', 'Agregar a favoritas') : 'Agregar a favoritas';
            }

            function getRemoveLabel() {
                return window.publicT ? window.publicT('favorites.remove', 'Quitar de favoritas') : 'Quitar de favoritas';
            }

            function setButtonState(button, isActive) {
                if (!button) return;

                if (!button.dataset.favoriteDefaultColor) {
                    const computed = window.getComputedStyle(button);
                    button.dataset.favoriteDefaultColor = button.style.color || computed.color || '';
                    button.dataset.favoriteDefaultBackground = button.style.backgroundColor || computed.backgroundColor || '';
                    button.dataset.favoriteDefaultBorder = button.style.borderColor || computed.borderColor || '';
                    button.dataset.favoriteDefaultShadow = button.style.boxShadow || computed.boxShadow || '';
                }

                if (isActive) {
                    button.style.color = 'var(--fe-ui-favorites_active_text, #e11d48)';
                    button.style.backgroundColor = 'var(--fe-ui-favorites_active_bg, #ffe4e6)';
                    button.style.borderColor = 'var(--fe-ui-favorites_active_border, #fecdd3)';
                    button.style.boxShadow = '0 0 0 4px var(--fe-ui-favorites_active_ring, #ffe4e6)';
                } else {
                    button.style.color = button.dataset.favoriteDefaultColor || '';
                    button.style.backgroundColor = button.dataset.favoriteDefaultBackground || '';
                    button.style.borderColor = button.dataset.favoriteDefaultBorder || '';
                    button.style.boxShadow = button.dataset.favoriteDefaultShadow || '';
                }

                const icon = button.querySelector('svg');
                if (icon) {
                    if (isActive) {
                        icon.setAttribute('fill', 'currentColor');
                    } else {
                        icon.setAttribute('fill', 'none');
                    }
                }

                const label = isActive ? getRemoveLabel() : getAddLabel();
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                button.setAttribute('aria-label', label);
                button.setAttribute('title', label);
            }

            function getButtonId(button, explicitId = null) {
                if (explicitId !== null && explicitId !== undefined) {
                    return toPositiveInt(explicitId);
                }
                return toPositiveInt(button?.dataset?.propertyId);
            }

            function updateFavoritesCounter() {
                const count = readIds().length;
                document.querySelectorAll('[data-favorites-count]').forEach((el) => {
                    el.textContent = String(count);
                    el.classList.toggle('hidden', count === 0);
                });
            }

            const publicFavorites = {
                key: STORAGE_KEY,

                getIds() {
                    return readIds();
                },

                setIds(ids) {
                    return writeIds(ids);
                },

                has(id) {
                    const numericId = toPositiveInt(id);
                    if (!numericId) return false;
                    return readIds().includes(numericId);
                },

                add(id) {
                    const numericId = toPositiveInt(id);
                    if (!numericId) return readIds();

                    const current = readIds();
                    if (current.includes(numericId)) return current;
                    current.unshift(numericId);
                    return writeIds(current);
                },

                remove(id) {
                    const numericId = toPositiveInt(id);
                    if (!numericId) return readIds();

                    const next = readIds().filter((item) => item !== numericId);
                    return writeIds(next);
                },

                toggle(id) {
                    const numericId = toPositiveInt(id);
                    if (!numericId) {
                        return { ids: readIds(), active: false };
                    }

                    const current = readIds();
                    const exists = current.includes(numericId);
                    const next = exists ? current.filter((item) => item !== numericId) : [numericId, ...current];
                    const stored = writeIds(next);
                    return { ids: stored, active: !exists };
                },

                syncButton(button, explicitId = null) {
                    const numericId = getButtonId(button, explicitId);
                    if (!button || !numericId) return;
                    button.dataset.propertyId = String(numericId);
                    setButtonState(button, this.has(numericId));
                },

                syncButtons(root = document) {
                    root.querySelectorAll('[data-favorite-btn]').forEach((button) => {
                        this.syncButton(button);
                    });
                },

                bind(root = document) {
                    if (!root || root.__publicFavoritesBound) return;
                    root.__publicFavoritesBound = true;

                    root.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-favorite-btn]');
                        if (!button || !root.contains(button)) return;

                        event.preventDefault();
                        event.stopPropagation();

                        const numericId = getButtonId(button);
                        if (!numericId) return;

                        const result = this.toggle(numericId);
                        setButtonState(button, result.active);
                    });
                },
            };

            window.publicFavorites = publicFavorites;

            document.addEventListener('DOMContentLoaded', () => {
                publicFavorites.bind(document);
                publicFavorites.syncButtons(document);
                updateFavoritesCounter();
            });

            window.addEventListener('public:favorites-changed', () => {
                publicFavorites.syncButtons(document);
                updateFavoritesCounter();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>


