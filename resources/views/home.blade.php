@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';
    $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
    $pageTitle = $pageData?->entity?->title($locale)
        ?? $txt('home_page_title', 'San Miguel Properties - Encuentra tu hogar ideal', 'San Miguel Properties - Find your ideal home');

    $contactPhone = $settings['contact_phone'] ?? '+52 55 1234 5678';
    $contactPhoneHref = preg_replace('/[^0-9+]/', '', (string) $contactPhone) ?: '+525512345678';
    $contactWhatsappRaw = $settings['contact_whatsapp'] ?? '+525512345678';
    $contactWhatsapp = preg_replace('/[^0-9]/', '', (string) $contactWhatsappRaw) ?: '525512345678';
    $contactEmail = $settings['contact_email'] ?? 'info@sanmiguelproperties.com';
@endphp

@section('title', $pageTitle)

@section('content')
{{-- ============================================== --}}
{{-- HERO SECTION CON SLIDER (SWIPER.JS) --}}
{{-- ============================================== --}}
<section id="hero" class="relative h-screen min-h-[600px] max-h-[900px] overflow-hidden">
    {{-- Slider Container --}}
    <div class="swiper hero-slider absolute inset-0 w-full h-full">
        <div class="swiper-wrapper" id="heroSliderWrapper">
            {{-- Slides se cargan dinÃƒÆ’Ã‚Â¡micamente desde la API --}}
            {{-- Placeholder mientras carga --}}
            <div class="swiper-slide hero-slide-placeholder">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 to-slate-700"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="animate-pulse text-white/50">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p>{{ $txt('home_hero_loading', 'Cargando propiedades destacadas...','Loading featured properties...') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Pagination --}}
        <div class="swiper-pagination !bottom-8"></div>
        
        {{-- Navigation Buttons --}}
        <div class="swiper-button-prev !left-4 lg:!left-8"></div>
        <div class="swiper-button-next !right-4 lg:!right-8"></div>
    </div>

    {{-- Hero Content Overlay - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
    <div class="absolute inset-0 z-10" style="background: linear-gradient(to bottom, var(--fe-hero-overlay_from, rgba(0,0,0,0.6)), var(--fe-hero-overlay_via, rgba(0,0,0,0.4)), var(--fe-hero-overlay_to, rgba(0,0,0,0.7)));"></div>
    
    <div class="relative z-20 h-full flex flex-col justify-center items-center text-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            {{-- Badge - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-white/90 text-sm font-medium mb-6 animate-fade-in" style="background: var(--fe-hero-badge_bg, rgba(255,255,255,0.1));">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: var(--fe-hero-badge_dot, #768D59);"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" style="background-color: var(--fe-hero-badge_dot, #768D59);"></span>
                </span>
                {{ $txt('hero_badge_text', '+500 propiedades disponibles', '+500 available properties') }}
            </div>

            {{-- Main Title - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
            <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold text-white mb-6 animate-slide-up">
                {{ $txt('hero_title_line1', 'Encuentra tu', 'Find your') }}
                <span class="block text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-hero-title_gradient_from, #D1A054), var(--fe-hero-title_gradient_via, #FFFAF5), var(--fe-hero-title_gradient_to, #768D59));">
                    {{ $txt('hero_title_highlight', 'hogar ideal', 'ideal home') }}
                </span>
            </h1>

            {{-- Subtitle --}}
            <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto mb-10 animate-slide-up" style="animation-delay: 0.1s;">
                {{ $txt('hero_subtitle', 'Casas, departamentos y terrenos en las mejores ubicaciones. Tu próxima inversión inmobiliaria está a un clic de distancia.', 'Houses, apartments and land in the best locations. Your next real estate investment is just one click away.') }}
            </p>

            {{-- Search Bar - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
            <div class="relative max-w-3xl mx-auto animate-slide-up" style="animation-delay: 0.2s;" x-data="heroSearch()">
                <form @submit.prevent="submitSearch()" class="flex flex-col sm:flex-row gap-3 p-3 backdrop-blur-md rounded-2xl border border-white/20" style="background: var(--fe-hero-search_bg, rgba(255,255,255,0.1));">
                    <div class="relative flex-1">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="{{ $txt('hero_search_placeholder', 'Buscar por ubicación, tipo o características...', 'Search by location, type or features...') }}"
                               class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/10 rounded-xl text-white placeholder-white/50 focus:outline-none transition-all"
                               style="--tw-ring-color: var(--fe-hero-search_focus, #D1A054);">
                    </div>
                    <button type="submit" class="px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59)); --tw-shadow-color: var(--fe-primary-from, #D1A054);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ $txt('hero_search_button', 'Buscar', 'Search') }}
                    </button>
                </form>

                {{-- Quick Filters (dinÃƒÆ’Ã‚Â¡micos) --}}
                <div class="flex flex-wrap justify-center gap-2 mt-4" id="heroQuickFilters">
                    {{-- Se llenan dinÃƒÆ’Ã‚Â¡micamente desde JS --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 animate-bounce hidden sm:block">
        <a href="#servicios" class="flex flex-col items-center gap-2 text-white/60 hover:text-white transition-colors">
            <span class="text-xs font-medium uppercase tracking-wider">{{ $txt('hero_scroll_cta', 'Descubre más', 'Discover more') }}</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </a>
    </div>
</section>

{{-- ============================================== --}}
{{-- STATS BAR - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section class="relative z-30 -mt-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 rounded-2xl shadow-xl border" style="background-color: var(--fe-stats-bg, #ffffff); border-color: var(--fe-stats-border, #f1f5f9);">
            @php $statsItems = $pageData?->repeater('stats_items') ?? []; @endphp
            @forelse($statsItems as $stat)
            <div class="text-center p-4 {{ !$loop->last ? 'border-r' : '' }}" style="border-color: var(--fe-stats-border, #f1f5f9);">
                <div class="text-3xl sm:text-4xl font-bold text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-stats-properties_from, #D1A054), var(--fe-stats-properties_to, #D1A054));">{{ $stat->field('stat_number') }}</div>
                <div class="text-sm mt-1" style="color: var(--fe-stats-text, #5B5B5B);">{{ $stat->field('stat_label') }}</div>
            </div>
            @empty
            <div class="text-center p-4"><div class="text-3xl font-bold">500+</div><div class="text-sm mt-1">{{ $txt('stats_fallback_label', 'Propiedades', 'Properties') }}</div></div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- SERVICIOS / CARACTERÃƒÆ’Ã‚ÂSTICAS - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="servicios" class="py-20 lg:py-28" style="background-color: var(--fe-services-bg, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: var(--fe-services-badge_bg, #eef2ff); color: var(--fe-services-badge_text, #D1A054);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                {{ $txt('services_badge', 'Nuestros Servicios', 'Our Services') }}
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4" style="color: var(--fe-services-title, #1C1C1C);">
                {{ $txt('services_title', '¿Por qué elegirnos?', 'Why choose us?') }}
            </h2>
            <p class="text-lg" style="color: var(--fe-services-subtitle, #5B5B5B);">
                {{ $txt('services_subtitle', 'Ofrecemos una experiencia inmobiliaria completa con tecnología de vanguardia y un equipo de expertos dedicados a ti.', 'We offer a complete real estate experience with cutting-edge technology and an expert team dedicated to you.') }}
            </p>
        </div>

        {{-- Features Grid --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Feature 1 - BÃƒÆ’Ã‚Âºsqueda Inteligente --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature1_from, #D1A054), var(--fe-services-feature1_to, #D1A054));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature1_title', 'Búsqueda Inteligente', 'Smart Search') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature1_desc', 'Filtros avanzados y búsqueda por mapa para encontrar exactamente lo que necesitas en segundos.', 'Advanced filters and map search to find exactly what you need in seconds.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature1_glow, rgba(209, 160, 84, 0.05));"></div>
            </div>

            {{-- Feature 2 - Transacciones Seguras --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature2_from, #768D59), var(--fe-services-feature2_to, #768D59));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature2_title', 'Transacciones Seguras', 'Secure Transactions') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature2_desc', 'Proceso de compra transparente con asesoría legal incluida y documentación verificada.', 'Transparent buying process with legal guidance and verified documentation.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature2_glow, rgba(118, 141, 89, 0.05));"></div>
            </div>

            {{-- Feature 3 - Tours Virtuales --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature3_from, #A52A2A), var(--fe-services-feature3_to, #A52A2A));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature3_title', 'Tours Virtuales 360°', '360 Virtual Tours') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature3_desc', 'Recorre las propiedades desde la comodidad de tu hogar con nuestros tours virtuales inmersivos.', 'Explore properties from home with our immersive virtual tours.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature3_glow, rgba(165, 42, 42, 0.05));"></div>
            </div>

            {{-- Feature 4 - Asesores Expertos --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature4_from, #D1A054), var(--fe-services-feature4_to, #D1A054));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature4_title', 'Asesores Expertos', 'Expert Advisors') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature4_desc', 'Un equipo de profesionales certificados te acompaña en cada paso del proceso.', 'A team of certified professionals supports you at every step.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature4_glow, rgba(91, 91, 91, 0.05));"></div>
            </div>

            {{-- Feature 5 - Financiamiento Flexible --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature5_from, #A52A2A), var(--fe-services-feature5_to, #A52A2A));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature5_title', 'Financiamiento Flexible', 'Flexible Financing') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature5_desc', 'Opciones de crédito con las mejores tasas del mercado y planes a tu medida.', 'Credit options with competitive rates and plans tailored to you.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature5_glow, rgba(165, 42, 42, 0.05));"></div>
            </div>

            {{-- Feature 6 - App MÃƒÆ’Ã‚Â³vil --}}
            <div class="group relative p-8 rounded-2xl border transition-all duration-300 hover:shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-services-card_bg_from, #f8fafc), var(--fe-services-card_bg_to, #ffffff)); border-color: var(--fe-services-card_border, #f1f5f9);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-services-feature6_from, #768D59), var(--fe-services-feature6_to, #979790));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color: var(--fe-services-card_title, #1C1C1C);">{{ $txt('services_feature6_title', 'App Móvil', 'Mobile App') }}</h3>
                <p style="color: var(--fe-services-card_text, #5B5B5B);">{{ $txt('services_feature6_desc', 'Gestiona tus favoritos, agenda visitas y recibe alertas desde cualquier lugar.', 'Manage favorites, schedule visits and receive alerts from anywhere.') }}</p>
                <div class="absolute top-4 right-4 w-20 h-20 rounded-full blur-2xl transition-colors" style="background-color: var(--fe-services-feature6_glow, rgba(118, 141, 89, 0.05));"></div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- CTA - PROPIEDADES EN VENTA - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="venta" class="relative py-24 lg:py-32 overflow-hidden">
    {{-- Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="{{ $txt('cta_sale_image_alt', 'Casa moderna en venta', 'Modern house for sale') }}" class="w-full h-full object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(to right, var(--fe-cta_sale-overlay_from, rgba(28, 28, 28, 0.95)), var(--fe-cta_sale-overlay_via, rgba(28, 28, 28, 0.8)), var(--fe-cta_sale-overlay_to, transparent));"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-sm font-medium mb-6" style="background: var(--fe-cta_sale-badge_bg, rgba(255,255,255,0.1)); color: var(--fe-cta_sale-badge_text, #768D59);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $txt('cta_sale_badge', 'Propiedades en Venta', 'Properties for Sale') }}
            </div>

            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
                {{ $txt('cta_sale_title_line1', 'Tu próxima', 'Your next') }}
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-cta_sale-highlight_from, #768D59), var(--fe-cta_sale-highlight_to, #979790));">{{ $txt('cta_sale_title_highlight', 'inversión', 'investment') }}</span>
                {{ $txt('cta_sale_title_line2', 'te espera', 'is waiting for you') }}
            </h2>

            <p class="text-xl text-white/80 mb-8">
                {{ $txt('cta_sale_description', 'Descubre nuestra selección exclusiva de propiedades en venta. Desde acogedores departamentos hasta lujosas residencias, encontrarás opciones para todos los presupuestos.', 'Discover our exclusive selection of properties for sale. From cozy apartments to luxury residences, you will find options for every budget.') }}
            </p>

            {{-- Stats --}}
            <div class="flex flex-wrap gap-8 mb-10">
                <div>
                    <div class="text-4xl font-bold text-white">{{ $txt('cta_sale_stat_houses_value', '200+', '200+') }}</div>
                    <div class="text-white/60 text-sm">{{ $txt('cta_sale_stat_houses', 'Casas disponibles', 'Available houses') }}</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-white">{{ $txt('cta_sale_stat_apartments_value', '150+', '150+') }}</div>
                    <div class="text-white/60 text-sm">{{ $txt('cta_sale_stat_apartments', 'Departamentos', 'Apartments') }}</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-white">{{ $txt('cta_sale_stat_land_value', '50+', '50+') }}</div>
                    <div class="text-white/60 text-sm">{{ $txt('cta_sale_stat_land', 'Terrenos', 'Land') }}</div>
                </div>
            </div>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/propiedades?operation_type=sale" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-cta_sale-btn_primary_from, #768D59), var(--fe-cta_sale-btn_primary_to, #768D59));">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ $txt('cta_sale_button_primary', 'Ver propiedades en venta', 'View properties for sale') }}
                </a>
                <a href="#contacto" class="inline-flex items-center justify-center gap-2 px-8 py-4 backdrop-blur-sm text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-300" style="background: var(--fe-cta_sale-btn_secondary_bg, rgba(255,255,255,0.1));">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    {{ $txt('cta_sale_button_secondary', 'Hablar con un asesor', 'Talk to an advisor') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Decorative Elements --}}
    <div class="absolute bottom-0 right-0 w-1/3 h-full hidden lg:block">
        <div class="absolute bottom-10 right-10 w-64 h-64 border-2 border-white/10 rounded-3xl"></div>
        <div class="absolute bottom-20 right-20 w-64 h-64 rounded-3xl" style="border: 2px solid var(--fe-cta_sale-decor, rgba(118, 141, 89, 0.2));"></div>
    </div>
</section>

{{-- ============================================== --}}
{{-- CTA - PROPIEDADES EN RENTA - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="renta" class="relative py-24 lg:py-32 overflow-hidden">
    {{-- Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="{{ $txt('cta_rent_image_alt', 'Departamento moderno en renta', 'Modern apartment for rent') }}" class="w-full h-full object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(to left, var(--fe-cta_rent-overlay_from, rgba(28, 28, 28, 0.95)), var(--fe-cta_rent-overlay_via, rgba(28, 28, 28, 0.8)), var(--fe-cta_rent-overlay_to, transparent));"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl ml-auto text-right">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-sm font-medium mb-6" style="background: var(--fe-cta_rent-badge_bg, rgba(255,255,255,0.1)); color: var(--fe-cta_rent-badge_text, #D1A054);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $txt('cta_rent_badge', 'Propiedades en Renta', 'Properties for Rent') }}
            </div>

            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
                {{ $txt('cta_rent_title_line1', 'Renta sin', 'Rent with no') }}
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-cta_rent-highlight_from, #D1A054), var(--fe-cta_rent-highlight_to, #D1A054));">{{ $txt('cta_rent_title_highlight', 'complicaciones', 'hassle') }}</span>
            </h2>

            <p class="text-xl text-white/80 mb-8">
                {{ $txt('cta_rent_description', 'Encuentra el espacio perfecto para tu próxima aventura. Contratos flexibles, propiedades verificadas y mudanza express disponible.', 'Find the perfect space for your next adventure. Flexible contracts, verified properties and express move-in available.') }}
            </p>

            {{-- Features List --}}
            <div class="flex flex-wrap justify-end gap-4 mb-10">
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-cta_rent-check_color, #D1A054);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $txt('cta_rent_feature1', 'Sin aval', 'No guarantor') }}
                </div>
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-cta_rent-check_color, #D1A054);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $txt('cta_rent_feature2', 'Contratos flexibles', 'Flexible contracts') }}
                </div>
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-cta_rent-check_color, #D1A054);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $txt('cta_rent_feature3', 'Mudanza express', 'Express move-in') }}
                </div>
            </div>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="/propiedades?operation_type=rental" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-cta_rent-btn_primary_from, #D1A054), var(--fe-cta_rent-btn_primary_to, #D1A054));">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ $txt('cta_rent_button_primary', 'Ver propiedades en renta', 'View rental properties') }}
                </a>
                <a href="#contacto" class="inline-flex items-center justify-center gap-2 px-8 py-4 backdrop-blur-sm text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-300" style="background: var(--fe-cta_rent-btn_secondary_bg, rgba(255,255,255,0.1));">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ $txt('cta_rent_button_secondary', 'Llamar ahora', 'Call now') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Decorative Elements --}}
    <div class="absolute bottom-0 left-0 w-1/3 h-full hidden lg:block">
        <div class="absolute bottom-10 left-10 w-64 h-64 border-2 border-white/10 rounded-3xl"></div>
        <div class="absolute bottom-20 left-20 w-64 h-64 rounded-3xl" style="border: 2px solid var(--fe-cta_rent-decor, rgba(209, 160, 84, 0.2));"></div>
    </div>
</section>

{{-- ============================================== --}}
{{-- PROPIEDADES CON FILTROS Y PAGINACIÃƒÆ’Ã¢â‚¬Å“N - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="propiedades" class="py-20 lg:py-28" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: var(--fe-properties-badge_bg, #eef2ff); color: var(--fe-properties-badge_text, #D1A054);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $txt('properties_section_badge', 'Catálogo de Propiedades', 'Property Catalog') }}
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4" style="color: var(--fe-properties-title, #1C1C1C);">
                {{ $txt('properties_section_title', 'Explora nuestras propiedades', 'Explore our properties') }}
            </h2>
            <p class="text-lg" style="color: var(--fe-properties-subtitle, #5B5B5B);">
                {{ $txt('properties_section_subtitle', 'Utiliza los filtros para encontrar la propiedad que se ajuste a tus necesidades.', 'Use the filters to find the property that matches your needs.') }}
            </p>
        </div>

        {{-- Filters Section --}}
        <div class="mb-10" x-data="propertiesFilter()">
            <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                <div class="flex flex-wrap items-center gap-4">
                    {{-- Search Input --}}
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-properties-filter_icon, #94a3b8);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text"
                                   x-model="filters.search"
                                   @input.debounce.300ms="applyFilters()"
                                   placeholder="{{ $txt('properties_search_placeholder', 'Buscar propiedades...','Search properties...') }}"
                                   class="w-full pl-12 pr-4 py-3 rounded-xl transition-all focus:outline-none"
                                   style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);">
                        </div>
                    </div>

                    {{-- Property Type Filter (dinÃƒÆ’Ã‚Â¡mico) --}}
                    <div class="min-w-[180px]">
                        <select x-model="filters.property_type_name"
                                @change="applyFilters()"
                                class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
                                style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);">
                            <option value="">{{ $txt('properties_filter_all_types', 'Todos los tipos', 'All types') }}</option>
                            <template x-for="type in dynamicPropertyTypes" :key="type">
                                <option :value="type" x-text="type"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Sort Filter --}}
                    <div class="min-w-[180px]">
                        <select x-model="filters.order"
                                @change="applyFilters()"
                                class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
                                style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);">
                            <option value="updated_at">{{ $txt('properties_sort_recent', 'Más recientes', 'Most recent') }}</option>
                            <option value="created_at">{{ $txt('properties_sort_oldest', 'Más antiguas', 'Oldest') }}</option>
                            <option value="title">{{ $txt('properties_sort_alphabetic', 'Alfabético', 'Alphabetical') }}</option>
                        </select>
                    </div>

                    {{-- Clear Filters --}}
                    <button @click="clearFilters()"
                            x-show="hasFilters()"
                            class="px-4 py-3 text-sm font-medium transition-colors flex items-center gap-2"
                            style="color: var(--fe-properties-filter_clear, #5B5B5B);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ $txt('properties_clear_filters', 'Limpiar filtros', 'Clear filters') }}
                    </button>
                </div>

                {{-- Quick Filter Tags (dinÃƒÆ’Ã‚Â¡micos) --}}
                <div class="flex flex-wrap gap-2 mt-4 pt-4" style="border-top: 1px solid var(--fe-properties-filter_divider, #f1f5f9);">
                    <button @click="togglePublished(true)"
                            :class="filters.published === true ? 'filter-tag-active' : 'filter-tag-inactive'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all">
                        ✅ {{ $txt('properties_published_tag', 'Publicadas', 'Published') }}
                    </button>
                    <template x-for="type in dynamicPropertyTypes" :key="'tag-' + type">
                        <button @click="setPropertyType(type)"
                                :class="filters.property_type_name === type ? 'filter-tag-active' : 'filter-tag-inactive'"
                                class="px-4 py-2 rounded-full text-sm font-medium transition-all"
                                x-text="type">
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Properties Grid --}}
        <div id="propertiesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {{-- Loading Skeleton --}}
            <template x-for="i in 6" :key="i">
                <div class="property-skeleton bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
                    <div class="skeleton h-56 w-full"></div>
                    <div class="p-6 space-y-4">
                        <div class="skeleton h-4 w-3/4 rounded"></div>
                        <div class="skeleton h-6 w-full rounded"></div>
                        <div class="skeleton h-4 w-1/2 rounded"></div>
                        <div class="flex gap-4">
                            <div class="skeleton h-4 w-16 rounded"></div>
                            <div class="skeleton h-4 w-16 rounded"></div>
                            <div class="skeleton h-4 w-16 rounded"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div id="propertiesEmpty" class="hidden text-center py-16">
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-2">{{ $txt('properties_empty_title', 'No se encontraron propiedades', 'No properties found') }}</h3>
            <p class="text-slate-600 mb-6">{{ $txt('properties_empty_subtitle', 'Intenta ajustar los filtros o buscar con otros términos.', 'Try adjusting filters or searching with other terms.') }}</p>
            <button onclick="window.propertiesApp.clearFilters()" class="px-6 py-3 text-white font-medium rounded-xl transition-colors hover:opacity-90" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                {{ $txt('properties_clear_filters', 'Limpiar filtros', 'Clear filters') }}
            </button>
        </div>

        {{-- Pagination --}}
        <div id="propertiesPagination" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-12 pt-8 border-t border-slate-200">
            <div class="text-sm text-slate-600">
                {{ $txt('properties_pagination_showing', 'Mostrando', 'Showing') }} <span id="paginationFrom">0</span> - <span id="paginationTo">0</span> {{ $txt('properties_pagination_of', 'de', 'of') }} <span id="paginationTotal">0</span> {{ $txt('properties_pagination_properties', 'propiedades', 'properties') }}
            </div>
            <div class="flex items-center gap-2" id="paginationButtons">
                {{-- Pagination buttons will be inserted here --}}
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- SECCIÃƒÆ’Ã¢â‚¬Å“N FUTURISTA - PROCESO DE COMPRA - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section class="py-20 lg:py-28 relative overflow-hidden" style="background-color: var(--fe-process-bg, #1C1C1C);">
    {{-- Animated Background --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full blur-3xl animate-float" style="background-color: var(--fe-process-glow1, rgba(209, 160, 84, 0.2));"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 rounded-full blur-3xl animate-float" style="animation-delay: -3s; background-color: var(--fe-process-glow2, rgba(118, 141, 89, 0.2));"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.05)_1px,transparent_0)] [background-size:40px_40px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background: var(--fe-process-badge_bg, rgba(255,255,255,0.1)); color: var(--fe-process-badge_text, #768D59);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                {{ $txt('process_badge', 'Proceso Simplificado', 'Simplified Process') }}
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                {{ $txt('process_title', 'Tu nuevo hogar en', 'Your new home in') }}
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-process-highlight_from, #D1A054), var(--fe-process-highlight_to, #768D59));">{{ $txt('process_title_highlight', '4 simples pasos', '4 simple steps') }}</span>
            </h2>
            <p class="text-lg" style="color: var(--fe-process-subtitle, #94a3b8);">
                {{ $txt('process_subtitle', 'Hemos simplificado el proceso inmobiliario para que puedas enfocarte en lo que realmente importa.', 'We simplified the real estate process so you can focus on what really matters.') }}
            </p>
        </div>

        {{-- Steps --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Step 1 --}}
            <div class="relative group">
                <div class="absolute -inset-1 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-300" style="background: linear-gradient(to right, var(--fe-process-step1_from, #D1A054), var(--fe-process-step1_to, #D1A054));"></div>
                <div class="relative backdrop-blur-sm rounded-2xl p-8 h-full" style="background: var(--fe-process-card_bg, rgba(30, 41, 59, 0.5)); border: 1px solid var(--fe-process-card_border, rgba(51, 65, 85, 0.5));">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold mb-6" style="background: linear-gradient(to bottom right, var(--fe-process-step1_from, #D1A054), var(--fe-process-step1_to, #D1A054));">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">{{ $txt('process_step1_title', 'Explora', 'Explore') }}</h3>
                    <p style="color: var(--fe-process-card_text, #94a3b8);">{{ $txt('process_step1_desc', 'Navega por nuestro catálogo y usa los filtros para encontrar propiedades que te interesen.', 'Browse our catalog and use filters to find properties that interest you.') }}</p>
                </div>
                {{-- Connector --}}
                <div class="hidden lg:block absolute top-1/2 -right-4 w-8 h-0.5" style="background: linear-gradient(to right, var(--fe-process-step1_from, #D1A054), transparent);"></div>
            </div>

            {{-- Step 2 --}}
            <div class="relative group">
                <div class="absolute -inset-1 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-300" style="background: linear-gradient(to right, var(--fe-process-step2_from, #A52A2A), var(--fe-process-step2_to, #A52A2A));"></div>
                <div class="relative backdrop-blur-sm rounded-2xl p-8 h-full" style="background: var(--fe-process-card_bg, rgba(30, 41, 59, 0.5)); border: 1px solid var(--fe-process-card_border, rgba(51, 65, 85, 0.5));">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold mb-6" style="background: linear-gradient(to bottom right, var(--fe-process-step2_from, #A52A2A), var(--fe-process-step2_to, #A52A2A));">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">{{ $txt('process_step2_title', 'Agenda', 'Schedule') }}</h3>
                    <p style="color: var(--fe-process-card_text, #94a3b8);">{{ $txt('process_step2_desc', 'Programa una visita presencial o virtual con uno de nuestros asesores expertos.', 'Schedule an in-person or virtual visit with one of our expert advisors.') }}</p>
                </div>
                {{-- Connector --}}
                <div class="hidden lg:block absolute top-1/2 -right-4 w-8 h-0.5" style="background: linear-gradient(to right, var(--fe-process-step2_from, #A52A2A), transparent);"></div>
            </div>

            {{-- Step 3 --}}
            <div class="relative group">
                <div class="absolute -inset-1 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-300" style="background: linear-gradient(to right, var(--fe-process-step3_from, #5B5B5B), var(--fe-process-step3_to, #979790));"></div>
                <div class="relative backdrop-blur-sm rounded-2xl p-8 h-full" style="background: var(--fe-process-card_bg, rgba(30, 41, 59, 0.5)); border: 1px solid var(--fe-process-card_border, rgba(51, 65, 85, 0.5));">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold mb-6" style="background: linear-gradient(to bottom right, var(--fe-process-step3_from, #5B5B5B), var(--fe-process-step3_to, #979790));">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">{{ $txt('process_step3_title', 'Negocia', 'Negotiate') }}</h3>
                    <p style="color: var(--fe-process-card_text, #94a3b8);">{{ $txt('process_step3_desc', 'Te ayudamos a negociar el mejor precio y condiciones para tu compra o renta.', 'We help you negotiate the best price and conditions for your purchase or rental.') }}</p>
                </div>
                {{-- Connector --}}
                <div class="hidden lg:block absolute top-1/2 -right-4 w-8 h-0.5" style="background: linear-gradient(to right, var(--fe-process-step3_from, #5B5B5B), transparent);"></div>
            </div>

            {{-- Step 4 --}}
            <div class="relative group">
                <div class="absolute -inset-1 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-300" style="background: linear-gradient(to right, var(--fe-process-step4_from, #768D59), var(--fe-process-step4_to, #768D59));"></div>
                <div class="relative backdrop-blur-sm rounded-2xl p-8 h-full" style="background: var(--fe-process-card_bg, rgba(30, 41, 59, 0.5)); border: 1px solid var(--fe-process-card_border, rgba(51, 65, 85, 0.5));">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold mb-6" style="background: linear-gradient(to bottom right, var(--fe-process-step4_from, #768D59), var(--fe-process-step4_to, #768D59));">
                        4
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">{{ $txt('process_step4_title', '¡Listo!', 'Done!') }}</h3>
                    <p style="color: var(--fe-process-card_text, #94a3b8);">{{ $txt('process_step4_desc', 'Firma, recibe las llaves y disfruta de tu nuevo hogar. ¡Así de fácil!', 'Sign, receive the keys and enjoy your new home. It is that easy!') }}</p>
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="text-center mt-16">
            <a href="#contacto" class="inline-flex items-center gap-2 px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                {{ $txt('process_cta', 'Comenzar ahora', 'Start now') }}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- TESTIMONIOS - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section class="py-20 lg:py-28" style="background-color: var(--fe-testimonials-bg, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: var(--fe-testimonials-badge_bg, #fffbeb); color: var(--fe-testimonials-badge_text, #d97706);">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                {{ $txt('testimonials_badge', 'Lo que dicen nuestros clientes', 'What our clients say') }}
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4" style="color: var(--fe-testimonials-title, #1C1C1C);">
                {{ $txt('testimonials_title', 'Historias de éxito', 'Success stories') }}
            </h2>
            <p class="text-lg" style="color: var(--fe-testimonials-subtitle, #5B5B5B);">
                {{ $txt('testimonials_subtitle', 'Cientos de familias han encontrado su hogar ideal con nosotros.', 'Hundreds of families have found their ideal home with us.') }}
            </p>
        </div>

        {{-- Testimonials Grid --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Testimonial 1 --}}
            <div class="rounded-2xl p-8 border relative" style="background: linear-gradient(to bottom right, var(--fe-testimonials-card_bg_from, #f8fafc), var(--fe-testimonials-card_bg_to, #ffffff)); border-color: var(--fe-testimonials-card_border, #f1f5f9);">
                <div class="absolute top-6 right-6 text-6xl font-serif" style="color: var(--fe-testimonials-quote1, #e0e7ff);">"</div>
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: var(--fe-testimonials-stars, #D1A054);">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <p class="mb-6 relative z-10" style="color: var(--fe-testimonials-card_text, #5B5B5B);">
                    {{ $txt('testimonials_quote_1', 'El proceso fue increíblemente sencillo. En menos de un mes encontré la casa perfecta para mi familia. El equipo de San Miguel fue excepcional.', 'The process was incredibly simple. In less than a month I found the perfect home for my family. The San Miguel team was exceptional.') }}
                </p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(to bottom right, var(--fe-testimonials-avatar1_from, #D1A054), var(--fe-testimonials-avatar1_to, #768D59));">
                        MG
                    </div>
                    <div>
                        <div class="font-semibold" style="color: var(--fe-testimonials-name, #1C1C1C);">{{ $txt('testimonials_name_1', 'María García', 'Maria Garcia') }}</div>
                        <div class="text-sm" style="color: var(--fe-testimonials-role, #5B5B5B);">{{ $txt('testimonials_role_1', 'Compradora - Polanco', 'Buyer - Polanco') }}</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="rounded-2xl p-8 border relative" style="background: linear-gradient(to bottom right, var(--fe-testimonials-card_bg_from, #f8fafc), var(--fe-testimonials-card_bg_to, #ffffff)); border-color: var(--fe-testimonials-card_border, #f1f5f9);">
                <div class="absolute top-6 right-6 text-6xl font-serif" style="color: var(--fe-testimonials-quote2, #d1fae5);">"</div>
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: var(--fe-testimonials-stars, #D1A054);">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <p class="mb-6 relative z-10" style="color: var(--fe-testimonials-card_text, #5B5B5B);">
                    {{ $txt('testimonials_quote_2', 'Como inversionista, valoro la transparencia. San Miguel me brindó toda la información que necesitaba para tomar la mejor decisión.', 'As an investor, I value transparency. San Miguel gave me all the information I needed to make the best decision.') }}
                </p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(to bottom right, var(--fe-testimonials-avatar2_from, #A52A2A), var(--fe-testimonials-avatar2_to, #D1A054));">
                        CR
                    </div>
                    <div>
                        <div class="font-semibold" style="color: var(--fe-testimonials-name, #1C1C1C);">{{ $txt('testimonials_name_2', 'Carlos Rodríguez', 'Carlos Rodriguez') }}</div>
                        <div class="text-sm" style="color: var(--fe-testimonials-role, #5B5B5B);">{{ $txt('testimonials_role_2', 'Inversionista - Santa Fe', 'Investor - Santa Fe') }}</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial 3 --}}
            <div class="rounded-2xl p-8 border relative" style="background: linear-gradient(to bottom right, var(--fe-testimonials-card_bg_from, #f8fafc), var(--fe-testimonials-card_bg_to, #ffffff)); border-color: var(--fe-testimonials-card_border, #f1f5f9);">
                <div class="absolute top-6 right-6 text-6xl font-serif" style="color: var(--fe-testimonials-quote3, rgba(165,42,42,0.1));">"</div>
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: var(--fe-testimonials-stars, #D1A054);">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <p class="mb-6 relative z-10" style="color: var(--fe-testimonials-card_text, #5B5B5B);">
                    {{ $txt('testimonials_quote_3', 'Rentar mi departamento fue súper fácil. Sin aval, contrato flexible y el equipo siempre disponible para resolver mis dudas.', 'Renting my apartment was super easy. No guarantor, flexible contract and a team always available to solve my questions.') }}
                </p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(to bottom right, var(--fe-testimonials-avatar3_from, #D1A054), var(--fe-testimonials-avatar3_to, #D1A054));">
                        AL
                    </div>
                    <div>
                        <div class="font-semibold" style="color: var(--fe-testimonials-name, #1C1C1C);">{{ $txt('testimonials_name_3', 'Ana López', 'Ana Lopez') }}</div>
                        <div class="text-sm" style="color: var(--fe-testimonials-role, #5B5B5B);">{{ $txt('testimonials_role_3', 'Arrendataria - Condesa', 'Tenant - Condesa') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- SOBRE NOSOTROS - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="nosotros" class="py-20 lg:py-28" style="background: linear-gradient(to bottom right, var(--fe-about-bg_from, #f8fafc), var(--fe-about-bg_to, rgba(238, 242, 255, 0.3)));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Image Side --}}
            <div class="relative">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80" alt="{{ $txt('about_image_alt', 'Equipo San Miguel Properties', 'San Miguel Properties team') }}" class="w-full h-[500px] object-cover">
                    <div class="absolute inset-0" style="background: linear-gradient(to top, var(--fe-about-image_overlay, rgba(28, 28, 28, 0.6)), transparent);"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-8">
                        <p class="text-white text-lg font-medium">{{ $txt('about_image_caption_title', 'Nuestro equipo de expertos', 'Our team of experts') }}</p>
                        <p class="text-white/70">{{ $txt('about_image_caption_subtitle', '+15 años de experiencia en el mercado', '+15 years of market experience') }}</p>
                    </div>
                </div>
                {{-- Floating Card --}}
                <div class="absolute -bottom-6 -right-6 rounded-2xl shadow-xl p-6 max-w-xs hidden lg:block" style="background-color: var(--fe-about-card_bg, #ffffff);">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center text-white" style="background: linear-gradient(to bottom right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" style="color: var(--fe-about-card_title, #1C1C1C);">{{ $txt('about_card_metric_value', '98%', '98%') }}</div>
                            <div class="text-sm" style="color: var(--fe-about-card_text, #5B5B5B);">{{ $txt('about_card_metric_label', 'Satisfacción de clientes', 'Client satisfaction') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content Side --}}
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6" style="background-color: var(--fe-about-badge_bg, #e0e7ff); color: var(--fe-about-badge_text, #D1A054);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ $txt('home_about_badge', 'Sobre Nosotros', 'About Us') }}
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6" style="color: var(--fe-about-title, #1C1C1C);">
                    {{ $txt('home_about_title', 'Más que una inmobiliaria, somos tu', 'More than a real estate agency, we are your') }}
                    <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">{{ $txt('home_about_title_highlight', 'aliado', 'ally') }}</span>
                </h2>

                <p class="text-lg mb-8" style="color: var(--fe-about-text, #5B5B5B);">
                    {{ $txt('home_about_text', 'Desde 2009, San Miguel Properties ha sido el puente entre familias y sus hogares soñados. Con un enfoque centrado en el cliente y tecnología de vanguardia, hemos transformado la experiencia inmobiliaria en México.', 'Since 2009, San Miguel Properties has been the bridge between families and their dream homes. With a customer-focused approach and cutting-edge technology, we have transformed the real estate experience in Mexico.') }}
                </p>

                {{-- Values --}}
                <div class="space-y-4 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--fe-about-value1_bg, #e0e7ff); color: var(--fe-about-value1_icon, #D1A054);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--fe-about-value_title, #1C1C1C);">{{ $txt('home_about_value_1_title', 'Transparencia total', 'Total transparency') }}</h4>
                            <p class="text-sm" style="color: var(--fe-about-value_text, #5B5B5B);">{{ $txt('home_about_value_1_desc', 'Sin costos ocultos. Toda la información que necesitas, cuando la necesitas.', 'No hidden costs. All the information you need, when you need it.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--fe-about-value2_bg, #d1fae5); color: var(--fe-about-value2_icon, #768D59);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--fe-about-value_title, #1C1C1C);">{{ $txt('home_about_value_2_title', 'Asesoría personalizada', 'Personalized advice') }}</h4>
                            <p class="text-sm" style="color: var(--fe-about-value_text, #5B5B5B);">{{ $txt('home_about_value_2_desc', 'Un asesor dedicado que entiende tus necesidades y te guía en cada paso.', 'A dedicated advisor who understands your needs and guides you at every step.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--fe-about-value3_bg, rgba(165,42,42,0.1)); color: var(--fe-about-value3_icon, #A52A2A);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--fe-about-value_title, #1C1C1C);">{{ $txt('home_about_value_3_title', 'Tecnología innovadora', 'Innovative technology') }}</h4>
                            <p class="text-sm" style="color: var(--fe-about-value_text, #5B5B5B);">{{ $txt('home_about_value_3_desc', 'Herramientas digitales que simplifican la búsqueda y el proceso de compra.', 'Digital tools that simplify search and the buying process.') }}</p>
                        </div>
                    </div>
                </div>

                <a href="#contacto" class="inline-flex items-center gap-2 px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                    {{ $txt('home_about_cta', 'Conoce más sobre nosotros', 'Learn more about us') }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- FORMULARIO DE CONTACTO - Usa variables CSS dinÃƒÆ’Ã‚Â¡micas --}}
{{-- ============================================== --}}
<section id="contacto" class="py-20 lg:py-28" style="background-color: var(--fe-contact-bg, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">
            {{-- Contact Info --}}
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6" style="background-color: var(--fe-contact-badge_bg, #eef2ff); color: var(--fe-contact-badge_text, #D1A054);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ $txt('home_contact_badge', 'Contáctanos', 'Contact us') }}
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6" style="color: var(--fe-contact-title, #1C1C1C);">
                    {{ $txt('home_contact_title', '¿Listo para encontrar tu', 'Ready to find your') }}
                    <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">{{ $txt('home_contact_title_highlight', 'hogar ideal', 'ideal home') }}</span>?
                </h2>

                <p class="text-lg mb-10" style="color: var(--fe-contact-text, #5B5B5B);">
                    {{ $txt('home_contact_text', 'Déjanos tus datos y uno de nuestros asesores se pondrá en contacto contigo en menos de 24 horas.', 'Leave your details and one of our advisors will contact you in less than 24 hours.') }}
                </p>

                {{-- Contact Methods --}}
                <div class="space-y-6">
                    <a href="tel:{{ $contactPhoneHref }}" class="flex items-center gap-4 p-4 rounded-xl transition-colors group" style="background-color: var(--fe-contact-method_bg, #f8fafc);">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform" style="background: linear-gradient(to bottom right, var(--fe-contact-phone_from, #768D59), var(--fe-contact-phone_to, #768D59));">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold" style="color: var(--fe-contact-method_title, #1C1C1C);">{{ $txt('home_contact_phone_label', 'Teléfono', 'Phone') }}</p>
                            <p style="color: var(--fe-contact-method_text, #5B5B5B);">{{ $contactPhone }}</p>
                        </div>
                    </a>

                    <a href="https://wa.me/{{ $contactWhatsapp }}" target="_blank" class="flex items-center gap-4 p-4 rounded-xl transition-colors group" style="background-color: var(--fe-contact-method_bg, #f8fafc);">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform" style="background: linear-gradient(to bottom right, var(--fe-contact-whatsapp_from, #768D59), var(--fe-contact-whatsapp_to, #768D59));">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold" style="color: var(--fe-contact-method_title, #1C1C1C);">{{ $txt('home_contact_whatsapp_label', 'WhatsApp', 'WhatsApp') }}</p>
                            <p style="color: var(--fe-contact-method_text, #5B5B5B);">{{ $txt('home_contact_whatsapp_text', 'Chatea con nosotros', 'Chat with us') }}</p>
                        </div>
                    </a>

                    <a href="mailto:{{ $contactEmail }}" class="flex items-center gap-4 p-4 rounded-xl transition-colors group" style="background-color: var(--fe-contact-method_bg, #f8fafc);">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform" style="background: linear-gradient(to bottom right, var(--fe-contact-email_from, #D1A054), var(--fe-contact-email_to, #D1A054));">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold" style="color: var(--fe-contact-method_title, #1C1C1C);">{{ $txt('home_contact_email_label', 'Email', 'Email') }}</p>
                            <p style="color: var(--fe-contact-method_text, #5B5B5B);">{{ $contactEmail }}</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Contact Form --}}
            <div class="rounded-3xl p-8 lg:p-10 border shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-contact-form_bg_from, #f8fafc), var(--fe-contact-form_bg_to, #ffffff)); border-color: var(--fe-contact-form_border, #f1f5f9);">
                <form class="space-y-6">
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="contact_name" class="block text-sm font-medium mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('home_contact_form_name_label', 'Nombre completo', 'Full name') }}</label>
                            <input type="text" id="contact_name" name="name" required
                                   class="w-full px-4 py-3 rounded-xl transition-all focus:outline-none"
                                   style="background-color: var(--fe-contact-input_bg, #ffffff); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);"
                                   placeholder="{{ $txt('home_contact_form_name_placeholder', 'Tu nombre', 'Your name') }}">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('home_contact_form_phone_label', 'Teléfono', 'Phone') }}</label>
                            <input type="tel" id="contact_phone" name="phone" required
                                   class="w-full px-4 py-3 rounded-xl transition-all focus:outline-none"
                                   style="background-color: var(--fe-contact-input_bg, #ffffff); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);"
                                   placeholder="{{ $txt('home_contact_form_phone_placeholder', '+52 55 1234 5678', '+1 555 123 4567') }}">
                        </div>
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('home_contact_form_email_label', 'Correo electrónico', 'Email') }}</label>
                        <input type="email" id="contact_email" name="email" required
                               class="w-full px-4 py-3 rounded-xl transition-all focus:outline-none"
                               style="background-color: var(--fe-contact-input_bg, #ffffff); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);"
                               placeholder="{{ $txt('home_contact_form_email_placeholder', 'tu@correo.com', 'you@email.com') }}">
                    </div>

                    <div>
                        <label for="contact_interest" class="block text-sm font-medium mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('home_contact_form_interest_label', 'Estoy interesado en', 'I am interested in') }}</label>
                        <select id="contact_interest" name="interest"
                                class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
                                style="background-color: var(--fe-contact-input_bg, #ffffff); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);">
                            <option value="">{{ $txt('home_contact_form_interest_placeholder', 'Selecciona una opción', 'Select an option') }}</option>
                            <option value="comprar">{{ $txt('home_contact_form_interest_buy', 'Comprar una propiedad', 'Buy a property') }}</option>
                            <option value="rentar">{{ $txt('home_contact_form_interest_rent', 'Rentar una propiedad', 'Rent a property') }}</option>
                            <option value="vender">{{ $txt('home_contact_form_interest_sell', 'Vender mi propiedad', 'Sell my property') }}</option>
                            <option value="inversion">{{ $txt('home_contact_form_interest_invest', 'Invertir en bienes raíces', 'Invest in real estate') }}</option>
                            <option value="otro">{{ $txt('home_contact_form_interest_other', 'Otro', 'Other') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="contact_message" class="block text-sm font-medium mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('home_contact_form_message_label', 'Mensaje', 'Message') }}</label>
                        <textarea id="contact_message" name="message" rows="4"
                                  class="w-full px-4 py-3 rounded-xl transition-all resize-none focus:outline-none"
                                  style="background-color: var(--fe-contact-input_bg, #ffffff); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);"
                                  placeholder="{{ $txt('home_contact_form_message_placeholder', 'Cuéntanos más sobre lo que buscas...', 'Tell us more about what you are looking for...') }}"></textarea>
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="contact_privacy" name="privacy" required
                               class="mt-1 h-4 w-4 rounded border-slate-300 focus:ring-2"
                               style="color: var(--fe-primary-from, #D1A054);">
                        <label for="contact_privacy" class="text-sm" style="color: var(--fe-contact-privacy_text, #5B5B5B);">
                            {{ $txt('home_contact_form_privacy', 'Acepto la política de privacidad y autorizo el tratamiento de mis datos.', 'I accept the privacy policy and authorize data processing.') }}
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full px-8 py-4 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-[1.02] flex items-center justify-center gap-2"
                            style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        {{ $txt('home_contact_form_submit', 'Enviar mensaje', 'Send message') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const isEnLocale = (window.__PUBLIC_LOCALE__ || 'es') === 'en';
const tPublic = window.publicT || ((key, fallback = '') => fallback);
const homeI18n = {
    heroImageAlt: tPublic('home.hero.imageAlt', isEnLocale ? 'Property' : 'Propiedad'),
    heroTypeFallback: tPublic('home.hero.typeFallback', isEnLocale ? 'Property' : 'Propiedad'),
    heroFeaturedFallback: tPublic('home.hero.featuredFallback', isEnLocale ? 'Featured property' : 'Propiedad destacada'),
    priceFallback: tPublic('home.property.priceFallback', isEnLocale ? 'Ask for price' : 'Consultar precio'),
    locationFallback: tPublic('home.property.locationFallback', isEnLocale ? 'Location available' : 'Ubicacion disponible'),
    saleLabel: tPublic('common.sale', isEnLocale ? 'For sale' : 'En venta'),
    rentLabel: tPublic('common.rent', isEnLocale ? 'For rent' : 'En renta'),
    cardTitleFallback: tPublic('home.property.cardTitleFallback', isEnLocale ? 'Available property' : 'Propiedad disponible'),
    bedroomsShort: tPublic('home.property.bedroomsShort', isEnLocale ? 'Beds' : 'Rec.'),
    bathroomsShort: tPublic('home.property.bathroomsShort', isEnLocale ? 'Baths' : 'Banos'),
    detailsCta: tPublic('common.details', isEnLocale ? 'View details' : 'Ver detalles'),
    areaUnit: tPublic('home.property.areaUnit', isEnLocale ? 'sqm' : 'm2'),
};

// ============================================
// HERO SLIDER - SWIPER.JS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Hero Slider
    const heroSlider = new Swiper('.hero-slider', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1000,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Cargar propiedades para el slider desde la API
    loadHeroSlides();

    // Cargar opciones de filtro dinÃƒÆ’Ã‚Â¡micas para el hero y la barra de stats
    loadHomeFilterOptions();
});

async function loadHomeFilterOptions() {
    try {
        const res = await fetch('/api/public/properties/filter-options');
        const data = await res.json();
        if (data.success && data.data) {
            const opts = data.data;

            // Actualizar hero quick filters
            const heroFiltersEl = document.getElementById('heroQuickFilters');
            if (heroFiltersEl && opts.property_types && opts.property_types.length > 0) {
                heroFiltersEl.innerHTML = opts.property_types.map(type =>
                    `<a href="/propiedades?property_type_name=${encodeURIComponent(type)}" class="px-4 py-2 rounded-full bg-white/10 text-white/80 text-sm font-medium hover:bg-white/20 transition-colors border border-white/10">
                        ${type}
                    </a>`
                ).join('');
            }

            // Badge de propiedades: ya no se sobrescribe porque el texto viene del CMS
            // El admin puede personalizar el badge desde /admin/cms/pages ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢ Home ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢ hero_badge_text
        }
    } catch (e) {
        console.error('Error loading home filter options:', e);
    }
}

async function loadHeroSlides() {
    try {
        const response = await fetch('/api/public/properties?per_page=3&sort=desc&order=updated_at');
        const data = await response.json();
        
        if (data.success && data.data && data.data.data && data.data.data.length > 0) {
            const swiperWrapper = document.getElementById('heroSliderWrapper');
            swiperWrapper.innerHTML = '';
            
            data.data.data.forEach((property, index) => {
                const imageUrl = property.cover_media_asset?.serving_url || property.cover_media_asset?.url ||
                               `https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80`;
                
                const slide = document.createElement('div');
                slide.className = 'swiper-slide';
                slide.innerHTML = `
                    <div class="absolute inset-0">
                        <img src="${imageUrl}" alt="${property.title || homeI18n.heroImageAlt}" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-black/80 to-transparent z-10 hidden">
                        <div class="max-w-7xl mx-auto">
                            <span class="inline-block px-3 py-1 text-white text-sm font-medium rounded-full mb-4" style="background-color: var(--fe-primary-from, #D1A054);">
                                ${property.property_type_name || homeI18n.heroTypeFallback}
                            </span>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-2">${property.title || homeI18n.heroFeaturedFallback}</h3>
                            <p class="text-white/80">${property.location?.city || ''} ${property.location?.city_area || ''}</p>
                        </div>
                    </div>
                `;
                swiperWrapper.appendChild(slide);
            });

            // Reinicializar Swiper despuÃƒÆ’Ã‚Â©s de agregar slides
            const swiper = document.querySelector('.hero-slider').swiper;
            swiper.update();
            swiper.slideToLoop(0);
        }
    } catch (error) {
        console.error('Error loading hero slides:', error);
        // Mantener los slides de placeholder si hay error
    }
}

// ============================================
// PROPERTIES FILTER & PAGINATION (Alpine.js)
// ============================================
function propertiesFilter() {
    return {
        filters: {
            search: '',
            property_type_name: '',
            published: null,
            order: 'updated_at',
            sort: 'desc',
            per_page: 6,
            page: 1
        },
        properties: [],
        pagination: null,
        loading: true,
        dynamicPropertyTypes: [],

        init() {
            this.loadFilterOptions();
            this.loadProperties();
            // Make this instance globally available
            window.propertiesApp = this;
        },

        async loadFilterOptions() {
            try {
                const res = await fetch('/api/public/properties/filter-options');
                const data = await res.json();
                if (data.success && data.data) {
                    this.dynamicPropertyTypes = data.data.property_types || [];
                }
            } catch (e) {
                console.error('Error loading filter options:', e);
            }
        },

        async loadProperties() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams();
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key] !== null && this.filters[key] !== undefined && this.filters[key] !== '') {
                        params.append(key, this.filters[key]);
                    }
                });

                const response = await fetch(`/api/public/properties?${params.toString()}`);
                const data = await response.json();

                if (data.success && data.data) {
                    this.properties = data.data.data || [];
                    this.pagination = {
                        current_page: data.data.current_page,
                        last_page: data.data.last_page,
                        from: data.data.from,
                        to: data.data.to,
                        total: data.data.total
                    };
                    this.renderProperties();
                    this.renderPagination();
                }
            } catch (error) {
                console.error('Error loading properties:', error);
            } finally {
                this.loading = false;
            }
        },

        renderProperties() {
            const grid = document.getElementById('propertiesGrid');
            const empty = document.getElementById('propertiesEmpty');

            if (this.properties.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');
            grid.innerHTML = this.properties.map(property => this.createPropertyCard(property)).join('');
        },

        createPropertyCard(property) {
            const imageUrl = property.cover_media_asset?.serving_url || property.cover_media_asset?.url ||
                           'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
            
            const price = property.operations?.[0]?.formatted_amount || homeI18n.priceFallback;
            const operationType = property.operations?.[0]?.operation_type || '';
            const location = [property.location?.city, property.location?.city_area].filter(Boolean).join(', ') || homeI18n.locationFallback;

            return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                    <div class="relative h-56 overflow-hidden">
                        <img src="${imageUrl}" alt="${property.title || homeI18n.heroImageAlt}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        ${property.property_type_name ? `
                        <span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-medium rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #1C1C1C);">
                            ${property.property_type_name}
                        </span>
                        ` : ''}
                        
                        ${operationType ? `
                        <span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${operationType === 'sale' ? 'var(--fe-properties-sale_badge, #768D59)' : 'var(--fe-properties-rent_badge, #D1A054)'};">
                            ${operationType === 'sale' ? homeI18n.saleLabel : homeI18n.rentLabel}
                        </span>
                        ` : ''}
                        
                        <button class="absolute bottom-4 right-4 w-10 h-10 rounded-full backdrop-blur-sm flex items-center justify-center transition-colors opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 duration-300" style="background-color: var(--fe-properties-fav_btn_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-fav_btn_icon, #5B5B5B);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--fe-properties-card_location, #5B5B5B);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            ${location}
                        </div>
                        
                        <h3 class="text-lg font-bold mb-3 line-clamp-2 transition-colors" style="color: var(--fe-properties-card_title, #1C1C1C);">
                            ${property.title || homeI18n.cardTitleFallback}
                        </h3>
                        
                        <div class="text-2xl font-bold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                            ${price}
                        </div>
                        
                        <div class="flex items-center gap-4 text-sm border-t pt-4" style="color: var(--fe-properties-card_meta, #5B5B5B); border-color: var(--fe-properties-card_divider, #f1f5f9);">
                            ${property.bedrooms !== null ? `
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                ${property.bedrooms} ${homeI18n.bedroomsShort}
                            </div>
                            ` : ''}
                            
                            ${property.bathrooms !== null ? `
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                ${property.bathrooms} ${homeI18n.bathroomsShort}
                            </div>
                            ` : ''}
                            
                            ${property.construction_size ? `
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                                ${property.construction_size} ${homeI18n.areaUnit}
                            </div>
                            ` : ''}
                        </div>

                        <div class="mt-5">
                            <a href="/propiedades/${property.id}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                                ${homeI18n.detailsCta}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            `;
        },

        renderPagination() {
            const container = document.getElementById('paginationButtons');
            const fromEl = document.getElementById('paginationFrom');
            const toEl = document.getElementById('paginationTo');
            const totalEl = document.getElementById('paginationTotal');

            if (!this.pagination) return;

            fromEl.textContent = this.pagination.from || 0;
            toEl.textContent = this.pagination.to || 0;
            totalEl.textContent = this.pagination.total || 0;

            let buttons = '';

            // Previous button
            buttons += `
                <button onclick="window.propertiesApp.goToPage(${this.pagination.current_page - 1})" 
                        ${this.pagination.current_page <= 1 ? 'disabled' : ''}
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= this.pagination.last_page; i++) {
                if (
                    i === 1 ||
                    i === this.pagination.last_page ||
                    (i >= this.pagination.current_page - 1 && i <= this.pagination.current_page + 1)
                ) {
                    buttons += `
                        <button onclick="window.propertiesApp.goToPage(${i})"
                                class="w-10 h-10 rounded-lg ${i === this.pagination.current_page
                                    ? 'text-white'
                                    : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}
                                font-medium transition-colors"
                                ${i === this.pagination.current_page ? 'style="background: linear-gradient(to right, var(--fe-pagination-active_from, #D1A054), var(--fe-pagination-active_to, #768D59));"' : ''}>
                            ${i}
                        </button>
                    `;
                } else if (
                    i === this.pagination.current_page - 2 ||
                    i === this.pagination.current_page + 2
                ) {
                    buttons += `<span class="px-2 text-slate-400">...</span>`;
                }
            }

            // Next button
            buttons += `
                <button onclick="window.propertiesApp.goToPage(${this.pagination.current_page + 1})" 
                        ${this.pagination.current_page >= this.pagination.last_page ? 'disabled' : ''}
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            `;

            container.innerHTML = buttons;
        },

        applyFilters() {
            this.filters.page = 1;
            this.loadProperties();
        },

        clearFilters() {
            this.filters = {
                search: '',
                property_type_name: '',
                published: null,
                order: 'updated_at',
                sort: 'desc',
                per_page: 6,
                page: 1
            };
            this.loadProperties();
        },

        hasFilters() {
            return this.filters.search !== '' || 
                   this.filters.property_type_name !== '' || 
                   this.filters.published !== null;
        },

        togglePublished(value) {
            this.filters.published = this.filters.published === value ? null : value;
            this.applyFilters();
        },

        setPropertyType(type) {
            this.filters.property_type_name = this.filters.property_type_name === type ? '' : type;
            this.applyFilters();
        },

        goToPage(page) {
            if (page < 1 || page > this.pagination.last_page) return;
            this.filters.page = page;
            this.loadProperties();
            
            // Scroll to properties section
            document.getElementById('propiedades').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };
}

// ============================================
// HERO SEARCH - FunciÃƒÆ’Ã‚Â³n para el buscador del hero
// ============================================
function heroSearch() {
    return {
        searchQuery: '',
        submitSearch() {
            if (this.searchQuery.trim()) {
                window.location.href = '/propiedades?search=' + encodeURIComponent(this.searchQuery.trim());
            } else {
                window.location.href = '/propiedades';
            }
        }
    };
}
</script>
@endpush
