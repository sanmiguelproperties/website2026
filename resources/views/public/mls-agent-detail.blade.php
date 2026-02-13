@extends('layouts.public')

@section('title', 'Agente')

@section('content')
  <div class="relative overflow-hidden pt-24">
    {{-- Background decor --}}
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(79,70,229,.35));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(16,185,129,.35));"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.06)_1px,transparent_0)] [background-size:28px_28px]"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16" x-data="mlsAgentDetail()" x-init="init()">
      {{-- Breadcrumbs --}}
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition">Inicio</a>
        <span class="text-slate-400">/</span>
        <a href="{{ route('public.mls-agents.index') }}" class="text-slate-600 hover:text-slate-900 transition">Agentes</a>
        <span class="text-slate-400">/</span>
        <span class="text-slate-900 font-medium truncate" x-text="agent?.full_name || ('Agente #' + agentId)">Agente</span>
      </nav>

      {{-- Header --}}
      <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <div class="lg:col-span-8">
          <div class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="relative">
              <div class="h-64 sm:h-72 lg:h-80 bg-slate-100">
                <template x-if="coverUrl">
                  <img :src="coverUrl" :alt="agent?.full_name || 'Agente'" class="w-full h-full object-cover" loading="lazy" x-on:error="coverUrl = fallbackCover" />
                </template>
                <template x-if="!coverUrl">
                  <div class="h-full w-full skeleton"></div>
                </template>
              </div>

              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

              <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                    <span class="inline-block size-1.5 rounded-full bg-white/90"></span>
                    MLS #<span x-text="agentId"></span>
                  </span>

                  <template x-if="office?.mls_office_id">
                    <a :href="'/agencias/' + office.mls_office_id" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(255,255,255,0.85); color: #0f172a;">
                      Agencia: <span class="ml-1" x-text="office?.name || ('#' + office.mls_office_id)"></span>
                    </a>
                  </template>
                </div>

                <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white">
                  <span x-text="agent?.full_name || ('Agente #' + agentId)">Cargando…</span>
                </h1>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4 text-white/85">
                  <div class="inline-flex items-center gap-2" x-show="agent?.email">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    <a :href="agent?.email ? ('mailto:' + agent.email) : '#'" class="hover:underline" x-text="agent?.email"></a>
                  </div>
                  <div class="hidden sm:block text-white/30" x-show="agent?.email && (agent?.mobile || agent?.phone)">•</div>
                  <div class="inline-flex items-center gap-2" x-show="agent?.mobile || agent?.phone">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    <a :href="(agent?.mobile || agent?.phone) ? ('tel:' + (agent.mobile || agent.phone)) : '#'" class="hover:underline" x-text="agent?.mobile || agent?.phone"></a>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Propiedades</p>
                  <p class="mt-1 text-lg font-extrabold text-slate-900" x-text="agent?.properties_count ?? '—'"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Agencia (ID)</p>
                  <p class="mt-1 text-sm font-semibold text-slate-900 truncate" x-text="office?.mls_office_id || agent?.mls_office_id || '—'"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Website</p>
                  <a class="mt-1 text-sm font-semibold text-slate-900 truncate block hover:underline" :href="agent?.website || '#'
                     " target="_blank" rel="noopener" x-text="agent?.website ? agent.website.replace(/^https?:\/\//,'') : '—'"></a>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Licencia</p>
                  <p class="mt-1 text-sm font-semibold text-slate-900 truncate" x-text="agent?.license_number || '—'"></p>
                </div>
              </div>

              <div class="mt-6" x-show="agentBio">
                <h2 class="text-lg font-bold text-slate-900">Bio</h2>
                <p class="mt-3 text-slate-700 leading-relaxed whitespace-pre-line" x-text="agentBio"></p>
              </div>
            </div>
          </div>
        </div>

        {{-- Right column: Office summary + quick contact --}}
        <aside class="lg:col-span-4">
          <div class="lg:sticky lg:top-28 space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
              <h3 class="text-sm font-semibold text-slate-900">Contacto</h3>

              <div class="mt-4 space-y-3">
                <a class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg"
                   :href="agent?.email ? ('mailto:' + agent.email) : '#'"
                   :class="agent?.email ? '' : 'opacity-50 pointer-events-none'"
                   style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                  Enviar correo
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </a>

                <a class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
                   :href="(agent?.mobile || agent?.phone) ? ('tel:' + (agent.mobile || agent.phone)) : '#'"
                   :class="(agent?.mobile || agent?.phone) ? '' : 'opacity-50 pointer-events-none'">
                  Llamar
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                </a>
              </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6" x-show="office">
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-900">Agencia</h3>
                <template x-if="office?.mls_office_id">
                  <a :href="'/agencias/' + office.mls_office_id" class="text-xs font-semibold hover:underline" style="color: var(--fe-primary-from, #4f46e5);">Ver agencia</a>
                </template>
              </div>

              <div class="mt-4 flex gap-4">
                <div class="size-14 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">
                  <template x-if="office?.image">
                    <img :src="office.image" :alt="office?.name || 'Agencia'" class="w-full h-full object-cover" loading="lazy" x-on:error="office.image = null" />
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
                  <p class="font-bold text-slate-900 truncate" x-text="office?.name || ('Agencia #' + office?.mls_office_id)"></p>
                  <p class="mt-0.5 text-xs text-slate-600 truncate" x-text="officeLocation || ''"></p>
                  <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                      <p class="text-[11px] font-semibold text-slate-600">Agentes</p>
                      <p class="mt-1 text-sm font-extrabold text-slate-900" x-text="office?.agents_count ?? '—'"></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                      <p class="text-[11px] font-semibold text-slate-600">Propiedades</p>
                      <p class="mt-1 text-sm font-extrabold text-slate-900" x-text="office?.properties_count ?? '—'"></p>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </aside>
      </div>

      {{-- Properties section (filters + pagination) --}}
      <section class="mt-10" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
          <div class="p-6 sm:p-8 border-b border-slate-100">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Propiedades del agente</h2>
                <p class="mt-1 text-sm text-slate-600">Filtros y paginación con el mismo estilo del catálogo.</p>
              </div>
              <div class="text-sm text-slate-600">
                Mostrando <span x-text="propertiesPagination?.from || 0"></span> - <span x-text="propertiesPagination?.to || 0"></span>
                de <span x-text="propertiesPagination?.total || 0"></span>
              </div>
            </div>
          </div>

          <div class="p-6 sm:p-8" x-data="propertiesFilterForAgent()" x-init="init(window.__AGENT_ID__)">
            {{-- Reutilizamos UI de filtros estilo catálogo --}}
            <div class="rounded-2xl border p-4 sm:p-6 shadow-sm mb-8" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
              <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end">
                <div class="flex-1">
                  <label class="block text-xs font-semibold text-slate-600 mb-2">Buscar</label>
                  <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-properties-filter_icon, #94a3b8);">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="filters.search" @input.debounce.300ms="applyFilters()"
                           placeholder="Buscar por ciudad, zona, tipo…" class="w-full pl-12 pr-4 py-3 rounded-xl transition-all focus:outline-none"
                           style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
                  </div>
                </div>
                <button @click="showFiltersModal = true"
                        class="hidden sm:inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                        style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                  Filtros Avanzados
                  <span x-show="countActiveFilters() > 0" x-text="countActiveFilters()" class="ml-1 w-5 h-5 flex items-center justify-center text-xs font-bold rounded-full bg-white text-indigo-600"></span>
                </button>
              </div>

              <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-slate-600">
                  Mostrando <span x-text="pagination?.from || 0"></span> - <span x-text="pagination?.to || 0"></span> de <span x-text="pagination?.total || 0"></span>
                </div>
                <button @click="clearFilters()" x-show="hasFilters()" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                  Limpiar filtros
                </button>
              </div>
            </div>

            {{-- Filters Modal (similar a catálogo) --}}
            <div x-show="showFiltersModal" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
              <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showFiltersModal = false"></div>
              <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div x-show="showFiltersModal" class="relative w-full sm:max-w-2xl bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[90vh] sm:max-h-[85vh] overflow-hidden flex flex-col" @click.away="showFiltersModal = false">
                  <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                      </div>
                      <div>
                        <h3 class="text-lg font-bold text-slate-900">Filtros Avanzados</h3>
                        <p class="text-sm text-slate-500">Personaliza tu búsqueda</p>
                      </div>
                    </div>
                    <button @click="showFiltersModal = false" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-slate-100 transition" aria-label="Cerrar">
                      <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>

                  <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-3">Tipo de Propiedad</label>
                      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button @click="filters.property_type_name = ''; applyFiltersInModal()" :class="filters.property_type_name === '' ? 'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-3 rounded-xl text-sm font-medium transition-all">Todos</button>
                        <template x-for="type in propertyTypes" :key="type">
                          <button @click="filters.property_type_name = type; applyFiltersInModal()" :class="filters.property_type_name === type ? 'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-3 rounded-xl text-sm font-medium transition-all" x-text="type"></button>
                        </template>
                      </div>
                    </div>

                    <div x-show="operationTypes.length > 0">
                      <label class="block text-sm font-semibold text-slate-700 mb-3">Tipo de Operación</label>
                      <div class="flex flex-wrap gap-2">
                        <button @click="filters.operation_type = ''; applyFiltersInModal()" :class="filters.operation_type === '' ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-3 rounded-xl text-sm font-medium transition-all">Todos</button>
                        <template x-for="opType in operationTypes" :key="opType">
                          <button @click="filters.operation_type = opType; applyFiltersInModal()" :class="filters.operation_type === opType ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-3 rounded-xl text-sm font-medium transition-all" x-text="getOperationEmoji(opType) + ' ' + getOperationLabel(opType)"></button>
                        </template>
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-3">Rango de Precio</label>
                      <div class="grid grid-cols-2 gap-3">
                        <div>
                          <label class="block text-xs text-slate-500 mb-1">Mínimo</label>
                          <input type="number" x-model="filters.min_price" @input.debounce.500ms="applyFiltersInModal()" placeholder="0" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" style="background-color: var(--fe-properties-input_bg, #f8fafc);" />
                        </div>
                        <div>
                          <label class="block text-xs text-slate-500 mb-1">Máximo</label>
                          <input type="number" x-model="filters.max_price" @input.debounce.500ms="applyFiltersInModal()" placeholder="Sin límite" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" style="background-color: var(--fe-properties-input_bg, #f8fafc);" />
                        </div>
                      </div>
                    </div>

                    <div x-show="availableCities.length > 0 || availableRegions.length > 0 || availableCityAreas.length > 0">
                      <label class="block text-sm font-semibold text-slate-700 mb-3">Ubicación</label>
                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div x-show="availableRegions.length > 0">
                          <label class="block text-xs text-slate-500 mb-1">Región/Estado</label>
                          <select x-model="filters.region" @change="applyFiltersInModal()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer" style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                            <option value="">Todas</option>
                            <template x-for="r in availableRegions" :key="r"><option :value="r" x-text="r"></option></template>
                          </select>
                        </div>
                        <div x-show="availableCities.length > 0">
                          <label class="block text-xs text-slate-500 mb-1">Ciudad</label>
                          <select x-model="filters.city" @change="applyFiltersInModal()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer" style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                            <option value="">Todas</option>
                            <template x-for="c in availableCities" :key="c"><option :value="c" x-text="c"></option></template>
                          </select>
                        </div>
                        <div x-show="availableCityAreas.length > 0">
                          <label class="block text-xs text-slate-500 mb-1">Zona</label>
                          <select x-model="filters.city_area" @change="applyFiltersInModal()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer" style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                            <option value="">Todas</option>
                            <template x-for="a in availableCityAreas" :key="a"><option :value="a" x-text="a"></option></template>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-3">Ordenar por</label>
                      <select x-model="filters.order" @change="applyFiltersInModal()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer" style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                        <option value="updated_at">Más recientes</option>
                        <option value="created_at">Más antiguas</option>
                        <option value="title">A–Z</option>
                      </select>
                    </div>
                  </div>

                  <div class="p-4 sm:p-6 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row gap-3">
                    <button @click="clearFilters(); showFiltersModal = false" class="flex-1 px-6 py-3 rounded-xl font-semibold border border-slate-300 text-slate-700 hover:bg-slate-100 transition-all">Limpiar todo</button>
                    <button @click="showFiltersModal = false" class="flex-1 px-6 py-3 rounded-xl font-semibold text-white transition-all hover:shadow-lg" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">Ver <span x-text="pagination?.total || 0"></span> resultados</button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Grid --}}
            <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" id="propertiesGrid"></div>

            <div id="propertiesEmpty" class="hidden text-center py-16">
              <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-slate-900 mb-2">No se encontraron propiedades</h3>
              <p class="text-slate-600 mb-6">Intenta ajustar los filtros o buscar con otros términos.</p>
              <button @click="clearFilters()" class="px-6 py-3 rounded-xl text-white font-semibold" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">Limpiar filtros</button>
            </div>

            {{-- Pagination --}}
            <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
              <button @click="goToPage((pagination?.current_page || 1) - 1)" :disabled="!(pagination?.current_page > 1)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">Anterior</button>
              <div class="text-sm text-slate-600">Página <span x-text="pagination?.current_page || 1"></span> de <span x-text="pagination?.last_page || 1"></span></div>
              <button @click="goToPage((pagination?.current_page || 1) + 1)" :disabled="!(pagination?.current_page < pagination?.last_page)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">Siguiente</button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.__AGENT_ID__ = @json($mlsAgentId ?? null);

    function mlsAgentDetail() {
      return {
        agentId: window.__AGENT_ID__,
        agent: null,
        office: null,
        coverUrl: null,
        fallbackCover: 'https://images.unsplash.com/photo-1521791055366-0d553872125f?auto=format&fit=crop&w=2070&q=80',

        get officeLocation() {
          const o = this.office;
          return [o?.city, o?.state_province].filter(Boolean).join(', ');
        },

        get agentBio() {
          const a = this.agent;
          return (a?.bio_es || a?.bio || '').toString().trim();
        },

        async init() {
          if (!this.agentId) return;
          await this.loadAgent();
        },

        async loadAgent() {
          try {
            const res = await fetch(`/api/public/mls-agents/${this.agentId}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data?.success) return;
            this.agent = data.data?.agent || null;
            this.office = data.data?.office || (this.agent?.office || null);

            // Cover: usar foto del agente si existe; fallback.
            this.coverUrl = this.agent?.photo || this.fallbackCover;
            document.title = `${this.agent?.full_name || 'Agente'} | San Miguel Properties`;
          } catch (e) {
            console.error(e);
          }
        },
      }
    }

    // Properties filter component scoped to an agent
    function propertiesFilterForAgent() {
      return {
        agentId: null,
        showFiltersModal: false,
        propertyTypes: [],
        operationTypes: [],
        availableCities: [],
        availableRegions: [],
        availableCityAreas: [],

        filters: {
          mls_agent_id: null,
          search: '',
          property_type_name: '',
          operation_type: '',
          min_price: '',
          max_price: '',
          region: '',
          city: '',
          city_area: '',
          order: 'updated_at',
          sort: 'desc',
          per_page: 9,
          page: 1,
        },
        properties: [],
        pagination: null,

        init(agentId) {
          const resolvedAgentId = agentId || window.__AGENT_ID__;
          this.agentId = resolvedAgentId;
          this.filters.mls_agent_id = resolvedAgentId;
          this.loadFilterOptions();
          this.loadProperties();
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.showFiltersModal) this.showFiltersModal = false;
          });
        },

        async loadFilterOptions() {
          try {
            const res = await fetch('/api/public/properties/filter-options');
            const data = await res.json();
            if (data.success && data.data) {
              const opts = data.data;
              this.propertyTypes = opts.property_types || [];
              this.operationTypes = opts.operation_types || [];
              this.availableCities = opts.cities || [];
              this.availableRegions = opts.regions || [];
              this.availableCityAreas = opts.city_areas || [];
            }
          } catch (e) {
            console.error('Error loading filter options:', e);
          }
        },

        getOperationLabel(type) {
          const labels = { sale: 'Venta', rental: 'Renta', lease: 'Arrendamiento' };
          return labels[type] || type;
        },

        getOperationEmoji(type) {
          const emojis = { sale: '🏷️', rental: '🔑', lease: '📋' };
          return emojis[type] || '📌';
        },

        countActiveFilters() {
          let c = 0;
          if (this.filters.property_type_name) c++;
          if (this.filters.operation_type) c++;
          if (this.filters.min_price) c++;
          if (this.filters.max_price) c++;
          if (this.filters.region) c++;
          if (this.filters.city) c++;
          if (this.filters.city_area) c++;
          return c;
        },

        hasFilters() {
          return this.filters.search !== '' || this.countActiveFilters() > 0;
        },

        applyFilters() {
          this.filters.page = 1;
          this.loadProperties();
        },

        applyFiltersInModal() {
          this.filters.page = 1;
          this.loadProperties();
        },

        clearFilters() {
          const agentId = this.filters.mls_agent_id;
          this.filters = {
            mls_agent_id: agentId,
            search: '',
            property_type_name: '',
            operation_type: '',
            min_price: '',
            max_price: '',
            region: '',
            city: '',
            city_area: '',
            order: 'updated_at',
            sort: 'desc',
            per_page: 9,
            page: 1,
          };
          this.loadProperties();
        },

        goToPage(page) {
          if (!this.pagination) return;
          if (page < 1 || page > this.pagination.last_page) return;
          this.filters.page = page;
          this.loadProperties();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async loadProperties() {
          const grid = document.getElementById('propertiesGrid');
          const empty = document.getElementById('propertiesEmpty');

          grid.innerHTML = Array.from({ length: 9 }).map(() => `
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
              <div class="skeleton h-56 w-full"></div>
              <div class="p-6 space-y-4">
                <div class="skeleton h-4 w-3/4 rounded"></div>
                <div class="skeleton h-6 w-full rounded"></div>
                <div class="skeleton h-4 w-1/2 rounded"></div>
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

            const res = await fetch(`/api/public/properties?${params.toString()}`);
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
              const imageUrl = p.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
              const price = p.operations?.[0]?.formatted_amount || 'Consultar precio';
              const op = p.operations?.[0]?.operation_type || '';
              const location = [p.location?.city, p.location?.city_area].filter(Boolean).join(', ') || 'Ubicación disponible';
              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="relative h-56 overflow-hidden">
                    <img src="${esc(imageUrl)}" alt="${esc(p.title || 'Propiedad')}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    ${p.property_type_name ? `<span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-semibold rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #0f172a);">${esc(p.property_type_name)}</span>` : ''}
                    ${op ? `<span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${op === 'sale' ? 'var(--fe-properties-sale_badge, #10b981)' : 'var(--fe-properties-rent_badge, #f59e0b)'};">${op === 'sale' ? 'En Venta' : 'En Renta'}</span>` : ''}
                  </div>
                  <div class="p-6">
                    <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--fe-properties-card_location, #64748b);">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      ${esc(location)}
                    </div>
                    <h3 class="text-lg font-bold mb-3 line-clamp-2" style="color: var(--fe-properties-card_title, #0f172a);">${esc(p.title || 'Propiedad disponible')}</h3>
                    <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">${esc(price)}</div>
                    <a href="/propiedades/${esc(p.id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">Ver detalles <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg></a>
                  </div>
                </div>
              `;
            }).join('');
          } catch (e) {
            console.error(e);
            grid.innerHTML = '';
            empty.classList.remove('hidden');
          }
        },
      }
    }
  </script>
@endpush

