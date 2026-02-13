@extends('layouts.app')

@section('title', 'Administrar Agencias')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Agencias</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona agencias (EasyBroker). El campo <span class="font-semibold">A nuestro cargo</span> solo se modifica manualmente.</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-agency" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nueva Agencia
      </button>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
      <h2 class="text-lg font-semibold text-[var(--c-text)]">Agencias del Sistema</h2>
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <input type="text" id="search-agencies" placeholder="Buscar agencias..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
        <select id="filter-managed" class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
          <option value="">A nuestro cargo: Todos</option>
          <option value="1">Solo a nuestro cargo</option>
          <option value="0">Solo NO a nuestro cargo</option>
        </select>
        <button id="btn-refresh-agencies" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition" aria-label="Actualizar">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Agencies List -->
    <div id="agencies-list" class="space-y-3"></div>

    <!-- Pagination -->
    <div id="agencies-pagination" class="flex justify-between items-center mt-6"></div>
  </div>
</div>

<!-- Create/Edit Agency Modal -->
<div id="agency-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-labelledby="agency-modal-title">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="agency-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Agencia</h3>
        <p class="text-xs text-[var(--c-muted)] mt-1">El campo <span class="font-semibold">A nuestro cargo</span> se guarda con un endpoint manual separado.</p>
      </div>

      <form id="agency-form" class="p-6 space-y-4">
        <!-- PK -->
        <div>
          <label for="agency-id" class="block text-sm font-medium text-[var(--c-text)] mb-1">ID (EasyBroker) <span class="text-red-400">*</span></label>
          <input type="number" id="agency-id" name="id" min="1" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
          <p class="text-xs text-[var(--c-muted)] mt-1">Este ID es la PK y no debe cambiarse.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="agency-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre <span class="text-red-400">*</span></label>
            <input type="text" id="agency-name" name="name" maxlength="255" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
          </div>
          <div>
            <label for="agency-account-owner" class="block text-sm font-medium text-[var(--c-text)] mb-1">Account owner</label>
            <input type="text" id="agency-account-owner" name="account_owner" maxlength="255" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="agency-email" class="block text-sm font-medium text-[var(--c-text)] mb-1">Email</label>
            <input type="email" id="agency-email" name="email" maxlength="255" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent">
          </div>
          <div>
            <label for="agency-phone" class="block text-sm font-medium text-[var(--c-text)] mb-1">Teléfono</label>
            <input type="text" id="agency-phone" name="phone" maxlength="50" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent">
          </div>
        </div>

        <div>
          <label for="agency-logo-url" class="block text-sm font-medium text-[var(--c-text)] mb-1">Logo URL</label>
          <input type="url" id="agency-logo-url" name="logo_url" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" placeholder="https://...">
        </div>

        <div>
          <label class="flex items-center">
            <input type="checkbox" id="agency-managed-by-us" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
            <span class="ml-2 text-sm font-medium text-[var(--c-text)]">A nuestro cargo</span>
          </label>
          <p class="text-xs text-[var(--c-muted)] mt-1">No se modifica por el CRUD estándar; se guarda con un endpoint manual.</p>
        </div>

        <div>
          <label for="agency-raw-payload" class="block text-sm font-medium text-[var(--c-text)] mb-1">Raw payload (JSON)</label>
          <textarea id="agency-raw-payload" rows="4" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent font-mono text-xs" placeholder='{"source":"manual"}'></textarea>
          <p class="text-xs text-[var(--c-muted)] mt-1">Opcional. Si lo llenas, debe ser JSON válido.</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-agency" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;

  // Hook global button
  window.dashNewAction = () => openAgencyModal();

  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido. Por favor, inicia sesión nuevamente.');
    return;
  }

  loadAgencies();

  document.getElementById('btn-create-agency').addEventListener('click', () => openAgencyModal());
  document.getElementById('btn-refresh-agencies').addEventListener('click', () => loadAgencies(1));
  document.getElementById('search-agencies').addEventListener('input', debounce(() => loadAgencies(1), 300));
  document.getElementById('filter-managed').addEventListener('change', () => loadAgencies(1));
  document.getElementById('agency-form').addEventListener('submit', saveAgency);
  document.getElementById('btn-cancel-agency').addEventListener('click', () => closeAgencyModal());

  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const isMutation = ['POST','PUT','PATCH','DELETE'].includes(method);
    const headers = {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json',
      ...(options.headers || {}),
    };
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

  async function loadAgencies(page = 1) {
    const search = document.getElementById('search-agencies').value;
    const managed = document.getElementById('filter-managed').value;

    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', '15');
    p.set('order', 'updated_at');
    p.set('sort', 'desc');
    if (search) p.set('search', search);
    if (managed !== '') p.set('is_managed_by_us', managed);

    const url = `${API_BASE}/agencies?${p.toString()}`;
    try {
      const payload = await apiFetch(url);
      if (payload?.success) {
        renderAgencies(payload.data);
        renderPagination(payload.data);
      }
    } catch (_e) {}
  }

  function badgeManaged(v) {
    if (v) {
      return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">A nuestro cargo</span>';
    }
    return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[var(--c-elev)] text-[var(--c-muted)] border border-[var(--c-border)]">—</span>';
  }

  function renderAgencies(agenciesData) {
    const container = document.getElementById('agencies-list');
    container.innerHTML = '';

    if (!agenciesData?.data?.length) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron agencias</p>';
      return;
    }

    agenciesData.data.forEach(agency => {
      const el = document.createElement('div');
      el.className = 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';

      const logo = agency.logo_url
        ? `<img src="${escapeHtml(agency.logo_url)}" alt="" class="w-10 h-10 rounded-xl object-cover border border-[var(--c-border)]" onerror="this.style.display='none';">`
        : `<div class="w-10 h-10 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] grid place-items-center text-[var(--c-muted)]">A</div>`;

      el.innerHTML = `
        <div class="flex items-center gap-4 min-w-0">
          ${logo}
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="font-medium text-[var(--c-text)] truncate">${escapeHtml(agency.name || '—')}</h3>
              <span class="text-xs text-[var(--c-muted)]">#${escapeHtml(agency.id)}</span>
              ${badgeManaged(!!agency.is_managed_by_us)}
            </div>
            <p class="text-sm text-[var(--c-muted)] truncate">${escapeHtml(agency.email || '—')} ${agency.phone ? '• ' + escapeHtml(agency.phone) : ''}</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-end">
          <label class="inline-flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)]">
            <input type="checkbox" class="toggle-managed rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" data-id="${escapeHtml(agency.id)}" ${agency.is_managed_by_us ? 'checked' : ''}>
            A nuestro cargo
          </label>
          <button class="edit-agency-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition"
            data-id="${escapeHtml(agency.id)}"
            data-name="${escapeHtml(agency.name || '')}"
            data-account-owner="${escapeHtml(agency.account_owner || '')}"
            data-logo-url="${escapeHtml(agency.logo_url || '')}"
            data-phone="${escapeHtml(agency.phone || '')}"
            data-email="${escapeHtml(agency.email || '')}"
            data-managed="${agency.is_managed_by_us ? '1' : '0'}"
            data-raw-payload='${escapeHtml(JSON.stringify(agency.raw_payload ?? null))}'>
            Editar
          </button>
          <button class="delete-agency-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${escapeHtml(agency.id)}">
            Eliminar
          </button>
        </div>
      `;

      container.appendChild(el);
    });

    container.querySelectorAll('.edit-agency-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const d = e.currentTarget.dataset;
        const raw = safeParseJson(d.rawPayload);
        openAgencyModal(
          d.id,
          d.name,
          d.accountOwner,
          d.logoUrl,
          d.phone,
          d.email,
          d.managed === '1',
          raw
        );
      });
    });

    container.querySelectorAll('.delete-agency-btn').forEach(btn => {
      btn.addEventListener('click', (e) => deleteAgency(e.currentTarget.dataset.id));
    });

    container.querySelectorAll('.toggle-managed').forEach(chk => {
      chk.addEventListener('change', async (e) => {
        const id = e.currentTarget.dataset.id;
        const val = !!e.currentTarget.checked;
        try {
          const payload = await apiFetch(`${API_BASE}/agencies/${id}/managed-by-us`, {
            method: 'PATCH',
            body: JSON.stringify({ is_managed_by_us: val }),
          });
          if (payload?.success) {
            window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
            loadAgencies(1);
          }
        } catch (_e) {
          e.currentTarget.checked = !val;
        }
      });
    });
  }

  function renderPagination(agenciesData) {
    const container = document.getElementById('agencies-pagination');
    container.innerHTML = '';
    if (!agenciesData || agenciesData.last_page <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Anterior';
    prevBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    prevBtn.disabled = !agenciesData.prev_page_url;
    prevBtn.addEventListener('click', () => loadAgencies(agenciesData.current_page - 1));

    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Siguiente';
    nextBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    nextBtn.disabled = !agenciesData.next_page_url;
    nextBtn.addEventListener('click', () => loadAgencies(agenciesData.current_page + 1));

    const pageInfo = document.createElement('div');
    pageInfo.textContent = `Página ${agenciesData.current_page} de ${agenciesData.last_page}`;
    pageInfo.className = 'text-sm text-[var(--c-muted)]';

    container.appendChild(prevBtn);
    container.appendChild(pageInfo);
    container.appendChild(nextBtn);
  }

  function openAgencyModal(id = null, name = '', accountOwner = '', logoUrl = '', phone = '', email = '', managedByUs = false, rawPayload = null) {
    const modal = document.getElementById('agency-modal');
    const title = document.getElementById('agency-modal-title');

    const idField = document.getElementById('agency-id');
    const nameField = document.getElementById('agency-name');
    const accountOwnerField = document.getElementById('agency-account-owner');
    const logoUrlField = document.getElementById('agency-logo-url');
    const phoneField = document.getElementById('agency-phone');
    const emailField = document.getElementById('agency-email');
    const managedField = document.getElementById('agency-managed-by-us');
    const rawField = document.getElementById('agency-raw-payload');

    if (id) {
      title.textContent = 'Editar Agencia';
      idField.value = id;
      idField.disabled = true;
      nameField.value = name;
      accountOwnerField.value = accountOwner;
      logoUrlField.value = logoUrl;
      phoneField.value = phone;
      emailField.value = email;
      managedField.checked = !!managedByUs;
      rawField.value = rawPayload == null ? '' : JSON.stringify(rawPayload, null, 2);
    } else {
      title.textContent = 'Crear Agencia';
      idField.value = '';
      idField.disabled = false;
      nameField.value = '';
      accountOwnerField.value = '';
      logoUrlField.value = '';
      phoneField.value = '';
      emailField.value = '';
      managedField.checked = false;
      rawField.value = '';
    }

    modal.classList.remove('hidden');
  }

  function closeAgencyModal() {
    document.getElementById('agency-modal').classList.add('hidden');
  }

  async function saveAgency(e) {
    e.preventDefault();

    const id = document.getElementById('agency-id').value;
    const name = document.getElementById('agency-name').value;
    const accountOwner = document.getElementById('agency-account-owner').value;
    const logoUrl = document.getElementById('agency-logo-url').value;
    const phone = document.getElementById('agency-phone').value;
    const email = document.getElementById('agency-email').value;
    const managedByUs = document.getElementById('agency-managed-by-us').checked;
    const rawText = document.getElementById('agency-raw-payload').value.trim();

    let rawPayload = null;
    if (rawText) {
      try {
        rawPayload = JSON.parse(rawText);
      } catch (_e) {
        showError('JSON inválido', 'El campo raw payload debe ser JSON válido.');
        return;
      }
    }

    const isEdit = document.getElementById('agency-id').disabled;
    const url = isEdit ? `${API_BASE}/agencies/${id}` : `${API_BASE}/agencies`;
    const method = isEdit ? 'PATCH' : 'POST';

    const payload = {
      ...(isEdit ? {} : { id: parseInt(id, 10) }),
      name,
      account_owner: accountOwner || null,
      logo_url: logoUrl || null,
      phone: phone || null,
      email: email || null,
      raw_payload: rawPayload,
    };

    try {
      const res = await apiFetch(url, {
        method,
        body: JSON.stringify(payload),
      });

      // Guardar el booleano manualmente (endpoint dedicado)
      const agencyId = isEdit ? id : (res?.data?.id ?? id);
      const resManaged = await apiFetch(`${API_BASE}/agencies/${agencyId}/managed-by-us`, {
        method: 'PATCH',
        body: JSON.stringify({ is_managed_by_us: !!managedByUs }),
      });

      window.dispatchEvent(new CustomEvent('api:response', { detail: resManaged || res }));
      closeAgencyModal();
      loadAgencies(1);
    } catch (_e) {
      // error ya disparado por apiFetch
    }
  }

  async function deleteAgency(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta agencia?')) return;

    try {
      const payload = await apiFetch(`${API_BASE}/agencies/${id}`, { method: 'DELETE' });
      if (payload?.success) {
        loadAgencies(1);
        window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      }
    } catch (_e) {}
  }

  function showError(_title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: 'CLIENT_ERROR',
        errors: { general: [message] },
      }
    }));
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function safeParseJson(s) {
    if (!s) return null;
    try { return JSON.parse(s); } catch (_e) { return null; }
  }
});
</script>
@endsection

