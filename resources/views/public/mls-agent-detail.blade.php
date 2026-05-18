@extends('layouts.public')

@php
  $isEn = ($locale ?? app()->getLocale()) === 'en';
  $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
  $pageTitle = $pageData?->entity?->title($locale ?? app()->getLocale()) ?? ($isEn ? 'Agent' : 'Agente');
  $agencyContactSettings = $settings ?? [];
  $agencyContact = [
    'phone' => $agencyContactSettings['contact_phone'] ?? '+52 55 1234 5678',
    'whatsapp' => preg_replace('/[^0-9]/', '', (string) ($agencyContactSettings['contact_whatsapp'] ?? $agencyContactSettings['contact_phone'] ?? '525512345678')),
    'email' => $agencyContactSettings['contact_email'] ?? 'info@sanmiguelproperties.com',
  ];
  $agentLabels = [
    'home' => $txt('i18n_breadcrumb_home', 'Inicio', 'Home'),
    'agents' => $txt('i18n_breadcrumb_agents', 'Agentes', 'Agents'),
    'agent' => $txt('i18n_label_agent', 'Agente', 'Agent'),
    'agency' => $txt('i18n_label_agency', 'Agencia', 'Agency'),
    'contactAgency' => $txt('i18n_label_contactAgency', 'Contactar agencia', 'Contact agency'),
    'contact' => $txt('i18n_label_contactAgent', 'Contactar agente', 'Contact agent'),
    'sendEmail' => $txt('i18n_label_sendEmail', 'Enviar correo', 'Send email'),
    'agencyWhatsapp' => $txt('i18n_label_agencyWhatsapp', 'WhatsApp agencia', 'Agency WhatsApp'),
    'agentWhatsapp' => $txt('i18n_label_agentWhatsapp', 'WhatsApp agente', 'Agent WhatsApp'),
    'agencyEmail' => $txt('i18n_label_agencyEmail', 'Enviar correo a agencia', 'Email agency'),
    'propertiesTitle' => $txt('properties_title', 'Propiedades del agente', 'Agent properties'),
    'propertiesSubtitle' => $txt('properties_subtitle', 'Busca y navega propiedades vinculadas a este agente.', 'Search and browse properties linked to this agent.'),
    'showing' => $txt('i18n_label_showing', 'Mostrando', 'Showing'),
    'of' => $txt('i18n_label_of', 'de', 'of'),
    'search' => $txt('i18n_label_search', 'Buscar', 'Search'),
    'searchPlaceholder' => $txt('search_placeholder', 'Buscar por ciudad, zona, tipo...', 'Search by city, area, type...'),
    'clearFilters' => $txt('i18n_label_clearFilters', 'Limpiar filtros', 'Clear filters'),
    'noProperties' => $txt('i18n_label_noProperties', 'No se encontraron propiedades', 'No properties found'),
    'noPropertiesHelp' => $txt('i18n_label_noPropertiesHelp', 'Intenta ajustar tu búsqueda.', 'Try adjusting your search terms.'),
    'previous' => $txt('i18n_label_previous', 'Anterior', 'Previous'),
    'next' => $txt('i18n_label_next', 'Siguiente', 'Next'),
    'page' => $txt('i18n_label_page', 'Página', 'Page'),
    'loading' => $txt('i18n_label_loading', 'Cargando...', 'Loading...'),
    'bio' => $txt('i18n_label_bio', 'Bio', 'Bio'),
  ];
@endphp

@section('title', $pageTitle)

