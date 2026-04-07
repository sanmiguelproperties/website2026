@extends('layouts.app')

@section('title', 'Administrar Zonas SEO')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Paginas de Zona</h1>
      <p class="text-[var(--c-muted)] mt-1">Cada zona tiene su URL SEO y contenido bilingue administrable.</p>
    </div>
    <div class="flex items-center gap-2">
      <button id="btn-sync-zones" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Sincronizar zonas
      </button>
      <button id="btn-refresh-zones" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        Actualizar
      </button>
    </div>
  </div>

  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)]">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-8">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <input id="zones-search" type="search" placeholder="Zona, ciudad, estado, slug..." class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
        </div>
        <div class="md:col-span-4">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Estado</label>
          <select id="zones-active-filter" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
          </select>
        </div>
      </div>
    </div>

    <div class="p-5 space-y-3" id="zones-list">
      <div class="text-sm text-[var(--c-muted)]">Cargando zonas...</div>
    </div>

    <div class="px-5 pb-5 pt-2 flex items-center justify-between">
      <button id="zones-prev-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-50" disabled>Anterior</button>
      <div id="zones-page-info" class="text-sm text-[var(--c-muted)]">Pagina 1</div>
      <button id="zones-next-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-50" disabled>Siguiente</button>
    </div>
  </div>
</div>

