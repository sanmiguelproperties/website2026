@extends('layouts.app')

@section('title', 'MLS -> EasyBroker')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">MLS -> EasyBroker</h1>
      <p class="text-[var(--c-muted)] mt-1">Selecciona agencia MLS, filtra propiedades y envíalas a EasyBroker con creación/actualización automática.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-reload" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Recargar
      </button>
      <button id="btn-validate" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        Prevalidar selección
      </button>
      <button id="btn-send" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
        Enviar seleccionadas
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <p class="text-xs text-[var(--c-muted)]">Estado EasyBroker</p>
      <p id="status-easybroker" class="mt-1 text-lg font-semibold">Verificando...</p>
      <p id="status-easybroker-hint" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
    </div>

    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <p class="text-xs text-[var(--c-muted)]">Tipos de propiedad</p>
      <p id="status-property-types" class="mt-1 text-lg font-semibold">—</p>
      <p class="mt-1 text-xs text-[var(--c-muted)]">Disponibles en EasyBroker</p>
    </div>

    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <p class="text-xs text-[var(--c-muted)]">Propiedades visibles</p>
      <p id="status-visible-properties" class="mt-1 text-lg font-semibold">—</p>
      <p id="status-page-info" class="mt-1 text-xs text-[var(--c-muted)]">—</p>
    </div>

    <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] p-5">
      <p class="text-xs text-[var(--c-muted)]">Seleccionadas</p>
      <p id="status-selected" class="mt-1 text-lg font-semibold">0</p>
      <p class="mt-1 text-xs text-[var(--c-muted)]">Para enviar</p>
    </div>
  </div>

  <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)]">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Agencia MLS</label>
          <select id="filter-office" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todas</option>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Estado de sync</label>
          <select id="filter-synced" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="all">Todas</option>
            <option value="unsynced">Solo sin EasyBroker ID</option>
            <option value="synced">Solo con EasyBroker ID</option>
          </select>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <input id="filter-search" type="search" placeholder="Título, MLS ID, EB ID..." class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Status destino</label>
          <select id="target-status" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="not_published">not_published</option>
            <option value="published">published</option>
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Fallback Property Type</label>
          <select id="fallback-property-type" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Auto</option>
          </select>
        </div>
      </div>
    </div>

    <div class="p-5">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-[var(--c-muted)]">
              <th class="py-2 pr-3">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" id="select-page" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" />
                  <span>Página</span>
                </label>
              </th>
              <th class="py-2 pr-3">Propiedad MLS</th>
              <th class="py-2 pr-3">Agencia MLS</th>
              <th class="py-2 pr-3">Operación</th>
              <th class="py-2 pr-3">EasyBroker</th>
              <th class="py-2 pr-3">Validación</th>
            </tr>
          </thead>
          <tbody id="properties-tbody" class="divide-y divide-[var(--c-border)]"></tbody>
        </table>
      </div>

      <div id="properties-empty" class="hidden text-center py-10 text-[var(--c-muted)]">
        No hay propiedades para los filtros seleccionados.
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="text-sm text-[var(--c-muted)]" id="paging-summary">—</div>
        <div class="flex items-center gap-2">
          <button id="btn-prev" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] disabled:opacity-40" disabled>Anterior</button>
          <button id="btn-next" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] disabled:opacity-40" disabled>Siguiente</button>
        </div>
      </div>
    </div>
  </div>

  <div class="rounded-2xl bg-[var(--c-surface)] border border-[var(--c-border)] overflow-hidden">
    <div class="px-5 py-4 border-b border-[var(--c-border)]">
      <h3 class="text-sm font-semibold">Resultado de envío</h3>
      <p class="text-xs text-[var(--c-muted)]">Se muestran resumen y errores por propiedad.</p>
    </div>
    <div class="p-5 space-y-3">
      <div id="result-summary" class="text-sm text-[var(--c-muted)]">Aún no hay ejecuciones.</div>
      <pre id="result-log" class="hidden text-xs bg-[var(--c-elev)] border border-[var(--c-border)] rounded-xl p-4 overflow-auto max-h-[360px]"></pre>
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
        message: 'No se encontró token de sesión. Inicia sesión nuevamente.',
        code: 'TOKEN_MISSING',
      }
    }));
    return;
  }

  const $ = (sel) => document.querySelector(sel);

  let currentPage = 1;
  let lastPagination = null;
  let currentRows = [];
  const selectedPropertyIds = new Set();

  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
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
        minute: '2-digit',
      });
    } catch (_e) {
      return String(iso);
    }
  }

  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${API_TOKEN}`,
      ...(options.headers || {}),
    };

    const response = await fetch(url, {
      ...options,
      method,
      headers,
    });

    let json = null;
    try {
      json = await response.clone().json();
    } catch (_e) {
      json = null;
    }

    if (!response.ok) {
      const detail = {
        success: false,
        message: json?.message || response.statusText || 'Error de API',
        code: json?.code || 'SERVER_ERROR',
        errors: json?.errors || null,
        status: response.status,
      };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    return json;
  }

  function updateSelectedCounter() {
    $('#status-selected').textContent = String(selectedPropertyIds.size);
  }

  function buildQueryParams(page = 1) {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('per_page', '15');

    const officeId = $('#filter-office').value;
    const synced = $('#filter-synced').value;
    const search = $('#filter-search').value.trim();
    const targetStatus = $('#target-status').value;
    const fallbackType = $('#fallback-property-type').value;

    if (officeId) params.set('mls_office_id', officeId);
    if (synced) params.set('synced', synced);
    if (search) params.set('search', search);
    if (targetStatus) params.set('target_status', targetStatus);
    if (fallbackType) params.set('fallback_property_type', fallbackType);

    return params;
  }

  function renderRows(rows) {
    const tbody = $('#properties-tbody');
    tbody.innerHTML = '';

    if (!rows.length) {
      $('#properties-empty').classList.remove('hidden');
      return;
    }

    $('#properties-empty').classList.add('hidden');

    rows.forEach((row) => {
      const checked = selectedPropertyIds.has(row.id) ? 'checked' : '';
      const missing = row.export_preview?.missing_required || [];
      const ready = row.export_preview?.ready;

      const operation = row.primary_operation
        ? `${row.primary_operation.type || '—'} · ${row.primary_operation.amount || '—'} ${row.primary_operation.currency_code || ''}`
        : '—';

      const officeName = row.mls_office?.name || '—';
      const ebId = row.easybroker_public_id || 'Sin ID';

      const validationBadge = ready
        ? '<span class="px-2 py-1 rounded-lg text-xs bg-green-100 text-green-700">Lista</span>'
        : `<span class="px-2 py-1 rounded-lg text-xs bg-yellow-100 text-yellow-700">Faltan: ${escapeHtml(missing.join(', '))}</span>`;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="py-3 pr-3 align-top">
          <input type="checkbox" data-role="select-row" data-id="${row.id}" ${checked} class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" />
        </td>
        <td class="py-3 pr-3 align-top">
          <div class="font-medium">${escapeHtml(row.title || '(Sin título)')}</div>
          <div class="text-xs text-[var(--c-muted)] mt-1">MLS: ${escapeHtml(row.mls_public_id || row.mls_id || '—')}</div>
          <div class="text-xs text-[var(--c-muted)]">Tipo: ${escapeHtml(row.property_type_name || row.category || '—')}</div>
        </td>
        <td class="py-3 pr-3 align-top">
          <div>${escapeHtml(officeName)}</div>
          <div class="text-xs text-[var(--c-muted)]">${escapeHtml((row.mls_office?.city || '') + (row.mls_office?.state_province ? ', ' + row.mls_office.state_province : ''))}</div>
        </td>
        <td class="py-3 pr-3 align-top">${escapeHtml(operation)}</td>
        <td class="py-3 pr-3 align-top">
          <div>${escapeHtml(ebId)}</div>
          <div class="text-xs text-[var(--c-muted)]">Último sync: ${escapeHtml(fmtDate(row.last_synced_at))}</div>
        </td>
        <td class="py-3 pr-3 align-top">${validationBadge}</td>
      `;

      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('[data-role="select-row"]').forEach((checkbox) => {
      checkbox.addEventListener('change', (event) => {
        const id = Number(event.target.getAttribute('data-id'));
        if (event.target.checked) {
          selectedPropertyIds.add(id);
        } else {
          selectedPropertyIds.delete(id);
        }
        updateSelectedCounter();
        syncSelectPageCheckbox();
      });
    });

    syncSelectPageCheckbox();
  }

  function syncSelectPageCheckbox() {
    if (!currentRows.length) {
      $('#select-page').checked = false;
      $('#select-page').indeterminate = false;
      return;
    }

    const selectedInPage = currentRows.filter((row) => selectedPropertyIds.has(row.id)).length;

    if (selectedInPage === 0) {
      $('#select-page').checked = false;
      $('#select-page').indeterminate = false;
      return;
    }

    if (selectedInPage === currentRows.length) {
      $('#select-page').checked = true;
      $('#select-page').indeterminate = false;
      return;
    }

    $('#select-page').checked = false;
    $('#select-page').indeterminate = true;
  }

  async function loadEasyBrokerStatus() {
    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/status`);
      const data = payload?.data || {};

      if (data.configured) {
        $('#status-easybroker').textContent = 'Configurado';
        $('#status-easybroker').className = 'mt-1 text-lg font-semibold text-green-500';
        $('#status-easybroker-hint').textContent = data.api_key || 'API key activa';
      } else {
        $('#status-easybroker').textContent = 'No configurado';
        $('#status-easybroker').className = 'mt-1 text-lg font-semibold text-red-500';
        $('#status-easybroker-hint').textContent = 'Configura /admin/easybroker primero';
      }
    } catch (_e) {
      $('#status-easybroker').textContent = 'Error';
      $('#status-easybroker').className = 'mt-1 text-lg font-semibold text-red-500';
      $('#status-easybroker-hint').textContent = 'No se pudo consultar estado';
    }
  }

  async function loadOffices() {
    const payload = await apiFetch(`${API_BASE}/easybroker/mls-export/offices`);
    const offices = payload?.data || [];

    const select = $('#filter-office');
    const current = select.value;

    select.innerHTML = '<option value="">Todas</option>';
    offices.forEach((office) => {
      const option = document.createElement('option');
      option.value = String(office.mls_office_id);
      option.textContent = `${office.name || 'Sin nombre'} (${office.mls_properties_count || 0})`;
      select.appendChild(option);
    });

    select.value = current;
  }

  async function loadPropertyTypes() {
    try {
      const payload = await apiFetch(`${API_BASE}/easybroker/mls-export/property-types`);
      const types = payload?.data?.types || [];

      $('#status-property-types').textContent = String(types.length);

      const select = $('#fallback-property-type');
      const current = select.value;
      select.innerHTML = '<option value="">Auto</option>';

      types.forEach((type) => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        select.appendChild(option);
      });

      if (current) {
        select.value = current;
      }
    } catch (_e) {
      $('#status-property-types').textContent = '0';
    }
  }

  async function loadProperties(page = 1) {
    currentPage = page;

    const params = buildQueryParams(page);
    const payload = await apiFetch(`${API_BASE}/easybroker/mls-export/properties?${params.toString()}`);

    const pager = payload?.data || {};
    const rows = Array.isArray(pager.data) ? pager.data : [];

    currentRows = rows;
    lastPagination = pager;

    renderRows(rows);

    $('#status-visible-properties').textContent = String(pager.total || 0);
    $('#status-page-info').textContent = `Página ${pager.current_page || 1} / ${pager.last_page || 1}`;
    $('#paging-summary').textContent = `Mostrando ${rows.length} de ${pager.total || 0} propiedades`;

    $('#btn-prev').disabled = !(pager.current_page > 1);
    $('#btn-next').disabled = !(pager.current_page < pager.last_page);
  }

  function renderExecutionResult(responseData) {
    const stats = responseData?.stats || {};
    const rows = responseData?.results || [];

    const summary = [
      `Solicitadas: ${stats.requested || 0}`,
      `Procesadas: ${stats.processed || 0}`,
      `Creadas: ${stats.created || 0}`,
      `Actualizadas: ${stats.updated || 0}`,
      `Errores: ${stats.errors || 0}`,
      `Omitidas: ${stats.skipped || 0}`,
    ].join(' · ');

    $('#result-summary').textContent = summary;

    const condensed = rows.map((row) => ({
      success: row.success,
      action: row.action,
      property_id: row.property_id,
      easybroker_public_id: row.easybroker_public_id,
      message: row.message,
      missing_required: row.missing_required || [],
      status: row.status,
    }));

    const pre = $('#result-log');
    pre.classList.remove('hidden');
    pre.textContent = JSON.stringify(condensed, null, 2);
  }

  async function runSend({ dryRun }) {
    const ids = Array.from(selectedPropertyIds.values());
    if (!ids.length) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: {
          success: false,
          message: 'Selecciona al menos una propiedad para continuar.',
          code: 'NO_PROPERTIES_SELECTED',
        }
      }));
      return;
    }

    const targetStatus = $('#target-status').value || 'not_published';
    const fallbackPropertyType = $('#fallback-property-type').value || null;

    const payload = {
      property_ids: ids,
      target_status: targetStatus,
      fallback_property_type: fallbackPropertyType,
      dry_run: !!dryRun,
      create_if_missing_on_404: true,
    };

    const button = dryRun ? $('#btn-validate') : $('#btn-send');
    const previousText = button.textContent;
    button.disabled = true;
    button.textContent = dryRun ? 'Validando...' : 'Enviando...';

    try {
      const response = await apiFetch(`${API_BASE}/easybroker/mls-export/send`, {
        method: 'POST',
        body: JSON.stringify(payload),
      });

      if (response?.success) {
        renderExecutionResult(response.data);
        window.dispatchEvent(new CustomEvent('api:response', { detail: response }));
        await loadProperties(currentPage);
      }
    } catch (e) {
      $('#result-summary').textContent = `Error: ${e.message || 'No se pudo completar la operación.'}`;
    } finally {
      button.disabled = false;
      button.textContent = previousText;
    }
  }

  function attachEvents() {
    $('#btn-reload').addEventListener('click', async () => {
      await Promise.all([
        loadEasyBrokerStatus(),
        loadOffices(),
        loadPropertyTypes(),
      ]);
      await loadProperties(1);
    });

    $('#btn-validate').addEventListener('click', () => runSend({ dryRun: true }));
    $('#btn-send').addEventListener('click', () => runSend({ dryRun: false }));

    $('#btn-prev').addEventListener('click', () => {
      if (lastPagination?.current_page > 1) {
        loadProperties(lastPagination.current_page - 1);
      }
    });

    $('#btn-next').addEventListener('click', () => {
      if (lastPagination?.current_page < lastPagination?.last_page) {
        loadProperties(lastPagination.current_page + 1);
      }
    });

    $('#select-page').addEventListener('change', (event) => {
      if (event.target.checked) {
        currentRows.forEach((row) => selectedPropertyIds.add(row.id));
      } else {
        currentRows.forEach((row) => selectedPropertyIds.delete(row.id));
      }
      renderRows(currentRows);
      updateSelectedCounter();
    });

    ['filter-office', 'filter-synced', 'target-status', 'fallback-property-type'].forEach((id) => {
      $("#" + id).addEventListener('change', () => loadProperties(1));
    });

    $('#filter-search').addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        loadProperties(1);
      }
    });
  }

  async function init() {
    attachEvents();
    await loadEasyBrokerStatus();
    await loadOffices();
    await loadPropertyTypes();
    await loadProperties(1);
    updateSelectedCounter();
  }

  init();
});
</script>
@endsection

