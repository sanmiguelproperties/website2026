@extends('layouts.public')

@php
  $isEn = ($locale ?? app()->getLocale()) === 'en';
  $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
  $pageTitle = $pageData?->entity?->title($locale ?? app()->getLocale()) ?? ($isEn ? 'Agency' : 'Agencia');
  $officeLabels = [
    'home' => $txt('i18n_breadcrumb_home', 'Inicio', 'Home'),
    'agency' => $txt('i18n_label_agency', 'Agencia', 'Agency'),
    'agencies' => $txt('i18n_breadcrumb_agencies', 'Agencias', 'Agencies'),
    'agents' => $txt('agents_title', 'Agentes', 'Agents'),
    'properties' => $txt('i18n_label_properties', 'Propiedades', 'Properties'),
    'phone' => $txt('i18n_label_phone', 'TelÃ©fono', 'Phone'),
    'description' => $txt('i18n_label_description', 'DescripciÃ³n', 'Description'),
    'contact' => $txt('i18n_label_contact', 'Contacto', 'Contact'),
    'paid' => $txt('i18n_label_paid', 'Pagado', 'Paid'),
    'locationAvailable' => $txt('i18n_common_locationAvailable', 'UbicaciÃ³n disponible', 'Location available'),
    'loading' => $txt('i18n_label_loading', 'Cargando...', 'Loading...'),
    'website' => $txt('i18n_label_website', 'Sitio web', 'Website'),
    'search' => $txt('i18n_label_search', 'Buscar', 'Search'),
    'searchPlaceholder' => $txt('search_placeholder', 'Buscar por ciudad, zona, tipo...', 'Search by city, area, type...'),
    'searchAgentPlaceholder' => $txt('i18n_label_searchAgentPlaceholder', 'Buscar agente...', 'Search agent...'),
    'clearFilters' => $txt('i18n_label_clearFilters', 'Limpiar filtros', 'Clear filters'),
    'previous' => $txt('i18n_label_previous', 'Anterior', 'Previous'),
    'next' => $txt('i18n_label_next', 'Siguiente', 'Next'),
    'page' => $txt('i18n_label_page', 'PÃ¡gina', 'Page'),
    'of' => $txt('i18n_label_of', 'de', 'of'),
    'showing' => $txt('i18n_label_showing', 'Mostrando', 'Showing'),
    'noAgents' => $txt('i18n_label_noAgents', 'No hay agentes para esta agencia.', 'No agents in this agency.'),
    'noProperties' => $txt('i18n_label_noProperties', 'No se encontraron propiedades', 'No properties found'),
    'noPropertiesHelp' => $txt('i18n_label_noPropertiesHelp', 'Intenta ajustar tu bÃºsqueda.', 'Try adjusting your search.'),
    'propertiesTitle' => $txt('properties_title', 'Propiedades de la agencia', 'Agency properties'),
    'propertiesSubtitle' => $txt('properties_subtitle', 'Busca y navega propiedades vinculadas a esta agencia.', 'Search and browse properties linked to this agency.'),
  ];
@endphp

@section('title', $pageTitle)