<div id="zone-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto px-3">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-[var(--c-text)]">Editar zona SEO</h3>
          <p id="zone-modal-location" class="text-xs text-[var(--c-muted)]">-</p>
        </div>
        <button id="btn-close-zone-modal" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Cerrar</button>
      </div>

      <form id="zone-form" class="p-6 space-y-5">
        <input type="hidden" id="zone-id" />

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-6">
            <label class="block text-sm font-medium mb-1">Slug</label>
            <input id="zone-slug" type="text" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium mb-1">Activa</label>
            <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
              <input id="zone-is-active" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" />
              <span class="text-sm">Visible</span>
            </label>
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium mb-1">Menu</label>
            <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
              <input id="zone-show-in-menu" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" />
              <span class="text-sm">Mostrar en menu</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Orden en menu</label>
          <input id="zone-menu-order" type="number" min="0" step="1" placeholder="Automatico" class="w-full md:max-w-xs px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          <p class="text-xs text-[var(--c-muted)] mt-1">Menor numero = mayor prioridad. Vacio usa orden alfabetico.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Titulo ES</label>
            <input id="zone-title-es" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Title EN</label>
            <input id="zone-title-en" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Descripcion ES</label>
            <textarea id="zone-description-es" rows="4" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description EN</label>
            <textarea id="zone-description-en" rows="4" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Meta title ES</label>
            <input id="zone-meta-title-es" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Meta title EN</label>
            <input id="zone-meta-title-en" type="text" maxlength="255" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Meta description ES</label>
            <textarea id="zone-meta-description-es" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Meta description EN</label>
            <textarea id="zone-meta-description-en" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-zone" class="px-4 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Cancelar</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = '/api';
  const TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  if (!TOKEN) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: { success: false, message: 'Token no disponible. Inicia sesion nuevamente.' }
    }));
    return;
  }

  const state = {
    page: 1,
    lastPage: 1,
    loading: false,
  };

  const $ = (s, root = document) => root.querySelector(s);

  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
      ...options,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${TOKEN}`,
        'X-CSRF-TOKEN': CSRF,
        ...(options.headers || {}),
      },
    });

    const data = await response.json().catch(() => null);
    if (!response.ok) {
      throw data || { message: 'Error de red' };
    }
    return data;
  }

  function buildQuery(page = 1) {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('per_page', '20');

    const search = $('#zones-search').value.trim();
    if (search !== '') params.set('search', search);

    const active = $('#zones-active-filter').value;
    if (active !== '') params.set('is_active', active);

    return params.toString();
  }

  function renderZones(payload) {
    const container = $('#zones-list');
    const rows = payload?.data || [];

    if (!rows.length) {
      container.innerHTML = '<div class="text-sm text-[var(--c-muted)]">No hay zonas registradas.</div>';
      return;
    }

    container.innerHTML = rows.map((zone) => {
      const statusClass = zone.is_active
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-rose-100 text-rose-700';
      const statusText = zone.is_active ? 'Activa' : 'Inactiva';
      const inMenu = zone.show_in_menu !== false;
      const menuClass = inMenu
        ? 'bg-sky-100 text-sky-700'
        : 'bg-slate-200 text-slate-700';
      const menuText = inMenu ? 'En menu' : 'Oculta menu';
      const menuOrderText = Number.isInteger(zone.menu_order)
        ? String(zone.menu_order)
        : 'auto';

      return `
        <div class="p-4 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-semibold text-[var(--c-text)] truncate">${esc(zone.city_area)}, ${esc(zone.city)}</p>
              <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusClass}">${statusText}</span>
              <span class="px-2 py-1 rounded-full text-xs font-semibold ${menuClass}">${menuText}</span>
            </div>
            <p class="text-xs text-[var(--c-muted)] mt-1">Slug: /zonas/${esc(zone.slug)} - ${esc(zone.region)}</p>
            <p class="text-xs text-[var(--c-muted)] mt-1">Menu order: ${esc(menuOrderText)}</p>
            <p class="text-xs text-[var(--c-muted)] mt-1">ES: ${esc(zone.title_es || '-')} | EN: ${esc(zone.title_en || '-')}</p>
          </div>
          <button class="zone-edit-btn px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm" data-zone-id="${zone.id}">
            Editar
          </button>
        </div>
      `;
    }).join('');

    container.querySelectorAll('.zone-edit-btn').forEach((btn) => {
      btn.addEventListener('click', () => openZoneModal(btn.dataset.zoneId));
    });
  }

  function renderPagination(meta) {
    state.page = meta.current_page || 1;
    state.lastPage = meta.last_page || 1;

    $('#zones-page-info').textContent = `Pagina ${state.page} de ${state.lastPage}`;
    $('#zones-prev-page').disabled = state.page <= 1;
    $('#zones-next-page').disabled = state.page >= state.lastPage;
  }

  async function loadZones(page = 1) {
    if (state.loading) return;
    state.loading = true;

    try {
      const query = buildQuery(page);
      const payload = await apiFetch(`${API_BASE}/zone-pages?${query}`);
      if (!payload?.success) throw payload;

      renderZones(payload.data);
      renderPagination(payload.data);
    } catch (error) {
      $('#zones-list').innerHTML = '<div class="text-sm text-rose-400">No se pudieron cargar las zonas.</div>';
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: error?.message || 'Error cargando zonas.' }
      }));
    } finally {
      state.loading = false;
    }
  }

  async function syncZones() {
    try {
      const payload = await apiFetch(`${API_BASE}/zone-pages/sync`, { method: 'POST' });
      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      await loadZones(1);
    } catch (error) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: error?.message || 'Error sincronizando zonas.' }
      }));
    }
  }

  function openModal() {
    $('#zone-modal').classList.remove('hidden');
  }

  function closeModal() {
    $('#zone-modal').classList.add('hidden');
  }

  async function openZoneModal(zoneId) {
    try {
      const payload = await apiFetch(`${API_BASE}/zone-pages/${zoneId}`);
      if (!payload?.success) throw payload;

      const zone = payload.data;
      $('#zone-id').value = zone.id;
      $('#zone-modal-location').textContent = `${zone.city_area}, ${zone.city}, ${zone.region}`;

      $('#zone-slug').value = zone.slug || '';
      $('#zone-is-active').checked = !!zone.is_active;
      $('#zone-show-in-menu').checked = zone.show_in_menu !== false;
      $('#zone-menu-order').value = Number.isInteger(zone.menu_order) ? String(zone.menu_order) : '';
      $('#zone-title-es').value = zone.title_es || '';
      $('#zone-title-en').value = zone.title_en || '';
      $('#zone-description-es').value = zone.description_es || '';
      $('#zone-description-en').value = zone.description_en || '';
      $('#zone-meta-title-es').value = zone.meta_title_es || '';
      $('#zone-meta-title-en').value = zone.meta_title_en || '';
      $('#zone-meta-description-es').value = zone.meta_description_es || '';
      $('#zone-meta-description-en').value = zone.meta_description_en || '';

      openModal();
    } catch (error) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: error?.message || 'Error cargando zona.' }
      }));
    }
  }

  async function saveZone(event) {
    event.preventDefault();
    const zoneId = $('#zone-id').value;
    if (!zoneId) return;

    const menuOrderRaw = $('#zone-menu-order').value.trim();
    if (menuOrderRaw !== '' && !/^\d+$/.test(menuOrderRaw)) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: 'El orden de menu debe ser un numero entero mayor o igual a 0.' }
      }));
      return;
    }

    const body = {
      slug: $('#zone-slug').value.trim(),
      is_active: $('#zone-is-active').checked,
      show_in_menu: $('#zone-show-in-menu').checked,
      menu_order: menuOrderRaw === '' ? null : Number(menuOrderRaw),
      title_es: $('#zone-title-es').value.trim() || null,
      title_en: $('#zone-title-en').value.trim() || null,
      description_es: $('#zone-description-es').value.trim() || null,
      description_en: $('#zone-description-en').value.trim() || null,
      meta_title_es: $('#zone-meta-title-es').value.trim() || null,
      meta_title_en: $('#zone-meta-title-en').value.trim() || null,
      meta_description_es: $('#zone-meta-description-es').value.trim() || null,
      meta_description_en: $('#zone-meta-description-en').value.trim() || null,
    };

    try {
      const payload = await apiFetch(`${API_BASE}/zone-pages/${zoneId}`, {
        method: 'PUT',
        body: JSON.stringify(body),
      });
      window.dispatchEvent(new CustomEvent('api:response', { detail: payload }));
      closeModal();
      await loadZones(state.page);
    } catch (error) {
      window.dispatchEvent(new CustomEvent('api:response', {
        detail: { success: false, message: error?.message || 'Error guardando zona.', errors: error?.errors || null }
      }));
    }
  }

  const debounce = (fn, wait = 300) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  };

  $('#btn-sync-zones').addEventListener('click', syncZones);
  $('#btn-refresh-zones').addEventListener('click', () => loadZones(state.page));
  $('#zones-search').addEventListener('input', debounce(() => loadZones(1), 350));
  $('#zones-active-filter').addEventListener('change', () => loadZones(1));
  $('#zones-prev-page').addEventListener('click', () => { if (state.page > 1) loadZones(state.page - 1); });
  $('#zones-next-page').addEventListener('click', () => { if (state.page < state.lastPage) loadZones(state.page + 1); });

  $('#btn-close-zone-modal').addEventListener('click', closeModal);
  $('#btn-cancel-zone').addEventListener('click', closeModal);
  $('#zone-form').addEventListener('submit', saveZone);

  $('#zone-modal').addEventListener('click', (event) => {
    if (event.target.id === 'zone-modal') closeModal();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !$('#zone-modal').classList.contains('hidden')) {
      closeModal();
    }
  });

  loadZones(1);
});
</script>
@endsection
