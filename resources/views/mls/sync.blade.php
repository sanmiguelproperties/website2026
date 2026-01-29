@extends('layouts.app')

@section('title', 'Sincronización MLS AMPI')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Sincronización MLS AMPI</h1>
      <p class="text-[var(--c-muted)] mt-1">Configura y sincroniza propiedades desde la API de MLS AMPI San Miguel de Allende</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-test-connection" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Probar conexión
      </button>

      <button id="btn-sync-properties" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft" title="Sincroniza solo los datos de las propiedades, sin descargar imágenes">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span id="btn-sync-properties-text">Sincronizar propiedades</span>
      </button>

      <button id="btn-sync-images" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition" title="Descarga las imágenes de las propiedades ya sincronizadas">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span id="btn-sync-images-text">Descargar imágenes</span>
      </button>

      <button id="btn-delete-mls-properties" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Eliminar propiedades MLS
      </button>

      <button id="btn-force-unlock" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-warning)] hover:border-[var(--c-warning)] hover:text-white transition" title="Fuerza la liberación del lock de sincronización">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Desbloquear
      </button>
    </div>
  </div>

  <!-- Status Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- Estado de configuración -->
    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">Estado</p>
          <p id="status-configured" class="mt-1 text-lg font-semibold">Verificando...</p>
          <p id="status-configured-hint" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
        </div>
        <div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- API Key -->
    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">API Key</p>
          <p id="status-api-key" class="mt-1 text-lg font-semibold truncate max-w-[180px]">—</p>
          <p id="status-config-source" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
        </div>
        <div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Propiedades sincronizadas -->
    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">Propiedades MLS</p>
          <p id="status-properties" class="mt-1 text-lg font-semibold">—</p>
          <p id="status-properties-hint" class="mt-1 text-xs text-[var(--c-muted)]">Sincronizadas</p>
        </div>
        <div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Última sincronización -->
    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">Última sincronización</p>
          <p id="status-last-sync" class="mt-1 text-lg font-semibold">—</p>
          <p id="status-last-sync-hint" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
        </div>
        <div class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Estado del Lock -->
    <div id="lock-status-card" class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">Lock de sincronización</p>
          <p id="status-lock" class="mt-1 text-lg font-semibold">—</p>
          <p id="status-lock-hint" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
        </div>
        <div id="lock-indicator" class="size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Columna izquierda: Configuración -->
    <div class="lg:col-span-1 space-y-4">
      <!-- Formulario de configuración -->
      <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--c-border)]">
          <h3 class="text-sm font-semibold">Configuración de API MLS</h3>
          <p class="text-xs text-[var(--c-muted)]">Configura las credenciales de MLS AMPI</p>
        </div>
        <form id="config-form" class="p-5 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1.5">Nombre de configuración</label>
            <input id="config-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="MLS Principal" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5">API Key <span class="text-red-400">*</span></label>
            <div class="relative">
              <input id="config-api-key" type="password" maxlength="500" class="w-full px-3 py-2 pr-20 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="Tu API Key de MLS AMPI" />
              <button type="button" id="btn-toggle-api-key" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 text-xs rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition">
                Mostrar
              </button>
            </div>
            <p id="config-api-key-hint" class="mt-1 text-xs text-[var(--c-muted)]">Deja vacío para mantener la actual</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5">URL Base de la API</label>
            <input id="config-base-url" type="url" maxlength="500" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="https://ampisanmigueldeallende.com/api" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1.5">Rate Limit</label>
              <input id="config-rate-limit" type="number" min="1" max="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="10" />
              <p class="mt-1 text-xs text-[var(--c-muted)]">Req/segundo</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1.5">Timeout</label>
              <input id="config-timeout" type="number" min="5" max="120" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="30" />
              <p class="mt-1 text-xs text-[var(--c-muted)]">Segundos</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1.5">Tamaño de lote</label>
              <input id="config-batch-size" type="number" min="10" max="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="50" />
              <p class="mt-1 text-xs text-[var(--c-muted)]">Props/página</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1.5">Modo de sync</label>
              <select id="config-sync-mode" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition">
                <option value="full">Completo</option>
                <option value="incremental">Incremental</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5">Notas</label>
            <textarea id="config-notes" rows="2" maxlength="1000" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition resize-none" placeholder="Notas opcionales..."></textarea>
          </div>

          <div class="flex items-center gap-3 pt-2">
            <button type="submit" id="btn-save-config" class="flex-1 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition font-medium">
              Guardar configuración
            </button>
            <button type="button" id="btn-delete-api-key" class="px-4 py-2 rounded-xl border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">
              <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
              </svg>
            </button>
          </div>
        </form>
      </div>

      <!-- Cómo funciona -->
      <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--c-border)]">
          <h3 class="text-sm font-semibold">¿Cómo funciona?</h3>
        </div>
        <div class="p-5 space-y-3 text-sm text-[var(--c-muted)]">
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">1</span>
            <p>Se consulta el endpoint <code class="px-1 py-0.5 rounded bg-[var(--c-elev)]">/properties</code> de MLS AMPI con paginación</p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">2</span>
            <p>Se identifican propiedades nuevas o actualizadas comparando <code class="px-1 py-0.5 rounded bg-[var(--c-elev)]">mls_id</code></p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">3</span>
            <p>Se sincronizan características desde <code class="px-1 py-0.5 rounded bg-[var(--c-elev)]">/features</code> y vecindarios</p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">4</span>
            <p>Se despublican propiedades que ya no existen en MLS AMPI</p>
          </div>
        </div>
      </div>

      <!-- Info de API -->
      <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--c-border)]">
          <h3 class="text-sm font-semibold">Documentación API</h3>
        </div>
        <div class="p-5 text-sm text-[var(--c-muted)]">
          <p class="mb-3">La API de MLS AMPI San Miguel de Allende proporciona acceso a:</p>
          <ul class="space-y-1 list-disc list-inside">
            <li>Propiedades con imágenes y detalles</li>
            <li>Agentes y oficinas</li>
            <li>Características y vecindarios</li>
          </ul>
          <a href="https://ampisanmigueldeallende.com/api/documentation" target="_blank" class="inline-flex items-center gap-1 mt-3 text-[var(--c-primary)] hover:underline">
            Ver documentación completa
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Columna derecha: Resultado de sincronización -->
    <div class="lg:col-span-2">
      <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden h-full flex flex-col">
        <div class="px-5 py-4 border-b border-[var(--c-border)] flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold">Resultado de sincronización</h3>
            <p class="text-xs text-[var(--c-muted)]" id="sync-subtitle">Ejecuta una sincronización para ver los resultados</p>
          </div>
          <button id="btn-clear-log" class="text-xs px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
            Limpiar
          </button>
        </div>

        <!-- Progress bar -->
        <div id="sync-progress" class="hidden px-5 py-3 border-b border-[var(--c-border)] bg-[var(--c-elev)]">
          <div class="flex items-center gap-3">
            <div class="animate-spin size-5 border-2 border-[var(--c-primary)] border-t-transparent rounded-full"></div>
            <span class="text-sm text-[var(--c-muted)]" id="sync-progress-text">Sincronizando...</span>
          </div>
        </div>

        <!-- Statistics cards -->
        <div id="sync-stats" class="hidden px-5 py-4 border-b border-[var(--c-border)] bg-[var(--c-elev)]">
          <div class="grid grid-cols-5 gap-4">
            <div class="text-center">
              <p id="sync-stat-created" class="text-2xl font-bold text-green-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Creadas</p>
            </div>
            <div class="text-center">
              <p id="sync-stat-updated" class="text-2xl font-bold text-blue-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Actualizadas</p>
            </div>
            <div class="text-center">
              <p id="sync-stat-skipped" class="text-2xl font-bold text-gray-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Sin cambios</p>
            </div>
            <div class="text-center">
              <p id="sync-stat-unpublished" class="text-2xl font-bold text-yellow-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Despublicadas</p>
            </div>
            <div class="text-center">
              <p id="sync-stat-errors" class="text-2xl font-bold text-red-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Errores</p>
            </div>
          </div>
        </div>

        <!-- Image sync progress bar -->
        <div id="images-progress" class="hidden px-5 py-4 border-b border-[var(--c-border)] bg-[var(--c-elev)]">
          <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium">Progreso de descarga de imágenes</span>
            <span id="images-progress-percent" class="text-sm font-bold text-[var(--c-primary)]">0%</span>
          </div>
          <div class="h-3 w-full bg-[var(--c-surface)] rounded-full overflow-hidden">
            <div id="images-progress-bar" class="h-full bg-[var(--c-primary)] transition-all duration-300" style="width: 0%"></div>
          </div>
          <p id="images-progress-text" class="mt-2 text-xs text-[var(--c-muted)]">Iniciando...</p>
        </div>

        <!-- Log -->
        <div class="flex-1 min-h-[300px] overflow-y-auto p-5">
          <div id="sync-log" class="space-y-2 font-mono text-xs">
            <div class="text-[var(--c-muted)] text-center py-8">
              <svg class="mx-auto size-10 opacity-50 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              <p>Haz clic en "Sincronizar ahora" para comenzar</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = '/api';
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';

  if (!API_TOKEN) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: 'No se encontró un token de acceso válido. Por favor inicia sesión nuevamente.',
        code: 'TOKEN_MISSING',
      }
    }));
    return;
  }

  // Utils
  const $ = (sel) => document.querySelector(sel);
  
  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${API_TOKEN}`,
      ...(options.headers || {}),
    };

    const res = await fetch(url, { ...options, method, headers });
    let json = null;
    let responseText = null;
    try { 
      json = await res.clone().json(); 
      responseText = JSON.stringify(json, null, 2);
    } catch (_e) {
      try {
        responseText = await res.clone().text();
      } catch (_e2) {
        responseText = 'No se pudo leer la respuesta';
      }
    }

    if (!res.ok) {
      const detail = {
        success: false,
        message: json?.message || res.statusText || 'Error de API',
        code: json?.code || `HTTP_${res.status}`,
        errors: json?.errors || null,
        status: res.status,
        response: responseText,
      };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    return json;
  }

  function fmtDate(iso) {
    if (!iso) return '—';
    try {
      const d = new Date(iso);
      if (Number.isNaN(d.getTime())) return String(iso);
      return d.toLocaleString('es-CO', { 
        year: 'numeric', 
        month: 'short', 
        day: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    } catch (_e) {
      return String(iso);
    }
  }

  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g, '&')
      .replace(/</g, '<')
      .replace(/>/g, '>')
      .replace(/"/g, '"')
      .replace(/'/g, '&#039;');
  }

  // State
  let isSyncing = false;
  let currentConfig = null;

  // Load status
  async function loadStatus() {
    try {
      const payload = await apiFetch(`${API_BASE}/mls/status`);
      
      if (payload?.success) {
        const data = payload.data;
        
        // Estado de configuración
        if (data.configured) {
          $('#status-configured').textContent = 'Configurado';
          $('#status-configured').className = 'mt-1 text-lg font-semibold text-green-500';
          $('#status-configured-hint').textContent = 'Listo para sincronizar';
        } else {
          $('#status-configured').textContent = 'No configurado';
          $('#status-configured').className = 'mt-1 text-lg font-semibold text-red-500';
          $('#status-configured-hint').textContent = 'Configura la API Key';
        }

        // API Key
        $('#status-api-key').textContent = data.api_key || '—';
        $('#status-config-source').textContent = data.config_source === 'database' 
          ? 'Configurado en BD' 
          : 'Configurado en .env';

        // Propiedades
        const total = data.total_properties || 0;
        const published = data.published_properties || 0;
        $('#status-properties').textContent = total;
        $('#status-properties-hint').textContent = `${published} publicadas`;

        // Última sincronización
        if (data.last_sync?.last_sync_at) {
          $('#status-last-sync').textContent = fmtDate(data.last_sync.last_sync_at);
          const created = data.last_sync.created || 0;
          const updated = data.last_sync.updated || 0;
          const errors = data.last_sync.errors || 0;
          $('#status-last-sync-hint').textContent = `+${created} / ~${updated} / ${errors} err`;
        } else {
          $('#status-last-sync').textContent = 'Nunca';
          $('#status-last-sync-hint').textContent = 'Ejecuta la primera sincronización';
        }

        // Estado del lock
        const isLocked = data.sync_locked;
        const isStale = data.lock_stale;
        const lockCard = $('#lock-status-card');
        const lockIndicator = $('#lock-indicator');
        
        if (isLocked) {
          $('#status-lock').textContent = 'Bloqueado';
          $('#status-lock').className = 'mt-1 text-lg font-semibold text-red-500';
          
          if (isStale) {
            $('#status-lock-hint').textContent = 'Obsoleto - Usa Desbloquear';
            $('#status-lock-hint').className = 'mt-1 text-xs text-yellow-500 font-medium';
            lockCard.className = 'rounded-2xl bg-yellow-50 border border-yellow-300 p-5';
            lockIndicator.className = 'size-10 rounded-xl grid place-items-center bg-yellow-100 border border-yellow-300';
          } else {
            $('#status-lock-hint').textContent = 'Sincronización en curso';
            $('#status-lock-hint').className = 'mt-1 text-xs text-blue-500';
            lockCard.className = 'rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5';
            lockIndicator.className = 'size-10 rounded-xl grid place-items-center bg-[var(--c-elev)] border border-[var(--c-border)]';
          }
        } else {
          $('#status-lock').textContent = 'Libre';
          $('#status-lock').className = 'mt-1 text-lg font-semibold text-green-500';
          $('#status-lock-hint').textContent = 'Listo para sincronizar';
          $('#status-lock-hint').className = 'mt-1 text-xs text-[var(--c-muted)]';
          lockCard.className = 'rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5';
          lockIndicator.className = 'size-10 rounded-xl grid place-items-center bg-green-100 border border-green-300';
        }
      }
    } catch (e) {
      console.error('Error loading status:', e);
    }
  }

  // Load config
  async function loadConfig() {
    try {
      const payload = await apiFetch(`${API_BASE}/mls/config`);
      
      if (payload?.success) {
        currentConfig = payload.data;
        
        // Llenar formulario
        $('#config-name').value = currentConfig.name || 'MLS Principal';
        $('#config-api-key').value = ''; // No mostrar la API key real, solo placeholder
        $('#config-api-key').placeholder = currentConfig.has_api_key 
          ? (currentConfig.api_key_masked || '••••••••') 
          : 'Tu API Key de MLS AMPI';
        $('#config-api-key-hint').textContent = currentConfig.has_api_key 
          ? 'Deja vacío para mantener la actual' 
          : 'Requerida para sincronizar';
        $('#config-base-url').value = currentConfig.base_url || 'https://ampisanmigueldeallende.com/api';
        $('#config-rate-limit').value = currentConfig.rate_limit || 10;
        $('#config-timeout').value = currentConfig.timeout || 30;
        $('#config-batch-size').value = currentConfig.batch_size || 50;
        $('#config-sync-mode').value = currentConfig.sync_mode || 'full';
        $('#config-notes').value = currentConfig.notes || '';
      }
    } catch (e) {
      console.error('Error loading config:', e);
    }
  }

  // Save config
  async function saveConfig() {
    const data = {
      name: $('#config-name').value || 'MLS Principal',
      base_url: $('#config-base-url').value || 'https://ampisanmigueldeallende.com/api',
      rate_limit: parseInt($('#config-rate-limit').value) || 10,
      timeout: parseInt($('#config-timeout').value) || 30,
      batch_size: parseInt($('#config-batch-size').value) || 50,
      sync_mode: $('#config-sync-mode').value || 'full',
      notes: $('#config-notes').value || null,
    };

    // Solo enviar api_key si se ingresó una nueva
    const apiKeyValue = $('#config-api-key').value.trim();
    if (apiKeyValue) {
      data.api_key = apiKeyValue;
    }

    try {
      const payload = await apiFetch(`${API_BASE}/mls/config`, {
        method: 'PUT',
        body: JSON.stringify(data),
      });

      if (payload?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
        addLogEntry('info', '✓ Configuración guardada exitosamente');
        
        // Recargar estado y configuración
        await loadStatus();
        await loadConfig();
      }
    } catch (e) {
      addLogEntry('error', '✗ Error al guardar configuración');
    }
  }

  // Delete API key
  async function deleteApiKey() {
    if (!confirm('¿Estás seguro de eliminar la API Key? Deberás configurarla de nuevo para sincronizar.')) {
      return;
    }

    try {
      const payload = await apiFetch(`${API_BASE}/mls/config/api-key`, {
        method: 'DELETE',
      });

      if (payload?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
        addLogEntry('info', 'API Key eliminada');
        
        await loadStatus();
        await loadConfig();
      }
    } catch (e) {
      addLogEntry('error', '✗ Error al eliminar API Key');
    }
  }

  // Test connection
  async function testConnection() {
    try {
      addLogEntry('info', 'Probando conexión con MLS AMPI...');
      const payload = await apiFetch(`${API_BASE}/mls/test-connection`);
      
      if (payload?.success) {
        addLogEntry('info', `✓ Conexión exitosa. Total propiedades en MLS: ${payload.data.total_properties}`);
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } catch (e) {
      addLogEntry('error', `✗ Error de conexión: ${e.message}`);
    }
  }

  // Sync properties only (without images) - Uses progressive sync for server-limited environments
  async function syncProperties() {
    if (isSyncing) return;
    
    isSyncing = true;
    $('#btn-sync-properties').disabled = true;
    $('#btn-sync-properties-text').textContent = 'Sincronizando...';
    $('#sync-progress').classList.remove('hidden');
    $('#sync-stats').classList.add('hidden');
    
    // Limpiar log
    $('#sync-log').innerHTML = '';
    addLogEntry('info', 'Iniciando sincronización progresiva de propiedades...');
    addLogEntry('info', '💡 Usando sincronización por lotes para servidores con límites de tiempo');
    
    // Acumular estadísticas
    let totalCreated = 0;
    let totalUpdated = 0;
    let totalUnpublished = 0;
    let totalErrors = 0;
    let batchCount = 0;
    let totalProperties = 0;
    let lastOffset = 0;
    
    try {
      // Loop de sincronización progresiva
      let maxRetries = 5;
      let retryCount = 0;
      
      while (true) {
        try {
          $('#sync-progress-text').textContent = `Procesando lote ${batchCount + 1}...`;
          
          // Construir parámetros de la request
          const params = { 
            batch_size: 20,  // Lote pequeño para servidores limitados
            skip_media: true 
          };
          
          // Incluir offset si ya tenemos uno
          if (lastOffset > 0) {
            params.offset = lastOffset;
          }
          
          const payload = await apiFetch(`${API_BASE}/mls/sync/progressive`, {
            method: 'POST',
            body: JSON.stringify(params),
          });

          if (payload?.success) {
            const data = payload.data || {};
            retryCount = 0; // Resetear reintentos en éxito
            
            // Acumular estadísticas
            totalCreated += data.created || 0;
            totalUpdated += data.updated || 0;
            totalUnpublished += data.unpublished || 0;
            totalErrors += data.errors || 0;
            totalProperties = data.total_in_mls || totalProperties;
            batchCount++;
            
            // Actualizar progreso visual
            const progress = data.progress_percentage || 0;
            $('#sync-progress-text').textContent = `Lote ${batchCount}: ${data.processed || 0} propiedades (${progress.toFixed(1)}% completado)`;
            
            addLogEntry('info', `📦 Lote ${batchCount}: ${data.processed || 0} procesadas | +${data.created || 0} creadas | ~${data.updated || 0} actualizadas`);
            
            // Mostrar estadísticas en tiempo real
            $('#sync-stats').classList.remove('hidden');
            $('#sync-stat-created').textContent = totalCreated;
            $('#sync-stat-updated').textContent = totalUpdated;
            $('#sync-stat-skipped').textContent = totalProperties - totalCreated - totalUpdated - totalErrors;
            $('#sync-stat-unpublished').textContent = totalUnpublished;
            $('#sync-stat-errors').textContent = totalErrors;
            
            // Verificar si completó
            if (data.completed) {
              addLogEntry('success', `✓ Sincronización completada en ${batchCount} lotes`);
              addLogEntry('info', `📊 Total: ${totalCreated} creadas, ${totalUpdated} actualizadas, ${totalUnpublished} despublicadas`);
              
              if (totalErrors > 0) {
                addLogEntry('warning', `⚠️ Errores encontrados: ${totalErrors}`);
              }
              
              $('#sync-subtitle').textContent = `Última ejecución: ${fmtDate(new Date().toISOString())}`;
              
              break;
            }
            
            // Actualizar offset para la siguiente iteración
            lastOffset = data.next_offset || lastOffset + 20;
            
            // Pequeña pausa entre lotes para no saturar el servidor
            await new Promise(resolve => setTimeout(resolve, 500));
          } else {
            // Mostrar detalles del error
            const errorStatus = payload?.status || 'N/A';
            const errorCode = payload?.code || 'UNKNOWN';
            const errorMessage = payload?.message || 'Error desconocido';
            const errorResponse = payload?.response || 'Sin detalles';
            
            addLogEntry('error', `✗ Error en lote ${batchCount + 1}: [${errorStatus}] ${errorCode}: ${errorMessage}`);
            addLogEntry('debug', `Respuesta del servidor: ${errorResponse}`);
            break;
          }
        } catch (e) {
          retryCount++;
          if (retryCount < maxRetries) {
            addLogEntry('warning', `⚠️ Error de conexión (intento ${retryCount}/${maxRetries}). Reintentando en 3 segundos...`);
            await new Promise(resolve => setTimeout(resolve, 3000));
            continue;
          }
          addLogEntry('error', `✗ Error en lote ${batchCount + 1}: ${e.message}`);
          break;
        }
      }
      
      window.dispatchEvent(new CustomEvent('api:response', { 
        detail: { 
          success: true, 
          data: { 
            totalCreated, 
            totalUpdated, 
            totalUnpublished, 
            totalErrors, 
            batchCount 
          } 
        } 
      }));
      
      // Recargar estado
      await loadStatus();
      
    } catch (e) {
      addLogEntry('error', `✗ Error: ${e.message}`);
      $('#sync-stats').classList.remove('hidden');
    } finally {
      isSyncing = false;
      $('#btn-sync-properties').disabled = false;
      $('#btn-sync-properties-text').textContent = 'Sincronizar propiedades';
      $('#sync-progress').classList.add('hidden');
    }
  }

  // Sync images only - process all properties in batches
  async function syncImages() {
    if (isSyncing) return;
    
    isSyncing = true;
    $('#btn-sync-images').disabled = true;
    $('#btn-sync-images-text').textContent = 'Procesando...';
    $('#sync-progress').classList.remove('hidden');
    $('#sync-stats').classList.add('hidden');
    $('#images-progress').classList.remove('hidden');
    
    // Limpiar log
    $('#sync-log').innerHTML = '';
    addLogEntry('info', 'Iniciando descarga progresiva de imágenes...');
    
    // Acumular estadísticas
    let totalLinked = 0;
    let totalDispatched = 0;
    let totalProcessed = 0;
    let totalErrors = 0;
    let batchCount = 0;
    
    try {
      // Obtener progreso actual primero
      try {
        const progressPayload = await apiFetch(`${API_BASE}/mls/sync-images/progress`);
        if (progressPayload?.success) {
          const progress = progressPayload.data || {};
          const initialPercent = progress.progress_percentage || 0;
          $('#images-progress-bar').style.width = `${initialPercent}%`;
          $('#images-progress-percent').textContent = `${initialPercent.toFixed(1)}%`;
          $('#images-progress-text').textContent = `Propiedades sincronizadas: ${progress.synced_recently || 0} / ${progress.total || 0}`;
        }
      } catch (e) {
        console.log('No se pudo obtener progreso inicial');
      }
      
      // Loop de sincronización progresiva
      let currentOffset = null;
      while (true) {
        $('#sync-progress-text').textContent = `Procesando lote ${batchCount + 1}...`;
        
        // Construir parámetros de la request
        const params = { batch_size: 50, force: false };
        
        // Incluir offset si ya tenemos uno
        if (currentOffset !== null) {
          params.offset = currentOffset;
        }
        
        const payload = await apiFetch(`${API_BASE}/mls/sync-images/progressive`, {
          method: 'POST',
          body: JSON.stringify(params),
        });

        if (payload?.success) {
          const data = payload.data || {};
          
          // Acumular estadísticas
          totalLinked += data.linked || 0;
          totalDispatched += data.dispatched || 0;
          totalProcessed += data.processed_this_batch || 0;
          totalErrors += data.errors || 0;
          batchCount++;
          
          // Actualizar progreso visual
          const progress = data.progress_percentage || 0;
          $('#images-progress-bar').style.width = `${progress}%`;
          $('#images-progress-percent').textContent = `${progress.toFixed(1)}%`;
          $('#images-progress-text').textContent = `Procesando lote ${batchCount}: ${data.processed_this_batch || 0} propiedades (${progress.toFixed(1)}% completado)`;
          
          addLogEntry('info', `📦 Lote ${batchCount}: ${data.processed_this_batch || 0} propiedades procesadas, ${data.linked_images || 0} imágenes vinculadas, ${data.dispatched_jobs || 0} jobs dispatchados`);
          
          // Verificar si completó
          if (data.completed) {
            addLogEntry('success', `✓ Sincronización de imágenes completada en ${batchCount} lotes`);
            addLogEntry('info', `📷 Total imágenes vinculadas: ${totalLinked}`);
            addLogEntry('info', `📥 Total jobs dispatchados: ${totalDispatched}`);
            addLogEntry('info', `🏠 Total propiedades procesadas: ${totalProcessed}`);
            
            if (totalErrors > 0) {
              addLogEntry('warning', `⚠️ Errores encontrados: ${totalErrors}`);
            }
            
            if (totalDispatched > 0) {
              addLogEntry('info', '⏳ Las imágenes se están descargando en segundo plano');
            }
            
            $('#sync-subtitle').textContent = `Última ejecución: ${fmtDate(new Date().toISOString())}`;
            
            // Actualizar progreso al 100%
            $('#images-progress-bar').style.width = '100%';
            $('#images-progress-percent').textContent = '100%';
            $('#images-progress-text').textContent = `Completado: ${totalProcessed} propiedades procesadas`;
            
            break;
          }
          
          // Actualizar offset para la siguiente iteración
          currentOffset = data.next_offset || 0;
          
          // Pequeña pausa entre lotes para no saturar el servidor
          await new Promise(resolve => setTimeout(resolve, 500));
        } else {
          addLogEntry('error', `✗ Error en lote ${batchCount + 1}: ${payload?.message || 'Error desconocido'}`);
          break;
        }
      }
      
      window.dispatchEvent(new CustomEvent('api:response', { detail: { success: true, data: { totalLinked, totalDispatched, totalProcessed, totalErrors, batchCount } } }));
      
      // Recargar estado
      await loadStatus();
      
    } catch (e) {
      addLogEntry('error', `✗ Error: ${e.message}`);
    } finally {
      isSyncing = false;
      $('#btn-sync-images').disabled = false;
      $('#btn-sync-images-text').textContent = 'Descargar imágenes';
      $('#sync-progress').classList.add('hidden');
    }
  }

  function addLogEntry(level, message) {
    const log = $('#sync-log');
    
    // Remover placeholder si existe
    const placeholder = log.querySelector('.text-center');
    if (placeholder) placeholder.remove();

    const entry = document.createElement('div');
    entry.className = 'flex items-start gap-2 py-1';
    
    const levelColors = {
      'info': 'text-blue-500',
      'error': 'text-red-500',
      'warning': 'text-yellow-500',
      'debug': 'text-[var(--c-muted)]',
      'success': 'text-green-500',
    };
    
    const levelClass = levelColors[level] || 'text-[var(--c-text)]';
    const time = new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    
    entry.innerHTML = `
      <span class="text-[var(--c-muted)] shrink-0">[${escapeHtml(time)}]</span>
      <span class="${levelClass}">${escapeHtml(message)}</span>
    `;
    
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
  }

  function clearLog() {
    $('#sync-log').innerHTML = `
      <div class="text-[var(--c-muted)] text-center py-8">
        <svg class="mx-auto size-10 opacity-50 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <p>Haz clic en "Sincronizar ahora" para comenzar</p>
      </div>
    `;
    $('#sync-stats').classList.add('hidden');
    $('#sync-subtitle').textContent = 'Ejecuta una sincronización para ver los resultados';
  }

  // Toggle API key visibility
  let apiKeyVisible = false;
  function toggleApiKeyVisibility() {
    apiKeyVisible = !apiKeyVisible;
    $('#config-api-key').type = apiKeyVisible ? 'text' : 'password';
    $('#btn-toggle-api-key').textContent = apiKeyVisible ? 'Ocultar' : 'Mostrar';
  }

  // Delete all MLS properties
  async function deleteAllMLSProperties() {
    const totalProperties = $('#status-properties').textContent;
    
    if (!confirm(`¿Estás seguro de que deseas ELIMINAR todas las propiedades del MLS?\n\nEsta acción no se puede deshacer.\n\nSe eliminarán aproximadamente ${totalProperties} propiedades.`)) {
      return;
    }
    
    // Segunda confirmación
    if (!confirm('¿REALMENTE estás seguro? Esta acción eliminará permanentemente todas las propiedades sincronizadas del MLS.')) {
      return;
    }

    try {
      addLogEntry('warning', 'Eliminando propiedades del MLS...');
      
      const payload = await apiFetch(`${API_BASE}/mls/properties`, {
        method: 'DELETE',
        body: JSON.stringify({ confirm: true }),
      });

      if (payload?.success) {
        const deletedCount = payload.data?.deleted_count || 0;
        addLogEntry('info', `✓ Se eliminaron ${deletedCount} propiedades del MLS`);
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
        
        // Recargar estado
        await loadStatus();
      }
    } catch (e) {
      addLogEntry('error', `✗ Error al eliminar propiedades: ${e.message}`);
    }
  }

  // Force unlock the sync lock
  async function forceUnlock() {
    if (!confirm('¿Estás seguro de que deseas forzar la liberación del lock de sincronización?\n\nEsto solo debería hacerse si otra sincronización se quedó colgada o no responde.')) {
      return;
    }

    try {
      addLogEntry('warning', 'Intentando liberar el lock de sincronización (forzado)...');
      
      const payload = await apiFetch(`${API_BASE}/mls/sync/unlock`, {
        method: 'POST',
        body: JSON.stringify({ force: true }),
      });

      if (payload?.success) {
        addLogEntry('success', '✓ Lock liberado exitosamente');
        addLogEntry('info', '💡 Ya puedes iniciar una nueva sincronización');
        
        // Recargar estado
        await loadStatus();
      } else {
        const requiresForce = payload?.errors?.requires_force;
        if (requiresForce) {
          addLogEntry('error', `✗ No se pudo liberar el lock: ${payload?.message || 'Error desconocido'}`);
          addLogEntry('info', '💡 El lock está activo y no está obsoleto. Espera a que termine o verifica si hay otro proceso en ejecución.');
        } else {
          addLogEntry('error', `✗ No se pudo liberar el lock: ${payload?.message || 'Error desconocido'}`);
        }
      }
    } catch (e) {
      addLogEntry('error', `✗ Error al liberar lock: ${e.message}`);
    }
  }

  // Events
  $('#btn-test-connection').addEventListener('click', testConnection);
  $('#btn-sync-properties').addEventListener('click', syncProperties);
  $('#btn-sync-images').addEventListener('click', syncImages);
  $('#btn-clear-log').addEventListener('click', clearLog);
  $('#btn-toggle-api-key').addEventListener('click', toggleApiKeyVisibility);
  $('#btn-delete-api-key').addEventListener('click', deleteApiKey);
  $('#btn-delete-mls-properties').addEventListener('click', deleteAllMLSProperties);
  $('#btn-force-unlock').addEventListener('click', forceUnlock);
  
  $('#config-form').addEventListener('submit', (e) => {
    e.preventDefault();
    saveConfig();
  });

  // Init
  loadStatus();
  loadConfig();
});
</script>
@endsection
