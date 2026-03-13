@extends('layouts.app')

@section('title', 'Correos Corporativos')

@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Correos Corporativos</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestion de cuentas, envio y bandeja interna.</p>
    </div>
    <div class="flex gap-2">
      <button id="btn-refresh" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">Actualizar</button>
      <button id="btn-test" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">Probar conexion</button>
      <button id="btn-sync" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]">Sincronizar inbox</button>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="space-y-4">
      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Cuenta</h2>
        <form id="account-form" class="space-y-2">
          <input type="hidden" id="account-id">
          <input id="acc-name" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre interno" required>
          <input id="acc-email" type="email" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Correo" required>
          <input id="acc-from" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Nombre remitente">
          <select id="acc-user" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"><option value="">Sin usuario</option></select>

          <div class="text-xs text-[var(--c-muted)] mt-2">IMAP</div>
          <div class="grid grid-cols-2 gap-2">
            <input id="imap-host" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Host" required>
            <input id="imap-port" type="number" value="993" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <select id="imap-encryption" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"><option value="ssl">SSL</option><option value="tls">TLS</option><option value="none">NONE</option></select>
            <input id="imap-user" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Usuario IMAP">
          </div>
          <input id="imap-pass" type="password" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Password IMAP (opcional al editar)">
          <label class="inline-flex items-center gap-2 text-xs"><input id="imap-cert" type="checkbox"> Validar certificado</label>

          <div class="text-xs text-[var(--c-muted)] mt-2">SMTP</div>
          <div class="grid grid-cols-2 gap-2">
            <input id="smtp-host" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Host" required>
            <input id="smtp-port" type="number" value="587" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" required>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <select id="smtp-encryption" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">NONE</option></select>
            <input id="smtp-user" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Usuario SMTP">
          </div>
          <input id="smtp-pass" type="password" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Password SMTP (opcional al editar)">

          <textarea id="acc-notes" rows="2" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Notas"></textarea>
          <label class="inline-flex items-center gap-2 text-sm"><input id="acc-active" type="checkbox" checked> Activa</label>

          <div class="flex gap-2 justify-end">
            <button type="button" id="btn-new" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]">Nueva</button>
            <button type="button" id="btn-delete" class="px-3 py-2 rounded-lg border border-[var(--c-danger)] text-[var(--c-danger)]">Eliminar</button>
            <button type="submit" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]">Guardar</button>
          </div>
        </form>
      </div>

      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Cuentas</h2>
        <div id="accounts-list" class="space-y-2 max-h-[260px] overflow-y-auto text-sm text-[var(--c-muted)]">Sin cuentas</div>
      </div>
    </div>

    <div class="xl:col-span-2 space-y-4">
      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold mb-3">Enviar correo</h2>
        <form id="send-form" class="space-y-2">
          <select id="send-account" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"></select>
          <input id="send-to" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Para (separar por coma)" required>
          <div class="grid grid-cols-2 gap-2">
            <input id="send-cc" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="CC opcional">
            <input id="send-bcc" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="BCC opcional">
          </div>
          <input id="send-subject" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Asunto">
          <textarea id="send-body" rows="5" class="w-full px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Mensaje" required></textarea>
          <div class="text-right"><button type="submit" class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]">Enviar</button></div>
        </form>
      </div>

      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
          <select id="f-account" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"></select>
          <select id="f-direction" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"><option value="">Todo</option><option value="incoming">Entrantes</option><option value="outgoing">Salientes</option></select>
          <select id="f-status" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]"><option value="">Todo estado</option><option value="unread">No leido</option><option value="read">Leido</option><option value="sent">Enviado</option><option value="failed">Fallido</option></select>
          <input id="f-search" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)]" placeholder="Buscar...">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
          <div>
            <div id="messages" class="max-h-[380px] overflow-y-auto divide-y divide-[var(--c-border)] text-sm"><div class="p-3 text-[var(--c-muted)]">Sin mensajes</div></div>
            <div class="flex items-center justify-between mt-3">
              <button id="btn-prev" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] disabled:opacity-40">Anterior</button>
              <span id="page-info" class="text-xs text-[var(--c-muted)]">Pagina 1</span>
              <button id="btn-next" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] disabled:opacity-40">Siguiente</button>
            </div>
          </div>
          <div id="detail" class="text-sm text-[var(--c-muted)]">Selecciona un mensaje para ver detalle.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const API = '/api/corporate-email';
  const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  if (!token) {
    window.dispatchEvent(new CustomEvent('api:response', { detail: { success: false, message: 'No se encontro token API.', code: 'TOKEN_MISSING' } }));
    return;
  }

  const state = { users: [], accounts: [], selected: null, messages: null, page: 1, selectedMessage: null };
  const $ = (id) => document.getElementById(id);

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

  function esc(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function fmt(date) {
    if (!date) return '-';
    const d = new Date(date);
    if (Number.isNaN(d.getTime())) return String(date);
    return d.toLocaleString('es-CO', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
  }

  function renderAccountOptions() {
    const options = ['<option value="">Selecciona cuenta</option>'];
    const filter = ['<option value="">Todas las cuentas</option>'];
    state.accounts.forEach((a) => {
      const label = `${a.name} <${a.email_address}>`;
      options.push(`<option value="${a.id}">${esc(label)}</option>`);
      filter.push(`<option value="${a.id}">${esc(label)}</option>`);
    });
    $('send-account').innerHTML = options.join('');
    $('f-account').innerHTML = filter.join('');
    if (state.selected) {
      $('send-account').value = String(state.selected);
      $('f-account').value = String(state.selected);
    }
  }

  function renderUsers() {
    const options = ['<option value="">Sin usuario</option>'];
    state.users.forEach((u) => options.push(`<option value="${u.id}">${esc(u.name)} (${esc(u.email)})</option>`));
    $('acc-user').innerHTML = options.join('');
  }

  function setForm(a = null) {
    $('account-id').value = a?.id || '';
    $('acc-name').value = a?.name || '';
    $('acc-email').value = a?.email_address || '';
    $('acc-from').value = a?.from_name || '';
    $('acc-user').value = a?.user_id || '';
    $('imap-host').value = a?.imap_host || '';
    $('imap-port').value = a?.imap_port || 993;
    $('imap-encryption').value = a?.imap_encryption || 'ssl';
    $('imap-user').value = a?.imap_username || '';
    $('imap-pass').value = '';
    $('imap-cert').checked = !!a?.imap_validate_cert;
    $('smtp-host').value = a?.smtp_host || '';
    $('smtp-port').value = a?.smtp_port || 587;
    $('smtp-encryption').value = a?.smtp_encryption || 'tls';
    $('smtp-user').value = a?.smtp_username || '';
    $('smtp-pass').value = '';
    $('acc-notes').value = a?.notes || '';
    $('acc-active').checked = a ? !!a.is_active : true;
  }

  function renderAccountsList() {
    if (state.accounts.length === 0) {
      $('accounts-list').innerHTML = 'Sin cuentas';
      return;
    }
    $('accounts-list').innerHTML = state.accounts.map((a) => {
      const active = a.is_active ? 'text-green-500' : 'text-red-500';
      const selected = state.selected === a.id ? 'border-[var(--c-primary)] bg-[var(--c-elev)]' : 'border-[var(--c-border)]';
      return `<button type="button" class="w-full text-left p-2 rounded-lg border ${selected}" data-id="${a.id}"><div class="flex justify-between"><span>${esc(a.name)}</span><span class="text-xs ${active}">${a.is_active ? 'Activa' : 'Inactiva'}</span></div><div class="text-xs text-[var(--c-muted)]">${esc(a.email_address)}</div></button>`;
    }).join('');

    $('accounts-list').querySelectorAll('[data-id]').forEach((el) => {
      el.addEventListener('click', () => {
        const id = Number(el.dataset.id);
        state.selected = id;
        const account = state.accounts.find((a) => a.id === id);
        setForm(account);
        renderAccountOptions();
        renderAccountsList();
        loadMessages(1);
      });
    });
  }

  async function loadUsers() {
    const payload = await request('/api/users?per_page=100&sort=asc&order=name');
    state.users = payload?.data?.data || [];
    renderUsers();
  }

  async function loadAccounts() {
    const payload = await request(`${API}/accounts?per_page=100&sort=asc&order=name`);
    state.accounts = payload?.data?.data || [];
    if (!state.selected && state.accounts.length > 0) state.selected = state.accounts[0].id;
    if (state.selected && !state.accounts.some((a) => a.id === state.selected)) state.selected = state.accounts[0]?.id || null;
    renderAccountOptions();
    renderAccountsList();
    setForm(state.accounts.find((a) => a.id === state.selected) || null);
  }

  async function saveAccount(e) {
    e.preventDefault();
    const id = $('account-id').value ? Number($('account-id').value) : null;
    const body = {
      user_id: $('acc-user').value ? Number($('acc-user').value) : null,
      name: $('acc-name').value.trim(),
      email_address: $('acc-email').value.trim(),
      from_name: $('acc-from').value.trim() || null,
      imap_host: $('imap-host').value.trim(),
      imap_port: Number($('imap-port').value || 993),
      imap_encryption: $('imap-encryption').value,
      imap_validate_cert: $('imap-cert').checked,
      imap_username: $('imap-user').value.trim() || null,
      smtp_host: $('smtp-host').value.trim(),
      smtp_port: Number($('smtp-port').value || 587),
      smtp_encryption: $('smtp-encryption').value,
      smtp_username: $('smtp-user').value.trim() || null,
      is_active: $('acc-active').checked,
      notes: $('acc-notes').value.trim() || null,
    };
    if ($('imap-pass').value.trim() !== '') body.imap_password = $('imap-pass').value;
    if ($('smtp-pass').value.trim() !== '') body.smtp_password = $('smtp-pass').value;

    const payload = id
      ? await request(`${API}/accounts/${id}`, { method: 'PUT', body })
      : await request(`${API}/accounts`, { method: 'POST', body });

    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    state.selected = payload?.data?.id || state.selected;
    await loadAccounts();
    await loadMessages(1);
  }

  async function deleteAccount() {
    const id = $('account-id').value ? Number($('account-id').value) : null;
    if (!id) return;
    if (!confirm('Eliminar esta cuenta?')) return;
    const payload = await request(`${API}/accounts/${id}`, { method: 'DELETE' });
    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    state.selected = null;
    setForm(null);
    await loadAccounts();
    await loadMessages(1);
  }
  async function testConnection() {
    if (!state.selected) return;
    try {
      const payload = await request(`${API}/accounts/${state.selected}/test-connection`, { method: 'POST' });
      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    } catch (_e) {}
  }

  async function syncInbox() {
    if (!state.selected) return;
    try {
      const payload = await request(`${API}/accounts/${state.selected}/sync`, { method: 'POST', body: { limit: 50, folder: 'INBOX' } });
      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      await loadMessages(1);
      await loadAccounts();
    } catch (_e) {}
  }

  async function sendMail(e) {
    e.preventDefault();
    const accountId = $('send-account').value ? Number($('send-account').value) : null;
    if (!accountId) return;

    const payload = await request(`${API}/send`, {
      method: 'POST',
      body: {
        corporate_email_account_id: accountId,
        to: $('send-to').value.trim(),
        cc: $('send-cc').value.trim() || null,
        bcc: $('send-bcc').value.trim() || null,
        subject: $('send-subject').value.trim() || null,
        body_text: $('send-body').value,
      },
    });

    window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
    $('send-to').value = '';
    $('send-cc').value = '';
    $('send-bcc').value = '';
    $('send-subject').value = '';
    $('send-body').value = '';
    await loadMessages(1);
  }

  async function loadMessages(page = 1) {
    const params = new URLSearchParams({ page: String(page), per_page: '20', order: 'created_at', sort: 'desc' });
    const account = $('f-account').value || (state.selected ? String(state.selected) : '');
    if (account) params.set('corporate_email_account_id', account);
    if ($('f-direction').value) params.set('direction', $('f-direction').value);
    if ($('f-status').value) params.set('status', $('f-status').value);
    if ($('f-search').value.trim()) params.set('search', $('f-search').value.trim());

    const payload = await request(`${API}/messages?${params.toString()}`);
    state.messages = payload?.data || null;
    state.page = page;
    renderMessages();
  }

  function renderMessages() {
    const pag = state.messages;
    if (!pag || !Array.isArray(pag.data) || pag.data.length === 0) {
      $('messages').innerHTML = '<div class="p-3 text-[var(--c-muted)]">Sin mensajes</div>';
      $('btn-prev').disabled = true;
      $('btn-next').disabled = true;
      $('page-info').textContent = 'Pagina 1';
      return;
    }

    $('messages').innerHTML = pag.data.map((m) => {
      const from = m.from_name || m.from_email || '-';
      const when = m.received_at || m.sent_at || m.created_at;
      const badge = m.direction === 'incoming' ? 'Entrada' : 'Salida';
      const unread = m.status === 'unread' ? ' • No leido' : '';
      const selected = state.selectedMessage === m.id ? 'bg-[var(--c-elev)]' : '';
      return `<button type="button" class="w-full text-left p-3 ${selected}" data-mid="${m.id}"><div class="font-medium truncate">${esc(m.subject || '(Sin asunto)')}</div><div class="text-xs text-[var(--c-muted)] truncate">${esc(from)} | ${esc(badge)}${esc(unread)}</div><div class="text-xs text-[var(--c-muted)]">${esc(fmt(when))}</div></button>`;
    }).join('');

    $('messages').querySelectorAll('[data-mid]').forEach((el) => {
      el.addEventListener('click', () => detailMessage(Number(el.dataset.mid)));
    });

    $('btn-prev').disabled = !pag.prev_page_url;
    $('btn-next').disabled = !pag.next_page_url;
    $('page-info').textContent = `Pagina ${pag.current_page} de ${pag.last_page}`;
  }

  async function detailMessage(id) {
    const payload = await request(`${API}/messages/${id}`);
    const m = payload?.data;
    if (!m) return;
    state.selectedMessage = m.id;
    renderMessages();

    const to = Array.isArray(m.to_recipients) ? m.to_recipients.map((r) => r.name ? `${r.name} <${r.email}>` : r.email).join(', ') : '-';
    const cc = Array.isArray(m.cc_recipients) ? m.cc_recipients.map((r) => r.name ? `${r.name} <${r.email}>` : r.email).join(', ') : '-';
    const bcc = Array.isArray(m.bcc_recipients) ? m.bcc_recipients.map((r) => r.name ? `${r.name} <${r.email}>` : r.email).join(', ') : '-';
    const body = m.body_text || '(Sin cuerpo de texto)';
    const markBtn = m.direction === 'incoming' && m.status === 'unread' ? '<button id="btn-mark" class="px-3 py-1.5 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-xs">Marcar leido</button>' : '';

    $('detail').innerHTML = `
      <div class="space-y-2">
        <div class="flex items-start justify-between">
          <h3 class="font-semibold">${esc(m.subject || '(Sin asunto)')}</h3>
          ${markBtn}
        </div>
        <div class="text-xs text-[var(--c-muted)]">${esc(fmt(m.received_at || m.sent_at || m.created_at))}</div>
        <div class="text-sm rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] p-2 space-y-1">
          <div><b>Desde:</b> ${esc(m.from_name ? `${m.from_name} <${m.from_email || '-'}>` : (m.from_email || '-'))}</div>
          <div><b>Para:</b> ${esc(to)}</div>
          <div><b>CC:</b> ${esc(cc)}</div>
          <div><b>BCC:</b> ${esc(bcc)}</div>
          <div><b>Estado:</b> ${esc(m.status || '-')}</div>
        </div>
        <pre class="whitespace-pre-wrap text-sm rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] p-2 max-h-[250px] overflow-auto">${esc(body)}</pre>
      </div>
    `;

    document.getElementById('btn-mark')?.addEventListener('click', async () => {
      const mark = await request(`${API}/messages/${m.id}/mark-read`, { method: 'POST' });
      window.dispatchEvent(new CustomEvent('api:response', { detail: mark }));
      await detailMessage(m.id);
      await loadMessages(state.page || 1);
    });
  }

  function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), delay);
    };
  }

  $('account-form').addEventListener('submit', (e) => saveAccount(e));
  $('send-form').addEventListener('submit', (e) => sendMail(e));
  $('btn-new').addEventListener('click', () => setForm(null));
  $('btn-delete').addEventListener('click', deleteAccount);
  $('btn-test').addEventListener('click', testConnection);
  $('btn-sync').addEventListener('click', syncInbox);
  $('btn-refresh').addEventListener('click', async () => { await loadAccounts(); await loadMessages(1); });

  $('f-account').addEventListener('change', () => loadMessages(1));
  $('f-direction').addEventListener('change', () => loadMessages(1));
  $('f-status').addEventListener('change', () => loadMessages(1));
  $('f-search').addEventListener('input', debounce(() => loadMessages(1), 300));

  $('send-account').addEventListener('change', () => {
    const id = $('send-account').value ? Number($('send-account').value) : null;
    if (id) {
      state.selected = id;
      renderAccountsList();
      $('f-account').value = String(id);
    }
  });

  $('btn-prev').addEventListener('click', () => {
    if (!state.messages?.prev_page_url) return;
    loadMessages(state.messages.current_page - 1);
  });

  $('btn-next').addEventListener('click', () => {
    if (!state.messages?.next_page_url) return;
    loadMessages(state.messages.current_page + 1);
  });

  (async () => {
    try {
      await loadUsers();
      await loadAccounts();
      await loadMessages(1);
    } catch (_e) {}
  })();
})();
</script>
@endsection
