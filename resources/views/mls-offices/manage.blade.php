@extends('layouts.app')

@section('title', 'Administrar Agencias MLS')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Agencias MLS</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona las oficinas/agencias del MLS AMPI San Miguel de Allende</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-sync-offices" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Sincronizar agencias
      </button>

      <button id="btn-create-office" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nueva agencia
      </button>

      <button id="btn-refresh-offices" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
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
        <div class="lg:col-span-5">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <div class="flex items-center gap-2 rounded-xl bg-[var(--c-elev)] px-3 py-2 ring-1 ring-[var(--c-border)] focus-within:ring-[var(--c-primary)]">
            <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input id="filter-search" type="search" placeholder="Nombre, email, ciudad, MLS ID…" class="bg-transparent outline-none w-full text-sm placeholder:text-[var(--c-muted)]" />
          </div>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Paid</label>
          <select id="filter-paid" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="1">Paid</option>
            <option value="0">Free</option>
          </select>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">A nuestro cargo</label>
          <select id="filter-managed" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Orden</label>
          <select id="filter-order" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="name:asc" selected>Nombre (A–Z)</option>
            <option value="name:desc">Nombre (Z–A)</option>
            <option value="updated_at:desc">Actualizado (desc)</option>
            <option value="updated_at:asc">Actualizado (asc)</option>
            <option value="mls_office_id:asc">MLS ID (asc)</option>
            <option value="mls_office_id:desc">MLS ID (desc)</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
          <span id="offices-count">—</span>
          <span class="opacity-60">•</span>
          <span id="offices-page">—</span>
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
              <th class="py-2 pr-3">Agencia</th>
              <th class="py-2 pr-3">Ciudad</th>
              <th class="py-2 pr-3">Email</th>
              <th class="py-2 pr-3">MLS ID</th>
              <th class="py-2 pr-3">Paid</th>
              <th class="py-2 pr-3">A cargo</th>
              <th class="py-2 pr-3">Agentes</th>
              <th class="py-2 pr-3">Propiedades</th>
              <th class="py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="offices-tbody" class="divide-y divide-[var(--c-border)]">
          </tbody>
        </table>
      </div>

      <div id="offices-empty" class="hidden text-center py-12">
        <div class="mx-auto size-12 rounded-2xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-6 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
        </div>
        <p class="mt-3 text-[var(--c-text)] font-medium">No se encontraron agencias</p>
        <p class="text-sm text-[var(--c-muted)]">Prueba ajustando los filtros o sincroniza desde el MLS.</p>
      </div>

      <div id="offices-loading" class="hidden py-10">
        <div class="animate-pulse space-y-3">
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Drawer: Create/Edit MLS Office -->
<div id="office-drawer" class="fixed inset-0 z-[11000] hidden" aria-modal="true" role="dialog" aria-labelledby="office-drawer-title">
  <div data-js="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

  <div class="absolute right-0 top-0 h-full w-full max-w-4xl">
    <div class="h-full bg-[var(--c-surface)] border-l border-[var(--c-border)] shadow-2xl flex flex-col">
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h3 id="office-drawer-title" class="text-lg font-semibold truncate">Nueva agencia MLS</h3>
          <p id="office-drawer-subtitle" class="text-xs text-[var(--c-muted)]">Completa la información de la agencia</p>
        </div>
        <div class="flex items-center gap-2">
          <button id="btn-office-save" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
            Guardar
          </button>
          <button id="btn-office-close" class="p-2 rounded-xl hover:bg-[var(--c-elev)] transition" aria-label="Cerrar">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-[var(--c-border)]">
        <nav class="flex overflow-x-auto">
          <button class="office-tab-btn px-5 py-3 text-sm font-medium border-b-2 border-[var(--c-primary)] text-[var(--c-primary)]" data-tab="datos">Datos</button>
          <button class="office-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="contacto">Contacto</button>
          <button class="office-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="agentes">Agentes</button>
          <button class="office-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="propiedades">Propiedades</button>
        </nav>
      </div>

      <form id="office-form" class="flex-1 min-h-0 overflow-y-auto">
        <input type="hidden" id="office-pk" value="" />

        <!-- Tab: Datos -->
        <section class="office-tab-panel p-6 space-y-5" data-panel="datos">
          <!-- Imagen -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Imagen remota (MLS)</label>
              <div id="img-preview-container" class="mb-2">
                <img id="img-preview" src="" alt="Imagen de la agencia" class="hidden size-20 rounded-xl object-cover border border-[var(--c-border)]" />
              </div>
              <input id="field-image-url" type="url" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
              <p class="mt-1 text-xs text-[var(--c-muted)]">URL de la imagen desde el MLS</p>
            </div>
            <div class="md:col-span-8">
              <label class="block text-sm font-medium mb-2">Imagen local (MediaAsset)</label>
              <x-media-input
                name="image_media_asset_id"
                mode="single"
                :max="1"
                placeholder="Seleccionar imagen"
                button="Seleccionar imagen"
                preview="true"
                columns="8"
              />
            </div>
          </div>

          <!-- Identificadores -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Office ID <span class="text-red-400">*</span></label>
              <input id="field-mls-office-id" type="number" min="1" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="ID en el MLS" required />
            </div>
            <div class="md:col-span-5">
              <label class="block text-sm font-medium mb-1">Nombre</label>
              <input id="field-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre de la agencia" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Paid</label>
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                <input id="field-paid" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                <span class="text-sm">Sí</span>
              </label>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Horario</label>
              <input id="field-business-hours" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="9am-6pm" />
            </div>
          </div>

          <!-- Ubicación -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Estado/Provincia</label>
              <input id="field-state-province" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Ciudad</label>
              <input id="field-city" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Código postal</label>
              <input id="field-zip-code" type="text" maxlength="20" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Dirección</label>
            <input id="field-address" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
              <label class="block text-sm font-medium mb-1">Latitud</label>
              <input id="field-latitude" type="number" step="0.0000001" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-6">
              <label class="block text-sm font-medium mb-1">Longitud</label>
              <input id="field-longitude" type="number" step="0.0000001" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <!-- Descripciones -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Descripción (EN)</label>
              <textarea id="field-description" rows="4" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Descripción (ES)</label>
              <textarea id="field-description-es" rows="4" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]"></textarea>
            </div>
          </div>
        </section>

        <!-- Tab: Contacto -->
        <section class="office-tab-panel hidden p-6 space-y-5" data-panel="contacto">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Teléfono 1</label>
              <input id="field-phone-1" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Teléfono 2</label>
              <input id="field-phone-2" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Teléfono 3</label>
              <input id="field-phone-3" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Fax</label>
              <input id="field-fax" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Email</label>
              <input id="field-email" type="email" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Sitio web</label>
              <input id="field-website" type="url" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>

          <h4 class="text-sm font-semibold pt-4 border-t border-[var(--c-border)]">Redes sociales</h4>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Facebook</label>
              <input id="field-facebook" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Instagram</label>
              <input id="field-instagram" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">YouTube</label>
              <input id="field-youtube" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">X / Twitter</label>
              <input id="field-x-twitter" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">TikTok</label>
              <input id="field-tiktok" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" />
            </div>
          </div>
        </section>

        <!-- Tab: Agentes -->
        <section class="office-tab-panel hidden p-6 space-y-5" data-panel="agentes">
          <div>
            <h4 class="text-sm font-semibold">Agentes de esta agencia</h4>
            <p class="text-xs text-[var(--c-muted)]">Agentes vinculados a esta oficina MLS.</p>
          </div>
          <div class="overflow-x-auto rounded-2xl border border-[var(--c-border)]">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-xs text-[var(--c-muted)] bg-[var(--c-elev)]">
                  <th class="py-2 px-3">Agente</th>
                  <th class="py-2 px-3">Email</th>
                  <th class="py-2 px-3">MLS ID</th>
                  <th class="py-2 px-3">Estado</th>
                </tr>
              </thead>
              <tbody id="office-agents-tbody" class="divide-y divide-[var(--c-border)]"></tbody>
            </table>
          </div>
          <div id="office-agents-empty" class="hidden text-center py-8">
            <p class="text-sm text-[var(--c-muted)]">Esta agencia no tiene agentes.</p>
          </div>
        </section>

        <!-- Tab: Propiedades -->
        <section class="office-tab-panel hidden p-6 space-y-5" data-panel="propiedades">
          <div>
            <h4 class="text-sm font-semibold">Propiedades de esta agencia</h4>
            <p class="text-xs text-[var(--c-muted)]">Propiedades vinculadas a esta oficina MLS.</p>
          </div>
          <div class="overflow-x-auto rounded-2xl border border-[var(--c-border)]">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-xs text-[var(--c-muted)] bg-[var(--c-elev)]">
                  <th class="py-2 px-3">ID</th>
                  <th class="py-2 px-3">Título</th>
                  <th class="py-2 px-3">MLS ID</th>
                  <th class="py-2 px-3">Estado</th>
                  <th class="py-2 px-3">Categoría</th>
                </tr>
              </thead>
              <tbody id="office-properties-tbody" class="divide-y divide-[var(--c-border)]"></tbody>
            </table>
          </div>
          <div id="office-properties-empty" class="hidden text-center py-8">
            <p class="text-sm text-[var(--c-muted)]">Esta agencia no tiene propiedades.</p>
          </div>
        </section>
      </form>

      <div class="px-6 py-4 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="text-xs text-[var(--c-muted)]">Los cambios se aplican usando el API (Passport).</div>
        <div class="flex items-center gap-2">
          <button id="btn-office-delete" type="button" class="hidden px-4 py-2 rounded-xl border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">Eliminar</button>
          <button id="btn-office-cancel" type="button" class="px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sync feedback modal -->