@section('content')
  <div class="relative overflow-hidden pt-24 pb-16" x-data="officeDetailPage()" x-init="init()">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition" x-text="labels.home"></a>
        <span class="text-slate-400">/</span>
        <a href="{{ route('public.mls-offices.index') }}" class="text-slate-600 hover:text-slate-900 transition" x-text="labels.agencies"></a>
        <span class="text-slate-400">/</span>
        <span class="text-slate-900 font-medium truncate" x-text="office?.name || (labels.agency + ' #' + officeId)"></span>
      </nav>

      <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <section class="lg:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
          <div class="relative h-72 sm:h-80 bg-slate-100">
            <template x-if="coverUrl">
              <img :src="coverUrl" :alt="office?.name || labels.agency" class="w-full h-full object-cover" loading="lazy" x-on:error="coverUrl = fallbackCover" />
            </template>
            <template x-if="!coverUrl">
              <div class="h-full w-full skeleton"></div>
            </template>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 text-white">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(15,23,42,.55);">MLS #<span x-text="officeId"></span></span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(255,255,255,0.85); color: #1C1C1C;" x-show="office?.paid" x-text="labels.paid"></span>
              </div>

              <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight" x-text="office?.name || labels.loading"></h1>

              <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4 text-white/90">
                <div class="inline-flex items-center gap-2" x-text="officeLocation || labels.locationAvailable"></div>
                <div class="hidden sm:block text-white/40">â€¢</div>
                <div class="inline-flex items-center gap-2" x-show="office?.email" x-text="office?.email"></div>
              </div>
            </div>
          </div>

          <div class="p-6 sm:p-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600" x-text="labels.agents"></p>
                <p class="mt-1 text-lg font-extrabold text-slate-900" x-text="office?.agents_count ?? 'â€”'"></p>
              </div>
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600" x-text="labels.properties"></p>
                <p class="mt-1 text-lg font-extrabold text-slate-900" x-text="office?.properties_count ?? 'â€”'"></p>
              </div>
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600" x-text="labels.phone"></p>
                <p class="mt-1 text-sm font-semibold text-slate-900 truncate" x-text="officePhone || 'â€”'"></p>
              </div>
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600" x-text="labels.website"></p>
                <a class="mt-1 text-sm font-semibold text-slate-900 truncate block hover:underline" :href="office?.website || '#'" target="_blank" rel="noopener" x-text="office?.website ? office.website.replace(/^https?:\/\//,'') : 'â€”'"></a>
              </div>
            </div>

            <div class="mt-6" x-show="officeDescription">
              <h2 class="text-lg font-bold text-slate-900" x-text="labels.description"></h2>
              <p class="mt-3 text-slate-700 leading-relaxed whitespace-pre-line" x-text="officeDescription"></p>
            </div>
          </div>
        </section>

        <aside class="lg:col-span-4 space-y-6">
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
            <div class="flex items-center justify-between gap-3">
              <h3 class="text-sm font-semibold text-slate-900" x-text="labels.agents"></h3>
              <span class="text-xs font-semibold text-slate-600" x-text="agentsPagination ? (labels.page + ' ' + agentsPagination.current_page + ' / ' + agentsPagination.last_page) : ''"></span>
            </div>

            <div class="mt-4">
              <input type="text" x-model="agentsFilters.search" @input.debounce.300ms="applyAgentsFilters()"
                     :placeholder="labels.searchAgentPlaceholder"
                     class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none">
            </div>

            <div class="mt-5 space-y-3">
              <template x-if="agentsLoading">
                <div class="space-y-3">
                  <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4"><div class="h-4 w-40 skeleton rounded"></div><div class="mt-2 h-3 w-28 skeleton rounded"></div></div>
                  <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4"><div class="h-4 w-44 skeleton rounded"></div><div class="mt-2 h-3 w-24 skeleton rounded"></div></div>
                </div>
              </template>

              <template x-if="!agentsLoading && agents.length === 0">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700" x-text="labels.noAgents"></div>
              </template>

              <template x-for="a in agents" :key="a.mls_agent_id">
                <a :href="'/agentes/' + a.mls_agent_id" class="block rounded-2xl border border-slate-100 bg-slate-50 p-4 hover:bg-slate-100 transition">
                  <div class="flex items-start gap-4">
                    <div class="size-12 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">
                      <template x-if="a.photo"><img :src="a.photo" :alt="a.full_name" class="w-full h-full object-cover" loading="lazy" x-on:error="a.photo = null" /></template>
                      <template x-if="!a.photo"><span class="text-slate-400 text-xs" x-text="tPublic('mls.office.imagePlaceholder', isEnLocale ? 'IMG' : 'IMG')"></span></template>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-bold text-slate-900 truncate" x-text="a.full_name"></p>
                      <p class="mt-0.5 text-xs text-slate-600 truncate" x-text="a.email || 'â€”'"></p>
                    </div>
                  </div>
                </a>
              </template>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
              <button @click="goAgentsPage((agentsPagination?.current_page || 1) - 1)" :disabled="!(agentsPagination?.current_page > 1)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.previous"></button>
              <button @click="goAgentsPage((agentsPagination?.current_page || 1) + 1)" :disabled="!(agentsPagination?.current_page < agentsPagination?.last_page)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.next"></button>
            </div>
          </section>
        </aside>
      </div>

      <section class="mt-10 rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden" x-data="officeProperties()" x-init="init(window.__OFFICE_ID__)">
        <div class="p-6 sm:p-8 border-b border-slate-100">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-extrabold text-slate-900" x-text="labels.propertiesTitle"></h2>
              <p class="mt-1 text-sm text-slate-600" x-text="labels.propertiesSubtitle"></p>
            </div>
            <div class="text-sm text-slate-600">
              <span x-text="labels.showing"></span> <span x-text="pagination?.from || 0"></span> - <span x-text="pagination?.to || 0"></span>
              <span x-text="labels.of"></span> <span x-text="pagination?.total || 0"></span>
            </div>
          </div>
        </div>

        <div class="p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end mb-8">
            <div class="flex-1">
              <label class="block text-xs font-semibold text-slate-600 mb-2" x-text="labels.search"></label>
              <input type="text" x-model="filters.search" @input.debounce.300ms="applyFilters()" :placeholder="labels.searchPlaceholder" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none">
            </div>
            <button @click="clearFilters()" x-show="hasFilters()" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition" x-text="labels.clearFilters"></button>
          </div>

          <div id="officePropertiesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"></div>

          <div id="officePropertiesEmpty" class="hidden text-center py-16">
            <h3 class="text-xl font-semibold text-slate-900 mb-2" x-text="labels.noProperties"></h3>
            <p class="text-slate-600 mb-6" x-text="labels.noPropertiesHelp"></p>
          </div>

          <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
            <button @click="goToPage((pagination?.current_page || 1) - 1)" :disabled="!(pagination?.current_page > 1)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.previous"></button>
            <div class="text-sm text-slate-600"><span x-text="labels.page"></span> <span x-text="pagination?.current_page || 1"></span> <span x-text="labels.of"></span> <span x-text="pagination?.last_page || 1"></span></div>
            <button @click="goToPage((pagination?.current_page || 1) + 1)" :disabled="!(pagination?.current_page < pagination?.last_page)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.next"></button>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    const tPublic = (key, fallback = '') => (window.publicT ? window.publicT(key, fallback) : fallback);
    const isEnLocale = (window.__PUBLIC_LOCALE__ || 'es') === 'en';
    window.__OFFICE_ID__ = @json($mlsOfficeId ?? null);

    const OFFICE_LABELS = @json($officeLabels);

    function officeDetailPage() {
      return {
        labels: OFFICE_LABELS,
        officeId: window.__OFFICE_ID__,
        office: null,
        coverUrl: null,
        fallbackCover: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',

        agents: [],
        agentsLoading: true,
        agentsPagination: null,
        agentsFilters: { search: '', per_page: 8, page: 1, order: 'name', sort: 'asc' },

        get officeLocation() {
          return [this.office?.city, this.office?.state_province].filter(Boolean).join(', ');
        },

        get officePhone() {
          return this.office?.phone_1 || this.office?.phone_2 || this.office?.phone_3 || '';
        },

        get officeDescription() {
          return (this.office?.description || this.office?.description_es || '').toString().trim();
        },

        async init() {
          if (!this.officeId) return;
          await this.loadOffice();
          await this.loadAgents();
        },

        async loadOffice() {
          try {
            const res = await fetch(`/api/public/mls-offices/${this.officeId}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data?.success) return;
            this.office = data.data;
            this.coverUrl = this.office?.image || this.office?.image_url || this.fallbackCover;
            document.title = `${this.office?.name || this.labels.agency} | ${tPublic('common.siteName', 'San Miguel Properties')}`;
          } catch (_e) {
            // noop
          }
        },

        async loadAgents() {
          this.agentsLoading = true;
          try {
            const p = new URLSearchParams();
            Object.keys(this.agentsFilters).forEach((k) => {
              const v = this.agentsFilters[k];
              if (v === '' || v === null || v === undefined) return;
              p.set(k, String(v));
            });

            const res = await fetch(`/api/public/mls-offices/${this.officeId}/agents?${p.toString()}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data?.success) {
              this.agents = [];
              this.agentsPagination = null;
              return;
            }

            const pag = data.data;
            this.agents = pag?.data || [];
            this.agentsPagination = {
              current_page: pag?.current_page || 1,
              last_page: pag?.last_page || 1,
              from: pag?.from || 0,
              to: pag?.to || 0,
              total: pag?.total || 0,
            };
          } catch (_e) {
            this.agents = [];
            this.agentsPagination = null;
          } finally {
            this.agentsLoading = false;
          }
        },

        applyAgentsFilters() {
          this.agentsFilters.page = 1;
          this.loadAgents();
        },

        goAgentsPage(page) {
          if (!this.agentsPagination) return;
          if (page < 1 || page > this.agentsPagination.last_page) return;
          this.agentsFilters.page = page;
          this.loadAgents();
        },
      };
    }

    function officeProperties() {
      return {
        labels: OFFICE_LABELS,
        filters: {
          mls_office_id: null,
          search: '',
          page: 1,
          per_page: 9,
          order: 'updated_at',
          sort: 'desc',
        },
        pagination: null,
        properties: [],

        init(officeId) {
          this.filters.mls_office_id = officeId || window.__OFFICE_ID__;
          this.loadProperties();
        },

        hasFilters() {
          return !!this.filters.search;
        },

        applyFilters() {
          this.filters.page = 1;
          this.loadProperties();
        },

        clearFilters() {
          this.filters.search = '';
          this.filters.page = 1;
          this.loadProperties();
        },

        goToPage(page) {
          if (!this.pagination) return;
          if (page < 1 || page > this.pagination.last_page) return;
          this.filters.page = page;
          this.loadProperties();
        },

        async loadProperties() {
          const grid = document.getElementById('officePropertiesGrid');
          const empty = document.getElementById('officePropertiesEmpty');

          grid.innerHTML = Array.from({ length: 6 }).map(() => `
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
              <div class="skeleton h-52 w-full"></div>
              <div class="p-6 space-y-3">
                <div class="skeleton h-4 w-3/4 rounded"></div>
                <div class="skeleton h-4 w-1/2 rounded"></div>
                <div class="skeleton h-10 rounded-xl"></div>
              </div>
            </div>
          `).join('');

          try {
            const params = new URLSearchParams();
            Object.keys(this.filters).forEach((k) => {
              const v = this.filters[k];
              if (v === '' || v === null || v === undefined) return;
              params.set(k, String(v));
            });

            const res = await fetch(`/api/public/properties?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();

            if (!res.ok || !data?.success) {
              grid.innerHTML = '';
              empty.classList.remove('hidden');
              return;
            }

            this.properties = data.data?.data || [];
            this.pagination = {
              current_page: data.data?.current_page || 1,
              last_page: data.data?.last_page || 1,
              from: data.data?.from || 0,
              to: data.data?.to || 0,
              total: data.data?.total || 0,
            };

            if (!this.properties.length) {
              grid.innerHTML = '';
              empty.classList.remove('hidden');
              return;
            }
            empty.classList.add('hidden');

            const esc = (s) => String(s ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'", '&#039;');

            grid.innerHTML = this.properties.map((p) => {
              const imageUrl = p.cover_media_asset?.serving_url || p.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
              const price = p.operations?.[0]?.formatted_amount || tPublic('common.consultPrice', isEnLocale ? 'Ask for price' : 'Consultar precio');
              const op = p.operations?.[0]?.operation_type || '';
              const location = [p.location?.city, p.location?.city_area].filter(Boolean).join(', ') || tPublic('common.locationAvailable', isEnLocale ? 'Location available' : 'Ubicacion disponible');

              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="relative h-52 overflow-hidden">
                    <img src="${esc(imageUrl)}" alt="${esc(p.title || tPublic('common.properties', isEnLocale ? 'Property' : 'Propiedad'))}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';" />
                    ${op ? `<span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color:${op === 'sale' ? 'var(--fe-properties-sale_badge, #768D59)' : 'var(--fe-properties-rent_badge, #D1A054)'};">${op === 'sale' ? tPublic('common.sale', isEnLocale ? 'For sale' : 'En venta') : tPublic('common.rent', isEnLocale ? 'For rent' : 'En renta')}</span>` : ''}
                  </div>
                  <div class="p-6">
                    <div class="text-sm text-slate-600 mb-2">${esc(location)}</div>
                    <h3 class="text-lg font-bold mb-3 line-clamp-2 text-slate-900">${esc(p.title || tPublic('common.available', isEnLocale ? 'Available property' : 'Propiedad disponible'))}</h3>
                    <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">${esc(price)}</div>
                    <a href="/propiedades/${esc(p.id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">${tPublic('common.details', isEnLocale ? 'View details' : 'Ver detalles')}</a>
                  </div>
                </div>
              `;
            }).join('');
          } catch (_e) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
          }
        },
      };
    }
  </script>
@endpush






