<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @php
        $pageData = $pageData ?? null;
        $currentLocale = app()->getLocale() === 'en' ? 'en' : 'es';
        $txt = static fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($currentLocale === 'en' ? $en : $es);
        $pageEntity = $pageData?->entity;

        $seoTitle = $pageEntity && method_exists($pageEntity, 'metaTitle')
            ? ($pageEntity->metaTitle($currentLocale) ?: ($pageEntity->title($currentLocale) ?: $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties')))
            : $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties');

        $seoDescription = $pageEntity && method_exists($pageEntity, 'metaDescription')
            ? ($pageEntity->metaDescription($currentLocale) ?: $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.'))
            : $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.');

        $seoKeywords = $currentLocale === 'en'
            ? ($pageEntity->meta_keywords_en ?? null)
            : ($pageEntity->meta_keywords_es ?? null);
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
        // Detectar la vista actual basÃƒÆ’Ã‚Â¡ndose en la ruta
        $currentView = $frontendColorService->detectCurrentView();
        // Generar CSS con colores combinados (global + vista especÃƒÆ’Ã‚Â­fica)
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
        $pageTitle = $pageEntity && method_exists($pageEntity, 'title')
            ? $pageEntity->title($currentLocale)
            : null;
        $seoTitle = $pageEntity && method_exists($pageEntity, 'metaTitle')
            ? ($pageEntity->metaTitle($currentLocale) ?: $pageTitle ?: $siteName)
            : ($pageTitle ?: $siteName);

        $seoDefaultDescription = $txt('i18n_seo_defaultDescription', 'San Miguel Properties - Portal inmobiliario en San Miguel de Allende.', 'San Miguel Properties - Real estate portal in San Miguel de Allende.');

        $seoDescription = $pageEntity && method_exists($pageEntity, 'metaDescription')
            ? ($pageEntity->metaDescription($currentLocale) ?: $seoDefaultDescription)
            : $seoDefaultDescription;

        $seoKeywords = $currentLocale === 'en'
            ? ($pageEntity->meta_keywords_en ?? null)
            : ($pageEntity->meta_keywords_es ?? null);

        $pageFields = method_exists($pageData, 'allFields')
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
        /* Scrollbar personalizado - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--fe-ui-scrollbar_from, #D1A054), var(--fe-ui-scrollbar_to, #768D59));
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--fe-ui-scrollbar_hover_from, #A52A2A), var(--fe-ui-scrollbar_hover_to, #768D59));
        }

        /* Gradient text helper - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas */
        .text-gradient {
            background: linear-gradient(135deg, var(--fe-primary-from, #D1A054) 0%, var(--fe-primary-to, #768D59) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Gradient border helper - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas */
        .border-gradient {
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box, linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59)) border-box;
        }

        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Swiper custom styles - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas */
        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 1;
        }
        .swiper-pagination-bullet-active {
            background: linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));
        }
        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(0, 0, 0, 0.3);
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
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Filter chip active state - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas */
        .filter-chip.active {
            background: linear-gradient(135deg, var(--fe-filters-active_from, #D1A054), var(--fe-filters-active_to, #768D59));
            color: white;
        }
        
        /* Filter tag states for properties section */
        .filter-tag-active {
            background: linear-gradient(to right, var(--fe-properties-tag_active_from, #D1A054), var(--fe-properties-tag_active_to, #768D59));
            color: white;
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
            background: linear-gradient(135deg, rgba(209, 160, 84, 0.85) 0%, rgba(118, 141, 89, 0.75) 100%);
        }

        /* Clases de utilidad para usar variables CSS dinÃƒÆ’Ã‚Â¡micas */
        
        /* Gradientes primarios */
        .bg-fe-gradient-primary {
            background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));
        }
        
        /* Gradiente para tÃƒÆ’Ã‚Â­tulos hero */
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
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 font-sans antialiased">
    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white transition-opacity duration-500">
        <div class="relative">
            <div class="w-16 h-16 border-4 border-slate-200 rounded-full"></div>
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
    <button id="backToTop" class="fixed bottom-8 right-8 z-50 hidden w-12 h-12 rounded-full text-white shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-ui-back_to_top_from, #D1A054), var(--fe-ui-back_to_top_to, #768D59)); --tw-ring-color: rgba(209,160,84,0.2);" aria-label="{{ $txt('layout_back_to_top_aria', 'Volver arriba', 'Back to top') }}">
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

        // Format currency helper
        function formatCurrency(amount, currency = 'MXN') {
            return new Intl.NumberFormat((window.__PUBLIC_LOCALE__ === 'en') ? 'en-US' : 'es-MX', {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

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
    </script>

    @stack('scripts')
</body>
</html>


