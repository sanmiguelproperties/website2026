{{-- Header Público - San Miguel Properties --}}
{{-- Usa variables CSS dinámicas del frontend color system --}}
@php
    // Solo en la Home el header inicia transparente con texto blanco.
    // En el resto de vistas debe iniciar como si estuviera "scrolleado" (texto oscuro) para que sea legible sobre fondo blanco.
    $isHome = request()->routeIs('home');
@endphp

<header
        x-data="{ mobileMenuOpen: false, scrolled: {{ $isHome ? 'false' : 'true' }}, isHome: {{ $isHome ? 'true' : 'false' }} }"
        x-init="
            if (isHome) {
                scrolled = window.pageYOffset > 50;
                window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 50 });
            } else {
                scrolled = true;
            }
        "
        :class="scrolled ? 'bg-white/95 shadow-soft backdrop-blur-lg' : 'bg-transparent'"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between">
            {{-- Logo - Usa imagen del CMS si existe, sino fallback al SVG --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'San Miguel Properties' }}" class="h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                @else
                    <div class="grid h-11 w-11 place-items-center rounded-xl text-white shadow-lg transition-transform duration-300 group-hover:scale-105" style="background: linear-gradient(to bottom right, var(--fe-header-logo_gradient_from, #4f46e5), var(--fe-header-logo_gradient_to, #10b981));">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18" />
                            <path d="M6 21V7a2 2 0 0 1 2-2h3" />
                            <path d="M11 21V11a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v10" />
                            <path d="M9 9h2" />
                            <path d="M9 13h2" />
                            <path d="M9 17h2" />
                            <path d="M15 13h2" />
                            <path d="M15 17h2" />
                        </svg>
                    </div>
                    <div :class="{ 'text-slate-900': scrolled, 'text-white': !scrolled }" class="transition-colors duration-300">
                        <p class="text-base font-bold tracking-tight">San Miguel</p>
                        <p class="text-xs font-medium opacity-80">Properties</p>
                    </div>
                @endif
            </a>

            {{-- Navigation Links (Desktop) - Usa variables CSS dinámicas --}}
            <div class="hidden lg:flex lg:items-center lg:gap-1">
                <a href="{{ url('/') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   :style="scrolled ? 'transition: color 0.2s;' : ''"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Inicio
                </a>
                <a href="{{ route('public.properties.index') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Propiedades
                </a>
                <a href="{{ route('public.mls-offices.index') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Agencias
                </a>
                <a href="{{ route('public.mls-agents.index') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Agentes
                </a>
                <a href="{{ route('about') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Nosotros
                </a>
                <a href="{{ route('public.contact') }}" 
                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                   class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                    Contacto
                </a>
            </div>

            {{-- CTA Button & Mobile Menu Button --}}
            <div class="flex items-center gap-4">
                {{-- Phone Number (Desktop) --}}
                <a href="tel:+525512345678" 
                   :class="{ 'text-slate-600': scrolled, 'text-white/90': !scrolled }"
                   class="hidden md:flex items-center gap-2 text-sm font-medium transition-colors duration-200 hover:text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    +52 55 1234 5678
                </a>

                {{-- Login/Dashboard Button - Usa variables CSS dinámicas --}}
                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"
                       style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #4f46e5), var(--fe-header-cta_button_to, #10b981));">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"
                       style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #4f46e5), var(--fe-header-cta_button_to, #10b981));">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Acceder
                    </a>
                @endauth

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        :class="{ 'text-slate-900': scrolled, 'text-white': !scrolled }"
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg transition-colors duration-200 hover:bg-slate-900/10 focus:outline-none"
                        aria-label="Abrir menú">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu - Usa variables CSS dinámicas --}}
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden absolute top-full left-0 right-0 bg-white shadow-xl rounded-b-2xl border-t border-slate-100">
            <div class="px-4 py-6 space-y-1">
                <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Inicio
                </a>
                <a href="{{ route('public.properties.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Propiedades
                </a>
                <a href="{{ route('public.mls-offices.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 21V7l8-4v18" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V11l-6-4" />
                    </svg>
                    Agencias
                </a>
                <a href="{{ route('public.mls-agents.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21a8 8 0 10-16 0" />
                    </svg>
                    Agentes
                </a>
                <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Nosotros
                </a>
                <a href="{{ route('public.contact') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-header-mobile_menu_icon_active, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Contacto
                </a>

                {{-- Divider --}}
                <div class="my-4 border-t border-slate-100"></div>

                {{-- Phone --}}
                <a href="tel:+525512345678" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-primary-to, #10b981);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    +52 55 1234 5678
                </a>

                {{-- Login Button - Usa variables CSS dinámicas --}}
                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #4f46e5), var(--fe-header-cta_button_to, #10b981));">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Ir al Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #4f46e5), var(--fe-header-cta_button_to, #10b981));">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Acceder al Portal
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>

{{-- CSS para hover en navegación usando variables dinámicas --}}
<style>
    .nav-link-hover:hover {
        color: var(--fe-header-nav_hover, #4f46e5) !important;
    }
</style>

{{-- Spacer to prevent content from going under fixed header --}}
<div class="h-0"></div>
