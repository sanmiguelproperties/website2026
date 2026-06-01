@extends('layouts.app')

@section('title', 'Administrar videos tutoriales')

@section('content')
@php
  $canManageManual = \App\Support\Rbac::canAny(auth()->user(), 'manual.manage');
@endphp
<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Videos tutoriales</h1>
      <p class="mt-1 text-[var(--c-muted)]">Administra titulos y enlaces externos de YouTube para la ayuda interna.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      @if($canManageManual)
        <a href="{{ route('manual-articles') }}" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
          Administrar manual
        </a>
      @endif
      <a href="{{ route('tutorials') }}" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 8.5v7a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 15.5v-7A2.5 2.5 0 0 1 4.5 6h15A2.5 2.5 0 0 1 22 8.5Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>
        Ver tutoriales
      </a>
      <button id="tutorial-refresh" type="button" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 14a7 7 0 0 0 12 3.5L20 15"/><path d="M18.5 10a7 7 0 0 0-12-3.5L4 9"/></svg>
        Actualizar
      </button>
      <button id="tutorial-create" type="button" class="inline-flex items-center gap-2 rounded-lg bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        Nuevo video
      </button>
    </div>
  </div>

  <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
    <div class="border-b border-[var(--c-border)] p-4">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_180px_160px_140px]">
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Buscar</span>
          <input id="tutorial-search" type="search" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm" placeholder="Titulo, descripcion o URL">
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Estado</span>
          <select id="tutorial-active-filter" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Ordenar por</span>
          <select id="tutorial-order" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="sort_order">Orden</option>
            <option value="title">Titulo</option>
            <option value="updated_at">Actualizacion</option>
            <option value="created_at">Creacion</option>
          </select>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Direccion</span>
          <select id="tutorial-sort" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="asc">Asc</option>
            <option value="desc">Desc</option>
          </select>
        </label>
      </div>
    </div>

    <div id="tutorial-list" class="divide-y divide-[var(--c-border)]">
      <div class="p-5 text-sm text-[var(--c-muted)]">Cargando videos...</div>
    </div>

    <div id="tutorial-pagination" class="flex flex-col gap-3 border-t border-[var(--c-border)] p-4 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-sm text-[var(--c-muted)]">Sin registros</p>
    </div>
  </section>
</div>

