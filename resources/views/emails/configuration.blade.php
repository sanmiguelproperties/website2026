@extends('layouts.app')

@section('title', 'Configuracion de correo')

@section('content')
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Configuracion de correo</h1>
      <p class="text-[var(--c-muted)] mt-1">Cuentas corporativas, credenciales IMAP/SMTP y estado de sincronizacion.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button id="cfg-btn-refresh" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Actualizar</button>
      <button id="cfg-btn-test" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Probar conexion</button>
      <button id="cfg-btn-sync" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Sincronizar inbox</button>
      <button id="cfg-btn-new-top" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm">Nueva cuenta</button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Total</p>
      <p id="cfg-stat-total" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Activas</p>
      <p id="cfg-stat-active" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Listas para enviar</p>
      <p id="cfg-stat-send" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Listas para recibir</p>
      <p id="cfg-stat-sync" class="text-2xl font-semibold">0</p>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[360px_1fr] gap-4">
    <aside class="space-y-4">
      <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <div class="flex items-center justify-between gap-3 mb-3">
          <h2 class="font-semibold">Cuentas</h2>
          <span id="cfg-list-count" class="text-xs text-[var(--c-muted)]">0 registros</span>
        </div>
        <div class="space-y-2">
          <input id="cfg-search" type="search" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" placeholder="Buscar cuenta">
          <select id="cfg-status-filter" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todas</option>
            <option value="active">Activas</option>
            <option value="inactive">Inactivas</option>
          </select>
        </div>
        <div id="cfg-accounts-list" class="mt-3 space-y-2 max-h-[520px] overflow-y-auto text-sm">
          <div class="p-3 text-[var(--c-muted)]">Sin cuentas</div>
        </div>
      </div>

      <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Estado de la cuenta</h2>
        <div id="cfg-account-status" class="space-y-2 text-sm text-[var(--c-muted)]">
          Selecciona una cuenta para ver el estado.
        </div>
      </div>
    </aside>

    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <form id="cfg-form" class="space-y-5">
        <input type="hidden" id="cfg-account-id">

        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 id="cfg-form-title" class="text-lg font-semibold">Nueva cuenta</h2>
            <p id="cfg-form-subtitle" class="text-sm text-[var(--c-muted)]">Completa los datos generales, IMAP y SMTP.</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <button id="cfg-btn-new-form" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Nueva</button>
            <button id="cfg-btn-delete" type="button" class="px-3 py-2 rounded-lg border border-[var(--c-danger)] text-[var(--c-danger)] text-sm disabled:opacity-40">Eliminar</button>
            <button type="submit" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm">Guardar</button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="space-y-4">
            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-bg)] p-4">
              <h3 class="font-semibold mb-3">Datos generales</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Nombre interno</span>
                  <input id="cfg-name" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Correo</span>
                  <input id="cfg-email" type="email" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Nombre del remitente</span>
                  <input id="cfg-from" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Usuario asignado</span>
                  <select id="cfg-user" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                    <option value="">Sin usuario</option>
                  </select>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                  <input id="cfg-active" type="checkbox" checked>
                  Activa
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Notas</span>
                  <textarea id="cfg-notes" rows="4" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"></textarea>
                </label>
              </div>
            </div>

            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-bg)] p-4">
              <h3 class="font-semibold mb-3">Estado tecnico</h3>
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Ultima sincronizacion</dt>
                  <dd id="cfg-last-sync">-</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Estado de sincronizacion</dt>
                  <dd id="cfg-last-status">-</dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-xs text-[var(--c-muted)]">Error de sincronizacion</dt>
                  <dd id="cfg-last-error" class="break-words">-</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="space-y-4">
            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-bg)] p-4">
              <h3 class="font-semibold mb-3">Recepcion IMAP</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Host IMAP</span>
                  <input id="cfg-imap-host" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Puerto IMAP</span>
                  <input id="cfg-imap-port" type="number" min="1" max="65535" value="993" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Cifrado IMAP</span>
                  <select id="cfg-imap-encryption" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                    <option value="ssl">SSL</option>
                    <option value="tls">TLS</option>
                    <option value="none">NONE</option>
                  </select>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Usuario IMAP</span>
                  <input id="cfg-imap-user" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Clave IMAP</span>
                  <input id="cfg-imap-pass" type="password" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" autocomplete="new-password">
                </label>
                <div id="cfg-imap-current" class="text-xs text-[var(--c-muted)] md:col-span-2">Sin clave guardada</div>
                <label class="inline-flex items-center gap-2 text-sm md:col-span-2">
                  <input id="cfg-imap-cert" type="checkbox">
                  Validar certificado IMAP
                </label>
                <label class="inline-flex items-center gap-2 text-sm md:col-span-2">
                  <input id="cfg-clear-imap-pass" type="checkbox">
                  Eliminar clave IMAP guardada
                </label>
              </div>
            </div>

            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-bg)] p-4">
              <h3 class="font-semibold mb-3">Envio SMTP</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Host SMTP</span>
                  <input id="cfg-smtp-host" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Puerto SMTP</span>
                  <input id="cfg-smtp-port" type="number" min="1" max="65535" value="587" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Cifrado SMTP</span>
                  <select id="cfg-smtp-encryption" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">NONE</option>
                  </select>
                </label>
                <label class="space-y-1">
                  <span class="text-xs text-[var(--c-muted)]">Usuario SMTP</span>
                  <input id="cfg-smtp-user" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">
                </label>
                <label class="space-y-1 md:col-span-2">
                  <span class="text-xs text-[var(--c-muted)]">Clave SMTP</span>
                  <input id="cfg-smtp-pass" type="password" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" autocomplete="new-password">
                </label>
                <div id="cfg-smtp-current" class="text-xs text-[var(--c-muted)] md:col-span-2">Sin clave guardada</div>
                <label class="inline-flex items-center gap-2 text-sm md:col-span-2">
                  <input id="cfg-clear-smtp-pass" type="checkbox">
                  Eliminar clave SMTP guardada
                </label>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
  </div>
