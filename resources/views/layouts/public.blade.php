<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="San Miguel Properties - Tu portal inmobiliario de confianza. Encuentra casas, departamentos y terrenos en venta y renta.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'San Miguel Properties - Portal Inmobiliario')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Frontend Color Variables (Dynamic from Database) -->
    @php
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
        $siteName = $cmsSettings->firstWhere('setting_key', 'site_name')?->value_es ?? 'San Miguel Properties';
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
            background: #f1f5f9;
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
            background: linear-gradient(white, white) padding-box, linear-gradient(135deg, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59)) border-box;
        }

        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Swiper custom styles - Usa variables CSS dinámicas */
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

        /* Filter chip active state - Usa variables CSS dinámicas */
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

        /* Clases de utilidad para usar variables CSS dinámicas */
        
        /* Gradientes primarios */
        .bg-fe-gradient-primary {
            background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));
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
    <button id="backToTop" class="fixed bottom-8 right-8 z-50 hidden w-12 h-12 rounded-full text-white shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-ui-back_to_top_from, #D1A054), var(--fe-ui-back_to_top_to, #768D59)); --tw-ring-color: rgba(209,160,84,0.2);" aria-label="Volver arriba">
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
            return new Intl.NumberFormat('es-MX', {
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