<div id="tutorial-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-labelledby="tutorial-modal-title">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-3xl max-h-[90vh] overflow-y-auto px-3">
    <div class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] px-5 py-4">
        <div>
          <h2 id="tutorial-modal-title" class="text-lg font-semibold">Nuevo video tutorial</h2>
          <p class="text-xs text-[var(--c-muted)]">Usa enlaces de YouTube tipo watch, youtu.be, embed o shorts.</p>
        </div>
        <button id="tutorial-modal-close" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm hover:bg-[var(--c-surface)]">Cerrar</button>
      </div>

      <form id="tutorial-form" class="space-y-4 p-5">
        <input id="tutorial-id" type="hidden">

        <label class="block space-y-1">
          <span class="text-sm font-semibold">Titulo <span class="text-red-500">*</span></span>
          <input id="tutorial-title" type="text" maxlength="180" required class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </label>

        <label class="block space-y-1">
          <span class="text-sm font-semibold">URL de YouTube <span class="text-red-500">*</span></span>
          <input id="tutorial-url" type="url" maxlength="2048" required placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </label>

        <label class="block space-y-1">
          <span class="text-sm font-semibold">Descripcion</span>
          <textarea id="tutorial-description" rows="4" maxlength="5000" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></textarea>
        </label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="block space-y-1">
            <span class="text-sm font-semibold">Orden</span>
            <input id="tutorial-sort-order" type="number" min="0" max="1000000" step="1" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
          </label>
          <label class="flex items-end">
            <span class="inline-flex w-full items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2">
              <input id="tutorial-is-active" type="checkbox" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]" checked>
              <span class="text-sm font-semibold">Activo</span>
            </span>
          </label>
        </div>

        <div class="flex justify-end gap-3 border-t border-[var(--c-border)] pt-4">
          <button id="tutorial-cancel" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">Cancelar</button>
          <button type="submit" class="rounded-lg bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(() => {
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const list = document.getElementById('tutorial-list');
  const pagination = document.getElementById('tutorial-pagination');
  const search = document.getElementById('tutorial-search');
  const activeFilter = document.getElementById('tutorial-active-filter');
  const order = document.getElementById('tutorial-order');
  const sort = document.getElementById('tutorial-sort');
  const refresh = document.getElementById('tutorial-refresh');
  const create = document.getElementById('tutorial-create');
  const modal = document.getElementById('tutorial-modal');
  const form = document.getElementById('tutorial-form');
  const modalTitle = document.getElementById('tutorial-modal-title');
  const idField = document.getElementById('tutorial-id');
  const titleField = document.getElementById('tutorial-title');
  const urlField = document.getElementById('tutorial-url');
  const descriptionField = document.getElementById('tutorial-description');
  const sortOrderField = document.getElementById('tutorial-sort-order');
  const isActiveField = document.getElementById('tutorial-is-active');
  let currentPage = 1;
  let rows = [];

  if (!API_TOKEN) {
    renderError('No se encontro un token de acceso. Inicia sesion nuevamente.');
    return;
  }

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

  const notify = (success, message, payload = {}) => {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: { success, message, ...(payload || {}) },
    }));
  };

  const headers = (json = false) => ({
    Accept: 'application/json',
    Authorization: `Bearer ${API_TOKEN}`,
    'X-CSRF-TOKEN': CSRF_TOKEN,
    ...(json ? { 'Content-Type': 'application/json' } : {}),
  });

  const readJson = async (response) => {
    const text = await response.text();
    if (!text) return null;

    try {
      return JSON.parse(text);
    } catch (_error) {
      return { success: false, message: 'Respuesta invalida del servidor' };
    }
  };

  const queryParams = () => {
    const params = new URLSearchParams({
      page: String(currentPage),
      per_page: '15',
      include_inactive: '1',
      order: order.value || 'sort_order',
      sort: sort.value || 'asc',
    });

    if (search.value.trim()) params.set('search', search.value.trim());
    if (activeFilter.value !== '') params.set('is_active', activeFilter.value);

    return params.toString();
  };

  async function loadVideos(page = 1) {
    currentPage = page;
    list.innerHTML = '<div class="p-5 text-sm text-[var(--c-muted)]">Cargando videos...</div>';

    try {
      const response = await fetch(`/api/tutorial-videos?${queryParams()}`, {
        headers: headers(),
      });
      const payload = await readJson(response);

      if (!response.ok || !payload?.success) {
        renderError(payload?.message || 'No se pudieron cargar los videos.');
        return;
      }

      rows = payload.data?.data || [];
      renderList(rows);
      renderPagination(payload.data || {});
    } catch (_error) {
      renderError('No se pudieron cargar los videos.');
    }
  }

  function renderList(videos) {
    if (!videos.length) {
      list.innerHTML = '<div class="p-5 text-center text-sm text-[var(--c-muted)]">No hay videos tutoriales con esos filtros.</div>';
      return;
    }

    list.innerHTML = videos.map((video) => `
      <article class="grid gap-4 p-4 lg:grid-cols-[180px_1fr_auto] lg:items-center">
        <div class="aspect-video overflow-hidden rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)]">
          <img src="${escapeHtml(video.youtube_thumbnail_url)}" alt="" class="h-full w-full object-cover">
        </div>
        <div class="min-w-0 space-y-2">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="font-semibold text-[var(--c-text)]">${escapeHtml(video.title)}</h3>
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold ${video.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${video.is_active ? 'Activo' : 'Inactivo'}</span>
            <span class="rounded-full bg-[var(--c-elev)] px-2 py-0.5 text-xs text-[var(--c-muted)]">Orden ${escapeHtml(video.sort_order)}</span>
          </div>
          <p class="line-clamp-2 text-sm text-[var(--c-muted)]">${escapeHtml(video.description || 'Sin descripcion')}</p>
          <a href="${escapeHtml(video.youtube_url)}" target="_blank" rel="noopener noreferrer" class="block truncate text-xs text-[var(--c-primary)] hover:underline">${escapeHtml(video.youtube_url)}</a>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end">
          <button type="button" data-action="edit" data-id="${video.id}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">Editar</button>
          <button type="button" data-action="delete" data-id="${video.id}" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Eliminar</button>
        </div>
      </article>
    `).join('');

    list.querySelectorAll('[data-action="edit"]').forEach((button) => {
      button.addEventListener('click', () => {
        const video = rows.find((item) => String(item.id) === String(button.dataset.id));
        if (video) openModal(video);
      });
    });

    list.querySelectorAll('[data-action="delete"]').forEach((button) => {
      button.addEventListener('click', () => deleteVideo(button.dataset.id));
    });
  }

  function renderPagination(data) {
    const current = Number(data.current_page || 1);
    const last = Number(data.last_page || 1);
    const total = Number(data.total || 0);

    pagination.innerHTML = `
      <p class="text-sm text-[var(--c-muted)]">${total} video${total === 1 ? '' : 's'}</p>
      <div class="flex items-center gap-2">
        <button id="tutorial-prev" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm disabled:opacity-50" ${current <= 1 ? 'disabled' : ''}>Anterior</button>
        <span class="text-sm text-[var(--c-muted)]">Pagina ${current} de ${last}</span>
        <button id="tutorial-next" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm disabled:opacity-50" ${current >= last ? 'disabled' : ''}>Siguiente</button>
      </div>
    `;

    document.getElementById('tutorial-prev')?.addEventListener('click', () => loadVideos(current - 1));
    document.getElementById('tutorial-next')?.addEventListener('click', () => loadVideos(current + 1));
  }

  function renderError(message) {
    list.innerHTML = `<div class="p-5 text-sm text-red-600">${escapeHtml(message)}</div>`;
  }

  function openModal(video = null) {
    const editing = Boolean(video);
    modalTitle.textContent = editing ? 'Editar video tutorial' : 'Nuevo video tutorial';
    idField.value = video?.id || '';
    titleField.value = video?.title || '';
    urlField.value = video?.youtube_url || '';
    descriptionField.value = video?.description || '';
    sortOrderField.value = video?.sort_order ?? '';
    isActiveField.checked = editing ? Boolean(video?.is_active) : true;
    modal.classList.remove('hidden');
    titleField.focus();
  }

  function closeModal() {
    modal.classList.add('hidden');
    form.reset();
    idField.value = '';
  }

  async function saveVideo(event) {
    event.preventDefault();

    const id = idField.value;
    const body = {
      title: titleField.value.trim(),
      youtube_url: urlField.value.trim(),
      description: descriptionField.value.trim() || null,
      sort_order: sortOrderField.value === '' ? null : Number(sortOrderField.value),
      is_active: isActiveField.checked,
    };

    try {
      const response = await fetch(id ? `/api/tutorial-videos/${id}` : '/api/tutorial-videos', {
        method: id ? 'PUT' : 'POST',
        headers: headers(true),
        body: JSON.stringify(body),
      });
      const payload = await readJson(response);

      if (!response.ok || !payload?.success) {
        notify(false, payload?.message || 'No se pudo guardar el video.', { raw: payload, errors: payload?.errors || null });
        return;
      }

      closeModal();
      notify(true, payload.message || 'Video guardado.', { code: payload.code, raw: payload });
      loadVideos(currentPage);
    } catch (_error) {
      notify(false, 'No se pudo guardar el video.');
    }
  }

  async function deleteVideo(id) {
    if (!window.confirm('Eliminar este video tutorial?')) {
      return;
    }

    try {
      const response = await fetch(`/api/tutorial-videos/${id}`, {
        method: 'DELETE',
        headers: headers(),
      });
      const payload = await readJson(response);

      if (!response.ok || !payload?.success) {
        notify(false, payload?.message || 'No se pudo eliminar el video.', { raw: payload });
        return;
      }

      notify(true, payload.message || 'Video eliminado.', { code: payload.code, raw: payload });
      loadVideos(currentPage);
    } catch (_error) {
      notify(false, 'No se pudo eliminar el video.');
    }
  }

  search.addEventListener('input', debounce(() => loadVideos(1)));
  activeFilter.addEventListener('change', () => loadVideos(1));
  order.addEventListener('change', () => loadVideos(1));
  sort.addEventListener('change', () => loadVideos(1));
  refresh.addEventListener('click', () => loadVideos(currentPage));
  create.addEventListener('click', () => openModal());
  form.addEventListener('submit', saveVideo);
  document.getElementById('tutorial-modal-close').addEventListener('click', closeModal);
  document.getElementById('tutorial-cancel').addEventListener('click', closeModal);
  modal.addEventListener('click', (event) => {
    if (event.target === modal.firstElementChild) closeModal();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
  });

  loadVideos();
})();
</script>
@endsection
