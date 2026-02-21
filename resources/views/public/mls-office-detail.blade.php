@extends('layouts.public')

@section('title', 'Agencia')

@section('content')
  <div class="relative overflow-hidden pt-24">
    {{-- Background decor --}}
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.06)_1px,transparent_0)] [background-size:28px_28px]"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16" x-data="mlsOfficeDetail()" x-init="init()">
      {{-- Breadcrumbs --}}
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition">Inicio</a>
        <span class="text-slate-400">/</span>
        <a href="{{ route('public.mls-offices.index') }}" class="text-slate-600 hover:text-slate-900 transition">Agencias</a>
        <span class="text-slate-400">/</span>
        <span class="text-slate-900 font-medium truncate" x-text="office?.name || ('Agencia #' + officeId)">Agencia</span>
      </nav>

      {{-- Header --}}
      <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <div class="lg:col-span-8">
          <div class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="relative">
              <div class="h-64 sm:h-72 lg:h-80 bg-slate-100">
                <template x-if="coverUrl">
                  <img :src="coverUrl" :alt="office?.name || 'Agencia'" class="w-full h-full object-cover" loading="lazy" x-on:error="coverUrl = fallbackCover" />
                </template>
                <template x-if="!coverUrl">
                  <div class="h-full w-full skeleton"></div>
                </template>
              </div>

              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

              <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                    <span class="inline-block size-1.5 rounded-full bg-white/90"></span>
                    MLS #<span x-text="officeId"></span>
                  </span>
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(255,255,255,0.85); color: #1C1C1C;" x-show="office?.paid">
                    Paid
                  </span>
                </div>

                <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white">
                  <span x-text="office?.name || ('Agencia #' + officeId)">Cargando…</span>
                </h1>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4 text-white/85">
                  <div class="inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span x-text="officeLocation || 'Ubicación disponible'"></span>
                  </div>
                  <div class="hidden sm:block text-white/30">•</div>
                  <div class="inline-flex items-center gap-2" x-show="office?.email">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    <a :href="office?.email ? ('mailto:' + office.email) : '#'" class="hover:underline" x-text="office?.email"></a>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Agentes</p>
                  <p class="mt-1 text-lg font-extrabold text-slate-900" x-text="office?.agents_count ?? '—'"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Propiedades</p>
                  <p class="mt-1 text-lg font-extrabold text-slate-900" x-text="office?.properties_count ?? '—'"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Teléfono</p>
                  <p class="mt-1 text-sm font-semibold text-slate-900 truncate" x-text="officePhone || '—'"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs font-semibold text-slate-600">Website</p>
                  <a class="mt-1 text-sm font-semibold text-slate-900 truncate block hover:underline" :href="office?.website || '#'" target="_blank" rel="noopener" x-text="office?.website ? office.website.replace(/^https?:\/\//,'') : '—'"></a>
                </div>
              </div>

              <div class="mt-6" x-show="officeDescription">
                <h2 class="text-lg font-bold text-slate-900">Descripción</h2>
                <p class="mt-3 text-slate-700 leading-relaxed whitespace-pre-line" x-text="officeDescription"></p>
              </div>
            </div>
          </div>
        </div>

        {{-- Right column: Agents list --}}
        <aside class="lg:col-span-4">
          <div class="lg:sticky lg:top-28 space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-900">Agentes</h3>
                <span class="text-xs font-semibold text-slate-600" x-text="agentsPagination ? ('Página ' + agentsPagination.current_page + ' / ' + agentsPagination.last_page) : ''"></span>
              </div>

              <div class="mt-4">
                <div class="relative">
                  <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                  <input type="text" x-model="agentsFilters.search" @input.debounce.300ms="applyAgentsFilters()"
                         placeholder="Buscar agente..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus-fe-primary" />
                </div>
              </div>

              <div class="mt-5 space-y-3" id="agentsList">
                <template x-if="agentsLoading">
                  <div class="space-y-3">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4"><div class="h-4 w-40 skeleton rounded"></div><div class="mt-2 h-3 w-28 skeleton rounded"></div></div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4"><div class="h-4 w-44 skeleton rounded"></div><div class="mt-2 h-3 w-24 skeleton rounded"></div></div>
                  </div>
                </template>

                <template x-if="!agentsLoading && agents.length === 0">
                  <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700">
                    No hay agentes para esta agencia.
                  </div>
                </template>

                <template x-for="a in agents" :key="a.mls_agent_id">
                  <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start gap-4">
                      <div class="size-12 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">
                        <template x-if="a.photo">
                          <img :src="a.photo" :alt="a.full_name" class="w-full h-full object-cover" loading="lazy" x-on:error="a.photo = null" />
                        </template>
                        <template x-if="!a.photo">
                          <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" /></svg>
                        </template>
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                          <p class="font-bold text-slate-900 truncate" x-text="a.full_name"></p>
                          <span class="text-xs font-semibold text-slate-500" x-text="a.properties_count ? (a.properties_count + ' props') : ''"></span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-600 truncate" x-text="a.email || '—'"></p>
                        <div class="mt-3 flex flex-col gap-2" x-show="a.phone || a.mobile">
                          <a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900" :href="(a.mobile || a.phone) ? ('tel:' + (a.mobile || a.phone)) : '#'" x-text="a.mobile || a.phone"></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <div class="mt-5 flex items-center justify-between gap-3">
                <button @click="goAgentsPage((agentsPagination?.current_page || 1) - 1)" :disabled="!(agentsPagination?.current_page > 1)"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                  Anterior
                </button>
                <button @click="goAgentsPage((agentsPagination?.current_page || 1) + 1)" :disabled="!(agentsPagination?.current_page < agentsPagination?.last_page)"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                  Siguiente
                </button>
              </div>
            </section>
          </div>
        </aside>
      </div>

      {{-- Properties section (filters + pagination similar to properties page) --}}
      <section class="mt-10" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
          <div class="p-6 sm:p-8 border-b border-slate-100">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Propiedades de la agencia</h2>
                <p class="mt-1 text-sm text-slate-600">Incluye filtros y paginación con el mismo estilo del catálogo.</p>
              </div>
              <div class="text-sm text-slate-600">
                Mostrando <span x-text="propertiesPagination?.from || 0"></span> - <span x-text="propertiesPagination?.to || 0"></span>
                de <span x-text="propertiesPagination?.total || 0"></span>
              </div>
            </div>
          </div>

          <div class="p-6 sm:p-8" x-data="propertiesFilterForOffice()" x-init="init(window.__OFFICE_ID__)">
            {{-- Reutilizamos el UI de filtros (barra + modal) de propiedades-index --}}
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
                           style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);">
                  </div>
                </div>
                <button @click="showFiltersModal = true"
                        class="hidden sm:inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                        style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
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

            {{-- Filters Modal (simplified copy from properties-index) --}}
            <div x-show="showFiltersModal" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
              <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showFiltersModal = false"></div>
              <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div x-show="showFiltersModal" class="relative w-full sm:max-w-2xl bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[90vh] sm:max-h-[85vh] overflow-hidden flex flex-col" @click.away="showFiltersModal = false">
                  <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
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
                    <button @click="showFiltersModal = false" class="flex-1 px-6 py-3 rounded-xl font-semibold text-white transition-all hover:shadow-lg" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">Ver <span x-text="pagination?.total || 0"></span> resultados</button>
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
              <button @click="clearFilters()" class="px-6 py-3 rounded-xl text-white font-semibold" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">Limpiar filtros</button>
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
    window.__OFFICE_ID__ = @json($mlsOfficeId ?? null);

    function mlsOfficeDetail() {
      return {
        officeId: window.__OFFICE_ID__,
        office: null,
        coverUrl: null,
        fallbackCover: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',

        // Agents
        agents: [],
        agentsLoading: true,
        agentsPagination: null,
        agentsFilters: { search: '', per_page: 8, page: 1, order: 'name', sort: 'asc' },

        get officeLocation() {
          const o = this.office;
          return [o?.city, o?.state_province].filter(Boolean).join(', ');
        },

        get officePhone() {
          const o = this.office;
          return o?.phone_1 || o?.phone_2 || o?.phone_3 || '';
        },

        get officeDescription() {
          const o = this.office;
          return (o?.description_es || o?.description || '').toString().trim();
        },

        async init() {
          if (!this.officeId) return;
          await this.loadOffice();
          await this.loadAgents();
        },

        async loadOffice() {
          try {
            const res = await fetch(`/api/public/mls-offices/${this.officeId}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data?.success) return;
            this.office = data.data;
            this.coverUrl = this.office?.image || this.office?.image_url || this.fallbackCover;
            document.title = `${this.office?.name || 'Agencia'} | San Miguel Properties`;
          } catch (e) {
            console.error(e);
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

            const res = await fetch(`/api/public/mls-offices/${this.officeId}/agents?${p.toString()}`, { headers: { 'Accept': 'application/json' } });
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
          } catch (e) {
            console.error(e);
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
      }
    }

    // Properties filter component scoped to an office
    function propertiesFilterForOffice() {
      return {
        officeId: null,
        showFiltersModal: false,
        propertyTypes: [],
        operationTypes: [],
        availableCities: [],
        availableRegions: [],
        availableCityAreas: [],

        filters: {
          mls_office_id: null,
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

        init(officeId) {
          // Importante: este componente está dentro de otro x-data (mlsOfficeDetail).
          // Al tener su propio x-data, `officeId` NO se hereda; por eso lo recibimos por parámetro
          // (desde `window.__OFFICE_ID__`) y además dejamos fallback.
          const resolvedOfficeId = officeId || window.__OFFICE_ID__;
          this.officeId = resolvedOfficeId;
          this.filters.mls_office_id = resolvedOfficeId;
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
          const officeId = this.filters.mls_office_id;
          this.filters = {
            mls_office_id: officeId,
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
              const imageUrl = p.cover_media_asset?.serving_url || p.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
              const price = p.operations?.[0]?.formatted_amount || 'Consultar precio';
              const op = p.operations?.[0]?.operation_type || '';
              const location = [p.location?.city, p.location?.city_area].filter(Boolean).join(', ') || 'Ubicación disponible';
              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="relative h-56 overflow-hidden">
                    <img src="${esc(imageUrl)}" alt="${esc(p.title || 'Propiedad')}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    ${p.property_type_name ? `<span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-semibold rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #1C1C1C);">${esc(p.property_type_name)}</span>` : ''}
                    ${op ? `<span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${op === 'sale' ? 'var(--fe-properties-sale_badge, #768D59)' : 'var(--fe-properties-rent_badge, #D1A054)'};">${op === 'sale' ? 'En Venta' : 'En Renta'}</span>` : ''}
                  </div>
                  <div class="p-6">
                    <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--fe-properties-card_location, #64748b);">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      ${esc(location)}
                    </div>
                    <h3 class="text-lg font-bold mb-3 line-clamp-2" style="color: var(--fe-properties-card_title, #1C1C1C);">${esc(p.title || 'Propiedad disponible')}</h3>
                    <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">${esc(price)}</div>
                    <a href="/propiedades/${esc(p.id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">Ver detalles <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg></a>
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

