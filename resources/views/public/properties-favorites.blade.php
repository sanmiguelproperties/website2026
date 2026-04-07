@extends('layouts.public')

@php
  $isEn = ($locale ?? app()->getLocale()) === 'en';
  $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
  $pageTitle = $pageData?->entity?->title($locale ?? app()->getLocale()) ?? ($isEn ? 'Favorite Properties' : 'Propiedades Favoritas');
  $favoritesLabels = [
    'badge' => $txt('favorites_badge', 'Mis favoritas', 'My favorites'),
    'titlePrefix' => $txt('favorites_title_prefix', 'Tu coleccion de', 'Your collection of'),
    'titleHighlight' => $txt('favorites_title_highlight', 'propiedades favoritas', 'favorite properties'),
    'subtitle' => $txt('favorites_subtitle', 'Guarda las propiedades que te interesan y revisalas cuando quieras, sin iniciar sesion.', 'Save the properties that interest you and review them anytime, without signing in.'),
    'savedCountPrefix' => $txt('favorites_saved_count_prefix', 'Tienes', 'You have'),
    'savedCountSuffix' => $txt('favorites_saved_count_suffix', 'propiedades en favoritas', 'properties in favorites'),
    'explore' => $txt('favorites_explore_cta', 'Explorar propiedades', 'Browse properties'),
    'clear' => $txt('favorites_clear_cta', 'Vaciar favoritas', 'Clear favorites'),
    'emptyTitle' => $txt('favorites_empty_title', 'Aun no tienes favoritas', 'No favorites yet'),
    'emptySubtitle' => $txt('favorites_empty_subtitle', 'Usa el icono de corazon para guardar propiedades desde cualquier listado o detalle.', 'Use the heart icon to save properties from any listing or detail page.'),
    'askPrice' => $txt('favorites_ask_price', 'Consultar precio', 'Ask for price'),
    'locationFallback' => $txt('favorites_location_fallback', 'Ubicacion disponible', 'Location available'),
  ];
@endphp

@section('title', $pageTitle)