@section('content')
  <div class="relative overflow-hidden pt-24 pb-16" x-data="agentDetailPage()" x-init="init()">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition" x-text="labels.home"></a>
        <span class="text-slate-400">/</span>
        <a href="{{ route('public.mls-agents.index') }}" class="text-slate-600 hover:text-slate-900 transition" x-text="labels.agents"></a>
        <span class="text-slate-400">/</span>
        <span class="text-slate-900 font-medium truncate" x-text="agent?.full_name || (labels.agent + ' #' + agentId)"></span>
      </nav>

      <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <section class="lg:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
          <div class="relative h-96 sm:h-[28rem] lg:h-[34rem] bg-slate-100">
            <template x-if="coverUrl">
              <img :src="coverUrl" :alt="agent?.full_name || labels.agent" class="w-full h-full object-cover" style="object-position: center 20%;" loading="lazy" x-on:error="coverUrl = fallbackCover" />
            </template>
            <template x-if="!coverUrl">
              <div class="h-full w-full skeleton"></div>
            </template>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 text-white">
              <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight" x-text="agent?.full_name || (labels.loading)"></h1>
            </div>
          </div>

          <div class="p-6 sm:p-8" x-show="agentBio">
            <h2 class="text-lg font-bold text-slate-900" x-text="labels.bio"></h2>
            <div class="mt-3 text-slate-700 leading-relaxed rich-content" x-html="agentBioHtml"></div>
          </div>
        </section>

        <aside class="lg:col-span-4 space-y-6">
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
            <h3 class="text-sm font-semibold text-slate-900" x-text="labels.contact"></h3>
            <div class="mt-4 space-y-3">
              <a class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg"
                 :href="agent?.email ? ('mailto:' + agent.email) : '#'"
                 :class="agent?.email ? '' : 'opacity-50 pointer-events-none'"
                 style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span x-text="labels.sendEmail"></span>
              </a>
              <a x-show="agentWhatsappUrl"
                 :href="agentWhatsappUrl"
                 target="_blank"
                 rel="noopener"
                 class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 transition hover:bg-emerald-100">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span x-text="labels.agentWhatsapp"></span>
              </a>
            </div>
          </section>

          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6" x-show="office">
            <h3 class="text-sm font-semibold text-slate-900" x-text="labels.contactAgency"></h3>

            <div class="mt-4 flex gap-4">
              <div class="size-14 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">
                <template x-if="office?.image">
                  <img :src="office.image" :alt="office?.name || labels.agency" class="w-full h-full object-cover" loading="lazy" x-on:error="office.image = null" />
                </template>
                <template x-if="!office?.image">
                  <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 21V7l8-4v18" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V11l-6-4" />
                  </svg>
                </template>
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-bold text-slate-900 truncate" x-text="office?.name || (labels.agency + ' #' + office?.mls_office_id)"></p>
                <p class="mt-0.5 text-xs text-slate-600 truncate" x-text="officeLocation"></p>
              </div>
            </div>

            <div class="mt-5 grid gap-3">
              <a x-show="agencyWhatsappUrl"
                 :href="agencyWhatsappUrl"
                 target="_blank"
                 rel="noopener"
                 class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg"
                 style="background-color: #22c55e;">
                <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span x-text="labels.agencyWhatsapp"></span>
              </a>

              <a x-show="agencyEmailUrl"
                 :href="agencyEmailUrl"
                 class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                <svg class="h-5 w-5 shrink-0 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span x-text="labels.agencyEmail"></span>
              </a>

            </div>
          </section>
        </aside>
      </div>

      <section class="mt-10 rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden" x-data="agentProperties()" x-init="init(window.__AGENT_ID__)">
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
              <input type="text" x-model="filters.search" @input.debounce.300ms="applyFilters()"
                     :placeholder="labels.searchPlaceholder"
                     class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none">
            </div>
            <button @click="clearFilters()" x-show="hasFilters()"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
                    x-text="labels.clearFilters"></button>
          </div>

          <div id="agentPropertiesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"></div>

          <div id="agentPropertiesEmpty" class="hidden text-center py-16">
            <h3 class="text-xl font-semibold text-slate-900 mb-2" x-text="labels.noProperties"></h3>
            <p class="text-slate-600 mb-6" x-text="labels.noPropertiesHelp"></p>
          </div>

          <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
            <button @click="goToPage((pagination?.current_page || 1) - 1)" :disabled="!(pagination?.current_page > 1)"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.previous"></button>
            <div class="text-sm text-slate-600"><span x-text="labels.page"></span> <span x-text="pagination?.current_page || 1"></span> <span x-text="labels.of"></span> <span x-text="pagination?.last_page || 1"></span></div>
            <button @click="goToPage((pagination?.current_page || 1) + 1)" :disabled="!(pagination?.current_page < pagination?.last_page)"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition" x-text="labels.next"></button>
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
    window.__AGENT_ID__ = @json($mlsAgentId ?? null);

    const AGENT_LABELS = @json($agentLabels);
    const AGENCY_CONTACT = @json($agencyContact);
    const propertyCardI18n = {
      heroImageAlt: tPublic('home.hero.imageAlt', isEnLocale ? 'Property' : 'Propiedad'),
      priceFallback: tPublic('home.property.priceFallback', isEnLocale ? 'Ask for price' : 'Consultar precio'),
      locationFallback: tPublic('home.property.locationFallback', isEnLocale ? 'Location available' : 'Ubicacion disponible'),
      cardTitleFallback: tPublic('home.property.cardTitleFallback', isEnLocale ? 'Available property' : 'Propiedad disponible'),
      saleLabel: tPublic('common.sale', isEnLocale ? 'For sale' : 'En venta'),
      rentLabel: tPublic('common.rent', isEnLocale ? 'For rent' : 'En renta'),
      lotSizeLabel: tPublic('property.lotSize', isEnLocale ? 'Lot size' : 'M2 de terreno'),
      constructionSizeLabel: tPublic('property.constructionSize', isEnLocale ? 'Construction size' : 'M2 de construccion'),
      roomsLabel: tPublic('property.rooms', isEnLocale ? 'Rooms' : 'Cuartos'),
      bathroomsLabel: tPublic('property.bathrooms', isEnLocale ? 'Baths' : 'Banos'),
      halfBathroomsLabel: tPublic('property.halfBathrooms', isEnLocale ? 'Half baths' : 'Medios banos'),
      detailsCta: tPublic('common.details', isEnLocale ? 'View details' : 'Ver detalles'),
      areaUnit: tPublic('home.property.areaUnit', isEnLocale ? 'sqm' : 'm2'),
    };
    const propertyIconUrls = {
      location: @json(asset('iconos-base/ubicacion.svg')),
      bedrooms: @json(asset('iconos-base/recamaras.svg')),
      bathrooms: @json(asset('iconos-base/banos.svg')),
      halfBathrooms: @json(asset('iconos-base/medio-bano.svg')),
      lot: @json(asset('iconos-base/area.svg')),
      construction: @json(asset('iconos-base/construccion.svg')),
    };

    function propertyIcon(name, className = 'w-4 h-4 shrink-0 overflow-visible') {
      const src = propertyIconUrls[name];
      return src ? `<img src="${src}" alt="" aria-hidden="true" class="${className} inline-block object-contain opacity-75">` : '';
    }

    const wholeNumberFormatter = new Intl.NumberFormat(isEnLocale ? 'en-US' : 'es-MX', {
      maximumFractionDigits: 0,
    });

    const featureNumberFormatter = new Intl.NumberFormat(isEnLocale ? 'en-US' : 'es-MX', {
      maximumFractionDigits: 1,
    });

    function cardNumberValue(value) {
      const text = String(value ?? '').trim();
      if (text === '') return null;
      const number = Number(text);
      return Number.isFinite(number) && number > 0 ? featureNumberFormatter.format(number) : null;
    }

    function cardAreaValue(value) {
      const number = Number(value);
      return Number.isFinite(number) && number > 0 ? `${wholeNumberFormatter.format(number)} ${propertyCardI18n.areaUnit}` : null;
    }

    function cardPriceValue(value) {
      const text = String(value ?? '').trim();
      return text ? text.replace(/([.,]\d{1,2})(?=\s*(?:[A-Z]{3})?$)/, '') : propertyCardI18n.priceFallback;
    }

    function agentDetailPage() {
      return {
        labels: AGENT_LABELS,
        agentId: window.__AGENT_ID__,
        agent: null,
        office: null,
        coverUrl: null,
        fallbackCover: 'https://images.unsplash.com/photo-1521791055366-0d553872125f?auto=format&fit=crop&w=2070&q=80',

        get officeLocation() {
          const o = this.office;
          return [o?.city, o?.state_province].filter(Boolean).join(', ');
        },

        get agencyWhatsappUrl() {
          const agencyName = this.office?.name || tPublic('common.siteName', 'San Miguel Properties');
          const agentName = this.agent?.full_name || this.labels.agent;
          const message = isEnLocale
            ? `Hi, I would like to contact ${agencyName} about ${agentName}.`
            : `Hola, me gustaria contactar a ${agencyName} sobre ${agentName}.`;

          return this.whatsappUrl(AGENCY_CONTACT.whatsapp || AGENCY_CONTACT.phone, message);
        },

        get agencyEmail() {
          return this.sanitizeEmail(this.office?.email || AGENCY_CONTACT.email || '');
        },

        get agencyEmailUrl() {
          return this.agencyEmail ? `mailto:${this.agencyEmail}` : '';
        },

        get agentWhatsappUrl() {
          const agentPhone = this.agent?.mobile || this.agent?.phone || '';
          const agentName = this.agent?.full_name || this.labels.agent;
          const message = isEnLocale
            ? `Hi ${agentName}, I would like more information.`
            : `Hola ${agentName}, me gustaria recibir mas informacion.`;

          return this.whatsappUrl(agentPhone, message);
        },

        get agentBio() {
          const a = this.agent;
          return (a?.bio || a?.bio_es || '').toString().trim();
        },

        get agentBioHtml() {
          if (typeof window.publicSanitizeRichHtml === 'function') {
            return window.publicSanitizeRichHtml(this.agentBio, '');
          }
          return this.agentBio;
        },

        normalizeWhatsappNumber(value) {
          let digits = String(value || '').replace(/\D/g, '');
          if (digits.length === 10) digits = `52${digits}`;
          return digits;
        },

        whatsappUrl(value, message) {
          const number = this.normalizeWhatsappNumber(value);
          return number ? `https://wa.me/${number}?text=${encodeURIComponent(message)}` : '';
        },

        sanitizeEmail(value) {
          return String(value || '').replace(/[\r\n]/g, '').trim();
        },

        async init() {
          if (!this.agentId) return;
          try {
            const res = await fetch(`/api/public/mls-agents/${this.agentId}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data?.success) return;

            this.agent = data.data?.agent || null;
            this.office = data.data?.office || this.agent?.office || null;
            this.coverUrl = this.agent?.photo || this.fallbackCover;
            document.title = `${this.agent?.full_name || this.labels.agent} | ${tPublic('common.siteName', 'San Miguel Properties')}`;
          } catch (_e) {
            // noop
          }
        },
      };
    }

    function agentProperties() {
      return {
        labels: AGENT_LABELS,
        filters: {
          mls_agent_id: null,
          search: '',
          page: 1,
          per_page: 9,
          order: 'updated_at',
          sort: 'desc',
        },
        pagination: null,
        properties: [],

        init(agentId) {
          this.filters.mls_agent_id = agentId || window.__AGENT_ID__;
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
          const grid = document.getElementById('agentPropertiesGrid');
          const empty = document.getElementById('agentPropertiesEmpty');

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
              const firstOperation = p.operations?.[0] || null;
              const fallbackAmount = (typeof window.formatDisplayPrice === 'function')
                ? window.formatDisplayPrice(firstOperation?.amount, firstOperation?.currency?.code || firstOperation?.currency_code)
                : '';
              const price = cardPriceValue(firstOperation?.formatted_amount || fallbackAmount || propertyCardI18n.priceFallback);
              const op = p.operations?.[0]?.operation_type || '';
              const location = p.location?.city_area
                || p.location?.city
                || propertyCardI18n.locationFallback;
              const cardDetails = [
                { icon: 'lot', label: propertyCardI18n.lotSizeLabel, value: cardAreaValue(p.lot_size) },
                { icon: 'construction', label: propertyCardI18n.constructionSizeLabel, value: cardAreaValue(p.construction_size) },
                { icon: 'bedrooms', label: propertyCardI18n.roomsLabel, value: cardNumberValue(p.bedrooms) },
                { icon: 'bathrooms', label: propertyCardI18n.bathroomsLabel, value: cardNumberValue(p.bathrooms) },
                { icon: 'halfBathrooms', label: propertyCardI18n.halfBathroomsLabel, value: cardNumberValue(p.half_bathrooms) },
              ].filter((item) => item.value);

              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="relative h-56 overflow-hidden">
                    <img src="${esc(imageUrl)}" alt="${esc(p.title || propertyCardI18n.heroImageAlt)}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';" />
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="background: linear-gradient(to top, var(--fe-properties-image_overlay, rgba(0,0,0,0.5)), transparent);"></div>
                    ${p.property_type_name ? `<span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-medium rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #1C1C1C);">${esc(p.property_type_name)}</span>` : ''}
                    ${op ? `<span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${op === 'sale' ? 'var(--fe-properties-sale_badge, #768D59)' : 'var(--fe-properties-rent_badge, #D1A054)'};">${op === 'sale' ? propertyCardI18n.saleLabel : propertyCardI18n.rentLabel}</span>` : ''}
                    <button type="button" data-favorite-btn data-property-id="${esc(p.id)}" class="absolute bottom-4 right-4 w-10 h-10 rounded-full backdrop-blur-sm flex items-center justify-center transition-colors opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 duration-300 border" style="background-color: var(--fe-properties-fav_btn_bg, rgba(255,255,255,0.9)); border-color: var(--fe-properties-fav_btn_border, #e2e8f0); color: var(--fe-properties-fav_btn_icon, #5B5B5B);">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </button>
                  </div>
                  <div class="p-6">
                    <h3 class="text-lg font-bold mb-3 line-clamp-2 transition-colors" style="color: var(--fe-properties-card_title, #1C1C1C);">${esc(p.title || propertyCardI18n.cardTitleFallback)}</h3>
                    <div class="flex items-center gap-2 text-sm mb-3" style="color: var(--fe-properties-card_location, #5B5B5B);">
                      ${propertyIcon('location')}
                      <span class="truncate">${esc(location)}</span>
                    </div>
                    <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">${esc(price)}</div>
                    ${cardDetails.length ? `
                    <div class="grid grid-cols-3 gap-x-3 gap-y-3 text-sm border-t pt-4" style="color: var(--fe-properties-card_meta, #5B5B5B); border-color: var(--fe-properties-card_divider, #f1f5f9);">
                      ${cardDetails.map((item) => `
                      <div class="flex min-w-0 items-center gap-1.5" title="${esc(item.label)}" aria-label="${esc(item.label)}: ${esc(item.value)}">
                        ${propertyIcon(item.icon, 'w-8 h-8 shrink-0 overflow-visible')}
                        <span class="truncate font-semibold" style="color: var(--fe-properties-card_title, #1C1C1C);">${esc(item.value)}</span>
                      </div>
                      `).join('')}
                    </div>
                    ` : ''}
                    <div class="mt-5">
                      <a href="/propiedades/${esc(p.id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                        ${propertyCardI18n.detailsCta}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              `;
            }).join('');

            window.publicFavorites?.syncButtons(grid);
          } catch (_e) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
          }
        },
      };
    }
  </script>
@endpush