<div id="sync-modal" class="fixed inset-0 z-[12000] hidden" aria-modal="true">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-js="sync-overlay"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] shadow-2xl max-w-md w-full p-6">
      <h3 id="sync-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Sincronizando…</h3>
      <p id="sync-modal-message" class="mt-2 text-sm text-[var(--c-muted)]">Espera mientras se obtienen las agencias del MLS.</p>
      <div id="sync-modal-spinner" class="mt-4 flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--c-primary)]"></div>
      </div>
      <div id="sync-modal-result" class="hidden mt-4 space-y-2 text-sm"></div>
      <div class="mt-4 flex justify-end">
        <button id="sync-modal-close" class="hidden px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = '/api';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';

  window.dashNewAction = () => openDrawerForCreate();

  if (!API_TOKEN) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: { success: false, message: 'No se encontró un token de acceso válido.', code: 'TOKEN_MISSING', errors: { auth: ['Token requerido'] } }
    }));
    return;
  }

  const $ = (sel, el = document) => el.querySelector(sel);
  const $$ = (sel, el = document) => Array.from(el.querySelectorAll(sel));
  const toInt = (v) => { if (v == null) return null; const s = String(v).trim(); if (!s) return null; const n = parseInt(s, 10); return Number.isFinite(n) ? n : null; };
  const toNum = (v) => { if (v == null) return null; const s = String(v).trim(); if (!s) return null; const n = Number(s); return Number.isFinite(n) ? n : null; };
  const toStrOrNull = (v) => { const s = (v ?? '').toString().trim(); return s === '' ? null : s; };
  function escapeHtml(s) { return String(s ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;'); }

  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const isMutation = ['POST','PUT','PATCH','DELETE'].includes(method);
    const headers = { 'Accept': 'application/json', ...(options.headers || {}), 'Authorization': `Bearer ${API_TOKEN}` };
    if (isMutation && CSRF_TOKEN) headers['X-CSRF-TOKEN'] = CSRF_TOKEN;
    if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
    const res = await fetch(url, { ...options, method, headers });
    let json = null;
    try { json = await res.clone().json(); } catch (_e) {}
    if (!res.ok) {
      const detail = { success: false, message: json?.message || res.statusText, code: json?.code || 'SERVER_ERROR', errors: json?.errors || null, status: res.status, raw: json };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }
    return json;
  }

  // State
  const state = { list: { page: 1, perPage: 15, last: null } };

  const filterEls = { search: $('#filter-search'), paid: $('#filter-paid'), managed: $('#filter-managed'), order: $('#filter-order') };

  function getOrderParams() { const raw = filterEls.order.value || 'name:asc'; const [order, sort] = raw.split(':'); return { order: order || 'name', sort: sort || 'asc' }; }

  function buildListUrl(page) {
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', String(state.list.perPage));
    const search = filterEls.search.value.trim();
    const paid = filterEls.paid.value;
    const managed = filterEls.managed?.value;
    if (search) p.set('search', search);
    if (paid !== '') p.set('paid', paid);
    if (managed !== undefined && managed !== '') p.set('is_managed_by_us', managed);
    const { order, sort } = getOrderParams();
    p.set('order', order);
    p.set('sort', sort);
    return `${API_BASE}/mls-offices?${p.toString()}`;
  }

  function badgePaid(paid) {
    if (paid) return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100"><span class="size-1.5 rounded-full bg-green-500"></span> Paid</span>`;
    return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] text-[var(--c-muted)] border border-[var(--c-border)]"><span class="size-1.5 rounded-full bg-[var(--c-border)]"></span> Free</span>`;
  }

  function renderOffices(paginated) {
    const tbody = $('#offices-tbody');
    const empty = $('#offices-empty');
    tbody.innerHTML = '';
    const items = paginated?.data || [];
    if (!items.length) { empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    items.forEach(o => {
      const imgUrl = o.image || o.image_url || null;
      const img = imgUrl
        ? `<img src="${escapeHtml(imgUrl)}" class="size-10 rounded-xl object-cover border border-[var(--c-border)]" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';" /><div class="size-10 rounded-xl place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]" style="display:none;"><svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/></svg></div>`
        : `<div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]"><svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/></svg></div>`;

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-[var(--c-elev)]/50 transition';
      tr.innerHTML = `
        <td class="py-3 pr-3"><div class="flex items-center gap-3">${img}<div class="min-w-0"><div class="font-medium text-[var(--c-text)] truncate">${escapeHtml(o.name || 'Sin nombre')}</div><div class="text-xs text-[var(--c-muted)] truncate">${escapeHtml(o.state_province || '')}</div></div></div></td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(o.city || '—')}</td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(o.email || '—')}</td>
        <td class="py-3 pr-3 text-[var(--c-muted)]">${escapeHtml(o.mls_office_id)}</td>
        <td class="py-3 pr-3">${badgePaid(!!o.paid)}</td>
        <td class="py-3 pr-3"><button class="btn-toggle-managed text-xs px-2 py-1 rounded-lg transition ${o.is_managed_by_us ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-[var(--c-elev)] text-[var(--c-muted)] hover:bg-[var(--c-border)]'}" data-id="${o.mls_office_id}" data-managed="${o.is_managed_by_us ? '1' : '0'}">${o.is_managed_by_us ? 'Sí' : 'No'}</button></td>
        <td class="py-3 pr-3"><span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] border border-[var(--c-border)]">${escapeHtml(o.agents_count ?? '—')}</span></td>
        <td class="py-3 pr-3"><span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] border border-[var(--c-border)]">${escapeHtml(o.properties_count ?? '—')}</span></td>
        <td class="py-3 text-right"><div class="inline-flex items-center gap-2">
          <button class="btn-edit-office px-3 py-1.5 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition" data-id="${o.mls_office_id}">Editar</button>
          <button class="btn-del-office px-3 py-1.5 text-xs rounded-lg bg-[var(--c-danger)] text-white hover:opacity-90 transition" data-id="${o.mls_office_id}">Eliminar</button>
        </div></td>`;
      tbody.appendChild(tr);
    });

    $('.btn-edit-office', tbody).forEach(btn => btn.addEventListener('click', () => openDrawerForEdit(btn.dataset.id)));
    $('.btn-del-office', tbody).forEach(btn => btn.addEventListener('click', () => deleteOffice(btn.dataset.id)));
    $('.btn-toggle-managed', tbody).forEach(btn => btn.addEventListener('click', () => toggleManagedByUs(btn.dataset.id, btn.dataset.managed === '1')));
  }

  function renderPager(paginated) {
    $('#offices-count').textContent = `${paginated?.total ?? 0} total`;
    $('#offices-page').textContent = `Página ${paginated?.current_page ?? 1} de ${paginated?.last_page ?? 1}`;
    $('#btn-prev-page').disabled = !(paginated?.prev_page_url);
    $('#btn-next-page').disabled = !(paginated?.next_page_url);
  }

  function setLoading(on) { $('#offices-loading').classList.toggle('hidden', !on); $('#offices-tbody').classList.toggle('opacity-50', on); }

  async function loadOffices(page = 1) {
    state.list.page = page;
    setLoading(true);
    try {
      const payload = await apiFetch(buildListUrl(page));
      if (payload?.success) { state.list.last = payload.data; renderOffices(payload.data); renderPager(payload.data); }
    } finally { setLoading(false); }
  }

  // Drawer
  const drawer = $('#office-drawer');
  const drawerOverlay = drawer.querySelector('[data-js="overlay"]');
  function openDrawer() { drawer.classList.remove('hidden'); document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden'; }
  function closeDrawer() { drawer.classList.add('hidden'); document.documentElement.style.overflow = ''; document.body.style.overflow = ''; }

  function setActiveTab(tab) {
    $$('.office-tab-btn').forEach(btn => { const a = btn.dataset.tab === tab; btn.classList.toggle('border-b-2', a); btn.classList.toggle('border-[var(--c-primary)]', a); btn.classList.toggle('text-[var(--c-primary)]', a); btn.classList.toggle('text-[var(--c-muted)]', !a); });
    $$('.office-tab-panel').forEach(p => p.classList.toggle('hidden', p.dataset.panel !== tab));
  }
  $$('.office-tab-btn').forEach(btn => btn.addEventListener('click', () => setActiveTab(btn.dataset.tab)));

  function resetForm() {
    $('#office-pk').value = '';
    $('#office-drawer-title').textContent = 'Nueva agencia MLS';
    $('#office-drawer-subtitle').textContent = 'Completa la información de la agencia';
    $('#btn-office-delete').classList.add('hidden');
    $('#field-image-url').value = ''; $('#img-preview').classList.add('hidden'); $('#img-preview').src = '';
    $('#field-mls-office-id').value = ''; $('#field-name').value = ''; $('#field-paid').checked = false; $('#field-business-hours').value = '';
    $('#field-state-province').value = ''; $('#field-city').value = ''; $('#field-zip-code').value = ''; $('#field-address').value = '';
    $('#field-latitude').value = ''; $('#field-longitude').value = '';
    $('#field-description').value = ''; $('#field-description-es').value = '';
    $('#field-phone-1').value = ''; $('#field-phone-2').value = ''; $('#field-phone-3').value = ''; $('#field-fax').value = '';
    $('#field-email').value = ''; $('#field-website').value = '';
    $('#field-facebook').value = ''; $('#field-instagram').value = ''; $('#field-youtube').value = ''; $('#field-x-twitter').value = ''; $('#field-tiktok').value = '';
    const imgInput = document.querySelector('input[name="image_media_asset_id"]');
    if (imgInput) { imgInput.value = ''; imgInput.dispatchEvent(new Event('change', { bubbles: true })); }
    $('#office-agents-tbody').innerHTML = ''; $('#office-agents-empty').classList.add('hidden');
    $('#office-properties-tbody').innerHTML = ''; $('#office-properties-empty').classList.add('hidden');
  }

  function fillFromOffice(o) {
    $('#office-pk').value = o.mls_office_id;
    $('#office-drawer-title').textContent = `Editar agencia #${o.mls_office_id}`;
    $('#office-drawer-subtitle').textContent = `${o.name || 'Sin nombre'} • ${o.city || ''}`;
    $('#btn-office-delete').classList.remove('hidden');
    const imgUrl = o.image || o.image_url || null;
    $('#field-image-url').value = o.image_url ?? '';
    if (imgUrl) { $('#img-preview').src = imgUrl; $('#img-preview').classList.remove('hidden'); } else { $('#img-preview').classList.add('hidden'); }
    $('#field-mls-office-id').value = o.mls_office_id ?? '';
    $('#field-name').value = o.name ?? '';
    $('#field-paid').checked = !!o.paid;
    $('#field-business-hours').value = o.business_hours ?? '';
    $('#field-state-province').value = o.state_province ?? '';
    $('#field-city').value = o.city ?? '';
    $('#field-zip-code').value = o.zip_code ?? '';
    $('#field-address').value = o.address ?? '';
    $('#field-latitude').value = o.latitude ?? '';
    $('#field-longitude').value = o.longitude ?? '';
    $('#field-description').value = o.description ?? '';
    $('#field-description-es').value = o.description_es ?? '';
    $('#field-phone-1').value = o.phone_1 ?? '';
    $('#field-phone-2').value = o.phone_2 ?? '';
    $('#field-phone-3').value = o.phone_3 ?? '';
    $('#field-fax').value = o.fax ?? '';
    $('#field-email').value = o.email ?? '';
    $('#field-website').value = o.website ?? '';
    $('#field-facebook').value = o.facebook ?? '';
    $('#field-instagram').value = o.instagram ?? '';
    $('#field-youtube').value = o.youtube ?? '';
    $('#field-x-twitter').value = o.x_twitter ?? '';
    $('#field-tiktok').value = o.tiktok ?? '';
    const imgInput = document.querySelector('input[name="image_media_asset_id"]');
    if (imgInput) { imgInput.value = o.image_media_asset_id ?? ''; imgInput.dispatchEvent(new Event('change', { bubbles: true })); }

    // Agentes
    const agents = o.agents || [];
    const aTbody = $('#office-agents-tbody');
    const aEmpty = $('#office-agents-empty');
    aTbody.innerHTML = '';
    if (!agents.length) { aEmpty.classList.remove('hidden'); } else {
      aEmpty.classList.add('hidden');
      agents.forEach(a => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="py-2 px-3"><div class="flex items-center gap-2"><div class="font-medium text-[var(--c-text)]">${escapeHtml(a.name || a.full_name || 'Agente #' + a.mls_agent_id)}</div></div></td>
          <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(a.email || '—')}</td>
          <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(a.mls_agent_id)}</td>
          <td class="py-2 px-3">${a.is_active ? '<span class="text-xs text-green-500">Activo</span>' : '<span class="text-xs text-[var(--c-muted)]">Inactivo</span>'}</td>`;
        aTbody.appendChild(tr);
      });
    }

    // Propiedades
    const props = o.properties || [];
    const pTbody = $('#office-properties-tbody');
    const pEmpty = $('#office-properties-empty');
    pTbody.innerHTML = '';
    if (!props.length) { pEmpty.classList.remove('hidden'); } else {
      pEmpty.classList.add('hidden');
      props.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="py-2 px-3 text-[var(--c-text)]">#${escapeHtml(p.id)}</td>
          <td class="py-2 px-3 text-[var(--c-text)]">${escapeHtml(p.title || '(Sin título)')}</td>
          <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(p.mls_public_id || p.mls_id || '—')}</td>
          <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(p.status || '—')}</td>
          <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(p.category || '—')}</td>`;
        pTbody.appendChild(tr);
      });
    }
  }

  async function openDrawerForCreate() { resetForm(); setActiveTab('datos'); openDrawer(); }

  async function openDrawerForEdit(id) {
    resetForm(); setActiveTab('datos'); openDrawer();
    try {
      const payload = await apiFetch(`${API_BASE}/mls-offices/${id}`);
      if (payload?.success) fillFromOffice(payload.data);
    } catch (e) { /* error dispatched */ }
  }

  function buildPayloadFromForm() {
    return {
      mls_office_id: toInt($('#field-mls-office-id').value),
      name: toStrOrNull($('#field-name').value),
      paid: $('#field-paid').checked,
      business_hours: toStrOrNull($('#field-business-hours').value),
      state_province: toStrOrNull($('#field-state-province').value),
      city: toStrOrNull($('#field-city').value),
      zip_code: toStrOrNull($('#field-zip-code').value),
      address: toStrOrNull($('#field-address').value),
      latitude: toNum($('#field-latitude').value),
      longitude: toNum($('#field-longitude').value),
      description: toStrOrNull($('#field-description').value),
      description_es: toStrOrNull($('#field-description-es').value),
      image_url: toStrOrNull($('#field-image-url').value),
      image_media_asset_id: toInt(document.querySelector('input[name="image_media_asset_id"]')?.value),
      phone_1: toStrOrNull($('#field-phone-1').value),
      phone_2: toStrOrNull($('#field-phone-2').value),
      phone_3: toStrOrNull($('#field-phone-3').value),
      fax: toStrOrNull($('#field-fax').value),
      email: toStrOrNull($('#field-email').value),
      website: toStrOrNull($('#field-website').value),
      facebook: toStrOrNull($('#field-facebook').value),
      instagram: toStrOrNull($('#field-instagram').value),
      youtube: toStrOrNull($('#field-youtube').value),
      x_twitter: toStrOrNull($('#field-x-twitter').value),
      tiktok: toStrOrNull($('#field-tiktok').value),
    };
  }

  async function saveOffice() {
    const pk = $('#office-pk').value;
    const payload = buildPayloadFromForm();
    if (!payload.mls_office_id) {
      window.dispatchEvent(new CustomEvent('api:response', { detail: { success: false, message: 'MLS Office ID es obligatorio.', code: 'CLIENT_VALIDATION', errors: { mls_office_id: ['Requerido'] } } }));
      return;
    }
    const isEdit = !!pk;
    const url = isEdit ? `${API_BASE}/mls-offices/${pk}` : `${API_BASE}/mls-offices`;
    const method = isEdit ? 'PATCH' : 'POST';
    try {
      const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
      if (res?.success) { window.dispatchEvent(new CustomEvent('api:response', { detail: res })); closeDrawer(); await loadOffices(state.list.page); }
    } catch (e) { /* error dispatched */ }
  }

  async function deleteOffice(id) {
    if (!confirm('¿Eliminar esta agencia MLS? Esta acción no se puede deshacer.')) return;
    try {
      const res = await apiFetch(`${API_BASE}/mls-offices/${id}`, { method: 'DELETE' });
      if (res?.success) { window.dispatchEvent(new CustomEvent('api:response', { detail: res })); await loadOffices(state.list.page); }
    } catch (e) { /* error dispatched */ }
  }

  async function toggleManagedByUs(id, currentValue) {
    const newValue = !currentValue;
    try {
      const res = await apiFetch(`${API_BASE}/mls-offices/${id}/managed-by-us`, {
        method: 'PATCH',
        body: JSON.stringify({ is_managed_by_us: newValue })
      });
      if (res?.success) { window.dispatchEvent(new CustomEvent('api:response', { detail: res })); await loadOffices(state.list.page); }
    } catch (e) { /* error dispatched */ }
  }

  // Sync
  async function syncFromMLS() {
    const modal = $('#sync-modal');
    const title = $('#sync-modal-title');
    const message = $('#sync-modal-message');
    const spinner = $('#sync-modal-spinner');
    const result = $('#sync-modal-result');
    const closeBtn = $('#sync-modal-close');

    modal.classList.remove('hidden');
    title.textContent = 'Sincronizando agencias…';
    message.textContent = 'Procesando agencias del MLS por lotes. No cierres esta ventana.';
    spinner.classList.remove('hidden');
    result.classList.remove('hidden');
    result.innerHTML = `<div class="w-full bg-[var(--c-elev)] rounded-full h-3 border border-[var(--c-border)]"><div id="sync-progress-bar" class="bg-purple-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div></div><p id="sync-progress-text" class="text-xs text-[var(--c-muted)] text-center mt-1">Iniciando…</p>`;
    closeBtn.classList.add('hidden');

    const BATCH_SIZE = 25;
    let page = 1;
    let totalCreated = 0, totalUpdated = 0, totalErrors = 0, totalProcessed = 0, totalInMls = null;
    let completed = false;

    try {
      while (!completed) {
        const res = await apiFetch(`${API_BASE}/mls-offices/sync`, {
          method: 'POST',
          body: JSON.stringify({ batch_size: BATCH_SIZE, page, with_detail: true })
        });
        if (!res?.success) throw { message: res?.data?.message || res?.message || 'Error en la sincronización' };
        const d = res.data || {};
        totalCreated += d.created ?? 0;
        totalUpdated += d.updated ?? 0;
        totalErrors += d.errors ?? 0;
        totalProcessed += d.processed ?? 0;
        completed = d.completed ?? false;
        page = d.next_page ?? 0;
        if (d.total_in_mls) totalInMls = d.total_in_mls;

        const pct = totalInMls ? Math.round((totalProcessed / totalInMls) * 100) : (completed ? 100 : 50);
        const bar = $('#sync-progress-bar');
        const text = $('#sync-progress-text');
        if (bar) bar.style.width = `${Math.min(pct, 100)}%`;
        if (text) text.textContent = `${totalProcessed}${totalInMls ? '/' + totalInMls : ''} agencias procesadas (${pct}%)`;
        message.textContent = completed ? 'Sincronización completada.' : `Procesando página ${page}…`;
      }

      spinner.classList.add('hidden');
      title.textContent = '✅ Sincronización completada';
      message.textContent = '';
      result.innerHTML = `
        <div class="grid grid-cols-2 gap-2">
          <div class="rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] p-3 text-center"><div class="text-lg font-bold text-[var(--c-text)]">${totalProcessed}</div><div class="text-xs text-[var(--c-muted)]">Procesados</div></div>
          <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-center"><div class="text-lg font-bold text-green-700 dark:text-green-300">${totalCreated}</div><div class="text-xs text-green-600 dark:text-green-400">Creados</div></div>
          <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-center"><div class="text-lg font-bold text-blue-700 dark:text-blue-300">${totalUpdated}</div><div class="text-xs text-blue-600 dark:text-blue-400">Actualizados</div></div>
          <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-center"><div class="text-lg font-bold text-red-700 dark:text-red-300">${totalErrors}</div><div class="text-xs text-red-600 dark:text-red-400">Errores</div></div>
        </div>
        ${totalInMls ? `<p class="text-xs text-[var(--c-muted)] text-center mt-2">Total en MLS: ${totalInMls}</p>` : ''}`;
      closeBtn.classList.remove('hidden');
      await loadOffices(1);
    } catch (e) {
      spinner.classList.add('hidden');
      title.textContent = '❌ Error de sincronización';
      message.textContent = e?.message || 'No se pudieron sincronizar las agencias.';
      closeBtn.classList.remove('hidden');
      await loadOffices(1);
    }
  }

  function updateImgPreview() {
    const url = $('#field-image-url').value.trim();
    if (url) { $('#img-preview').src = url; $('#img-preview').classList.remove('hidden'); }
    else { $('#img-preview').classList.add('hidden'); $('#img-preview').src = ''; }
  }

  // Events
  $('#btn-create-office').addEventListener('click', openDrawerForCreate);
  $('#btn-refresh-offices').addEventListener('click', () => loadOffices(1));
  $('#btn-sync-offices').addEventListener('click', syncFromMLS);
  $('#btn-prev-page').addEventListener('click', () => { const c = state.list.last?.current_page || 1; if (c > 1) loadOffices(c - 1); });
  $('#btn-next-page').addEventListener('click', () => { const c = state.list.last?.current_page || 1; loadOffices(c + 1); });
  drawerOverlay.addEventListener('click', closeDrawer);
  $('#btn-office-close').addEventListener('click', closeDrawer);
  $('#btn-office-cancel').addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !drawer.classList.contains('hidden')) closeDrawer(); });
  $('#btn-office-save').addEventListener('click', (e) => { e.preventDefault(); saveOffice(); });
  $('#btn-office-delete').addEventListener('click', async () => { const pk = $('#office-pk').value; if (!pk) return; await deleteOffice(pk); closeDrawer(); });
  $('#field-image-url').addEventListener('change', updateImgPreview);
  $('#field-image-url').addEventListener('blur', updateImgPreview);
  $('#sync-modal-close').addEventListener('click', () => $('#sync-modal').classList.add('hidden'));
  $('#sync-modal').querySelector('[data-js="sync-overlay"]').addEventListener('click', () => { if (!$('#sync-modal-close').classList.contains('hidden')) $('#sync-modal').classList.add('hidden'); });

  const debounce = (fn, wait = 300) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); }; };
  filterEls.search.addEventListener('input', debounce(() => loadOffices(1), 300));
  filterEls.paid.addEventListener('change', () => loadOffices(1));
  if (filterEls.managed) filterEls.managed.addEventListener('change', () => loadOffices(1));
  filterEls.order.addEventListener('change', () => loadOffices(1));

  // Init
  (async () => { await loadOffices(1); })();
});
</script>
@endsection