@section('content')
  <div class="pt-24">
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold" style="background-color: var(--fe-properties-badge_bg, #eef2ff); color: var(--fe-properties-badge_text, #D1A054);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            {{ $favoritesLabels['badge'] }}
          </div>

          <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
            {{ $favoritesLabels['titlePrefix'] }}
            <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">{{ $favoritesLabels['titleHighlight'] }}</span>
          </h1>
          <p class="mt-4 text-lg text-slate-600">{{ $favoritesLabels['subtitle'] }}</p>
        </div>
      </div>
    </section>

    <section class="py-12 lg:py-16" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="favoritesPage()" x-init="init()">
        <div class="rounded-2xl border p-4 sm:p-6 shadow-sm mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
          <p class="text-sm sm:text-base text-slate-700">
            {{ $favoritesLabels['savedCountPrefix'] }} <span class="font-bold" x-text="favoritesCount"></span> {{ $favoritesLabels['savedCountSuffix'] }}.
          </p>

          <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('public.properties.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
              {{ $favoritesLabels['explore'] }}
            </a>
            <button type="button" @click="clearFavorites()" x-show="favoritesCount > 0" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition">
              {{ $favoritesLabels['clear'] }}
            </button>
          </div>
        </div>

        <div id="favoritesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"></div>

        <div id="favoritesEmpty" class="hidden text-center py-16">
          <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">{{ $favoritesLabels['emptyTitle'] }}</h3>
          <p class="text-slate-600 mb-6">{{ $favoritesLabels['emptySubtitle'] }}</p>
          <a href="{{ route('public.properties.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
            {{ $favoritesLabels['explore'] }}
          </a>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    const tPublic = (key, fallback = '') => (window.publicT ? window.publicT(key, fallback) : fallback);
    const isEnLocale = (window.__PUBLIC_LOCALE__ || 'es') === 'en';
    const FAVORITES_LABELS = @json($favoritesLabels);

    function favoritesPage() {
      return {
        favoritesCount: 0,
        favoriteIds: [],
        requestNonce: 0,

        init() {
          this.loadFromStorage();
          this.renderFavorites();
          window.addEventListener('public:favorites-changed', () => {
            this.loadFromStorage();
            this.renderFavorites();
          });
        },

        loadFromStorage() {
          this.favoriteIds = window.publicFavorites?.getIds() || [];
          this.favoritesCount = this.favoriteIds.length;
        },

        clearFavorites() {
          window.publicFavorites?.setIds([]);
        },

        sameIds(a, b) {
          if (a.length !== b.length) return false;
          for (let i = 0; i < a.length; i += 1) {
            if (Number(a[i]) !== Number(b[i])) return false;
          }
          return true;
        },

        escapeHtml(value) {
          return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
        },

        operationLabel(op) {
          const type = String(op || '').toLowerCase();
          if (['sale', 'venta'].includes(type)) return tPublic('common.sale', isEnLocale ? 'For sale' : 'En venta');
          if (['rent', 'rental', 'renta', 'arriendo'].includes(type)) return tPublic('common.rent', isEnLocale ? 'For rent' : 'En renta');
          return this.escapeHtml(type || (isEnLocale ? 'Available' : 'Disponible'));
        },

        createPropertyCard(property) {
          const imageUrl = property.cover_media_asset?.serving_url || property.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
          const price = property.operations?.[0]?.formatted_amount || FAVORITES_LABELS.askPrice || tPublic('common.consultPrice', isEnLocale ? 'Ask for price' : 'Consultar precio');
          const operationType = property.operations?.[0]?.operation_type || '';
          const location = [property.location?.city, property.location?.city_area].filter(Boolean).join(', ') || FAVORITES_LABELS.locationFallback || tPublic('common.locationAvailable', isEnLocale ? 'Location available' : 'Ubicacion disponible');
          const title = property.title || tPublic('common.available', isEnLocale ? 'Available property' : 'Propiedad disponible');

          return `
            <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
              <div class="relative h-56 overflow-hidden">
                <img src="${this.escapeHtml(imageUrl)}" alt="${this.escapeHtml(title)}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                ${property.property_type_name ? `
                  <span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-semibold rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #1C1C1C);">
                    ${this.escapeHtml(property.property_type_name)}
                  </span>
                ` : ''}

                ${operationType ? `
                  <span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${operationType === 'sale' ? 'var(--fe-properties-sale_badge, #768D59)' : 'var(--fe-properties-rent_badge, #D1A054)'};">
                    ${this.operationLabel(operationType)}
                  </span>
                ` : ''}

                <button type="button" data-favorite-btn data-property-id="${this.escapeHtml(property.id)}" class="absolute bottom-4 right-4 w-10 h-10 rounded-full backdrop-blur-sm flex items-center justify-center transition-colors border border-slate-200" style="background-color: var(--fe-properties-fav_btn_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-fav_btn_icon, #5B5B5B);">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>

              <div class="p-6">
                <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--fe-properties-card_location, #64748b);">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  ${this.escapeHtml(location)}
                </div>

                <h3 class="text-lg font-bold mb-3 line-clamp-2" style="color: var(--fe-properties-card_title, #1C1C1C);">
                  ${this.escapeHtml(title)}
                </h3>

                <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                  ${this.escapeHtml(price)}
                </div>

                <a href="/propiedades/${this.escapeHtml(property.id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                  ${tPublic('common.details', isEnLocale ? 'View details' : 'Ver detalles')}
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                  </svg>
                </a>
              </div>
            </div>
          `;
        },

        async fetchProperty(id) {
          try {
            const response = await fetch(`/api/public/properties/${id}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok || !data?.success || !data?.data?.id) return null;
            return data.data;
          } catch (_error) {
            return null;
          }
        },

        async renderFavorites() {
          const grid = document.getElementById('favoritesGrid');
          const empty = document.getElementById('favoritesEmpty');

          if (!this.favoriteIds.length) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
          }

          empty.classList.add('hidden');
          grid.innerHTML = Array.from({ length: Math.min(6, this.favoriteIds.length) }).map(() => `
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
              <div class="skeleton h-56 w-full"></div>
              <div class="p-6 space-y-4">
                <div class="skeleton h-4 w-3/4 rounded"></div>
                <div class="skeleton h-6 w-full rounded"></div>
                <div class="skeleton h-4 w-1/2 rounded"></div>
              </div>
            </div>
          `).join('');

          const currentNonce = ++this.requestNonce;
          const fetched = await Promise.all(this.favoriteIds.map((id) => this.fetchProperty(id)));
          if (currentNonce !== this.requestNonce) return;

          const byId = new Map();
          fetched.forEach((property) => {
            if (!property?.id) return;
            byId.set(Number(property.id), property);
          });

          const validIds = this.favoriteIds.filter((id) => byId.has(Number(id)));
          if (!this.sameIds(validIds, this.favoriteIds)) {
            window.publicFavorites?.setIds(validIds);
            return;
          }

          const orderedProperties = validIds.map((id) => byId.get(Number(id))).filter(Boolean);
          this.favoritesCount = orderedProperties.length;

          if (!orderedProperties.length) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
          }

          grid.innerHTML = orderedProperties.map((property) => this.createPropertyCard(property)).join('');
          window.publicFavorites?.syncButtons(grid);
        },
      };
    }
  </script>
@endpush
