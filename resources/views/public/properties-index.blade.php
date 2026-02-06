@extends('layouts.public')

@section('title', 'Propiedades')

@section('content')
  <div class="pt-24">
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-from, rgba(79,70,229,.35));"></div>
        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-to, rgba(16,185,129,.35));"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold" style="background-color: var(--fe-properties-badge_bg, #eef2ff); color: var(--fe-properties-badge_text, #4f46e5);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Catálogo
          </div>

          <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
            Explora nuestras <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">propiedades</span>
          </h1>
          <p class="mt-4 text-lg text-slate-600">
            Filtra por tipo y encuentra la propiedad ideal. Diseño 100% responsive.
          </p>
        </div>
      </div>
    </section>

    <section class="py-12 lg:py-16" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="propertiesFilter()" x-init="init()">
        
        {{-- Quick Search Bar (Always visible) --}}
        <div class="rounded-2xl border p-4 sm:p-6 shadow-sm mb-6" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
          <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end">
            {{-- Search Input --}}
            <div class="flex-1">
              <label class="block text-xs font-semibold text-slate-600 mb-2">Buscar</label>
              <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-properties-filter_icon, #94a3b8);">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       x-model="filters.search"
                       @input.debounce.300ms="applyFilters()"
                       placeholder="Buscar por ciudad, zona, tipo…"
                       class="w-full pl-12 pr-4 py-3 rounded-xl transition-all focus:outline-none"
                       style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
              </div>
            </div>

            {{-- Filter Button (Desktop) --}}
            <button @click="showFiltersModal = true"
                    class="hidden sm:inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                    style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              Filtros Avanzados
              <span x-show="countActiveFilters() > 0" 
                    x-text="countActiveFilters()"
                    class="ml-1 w-5 h-5 flex items-center justify-center text-xs font-bold rounded-full bg-white text-indigo-600"></span>
            </button>
          </div>

          {{-- Results count and clear --}}
          <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-slate-600">
              Mostrando <span x-text="pagination?.from || 0"></span> - <span x-text="pagination?.to || 0"></span>
              de <span x-text="pagination?.total || 0"></span>
            </div>

            <button @click="clearFilters()" x-show="hasFilters()"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar filtros
            </button>
          </div>

          {{-- Active Filters Tags --}}
          <div x-show="hasFilters()" class="mt-4 flex flex-wrap gap-2">
            <template x-if="filters.property_type_name">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                <span x-text="'Tipo: ' + filters.property_type_name"></span>
                <button @click="filters.property_type_name = ''; applyFilters()" class="ml-1 hover:text-indigo-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
            <template x-if="filters.operation_type">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                <span x-text="'Operación: ' + getOperationLabel(filters.operation_type)"></span>
                <button @click="filters.operation_type = ''; applyFilters()" class="ml-1 hover:text-emerald-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
            <template x-if="filters.bedrooms">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                <span x-text="'Recámaras: ' + filters.bedrooms + '+'"></span>
                <button @click="filters.bedrooms = ''; applyFilters()" class="ml-1 hover:text-blue-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
            <template x-if="filters.bathrooms">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">
                <span x-text="'Baños: ' + filters.bathrooms + '+'"></span>
                <button @click="filters.bathrooms = ''; applyFilters()" class="ml-1 hover:text-cyan-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
            <template x-if="filters.min_price || filters.max_price">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                <span x-text="getPriceRangeLabel()"></span>
                <button @click="filters.min_price = ''; filters.max_price = ''; applyFilters()" class="ml-1 hover:text-amber-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
            <template x-if="filters.city">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                <span x-text="'Ciudad: ' + filters.city"></span>
                <button @click="filters.city = ''; applyFilters()" class="ml-1 hover:text-purple-900">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </span>
            </template>
          </div>
        </div>

		{{-- Floating Filter Button (Mobile Only) --}}
		<button @click="showFiltersModal = true"
				class="sm:hidden fixed bottom-6 left-6 z-40 w-14 h-14 flex items-center justify-center rounded-full text-white shadow-lg transition-all duration-300 hover:scale-110"
				style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <span x-show="countActiveFilters() > 0" 
                x-text="countActiveFilters()"
                class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center text-xs font-bold rounded-full bg-red-500 text-white"></span>
        </button>

        {{-- Filters Modal/Popup --}}
        <div x-show="showFiltersModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
          
          {{-- Backdrop --}}
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showFiltersModal = false"></div>
          
          {{-- Modal Content --}}
          <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="showFiltersModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="relative w-full sm:max-w-2xl bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[90vh] sm:max-h-[85vh] overflow-hidden flex flex-col"
                 @click.away="showFiltersModal = false">
              
              {{-- Modal Header --}}
              <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Filtros Avanzados</h3>
                    <p class="text-sm text-slate-500">Personaliza tu búsqueda</p>
                  </div>
                </div>
                <button @click="showFiltersModal = false" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-slate-100 transition">
                  <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              {{-- Modal Body (Scrollable) --}}
              <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
                
                {{-- Tipo de Propiedad --}}
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Tipo de Propiedad</label>
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <button @click="filters.property_type_name = filters.property_type_name === '' ? '' : ''; applyFiltersInModal()"
                            :class="filters.property_type_name === '' ? 'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-3 rounded-xl text-sm font-medium transition-all">
                      Todos
                    </button>
                    <template x-for="type in propertyTypes" :key="type">
                      <button @click="filters.property_type_name = type; applyFiltersInModal()"
                              :class="filters.property_type_name === type ? 'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                              class="px-4 py-3 rounded-xl text-sm font-medium transition-all"
                              x-text="type">
                      </button>
                    </template>
                  </div>
                </div>

                {{-- Tipo de Operación (dinámico) --}}
                <div x-show="operationTypes.length > 0">
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Tipo de Operación</label>
                  <div class="flex flex-wrap gap-2">
                    <button @click="filters.operation_type = ''; applyFiltersInModal()"
                            :class="filters.operation_type === '' ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-3 rounded-xl text-sm font-medium transition-all">
                      Todos
                    </button>
                    <template x-for="opType in operationTypes" :key="opType">
                      <button @click="filters.operation_type = opType; applyFiltersInModal()"
                              :class="filters.operation_type === opType ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                              class="px-4 py-3 rounded-xl text-sm font-medium transition-all"
                              x-text="getOperationEmoji(opType) + ' ' + getOperationLabel(opType)">
                      </button>
                    </template>
                  </div>
                </div>

                {{-- Rango de Precio --}}
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Rango de Precio</label>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs text-slate-500 mb-1">Mínimo</label>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                        <input type="number" 
                               x-model="filters.min_price"
                               @input.debounce.500ms="applyFiltersInModal()"
                               placeholder="0"
                               class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                               style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                      </div>
                    </div>
                    <div>
                      <label class="block text-xs text-slate-500 mb-1">Máximo</label>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                        <input type="number" 
                               x-model="filters.max_price"
                               @input.debounce.500ms="applyFiltersInModal()"
                               placeholder="Sin límite"
                               class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                               style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Características (dinámicas) --}}
                <div x-show="availableBedrooms.length > 0 || availableBathrooms.length > 0 || availableParking.length > 0">
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Características</label>
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {{-- Recámaras --}}
                    <div x-show="availableBedrooms.length > 0">
                      <label class="block text-xs text-slate-500 mb-2">🛏️ Recámaras</label>
                      <div class="flex items-center gap-1 flex-wrap">
                        <template x-for="n in availableBedrooms" :key="'bed-' + n">
                          <button @click="filters.bedrooms = filters.bedrooms == n ? '' : n; applyFiltersInModal()"
                                  :class="filters.bedrooms == n ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                  class="w-9 h-9 rounded-lg text-sm font-medium transition-all"
                                  x-text="n + '+'">
                          </button>
                        </template>
                      </div>
                    </div>
                    {{-- Baños --}}
                    <div x-show="availableBathrooms.length > 0">
                      <label class="block text-xs text-slate-500 mb-2">🚿 Baños</label>
                      <div class="flex items-center gap-1 flex-wrap">
                        <template x-for="n in availableBathrooms" :key="'bath-' + n">
                          <button @click="filters.bathrooms = filters.bathrooms == n ? '' : n; applyFiltersInModal()"
                                  :class="filters.bathrooms == n ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                  class="w-9 h-9 rounded-lg text-sm font-medium transition-all"
                                  x-text="n + '+'">
                          </button>
                        </template>
                      </div>
                    </div>
                    {{-- Estacionamientos --}}
                    <div x-show="availableParking.length > 0">
                      <label class="block text-xs text-slate-500 mb-2">🚗 Estacionamientos</label>
                      <div class="flex items-center gap-1 flex-wrap">
                        <template x-for="n in availableParking" :key="'park-' + n">
                          <button @click="filters.parking_spaces = filters.parking_spaces == n ? '' : n; applyFiltersInModal()"
                                  :class="filters.parking_spaces == n ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                  class="w-9 h-9 rounded-lg text-sm font-medium transition-all"
                                  x-text="n + '+'">
                          </button>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Tamaño --}}
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Tamaño (m²)</label>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs text-slate-500 mb-1">Construcción mínima</label>
                      <input type="number" 
                             x-model="filters.min_construction_size"
                             @input.debounce.500ms="applyFiltersInModal()"
                             placeholder="Ej: 100"
                             class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                             style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                    </div>
                    <div>
                      <label class="block text-xs text-slate-500 mb-1">Terreno mínimo</label>
                      <input type="number" 
                             x-model="filters.min_lot_size"
                             @input.debounce.500ms="applyFiltersInModal()"
                             placeholder="Ej: 200"
                             class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                             style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                    </div>
                  </div>
                </div>

                {{-- Ubicación (dinámica con selects) --}}
                <div x-show="availableRegions.length > 0 || availableCities.length > 0 || availableCityAreas.length > 0">
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Ubicación</label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div x-show="availableRegions.length > 0">
                      <label class="block text-xs text-slate-500 mb-1">Región/Estado</label>
                      <select x-model="filters.region"
                              @change="applyFiltersInModal()"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer"
                              style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                        <option value="">Todas las regiones</option>
                        <template x-for="r in availableRegions" :key="r">
                          <option :value="r" x-text="r"></option>
                        </template>
                      </select>
                    </div>
                    <div x-show="availableCities.length > 0">
                      <label class="block text-xs text-slate-500 mb-1">Ciudad</label>
                      <select x-model="filters.city"
                              @change="applyFiltersInModal()"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer"
                              style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                        <option value="">Todas las ciudades</option>
                        <template x-for="c in availableCities" :key="c">
                          <option :value="c" x-text="c"></option>
                        </template>
                      </select>
                    </div>
                    <div x-show="availableCityAreas.length > 0">
                      <label class="block text-xs text-slate-500 mb-1">Zona/Colonia</label>
                      <select x-model="filters.city_area"
                              @change="applyFiltersInModal()"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer"
                              style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                        <option value="">Todas las zonas</option>
                        <template x-for="a in availableCityAreas" :key="a">
                          <option :value="a" x-text="a"></option>
                        </template>
                      </select>
                    </div>
                  </div>
                </div>

                {{-- Ordenar por --}}
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-3">Ordenar por</label>
                  <select x-model="filters.order" @change="applyFiltersInModal()"
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer"
                          style="background-color: var(--fe-properties-input_bg, #f8fafc);">
                    <option value="updated_at">Más recientes</option>
                    <option value="created_at">Más antiguas</option>
                    <option value="title">A–Z</option>
                  </select>
                </div>

              </div>

              {{-- Modal Footer --}}
              <div class="p-4 sm:p-6 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row gap-3">
                <button @click="clearFilters(); showFiltersModal = false"
                        class="flex-1 px-6 py-3 rounded-xl font-semibold border border-slate-300 text-slate-700 hover:bg-slate-100 transition-all">
                  Limpiar todo
                </button>
                <button @click="showFiltersModal = false"
                        class="flex-1 px-6 py-3 rounded-xl font-semibold text-white transition-all hover:shadow-lg"
                        style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                  Ver <span x-text="pagination?.total || 0"></span> resultados
                </button>
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
          <button @click="clearFilters()" class="px-6 py-3 rounded-xl text-white font-semibold" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
            Limpiar filtros
          </button>
        </div>

        {{-- Pagination --}}
        <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
          <button @click="goToPage((pagination?.current_page || 1) - 1)" :disabled="!(pagination?.current_page > 1)"
                  class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Anterior
          </button>
          <div class="text-sm text-slate-600">
            Página <span x-text="pagination?.current_page || 1"></span> de <span x-text="pagination?.last_page || 1"></span>
          </div>
          <button @click="goToPage((pagination?.current_page || 1) + 1)" :disabled="!(pagination?.current_page < pagination?.last_page)"
                  class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Siguiente
          </button>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    function propertiesFilter() {
      return {
        // Estado del modal
        showFiltersModal: false,
        
        // Opciones dinámicas de filtro (se cargan desde la API)
        propertyTypes: [],
        operationTypes: [],
        availableCities: [],
        availableRegions: [],
        availableCityAreas: [],
        availableBedrooms: [],
        availableBathrooms: [],
        availableParking: [],
        priceRange: { min: 0, max: 0 },
        totalAvailable: 0,
        filterOptionsLoaded: false,
        
        // Filtros - incluye todos los filtros avanzados de la API
        filters: {
          search: '',
          property_type_name: '',
          operation_type: '',
          min_price: '',
          max_price: '',
          bedrooms: '',
          bathrooms: '',
          parking_spaces: '',
          min_construction_size: '',
          min_lot_size: '',
          region: '',
          city: '',
          city_area: '',
          order: 'updated_at',
          sort: 'desc',
          per_page: 9,
          page: 1
        },
        properties: [],
        pagination: null,

        init() {
          // Cargar opciones de filtro dinámicas desde la API
          this.loadFilterOptions();
          // Leer filtros desde la URL al inicializar
          this.loadFiltersFromUrl();
          this.loadProperties();
          // Cerrar modal con Escape
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.showFiltersModal) {
              this.showFiltersModal = false;
            }
          });
          // Escuchar cambios en el historial (navegación adelante/atrás)
          window.addEventListener('popstate', () => {
            this.loadFiltersFromUrl();
            this.loadProperties();
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
              this.availableBedrooms = opts.bedrooms || [];
              this.availableBathrooms = opts.bathrooms || [];
              this.availableParking = opts.parking_spaces || [];
              this.priceRange = opts.price_range || { min: 0, max: 0 };
              this.totalAvailable = opts.total_properties || 0;
              this.filterOptionsLoaded = true;
            }
          } catch (e) {
            console.error('Error loading filter options:', e);
            // Fallback: dejar arrays vacíos, la UI mostrará solo "Todos"
          }
        },

        // Helper para obtener label de operación
        getOperationLabel(type) {
          const labels = { sale: 'Venta', rental: 'Renta', lease: 'Arrendamiento' };
          return labels[type] || type;
        },

        // Helper para obtener emoji de operación
        getOperationEmoji(type) {
          const emojis = { sale: '🏷️', rental: '🔑', lease: '📋' };
          return emojis[type] || '📌';
        },

        // Cargar filtros desde los parámetros de la URL
        loadFiltersFromUrl() {
          const urlParams = new URLSearchParams(window.location.search);
          
          // Lista de filtros que se pueden pasar por URL
          const filterKeys = [
            'search', 'property_type_name', 'operation_type', 'min_price', 'max_price',
            'bedrooms', 'bathrooms', 'parking_spaces', 'min_construction_size',
            'min_lot_size', 'region', 'city', 'city_area', 'order', 'sort', 'per_page', 'page'
          ];
          
          filterKeys.forEach(key => {
            if (urlParams.has(key)) {
              const value = urlParams.get(key);
              // Convertir a número si es necesario
              if (['min_price', 'max_price', 'bedrooms', 'bathrooms', 'parking_spaces',
                   'min_construction_size', 'min_lot_size', 'per_page', 'page'].includes(key)) {
                this.filters[key] = value ? parseInt(value, 10) || value : '';
              } else {
                this.filters[key] = value;
              }
            }
          });
        },

        // Actualizar la URL con los filtros actuales
        updateUrlWithFilters() {
          const params = new URLSearchParams();
          
          // Solo agregar filtros que tengan valor y no sean los valores por defecto
          Object.keys(this.filters).forEach(key => {
            const value = this.filters[key];
            // Excluir valores vacíos y valores por defecto
            if (value !== null && value !== undefined && value !== '') {
              // Excluir valores por defecto
              if (key === 'order' && value === 'updated_at') return;
              if (key === 'sort' && value === 'desc') return;
              if (key === 'per_page' && value === 9) return;
              if (key === 'page' && value === 1) return;
              
              params.append(key, value);
            }
          });
          
          // Construir la nueva URL
          const newUrl = params.toString()
            ? `${window.location.pathname}?${params.toString()}`
            : window.location.pathname;
          
          // Actualizar la URL sin recargar la página (usar null en lugar del objeto filters)
          window.history.pushState(null, '', newUrl);
        },

        // Contar filtros activos (excluyendo search, order, sort, per_page, page)
        countActiveFilters() {
          let count = 0;
          if (this.filters.property_type_name) count++;
          if (this.filters.operation_type) count++;
          if (this.filters.min_price) count++;
          if (this.filters.max_price) count++;
          if (this.filters.bedrooms) count++;
          if (this.filters.bathrooms) count++;
          if (this.filters.parking_spaces) count++;
          if (this.filters.min_construction_size) count++;
          if (this.filters.min_lot_size) count++;
          if (this.filters.region) count++;
          if (this.filters.city) count++;
          if (this.filters.city_area) count++;
          return count;
        },

        hasFilters() {
          return this.filters.search !== '' || this.countActiveFilters() > 0;
        },

        // Obtener etiqueta del rango de precio
        getPriceRangeLabel() {
          const min = this.filters.min_price;
          const max = this.filters.max_price;
          if (min && max) {
            return `Precio: $${Number(min).toLocaleString()} - $${Number(max).toLocaleString()}`;
          } else if (min) {
            return `Precio: desde $${Number(min).toLocaleString()}`;
          } else if (max) {
            return `Precio: hasta $${Number(max).toLocaleString()}`;
          }
          return '';
        },

        applyFilters() {
          this.filters.page = 1;
          this.updateUrlWithFilters();
          this.loadProperties();
        },

        // Aplicar filtros desde el modal (sin cerrar)
        applyFiltersInModal() {
          this.filters.page = 1;
          this.updateUrlWithFilters();
          this.loadProperties();
        },

        clearFilters() {
          this.filters.search = '';
          this.filters.property_type_name = '';
          this.filters.operation_type = '';
          this.filters.min_price = '';
          this.filters.max_price = '';
          this.filters.bedrooms = '';
          this.filters.bathrooms = '';
          this.filters.parking_spaces = '';
          this.filters.min_construction_size = '';
          this.filters.min_lot_size = '';
          this.filters.region = '';
          this.filters.city = '';
          this.filters.city_area = '';
          this.filters.order = 'updated_at';
          this.filters.page = 1;
          this.updateUrlWithFilters();
          this.loadProperties();
        },

        goToPage(page) {
          if (!this.pagination) return;
          if (page < 1 || page > this.pagination.last_page) return;
          this.filters.page = page;
          this.updateUrlWithFilters();
          this.loadProperties();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async loadProperties() {
          const grid = document.getElementById('propertiesGrid');
          const empty = document.getElementById('propertiesEmpty');

          // skeletons
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
            Object.keys(this.filters).forEach(key => {
              if (this.filters[key] !== null && this.filters[key] !== undefined && this.filters[key] !== '') {
                params.append(key, this.filters[key]);
              }
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

            grid.innerHTML = this.properties.map((p) => {
              const imageUrl = p.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1073&q=80';
              const price = p.operations?.[0]?.formatted_amount || 'Consultar precio';
              const op = p.operations?.[0]?.operation_type || '';
              const location = [p.location?.city, p.location?.city_area].filter(Boolean).join(', ') || 'Ubicación disponible';

              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="relative h-56 overflow-hidden">
                    <img src="${imageUrl}" alt="${(p.title || 'Propiedad').replaceAll('"','&quot;')}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    ${p.property_type_name ? `
                      <span class="absolute top-4 left-4 px-3 py-1 backdrop-blur-sm text-xs font-semibold rounded-full" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #0f172a);">
                        ${p.property_type_name}
                      </span>
                    ` : ''}

                    ${op ? `
                      <span class="absolute top-4 right-4 px-3 py-1 text-white text-xs font-semibold rounded-full" style="background-color: ${op === 'sale' ? 'var(--fe-properties-sale_badge, #10b981)' : 'var(--fe-properties-rent_badge, #f59e0b)'};">
                        ${op === 'sale' ? 'En Venta' : 'En Renta'}
                      </span>
                    ` : ''}
                  </div>

                  <div class="p-6">
                    <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--fe-properties-card_location, #64748b);">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      ${location}
                    </div>

                    <h3 class="text-lg font-bold mb-3 line-clamp-2" style="color: var(--fe-properties-card_title, #0f172a);">
                      ${p.title || 'Propiedad disponible'}
                    </h3>

                    <div class="text-2xl font-extrabold text-transparent bg-clip-text mb-4" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                      ${price}
                    </div>

                    <a href="/propiedades/${p.id}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                      Ver detalles
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                      </svg>
                    </a>
                  </div>
                </div>
              `;
            }).join('');
          } catch (e) {
            console.error(e);
            grid.innerHTML = '';
            empty.classList.remove('hidden');
          }
        }
      }
    }
  </script>
@endpush

