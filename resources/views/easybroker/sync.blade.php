@extends('layouts.app')

@section('title', 'Sincronización EasyBroker')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Sincronización EasyBroker</h1>
      <p class="text-[var(--c-muted)] mt-1">Configura y sincroniza propiedades desde la API de EasyBroker</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-test-connection" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Probar conexión
      </button>

      <button id="btn-sync-now" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span id="btn-sync-text">Sincronizar ahora</span>
      </button>
    </div>
  </div>

  <!-- Status Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
          <p class="text-xs text-[var(--c-muted)]">Propiedades</p>
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
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Columna izquierda: Configuración -->
    <div class="lg:col-span-1 space-y-4">
      <!-- Formulario de configuración -->
      <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--c-border)]">
          <h3 class="text-sm font-semibold">Configuración de API</h3>
          <p class="text-xs text-[var(--c-muted)]">Configura las credenciales de EasyBroker</p>
        </div>
        <form id="config-form" class="p-5 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1.5">Nombre de configuración</label>
            <input id="config-name" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="Principal" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5">API Key <span class="text-red-400">*</span></label>
            <div class="relative">
              <input id="config-api-key" type="password" maxlength="500" class="w-full px-3 py-2 pr-20 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="Tu API Key de EasyBroker" />
              <button type="button" id="btn-toggle-api-key" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 text-xs rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] hover:bg-[var(--c-elev)] transition">
                Mostrar
              </button>
            </div>
            <p id="config-api-key-hint" class="mt-1 text-xs text-[var(--c-muted)]">Deja vacío para mantener la actual</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5">URL Base de la API</label>
            <input id="config-base-url" type="url" maxlength="500" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="https://api.easybroker.com/v1" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1.5">Rate Limit</label>
              <input id="config-rate-limit" type="number" min="1" max="100" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="20" />
              <p class="mt-1 text-xs text-[var(--c-muted)]">Req/segundo</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1.5">Timeout</label>
              <input id="config-timeout" type="number" min="5" max="120" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] focus:border-[var(--c-primary)] focus:outline-none transition" placeholder="30" />
              <p class="mt-1 text-xs text-[var(--c-muted)]">Segundos</p>
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
            <p>Se consultan los <code class="px-1 py-0.5 rounded bg-[var(--c-elev)]">listing_statuses</code> para obtener el estado de publicación</p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">2</span>
            <p>Se identifican propiedades nuevas o actualizadas comparando fechas</p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">3</span>
            <p>Se obtiene el detalle de cada propiedad que requiere actualización</p>
          </div>
          <div class="flex gap-3">
            <span class="shrink-0 size-6 rounded-full bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-bold grid place-items-center">4</span>
            <p>Se despublican propiedades que ya no existen en EasyBroker</p>
          </div>
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
          <div class="grid grid-cols-4 gap-4">
            <div class="text-center">
              <p id="sync-stat-created" class="text-2xl font-bold text-green-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Creadas</p>
            </div>
            <div class="text-center">
              <p id="sync-stat-updated" class="text-2xl font-bold text-blue-500">0</p>
              <p class="text-xs text-[var(--c-muted)]">Actualizadas</p>
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
    try { json = await res.clone().json(); } catch (_e) {}

    if (!res.ok) {
      const detail = {
        success: false,
        message: json?.message || res.statusText || 'Error de API',
        code: json?.code || 'SERVER_ERROR',
        errors: json?.errors || null,
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
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // State
  let isSyncing = false;
  let currentConfig = null;

  // Load status
  async function loadStatus() {
    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/status`);
      
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
      }
    } catch (e) {
      console.error('Error loading status:', e);
    }
  }

  // Load config
  async function loadConfig() {
    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/config`);
      
      if (payload?.success) {
        currentConfig = payload.data;
        
        // Llenar formulario
        $('#config-name').value = currentConfig.name || 'Principal';
        $('#config-api-key').value = ''; // No mostrar la API key real, solo placeholder
        $('#config-api-key').placeholder = currentConfig.has_api_key 
          ? (currentConfig.api_key_masked || '••••••••') 
          : 'Tu API Key de EasyBroker';
        $('#config-api-key-hint').textContent = currentConfig.has_api_key 
          ? 'Deja vacío para mantener la actual' 
          : 'Requerida para sincronizar';
        $('#config-base-url').value = currentConfig.base_url || 'https://api.easybroker.com/v1';
        $('#config-rate-limit').value = currentConfig.rate_limit || 20;
        $('#config-timeout').value = currentConfig.timeout || 30;
        $('#config-notes').value = currentConfig.notes || '';
      }
    } catch (e) {
      console.error('Error loading config:', e);
    }
  }

  // Save config
  async function saveConfig() {
    const data = {
      name: $('#config-name').value || 'Principal',
      base_url: $('#config-base-url').value || 'https://api.easybroker.com/v1',
      rate_limit: parseInt($('#config-rate-limit').value) || 20,
      timeout: parseInt($('#config-timeout').value) || 30,
      notes: $('#config-notes').value || null,
    };

    // Solo enviar api_key si se ingresó una nueva
    const apiKeyValue = $('#config-api-key').value.trim();
    if (apiKeyValue) {
      data.api_key = apiKeyValue;
    }

    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/config`, {
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
      const payload = await apiFetch(`${API_BASE}/easybroker/config/api-key`, {
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
      addLogEntry('info', 'Probando conexión con EasyBroker...');
      const payload = await apiFetch(`${API_BASE}/easybroker/test-connection`);
      
      if (payload?.success) {
        addLogEntry('info', `✓ Conexión exitosa. Total propiedades en EasyBroker: ${payload.data.total_properties}`);
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } catch (e) {
      addLogEntry('error', `✗ Error de conexión: ${e.message}`);
    }
  }

  // Sync
  async function runSync() {
    if (isSyncing) return;
    
    isSyncing = true;
    $('#btn-sync-now').disabled = true;
    $('#btn-sync-text').textContent = 'Sincronizando...';
    $('#sync-progress').classList.remove('hidden');
    $('#sync-progress-text').textContent = 'Conectando con EasyBroker...';
    $('#sync-stats').classList.add('hidden');
    
    // Limpiar log
    $('#sync-log').innerHTML = '';
    addLogEntry('info', 'Iniciando sincronización...');

    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/sync`, {
        method: 'POST'
      });

      if (payload?.success) {
        const stats = payload.data?.stats || {};
        const logSummary = payload.data?.log_summary || {};

        // Mostrar estadísticas
        $('#sync-stats').classList.remove('hidden');
        $('#sync-stat-created').textContent = stats.created || 0;
        $('#sync-stat-updated').textContent = stats.updated || 0;
        $('#sync-stat-unpublished').textContent = stats.unpublished || 0;
        $('#sync-stat-errors').textContent = stats.errors || 0;

        // Agregar entradas de log
        (logSummary.last_entries || []).forEach(entry => {
          addLogEntry(entry.level, entry.message);
        });

        addLogEntry('info', '✓ Sincronización completada exitosamente');
        $('#sync-subtitle').textContent = `Última ejecución: ${fmtDate(new Date().toISOString())}`;

        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));

        // Recargar estado
        await loadStatus();
      }
    } catch (e) {
      addLogEntry('error', `✗ Error: ${e.message}`);
      $('#sync-stats').classList.remove('hidden');
    } finally {
      isSyncing = false;
      $('#btn-sync-now').disabled = false;
      $('#btn-sync-text').textContent = 'Sincronizar ahora';
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

  // Events
  $('#btn-test-connection').addEventListener('click', testConnection);
  $('#btn-sync-now').addEventListener('click', runSync);
  $('#btn-clear-log').addEventListener('click', clearLog);
  $('#btn-toggle-api-key').addEventListener('click', toggleApiKeyVisibility);
  $('#btn-delete-api-key').addEventListener('click', deleteApiKey);
  
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
