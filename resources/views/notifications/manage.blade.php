@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 id="notifications-title" class="text-2xl font-bold text-[var(--c-text)]">Notificaciones</h1>
      <p id="notifications-description" class="text-[var(--c-muted)] mt-1">Actividad enviada a usuarios del administrador.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button id="notifications-refresh" type="button" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 14a7 7 0 0 0 12 3.5L20 15"/><path d="M18.5 10a7 7 0 0 0-12-3.5L4 9"/></svg>
        Actualizar
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Total</p>
      <p id="notifications-stat-total" class="mt-1 text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Sin leer</p>
      <p id="notifications-stat-unread" class="mt-1 text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Leidas</p>
      <p id="notifications-stat-read" class="mt-1 text-2xl font-semibold">0</p>
    </div>
    <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
      <p class="text-xs text-[var(--c-muted)]">Hoy</p>
      <p id="notifications-stat-today" class="mt-1 text-2xl font-semibold">0</p>
    </div>
  </div>

  <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
    <div class="border-b border-[var(--c-border)] p-4">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_180px_220px_120px]">
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Buscar</span>
          <input id="notifications-search" type="search" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm" placeholder="Usuario, correo, titulo o mensaje">
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Estado</span>
          <select id="notifications-status" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todas</option>
            <option value="unread">Sin leer</option>
            <option value="read">Leidas</option>
          </select>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Evento</span>
          <select id="notifications-event" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            <option value="lead_created">Nuevo lead</option>
            <option value="lead_pending_assignment">Lead pendiente</option>
            <option value="lead_assigned">Lead asignado</option>
            <option value="lead_status_changed">Estado de lead</option>
            <option value="lead_converted">Lead convertido</option>
            <option value="visit_scheduled">Visita agendada</option>
            <option value="visit_updated">Visita actualizada</option>
            <option value="sync_issue">Incidencia sync</option>
          </select>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Por pagina</span>
          <select id="notifications-per-page" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="15">15</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </label>
      </div>
    </div>

    <div id="notifications-list" class="divide-y divide-[var(--c-border)]">
      <div class="p-5 text-sm text-[var(--c-muted)]">Cargando notificaciones...</div>
    </div>

    <div id="notifications-pagination" class="flex flex-col gap-3 border-t border-[var(--c-border)] p-4 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-sm text-[var(--c-muted)]">Sin registros</p>
    </div>
  </section>
</div>

