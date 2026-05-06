@extends('layouts.app')

@section('title', 'Mi bandeja de salida')

@section('content')
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Mi bandeja de salida</h1>
      <p class="text-[var(--c-muted)] mt-1">Correos enviados desde las cuentas disponibles para tu usuario.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('corporate-email.compose') }}" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm">Redactar</a>
      <button id="outbox-refresh" type="button" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Actualizar</button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Cuentas disponibles</p>
      <p id="outbox-stat-accounts" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Mensajes del filtro</p>
      <p id="outbox-stat-total" class="text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Fallidos en pagina</p>
      <p id="outbox-stat-failed" class="text-2xl font-semibold">0</p>
    </div>
  </div>

  <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
    <div class="grid grid-cols-1 md:grid-cols-[minmax(220px,1fr)_180px_minmax(220px,1fr)] gap-2">
      <select id="outbox-account" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
        <option value="">Todas las cuentas disponibles</option>
      </select>
      <select id="outbox-status" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
        <option value="">Todo estado</option>
        <option value="sent">Enviados</option>
        <option value="failed">Fallidos</option>
      </select>
      <input id="outbox-search" type="search" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" placeholder="Buscar por asunto">
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[430px_1fr] gap-4">
    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
      <div class="px-4 py-3 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <h2 class="font-semibold">Salida</h2>
        <span id="outbox-page-info" class="text-xs text-[var(--c-muted)]">Pagina 1</span>
      </div>
      <div id="outbox-list" class="divide-y divide-[var(--c-border)] max-h-[590px] overflow-y-auto">
        <div class="p-4 text-sm text-[var(--c-muted)]">Sin mensajes</div>
      </div>
      <div class="px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <button id="outbox-prev" type="button" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Anterior</button>
        <button id="outbox-next" type="button" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-40">Siguiente</button>
      </div>
    </section>

    <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
      <div id="outbox-detail" class="p-4 text-sm text-[var(--c-muted)]">
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
    return $('outbox-account').value ? Number($('outbox-account').value) : null;
  }

  function renderAccounts() {
    const options = ['<option value="">Todas las cuentas disponibles</option>'];
    state.accounts.forEach((account) => {
      options.push(`<option value="${account.id}">${esc(account.name)} &lt;${esc(account.email_address)}&gt;</option>`);
    });
    $('outbox-account').innerHTML = options.join('');
    $('outbox-stat-accounts').textContent = state.accounts.length;
  }

  function renderStats() {
    const pag = state.messages;
    const total = pag?.total ?? 0;
    const failed = Array.isArray(pag?.data)
      ? pag.data.filter((message) => message.status === 'failed').length
      : 0;

    $('outbox-stat-total').textContent = total;
    $('outbox-stat-failed').textContent = failed;
  }

  function statusBadge(status) {
    if (status === 'failed') {
      return '<span class="shrink-0 text-xs rounded-lg border border-[var(--c-danger)] text-[var(--c-danger)] px-2 py-0.5">Fallido</span>';
    }

    return '<span class="shrink-0 text-xs rounded-lg border border-green-200 text-green-700 px-2 py-0.5">Enviado</span>';
  }

  function renderMessages() {
    const pag = state.messages;
    renderStats();

    if (!pag || !Array.isArray(pag.data) || pag.data.length === 0) {
      $('outbox-list').innerHTML = '<div class="p-4 text-sm text-[var(--c-muted)]">Sin mensajes</div>';
      $('outbox-prev').disabled = true;
      $('outbox-next').disabled = true;
      $('outbox-page-info').textContent = 'Pagina 1';
      return;
    }

    $('outbox-list').innerHTML = pag.data.map((message) => {
      const to = recipients(message.to_recipients);
      const account = message.account?.email_address || '-';
      const date = message.sent_at || message.created_at;
      const selected = message.id === state.selectedMessageId ? 'bg-[var(--c-elev)]' : '';

      return `
        <button type="button" data-id="${message.id}" class="w-full text-left p-4 hover:bg-[var(--c-elev)] transition ${selected}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-medium truncate">${esc(message.subject || '(Sin asunto)')}</div>
              <div class="text-xs text-[var(--c-muted)] truncate">Para: ${esc(to)}</div>
            </div>
            ${statusBadge(message.status)}
          </div>
          <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--c-muted)]">
            <span>${esc(fmt(date))}</span>
            <span class="truncate">${esc(account)}</span>
          </div>
        </button>
      `;
    }).join('');

    $('outbox-list').querySelectorAll('[data-id]').forEach((button) => {
      button.addEventListener('click', () => loadMessageDetail(Number(button.dataset.id)));
    });

    $('outbox-prev').disabled = !pag.prev_page_url;
    $('outbox-next').disabled = !pag.next_page_url;
    $('outbox-page-info').textContent = `Pagina ${pag.current_page} de ${pag.last_page}`;
  }

  function renderDetail(message = null) {
    if (!message) {
      $('outbox-detail').innerHTML = 'Selecciona un mensaje para ver el detalle.';
      return;
    }

    const body = message.body_text || (message.body_html ? message.body_html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : '') || '(Sin cuerpo de texto)';
    const error = message.meta?.error
      ? `<div class="rounded-lg border border-[var(--c-danger)] text-[var(--c-danger)] p-3">${esc(message.meta.error)}</div>`
      : '';

    $('outbox-detail').innerHTML = `
      <div class="space-y-4">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2 class="text-xl font-semibold text-[var(--c-text)]">${esc(message.subject || '(Sin asunto)')}</h2>
            <p class="text-xs text-[var(--c-muted)] mt-1">${esc(fmt(message.sent_at || message.created_at))}</p>
          </div>
          ${statusBadge(message.status)}
        </div>

        ${error}

        <div class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] p-3 space-y-1">
          <div><span class="font-semibold">Desde:</span> ${esc(message.account?.from_name ? `${message.account.from_name} <${message.account.email_address || '-'}>` : (message.account?.email_address || '-'))}</div>
          <div><span class="font-semibold">Para:</span> ${esc(recipients(message.to_recipients))}</div>
          <div><span class="font-semibold">CC:</span> ${esc(recipients(message.cc_recipients))}</div>
          <div><span class="font-semibold">BCC:</span> ${esc(recipients(message.bcc_recipients))}</div>
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
    if ($('outbox-status').value) params.set('status', $('outbox-status').value);
    if ($('outbox-search').value.trim()) params.set('search', $('outbox-search').value.trim());

    const payload = await request(`${API}/outbox?${params.toString()}`);
    state.messages = payload?.data || null;
    state.page = page;
    renderMessages();
  }

  async function loadMessageDetail(id) {
    const payload = await request(`${API}/outbox/${id}`);
    const message = payload?.data;

    if (!message) return;

    state.selectedMessageId = message.id;
    renderMessages();
    renderDetail(message);
  }

  function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  $('outbox-account').addEventListener('change', () => loadMessages(1));
  $('outbox-status').addEventListener('change', () => loadMessages(1));
  $('outbox-search').addEventListener('input', debounce(() => loadMessages(1), 300));
  $('outbox-refresh').addEventListener('click', () => loadMessages(state.page || 1));
  $('outbox-prev').addEventListener('click', () => {
    if (state.messages?.prev_page_url) loadMessages(state.messages.current_page - 1);
  });
  $('outbox-next').addEventListener('click', () => {
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
