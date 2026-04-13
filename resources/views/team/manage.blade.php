@extends('layouts.app')

@section('title', 'Administrar Equipo')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Equipo de la Agencia</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona integrantes del equipo en espanol e ingles, con filtros por area y paginacion.</p>
    </div>

    <div class="flex items-center gap-2">
      <button id="btn-refresh-members" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        Actualizar
      </button>
      <button id="btn-create-member" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
        </svg>
        Nuevo integrante
      </button>
    </div>
  </div>

  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)]">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-5">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <input id="team-search" type="search" placeholder="Nombre, cargo, area o email..." class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
        </div>
        <div class="md:col-span-4">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Area</label>
          <select id="team-department-filter" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todas las areas</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="block text-xs text-[var(--c-muted)] mb-1">Estado</label>
          <select id="team-active-filter" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>
      </div>
    </div>

    <div id="team-members-list" class="p-5 space-y-3">
      <div class="text-sm text-[var(--c-muted)]">Cargando equipo...</div>
    </div>

    <div class="px-5 pb-5 pt-2 flex items-center justify-between gap-3">
      <button id="team-prev-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-50" disabled>Anterior</button>
      <div id="team-page-info" class="text-sm text-[var(--c-muted)]">Pagina 1</div>
      <button id="team-next-page" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm disabled:opacity-50" disabled>Siguiente</button>
    </div>
  </div>
</div>

