@extends('layouts.app')

@section('title', 'Mi bandeja de entrada')

@section('content')
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Mi bandeja de entrada</h1>
      <p class="text-[var(--c-muted)] mt-1">Mensajes entrantes de las cuentas disponibles para tu usuario.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button id="inbox-refresh" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Actualizar</button>
      <button id="inbox-sync" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm disabled:opacity-40">Sincronizar cuenta</button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Cuentas disponibles</p>
      <p id="inbox-stat-accounts" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Mensajes del filtro</p>
      <p id="inbox-stat-total" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">No leidos</p>
      <p id="inbox-stat-unread" class="text-2xl font-semibold">0</p>
    </div>
  </div>

  <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
    <div class="grid grid-cols-1 md:grid-cols-[minmax(220px,1fr)_180px_minmax(220px,1fr)] gap-2">
      <select id="inbox-account" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
        <option value="">Todas las cuentas disponibles</option>
      </select>
      <select id="inbox-status" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
        <option value="">Todo estado</option>
        <option value="unread">No leidos</option>
        <option value="read">Leidos</option>
      </select>
      <input id="inbox-search" type="search" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" placeholder="Buscar por asunto o remitente">
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[430px_1fr] gap-4">
    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
      <div class="px-4 py-3 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <h2 class="font-semibold">Entrada</h2>
        <span id="inbox-page-info" class="text-xs text-[var(--c-muted)]">Pagina 1</span>
      </div>
      <div id="inbox-list" class="divide-y divide-[var(--c-border)] max-h-[590px] overflow-y-auto">
        <div class="p-4 text-sm text-[var(--c-muted)]">Sin mensajes</div>
      </div>
      <div class="px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <button id="inbox-prev" type="button" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Anterior</button>
        <button id="inbox-next" type="button" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Siguiente</button>
      </div>
    </section>

    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
      <div id="inbox-detail" class="p-4 text-sm text-[var(--c-muted)]">
        Selecciona un mensaje para ver el detalle.
      </div>
    </section>
  </div>
</div>

