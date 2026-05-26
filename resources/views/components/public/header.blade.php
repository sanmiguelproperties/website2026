@php
    use App\Models\ZonePage;
    use App\Services\CmsService;
    use Illuminate\Support\Str;

    $isHome = request()->routeIs('home');
    $currentLocale = app()->getLocale();
    $nextLocale = $currentLocale === 'es' ? 'en' : 'es';
    $headerData = CmsService::getPageData('header', $currentLocale);
    $txt = static fn (string $key, string $es, string $en) => $headerData?->field($key) ?? ($currentLocale === 'en' ? $en : $es);

    $menu = CmsService::getMenu('main-header');
    $menuItems = $menu?->rootItems ?? collect();
    $showMlsOffices = CmsService::settingBoolean('public_show_mls_offices', true);
    $showMlsAgents = CmsService::settingBoolean('public_show_mls_agents', true);

    $isHiddenMlsUrl = static function (?string $resolvedUrl) use ($showMlsOffices, $showMlsAgents): bool {
        $path = parse_url((string) $resolvedUrl, PHP_URL_PATH);
        $normalizedPath = '/' . ltrim((string) ($path ?? $resolvedUrl ?? ''), '/');
        $normalizedPath = rtrim(Str::lower($normalizedPath), '/');
        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }

        if (!$showMlsOffices && (str_starts_with($normalizedPath, '/agencias') || str_starts_with($normalizedPath, '/mls-offices'))) {
            return true;
        }

        if (!$showMlsAgents && (str_starts_with($normalizedPath, '/agentes') || str_starts_with($normalizedPath, '/mls-agents'))) {
            return true;
        }

        return false;
    };

    $menuItems = $menuItems->filter(function ($item) use ($showMlsOffices, $showMlsAgents, $isHiddenMlsUrl) {
        $routeName = (string) ($item->route_name ?? '');

        if (
            !$showMlsOffices
            && in_array($routeName, ['public.mls-offices.index', 'public.mls-offices.show', 'public.mls-offices.legacy-index', 'public.mls-offices.legacy-show'], true)
        ) {
            return false;
        }

        if (
            !$showMlsAgents
            && in_array($routeName, ['public.mls-agents.index', 'public.mls-agents.show', 'public.mls-agents.legacy-index', 'public.mls-agents.legacy-show'], true)
        ) {
            return false;
        }

        return !$isHiddenMlsUrl($item->resolvedUrl());
    })->values();

    $isHomeMenuItem = static function ($item): bool {
        $routeName = Str::lower(trim((string) ($item->route_name ?? '')));
        if (in_array($routeName, ['home', 'public.home'], true)) {
            return true;
        }

        $resolvedUrl = trim((string) ($item->resolvedUrl() ?? ''), '/');
        if ($resolvedUrl === '') {
            return true;
        }

        return in_array(Str::lower($resolvedUrl), ['inicio', 'home'], true);
    };

    $contactSettings = CmsService::settings('contact', $currentLocale);
    $phoneDisplay = trim((string) ($contactSettings['contact_phone'] ?? '+52 55 1234 5678'));
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?: '+525512345678';
    $parsePixelSetting = static function (?string $value, int $default, int $min, int $max): int {
        if ($value === null) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false) {
            return $default;
        }

        return max($min, min($max, $parsed));
    };
    $logoHeightDesktop = $parsePixelSetting(CmsService::setting('header_logo_height_desktop', $currentLocale), 44, 24, 96);
    $logoHeightMobile = $parsePixelSetting(CmsService::setting('header_logo_height_mobile', $currentLocale), 36, 20, 80);
    $headerHeightDesktop = $parsePixelSetting(CmsService::setting('header_height_desktop', $currentLocale), 80, 80, 200);
    $headerHeightDesktop = max($headerHeightDesktop, $logoHeightDesktop + 20);

    $labels = [
        'home' => $txt('header_nav_home', 'Inicio', 'Home'),
        'properties' => $txt('header_nav_properties', 'Propiedades', 'Properties'),
        'neighborhoods' => $txt('header_nav_neighborhoods', 'Colonias', 'Neighborhoods'),
        'favorites' => $txt('header_nav_favorites', 'Favoritas', 'Favorites'),
        'offices' => $txt('header_nav_offices', 'Agencias', 'Agencies'),
        'agents' => $currentLocale === 'en' ? 'Agents' : 'Vendedores',
        'about' => $txt('header_nav_about', 'Nosotros', 'About'),
        'team' => $txt('header_nav_team', 'Equipo', 'Team'),
        'contact' => $txt('header_nav_contact', 'Contacto', 'Contact'),
        'dashboard' => $txt('header_cta_dashboard', 'Panel', 'Dashboard'),
        'login' => $txt('header_cta_login', 'Acceder', 'Login'),
        'menu' => $txt('header_mobile_menu', 'Abrir menu', 'Open menu'),
    ];
    $neighborhoodMenuItems = ZonePage::query()
        ->where('is_active', true)
        ->where(function ($query): void {
            $query->whereNull('show_in_menu')
                ->orWhere('show_in_menu', true);
        })
        ->orderByRaw('CASE WHEN menu_order IS NULL THEN 1 ELSE 0 END')
        ->orderBy('menu_order')
        ->orderBy('city_area')
        ->get(['slug', 'city_area', 'title_es', 'title_en'])
        ->map(function (ZonePage $zonePage): array {
            $label = trim((string) $zonePage->city_area);
            if ($label === '') {
                $label = trim((string) ($zonePage->title_es ?: $zonePage->title_en ?: ''));
            }

            return [
                'label' => $label,
                'href' => route('public.zones.show', ['zoneSlug' => $zonePage->slug]),
                'target' => '_self',
            ];
        })
        ->filter(fn (array $entry): bool => trim((string) ($entry['label'] ?? '')) !== '')
        ->unique(fn (array $entry): string => Str::lower(trim((string) $entry['label']) . '|' . trim((string) $entry['href'])))
        ->values()
        ->all();
    $luxuryPropertiesHref = route('public.properties.index', [
        'search' => 'Casa',
        'operation_type' => 'sale',
        'min_price' => 1500000,
    ]);
    $rentPropertiesHref = route('public.properties.index', [
        'operation_type' => 'rental',
    ]);
    $salePropertiesHref = route('public.properties.index', [
        'operation_type' => 'sale',
    ]);
    $rentMenuLabel = $txt('header_nav_properties_rent', 'Renta', 'Rent');
    $saleMenuLabel = $txt('header_nav_properties_sale', 'Venta', 'Sale');
    $terrainPropertiesHref = route('public.properties.index', [
        'property_type_name' => 'Land and Lots',
    ]);
    $terrainMenuLabel = $txt('header_nav_properties_terrains', 'Terrenos', 'Land');
    $commercialPropertiesHref = route('public.properties.index', [
        'property_type_name' => 'Commercial',
    ]);
    $commercialMenuLabel = $txt('header_nav_properties_commercial', 'Commercial', 'Commercial');
    $resolveMenuLink = static function ($item): array {
        $resolvedUrl = $item->resolvedUrl() ?? '#';
        $isExternal = Str::startsWith($resolvedUrl, ['http://', 'https://', 'mailto:', 'tel:', '#']);

        return [
            'href' => $isExternal ? $resolvedUrl : url($resolvedUrl),
            'target' => $item->target ?: '_self',
        ];
    };
    $isPropertiesMenuItem = static function ($item): bool {
        $routeName = (string) ($item->route_name ?? '');
        if ($routeName === 'public.properties.index') {
            return true;
        }

        $resolvedUrl = trim((string) ($item->resolvedUrl() ?? ''), '/');
        if (in_array(Str::lower($resolvedUrl), ['propiedades', 'properties'], true)) {
            return true;
        }

        $labelEs = Str::lower(trim((string) ($item->label_es ?? '')));
        $labelEn = Str::lower(trim((string) ($item->label_en ?? '')));

        return str_contains($labelEs, 'propiedad') || str_contains($labelEn, 'propert');
    };
    $isAgentsMenuItem = static function ($item): bool {
        $routeName = Str::lower(trim((string) ($item->route_name ?? '')));
        if (in_array($routeName, ['public.mls-agents.index', 'public.mls-agents.show', 'public.mls-agents.legacy-index', 'public.mls-agents.legacy-show'], true)) {
            return true;
        }

        $resolvedUrl = Str::lower(trim((string) ($item->resolvedUrl() ?? ''), '/'));
        if (in_array($resolvedUrl, ['agentes', 'agents', 'mls-agents'], true)) {
            return true;
        }

        $labelEs = Str::lower(trim((string) ($item->label_es ?? '')));
        $labelEn = Str::lower(trim((string) ($item->label_en ?? '')));

        return str_contains($labelEs, 'agente') || str_contains($labelEn, 'agent');
    };
    $isAboutMenuItem = static function ($item): bool {
        $routeName = Str::lower(trim((string) ($item->route_name ?? '')));
        if (in_array($routeName, ['about', 'public.about'], true)) {
            return true;
        }

        $resolvedUrl = Str::lower(trim((string) ($item->resolvedUrl() ?? ''), '/'));
        if (in_array($resolvedUrl, ['nosotros', 'about'], true)) {
            return true;
        }

        $labelEs = Str::lower(trim((string) ($item->label_es ?? '')));
        $labelEn = Str::lower(trim((string) ($item->label_en ?? '')));

        return str_contains($labelEs, 'nosotros') || str_contains($labelEn, 'about');
    };
    $isTeamMenuItem = static function ($item): bool {
        $routeName = Str::lower(trim((string) ($item->route_name ?? '')));
        if (in_array($routeName, ['public.team', 'public.team.legacy'], true)) {
            return true;
        }

        $resolvedUrl = Str::lower(trim((string) ($item->resolvedUrl() ?? ''), '/'));
        if (in_array($resolvedUrl, ['equipo', 'team'], true)) {
            return true;
        }

        $labelEs = Str::lower(trim((string) ($item->label_es ?? '')));
        $labelEn = Str::lower(trim((string) ($item->label_en ?? '')));

        return str_contains($labelEs, 'equipo') || str_contains($labelEn, 'team');
    };
    $menuItemLabel = static function ($item) use ($currentLocale, $labels, $isAgentsMenuItem): string {
        if ($isAgentsMenuItem($item)) {
            return $labels['agents'];
        }

        return $item->label($currentLocale);
    };
    $menuItems = $menuItems->reject($isHomeMenuItem)->values();
    $menuItems = $menuItems->reject($isTeamMenuItem)->values();
    [$propertyMenuItems, $otherMenuItems] = $menuItems->partition(fn ($item) => $isPropertiesMenuItem($item));
    $menuItems = $propertyMenuItems->concat($otherMenuItems)->values();
    $localizedMenuLabel = static function (?string $rawLabel, string $locale): string {
        $label = trim((string) $rawLabel);
        if ($label === '') {
            return '';
        }

        $parts = preg_split('/\s+\/\s+/u', $label);
        if (!is_array($parts) || count($parts) !== 2) {
            return $label;
        }

        return trim($locale === 'en' ? $parts[1] : $parts[0]);
    };
    $buildAboutSubmenuItems = static function ($item) use ($resolveMenuLink, $currentLocale, $localizedMenuLabel): array {
        $submenuItems = [];
        $children = $item->children ?? collect();

        foreach ($children as $child) {
            $childLink = $resolveMenuLink($child);
            $entry = [
                'label' => $localizedMenuLabel($child->label($currentLocale), $currentLocale),
                'href' => $childLink['href'],
                'target' => $childLink['target'],
            ];

            $label = Str::lower(trim((string) ($entry['label'] ?? '')));
            $href = Str::lower(trim((string) ($entry['href'] ?? '')));
            $isTeamLink = str_contains($label, 'equipo')
                || str_contains($label, 'team')
                || str_contains($href, '/equipo')
                || str_ends_with($href, '/team');

            if (!$isTeamLink) {
                $submenuItems[] = $entry;
            }
        }

        return collect($submenuItems)
            ->filter(fn (array $entry) => trim((string) ($entry['label'] ?? '')) !== '')
            ->unique(fn (array $entry) => Str::lower(trim((string) $entry['label']) . '|' . trim((string) $entry['href'])))
            ->values()
            ->all();
    };
    $isLuxurySubmenuItem = static function (array $entry) use ($luxuryPropertiesHref): bool {
        $label = Str::lower(trim((string) ($entry['label'] ?? '')));
        $href = Str::lower(trim((string) ($entry['href'] ?? '')));
        $luxuryHref = Str::lower($luxuryPropertiesHref);

        return str_contains($label, 'lujo')
            || str_contains($label, 'luxury')
            || str_contains($href, 'min_price=1500000')
            || str_contains($href, 'search=casa')
            || $href === $luxuryHref;
    };
    $isTerrainSubmenuItem = static function (array $entry) use ($terrainPropertiesHref): bool {
        $label = Str::lower(trim((string) ($entry['label'] ?? '')));
        $href = Str::lower(trim((string) ($entry['href'] ?? '')));
        $terrainHref = Str::lower($terrainPropertiesHref);

        return str_contains($label, 'terreno')
            || str_contains($label, 'land')
            || str_contains($label, 'lote')
            || str_contains($label, 'lot')
            || str_contains($href, 'property_type_name=land+and+lots')
            || str_contains($href, 'property_type_name=land%20and%20lots')
            || str_contains($href, 'property_type_name=terreno')
            || $href === $terrainHref;
    };
    $isCommercialSubmenuItem = static function (array $entry) use ($commercialPropertiesHref): bool {
        $label = Str::lower(trim((string) ($entry['label'] ?? '')));
        $href = Str::lower(trim((string) ($entry['href'] ?? '')));
        $commercialHref = Str::lower($commercialPropertiesHref);

        return str_contains($label, 'commercial')
            || str_contains($label, 'comercial')
            || str_contains($href, 'property_type_name=commercial')
            || $href === $commercialHref;
    };
    $isRentSubmenuItem = static function (array $entry) use ($rentPropertiesHref): bool {
        $label = Str::lower(trim((string) ($entry['label'] ?? '')));
        $href = Str::lower(trim((string) ($entry['href'] ?? '')));
        $rentHref = Str::lower($rentPropertiesHref);

        return in_array($label, ['renta', 'rent', 'rental', 'en renta', 'for rent'], true)
            || str_contains($href, 'operation_type=rental')
            || str_contains($href, 'operation_type=rent')
            || str_contains($href, 'operation_type=renta')
            || $href === $rentHref;
    };
    $isSaleSubmenuItem = static function (array $entry) use ($salePropertiesHref): bool {
        $label = Str::lower(trim((string) ($entry['label'] ?? '')));
        $href = Str::lower(trim((string) ($entry['href'] ?? '')));
        $saleHref = Str::lower($salePropertiesHref);

        return in_array($label, ['venta', 'sale', 'en venta', 'for sale'], true)
            || str_contains($href, 'operation_type=sale')
            || str_contains($href, 'operation_type=venta')
            || $href === $saleHref;
    };
    $buildPropertiesSubmenuItems = static function ($item) use (
        $resolveMenuLink,
        $txt,
        $currentLocale,
        $luxuryPropertiesHref,
        $rentPropertiesHref,
        $salePropertiesHref,
        $rentMenuLabel,
        $saleMenuLabel,
        $terrainPropertiesHref,
        $terrainMenuLabel,
        $commercialPropertiesHref,
        $commercialMenuLabel,
        $localizedMenuLabel,
        $isLuxurySubmenuItem,
        $isTerrainSubmenuItem,
        $isCommercialSubmenuItem,
        $isRentSubmenuItem,
        $isSaleSubmenuItem
    ): array {
        $baseLink = $resolveMenuLink($item);
        $allPropertiesItem = [
            'label' => $txt('header_nav_properties_all', 'Todas las propiedades', 'All properties'),
            'href' => $baseLink['href'],
            'target' => $baseLink['target'],
        ];

        $children = $item->children ?? collect();
        $regularItems = [];
        $luxuryItem = null;
        $rentItem = null;
        $saleItem = null;
        $terrainItem = null;
        $commercialItem = null;

        foreach ($children as $child) {
            $childLink = $resolveMenuLink($child);
            $entry = [
                'label' => $localizedMenuLabel($child->label($currentLocale), $currentLocale),
                'href' => $childLink['href'],
                'target' => $childLink['target'],
            ];

            if ($isLuxurySubmenuItem($entry)) {
                $luxuryItem = $luxuryItem ?? $entry;
                continue;
            }

            if ($isRentSubmenuItem($entry)) {
                $rentItem = $rentItem ?? $entry;
                continue;
            }

            if ($isSaleSubmenuItem($entry)) {
                $saleItem = $saleItem ?? $entry;
                continue;
            }

            if ($isTerrainSubmenuItem($entry)) {
                $terrainItem = $terrainItem ?? $entry;
                continue;
            }

            if ($isCommercialSubmenuItem($entry)) {
                $commercialItem = $commercialItem ?? $entry;
                continue;
            }

            $regularItems[] = $entry;
        }

        $luxuryItem = $luxuryItem ?? [
            'label' => $txt('header_nav_properties_luxury', 'Lujo', 'Luxury'),
            'href' => $luxuryPropertiesHref,
            'target' => '_self',
        ];
        $rentItem = $rentItem ?? [
            'label' => $rentMenuLabel,
            'href' => $rentPropertiesHref,
            'target' => '_self',
        ];
        $saleItem = $saleItem ?? [
            'label' => $saleMenuLabel,
            'href' => $salePropertiesHref,
            'target' => '_self',
        ];
        $terrainItem = $terrainItem ?? [
            'label' => $terrainMenuLabel,
            'href' => $terrainPropertiesHref,
            'target' => '_self',
        ];
        $commercialItem = $commercialItem ?? [
            'label' => $commercialMenuLabel,
            'href' => $commercialPropertiesHref,
            'target' => '_self',
        ];
        $submenuItems = array_merge([$rentItem, $saleItem, $luxuryItem, $terrainItem, $commercialItem, $allPropertiesItem], $regularItems);

        return collect($submenuItems)
            ->filter(fn (array $entry) => trim((string) ($entry['label'] ?? '')) !== '')
            ->unique(fn (array $entry) => Str::lower(trim((string) $entry['label']) . '|' . trim((string) $entry['href'])))
            ->values()
            ->all();
    };
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
    :class="scrolled ? 'is-scrolled bg-white/95 shadow-soft backdrop-blur-lg' : 'is-top bg-transparent'"
    class="smp-public-header fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    style="--header-desktop-height: {{ $headerHeightDesktop }}px;">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="header-inner-row flex items-center justify-between">
            <a href="{{ url('/') }}"
               class="flex items-center gap-3 group"
               style="--header-logo-height-desktop: {{ $logoHeightDesktop }}px; --header-logo-height-mobile: {{ $logoHeightMobile }}px;">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? $txt('i18n_common_siteName', 'San Miguel Properties', 'San Miguel Properties') }}" class="header-site-logo w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                @else
                    <div class="header-site-logo-fallback grid place-items-center rounded-xl text-white shadow-lg transition-transform duration-300 group-hover:scale-105" style="background: linear-gradient(to bottom right, var(--fe-header-logo_gradient_from, #D1A054), var(--fe-header-logo_gradient_to, #768D59));">
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
                    <div :class="{ 'text-slate-900': scrolled, 'text-white': !scrolled }" class="header-brand-text transition-colors duration-300">
                        <p class="text-base font-bold tracking-tight">{{ $txt('i18n_header_brand_primary', 'San Miguel', 'San Miguel') }}</p>
                        <p class="text-xs font-medium opacity-80">{{ $txt('i18n_header_brand_secondary', 'Properties', 'Properties') }}</p>
                    </div>
                @endif
            </a>

            <div class="hidden lg:flex lg:items-center lg:gap-1">
                @if($menuItems->isNotEmpty())
                    @foreach($menuItems as $item)
                        @php
                            $itemLink = $resolveMenuLink($item);
                            $href = $itemLink['href'];
                            $target = $itemLink['target'];
                            $isPropertiesItem = $isPropertiesMenuItem($item);
                            $propertiesSubmenuItems = $isPropertiesItem ? $buildPropertiesSubmenuItems($item) : [];
                            $isAboutItem = $isAboutMenuItem($item);
                            $aboutSubmenuItems = $isAboutItem ? $buildAboutSubmenuItems($item) : [];
                        @endphp
                        @if($isPropertiesItem && !empty($propertiesSubmenuItems))
                            <div x-data="{ open: false }"
                                 class="relative"
                                 @mouseenter="open = true"
                                 @mouseleave="open = false"
                                 @click.outside="open = false"
                                 @keydown.escape.window="open = false">
                                <a href="{{ $href }}" target="{{ $target }}"
                                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                                   class="header-nav-link relative inline-flex items-center gap-1 px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                                    {{ $menuItemLabel($item) }}
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </a>

                                <div x-show="open"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                 class="header-dropdown-panel absolute left-0 top-full z-40 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                                    @if(!empty($neighborhoodMenuItems))
                                        <div x-data="{ neighborhoodsOpen: false }"
                                             class="relative"
                                             @mouseenter="neighborhoodsOpen = true"
                                             @mouseleave="neighborhoodsOpen = false">
                                            <button type="button"
                                                    @click.prevent="neighborhoodsOpen = !neighborhoodsOpen"
                                                    class="header-dropdown-link flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                                <span>{{ $labels['neighborhoods'] }}</span>
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': neighborhoodsOpen }" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div x-show="neighborhoodsOpen"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                                 class="header-dropdown-panel absolute left-full top-0 z-50 ml-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                                                <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                                                    @foreach($neighborhoodMenuItems as $zoneMenuItem)
                                                        <a href="{{ $zoneMenuItem['href'] }}"
                                                           target="{{ $zoneMenuItem['target'] }}"
                                                           class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                                            {{ $zoneMenuItem['label'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @foreach($propertiesSubmenuItems as $submenuItem)
                                        <a href="{{ $submenuItem['href'] }}"
                                           target="{{ $submenuItem['target'] }}"
                                           class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                            {{ $submenuItem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($isAboutItem && !empty($aboutSubmenuItems))
                            <div x-data="{ open: false }"
                                 class="relative"
                                 @mouseenter="open = true"
                                 @mouseleave="open = false"
                                 @click.outside="open = false"
                                 @keydown.escape.window="open = false">
                                <a href="{{ $href }}" target="{{ $target }}"
                                   :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                                   class="header-nav-link relative inline-flex items-center gap-1 px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                                    {{ $menuItemLabel($item) }}
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </a>

                                <div x-show="open"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="header-dropdown-panel absolute left-0 top-full z-40 mt-2 w-60 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                                    @foreach($aboutSubmenuItems as $submenuItem)
                                        <a href="{{ $submenuItem['href'] }}"
                                           target="{{ $submenuItem['target'] }}"
                                           class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                            {{ $submenuItem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $href }}" target="{{ $target }}"
                               :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                               class="header-nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                                {{ $menuItemLabel($item) }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <div x-data="{ open: false }"
                         class="relative"
                         @mouseenter="open = true"
                         @mouseleave="open = false"
                         @click.outside="open = false"
                         @keydown.escape.window="open = false">
                        <a href="{{ route('public.properties.index') }}"
                           :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }"
                           class="header-nav-link relative inline-flex items-center gap-1 px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">
                            {{ $labels['properties'] }}
                            <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="header-dropdown-panel absolute left-0 top-full z-40 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                            @if(!empty($neighborhoodMenuItems))
                                <div x-data="{ neighborhoodsOpen: false }"
                                     class="relative"
                                     @mouseenter="neighborhoodsOpen = true"
                                     @mouseleave="neighborhoodsOpen = false">
                                    <button type="button"
                                            @click.prevent="neighborhoodsOpen = !neighborhoodsOpen"
                                            class="header-dropdown-link flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                        <span>{{ $labels['neighborhoods'] }}</span>
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': neighborhoodsOpen }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="neighborhoodsOpen"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="header-dropdown-panel absolute left-full top-0 z-50 ml-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                                        <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                                            @foreach($neighborhoodMenuItems as $zoneMenuItem)
                                                <a href="{{ $zoneMenuItem['href'] }}"
                                                   target="{{ $zoneMenuItem['target'] }}"
                                                   class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                                    {{ $zoneMenuItem['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <a href="{{ $rentPropertiesHref }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $rentMenuLabel }}
                            </a>
                            <a href="{{ $salePropertiesHref }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $saleMenuLabel }}
                            </a>
                            <a href="{{ $luxuryPropertiesHref }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $txt('header_nav_properties_luxury', 'Lujo', 'Luxury') }}
                            </a>
                            <a href="{{ $terrainPropertiesHref }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $terrainMenuLabel }}
                            </a>
                            <a href="{{ $commercialPropertiesHref }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $commercialMenuLabel }}
                            </a>
                            <a href="{{ route('public.properties.index') }}" class="header-dropdown-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                {{ $txt('header_nav_properties_all', 'Todas las propiedades', 'All properties') }}
                            </a>
                        </div>
                    </div>
                    @if($showMlsOffices)
                        <a href="{{ route('public.mls-offices.index') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="header-nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['offices'] }}</a>
                    @endif
                    @if($showMlsAgents)
                        <a href="{{ route('public.mls-agents.index') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="header-nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['agents'] }}</a>
                    @endif
                    <a href="{{ route('about') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="header-nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['about'] }}</a>
                    <a href="{{ route('public.contact') }}" :class="{ 'text-slate-700': scrolled, 'text-white/90 hover:text-white': !scrolled }" class="header-nav-link relative px-4 py-2 text-sm font-medium transition-colors duration-200 rounded-lg hover:bg-slate-900/5 nav-link-hover">{{ $labels['contact'] }}</a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('public.locale.switch', ['locale' => $nextLocale]) }}"
                   aria-label="{{ $languageSwitchLabel }}"
                   :class="{ 'text-slate-700 border-slate-300': scrolled, 'text-white/90 border-white/30 hover:text-white': !scrolled }"
                   class="header-lang-switch hidden sm:inline-flex items-center justify-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors">
                    {{ strtoupper($nextLocale) }}
                </a>

                <a href="tel:{{ $phoneHref }}"
                   :class="{ 'text-slate-600': scrolled, 'text-white/90': !scrolled }"
                   class="header-phone-link hidden md:flex items-center gap-2 text-sm font-medium transition-colors duration-200" style="--hover-color: var(--fe-primary-from, #D1A054);" onmouseover="this.style.color=getComputedStyle(this).getPropertyValue('--hover-color')" onmouseout="this.style.color='';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ $phoneDisplay }}
                </a>

                <a href="{{ route('public.properties.favorites') }}"
                   :class="{ 'text-slate-700 border-slate-300 bg-white': scrolled, 'text-white border-white/30 bg-white/10 hover:bg-white/20': !scrolled }"
                   class="header-favorites-link relative hidden sm:inline-flex items-center justify-center w-10 h-10 rounded-xl border transition-colors"
                   aria-label="{{ $labels['favorites'] }}"
                   title="{{ $labels['favorites'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span data-favorites-count class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full text-[10px] leading-[18px] text-center font-bold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">0</span>
                </a>

                @auth
                    <a href="{{ url('/dashboard') }}" class="header-cta-button hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59)); --tw-ring-color: var(--fe-header-cta_ring, rgba(209,160,84,0.2));">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        {{ $labels['dashboard'] }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="header-cta-button hidden sm:inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59)); --tw-ring-color: var(--fe-header-cta_ring, rgba(209,160,84,0.2));">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ $labels['login'] }}
                    </a>
                @endauth

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        :class="{ 'text-slate-900': scrolled, 'text-white': !scrolled }"
                        class="header-mobile-toggle lg:hidden inline-flex items-center justify-center p-2 rounded-lg transition-colors duration-200 hover:bg-slate-900/10 focus:outline-none"
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
             class="header-mobile-panel lg:hidden absolute top-full left-0 right-0 bg-white shadow-xl rounded-b-2xl border-t border-slate-100">
            <div class="px-4 py-6 space-y-1">
                @if($menuItems->isNotEmpty())
                    @foreach($menuItems as $item)
                        @php
                            $itemLink = $resolveMenuLink($item);
                            $href = $itemLink['href'];
                            $target = $itemLink['target'];
                            $isPropertiesItem = $isPropertiesMenuItem($item);
                            $propertiesSubmenuItems = $isPropertiesItem ? $buildPropertiesSubmenuItems($item) : [];
                            $isAboutItem = $isAboutMenuItem($item);
                            $aboutSubmenuItems = $isAboutItem ? $buildAboutSubmenuItems($item) : [];
                        @endphp
                        @if($isPropertiesItem && !empty($propertiesSubmenuItems))
                            <div x-data="{ open: false }" class="header-mobile-section rounded-xl border border-slate-100 bg-slate-50/60">
                                <button type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-slate-700 font-medium rounded-xl transition-colors hover:bg-slate-100/70">
                                    <span>{{ $menuItemLabel($item) }}</span>
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="space-y-1 px-3 pb-3">
                                    @if(!empty($neighborhoodMenuItems))
                                        <div x-data="{ neighborhoodsOpen: false }" class="rounded-lg border border-slate-200 bg-white">
                                            <button type="button"
                                                    @click="neighborhoodsOpen = !neighborhoodsOpen"
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                                <span>{{ $labels['neighborhoods'] }}</span>
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': neighborhoodsOpen }" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div x-show="neighborhoodsOpen"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                                 class="space-y-1 px-2 pb-2">
                                                @foreach($neighborhoodMenuItems as $zoneMenuItem)
                                                    <a href="{{ $zoneMenuItem['href'] }}"
                                                       target="{{ $zoneMenuItem['target'] }}"
                                                       @click="mobileMenuOpen = false; open = false; neighborhoodsOpen = false"
                                                       class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                                        {{ $zoneMenuItem['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @foreach($propertiesSubmenuItems as $submenuItem)
                                        <a href="{{ $submenuItem['href'] }}"
                                           target="{{ $submenuItem['target'] }}"
                                           @click="mobileMenuOpen = false; open = false"
                                           class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                            {{ $submenuItem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($isAboutItem && !empty($aboutSubmenuItems))
                            <div x-data="{ open: false }" class="header-mobile-section rounded-xl border border-slate-100 bg-slate-50/60">
                                <button type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-slate-700 font-medium rounded-xl transition-colors hover:bg-slate-100/70">
                                    <span>{{ $menuItemLabel($item) }}</span>
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="space-y-1 px-3 pb-3">
                                    @foreach($aboutSubmenuItems as $submenuItem)
                                        <a href="{{ $submenuItem['href'] }}"
                                           target="{{ $submenuItem['target'] }}"
                                           @click="mobileMenuOpen = false; open = false"
                                           class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                            {{ $submenuItem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $href }}" target="{{ $target }}" @click="mobileMenuOpen = false" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                                {{ $menuItemLabel($item) }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <div x-data="{ open: false }" class="header-mobile-section rounded-xl border border-slate-100 bg-slate-50/60">
                        <button type="button"
                                @click="open = !open"
                                class="flex w-full items-center justify-between px-4 py-3 text-left text-slate-700 font-medium rounded-xl transition-colors hover:bg-slate-100/70">
                            <span>{{ $labels['properties'] }}</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="space-y-1 px-3 pb-3">
                            @if(!empty($neighborhoodMenuItems))
                                <div x-data="{ neighborhoodsOpen: false }" class="rounded-lg border border-slate-200 bg-white">
                                    <button type="button"
                                            @click="neighborhoodsOpen = !neighborhoodsOpen"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                        <span>{{ $labels['neighborhoods'] }}</span>
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': neighborhoodsOpen }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="neighborhoodsOpen"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="space-y-1 px-2 pb-2">
                                        @foreach($neighborhoodMenuItems as $zoneMenuItem)
                                            <a href="{{ $zoneMenuItem['href'] }}"
                                               target="{{ $zoneMenuItem['target'] }}"
                                               @click="mobileMenuOpen = false; open = false; neighborhoodsOpen = false"
                                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                                {{ $zoneMenuItem['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <a href="{{ $rentPropertiesHref }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $rentMenuLabel }}
                            </a>
                            <a href="{{ $salePropertiesHref }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $saleMenuLabel }}
                            </a>
                            <a href="{{ $luxuryPropertiesHref }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $txt('header_nav_properties_luxury', 'Lujo', 'Luxury') }}
                            </a>
                            <a href="{{ $terrainPropertiesHref }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $terrainMenuLabel }}
                            </a>
                            <a href="{{ $commercialPropertiesHref }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $commercialMenuLabel }}
                            </a>
                            <a href="{{ route('public.properties.index') }}"
                               @click="mobileMenuOpen = false; open = false"
                               class="header-mobile-link block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-white hover:text-slate-900">
                                {{ $txt('header_nav_properties_all', 'Todas las propiedades', 'All properties') }}
                            </a>
                        </div>
                    </div>
                    @if($showMlsOffices)
                        <a href="{{ route('public.mls-offices.index') }}" @click="mobileMenuOpen = false" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['offices'] }}</a>
                    @endif
                    @if($showMlsAgents)
                        <a href="{{ route('public.mls-agents.index') }}" @click="mobileMenuOpen = false" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['agents'] }}</a>
                    @endif
                    <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['about'] }}</a>
                    <a href="{{ route('public.contact') }}" @click="mobileMenuOpen = false" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">{{ $labels['contact'] }}</a>
                @endif

                <div class="header-mobile-divider my-4 border-t border-slate-100"></div>

                <a href="{{ route('public.locale.switch', ['locale' => $nextLocale]) }}" @click="mobileMenuOpen = false" aria-label="{{ $languageSwitchLabel }}" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    {{ strtoupper($nextLocale) }}
                </a>

                <a href="tel:{{ $phoneHref }}" class="header-mobile-link flex items-center gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    {{ $phoneDisplay }}
                </a>

                <a href="{{ route('public.properties.favorites') }}" @click="mobileMenuOpen = false"
                   class="header-mobile-link flex items-center justify-between gap-3 px-4 py-3 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        {{ $labels['favorites'] }}
                    </span>
                    <span data-favorites-count class="hidden min-w-[20px] h-[20px] px-1 rounded-full text-[11px] leading-[20px] text-center font-bold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">0</span>
                </a>

                @auth
                    <a href="{{ url('/dashboard') }}" class="header-cta-button flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59));">
                        {{ $labels['dashboard'] }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="header-cta-button flex items-center justify-center gap-2 mt-4 w-full rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg" style="background: linear-gradient(to right, var(--fe-header-cta_button_from, #D1A054), var(--fe-header-cta_button_to, #768D59));">
                        {{ $labels['login'] }}
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>

<style>
    .smp-public-header .header-inner-row {
        height: 80px !important;
    }

    .smp-public-header .header-site-logo {
        height: var(--header-logo-height-mobile, 36px) !important;
        width: auto !important;
    }

    .smp-public-header .header-site-logo-fallback {
        width: var(--header-logo-height-mobile, 36px) !important;
        height: var(--header-logo-height-mobile, 36px) !important;
    }

    @media (min-width: 1024px) {
        .smp-public-header .header-inner-row {
            height: var(--header-desktop-height, 80px) !important;
        }

        .smp-public-header .header-site-logo {
            height: var(--header-logo-height-desktop, 44px) !important;
        }

        .smp-public-header .header-site-logo-fallback {
            width: var(--header-logo-height-desktop, 44px) !important;
            height: var(--header-logo-height-desktop, 44px) !important;
        }
    }

    .smp-public-header.is-scrolled {
        background-color: var(--fe-header-background_scrolled, rgba(255,255,255,0.95)) !important;
        box-shadow: var(--fe-header-shadow, 0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10)) !important;
    }

    .smp-public-header.is-top {
        background-color: var(--fe-header-background_top, transparent) !important;
    }

    .smp-public-header.is-scrolled .header-brand-text {
        color: var(--fe-header-brand_text_scrolled, #0f172a) !important;
    }

    .smp-public-header.is-top .header-brand-text {
        color: var(--fe-header-brand_text_top, #ffffff) !important;
    }

    .smp-public-header.is-scrolled .header-nav-link {
        color: var(--fe-header-nav_text_scrolled, #334155) !important;
    }

    .smp-public-header.is-top .header-nav-link {
        color: var(--fe-header-nav_text_top, rgba(255,255,255,0.9)) !important;
    }

    .smp-public-header.is-top .header-nav-link:hover {
        color: var(--fe-header-nav_text_top_hover, #ffffff) !important;
    }

    .smp-public-header .header-nav-link:hover {
        background-color: var(--fe-header-nav_hover_bg, rgba(15,23,42,0.05)) !important;
    }

    .nav-link-hover:hover {
        color: var(--fe-header-nav_hover, #D1A054) !important;
    }

    .smp-public-header .header-dropdown-panel {
        background-color: var(--fe-header-dropdown_bg, #ffffff) !important;
        border-color: var(--fe-header-dropdown_border, #e2e8f0) !important;
        box-shadow: var(--fe-header-dropdown_shadow, 0 25px 50px -12px rgba(0,0,0,0.25)) !important;
    }

    .smp-public-header .header-dropdown-state,
    .smp-public-header .header-dropdown-empty {
        border-color: var(--fe-header-dropdown_border, #e2e8f0) !important;
    }

    .smp-public-header .header-dropdown-title {
        color: var(--fe-header-dropdown_title, #0f172a) !important;
    }

    .smp-public-header .header-dropdown-link {
        color: var(--fe-header-dropdown_text, #334155) !important;
    }

    .smp-public-header .header-dropdown-link:hover {
        color: var(--fe-header-dropdown_text_hover, #0f172a) !important;
    }

    .smp-public-header .header-dropdown-panel .rounded-lg:hover {
        background-color: var(--fe-header-dropdown_hover_bg, #f8fafc) !important;
    }

    .smp-public-header .header-dropdown-icon {
        color: var(--fe-header-dropdown_icon, #64748b) !important;
    }

    .smp-public-header .header-dropdown-icon:hover {
        background-color: var(--fe-header-dropdown_icon_hover_bg, #f1f5f9) !important;
        color: var(--fe-header-dropdown_icon_hover, #334155) !important;
    }

    .smp-public-header .header-dropdown-tag {
        background-color: var(--fe-header-dropdown_tag_bg, #ffffff) !important;
        border-color: var(--fe-header-dropdown_tag_border, #e2e8f0) !important;
        color: var(--fe-header-dropdown_tag_text, #475569) !important;
    }

    .smp-public-header .header-dropdown-tag:hover {
        border-color: var(--fe-header-dropdown_tag_border_hover, #cbd5e1) !important;
        color: var(--fe-header-dropdown_tag_text_hover, #0f172a) !important;
        background-color: var(--fe-header-dropdown_tag_bg, #ffffff) !important;
    }

    .smp-public-header.is-scrolled .header-lang-switch {
        color: var(--fe-header-lang_text_scrolled, #334155) !important;
        border-color: var(--fe-header-lang_border_scrolled, #cbd5e1) !important;
    }

    .smp-public-header.is-top .header-lang-switch {
        color: var(--fe-header-lang_text_top, rgba(255,255,255,0.9)) !important;
        border-color: var(--fe-header-lang_border_top, rgba(255,255,255,0.3)) !important;
    }

    .smp-public-header.is-scrolled .header-phone-link {
        color: var(--fe-header-phone_text_scrolled, #475569) !important;
    }

    .smp-public-header.is-top .header-phone-link {
        color: var(--fe-header-phone_text_top, rgba(255,255,255,0.9)) !important;
    }

    .smp-public-header.is-scrolled .header-favorites-link {
        color: var(--fe-header-favorites_text_scrolled, #334155) !important;
        border-color: var(--fe-header-favorites_border_scrolled, #cbd5e1) !important;
        background-color: var(--fe-header-favorites_bg_scrolled, #ffffff) !important;
    }

    .smp-public-header.is-top .header-favorites-link {
        color: var(--fe-header-favorites_text_top, #ffffff) !important;
        border-color: var(--fe-header-favorites_border_top, rgba(255,255,255,0.3)) !important;
        background-color: var(--fe-header-favorites_bg_top, rgba(255,255,255,0.1)) !important;
    }

    .smp-public-header.is-top .header-favorites-link:hover {
        background-color: var(--fe-header-favorites_bg_top_hover, rgba(255,255,255,0.2)) !important;
    }

    .smp-public-header.is-scrolled .header-mobile-toggle {
        color: var(--fe-header-mobile_toggle_text_scrolled, #0f172a) !important;
    }

    .smp-public-header.is-top .header-mobile-toggle {
        color: var(--fe-header-mobile_toggle_text_top, #ffffff) !important;
    }

    .smp-public-header .header-mobile-toggle:hover {
        background-color: var(--fe-header-mobile_toggle_hover_bg, rgba(15,23,42,0.1)) !important;
    }

    .smp-public-header .header-mobile-panel {
        background-color: var(--fe-header-mobile_panel_bg, #ffffff) !important;
        border-color: var(--fe-header-mobile_panel_border, #f1f5f9) !important;
    }

    .smp-public-header .header-mobile-link {
        color: var(--fe-header-mobile_link_text, #334155) !important;
    }

    .smp-public-header .header-mobile-link:hover {
        background-color: var(--fe-header-mobile_link_hover_bg, #f8fafc) !important;
    }

    .smp-public-header .header-mobile-section {
        background-color: var(--fe-header-mobile_section_bg, rgba(248,250,252,0.6)) !important;
        border-color: var(--fe-header-mobile_section_border, #f1f5f9) !important;
    }

    .smp-public-header .header-mobile-panel button {
        color: var(--fe-header-mobile_section_title, #1e293b) !important;
    }

    .smp-public-header .header-mobile-panel button:hover {
        background-color: var(--fe-header-mobile_section_hover_bg, rgba(241,245,249,0.7)) !important;
    }

    .smp-public-header .header-mobile-state,
    .smp-public-header .header-mobile-city,
    .smp-public-header .header-mobile-empty {
        border-color: var(--fe-header-mobile_section_border, #f1f5f9) !important;
    }

    .smp-public-header .header-mobile-state {
        background-color: var(--fe-header-mobile_panel_bg, #ffffff) !important;
    }

    .smp-public-header .header-mobile-city {
        background-color: var(--fe-header-mobile_section_bg, rgba(248,250,252,0.6)) !important;
    }

    .smp-public-header .header-mobile-divider {
        border-color: var(--fe-header-mobile_section_border, #f1f5f9) !important;
    }

    .smp-public-header .header-mobile-empty {
        color: var(--fe-header-mobile_section_title_muted, #64748b) !important;
        background-color: var(--fe-header-mobile_panel_bg, #ffffff) !important;
    }

    .smp-public-header .header-cta-button {
        --tw-ring-color: var(--fe-header-cta_ring, rgba(209,160,84,0.2));
    }
</style>

<div class="h-0"></div>