<div id="team-member-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-labelledby="team-member-modal-title">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-5xl max-h-[90vh] overflow-y-auto px-3">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <div>
          <h3 id="team-member-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Nuevo integrante</h3>
          <p class="text-xs text-[var(--c-muted)]">Campos bilingues ES/EN para manejar equipos extensos.</p>
        </div>
        <button id="btn-close-team-member-modal" class="px-3 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Cerrar</button>
      </div>

      <form id="team-member-form" class="p-6 space-y-5">
        <input type="hidden" id="team-member-id" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Nombre completo <span class="text-red-400">*</span></label>
            <input id="team-full-name" type="text" maxlength="180" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input id="team-email" type="email" maxlength="180" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Cargo ES <span class="text-red-400">*</span></label>
            <input id="team-position-es" type="text" maxlength="180" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Position EN</label>
            <input id="team-position-en" type="text" maxlength="180" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Area ES</label>
            <input id="team-department-es" type="text" maxlength="120" placeholder="Ej: Marketing" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Department EN</label>
            <input id="team-department-en" type="text" maxlength="120" placeholder="Ex: Marketing" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Bio ES</label>
            <textarea id="team-bio-es" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Bio EN</label>
            <textarea id="team-bio-en" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Especialidades ES</label>
            <textarea id="team-specialties-es" rows="3" placeholder="Una especialidad por linea" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Specialties EN</label>
            <textarea id="team-specialties-en" rows="3" placeholder="One specialty per line" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"></textarea>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Telefono</label>
            <input id="team-phone" type="text" maxlength="60" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">WhatsApp</label>
            <input id="team-whatsapp" type="text" maxlength="60" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">LinkedIn URL</label>
            <input id="team-linkedin-url" type="url" maxlength="255" placeholder="https://..." class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Foto</label>
          <div data-fp-scope class="rounded-2xl border border-[var(--c-border)] p-4 bg-[var(--c-surface)]">
            <div class="flex items-center gap-3">
              <input
                type="text"
                id="team-photo-media-id"
                value=""
                placeholder="ID del media asset"
                readonly
                class="w-full rounded-lg border px-3 py-2 bg-[var(--c-elev)] border-[var(--c-border)] text-sm"
                data-filepicker="single"
                data-fp-max="1"
                data-fp-per-page="10"
                data-fp-preview="#team-photo-preview"
                data-fp-columns="4"
              />
              <button
                type="button"
                id="btn-open-team-photo-picker"
                class="cms-fp-open-btn px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 text-sm whitespace-nowrap"
                aria-controls="archive_manager-root"
              >
                Seleccionar
              </button>
            </div>
            <div id="team-photo-preview" class="mt-3"></div>
          </div>
          <p class="text-xs text-[var(--c-muted)] mt-1">Puedes usar el media manager para elegir la foto.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Orden</label>
            <input id="team-sort-order" type="number" min="0" step="1" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" value="0" />
          </div>
          <div class="flex items-end">
            <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
              <input id="team-is-active" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" checked />
              <span class="text-sm">Activo</span>
            </label>
          </div>
          <div class="flex items-end">
            <label class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-3 py-2">
              <input id="team-is-featured" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" />
              <span class="text-sm">Destacado</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-team-member" class="px-4 py-2 rounded-lg bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">Cancelar</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API_BASE = '/api/team-members';
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
    filters: {
      search: '',
      department: '',
      is_active: '',
    },
    departments: [],
  };

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const esc = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const notify = (success, message, errors = null) => {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: { success, message, errors },
    }));
  };

  const debounce = (fn, wait = 300) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  };

  async function apiFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = {
      Accept: 'application/json',
      Authorization: `Bearer ${TOKEN}`,
      ...(options.headers || {}),
    };

    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      headers['X-CSRF-TOKEN'] = CSRF;
    }

    if (options.body && !headers['Content-Type']) {
      headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, { ...options, method, headers });
    const json = await response.json().catch(() => null);

    if (!response.ok) {
      const error = {
        message: json?.message || 'Error de red',
        errors: json?.errors || null,
      };
      throw error;
    }

    return json;
  }

  function buildQuery(page = 1) {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('per_page', '20');

    if (state.filters.search) params.set('search', state.filters.search);
    if (state.filters.department) params.set('department', state.filters.department);
    if (state.filters.is_active !== '') params.set('is_active', state.filters.is_active);

    params.set('order', 'sort_order');
    params.set('sort', 'asc');

    return params.toString();
  }

  function memberImageUrl(member) {
    return member?.photo_media_asset?.serving_url || member?.photo_media_asset?.url || '';
  }

  function renderMembers(payload) {
    const container = $('#team-members-list');
    const rows = payload?.data || [];

    if (!rows.length) {
      container.innerHTML = '<div class="text-sm text-[var(--c-muted)]">No hay integrantes registrados.</div>';
      return;
    }

    container.innerHTML = rows.map((member) => {
      const activeBadge = member.is_active
        ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Activo</span>'
        : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Inactivo</span>';
      const featuredBadge = member.is_featured
        ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Destacado</span>'
        : '';
      const areaEs = member.department_es || '-';
      const areaEn = member.department_en || '-';
      const imageUrl = memberImageUrl(member);

      return `
        <div class="p-4 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="flex items-start gap-4 min-w-0">
            <div class="w-14 h-14 rounded-xl border border-[var(--c-border)] overflow-hidden bg-[var(--c-surface)] flex items-center justify-center shrink-0">
              ${imageUrl
                ? `<img src="${esc(imageUrl)}" alt="${esc(member.full_name)}" class="w-full h-full object-cover" />`
                : '<span class="text-xs text-[var(--c-muted)]">Sin foto</span>'}
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-semibold text-[var(--c-text)] truncate">${esc(member.full_name)}</h3>
                ${activeBadge}
                ${featuredBadge}
              </div>
              <p class="text-sm text-[var(--c-muted)] mt-1">ES: ${esc(member.position_es)} | EN: ${esc(member.position_en || '-')}</p>
              <p class="text-xs text-[var(--c-muted)] mt-1">Area ES: ${esc(areaEs)} | Area EN: ${esc(areaEn)}</p>
              <p class="text-xs text-[var(--c-muted)] mt-1">Orden: ${esc(member.sort_order ?? 0)}${member.email ? ` | ${esc(member.email)}` : ''}</p>
            </div>
          </div>
          <div class="flex items-center gap-2 justify-end">
            <button type="button" class="team-edit-btn px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-sm" data-member-id="${member.id}">Editar</button>
            <button type="button" class="team-delete-btn px-3 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700" data-member-id="${member.id}" data-member-name="${esc(member.full_name)}">Eliminar</button>
          </div>
        </div>
      `;
    }).join('');

    $$('.team-edit-btn', container).forEach((btn) => {
      btn.addEventListener('click', () => openEditMember(btn.dataset.memberId));
    });

    $$('.team-delete-btn', container).forEach((btn) => {
      btn.addEventListener('click', () => deleteMember(btn.dataset.memberId, btn.dataset.memberName));
    });
  }

  function renderPagination(meta) {
    state.page = meta.current_page || 1;
    state.lastPage = meta.last_page || 1;

    $('#team-page-info').textContent = `Pagina ${state.page} de ${state.lastPage}`;
    $('#team-prev-page').disabled = state.page <= 1;
    $('#team-next-page').disabled = state.page >= state.lastPage;
  }

  async function loadDepartments() {
    try {
      const response = await apiFetch(`${API_BASE}/departments`);
      const departments = response?.data || [];
      state.departments = departments;

      const filter = $('#team-department-filter');
      filter.innerHTML = '<option value="">Todas las areas</option>';

      departments.forEach((department) => {
        const label = department.name_es || department.name_en || department.key;
        filter.insertAdjacentHTML('beforeend', `<option value="${esc(department.key)}">${esc(label)}</option>`);
      });

      filter.value = state.filters.department || '';
    } catch (_error) {
      // no-op: si falla, solo queda sin filtro de departamentos
    }
  }

  async function loadMembers(page = 1) {
    if (state.loading) return;
    state.loading = true;

    try {
      const query = buildQuery(page);
      const response = await apiFetch(`${API_BASE}?${query}`);
      if (!response?.success) throw new Error('Respuesta invalida');

      renderMembers(response.data);
      renderPagination(response.data);
    } catch (error) {
      $('#team-members-list').innerHTML = '<div class="text-sm text-red-500">No se pudo cargar el equipo.</div>';
      notify(false, error?.message || 'Error cargando equipo', error?.errors || null);
    } finally {
      state.loading = false;
    }
  }

  function bindMediaPickerButton(button, input) {
    if (!button || !input || button.dataset.fpBound === '1') return;
    button.dataset.fpBound = '1';

    button.addEventListener('click', () => {
      if (typeof window.openMediaPickerFor === 'function') {
        window.openMediaPickerFor(input);
      } else {
        notify(false, 'Media Picker no disponible en esta vista.');
      }
    });
  }

  function openModal() {
    $('#team-member-modal').classList.remove('hidden');
  }

  function closeModal() {
    $('#team-member-modal').classList.add('hidden');
  }

  function resetForm() {
    $('#team-member-form').reset();
    $('#team-member-id').value = '';
    $('#team-sort-order').value = '0';
    $('#team-is-active').checked = true;
    $('#team-is-featured').checked = false;
    $('#team-photo-media-id').value = '';
    $('#team-photo-media-id').dispatchEvent(new Event('change'));
    $('#team-member-modal-title').textContent = 'Nuevo integrante';
  }

  function openCreateModal() {
    resetForm();
    openModal();
  }

  function fillForm(member) {
    $('#team-member-id').value = member.id || '';
    $('#team-full-name').value = member.full_name || '';
    $('#team-position-es').value = member.position_es || '';
    $('#team-position-en').value = member.position_en || '';
    $('#team-department-es').value = member.department_es || '';
    $('#team-department-en').value = member.department_en || '';
    $('#team-bio-es').value = member.bio_es || '';
    $('#team-bio-en').value = member.bio_en || '';
    $('#team-specialties-es').value = member.specialties_es || '';
    $('#team-specialties-en').value = member.specialties_en || '';
    $('#team-email').value = member.email || '';
    $('#team-phone').value = member.phone || '';
    $('#team-whatsapp').value = member.whatsapp || '';
    $('#team-linkedin-url').value = member.linkedin_url || '';
    $('#team-sort-order').value = String(member.sort_order ?? 0);
    $('#team-is-active').checked = Boolean(member.is_active);
    $('#team-is-featured').checked = Boolean(member.is_featured);

    const mediaId = member.photo_media_asset_id ? String(member.photo_media_asset_id) : '';
    $('#team-photo-media-id').value = mediaId;
    $('#team-photo-media-id').dispatchEvent(new Event('change'));

    $('#team-member-modal-title').textContent = `Editar integrante: ${member.full_name || ''}`;
  }

  async function openEditMember(memberId) {
    try {
      const response = await apiFetch(`${API_BASE}/${memberId}`);
      if (!response?.success || !response.data) {
        throw new Error('No se pudo obtener el integrante');
      }

      fillForm(response.data);
      openModal();
    } catch (error) {
      notify(false, error?.message || 'Error cargando integrante', error?.errors || null);
    }
  }

  function collectPayload() {
    const sortOrderRaw = ($('#team-sort-order').value || '').trim();

    return {
      full_name: $('#team-full-name').value.trim(),
      position_es: $('#team-position-es').value.trim(),
      position_en: $('#team-position-en').value.trim() || null,
      department_es: $('#team-department-es').value.trim() || null,
      department_en: $('#team-department-en').value.trim() || null,
      bio_es: $('#team-bio-es').value.trim() || null,
      bio_en: $('#team-bio-en').value.trim() || null,
      specialties_es: $('#team-specialties-es').value.trim() || null,
      specialties_en: $('#team-specialties-en').value.trim() || null,
      email: $('#team-email').value.trim() || null,
      phone: $('#team-phone').value.trim() || null,
      whatsapp: $('#team-whatsapp').value.trim() || null,
      linkedin_url: $('#team-linkedin-url').value.trim() || null,
      photo_media_asset_id: $('#team-photo-media-id').value ? parseInt($('#team-photo-media-id').value, 10) : null,
      sort_order: sortOrderRaw === '' ? 0 : parseInt(sortOrderRaw, 10),
      is_active: $('#team-is-active').checked,
      is_featured: $('#team-is-featured').checked,
    };
  }

  async function saveMember(event) {
    event.preventDefault();

    const memberId = $('#team-member-id').value;
    const payload = collectPayload();

    if (!payload.full_name) {
      notify(false, 'El nombre completo es obligatorio.');
      return;
    }

    if (!payload.position_es) {
      notify(false, 'El cargo en espanol es obligatorio.');
      return;
    }

    if (!Number.isFinite(payload.sort_order) || payload.sort_order < 0) {
      notify(false, 'El orden debe ser un numero entero mayor o igual a 0.');
      return;
    }

    try {
      const response = await apiFetch(memberId ? `${API_BASE}/${memberId}` : API_BASE, {
        method: memberId ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
      });

      notify(true, response?.message || 'Integrante guardado');
      closeModal();
      await loadDepartments();
      await loadMembers(state.page);
    } catch (error) {
      notify(false, error?.message || 'No se pudo guardar el integrante', error?.errors || null);
    }
  }

  async function deleteMember(memberId, memberName) {
    if (!memberId) return;
    const confirmation = confirm(`Se eliminara a ${memberName || 'este integrante'}. Esta accion no se puede deshacer.`);
    if (!confirmation) return;

    try {
      const response = await apiFetch(`${API_BASE}/${memberId}`, { method: 'DELETE' });
      notify(true, response?.message || 'Integrante eliminado');

      if (state.page > 1) {
        await loadMembers(state.page);
      } else {
        await loadMembers(1);
      }
      await loadDepartments();
    } catch (error) {
      notify(false, error?.message || 'No se pudo eliminar el integrante', error?.errors || null);
    }
  }

  function bindEvents() {
    bindMediaPickerButton($('#btn-open-team-photo-picker'), $('#team-photo-media-id'));

    $('#btn-create-member').addEventListener('click', openCreateModal);
    $('#btn-refresh-members').addEventListener('click', async () => {
      await loadDepartments();
      await loadMembers(state.page);
    });

    $('#team-search').addEventListener('input', debounce(async (event) => {
      state.filters.search = event.target.value.trim();
      await loadMembers(1);
    }, 350));

    $('#team-department-filter').addEventListener('change', async (event) => {
      state.filters.department = event.target.value;
      await loadMembers(1);
    });

    $('#team-active-filter').addEventListener('change', async (event) => {
      state.filters.is_active = event.target.value;
      await loadMembers(1);
    });

    $('#team-prev-page').addEventListener('click', () => {
      if (state.page > 1) loadMembers(state.page - 1);
    });

    $('#team-next-page').addEventListener('click', () => {
      if (state.page < state.lastPage) loadMembers(state.page + 1);
    });

    $('#team-member-form').addEventListener('submit', saveMember);
    $('#btn-close-team-member-modal').addEventListener('click', closeModal);
    $('#btn-cancel-team-member').addEventListener('click', closeModal);

    $('#team-member-modal').addEventListener('click', (event) => {
      if (event.target.id === 'team-member-modal') {
        closeModal();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !$('#team-member-modal').classList.contains('hidden')) {
        closeModal();
      }
    });
  }

  bindEvents();
  loadDepartments();
  loadMembers(1);
});
</script>
@endsection