<script>
(() => {
  const API = '/api/corporate-email/my';
  const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const state = { accounts: [], messages: null, page: 1, selectedMessageId: null };
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

  function recipients(list) {
    if (!Array.isArray(list) || list.length === 0) return '-';

    return list
      .map((recipient) => recipient?.name ? `${recipient.name} <${recipient.email}>` : recipient?.email)
      .filter(Boolean)
      .join(', ') || '-';
  }

  function selectedAccountId() {
    return $('inbox-account').value ? Number($('inbox-account').value) : null;
  }

  function renderAccounts() {
    const options = ['<option value="">Todas las cuentas disponibles</option>'];
    state.accounts.forEach((account) => {
      options.push(`<option value="${account.id}">${esc(account.name)} &lt;${esc(account.email_address)}&gt;</option>`);
    });
    $('inbox-account').innerHTML = options.join('');
    $('inbox-stat-accounts').textContent = state.accounts.length;
    $('inbox-sync').disabled = state.accounts.length === 0;
  }

  function renderStats() {
    const pag = state.messages;
    const total = pag?.total ?? 0;
    const unread = Array.isArray(pag?.data)
      ? pag.data.filter((message) => message.status === 'unread').length
      : 0;

    $('inbox-stat-total').textContent = total;
    $('inbox-stat-unread').textContent = unread;
  }

  function renderMessages() {
    const pag = state.messages;
    renderStats();

    if (!pag || !Array.isArray(pag.data) || pag.data.length === 0) {
      $('inbox-list').innerHTML = '<div class="p-4 text-sm text-[var(--c-muted)]">Sin mensajes</div>';
      $('inbox-prev').disabled = true;
      $('inbox-next').disabled = true;
      $('inbox-page-info').textContent = 'Pagina 1';
      return;
    }

    $('inbox-list').innerHTML = pag.data.map((message) => {
      const from = message.from_name || message.from_email || '-';
      const account = message.account?.email_address || '-';
      const date = message.received_at || message.created_at;
      const unread = message.status === 'unread';
      const selected = message.id === state.selectedMessageId ? 'bg-[var(--c-elev)]' : '';
      const titleClass = unread ? 'font-semibold' : 'font-medium';
      const status = unread
        ? '<span class="shrink-0 text-xs rounded-lg border border-[var(--c-primary)] px-2 py-0.5">Nuevo</span>'
        : '';

      return `
        <button type="button" data-id="${message.id}" class="w-full text-left p-4 hover:bg-[var(--c-elev)] transition ${selected}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="${titleClass} truncate">${esc(message.subject || '(Sin asunto)')}</div>
              <div class="text-xs text-[var(--c-muted)] truncate">${esc(from)}</div>
            </div>
            ${status}
          </div>
          <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--c-muted)]">
            <span>${esc(fmt(date))}</span>
            <span class="truncate">${esc(account)}</span>
          </div>
        </button>
      `;
    }).join('');

    $('inbox-list').querySelectorAll('[data-id]').forEach((button) => {
      button.addEventListener('click', () => loadMessageDetail(Number(button.dataset.id)));
    });

    $('inbox-prev').disabled = !pag.prev_page_url;
    $('inbox-next').disabled = !pag.next_page_url;
    $('inbox-page-info').textContent = `Pagina ${pag.current_page} de ${pag.last_page}`;
  }

  function renderDetail(message = null) {
    if (!message) {
      $('inbox-detail').innerHTML = 'Selecciona un mensaje para ver el detalle.';
      return;
    }

    const from = message.from_name
      ? `${message.from_name} <${message.from_email || '-'}>`
      : (message.from_email || '-');
    const body = message.body_text || (message.body_html ? message.body_html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : '') || '(Sin cuerpo de texto)';

    $('inbox-detail').innerHTML = `
      <div class="space-y-4">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2 class="text-xl font-semibold text-[var(--c-text)]">${esc(message.subject || '(Sin asunto)')}</h2>
            <p class="text-xs text-[var(--c-muted)] mt-1">${esc(fmt(message.received_at || message.created_at))}</p>
          </div>
          <span class="text-xs rounded-lg border border-[var(--c-border)] px-2 py-1">${esc(message.status || '-')}</span>
        </div>

        <div class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] p-3 space-y-1">
          <div><span class="font-semibold">De:</span> ${esc(from)}</div>
          <div><span class="font-semibold">Para:</span> ${esc(recipients(message.to_recipients))}</div>
          <div><span class="font-semibold">CC:</span> ${esc(recipients(message.cc_recipients))}</div>
          <div><span class="font-semibold">Cuenta:</span> ${esc(message.account?.email_address || '-')}</div>
        </div>

        <pre class="whitespace-pre-wrap rounded-lg border border-[var(--c-border)] bg-[var(--c-bg)] p-4 min-h-[320px] max-h-[520px] overflow-auto text-sm text-[var(--c-text)]">${esc(body)}</pre>
      </div>
    `;
  }

  async function loadAccounts() {
    const payload = await request(`${API}/accounts`);
    state.accounts = payload?.data || [];
    renderAccounts();
  }

  async function loadMessages(page = 1) {
    const params = new URLSearchParams({ page: String(page), per_page: '20' });
    const accountId = selectedAccountId();

    if (accountId) params.set('corporate_email_account_id', String(accountId));
    if ($('inbox-status').value) params.set('status', $('inbox-status').value);
    if ($('inbox-search').value.trim()) params.set('search', $('inbox-search').value.trim());

    const payload = await request(`${API}/inbox?${params.toString()}`);
    state.messages = payload?.data || null;
    state.page = page;
    renderMessages();
  }

  async function loadMessageDetail(id) {
    const payload = await request(`${API}/inbox/${id}`);
    const message = payload?.data;

    if (!message) return;

    state.selectedMessageId = message.id;
    if (Array.isArray(state.messages?.data)) {
      state.messages.data = state.messages.data.map((item) => item.id === message.id ? { ...item, status: message.status, read_at: message.read_at } : item);
    }

    renderMessages();
    renderDetail(message);
  }

  async function syncSelectedAccount() {
    const accountId = selectedAccountId();

    if (!accountId) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: 'Selecciona una cuenta para sincronizar.', code: 'CORP_EMAIL_ACCOUNT_REQUIRED' },
      }));
      return;
    }

    const payload = await request(`${API}/accounts/${accountId}/sync`, {
      method: 'POST',
      body: { limit: 50, folder: 'INBOX' },
    });

    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    await loadMessages(1);
  }

  function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  $('inbox-account').addEventListener('change', () => loadMessages(1));
  $('inbox-status').addEventListener('change', () => loadMessages(1));
  $('inbox-search').addEventListener('input', debounce(() => loadMessages(1), 300));
  $('inbox-refresh').addEventListener('click', () => loadMessages(state.page || 1));
  $('inbox-sync').addEventListener('click', () => syncSelectedAccount().catch(() => {}));
  $('inbox-prev').addEventListener('click', () => {
    if (state.messages?.prev_page_url) loadMessages(state.messages.current_page - 1);
  });
  $('inbox-next').addEventListener('click', () => {
    if (state.messages?.next_page_url) loadMessages(state.messages.current_page + 1);
  });

  (async () => {
    try {
      await loadAccounts();
      await loadMessages(1);
    } catch (_e) {}
  })();
})();
</script>
@endsection