<script>
(() => {
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const list = document.getElementById('notifications-list');
  const pagination = document.getElementById('notifications-pagination');
  const search = document.getElementById('notifications-search');
  const status = document.getElementById('notifications-status');
  const event = document.getElementById('notifications-event');
  const perPage = document.getElementById('notifications-per-page');
  const refresh = document.getElementById('notifications-refresh');
  const title = document.getElementById('notifications-title');
  const description = document.getElementById('notifications-description');
  let currentPage = 1;

  if (!API_TOKEN) {
    renderError('No se encontro un token de acceso. Inicia sesion nuevamente.');
    return;
  }

  const stats = {
    total: document.getElementById('notifications-stat-total'),
    unread: document.getElementById('notifications-stat-unread'),
    read: document.getElementById('notifications-stat-read'),
    today: document.getElementById('notifications-stat-today'),
  };

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const debounce = (callback, delay = 300) => {
    let timeout = null;
    return (...args) => {
      window.clearTimeout(timeout);
      timeout = window.setTimeout(() => callback(...args), delay);
    };
  };

  const headers = () => ({
    'Accept': 'application/json',
    'Authorization': `Bearer ${API_TOKEN}`,
    'X-CSRF-TOKEN': CSRF_TOKEN,
  });

  const readJson = async (response) => {
    const text = await response.text();
    if (!text) return null;

    try {
      return JSON.parse(text);
    } catch (error) {
      return {
        success: false,
        message: 'Respuesta invalida del servidor',
      };
    }
  };

  const formatNumber = (value) => new Intl.NumberFormat('es-MX').format(Number(value || 0));

  const formatType = (type) => {
    const labels = {
      lead_created: 'Nuevo lead',
      lead_pending_assignment: 'Lead pendiente',
      lead_assigned: 'Lead asignado',
      lead_assigned_admin: 'Lead asignado',
      lead_status_changed: 'Estado de lead',
      lead_converted: 'Lead convertido',
      visit_scheduled: 'Visita agendada',
      visit_updated: 'Visita actualizada',
      sync_issue: 'Incidencia sync',
    };

    return labels[type] || String(type || 'Notificacion').replace(/_/g, ' ');
  };

  const updateStats = (values = {}) => {
    stats.total.textContent = formatNumber(values.total);
    stats.unread.textContent = formatNumber(values.unread);
    stats.read.textContent = formatNumber(values.read);
    stats.today.textContent = formatNumber(values.today);
  };

  const updateScope = (scope) => {
    if (scope === 'global') {
      title.textContent = 'Notificaciones';
      description.textContent = 'Actividad del sistema enviada a usuarios del administrador.';
      return;
    }

    title.textContent = 'Mis notificaciones';
    description.textContent = 'Actividad relacionada con tus leads, clientes y visitas.';
  };

  const queryParams = () => {
    const params = new URLSearchParams({
      page: String(currentPage),
      per_page: perPage.value || '25',
    });

    if (search.value.trim()) params.set('search', search.value.trim());
    if (status.value) params.set('status', status.value);
    if (event.value) params.set('event', event.value);

    return params.toString();
  };

  async function loadNotifications(page = 1) {
    currentPage = page;
    list.innerHTML = '<div class="p-5 text-sm text-[var(--c-muted)]">Cargando notificaciones...</div>';

    try {
      const response = await fetch(`/api/notifications/admin?${queryParams()}`, {
        headers: headers(),
      });
      const payload = await readJson(response);

      if (!response.ok || !payload?.success) {
        renderError(payload?.message || 'No se pudieron cargar las notificaciones.');
        return;
      }

      updateStats(payload.stats || {});
      updateScope(payload.scope);
      renderList(payload.data?.data || []);
      renderPagination(payload.data || {});
    } catch (error) {
      renderError('No se pudo conectar con el servidor.');
    }
  }

  function renderError(message) {
    list.innerHTML = `<div class="p-5 text-sm text-red-600">${escapeHtml(message)}</div>`;
    pagination.innerHTML = '<p class="text-sm text-[var(--c-muted)]">Sin registros</p>';
  }

  function renderList(items) {
    if (!items.length) {
      list.innerHTML = '<div class="p-5 text-sm text-[var(--c-muted)]">No hay notificaciones con estos filtros.</div>';
      return;
    }

    list.innerHTML = items.map((item) => {
      const recipient = item.recipient || {};
      const recipientName = recipient.name || 'Usuario no disponible';
      const recipientEmail = recipient.email || '';
      const unread = !item.read_at;
      const actionUrl = item.action_url || '';

      return `
        <article class="grid gap-3 p-4 lg:grid-cols-[1fr_220px_140px] lg:items-center ${unread ? 'bg-[var(--c-elev)]/45' : 'bg-transparent'}">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full border border-[var(--c-border)] px-2 py-1 text-[11px] font-semibold text-[var(--c-muted)]">${escapeHtml(formatType(item.type))}</span>
              ${unread ? '<span class="rounded-full bg-[var(--c-primary)] px-2 py-1 text-[11px] font-semibold text-[var(--c-primary-ink)]">Sin leer</span>' : '<span class="rounded-full border border-[var(--c-border)] px-2 py-1 text-[11px] font-semibold text-[var(--c-muted)]">Leida</span>'}
              <span class="text-xs text-[var(--c-muted)]">${escapeHtml(item.created_at_human || item.created_at || '')}</span>
            </div>
            <h2 class="mt-2 text-base font-semibold text-[var(--c-text)]">${escapeHtml(item.title)}</h2>
            <p class="mt-1 text-sm leading-6 text-[var(--c-muted)]">${escapeHtml(item.message)}</p>
          </div>
          <div class="min-w-0 text-sm">
            <p class="font-semibold text-[var(--c-text)]">${escapeHtml(recipientName)}</p>
            <p class="truncate text-xs text-[var(--c-muted)]">${escapeHtml(recipientEmail || `ID ${recipient.id || '-'}`)}</p>
          </div>
          <div class="flex flex-wrap gap-2 lg:justify-end">
            ${actionUrl ? `<a href="${escapeHtml(actionUrl)}" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-elev)]">
              <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
              Abrir
            </a>` : ''}
          </div>
        </article>
      `;
    }).join('');
  }

  function renderPagination(meta) {
    const total = Number(meta.total || 0);
    const from = meta.from || 0;
    const to = meta.to || 0;
    const lastPage = Number(meta.last_page || 1);
    const page = Number(meta.current_page || currentPage);

    pagination.innerHTML = `
      <p class="text-sm text-[var(--c-muted)]">Mostrando ${formatNumber(from)}-${formatNumber(to)} de ${formatNumber(total)}</p>
      <div class="flex items-center gap-2">
        <button type="button" data-page="${page - 1}" class="notifications-page inline-flex size-9 items-center justify-center rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] disabled:opacity-40" ${page <= 1 ? 'disabled' : ''} aria-label="Pagina anterior">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <span class="text-sm text-[var(--c-muted)]">Pagina ${formatNumber(page)} de ${formatNumber(lastPage)}</span>
        <button type="button" data-page="${page + 1}" class="notifications-page inline-flex size-9 items-center justify-center rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] disabled:opacity-40" ${page >= lastPage ? 'disabled' : ''} aria-label="Pagina siguiente">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
      </div>
    `;
  }

  search.addEventListener('input', debounce(() => loadNotifications(1)));
  status.addEventListener('change', () => loadNotifications(1));
  event.addEventListener('change', () => loadNotifications(1));
  perPage.addEventListener('change', () => loadNotifications(1));
  refresh.addEventListener('click', () => loadNotifications(currentPage));
  pagination.addEventListener('click', (clickEvent) => {
    const button = clickEvent.target.closest('.notifications-page');
    if (!button || button.disabled) return;
    loadNotifications(Number(button.dataset.page || 1));
  });

  loadNotifications();
})();
</script>
@endsection