</div>

<script>
(() => {
  const API = '/api/corporate-email';
  const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const state = { users: [], accounts: [], selectedId: null };
  const $ = (id) => document.getElementById(id);

  if (!token) {
    window.dispatchEvent(new CustomEvent('api:response', { detail: { success: false, message: 'No se encontro token API.', code: 'TOKEN_MISSING' } }));
    return;
  }

  async function request(url, options = {}) {
    const headers = { Accept: 'application/json', Authorization: `Bearer ${token}`, 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) };
    const cfg = { method: options.method || 'GET', headers };

    if (options.body !== undefined) {
      headers['Content-Type'] = 'application/json';
      cfg.body = JSON.stringify(options.body);
    }

    const res = await fetch(url, cfg);
    let json = null;
    try { json = await res.clone().json(); } catch (_e) {}

    if (!res.ok) {
      const detail = { success: false, message: json?.message || 'Error de API', code: json?.code || 'API_ERROR', errors: json?.errors || null, raw: json };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    return json;
  }

  function esc(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function fmt(date) {
    if (!date) return '-';
    const parsed = new Date(date);
    if (Number.isNaN(parsed.getTime())) return String(date);

    return parsed.toLocaleString('es-CO', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  function selectedAccount() {
    return state.accounts.find((account) => account.id === state.selectedId) || null;
  }

  function isReadyForSync(account) {
    return !!(account?.is_active && account.imap_host && (account.imap_username || account.email_address) && account.has_imap_password);
  }

  function isReadyForSend(account) {
    return !!(account?.is_active && account.smtp_host && (account.smtp_username || account.email_address) && account.has_smtp_password && account.email_address);
  }

  function badge(label, ok) {
    const classes = ok
      ? 'border-green-200 bg-green-50 text-green-700'
      : 'border-red-200 bg-red-50 text-red-700';
    return `<span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs ${classes}">${esc(label)}</span>`;
  }

  function setButtons() {
    const hasSelection = !!state.selectedId;
    ['cfg-btn-test', 'cfg-btn-sync', 'cfg-btn-delete'].forEach((id) => {
      const el = $(id);
      if (el) el.disabled = !hasSelection;
    });
  }

  function renderUsers() {
    const options = ['<option value="">Sin usuario</option>'];
    state.users.forEach((user) => {
      options.push(`<option value="${user.id}">${esc(user.name)} (${esc(user.email)})</option>`);
    });
    $('cfg-user').innerHTML = options.join('');
  }

  function filteredAccounts() {
    const search = $('cfg-search').value.trim().toLowerCase();
    const status = $('cfg-status-filter').value;

    return state.accounts.filter((account) => {
      const matchesSearch = !search || [
        account.name,
        account.email_address,
        account.from_name,
        account.user?.name,
        account.user?.email,
      ].some((value) => String(value || '').toLowerCase().includes(search));
      const matchesStatus = !status
        || (status === 'active' && account.is_active)
        || (status === 'inactive' && !account.is_active);

      return matchesSearch && matchesStatus;
    });
  }

  function renderStats() {
    const total = state.accounts.length;
    const active = state.accounts.filter((account) => account.is_active).length;
    const sendReady = state.accounts.filter(isReadyForSend).length;
    const syncReady = state.accounts.filter(isReadyForSync).length;

    $('cfg-stat-total').textContent = total;
    $('cfg-stat-active').textContent = active;
    $('cfg-stat-send').textContent = sendReady;
    $('cfg-stat-sync').textContent = syncReady;
  }

  function renderAccountsList() {
    const accounts = filteredAccounts();
    $('cfg-list-count').textContent = `${accounts.length} registros`;

    if (accounts.length === 0) {
      $('cfg-accounts-list').innerHTML = '<div class="p-3 text-[var(--c-muted)]">Sin cuentas</div>';
      return;
    }

    $('cfg-accounts-list').innerHTML = accounts.map((account) => {
      const selected = account.id === state.selectedId ? 'border-[var(--c-primary)] bg-[var(--c-elev)]' : 'border-[var(--c-border)]';
      const status = account.is_active ? 'Activa' : 'Inactiva';
      const statusClass = account.is_active ? 'text-green-600' : 'text-red-600';
      const user = account.user ? `${account.user.name} (${account.user.email})` : 'Sin usuario';

      return `
        <button type="button" data-id="${account.id}" class="w-full text-left rounded-lg border ${selected} p-3 hover:bg-[var(--c-elev)] transition">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-medium truncate">${esc(account.name)}</div>
              <div class="text-xs text-[var(--c-muted)] truncate">${esc(account.email_address)}</div>
            </div>
            <span class="text-xs ${statusClass}">${esc(status)}</span>
          </div>
          <div class="mt-2 flex flex-wrap gap-1">
            ${badge('SMTP', isReadyForSend(account))}
            ${badge('IMAP', isReadyForSync(account))}
          </div>
          <div class="mt-2 text-xs text-[var(--c-muted)] truncate">${esc(user)}</div>
        </button>
      `;
    }).join('');

    $('cfg-accounts-list').querySelectorAll('[data-id]').forEach((button) => {
      button.addEventListener('click', () => {
        state.selectedId = Number(button.dataset.id);
        setForm(selectedAccount());
        renderAll();
      });
    });
  }

  function renderAccountStatus() {
    const account = selectedAccount();

    if (!account) {
      $('cfg-account-status').innerHTML = 'Selecciona una cuenta para ver el estado.';
      return;
    }

    $('cfg-account-status').innerHTML = `
      <div class="flex flex-wrap gap-2">
        ${badge('Cuenta activa', !!account.is_active)}
        ${badge('Envio SMTP', isReadyForSend(account))}
        ${badge('Recepcion IMAP', isReadyForSync(account))}
      </div>
      <div class="pt-2 space-y-1">
        <div><span class="text-[var(--c-muted)]">Correo:</span> ${esc(account.email_address)}</div>
        <div><span class="text-[var(--c-muted)]">Usuario:</span> ${esc(account.user?.name || 'Sin usuario')}</div>
        <div><span class="text-[var(--c-muted)]">Ultima sincronizacion:</span> ${esc(fmt(account.last_sync_at))}</div>
        <div><span class="text-[var(--c-muted)]">Estado:</span> ${esc(account.last_sync_status || '-')}</div>
      </div>
    `;
  }

  function renderTechnicalStatus(account) {
    $('cfg-last-sync').textContent = fmt(account?.last_sync_at);
    $('cfg-last-status').textContent = account?.last_sync_status || '-';
    $('cfg-last-error').textContent = account?.last_sync_error || '-';
    $('cfg-imap-current').textContent = account?.has_imap_password
      ? `Clave IMAP guardada: ${account.imap_password_masked || '********'}`
      : 'Sin clave IMAP guardada';
    $('cfg-smtp-current').textContent = account?.has_smtp_password
      ? `Clave SMTP guardada: ${account.smtp_password_masked || '********'}`
      : 'Sin clave SMTP guardada';

    $('cfg-clear-imap-pass').disabled = !account?.has_imap_password;
    $('cfg-clear-smtp-pass').disabled = !account?.has_smtp_password;
  }

  function renderAll() {
    renderStats();
    renderAccountsList();
    renderAccountStatus();
    setButtons();
  }

  function setForm(account = null) {
    $('cfg-account-id').value = account?.id || '';
    $('cfg-form-title').textContent = account ? `Editando ${account.name}` : 'Nueva cuenta';
    $('cfg-form-subtitle').textContent = account?.email_address || 'Completa los datos generales, IMAP y SMTP.';
    $('cfg-name').value = account?.name || '';
    $('cfg-email').value = account?.email_address || '';
    $('cfg-from').value = account?.from_name || '';
    $('cfg-user').value = account?.user_id || '';
    $('cfg-active').checked = account ? !!account.is_active : true;
    $('cfg-notes').value = account?.notes || '';
    $('cfg-imap-host').value = account?.imap_host || '';
    $('cfg-imap-port').value = account?.imap_port || 993;
    $('cfg-imap-encryption').value = account?.imap_encryption || 'ssl';
    $('cfg-imap-user').value = account?.imap_username || '';
    $('cfg-imap-pass').value = '';
    $('cfg-imap-cert').checked = !!account?.imap_validate_cert;
    $('cfg-clear-imap-pass').checked = false;
    $('cfg-smtp-host').value = account?.smtp_host || '';
    $('cfg-smtp-port').value = account?.smtp_port || 587;
    $('cfg-smtp-encryption').value = account?.smtp_encryption || 'tls';
    $('cfg-smtp-user').value = account?.smtp_username || '';
    $('cfg-smtp-pass').value = '';
    $('cfg-clear-smtp-pass').checked = false;
    renderTechnicalStatus(account);
    setButtons();
  }

  function newAccount() {
    state.selectedId = null;
    setForm(null);
    renderAll();
    $('cfg-name').focus();
  }

  function formBody() {
    const id = $('cfg-account-id').value ? Number($('cfg-account-id').value) : null;
    const body = {
      user_id: $('cfg-user').value ? Number($('cfg-user').value) : null,
      name: $('cfg-name').value.trim(),
      email_address: $('cfg-email').value.trim(),
      from_name: $('cfg-from').value.trim() || null,
      imap_host: $('cfg-imap-host').value.trim(),
      imap_port: Number($('cfg-imap-port').value || 993),
      imap_encryption: $('cfg-imap-encryption').value,
      imap_validate_cert: $('cfg-imap-cert').checked,
      imap_username: $('cfg-imap-user').value.trim() || null,
      smtp_host: $('cfg-smtp-host').value.trim(),
      smtp_port: Number($('cfg-smtp-port').value || 587),
      smtp_encryption: $('cfg-smtp-encryption').value,
      smtp_username: $('cfg-smtp-user').value.trim() || null,
      is_active: $('cfg-active').checked,
      notes: $('cfg-notes').value.trim() || null,
    };

    const imapPass = $('cfg-imap-pass').value;
    const smtpPass = $('cfg-smtp-pass').value;

    if (id && $('cfg-clear-imap-pass').checked) body.imap_password = null;
    else if (imapPass.trim() !== '') body.imap_password = imapPass;

    if (id && $('cfg-clear-smtp-pass').checked) body.smtp_password = null;
    else if (smtpPass.trim() !== '') body.smtp_password = smtpPass;

    return { id, body };
  }

  async function loadUsers() {
    const payload = await request('/api/users?per_page=100&sort=asc&order=name');
    state.users = payload?.data?.data || [];
    renderUsers();
  }

  async function loadAccounts(keepSelection = true) {
    const payload = await request(`${API}/accounts?per_page=100&sort=asc&order=name`);
    state.accounts = payload?.data?.data || [];

    if (keepSelection && state.selectedId && state.accounts.some((account) => account.id === state.selectedId)) {
      setForm(selectedAccount());
    } else {
      state.selectedId = state.accounts[0]?.id || null;
      setForm(selectedAccount());
    }

    renderAll();
  }

  async function saveAccount(event) {
    event.preventDefault();
    const { id, body } = formBody();

    const payload = id
      ? await request(`${API}/accounts/${id}`, { method: 'PUT', body })
      : await request(`${API}/accounts`, { method: 'POST', body });

    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    state.selectedId = payload?.data?.id || state.selectedId;
    await loadAccounts(true);
  }

  async function deleteAccount() {
    const account = selectedAccount();
    if (!account) return;
    if (!confirm(`Eliminar la cuenta ${account.email_address}?`)) return;

    const payload = await request(`${API}/accounts/${account.id}`, { method: 'DELETE' });
    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    state.selectedId = null;
    await loadAccounts(false);
  }

  async function testConnection() {
    const account = selectedAccount();
    if (!account) return;

    const payload = await request(`${API}/accounts/${account.id}/test-connection`, { method: 'POST' });
    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
  }

  async function syncInbox() {
    const account = selectedAccount();
    if (!account) return;

    const payload = await request(`${API}/accounts/${account.id}/sync`, {
      method: 'POST',
      body: { limit: 50, folder: 'INBOX' },
    });
    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    await loadAccounts(true);
  }

  function debounce(fn, delay = 250) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  $('cfg-form').addEventListener('submit', saveAccount);
  $('cfg-btn-new-top').addEventListener('click', newAccount);
  $('cfg-btn-new-form').addEventListener('click', newAccount);
  $('cfg-btn-delete').addEventListener('click', deleteAccount);
  $('cfg-btn-refresh').addEventListener('click', () => loadAccounts(true));
  $('cfg-btn-test').addEventListener('click', () => testConnection().catch(() => {}));
  $('cfg-btn-sync').addEventListener('click', () => syncInbox().catch(() => {}));
  $('cfg-search').addEventListener('input', debounce(renderAccountsList));
  $('cfg-status-filter').addEventListener('change', renderAccountsList);

  window.dashNewAction = newAccount;

  (async () => {
    try {
      await loadUsers();
      await loadAccounts(false);
    } catch (_e) {}
  })();
})();
</script>
@endsection
