@extends('layouts.app')

@section('title', 'Administrar Agentes MLS')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Agentes MLS</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona los agentes del MLS AMPI San Miguel de Allende</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-sync-mls" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Sincronizar agentes
      </button>

      <button id="btn-sync-relations" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        Vincular a propiedades
      </button>

      <button id="btn-create-agent" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nuevo agente
      </button>

      <button id="btn-refresh-agents" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
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
            <input id="filter-search" type="search" placeholder="Nombre, email, oficina, MLS ID…" class="bg-transparent outline-none w-full text-sm placeholder:text-[var(--c-muted)]" />
          </div>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Estado</label>
          <select id="filter-active" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>

        <div class="lg:col-span-4">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Orden</label>
          <select id="filter-order" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="name:asc" selected>Nombre (A–Z)</option>
            <option value="name:desc">Nombre (Z–A)</option>
            <option value="updated_at:desc">Actualizado (desc)</option>
            <option value="updated_at:asc">Actualizado (asc)</option>
            <option value="created_at:desc">Creado (desc)</option>
            <option value="created_at:asc">Creado (asc)</option>
            <option value="mls_agent_id:asc">MLS ID (asc)</option>
            <option value="mls_agent_id:desc">MLS ID (desc)</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
          <span id="agents-count">—</span>
          <span class="opacity-60">•</span>
          <span id="agents-page">—</span>
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
              <th class="py-2 pr-3">Agente</th>
              <th class="py-2 pr-3">Email</th>
              <th class="py-2 pr-3">Oficina</th>
              <th class="py-2 pr-3">MLS ID</th>
              <th class="py-2 pr-3">Estado</th>
              <th class="py-2 pr-3">Propiedades</th>
              <th class="py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="agents-tbody" class="divide-y divide-[var(--c-border)]">
            <!-- rows -->
          </tbody>
        </table>
      </div>

      <div id="agents-empty" class="hidden text-center py-12">
        <div class="mx-auto size-12 rounded-2xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-6 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <p class="mt-3 text-[var(--c-text)] font-medium">No se encontraron agentes</p>
        <p class="text-sm text-[var(--c-muted)]">Prueba ajustando los filtros o sincroniza desde el MLS.</p>
      </div>

      <div id="agents-loading" class="hidden py-10">
        <div class="animate-pulse space-y-3">
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
          <div class="h-10 rounded-xl bg-[var(--c-elev)]"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Drawer: Create/Edit MLS Agent -->
