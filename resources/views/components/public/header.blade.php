@php
    use App\Services\CmsService;
    use Illuminate\Support\Str;

    $isHome = request()->routeIs('home');
    $currentLocale = app()->getLocale();
    $nextLocale = $currentLocale === 'es' ? 'en' : 'es';
    $pageData = $pageData ?? null;
    $txt = static fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($currentLocale === 'en' ? $en : $es);

    $menu = CmsService::getMenu('main-header');
    $menuItems = $menu?->rootItems ?? collect();

    $contactSettings = CmsService::settings('contact', $currentLocale);
    $phoneDisplay = trim((string) ($contactSettings['contact_phone'] ?? '+52 55 1234 5678'));
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?: '+525512345678';

    $labels = [
        'home' => $txt('header_nav_home', 'Inicio', 'Home'),
        'properties' => $txt('header_nav_properties', 'Propiedades', 'Properties'),
        'offices' => $txt('header_nav_offices', 'Agencias', 'Agencies'),
        'agents' => $txt('header_nav_agents', 'Agentes', 'Agents'),
        'about' => $txt('header_nav_about', 'Nosotros', 'About'),
        'contact' => $txt('header_nav_contact', 'Contacto', 'Contact'),
        'dashboard' => $txt('header_cta_dashboard', 'Panel', 'Dashboard'),
        'login' => $txt('header_cta_login', 'Acceder', 'Login'),
        'menu' => $txt('header_mobile_menu', 'Abrir menu', 'Open menu'),
    ];
    $languageSwitchLabel = $nextLocale === 'en'
        ? $txt('header_switch_to_en', 'Cambiar a ingles', 'Switch to English')
        : $txt('header_switch_to_es', 'Cambiar a espanol', 'Switch to Spanish');
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
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties') }}" class="h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                @else
                    <div class="grid h-11 w-11 place-items-center rounded-xl text-white shadow-lg transition-transform duration-300 group-hover:scale-105" style="background: linear-gradient(to bottom right, var(--fe-header-logo_gradient_from, #D1A054), var(--fe-header-logo_gradient_to, #768D59));">
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
                        <p class="text-base font-bold tracking-tight">{{ $txt('i18n_header_brand_primary', 'San Miguel', 'San Miguel') }}</p>
                        <p class="text-xs font-medium opacity-80">{{ $txt('i18n_header_brand_secondary', 'Properties', 'Properties') }}</p>
                    </div>
                @endif
            </a>

            <div class="hidden lg:flex lg:items-center lg:gap-1">
                @if($menuItems->isNotEmpty())
                    @foreach($menuItems as $item)
                        @php
                            $resolvedUrl = $item->resolvedUrl() ?? '#';
                            $isExternal = Str::startsWith($resolvedUrl, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                            $href = $isExternal ? $resolvedUrl : url($resolvedUrl);
                            $target = $item->target ?: '_self';
                        @endphp
                        <a href="{{ $href }}" target="{{ $target }}"
                           :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                           class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                            {{ $item->label($currentLocale) }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ url('/') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['home'] }}</a>
                    <a href="{{ route('public.properties.index') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['properties'] }}</a>
                    <a href="{{ route('public.mls-offices.index') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['offices'] }}</a>
                    <a href="{{ route('public.mls-agents.index') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['agents'] }}</a>
                    <a href="{{ route('about') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['about'] }}</a>
                    <a href="{{ route('public.contact') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['contact'] }}</a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('public.locale.switch', ['locale' => $nextLocale]) }}"
                   aria-label="{{ $languageSwitchLabel }}"
                   :class="{ 'text-slate-700 border-slate-300': scrolled, 'text-white/90 border-white/30 hover:text-white': !scrolled }"
                   class="hidden sm:inline-flex items-center justify-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors">
                    {{ strtoupper($nextLocale) }}
                </a>

                <a href="tel:{{ $phoneHref }}"
                   :class="{ 'text-slate-600': scrolled, 'text-white/90': !scrolled }"
                   class="hidden md:flex items-center gap-2 text-sm font-medium transition-colors duration-200" style="--hover-color: var(--fe-primary-from, #D1A054);" onmouseover="this.style.color=getComputedStyle(this).getPropertyValue('--hover-color')" onmouseout="this.style.color='';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ $phoneDisplay }}
                </a>

                @auth
                    <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59)); --tw-ring-color: rgba(209,160,84,0.2);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        {{ $labels['dashboard'] }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59)); --tw-ring-color: rgba(209,160,84,0.2);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ $labels['login'] }}
                    </a>
                @endauth

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        :class="{ 'text-slate-900': scrolled, 'text-white': !scrolled }"
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg transition-colors duration-200 hover:bg-slate-900/10 focus:outline-none"
                        aria-label="{{ $labels['menu'] }}">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

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
                @if($menuItems->isNotEmpty())
                    @foreach($menuItems as $item)
                        @php
                            $resolvedUrl = $item->resolvedUrl() ?? '#';
                            $isExternal = Str::startsWith($resolvedUrl, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                            $href = $isExternal ? $resolvedUrl : url($resolvedUrl);
                            $target = $item->target ?: '_self';
                        @endphp
                        <a href="{{ $href }}" target="{{ $target }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                            {{ $item->label($currentLocale) }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['home'] }}</a>
                    <a href="{{ route('public.properties.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['properties'] }}</a>
                    <a href="{{ route('public.mls-offices.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['offices'] }}</a>
                    <a href="{{ route('public.mls-agents.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['agents'] }}</a>
                    <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['about'] }}</a>
                    <a href="{{ route('public.contact') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['contact'] }}</a>
                @endif

                <div class="my-4 border-t border-slate-100"></div>

                <a href="{{ route('public.locale.switch', ['locale' => $nextLocale]) }}" @click="mobileMenuOpen = false" aria-label="{{ $languageSwitchLabel }}" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    {{ strtoupper($nextLocale) }}
                </a>

                <a href="tel:{{ $phoneHref }}" class="flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    {{ $phoneDisplay }}
                </a>

                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59));">
                        {{ $labels['dashboard'] }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59));">
                        {{ $labels['login'] }}
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>

<style>
    .nav-link-hover:hover {
        color: var(--fe-header-nav_hover, #D1A054) !important;
    }
</style>

<div class="h-0"></div>
