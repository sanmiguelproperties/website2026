@extends('layouts.app')

@section('title', 'Redactar correo')

@section('content')
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Redactar correo</h1>
      <p class="text-[var(--c-muted)] mt-1">Envia mensajes usando las cuentas disponibles para tu usuario.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button id="compose-refresh" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Actualizar cuentas</button>
      <button id="compose-clear" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Limpiar</button>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[360px_1fr] gap-4">
    <aside class="space-y-4">
      <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Cuentas disponibles</h2>
        <div id="compose-accounts" class="space-y-2 text-sm">
          <div class="p-3 text-[var(--c-muted)]">Cargando cuentas...</div>
        </div>
      </div>

      <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Cuenta seleccionada</h2>
        <div id="compose-account-status" class="text-sm text-[var(--c-muted)]">
          Selecciona una cuenta para redactar.
        </div>
      </div>
    </aside>

    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <form id="compose-form" class="space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
          <label class="space-y-1 lg:col-span-2">
            <span class="text-xs text-[var(--c-muted)]">Desde</span>
            <select id="compose-account" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
              <option value="">Selecciona una cuenta disponible</option>
            </select>
          </label>

          <label class="space-y-1 lg:col-span-2">
            <span class="text-xs text-[var(--c-muted)]">Para</span>
            <input id="compose-to" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="correo@dominio.com, Nombre <correo@dominio.com>" required>
          </label>

          <label class="space-y-1">
            <span class="text-xs text-[var(--c-muted)]">CC</span>
            <input id="compose-cc" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Opcional">
          </label>

          <label class="space-y-1">
            <span class="text-xs text-[var(--c-muted)]">BCC</span>
            <input id="compose-bcc" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Opcional">
          </label>

          <label class="space-y-1 lg:col-span-2">
            <span class="text-xs text-[var(--c-muted)]">Asunto</span>
            <input id="compose-subject" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Asunto del correo">
          </label>

          <label class="space-y-1 lg:col-span-2">
            <span class="text-xs text-[var(--c-muted)]">Mensaje</span>
            <textarea id="compose-body" rows="14" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Escribe el mensaje..." required></textarea>
          </label>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <p id="compose-help" class="text-xs text-[var(--c-muted)]">El envio queda registrado como salida de la cuenta seleccionada.</p>
          <button id="compose-submit" type="submit" class="px-4 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm font-medium disabled:opacity-40">Enviar correo</button>
        </div>
      </form>
    </section>
  </div>
</div>