<div id="agent-drawer" class="fixed inset-0 z-[11000] hidden" aria-modal="true" role="dialog" aria-labelledby="agent-drawer-title">
  <div data-js="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

  <div class="absolute right-0 top-0 h-full w-full max-w-4xl">
    <div class="h-full bg-[var(--c-surface)] border-l border-[var(--c-border)] shadow-2xl flex flex-col">
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h3 id="agent-drawer-title" class="text-lg font-semibold truncate">Nuevo agente MLS</h3>
          <p id="agent-drawer-subtitle" class="text-xs text-[var(--c-muted)]">Completa la información del agente</p>
        </div>
        <div class="flex items-center gap-2">
          <button id="btn-agent-save" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
            Guardar
          </button>
          <button id="btn-agent-close" class="p-2 rounded-xl hover:bg-[var(--c-elev)] transition" aria-label="Cerrar">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-[var(--c-border)]">
        <nav class="flex overflow-x-auto">
          <button class="agent-tab-btn px-5 py-3 text-sm font-medium border-b-2 border-[var(--c-primary)] text-[var(--c-primary)]" data-tab="datos">Datos</button>
          <button class="agent-tab-btn px-5 py-3 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]" data-tab="propiedades">Propiedades</button>
        </nav>
      </div>

      <form id="agent-form" class="flex-1 min-h-0 overflow-y-auto">
        <input type="hidden" id="agent-id" value="" />

        <!-- Tab: Datos -->
        <section class="agent-tab-panel p-6 space-y-5" data-panel="datos">

          <!-- Foto -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Foto remota (MLS)</label>
              <div id="photo-preview-container" class="mb-2">
                <img id="photo-preview" src="" alt="Foto del agente" class="hidden size-20 rounded-xl object-cover border border-[var(--c-border)]" />
              </div>
              <input id="field-photo-url" type="url" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
              <p class="mt-1 text-xs text-[var(--c-muted)]">URL de la foto desde el MLS</p>
            </div>
            <div class="md:col-span-8">
              <label class="block text-sm font-medium mb-2">Foto local (MediaAsset)</label>
              <x-media-input
                name="photo_media_asset_id"
                mode="single"
                :max="1"
                placeholder="Seleccionar foto"
                button="Seleccionar foto"
                preview="true"
                columns="8"
              />
            </div>
          </div>

          <!-- Identificadores -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Agent ID <span class="text-red-400">*</span></label>
              <input id="field-mls-agent-id" type="number" min="1" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="ID en el MLS" required />
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">MLS Office ID</label>
              <input id="field-mls-office-id" type="number" min="0" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="ID oficina MLS" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Oficina</label>
              <input id="field-office-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre de oficina" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Activo</label>
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
                <input id="field-is-active" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" checked>
                <span class="text-sm">Sí</span>
              </label>
            </div>
          </div>

          <!-- Nombre completo -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Nombre completo</label>
              <input id="field-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre completo" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Nombre</label>
              <input id="field-first-name" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Apellido</label>
              <input id="field-last-name" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Apellido" />
            </div>
          </div>

          <!-- Contacto -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Email</label>
              <input id="field-email" type="email" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="email@ejemplo.com" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Teléfono</label>
              <input id="field-phone" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="+52 415 123 4567" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Celular</label>
              <input id="field-mobile" type="text" maxlength="50" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="+52 415 765 4321" />
            </div>
          </div>

          <!-- Licencia y website -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Número de licencia</label>
              <input id="field-license-number" type="text" maxlength="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Licencia" />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Sitio web</label>
              <input id="field-website" type="url" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="https://..." />
            </div>
            <div class="md:col-span-4">
              <label class="block text-sm font-medium mb-1">Usuario local (user_id)</label>
              <div class="flex gap-2">
                <input id="field-user-id" type="number" min="1" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="ID de usuario" />
                <button id="btn-find-user" type="button" class="px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Buscar</button>
              </div>
              <p id="user-preview" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
            </div>
          </div>

          <!-- Bio -->
          <div>
            <label class="block text-sm font-medium mb-1">Biografía</label>
            <textarea id="field-bio" rows="4" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Biografía del agente…"></textarea>
          </div>
        </section>

        <!-- Tab: Propiedades -->
        <section class="agent-tab-panel hidden p-6 space-y-5" data-panel="propiedades">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h4 class="text-sm font-semibold">Propiedades asociadas</h4>
              <p class="text-xs text-[var(--c-muted)]">Asocia o desasocia propiedades de este agente.</p>
            </div>
          </div>

          <!-- Agregar propiedades -->
          <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
            <h5 class="text-sm font-medium mb-2">Agregar propiedades</h5>
            <div class="flex items-center gap-2">
              <input id="attach-property-ids" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" placeholder="IDs separados por coma: 1, 5, 12…" />
              <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] px-3 py-2 text-sm whitespace-nowrap">
                <input id="attach-is-primary" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
                Principal
              </label>
              <button id="btn-attach-properties" type="button" class="px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition whitespace-nowrap">Asociar</button>
            </div>
          </div>

          <!-- Lista de propiedades asociadas -->
          <div class="overflow-x-auto rounded-2xl border border-[var(--c-border)]">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-xs text-[var(--c-muted)] bg-[var(--c-elev)]">
                  <th class="py-2 px-3">ID</th>
                  <th class="py-2 px-3">Título</th>
                  <th class="py-2 px-3">MLS ID</th>
                  <th class="py-2 px-3">Principal</th>
                  <th class="py-2 px-3 text-right">—</th>
                </tr>
              </thead>
              <tbody id="agent-properties-tbody" class="divide-y divide-[var(--c-border)]">
                <!-- rows -->
              </tbody>
            </table>
          </div>

          <div id="agent-properties-empty" class="hidden text-center py-8">
            <p class="text-sm text-[var(--c-muted)]">Este agente no tiene propiedades asociadas.</p>
          </div>
        </section>
      </form>

      <div class="px-6 py-4 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="text-xs text-[var(--c-muted)]" id="agent-drawer-footnote">Los cambios se aplican usando el API (Passport).</div>
        <div class="flex items-center gap-2">
          <button id="btn-agent-delete" type="button" class="hidden px-4 py-2 rounded-xl border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">Eliminar</button>
          <button id="btn-agent-cancel" type="button" class="px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sync feedback modal (lightweight) -->
