@extends('layouts.app')

@section('title', 'Administrar Propiedades')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Propiedades</h1>
      <p class="text-[var(--c-muted)] mt-1">Crea, edita y controla el inventario de propiedades</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-create-property" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nueva propiedad
      </button>

      <button id="btn-refresh-properties" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Actualizar
      </button>
    </div>
  </div>

  <!-- Filters + List -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)]">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-4">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <div class="flex items-center gap-2 rounded-xl bg-[var(--c-elev)] px-3 py-2 ring-1 ring-[var(--c-border)] focus-within:ring-[var(--c-primary)]">
            <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input id="filter-search" type="search" placeholder="Título, EB ID, MLS ID, tipo…" class="bg-transparent outline-none w-full text-sm placeholder:text-[var(--c-muted)]" />
          </div>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Origen</label>
          <select id="filter-source" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="manual">Manual</option>
            <option value="easybroker">EasyBroker</option>
            <option value="mls">MLS</option>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Agencia</label>
          <select id="filter-agency" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todas</option>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Publicado</label>
          <select id="filter-published" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Orden</label>
          <select id="filter-order" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="updated_at:desc" selected>Actualizado (desc)</option>
            <option value="updated_at:asc">Actualizado (asc)</option>
            <option value="created_at:desc">Creado (desc)</option>
            <option value="created_at:asc">Creado (asc)</option>
            <option value="title:asc">Título (A–Z)</option>
            <option value="title:desc">Título (Z–A)</option>
            <option value="easybroker_updated_at:desc">EasyBroker (desc)</option>
            <option value="easybroker_updated_at:asc">EasyBroker (asc)</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
          <span id="properties-count">—</span>
          <span class="opacity-60">•</span>
          <span id="properties-page">—</span>
        </div>

        <div class="flex items-center gap-2">
          <button id="btn-prev-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50" disabled>Anterior</button>
          <button id="btn-next-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50" disabled>Siguiente</button>
        </div>
      </div>
    </div>

    <div class="p-5">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-[var(--c-muted)]">
              <th class="py-2 pr-3">Propiedad</th>
              <th class="py-2 pr-3">Agencia</th>
              <th class="py-2 pr-3">Origen</th>
              <th class="py-2 pr-3">Tipo</th>
              <th class="py-2 pr-3">Estado</th>
              <th class="py-2 pr-3">Actualización</th>
              <th class="py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="properties-tbody" class="divide-y divide-[var(--c-border)]">
            <!-- rows -->
          </tbody>
        </table>
      </div>

      <div id="properties-empty" class="hidden text-center py-12">
        <div class="mx-auto size-12 rounded-2xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-6 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M6 21V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12"/><path d="M9 21v-6h6v6"/></svg>
        </div>
        <p class="mt-3 text-[var(--c-text)] font-medium">No se encontraron propiedades</p>
        <p class="text-sm text-[var(--c-muted)]">Prueba ajustando los filtros o crea una nueva propiedad.</p>
      </div>

      <div id="properties-loading" class="hidden py-10">
        <div class="animate-pulse space-y-3">
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Drawer: Create/Edit Property -->
<div id="property-drawer" class="fixed inset-0 z-[11000] hidden" aria-modal="true" role="dialog" aria-labelledby="property-drawer-title">
  <div data-js="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

  <div class="absolute right-0 top-0 h-full w-full max-w-4xl">
    <div class="h-full bg-[var(--c-surface)] border-l border-[var(--c-border)] shadow-2xl flex flex-col">
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h3 id="property-drawer-title" class="text-lg font-semibold truncate">Nueva propiedad</h3>
          <p id="property-drawer-subtitle" class="text-xs text-[var(--c-muted)]">Completa la información por secciones</p>
        </div>
        <div class="flex items-center gap-2">
          <button id="btn-property-save" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
            Guardar
          </button>
          <button id="btn-property-close" class="p-2 rounded-xl hover:bg-[var(--c-elev)] transition" aria-label="Cerrar">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-[var(--c-border)]">
        <nav class="flex overflow-x-auto">
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium border-b-2 border-[var(--c-primary)] text-[var(--c-primary)]" data-tab="general">General</button>
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="location">Ubicación</button>
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="operations">Operaciones</button>
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="media">Medios</button>
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="meta">Features/Tags</button>
          <button class="prop-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="raw">Raw JSON</button>
        </nav>
      </div>

      <form id="property-form" class="flex-1 min-h-0 overflow-y-auto">
        <input type="hidden" id="property-id" value="" />

        <!-- Tab: General -->
        <section class="prop-tab-panel p-6 space-y-5" data-panel="general">

          <!-- Sección: Identificadores y Origen -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Origen <span class="text-red-400">*</span></label>
              <select id="field-source" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                <option value="manual">Manual</option>
                <option value="easybroker">EasyBroker</option>
                <option value="mls">MLS</option>
              </select>
            </div>

            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Agencia <span class="text-red-400">*</span></label>
              <select id="field-agency-id" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                <option value="">Selecciona una agencia…</option>
              </select>
              <p class="mt-1 text-xs text-[var(--c-muted)]">Debe existir en el catálogo.</p>
            </div>

            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Agent User (opcional)</label>
              <div class="flex gap-2">
                <input id="field-agent-user-id" type="number" min="1" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="ID de usuario" />
                <button id="btn-find-agent" type="button" class="px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Buscar</button>
              </div>
              <p id="agent-preview" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Publicado</label>
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                <input id="field-published" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                <span class="text-sm">Visible</span>
              </label>
            </div>
          </div>

          <!-- IDs EasyBroker + Tipo -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">EasyBroker Public ID</label>
              <input id="field-easybroker-public-id" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Ej: EB-123" />
            </div>

            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">EasyBroker Agent ID (opcional)</label>
              <input id="field-easybroker-agent-id" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>

            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Tipo de Propiedad</label>
              <input id="field-property-type-name" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Apartamento, Casa, Lote…" />
            </div>
          </div>

          <!-- IDs MLS -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Public ID</label>
              <input id="field-mls-public-id" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Ej: MLS-456" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS ID</label>
              <input id="field-mls-id" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Office ID</label>
              <input id="field-mls-office-id" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Neighborhood</label>
              <input id="field-mls-neighborhood" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <!-- MLS Folder + Status + Category + For Rent -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Folder Name</label>
              <input id="field-mls-folder-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Status</label>
              <select id="field-status" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                <option value="">—</option>
                <option value="For Sale">For Sale</option>
                <option value="For Rent">For Rent</option>
                <option value="Price Reduction">Price Reduction</option>
                <option value="Contract Pending">Contract Pending</option>
                <option value="Under Contract">Under Contract</option>
              </select>
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Category</label>
              <select id="field-category" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                <option value="">—</option>
                <option value="Residential">Residential</option>
                <option value="Land and Lots">Land and Lots</option>
                <option value="Commercial">Commercial</option>
                <option value="Pre Sales">Pre Sales</option>
              </select>
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">For Rent</label>
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                <input id="field-for-rent" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                <span class="text-sm">Sí</span>
              </label>
            </div>
          </div>

          <!-- Título -->
          <div>
            <label class="block text-sm font-medium mb-1">Título</label>
            <input id="field-title" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Título comercial" />
          </div>

          <!-- Descripción general -->
          <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea id="field-description" rows="5" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Descripción (texto largo)"></textarea>
          </div>

          <!-- Descripciones bilingües -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Descripción corta (EN)</label>
              <textarea id="field-description-short-en" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Short description (English)"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Descripción corta (ES)</label>
              <textarea id="field-description-short-es" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Descripción corta (Español)"></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Descripción completa (EN)</label>
              <textarea id="field-description-full-en" rows="5" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Full description (English)"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Descripción completa (ES)</label>
              <textarea id="field-description-full-es" rows="5" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Descripción completa (Español)"></textarea>
            </div>
          </div>

          <!-- URLs -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">URL</label>
              <input id="field-url" type="url" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Ad Type</label>
              <input id="field-ad-type" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="sale / rental" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Virtual Tour URL</label>
              <input id="field-virtual-tour-url" type="url" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Video URL</label>
              <input id="field-video-url" type="url" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
            </div>
          </div>

          <!-- Numéricos: dormitorios, baños, etc. -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Dormitorios</label>
              <input id="field-bedrooms" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Baños</label>
              <input id="field-bathrooms" type="number" min="0" step="0.5" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Medios baños</label>
              <input id="field-half-bathrooms" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Parqueaderos</label>
              <input id="field-parking-spaces" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Pisos (floors)</label>
              <input id="field-floors" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Floor (texto)</label>
              <input id="field-floor" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Ej: 5" />
            </div>
          </div>

          <!-- Parking number/type + Year built -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Parking #</label>
              <input id="field-parking-number" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Parking Type</label>
              <select id="field-parking-type" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                <option value="">—</option>
                <option value="Any">Any</option>
                <option value="off_street">off_street</option>
                <option value="on_street">on_street</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Año constr.</label>
              <input id="field-year-built" type="number" min="1800" max="2100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Old Price</label>
              <input id="field-old-price" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Edad</label>
              <input id="field-age" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Ej: 8 años" />
            </div>
          </div>

          <!-- Tamaños m² y pies² -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Lote (m²)</label>
              <input id="field-lot-size" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Lote (ft²)</label>
              <input id="field-lot-feet" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Construcción (m²)</label>
              <input id="field-construction-size" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Construcción (ft²)</label>
              <input id="field-construction-feet" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Gastos</label>
              <input id="field-expenses" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Largo lote</label>
              <input id="field-lot-length" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Ancho lote</label>
              <input id="field-lot-width" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <!-- Sección: Características MLS -->
          <div class="pt-4 border-t border-[var(--c-border)]">
            <h4 class="text-sm font-semibold text-[var(--c-text)] mb-4">Características MLS</h4>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
              <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-1">Furnished</label>
                <select id="field-furnished" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                  <option value="">—</option>
                  <option value="Any">Any</option>
                  <option value="yes">yes</option>
                  <option value="no">no</option>
                  <option value="partially">partially</option>
                  <option value="Optional Pkg">Optional Pkg</option>
                </select>
              </div>
              <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-1">With View</label>
                <select id="field-with-view" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                  <option value="">—</option>
                  <option value="Any">Any</option>
                  <option value="Lake">Lake</option>
                  <option value="Mountain">Mountain</option>
                  <option value="Lake and Mountain">Lake and Mountain</option>
                  <option value="Ocean">Ocean</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                  <option value="Partial">Partial</option>
                </select>
              </div>
              <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-1">Payment</label>
                <select id="field-payment" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                  <option value="">—</option>
                  <option value="Any">Any</option>
                  <option value="ALL CASH">ALL CASH</option>
                  <option value="FINANCING">FINANCING</option>
                </select>
              </div>
              <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-1">Showing Terms</label>
                <select id="field-showing-terms" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
                  <option value="">—</option>
                  <option value="Any">Any</option>
                  <option value="Appointment">Appointment</option>
                  <option value="Pick Up Keys">Pick Up Keys</option>
                  <option value="Open">Open</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4">
              <div class="md:col-span-4">
                <label class="block text-sm font-medium mb-1">Selling Office Commission</label>
                <input id="field-selling-office-commission" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Ej: 3%" />
              </div>
              <div class="md:col-span-4">
                <label class="block text-sm font-medium mb-1">Casita Bedrooms</label>
                <input id="field-casita-bedrooms" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
              </div>
              <div class="md:col-span-4">
                <label class="block text-sm font-medium mb-1">Casita Bathrooms</label>
                <input id="field-casita-bathrooms" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mt-4">
              <div>
                <label class="block text-sm font-medium mb-1">With Yard</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-with-yard" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Gated Comm</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-gated-comm" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Pool</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-pool" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Casita</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-casita" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Approved</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-is-approved" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Allow Integ.</label>
                <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                  <input id="field-allow-integration" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                  <span class="text-sm">Sí</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Fechas -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">EB created_at</label>
              <input id="field-easybroker-created-at" type="datetime-local" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">EB updated_at</label>
              <input id="field-easybroker-updated-at" type="datetime-local" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS created_at</label>
              <input id="field-mls-created-at" type="datetime-local" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS updated_at</label>
              <input id="field-mls-updated-at" type="datetime-local" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Last synced at</label>
            <input id="field-last-synced-at" type="datetime-local" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
          </div>
        </section>

        <!-- Tab: Location -->
        <section class="prop-tab-panel hidden p-6 space-y-5" data-panel="location">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Región</label>
              <input id="loc-region" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Ciudad</label>
              <input id="loc-city" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Zona</label>
              <input id="loc-city-area" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-8">
              <label class="block text-sm font-medium mb-1">Dirección</label>
              <input id="loc-street" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Código postal</label>
              <input id="loc-postal-code" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Latitud</label>
              <input id="loc-latitude" type="number" step="0.0000001" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Longitud</label>
              <input id="loc-longitude" type="number" step="0.0000001" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Mostrar ubicación exacta</label>
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                <input id="loc-show-exact" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                <span class="text-sm">Sí</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Raw payload (location) (JSON)</label>
            <textarea id="loc-raw-payload" rows="6" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] font-mono text-xs" placeholder='{"key":"value"}'></textarea>
          </div>
        </section>

        <!-- Tab: Operations -->
        <section class="prop-tab-panel hidden p-6 space-y-5" data-panel="operations">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h4 class="text-sm font-semibold">Operaciones</h4>
              <p class="text-xs text-[var(--c-muted)]">Agrega una o varias operaciones (venta, arriendo, etc.).</p>
            </div>
            <button id="btn-add-operation" type="button" class="px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">+ Agregar</button>
          </div>

          <div class="overflow-x-auto rounded-2xl border border-[var(--c-border)]">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-xs text-[var(--c-muted)] bg-[var(--c-elev)]">
                  <th class="py-2 px-3">Tipo *</th>
                  <th class="py-2 px-3">Monto</th>
                  <th class="py-2 px-3">Moneda</th>
                  <th class="py-2 px-3">Código</th>
                  <th class="py-2 px-3">Formateado</th>
                  <th class="py-2 px-3">Unidad</th>
                  <th class="py-2 px-3 text-right">—</th>
                </tr>
              </thead>
              <tbody id="operations-tbody" class="divide-y divide-[var(--c-border)]">
                <!-- rows -->
              </tbody>
            </table>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Nota</label>
            <p class="text-xs text-[var(--c-muted)]">Si envías operaciones en el update, el backend reemplaza todas las operaciones existentes por las nuevas.</p>
          </div>
        </section>

        <!-- Tab: Media -->
        <section class="prop-tab-panel hidden p-6 space-y-5" data-panel="media">
          <div>
            <label class="block text-sm font-medium mb-2">Portada (cover_media_asset_id)</label>
            <x-media-input
              name="cover_media_asset_id"
              mode="single"
              :max="1"
              placeholder="Seleccionar portada"
              button="Seleccionar portada"
              preview="true"
              columns="8"
            />
            <p class="mt-2 text-xs text-[var(--c-muted)]">La portada es un MediaAsset existente.</p>
          </div>

          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <label class="block text-sm font-medium">Galería (media)</label>
              <div class="flex items-center gap-2">
                <label class="text-xs text-[var(--c-muted)]">Rol por defecto</label>
                <select id="media-default-role" class="px-2 py-1 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-xs">
                  <option value="image" selected>image</option>
                  <option value="video">video</option>
                  <option value="document">document</option>
                </select>
              </div>
            </div>

            <x-media-input
              name="gallery_media_ids"
              mode="multiple"
              :max="24"
              placeholder="Seleccionar medios para la galería"
              button="Seleccionar galería"
              preview="true"
              columns="8"
            />
            <p class="mt-2 text-xs text-[var(--c-muted)]">En el API se enviará como <code class="px-1 rounded bg-[var(--c-elev)]">media[]</code> con rol/título/posición por defecto.</p>
          </div>
        </section>

        <!-- Tab: Meta -->
        <section class="prop-tab-panel hidden p-6 space-y-5" data-panel="meta">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
              <div class="flex items-center justify-between gap-2">
                <div>
                  <h4 class="text-sm font-semibold">Features</h4>
                  <p class="text-xs text-[var(--c-muted)]">Selecciona features para esta propiedad.</p>
                </div>
              </div>

              <div class="mt-3">
                <div class="flex items-center gap-2">
                  <input id="features-search" type="search" placeholder="Buscar features…" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />
                  <button id="features-refresh" type="button" class="px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition">↻</button>
                </div>
              </div>

              <div id="features-selected" class="mt-3 flex flex-wrap gap-2"></div>

              <div id="features-list" class="mt-3 max-h-64 overflow-auto rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-2 space-y-1">
                <!-- checkboxes -->
              </div>

              <div class="mt-3 flex items-center justify-between">
                <button id="features-prev" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition disabled:opacity-50" disabled>Anterior</button>
                <span id="features-page" class="text-xs text-[var(--c-muted)]">—</span>
                <button id="features-next" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition disabled:opacity-50" disabled>Siguiente</button>
              </div>
            </div>

            <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
              <div>
                <h4 class="text-sm font-semibold">Tags</h4>
                <p class="text-xs text-[var(--c-muted)]">Selecciona tags para esta propiedad.</p>
              </div>

              <div class="mt-3">
                <div class="flex items-center gap-2">
                  <input id="tags-search" type="search" placeholder="Buscar tags…" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />
                  <button id="tags-refresh" type="button" class="px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition">↻</button>
                </div>
              </div>

              <div id="tags-selected" class="mt-3 flex flex-wrap gap-2"></div>

              <div id="tags-list" class="mt-3 max-h-64 overflow-auto rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-2 space-y-1">
                <!-- checkboxes -->
              </div>

              <div class="mt-3 flex items-center justify-between">
                <button id="tags-prev" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition disabled:opacity-50" disabled>Anterior</button>
                <span id="tags-page" class="text-xs text-[var(--c-muted)]">—</span>
                <button id="tags-next" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition disabled:opacity-50" disabled>Siguiente</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Tab: Raw -->
        <section class="prop-tab-panel hidden p-6 space-y-5" data-panel="raw">
          <div>
            <label class="block text-sm font-medium mb-1">Raw payload (property) (JSON)</label>
            <textarea id="field-raw-payload" rows="10" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] font-mono text-xs" placeholder='{"easybroker":{...}}'></textarea>
            <p class="mt-2 text-xs text-[var(--c-muted)]">Se envía como objeto JSON. Si lo dejas vacío, se omitirá.</p>
          </div>
        </section>
      </form>

      <div class="px-6 py-4 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="text-xs text-[var(--c-muted)]" id="property-drawer-footnote">Los cambios se aplican usando el API (Passport).</div>
        <div class="flex items-center gap-2">
          <button id="btn-property-delete" type="button" class="hidden px-4 py-2 rounded-xl border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">Eliminar</button>
          <button id="btn-property-cancel" type="button" class="px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = '/api';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';

  // Permite que el botón global del header (layouts/app) ejecute esta acción.
  window.dashNewAction = () => openDrawerForCreate();

  if (!API_TOKEN) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: 'No se encontró un token de acceso válido. Por favor inicia sesión nuevamente.',
        code: 'TOKEN_MISSING',
        errors: { auth: ['Token requerido'] }
      }
    }));
    return;
  }

  // ----------------------
  // Utils
  // ----------------------
  const $ = (sel, el = document) => el.querySelector(sel);
  const $$ = (sel, el = document) => Array.from(el.querySelectorAll(sel));

  const toInt = (v) => {
    if (v === null || v === undefined) return null;
    const s = String(v).trim();
    if (!s) return null;
    const n = parseInt(s, 10);
    return Number.isFinite(n) ? n : null;
  };

  const toNum = (v) => {
    if (v === null || v === undefined) return null;
    const s = String(v).trim();
    if (!s) return null;
    const n = Number(s);
    return Number.isFinite(n) ? n : null;
  };

  const toStrOrNull = (v) => {
    const s = (v ?? '').toString().trim();
    return s === '' ? null : s;
  };

  const parseJsonOrNull = (text, fieldNameForErrors) => {
    const raw = (text ?? '').trim();
    if (!raw) return null;
    try {
      const parsed = JSON.parse(raw);
      if (parsed && typeof parsed === 'object') return parsed;
      return parsed; // permite arrays
    } catch (e) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: `JSON inválido en ${fieldNameForErrors}`,
          code: 'CLIENT_JSON_PARSE_ERROR',
          errors: { [fieldNameForErrors]: ['JSON inválido, revisa comillas/llaves.'] }
        }
      }));
      throw e;
    }
  };

  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const isMutation = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

    const headers = {
      'Accept': 'application/json',
      ...(options.headers || {}),
      'Authorization': `Bearer ${API_TOKEN}`,
    };

    if (isMutation && CSRF_TOKEN) headers['X-CSRF-TOKEN'] = CSRF_TOKEN;

    const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData;
    if (!isFormData && options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';

    const res = await fetch(url, { ...options, method, headers });
    let json = null;
    try { json = await res.clone().json(); } catch (_e) {}

    if (!res.ok) {
      const detail = {
        success: false,
        message: json?.message || res.statusText || 'Error de API',
        code: json?.code || (res.status === 422 ? 'VALIDATION_ERROR' : 'SERVER_ERROR'),
        errors: json?.errors || null,
        status: res.status,
        raw: json
      };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    return json;
  }

  // ----------------------
  // State
  // ----------------------
  const state = {
    list: {
      page: 1,
      perPage: 15,
      last: null,
    },
    refs: {
      agencies: [],
      currencies: [],
      agentPreview: null,
    },
    meta: {
      features: { page: 1, perPage: 50, last: null },
      tags: { page: 1, perPage: 50, last: null },
      selectedFeatureIds: new Set(),
      selectedFeatureMap: new Map(),
      selectedTagIds: new Set(),
      selectedTagMap: new Map(),
    }
  };

  // ----------------------
  // Filters
  // ----------------------
  const filterEls = {
    search: $('#filter-search'),
    source: $('#filter-source'),
    agency: $('#filter-agency'),
    published: $('#filter-published'),
    order: $('#filter-order'),
  };

  function getOrderParams() {
    const raw = filterEls.order.value || 'updated_at:desc';
    const [order, sort] = raw.split(':');
    return { order: order || 'updated_at', sort: (sort || 'desc') };
  }

  function buildListUrl(page) {
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', String(state.list.perPage));

    const search = filterEls.search.value.trim();
    const source = filterEls.source.value;
    const agencyId = filterEls.agency.value;
    const published = filterEls.published.value;

    if (search) p.set('search', search);
    if (source) p.set('source', source);
    if (agencyId) p.set('agency_id', agencyId);
    if (published !== '') p.set('published', published);

    const { order, sort } = getOrderParams();
    p.set('order', order);
    p.set('sort', sort);

    return `${API_BASE}/properties?${p.toString()}`;
  }

  // ----------------------
  // Render list
  // ----------------------
  function fmtDate(iso) {
    if (!iso) return '—';
    try {
      const d = new Date(iso);
      if (Number.isNaN(d.getTime())) return String(iso);
      return d.toLocaleString('es-CO', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    } catch (_e) {
      return String(iso);
    }
  }

  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function badgePublished(published) {
    if (published) {
      return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">
        <span class="size-1.5 rounded-full bg-green-500"></span> Publicado
      </span>`;
    }
    return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] text-[var(--c-muted)] border border-[var(--c-border)]">
      <span class="size-1.5 rounded-full bg-[var(--c-border)]"></span> Borrador
    </span>`;
  }

  function badgeSource(source) {
    const map = {
      'manual': { bg: 'bg-gray-100 dark:bg-gray-800/40', text: 'text-gray-700 dark:text-gray-300', dot: 'bg-gray-500', label: 'Manual' },
      'easybroker': { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-800 dark:text-blue-100', dot: 'bg-blue-500', label: 'EasyBroker' },
      'mls': { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-800 dark:text-purple-100', dot: 'bg-purple-500', label: 'MLS' },
    };
    const cfg = map[source] || map['manual'];
    return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs ${cfg.bg} ${cfg.text}">
      <span class="size-1.5 rounded-full ${cfg.dot}"></span> ${cfg.label}
    </span>`;
  }

  function renderProperties(paginated) {
    const tbody = $('#properties-tbody');
    const empty = $('#properties-empty');

    tbody.innerHTML = '';

    const items = paginated?.data || [];
    if (!items.length) {
      empty.classList.remove('hidden');
      return;
    }

    empty.classList.add('hidden');

    items.forEach((p) => {
      const title = p.title || '(Sin título)';
      const sourceVal = p.source || 'manual';
      const relevantId = sourceVal === 'mls'
        ? (p.mls_id || p.mls_public_id || '—')
        : (p.easybroker_public_id || '—');
      const agencyName = p.agency?.name || `Agencia #${p.agency_id}`;
      const typeName = p.property_type_name || '—';
      const updated = p.updated_at || p.easybroker_updated_at || null;

      const cover = p.cover_media_asset?.url ? `<img src="${escapeHtml(p.cover_media_asset.url)}" class="size-10 rounded-xl object-cover border border-[var(--c-border)]" alt="">` :
        `<div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M6 21V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12"/><path d="M9 21v-6h6v6"/></svg>
        </div>`;

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-[var(--c-elev)]/50 transition';
      tr.innerHTML = `
        <td class="py-3 pr-3">
          <div class="flex items-center gap-3">
            ${cover}
            <div class="min-w-0">
              <div class="font-medium text-[var(--c-text)] truncate">${escapeHtml(title)}</div>
              <div class="text-xs text-[var(--c-muted)] truncate">ID: ${escapeHtml(relevantId)} • #${escapeHtml(p.id)}</div>
            </div>
          </div>
        </td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(agencyName)}</td>
        <td class="py-3 pr-3">${badgeSource(sourceVal)}</td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(typeName)}</td>
        <td class="py-3 pr-3">${badgePublished(!!p.published)}</td>
        <td class="py-3 pr-3 text-[var(--c-muted)]">${escapeHtml(fmtDate(updated))}</td>
        <td class="py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button class="btn-edit-prop px-3 py-1.5 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition" data-id="${p.id}">Editar</button>
            <button class="btn-del-prop px-3 py-1.5 text-xs rounded-lg bg-[var(--c-danger)] text-white hover:opacity-90 transition" data-id="${p.id}">Eliminar</button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    $$('.btn-edit-prop', tbody).forEach(btn => {
      btn.addEventListener('click', () => openDrawerForEdit(btn.dataset.id));
    });
    $$('.btn-del-prop', tbody).forEach(btn => {
      btn.addEventListener('click', () => deleteProperty(btn.dataset.id));
    });
  }

  function renderPager(paginated) {
    const countEl = $('#properties-count');
    const pageEl = $('#properties-page');
    const prevBtn = $('#btn-prev-page');
    const nextBtn = $('#btn-next-page');

    const total = paginated?.total ?? 0;
    const current = paginated?.current_page ?? 1;
    const last = paginated?.last_page ?? 1;

    countEl.textContent = `${total} total`;
    pageEl.textContent = `Página ${current} de ${last}`;

    prevBtn.disabled = !(paginated?.prev_page_url);
    nextBtn.disabled = !(paginated?.next_page_url);
  }

  function setLoading(on) {
    $('#properties-loading').classList.toggle('hidden', !on);
    $('#properties-tbody').classList.toggle('opacity-50', on);
  }

  async function loadProperties(page = 1) {
    state.list.page = page;
    setLoading(true);
    try {
      const payload = await apiFetch(buildListUrl(page));
      if (payload?.success) {
        state.list.last = payload.data;
        renderProperties(payload.data);
        renderPager(payload.data);
      } else {
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } finally {
      setLoading(false);
    }
  }

  // ----------------------
  // Refs (agencies, currencies)
  // ----------------------
  async function loadAgenciesInto(selectEl) {
    const payload = await apiFetch(`${API_BASE}/agencies?per_page=100&order=updated_at&sort=desc`);
    const rows = payload?.data?.data || [];
    state.refs.agencies = rows;

    const current = selectEl.value;
    selectEl.innerHTML = selectEl.id === 'filter-agency'
      ? '<option value="">Todas</option>'
      : '<option value="">Selecciona una agencia…</option>';

    rows.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id;
      opt.textContent = `${a.name} (#${a.id})`;
      selectEl.appendChild(opt);
    });

    if (current) selectEl.value = current;
  }

  async function loadCurrencies() {
    const payload = await apiFetch(`${API_BASE}/currencies?per_page=100&order=created_at&sort=desc`);
    const rows = payload?.data?.data || [];
    state.refs.currencies = rows;
  }

  // ----------------------
  // Drawer + Tabs
  // ----------------------
  const drawer = $('#property-drawer');
  const drawerOverlay = drawer.querySelector('[data-js="overlay"]');

  function openDrawer() {
    drawer.classList.remove('hidden');
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawer.classList.add('hidden');
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
  }

  function setActiveTab(tab) {
    $$('.prop-tab-btn').forEach(btn => {
      const active = btn.dataset.tab === tab;
      btn.classList.toggle('border-b-2', active);
      btn.classList.toggle('border-[var(--c-primary)]', active);
      btn.classList.toggle('text-[var(--c-primary)]', active);
      btn.classList.toggle('text-[var(--c-muted)]', !active);
    });

    $$('.prop-tab-panel').forEach(panel => {
      panel.classList.toggle('hidden', panel.dataset.panel !== tab);
    });
  }

  $$('.prop-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => setActiveTab(btn.dataset.tab));
  });

  // ----------------------
  // Operations UI
  // ----------------------
  function currencyOptionsHtml(selectedId) {
    const opts = ['<option value="">—</option>'];
    state.refs.currencies.forEach(c => {
      const sel = String(c.id) === String(selectedId) ? 'selected' : '';
      opts.push(`<option value="${c.id}" ${sel}>${escapeHtml(c.code)} • ${escapeHtml(c.name)}</option>`);
    });
    return opts.join('');
  }

  function addOperationRow(data = {}) {
    const tbody = $('#operations-tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 px-3">
        <input data-op="operation_type" type="text" maxlength="20" class="w-40 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)]" placeholder="sale" value="${escapeHtml(data.operation_type || '')}" />
      </td>
      <td class="py-2 px-3">
        <input data-op="amount" type="number" step="0.01" class="w-36 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)]" value="${data.amount ?? ''}" />
      </td>
      <td class="py-2 px-3">
        <select data-op="currency_id" class="w-56 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)]">
          ${currencyOptionsHtml(data.currency_id)}
        </select>
      </td>
      <td class="py-2 px-3">
        <input data-op="currency_code" type="text" maxlength="3" class="w-20 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] uppercase" value="${escapeHtml(data.currency_code || '')}" />
      </td>
      <td class="py-2 px-3">
        <input data-op="formatted_amount" type="text" maxlength="50" class="w-40 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)]" value="${escapeHtml(data.formatted_amount || '')}" />
      </td>
      <td class="py-2 px-3">
        <input data-op="unit" type="text" maxlength="20" class="w-24 px-2 py-1.5 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)]" value="${escapeHtml(data.unit || '')}" />
      </td>
      <td class="py-2 px-3 text-right">
        <button type="button" class="btn-remove-op px-2 py-1 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Quitar</button>
      </td>
    `;

    tr.querySelector('.btn-remove-op').addEventListener('click', () => tr.remove());
    tbody.appendChild(tr);
  }

  function getOperationsPayload() {
    const rows = $$('#operations-tbody tr');
    const ops = [];
    rows.forEach(tr => {
      const get = (k) => tr.querySelector(`[data-op="${k}"]`)?.value;
      const operation_type = toStrOrNull(get('operation_type'));
      const amount = toNum(get('amount'));
      const currency_id = toInt(get('currency_id'));
      const currency_code = toStrOrNull(get('currency_code'));
      const formatted_amount = toStrOrNull(get('formatted_amount'));
      const unit = toStrOrNull(get('unit'));

      // Skip completely empty rows
      if (!operation_type && amount === null && !currency_id && !currency_code && !formatted_amount && !unit) return;

      // Respect backend rule: operation_type required_with:operations
      if (!operation_type) {
        throw new Error('operation_type requerido en operaciones');
      }

      ops.push({
        operation_type,
        amount,
        currency_id,
        currency_code,
        formatted_amount,
        unit,
      });
    });
    return ops.length ? ops : null;
  }

  // ----------------------
  // Features/Tags UI
  // ----------------------
  function pill(label, onRemove) {
    const el = document.createElement('span');
    el.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs bg-[var(--c-surface)] border border-[var(--c-border)]';
    el.innerHTML = `<span class="truncate max-w-[220px]">${escapeHtml(label)}</span><button type="button" class="px-1.5 py-0.5 rounded-lg hover:bg-[var(--c-elev)]" aria-label="Quitar">✕</button>`;
    el.querySelector('button').addEventListener('click', onRemove);
    return el;
  }

  function renderSelectedMeta() {
    const fWrap = $('#features-selected');
    const tWrap = $('#tags-selected');

    fWrap.innerHTML = '';
    Array.from(state.meta.selectedFeatureIds).slice(0, 50).forEach(id => {
      const label = state.meta.selectedFeatureMap.get(String(id)) || `Feature #${id}`;
      fWrap.appendChild(pill(label, () => {
        state.meta.selectedFeatureIds.delete(String(id));
        state.meta.selectedFeatureMap.delete(String(id));
        renderSelectedMeta();
        // sync checkbox (si está visible)
        const cb = document.querySelector(`#features-list input[type="checkbox"][data-id="${id}"]`);
        if (cb) cb.checked = false;
      }));
    });

    tWrap.innerHTML = '';
    Array.from(state.meta.selectedTagIds).slice(0, 50).forEach(id => {
      const label = state.meta.selectedTagMap.get(String(id)) || `Tag #${id}`;
      tWrap.appendChild(pill(label, () => {
        state.meta.selectedTagIds.delete(String(id));
        state.meta.selectedTagMap.delete(String(id));
        renderSelectedMeta();
        const cb = document.querySelector(`#tags-list input[type="checkbox"][data-id="${id}"]`);
        if (cb) cb.checked = false;
      }));
    });
  }

  function renderMetaList({ type, paginated }) {
    const list = type === 'features' ? $('#features-list') : $('#tags-list');
    const pageEl = type === 'features' ? $('#features-page') : $('#tags-page');
    const prev = type === 'features' ? $('#features-prev') : $('#tags-prev');
    const next = type === 'features' ? $('#features-next') : $('#tags-next');

    const items = paginated?.data || [];

    list.innerHTML = '';
    if (!items.length) {
      list.innerHTML = `<div class="text-center text-sm text-[var(--c-muted)] py-6">Sin resultados</div>`;
    } else {
      items.forEach(it => {
        const id = String(it.id);
        const checked = type === 'features'
          ? state.meta.selectedFeatureIds.has(id)
          : state.meta.selectedTagIds.has(id);

        const row = document.createElement('label');
        row.className = 'flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-[var(--c-elev)] transition cursor-pointer';
        row.innerHTML = `
          <input type="checkbox" class="w-4 h-4 text-[var(--c-primary)] bg-[var(--c-surface)] border-[var(--c-border)] rounded focus:ring-[var(--c-primary)] focus:ring-2" ${checked ? 'checked' : ''} data-id="${escapeHtml(id)}" />
          <span class="text-sm text-[var(--c-text)]">${escapeHtml(it.name || it.slug || ('#' + it.id))}</span>
          ${type === 'features' && it.locale ? `<span class="ml-auto text-xs text-[var(--c-muted)]">${escapeHtml(it.locale)}</span>` : ''}
        `;

        const cb = row.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', () => {
          if (type === 'features') {
            if (cb.checked) {
              state.meta.selectedFeatureIds.add(id);
              state.meta.selectedFeatureMap.set(id, it.name || ('Feature #' + id));
            } else {
              state.meta.selectedFeatureIds.delete(id);
              state.meta.selectedFeatureMap.delete(id);
            }
          } else {
            if (cb.checked) {
              state.meta.selectedTagIds.add(id);
              state.meta.selectedTagMap.set(id, it.name || ('Tag #' + id));
            } else {
              state.meta.selectedTagIds.delete(id);
              state.meta.selectedTagMap.delete(id);
            }
          }
          renderSelectedMeta();
        });

        list.appendChild(row);
      });
    }

    const current = paginated?.current_page ?? 1;
    const last = paginated?.last_page ?? 1;
    pageEl.textContent = `Página ${current} de ${last}`;
    prev.disabled = !(paginated?.prev_page_url);
    next.disabled = !(paginated?.next_page_url);
  }

  async function loadFeatures(page = 1) {
    state.meta.features.page = page;
    const q = $('#features-search').value.trim();
    const p = new URLSearchParams({
      page: String(page),
      per_page: String(state.meta.features.perPage),
      order: 'name',
      sort: 'asc',
    });
    if (q) p.set('search', q);

    const payload = await apiFetch(`${API_BASE}/features?${p.toString()}`);
    state.meta.features.last = payload?.data;
    renderMetaList({ type: 'features', paginated: payload?.data });
  }

  async function loadTags(page = 1) {
    state.meta.tags.page = page;
    const q = $('#tags-search').value.trim();
    const p = new URLSearchParams({
      page: String(page),
      per_page: String(state.meta.tags.perPage),
      order: 'name',
      sort: 'asc',
    });
    if (q) p.set('search', q);

    const payload = await apiFetch(`${API_BASE}/tags?${p.toString()}`);
    state.meta.tags.last = payload?.data;
    renderMetaList({ type: 'tags', paginated: payload?.data });
  }

  // ----------------------
  // Drawer: populate / reset
  // ----------------------
  function resetForm() {
    $('#property-id').value = '';

    $('#property-drawer-title').textContent = 'Nueva propiedad';
    $('#property-drawer-subtitle').textContent = 'Completa la información por secciones';

    $('#btn-property-delete').classList.add('hidden');

    // Source + Agency + Agent + Published
    $('#field-source').value = 'manual';
    $('#field-agency-id').value = '';
    $('#field-agent-user-id').value = '';
    $('#agent-preview').textContent = '—';
    $('#field-published').checked = false;

    // EasyBroker IDs
    $('#field-easybroker-public-id').value = '';
    $('#field-easybroker-agent-id').value = '';
    $('#field-property-type-name').value = '';

    // MLS IDs
    $('#field-mls-public-id').value = '';
    $('#field-mls-id').value = '';
    $('#field-mls-office-id').value = '';
    $('#field-mls-neighborhood').value = '';
    $('#field-mls-folder-name').value = '';

    // Status fields
    $('#field-status').value = '';
    $('#field-category').value = '';
    $('#field-for-rent').checked = false;

    // Content
    $('#field-title').value = '';
    $('#field-description').value = '';
    $('#field-description-short-en').value = '';
    $('#field-description-full-en').value = '';
    $('#field-description-short-es').value = '';
    $('#field-description-full-es').value = '';

    // URLs
    $('#field-url').value = '';
    $('#field-ad-type').value = '';
    $('#field-virtual-tour-url').value = '';
    $('#field-video-url').value = '';

    // Numeric
    $('#field-bedrooms').value = '';
    $('#field-bathrooms').value = '';
    $('#field-half-bathrooms').value = '';
    $('#field-parking-spaces').value = '';
    $('#field-floors').value = '';
    $('#field-floor').value = '';
    $('#field-parking-number').value = '';
    $('#field-parking-type').value = '';
    $('#field-year-built').value = '';
    $('#field-old-price').value = '';
    $('#field-age').value = '';

    // Sizes
    $('#field-lot-size').value = '';
    $('#field-lot-feet').value = '';
    $('#field-construction-size').value = '';
    $('#field-construction-feet').value = '';
    $('#field-expenses').value = '';
    $('#field-lot-length').value = '';
    $('#field-lot-width').value = '';

    // MLS Characteristics
    $('#field-furnished').value = '';
    $('#field-with-view').value = '';
    $('#field-payment').value = '';
    $('#field-showing-terms').value = '';
    $('#field-selling-office-commission').value = '';
    $('#field-casita-bedrooms').value = '';
    $('#field-casita-bathrooms').value = '';
    $('#field-with-yard').checked = false;
    $('#field-gated-comm').checked = false;
    $('#field-pool').checked = false;
    $('#field-casita').checked = false;
    $('#field-is-approved').checked = false;
    $('#field-allow-integration').checked = false;

    // Dates
    $('#field-easybroker-created-at').value = '';
    $('#field-easybroker-updated-at').value = '';
    $('#field-mls-created-at').value = '';
    $('#field-mls-updated-at').value = '';
    $('#field-last-synced-at').value = '';

    // Location
    $('#loc-region').value = '';
    $('#loc-city').value = '';
    $('#loc-city-area').value = '';
    $('#loc-street').value = '';
    $('#loc-postal-code').value = '';
    $('#loc-latitude').value = '';
    $('#loc-longitude').value = '';
    $('#loc-show-exact').checked = false;
    $('#loc-raw-payload').value = '';

    // Raw
    $('#field-raw-payload').value = '';

    // Operations
    $('#operations-tbody').innerHTML = '';

    // Media inputs
    const coverInput = document.querySelector('input[name="cover_media_asset_id"]');
    const galleryInput = document.querySelector('input[name="gallery_media_ids"]');
    if (coverInput) {
      coverInput.value = '';
      coverInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (galleryInput) {
      galleryInput.value = '';
      galleryInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    $('#media-default-role').value = 'image';

    // Meta selection
    state.meta.selectedFeatureIds = new Set();
    state.meta.selectedFeatureMap = new Map();
    state.meta.selectedTagIds = new Set();
    state.meta.selectedTagMap = new Map();
    renderSelectedMeta();
  }

  function dtLocalValue(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';

    const pad = (n) => String(n).padStart(2, '0');
    const yyyy = d.getFullYear();
    const mm = pad(d.getMonth() + 1);
    const dd = pad(d.getDate());
    const hh = pad(d.getHours());
    const mi = pad(d.getMinutes());
    return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
  }

  function fillFromProperty(p) {
    $('#property-id').value = p.id;

    $('#property-drawer-title').textContent = `Editar propiedad #${p.id}`;
    const subtitleId = p.source === 'mls'
      ? (p.mls_id || p.mls_public_id || '—')
      : (p.easybroker_public_id || '—');
    $('#property-drawer-subtitle').textContent = `${subtitleId} • ${p.title || '(Sin título)'}`;

    $('#btn-property-delete').classList.remove('hidden');

    // Source + Agency + Agent + Published
    $('#field-source').value = p.source || 'manual';
    $('#field-agency-id').value = p.agency_id ?? '';
    $('#field-agent-user-id').value = p.agent_user_id ?? '';

    if (p.agent_user) {
      $('#agent-preview').textContent = `${p.agent_user.name || 'Usuario'} (${p.agent_user.email || '—'})`;
    } else {
      $('#agent-preview').textContent = '—';
    }

    $('#field-published').checked = !!p.published;

    // EasyBroker IDs
    $('#field-easybroker-public-id').value = p.easybroker_public_id ?? '';
    $('#field-easybroker-agent-id').value = p.easybroker_agent_id ?? '';
    $('#field-property-type-name').value = p.property_type_name ?? '';

    // MLS IDs
    $('#field-mls-public-id').value = p.mls_public_id ?? '';
    $('#field-mls-id').value = p.mls_id ?? '';
    $('#field-mls-office-id').value = p.mls_office_id ?? '';
    $('#field-mls-neighborhood').value = p.mls_neighborhood ?? '';
    $('#field-mls-folder-name').value = p.mls_folder_name ?? '';

    // Status fields
    $('#field-status').value = p.status ?? '';
    $('#field-category').value = p.category ?? '';
    $('#field-for-rent').checked = !!p.for_rent;

    // Content
    $('#field-title').value = p.title ?? '';
    $('#field-description').value = p.description ?? '';
    $('#field-description-short-en').value = p.description_short_en ?? '';
    $('#field-description-full-en').value = p.description_full_en ?? '';
    $('#field-description-short-es').value = p.description_short_es ?? '';
    $('#field-description-full-es').value = p.description_full_es ?? '';

    // URLs
    $('#field-url').value = p.url ?? '';
    $('#field-ad-type').value = p.ad_type ?? '';
    $('#field-virtual-tour-url').value = p.virtual_tour_url ?? '';
    $('#field-video-url').value = p.video_url ?? '';

    // Numeric
    $('#field-bedrooms').value = p.bedrooms ?? '';
    $('#field-bathrooms').value = p.bathrooms ?? '';
    $('#field-half-bathrooms').value = p.half_bathrooms ?? '';
    $('#field-parking-spaces').value = p.parking_spaces ?? '';
    $('#field-floors').value = p.floors ?? '';
    $('#field-floor').value = p.floor ?? '';
    $('#field-parking-number').value = p.parking_number ?? '';
    $('#field-parking-type').value = p.parking_type ?? '';
    $('#field-year-built').value = p.year_built ?? '';
    $('#field-old-price').value = p.old_price ?? '';
    $('#field-age').value = p.age ?? '';

    // Sizes
    $('#field-lot-size').value = p.lot_size ?? '';
    $('#field-lot-feet').value = p.lot_feet ?? '';
    $('#field-construction-size').value = p.construction_size ?? '';
    $('#field-construction-feet').value = p.construction_feet ?? '';
    $('#field-expenses').value = p.expenses ?? '';
    $('#field-lot-length').value = p.lot_length ?? '';
    $('#field-lot-width').value = p.lot_width ?? '';

    // MLS Characteristics
    $('#field-furnished').value = p.furnished ?? '';
    $('#field-with-view').value = p.with_view ?? '';
    $('#field-payment').value = p.payment ?? '';
    $('#field-showing-terms').value = p.showing_terms ?? '';
    $('#field-selling-office-commission').value = p.selling_office_commission ?? '';
    $('#field-casita-bedrooms').value = p.casita_bedrooms ?? '';
    $('#field-casita-bathrooms').value = p.casita_bathrooms ?? '';
    $('#field-with-yard').checked = !!p.with_yard;
    $('#field-gated-comm').checked = !!p.gated_comm;
    $('#field-pool').checked = !!p.pool;
    $('#field-casita').checked = !!p.casita;
    $('#field-is-approved').checked = !!p.is_approved;
    $('#field-allow-integration').checked = !!p.allow_integration;

    // Dates
    $('#field-easybroker-created-at').value = dtLocalValue(p.easybroker_created_at);
    $('#field-easybroker-updated-at').value = dtLocalValue(p.easybroker_updated_at);
    $('#field-mls-created-at').value = dtLocalValue(p.mls_created_at);
    $('#field-mls-updated-at').value = dtLocalValue(p.mls_updated_at);
    $('#field-last-synced-at').value = dtLocalValue(p.last_synced_at);

    // Location
    const loc = p.location || null;
    if (loc) {
      $('#loc-region').value = loc.region ?? '';
      $('#loc-city').value = loc.city ?? '';
      $('#loc-city-area').value = loc.city_area ?? '';
      $('#loc-street').value = loc.street ?? '';
      $('#loc-postal-code').value = loc.postal_code ?? '';
      $('#loc-latitude').value = loc.latitude ?? '';
      $('#loc-longitude').value = loc.longitude ?? '';
      $('#loc-show-exact').checked = !!loc.show_exact_location;
      $('#loc-raw-payload').value = loc.raw_payload ? JSON.stringify(loc.raw_payload, null, 2) : '';
    }

    // Operations
    $('#operations-tbody').innerHTML = '';
    (p.operations || []).forEach(op => addOperationRow(op));

    // Media
    const coverInput = document.querySelector('input[name="cover_media_asset_id"]');
    if (coverInput) {
      coverInput.value = p.cover_media_asset_id ?? '';
      coverInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const galleryInput = document.querySelector('input[name="gallery_media_ids"]');
    if (galleryInput) {
      const ids = (p.media_assets || p.mediaAssets || []).map(m => m.id).filter(Boolean);
      galleryInput.value = ids.join(',');
      galleryInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Features & tags
    state.meta.selectedFeatureIds = new Set((p.features || []).map(f => String(f.id)));
    state.meta.selectedFeatureMap = new Map((p.features || []).map(f => [String(f.id), f.name || ('Feature #' + f.id)]));

    state.meta.selectedTagIds = new Set((p.tags || []).map(t => String(t.id)));
    state.meta.selectedTagMap = new Map((p.tags || []).map(t => [String(t.id), t.name || ('Tag #' + t.id)]));

    renderSelectedMeta();

    // Raw
    $('#field-raw-payload').value = p.raw_payload ? JSON.stringify(p.raw_payload, null, 2) : '';
  }

  async function openDrawerForCreate() {
    await ensureRefsLoaded();
    resetForm();
    setActiveTab('general');
    openDrawer();

    // Cargar listas meta iniciales
    await Promise.all([loadFeatures(1), loadTags(1)]);
  }

  async function openDrawerForEdit(id) {
    await ensureRefsLoaded();
    resetForm();
    setActiveTab('general');
    openDrawer();

    const payload = await apiFetch(`${API_BASE}/properties/${id}`);
    if (payload?.success) {
      fillFromProperty(payload.data);
      await Promise.all([loadFeatures(1), loadTags(1)]);
    } else {
      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    }
  }

  async function ensureRefsLoaded() {
    // Agencies
    if (!state.refs.agencies.length) {
      await loadAgenciesInto($('#filter-agency'));
    }
    await loadAgenciesInto($('#field-agency-id'));

    // Currencies
    if (!state.refs.currencies.length) {
      await loadCurrencies();
    }
  }

  // ----------------------
  // Save/Delete
  // ----------------------
  function buildPayloadFromForm() {
    const dtToIso = (selector) => {
      const v = toStrOrNull($(selector).value);
      return v ? new Date(v).toISOString() : null;
    };

    const payload = {
      agency_id: toInt($('#field-agency-id').value),
      source: toStrOrNull($('#field-source').value) || 'manual',
      agent_user_id: toInt($('#field-agent-user-id').value),
      easybroker_public_id: toStrOrNull($('#field-easybroker-public-id').value),
      easybroker_agent_id: toStrOrNull($('#field-easybroker-agent-id').value),

      // MLS IDs
      mls_public_id: toStrOrNull($('#field-mls-public-id').value),
      mls_id: toStrOrNull($('#field-mls-id').value),
      mls_office_id: toStrOrNull($('#field-mls-office-id').value),
      mls_neighborhood: toStrOrNull($('#field-mls-neighborhood').value),
      mls_folder_name: toStrOrNull($('#field-mls-folder-name').value),

      // Status
      published: $('#field-published').checked,
      status: toStrOrNull($('#field-status').value),
      category: toStrOrNull($('#field-category').value),
      for_rent: $('#field-for-rent').checked,

      // Dates
      easybroker_created_at: dtToIso('#field-easybroker-created-at'),
      easybroker_updated_at: dtToIso('#field-easybroker-updated-at'),
      mls_created_at: dtToIso('#field-mls-created-at'),
      mls_updated_at: dtToIso('#field-mls-updated-at'),
      last_synced_at: dtToIso('#field-last-synced-at'),

      // Content
      title: toStrOrNull($('#field-title').value),
      description: toStrOrNull($('#field-description').value),
      description_short_en: toStrOrNull($('#field-description-short-en').value),
      description_full_en: toStrOrNull($('#field-description-full-en').value),
      description_short_es: toStrOrNull($('#field-description-short-es').value),
      description_full_es: toStrOrNull($('#field-description-full-es').value),
      url: toStrOrNull($('#field-url').value),
      ad_type: toStrOrNull($('#field-ad-type').value),
      property_type_name: toStrOrNull($('#field-property-type-name').value),

      // Numeric
      bedrooms: toInt($('#field-bedrooms').value),
      bathrooms: toNum($('#field-bathrooms').value),
      half_bathrooms: toInt($('#field-half-bathrooms').value),
      parking_spaces: toInt($('#field-parking-spaces').value),
      parking_number: toInt($('#field-parking-number').value),
      parking_type: toStrOrNull($('#field-parking-type').value),

      // Sizes
      lot_size: toNum($('#field-lot-size').value),
      lot_feet: toNum($('#field-lot-feet').value),
      construction_size: toNum($('#field-construction-size').value),
      construction_feet: toNum($('#field-construction-feet').value),
      expenses: toNum($('#field-expenses').value),
      old_price: toNum($('#field-old-price').value),
      lot_length: toNum($('#field-lot-length').value),
      lot_width: toNum($('#field-lot-width').value),

      floors: toInt($('#field-floors').value),
      floor: toStrOrNull($('#field-floor').value),
      age: toStrOrNull($('#field-age').value),
      year_built: toInt($('#field-year-built').value),

      // MLS characteristics
      furnished: toStrOrNull($('#field-furnished').value),
      with_yard: $('#field-with-yard').checked,
      with_view: toStrOrNull($('#field-with-view').value),
      gated_comm: $('#field-gated-comm').checked,
      pool: $('#field-pool').checked,
      casita: $('#field-casita').checked,
      casita_bedrooms: toStrOrNull($('#field-casita-bedrooms').value),
      casita_bathrooms: toStrOrNull($('#field-casita-bathrooms').value),
      payment: toStrOrNull($('#field-payment').value),
      selling_office_commission: toStrOrNull($('#field-selling-office-commission').value),
      showing_terms: toStrOrNull($('#field-showing-terms').value),
      is_approved: $('#field-is-approved').checked,
      allow_integration: $('#field-allow-integration').checked,

      // URLs
      virtual_tour_url: toStrOrNull($('#field-virtual-tour-url').value),
      video_url: toStrOrNull($('#field-video-url').value),

      cover_media_asset_id: toInt(document.querySelector('input[name="cover_media_asset_id"]')?.value),

      raw_payload: parseJsonOrNull($('#field-raw-payload').value, 'raw_payload'),
    };

    // Location (solo si hay algo)
    const location = {
      region: toStrOrNull($('#loc-region').value),
      city: toStrOrNull($('#loc-city').value),
      city_area: toStrOrNull($('#loc-city-area').value),
      street: toStrOrNull($('#loc-street').value),
      postal_code: toStrOrNull($('#loc-postal-code').value),
      show_exact_location: $('#loc-show-exact').checked,
      latitude: toNum($('#loc-latitude').value),
      longitude: toNum($('#loc-longitude').value),
      raw_payload: parseJsonOrNull($('#loc-raw-payload').value, 'location.raw_payload'),
    };
    const hasLoc = Object.values(location).some(v => v !== null && v !== undefined && v !== '' && v !== false);
    if (hasLoc) payload.location = location;

    // Operations
    try {
      const ops = getOperationsPayload();
      if (ops) payload.operations = ops;
    } catch (_e) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: 'Revisa operaciones: falta operation_type en una fila.',
          code: 'CLIENT_VALIDATION',
          errors: { operations: ['operation_type es requerido cuando envías operaciones'] }
        }
      }));
      throw _e;
    }

    // Features/Tags
    const featureIds = Array.from(state.meta.selectedFeatureIds).map(id => parseInt(id, 10)).filter(n => Number.isFinite(n));
    const tagIds = Array.from(state.meta.selectedTagIds).map(id => parseInt(id, 10)).filter(n => Number.isFinite(n));

    if (featureIds.length) payload.feature_ids = featureIds;
    if (tagIds.length) payload.tag_ids = tagIds;

    // Media (galería)
    const galleryRaw = document.querySelector('input[name="gallery_media_ids"]')?.value || '';
    const mediaIds = galleryRaw.split(',').map(s => s.trim()).filter(Boolean).map(s => parseInt(s, 10)).filter(n => Number.isFinite(n));

    if (mediaIds.length) {
      const role = $('#media-default-role').value || 'image';
      payload.media = mediaIds.map((id, idx) => ({
        media_asset_id: id,
        role,
        title: null,
        position: idx,
      }));
    }

    return payload;
  }

  async function saveProperty() {
    const id = $('#property-id').value;

    let payload;
    try {
      payload = buildPayloadFromForm();
    } catch (_e) {
      return;
    }

    const isEdit = !!id;

    // Regla mínima de cliente: solo requerimos agency_id
    if (!payload.agency_id) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: 'Agencia es obligatoria.',
          code: 'CLIENT_VALIDATION',
          errors: { agency_id: ['Requerido'] }
        }
      }));
      return;
    }

    const url = isEdit ? `${API_BASE}/properties/${id}` : `${API_BASE}/properties`;
    const method = isEdit ? 'PATCH' : 'POST';

    const res = await apiFetch(url, {
      method,
      body: JSON.stringify(payload),
      headers: { 'Content-Type': 'application/json' }
    });

    if (res?.success) {
      window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
      closeDrawer();
      await loadProperties(state.list.page);
    } else {
      window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
    }
  }

  async function deleteProperty(id) {
    if (!confirm('¿Eliminar esta propiedad? Esta acción no se puede deshacer.')) return;

    const res = await apiFetch(`${API_BASE}/properties/${id}`, { method: 'DELETE' });
    if (res?.success) {
      window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
      await loadProperties(state.list.page);
    } else {
      window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
    }
  }

  // ----------------------
  // Agent quick lookup
  // ----------------------
  async function findAgentById() {
    const id = toInt($('#field-agent-user-id').value);
    if (!id) {
      $('#agent-preview').textContent = '—';
      return;
    }

    const payload = await apiFetch(`${API_BASE}/users/${id}`);
    if (payload?.success) {
      const u = payload.data;
      $('#agent-preview').textContent = `${u.name || 'Usuario'} (${u.email || '—'})`;
    }
  }

  // ----------------------
  // Events
  // ----------------------
  $('#btn-create-property').addEventListener('click', openDrawerForCreate);
  $('#btn-refresh-properties').addEventListener('click', () => loadProperties(1));

  // Pagination
  $('#btn-prev-page').addEventListener('click', () => {
    const current = state.list.last?.current_page || 1;
    if (current > 1) loadProperties(current - 1);
  });
  $('#btn-next-page').addEventListener('click', () => {
    const current = state.list.last?.current_page || 1;
    loadProperties(current + 1);
  });

  // Drawer close
  drawerOverlay.addEventListener('click', closeDrawer);
  $('#btn-property-close').addEventListener('click', closeDrawer);
  $('#btn-property-cancel').addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !drawer.classList.contains('hidden')) closeDrawer();
  });

  // Save
  $('#btn-property-save').addEventListener('click', (e) => {
    e.preventDefault();
    saveProperty();
  });

  // Delete from drawer
  $('#btn-property-delete').addEventListener('click', async () => {
    const id = $('#property-id').value;
    if (!id) return;
    await deleteProperty(id);
    closeDrawer();
  });

  // Operations
  $('#btn-add-operation').addEventListener('click', () => addOperationRow({}));

  // Agent lookup
  $('#btn-find-agent').addEventListener('click', findAgentById);

  // Filters
  const debounce = (fn, wait = 300) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  };

  filterEls.search.addEventListener('input', debounce(() => loadProperties(1), 300));
  filterEls.source.addEventListener('change', () => loadProperties(1));
  filterEls.agency.addEventListener('change', () => loadProperties(1));
  filterEls.published.addEventListener('change', () => loadProperties(1));
  filterEls.order.addEventListener('change', () => loadProperties(1));

  // Meta
  $('#features-refresh').addEventListener('click', () => loadFeatures(1));
  $('#tags-refresh').addEventListener('click', () => loadTags(1));
  $('#features-search').addEventListener('input', debounce(() => loadFeatures(1), 300));
  $('#tags-search').addEventListener('input', debounce(() => loadTags(1), 300));

  $('#features-prev').addEventListener('click', () => {
    const curr = state.meta.features.last?.current_page || 1;
    if (curr > 1) loadFeatures(curr - 1);
  });
  $('#features-next').addEventListener('click', () => {
    const curr = state.meta.features.last?.current_page || 1;
    loadFeatures(curr + 1);
  });

  $('#tags-prev').addEventListener('click', () => {
    const curr = state.meta.tags.last?.current_page || 1;
    if (curr > 1) loadTags(curr - 1);
  });
  $('#tags-next').addEventListener('click', () => {
    const curr = state.meta.tags.last?.current_page || 1;
    loadTags(curr + 1);
  });

  // Init
  (async () => {
    await loadAgenciesInto($('#filter-agency'));
    await loadProperties(1);
  })();
});
</script>
@endsection