<script>
(() => {
  const API = '/api/corporate-email/my';
  const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const state = { accounts: [], selectedId: null, sending: false };
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

  function selectedAccount() {
    return state.accounts.find((account) => account.id === state.selectedId) || null;
  }

  function canSend(account) {
    return !!(account?.is_active && account.smtp_host && (account.smtp_username || account.email_address) && account.has_smtp_password);
  }

  function badge(label, ok) {
    const classes = ok
      ? 'border-green-200 bg-green-50 text-green-700'
      : 'border-red-200 bg-red-50 text-red-700';
    return `<span class="inline-flex items-center rounded-lg border px-2 py-1 text-xs ${classes}">${esc(label)}</span>`;
  }

  function setSending(isSending) {
    state.sending = isSending;
    $('compose-submit').disabled = isSending || !canSend(selectedAccount());
    $('compose-submit').textContent = isSending ? 'Enviando...' : 'Enviar correo';
  }

  function renderAccountOptions() {
    const options = ['<option value="">Selecciona una cuenta disponible</option>'];
    state.accounts.forEach((account) => {
      const disabled = canSend(account) ? '' : ' disabled';
      options.push(`<option value="${account.id}"${disabled}>${esc(account.name)} &lt;${esc(account.email_address)}&gt;</option>`);
    });

    $('compose-account').innerHTML = options.join('');
    $('compose-account').value = state.selectedId ? String(state.selectedId) : '';
  }

  function renderAccountsList() {
    if (state.accounts.length === 0) {
      $('compose-accounts').innerHTML = '<div class="p-3 text-[var(--c-muted)]">No tienes cuentas disponibles.</div>';
      return;
    }

    $('compose-accounts').innerHTML = state.accounts.map((account) => {
      const selected = account.id === state.selectedId ? 'border-[var(--c-primary)] bg-[var(--c-elev)]' : 'border-[var(--c-border)]';
      const ready = canSend(account);
      const status = ready ? 'Lista para enviar' : 'SMTP incompleto';

      return `
        <button type="button" data-id="${account.id}" ${ready ? '' : 'disabled'} class="w-full text-left rounded-lg border ${selected} p-3 hover:bg-[var(--c-elev)] transition disabled:opacity-50">
          <div class="font-medium truncate">${esc(account.name)}</div>
          <div class="text-xs text-[var(--c-muted)] truncate">${esc(account.email_address)}</div>
          <div class="mt-2">${badge(status, ready)}</div>
        </button>
      `;
    }).join('');

    $('compose-accounts').querySelectorAll('[data-id]').forEach((button) => {
      button.addEventListener('click', () => selectAccount(Number(button.dataset.id)));
    });
  }

  function renderAccountStatus() {
    const account = selectedAccount();

    if (!account) {
      $('compose-account-status').innerHTML = 'Selecciona una cuenta para redactar.';
      $('compose-submit').disabled = true;
      return;
    }

    $('compose-account-status').innerHTML = `
      <div class="space-y-2">
        <div class="font-medium text-[var(--c-text)]">${esc(account.name)}</div>
        <div>${esc(account.email_address)}</div>
        <div class="flex flex-wrap gap-2">
          ${badge('Cuenta activa', !!account.is_active)}
          ${badge('SMTP configurado', canSend(account))}
        </div>
        <div class="text-xs text-[var(--c-muted)]">
          Servidor SMTP: ${esc(account.smtp_host || '-')} : ${esc(account.smtp_port || '-')}
        </div>
      </div>
    `;
    $('compose-submit').disabled = !canSend(account) || state.sending;
  }

  function renderAll() {
    renderAccountOptions();
    renderAccountsList();
    renderAccountStatus();
  }

  function selectAccount(id) {
    state.selectedId = id;
    $('compose-account').value = String(id);
    renderAll();
  }

  function clearForm(keepAccount = true) {
    if (!keepAccount) state.selectedId = null;
    $('compose-to').value = '';
    $('compose-cc').value = '';
    $('compose-bcc').value = '';
    $('compose-subject').value = '';
    $('compose-body').value = '';
    renderAll();
    $('compose-to').focus();
  }

  async function loadAccounts() {
    const payload = await request(`${API}/accounts`);
    state.accounts = payload?.data || [];

    if (!state.selectedId || !state.accounts.some((account) => account.id === state.selectedId && canSend(account))) {
      state.selectedId = state.accounts.find(canSend)?.id || null;
    }

    renderAll();
  }

  async function sendMessage(event) {
    event.preventDefault();

    const account = selectedAccount();
    if (!account || !canSend(account)) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: 'Selecciona una cuenta disponible con SMTP configurado.', code: 'CORP_EMAIL_ACCOUNT_REQUIRED' },
      }));
      return;
    }

    setSending(true);

    try {
      const payload = await request(`${API}/send`, {
        method: 'POST',
        body: {
          corporate_email_account_id: account.id,
          to: $('compose-to').value.trim(),
          cc: $('compose-cc').value.trim() || null,
          bcc: $('compose-bcc').value.trim() || null,
          subject: $('compose-subject').value.trim() || null,
          body_text: $('compose-body').value,
        },
      });

      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      clearForm(true);
    } finally {
      setSending(false);
    }
  }

  $('compose-account').addEventListener('change', () => {
    state.selectedId = $('compose-account').value ? Number($('compose-account').value) : null;
    renderAll();
  });
  $('compose-form').addEventListener('submit', sendMessage);
  $('compose-refresh').addEventListener('click', () => loadAccounts().catch(() => {}));
  $('compose-clear').addEventListener('click', () => clearForm(true));

  (async () => {
    try {
      await loadAccounts();
    } catch (_e) {}
  })();
})();
</script>
@endsection