<div id="sync-modal" class="fixed inset-0 z-[12000] hidden" aria-modal="true">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-js="sync-overlay"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] shadow-2xl max-w-md w-full p-6">
      <h3 id="sync-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Sincronizando…</h3>
      <p id="sync-modal-message" class="mt-2 text-sm text-[var(--c-muted)]">Espera mientras se obtienen los agentes del MLS.</p>
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

  const toStrOrNull = (v) => {
    const s = (v ?? '').toString().trim();
    return s === '' ? null : s;
  };

  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

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
  };

  // ----------------------
  // Filters
  // ----------------------
  const filterEls = {
    search: $('#filter-search'),
    active: $('#filter-active'),
    order: $('#filter-order'),
  };

  function getOrderParams() {
    const raw = filterEls.order.value || 'name:asc';
    const [order, sort] = raw.split(':');
    return { order: order || 'name', sort: (sort || 'asc') };
  }

  function buildListUrl(page) {
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', String(state.list.perPage));

    const search = filterEls.search.value.trim();
    const active = filterEls.active.value;

    if (search) p.set('search', search);
    if (active !== '') p.set('is_active', active);

    const { order, sort } = getOrderParams();
    p.set('order', order);
    p.set('sort', sort);

    return `${API_BASE}/mls-agents?${p.toString()}`;
  }

  // ----------------------
  // Render list
  // ----------------------
  function badgeActive(isActive) {
    if (isActive) {
      return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">
        <span class="size-1.5 rounded-full bg-green-500"></span> Activo
      </span>`;
    }
    return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] text-[var(--c-muted)] border border-[var(--c-border)]">
      <span class="size-1.5 rounded-full bg-[var(--c-border)]"></span> Inactivo
    </span>`;
  }

  function renderAgents(paginated) {
    const tbody = $('#agents-tbody');
    const empty = $('#agents-empty');

    tbody.innerHTML = '';

    const items = paginated?.data || [];
    if (!items.length) {
      empty.classList.remove('hidden');
      return;
    }

    empty.classList.add('hidden');

    items.forEach((a) => {
      const displayName = a.name || [a.first_name, a.last_name].filter(Boolean).join(' ') || `Agente #${a.mls_agent_id}`;
      const email = a.email || '—';
      const office = a.office_name || (a.mls_office_id ? `Oficina #${a.mls_office_id}` : '—');
      const propertiesCount = a.properties_count ?? a.properties?.length ?? '—';

      // Photo: usar serving_url (local si fue descargada) > photo accessor > url > photo_url
      const photoUrl = a.photo || a.photo_media_asset?.serving_url || a.photo_media_asset?.url || a.photo_url || null;
      const photo = photoUrl
        ? `<img src="${escapeHtml(photoUrl)}" class="size-10 rounded-xl object-cover border border-[var(--c-border)]" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';" />
           <div class="size-10 rounded-xl place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]" style="display:none;">
             <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
           </div>`
        : `<div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
            <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>`;

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-[var(--c-elev)]/50 transition';
      tr.innerHTML = `
        <td class="py-3 pr-3">
          <div class="flex items-center gap-3">
            ${photo}
            <div class="min-w-0">
              <div class="font-medium text-[var(--c-text)] truncate">${escapeHtml(displayName)}</div>
              <div class="text-xs text-[var(--c-muted)] truncate">#${escapeHtml(a.id)}</div>
            </div>
          </div>
        </td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(email)}</td>
        <td class="py-3 pr-3 text-[var(--c-text)]">${escapeHtml(office)}</td>
        <td class="py-3 pr-3 text-[var(--c-muted)]">${escapeHtml(a.mls_agent_id)}</td>
        <td class="py-3 pr-3">${badgeActive(!!a.is_active)}</td>
        <td class="py-3 pr-3 text-[var(--c-text)]">
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-[var(--c-elev)] border border-[var(--c-border)]">
            ${escapeHtml(propertiesCount)}
          </span>
        </td>
        <td class="py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button class="btn-edit-agent px-3 py-1.5 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition" data-id="${a.id}">Editar</button>
            <button class="btn-del-agent px-3 py-1.5 text-xs rounded-lg bg-[var(--c-danger)] text-white hover:opacity-90 transition" data-id="${a.id}">Eliminar</button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    $$('.btn-edit-agent', tbody).forEach(btn => {
      btn.addEventListener('click', () => openDrawerForEdit(btn.dataset.id));
    });
    $$('.btn-del-agent', tbody).forEach(btn => {
      btn.addEventListener('click', () => deleteAgent(btn.dataset.id));
    });
  }

  function renderPager(paginated) {
    const countEl = $('#agents-count');
    const pageEl = $('#agents-page');
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
    $('#agents-loading').classList.toggle('hidden', !on);
    $('#agents-tbody').classList.toggle('opacity-50', on);
  }

  async function loadAgents(page = 1) {
    state.list.page = page;
    setLoading(true);
    try {
      const payload = await apiFetch(buildListUrl(page));
      if (payload?.success) {
        state.list.last = payload.data;
        renderAgents(payload.data);
        renderPager(payload.data);
      } else {
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } finally {
      setLoading(false);
    }
  }

  // ----------------------
  // Drawer + Tabs
  // ----------------------
  const drawer = $('#agent-drawer');
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
    $$('.agent-tab-btn').forEach(btn => {
      const active = btn.dataset.tab === tab;
      btn.classList.toggle('border-b-2', active);
      btn.classList.toggle('border-[var(--c-primary)]', active);
      btn.classList.toggle('text-[var(--c-primary)]', active);
      btn.classList.toggle('text-[var(--c-muted)]', !active);
    });

    $$('.agent-tab-panel').forEach(panel => {
      panel.classList.toggle('hidden', panel.dataset.panel !== tab);
    });
  }

  $$('.agent-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => setActiveTab(btn.dataset.tab));
  });

  // ----------------------
  // Drawer: populate / reset
  // ----------------------
  function resetForm() {
    $('#agent-id').value = '';

    $('#agent-drawer-title').textContent = 'Nuevo agente MLS';
    $('#agent-drawer-subtitle').textContent = 'Completa la información del agente';

    $('#btn-agent-delete').classList.add('hidden');

    // Photo
    $('#field-photo-url').value = '';
    $('#photo-preview').classList.add('hidden');
    $('#photo-preview').src = '';

    // Identifiers
    $('#field-mls-agent-id').value = '';
    $('#field-mls-office-id').value = '';
    $('#field-office-name').value = '';
    $('#field-is-active').checked = true;

    // Name
    $('#field-name').value = '';
    $('#field-first-name').value = '';
    $('#field-last-name').value = '';

    // Contact
    $('#field-email').value = '';
    $('#field-phone').value = '';
    $('#field-mobile').value = '';

    // License/Website/User
    $('#field-license-number').value = '';
    $('#field-website').value = '';
    $('#field-user-id').value = '';
    $('#user-preview').textContent = '—';

    // Bio
    $('#field-bio').value = '';

    // Media input
    const photoInput = document.querySelector('input[name="photo_media_asset_id"]');
    if (photoInput) {
      photoInput.value = '';
      photoInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Properties tab
    $('#agent-properties-tbody').innerHTML = '';
    $('#agent-properties-empty').classList.add('hidden');
    $('#attach-property-ids').value = '';
    $('#attach-is-primary').checked = false;
  }

  function fillFromAgent(a) {
    $('#agent-id').value = a.id;

    const displayName = a.name || [a.first_name, a.last_name].filter(Boolean).join(' ') || `Agente #${a.mls_agent_id}`;
    $('#agent-drawer-title').textContent = `Editar agente #${a.id}`;
    $('#agent-drawer-subtitle').textContent = `MLS ID: ${a.mls_agent_id} • ${displayName}`;

    $('#btn-agent-delete').classList.remove('hidden');

    // Photo: usar serving_url (local si fue descargada) > photo accessor > url > photo_url
    const photoUrl = a.photo || a.photo_media_asset?.serving_url || a.photo_media_asset?.url || a.photo_url || null;
    $('#field-photo-url').value = a.photo_url ?? '';
    if (photoUrl) {
      $('#photo-preview').src = photoUrl;
      $('#photo-preview').classList.remove('hidden');
    } else {
      $('#photo-preview').classList.add('hidden');
      $('#photo-preview').src = '';
    }

    // Identifiers
    $('#field-mls-agent-id').value = a.mls_agent_id ?? '';
    $('#field-mls-office-id').value = a.mls_office_id ?? '';
    $('#field-office-name').value = a.office_name ?? '';
    $('#field-is-active').checked = !!a.is_active;

    // Name
    $('#field-name').value = a.name ?? '';
    $('#field-first-name').value = a.first_name ?? '';
    $('#field-last-name').value = a.last_name ?? '';

    // Contact
    $('#field-email').value = a.email ?? '';
    $('#field-phone').value = a.phone ?? '';
    $('#field-mobile').value = a.mobile ?? '';

    // License/Website/User
    $('#field-license-number').value = a.license_number ?? '';
    $('#field-website').value = a.website ?? '';
    $('#field-user-id').value = a.user_id ?? '';
    if (a.user) {
      $('#user-preview').textContent = `${a.user.name || 'Usuario'} (${a.user.email || '—'})`;
    } else {
      $('#user-preview').textContent = '—';
    }

    // Bio
    $('#field-bio').value = a.bio ?? '';

    // Media input
    const photoInput = document.querySelector('input[name="photo_media_asset_id"]');
    if (photoInput) {
      photoInput.value = a.photo_media_asset_id ?? '';
      photoInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Properties
    renderAgentProperties(a.properties || []);
  }

  function renderAgentProperties(properties) {
    const tbody = $('#agent-properties-tbody');
    const empty = $('#agent-properties-empty');

    tbody.innerHTML = '';

    if (!properties.length) {
      empty.classList.remove('hidden');
      return;
    }

    empty.classList.add('hidden');

    properties.forEach(p => {
      const isPrimary = p.pivot?.is_primary ? 'Sí' : 'No';
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="py-2 px-3 text-[var(--c-text)]">#${escapeHtml(p.id)}</td>
        <td class="py-2 px-3 text-[var(--c-text)]">${escapeHtml(p.title || '(Sin título)')}</td>
        <td class="py-2 px-3 text-[var(--c-muted)]">${escapeHtml(p.mls_id || p.mls_public_id || '—')}</td>
        <td class="py-2 px-3">
          ${p.pivot?.is_primary
            ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">Sí</span>'
            : '<span class="text-xs text-[var(--c-muted)]">No</span>'}
        </td>
        <td class="py-2 px-3 text-right">
          <button type="button" class="btn-detach-prop px-2 py-1 rounded-lg text-xs bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] text-[var(--c-danger)] transition" data-id="${p.id}">Quitar</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    $$('.btn-detach-prop', tbody).forEach(btn => {
      btn.addEventListener('click', () => detachProperty(btn.dataset.id));
    });
  }

  // ----------------------
  // Open drawer
  // ----------------------
  async function openDrawerForCreate() {
    resetForm();
    setActiveTab('datos');
    openDrawer();
  }

  async function openDrawerForEdit(id) {
    resetForm();
    setActiveTab('datos');
    openDrawer();

    try {
      const payload = await apiFetch(`${API_BASE}/mls-agents/${id}`);
      if (payload?.success) {
        fillFromAgent(payload.data);
      } else {
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } catch (e) {
      // Error ya despachado por apiFetch
    }
  }

  // ----------------------
  // Build payload
  // ----------------------
  function buildPayloadFromForm() {
    const payload = {
      mls_agent_id: toInt($('#field-mls-agent-id').value),
      name: toStrOrNull($('#field-name').value),
      first_name: toStrOrNull($('#field-first-name').value),
      last_name: toStrOrNull($('#field-last-name').value),
      email: toStrOrNull($('#field-email').value),
      phone: toStrOrNull($('#field-phone').value),
      mobile: toStrOrNull($('#field-mobile').value),
      mls_office_id: toInt($('#field-mls-office-id').value),
      office_name: toStrOrNull($('#field-office-name').value),
      photo_url: toStrOrNull($('#field-photo-url').value),
      photo_media_asset_id: toInt(document.querySelector('input[name="photo_media_asset_id"]')?.value),
      license_number: toStrOrNull($('#field-license-number').value),
      bio: toStrOrNull($('#field-bio').value),
      website: toStrOrNull($('#field-website').value),
      is_active: $('#field-is-active').checked,
      user_id: toInt($('#field-user-id').value),
    };

    return payload;
  }

  // ----------------------
  // Save / Delete
  // ----------------------
  async function saveAgent() {
    const id = $('#agent-id').value;

    const payload = buildPayloadFromForm();

    // Validación mínima de cliente
    if (!payload.mls_agent_id) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: 'MLS Agent ID es obligatorio.',
          code: 'CLIENT_VALIDATION',
          errors: { mls_agent_id: ['Requerido'] }
        }
      }));
      return;
    }

    const isEdit = !!id;
    const url = isEdit ? `${API_BASE}/mls-agents/${id}` : `${API_BASE}/mls-agents`;
    const method = isEdit ? 'PATCH' : 'POST';

    try {
      const res = await apiFetch(url, {
        method,
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json' }
      });

      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
        closeDrawer();
        await loadAgents(state.list.page);
      } else {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
      }
    } catch (e) {
      // Error ya despachado por apiFetch
    }
  }

  async function deleteAgent(id) {
    if (!confirm('¿Eliminar este agente MLS? Esta acción no se puede deshacer.')) return;

    try {
      const res = await apiFetch(`${API_BASE}/mls-agents/${id}`, { method: 'DELETE' });
      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
        await loadAgents(state.list.page);
      } else {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
      }
    } catch (e) {
      // Error ya despachado por apiFetch
    }
  }

  // ----------------------
  // Attach / Detach properties
  // ----------------------
  async function attachProperties() {
    const agentId = $('#agent-id').value;
    if (!agentId) return;

    const raw = $('#attach-property-ids').value;
    const ids = raw.split(',').map(s => s.trim()).filter(Boolean).map(s => parseInt(s, 10)).filter(n => Number.isFinite(n));

    if (!ids.length) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: 'Ingresa al menos un ID de propiedad.',
          code: 'CLIENT_VALIDATION',
          errors: { property_ids: ['Requerido'] }
        }
      }));
      return;
    }

    const isPrimary = $('#attach-is-primary').checked;

    try {
      const res = await apiFetch(`${API_BASE}/mls-agents/${agentId}/properties`, {
        method: 'POST',
        body: JSON.stringify({ property_ids: ids, is_primary: isPrimary }),
        headers: { 'Content-Type': 'application/json' }
      });

      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
        $('#attach-property-ids').value = '';
        $('#attach-is-primary').checked = false;
        // Refresh agent detail to get updated properties
        renderAgentProperties(res.data?.properties || []);
      }
    } catch (e) {
      // Error ya despachado por apiFetch
    }
  }

  async function detachProperty(propertyId) {
    const agentId = $('#agent-id').value;
    if (!agentId) return;
    if (!confirm('¿Desasociar esta propiedad del agente?')) return;

    try {
      const res = await apiFetch(`${API_BASE}/mls-agents/${agentId}/properties`, {
        method: 'DELETE',
        body: JSON.stringify({ property_ids: [parseInt(propertyId, 10)] }),
        headers: { 'Content-Type': 'application/json' }
      });

      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: res }));
        renderAgentProperties(res.data?.properties || []);
      }
    } catch (e) {
      // Error ya despachado por apiFetch
    }
  }

  // ----------------------
  // Sync from MLS
  // ----------------------
  async function syncFromMLS() {
    const modal = $('#sync-modal');
    const title = $('#sync-modal-title');
    const message = $('#sync-modal-message');
    const spinner = $('#sync-modal-spinner');
    const result = $('#sync-modal-result');
    const closeBtn = $('#sync-modal-close');

    // Show modal
    modal.classList.remove('hidden');
    title.textContent = 'Sincronizando agentes…';
    message.textContent = 'Procesando agentes del MLS por lotes. No cierres esta ventana.';
    spinner.classList.remove('hidden');
    result.classList.remove('hidden');
    result.innerHTML = `
      <div class="w-full bg-[var(--c-elev)] rounded-full h-3 border border-[var(--c-border)]">
        <div id="sync-progress-bar" class="bg-purple-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
      </div>
      <p id="sync-progress-text" class="text-xs text-[var(--c-muted)] text-center mt-1">Iniciando…</p>
    `;
    closeBtn.classList.add('hidden');

    const BATCH_SIZE = 20;
    let offset = 0;
    let totalCreated = 0;
    let totalUpdated = 0;
    let totalErrors = 0;
    let totalProcessed = 0;
    let totalInMls = null;
    let completed = false;

    try {
      while (!completed) {
        const res = await apiFetch(`${API_BASE}/mls-agents/sync`, {
          method: 'POST',
          body: JSON.stringify({ batch_size: BATCH_SIZE, offset }),
          headers: { 'Content-Type': 'application/json' }
        });

        if (!res?.success) {
          throw { message: res?.data?.message || res?.message || 'Error en la sincronización' };
        }

        const d = res.data || {};
        totalCreated += d.created ?? 0;
        totalUpdated += d.updated ?? 0;
        totalErrors += d.errors ?? 0;
        totalProcessed += d.processed ?? 0;
        completed = d.completed ?? false;
        offset = d.next_offset ?? 0;
        if (d.total_in_mls) totalInMls = d.total_in_mls;

        // Update progress bar
        const pct = d.progress_percentage ?? (totalInMls ? Math.round((totalProcessed / totalInMls) * 100) : 0);
        const bar = $('#sync-progress-bar');
        const text = $('#sync-progress-text');
        if (bar) bar.style.width = `${Math.min(pct, 100)}%`;
        if (text) text.textContent = `${totalProcessed}${totalInMls ? '/' + totalInMls : ''} agentes procesados (${pct}%)`;
        message.textContent = completed ? 'Sincronización completada.' : `Procesando lote… offset ${offset}`;
      }

      spinner.classList.add('hidden');
      title.textContent = '✅ Sincronización completada';
      message.textContent = '';
      result.innerHTML = `
        <div class="grid grid-cols-2 gap-2">
          <div class="rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] p-3 text-center">
            <div class="text-lg font-bold text-[var(--c-text)]">${totalProcessed}</div>
            <div class="text-xs text-[var(--c-muted)]">Procesados</div>
          </div>
          <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-center">
            <div class="text-lg font-bold text-green-700 dark:text-green-300">${totalCreated}</div>
            <div class="text-xs text-green-600 dark:text-green-400">Creados</div>
          </div>
          <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-center">
            <div class="text-lg font-bold text-blue-700 dark:text-blue-300">${totalUpdated}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Actualizados</div>
          </div>
          <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-center">
            <div class="text-lg font-bold text-red-700 dark:text-red-300">${totalErrors}</div>
            <div class="text-xs text-red-600 dark:text-red-400">Errores</div>
          </div>
        </div>
        ${totalInMls ? `<p class="text-xs text-[var(--c-muted)] text-center mt-2">Total en MLS: ${totalInMls}</p>` : ''}
      `;
      closeBtn.classList.remove('hidden');
      // Refresh list
      await loadAgents(1);
    } catch (e) {
      spinner.classList.add('hidden');
      title.textContent = '❌ Error de sincronización';
      message.textContent = e?.message || 'No se pudieron sincronizar los agentes.';
      if (totalProcessed > 0) {
        result.innerHTML += `<p class="text-xs text-[var(--c-muted)] mt-2">Se procesaron ${totalProcessed} agentes antes del error (${totalCreated} creados, ${totalUpdated} actualizados).</p>`;
      }
      closeBtn.classList.remove('hidden');
      // Refresh list anyway
      await loadAgents(1);
    }
  }

  // ----------------------
  // Sync agent-property relations
  // ----------------------
  async function syncRelations() {
    const modal = $('#sync-modal');
    const title = $('#sync-modal-title');
    const message = $('#sync-modal-message');
    const spinner = $('#sync-modal-spinner');
    const result = $('#sync-modal-result');
    const closeBtn = $('#sync-modal-close');

    modal.classList.remove('hidden');
    title.textContent = 'Vinculando agentes a propiedades…';
    message.textContent = 'Consultando el API del MLS para obtener los agentes de cada propiedad.';
    spinner.classList.remove('hidden');
    result.classList.remove('hidden');
    result.innerHTML = `
      <div class="w-full bg-[var(--c-elev)] rounded-full h-3 border border-[var(--c-border)]">
        <div id="rel-progress-bar" class="bg-blue-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
      </div>
      <p id="rel-progress-text" class="text-xs text-[var(--c-muted)] text-center mt-1">Iniciando…</p>
    `;
    closeBtn.classList.add('hidden');

    const BATCH_SIZE = 10;
    let offset = 0;
    let totalLinked = 0;
    let totalProcessed = 0;
    let totalErrors = 0;
    let totalProperties = null;
    let completed = false;

    try {
      while (!completed) {
        const res = await apiFetch(`${API_BASE}/mls-agents/sync-property-agents`, {
          method: 'POST',
          body: JSON.stringify({ batch_size: BATCH_SIZE, offset }),
          headers: { 'Content-Type': 'application/json' }
        });

        if (!res?.success) {
          throw { message: res?.data?.message || res?.message || 'Error' };
        }

        const d = res.data || {};
        totalLinked += d.linked ?? 0;
        totalProcessed += d.processed ?? 0;
        totalErrors += d.errors ?? 0;
        completed = d.completed ?? false;
        offset = d.next_offset ?? 0;
        if (d.total_properties) totalProperties = d.total_properties;

        const pct = d.progress_percentage ?? 0;
        const bar = $('#rel-progress-bar');
        const text = $('#rel-progress-text');
        if (bar) bar.style.width = `${Math.min(pct, 100)}%`;
        if (text) text.textContent = `${totalProcessed}${totalProperties ? '/' + totalProperties : ''} propiedades (${pct}%) — ${totalLinked} vínculos creados`;
        message.textContent = completed ? 'Vinculación completada.' : `Procesando lote… offset ${offset}`;
      }

      spinner.classList.add('hidden');
      title.textContent = '✅ Vinculación completada';
      message.textContent = '';
      result.innerHTML = `
        <div class="grid grid-cols-3 gap-2">
          <div class="rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] p-3 text-center">
            <div class="text-lg font-bold text-[var(--c-text)]">${totalProcessed}</div>
            <div class="text-xs text-[var(--c-muted)]">Propiedades</div>
          </div>
          <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-center">
            <div class="text-lg font-bold text-blue-700 dark:text-blue-300">${totalLinked}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Vínculos</div>
          </div>
          <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-center">
            <div class="text-lg font-bold text-red-700 dark:text-red-300">${totalErrors}</div>
            <div class="text-xs text-red-600 dark:text-red-400">Errores</div>
          </div>
        </div>
      `;
      closeBtn.classList.remove('hidden');
      await loadAgents(1);
    } catch (e) {
      spinner.classList.add('hidden');
      title.textContent = '❌ Error';
      message.textContent = e?.message || 'Error al vincular agentes.';
      closeBtn.classList.remove('hidden');
      await loadAgents(1);
    }
  }

  // ----------------------
  // User quick lookup
  // ----------------------
  async function findUserById() {
    const id = toInt($('#field-user-id').value);
    if (!id) {
      $('#user-preview').textContent = '—';
      return;
    }

    try {
      const payload = await apiFetch(`${API_BASE}/users/${id}`);
      if (payload?.success) {
        const u = payload.data;
        $('#user-preview').textContent = `${u.name || 'Usuario'} (${u.email || '—'})`;
      }
    } catch (e) {
      $('#user-preview').textContent = 'No encontrado';
    }
  }

  // ----------------------
  // Photo URL preview
  // ----------------------
  function updatePhotoPreview() {
    const url = $('#field-photo-url').value.trim();
    if (url) {
      $('#photo-preview').src = url;
      $('#photo-preview').classList.remove('hidden');
    } else {
      $('#photo-preview').classList.add('hidden');
      $('#photo-preview').src = '';
    }
  }

  // ----------------------
  // Events
  // ----------------------
  $('#btn-create-agent').addEventListener('click', openDrawerForCreate);
  $('#btn-refresh-agents').addEventListener('click', () => loadAgents(1));
  $('#btn-sync-mls').addEventListener('click', syncFromMLS);
  $('#btn-sync-relations').addEventListener('click', syncRelations);

  // Pagination
  $('#btn-prev-page').addEventListener('click', () => {
    const current = state.list.last?.current_page || 1;
    if (current > 1) loadAgents(current - 1);
  });
  $('#btn-next-page').addEventListener('click', () => {
    const current = state.list.last?.current_page || 1;
    loadAgents(current + 1);
  });

  // Drawer close
  drawerOverlay.addEventListener('click', closeDrawer);
  $('#btn-agent-close').addEventListener('click', closeDrawer);
  $('#btn-agent-cancel').addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !drawer.classList.contains('hidden')) closeDrawer();
  });

  // Save
  $('#btn-agent-save').addEventListener('click', (e) => {
    e.preventDefault();
    saveAgent();
  });

  // Delete from drawer
  $('#btn-agent-delete').addEventListener('click', async () => {
    const id = $('#agent-id').value;
    if (!id) return;
    await deleteAgent(id);
    closeDrawer();
  });

  // Attach properties
  $('#btn-attach-properties').addEventListener('click', attachProperties);

  // User lookup
  $('#btn-find-user').addEventListener('click', findUserById);

  // Photo URL preview
  $('#field-photo-url').addEventListener('change', updatePhotoPreview);
  $('#field-photo-url').addEventListener('blur', updatePhotoPreview);

  // Sync modal close
  $('#sync-modal-close').addEventListener('click', () => {
    $('#sync-modal').classList.add('hidden');
  });
  $('#sync-modal').querySelector('[data-js="sync-overlay"]').addEventListener('click', () => {
    // Only close if close button is visible (sync finished)
    if (!$('#sync-modal-close').classList.contains('hidden')) {
      $('#sync-modal').classList.add('hidden');
    }
  });

  // Filters
  const debounce = (fn, wait = 300) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  };

  filterEls.search.addEventListener('input', debounce(() => loadAgents(1), 300));
  filterEls.active.addEventListener('change', () => loadAgents(1));
  filterEls.order.addEventListener('change', () => loadAgents(1));

  // Init
  (async () => {
    await loadAgents(1);
  })();
});
</script>
@endsection
