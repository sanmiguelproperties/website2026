@extends('layouts.app')

@section('title', 'Tutoriales internos')

@section('content')
@php
  $tutorialUser = auth()->user();
  $canManageTutorials = \App\Support\Rbac::canAny($tutorialUser, 'tutorials.manage');
@endphp

<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Tutoriales internos</h1>
      <p class="mt-1 text-[var(--c-muted)]">Videos privados del equipo para aprender a editar y operar el sistema.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      @if($canManageTutorials)
        <a href="{{ route('tutorial-videos') }}" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          Administrar videos
        </a>
      @endif
      <button id="tutorials-refresh" type="button" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 14a7 7 0 0 0 12 3.5L20 15"/><path d="M18.5 10a7 7 0 0 0-12-3.5L4 9"/></svg>
        Actualizar
      </button>
    </div>
  </div>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="border-b border-[var(--c-border)] p-4">
        <h2 id="tutorials-current-title" class="text-lg font-semibold">Selecciona un tutorial</h2>
        <p id="tutorials-current-description" class="mt-1 text-sm text-[var(--c-muted)]">Elige un video de la lista para reproducirlo aqui.</p>
      </div>
      <div class="bg-black">
        <div class="aspect-video">
          <iframe
            id="tutorials-player"
            class="hidden h-full w-full"
            title="Reproductor de tutorial"
            src=""
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
          <div id="tutorials-empty-player" class="flex h-full w-full items-center justify-center bg-[var(--c-elev)] text-sm text-[var(--c-muted)]">
            Cargando tutoriales...
          </div>
        </div>
      </div>
    </div>

    <aside class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="border-b border-[var(--c-border)] p-4">
        <label class="space-y-1">
          <span class="text-xs font-semibold text-[var(--c-muted)]">Buscar tutorial</span>
          <input id="tutorials-search" type="search" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm" placeholder="Titulo o descripcion">
        </label>
      </div>
      <div id="tutorials-list" class="max-h-[60vh] overflow-y-auto divide-y divide-[var(--c-border)]">
        <div class="p-4 text-sm text-[var(--c-muted)]">Cargando tutoriales...</div>
      </div>
    </aside>
  </section>
</div>

<script>
(() => {
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const player = document.getElementById('tutorials-player');
  const emptyPlayer = document.getElementById('tutorials-empty-player');
  const title = document.getElementById('tutorials-current-title');
  const description = document.getElementById('tutorials-current-description');
  const list = document.getElementById('tutorials-list');
  const search = document.getElementById('tutorials-search');
  const refresh = document.getElementById('tutorials-refresh');
  let videos = [];
  let selectedId = null;

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

  const headers = () => ({
    Accept: 'application/json',
    Authorization: `Bearer ${API_TOKEN}`,
    'X-CSRF-TOKEN': CSRF_TOKEN,
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
      page: '1',
      per_page: '100',
      order: 'sort_order',
      sort: 'asc',
    });

    if (search.value.trim()) params.set('search', search.value.trim());

    return params.toString();
  };

  async function loadVideos() {
    list.innerHTML = '<div class="p-4 text-sm text-[var(--c-muted)]">Cargando tutoriales...</div>';

    try {
      const response = await fetch(`/api/tutorial-videos?${queryParams()}`, {
        headers: headers(),
      });
      const payload = await readJson(response);

      if (!response.ok || !payload?.success) {
        renderError(payload?.message || 'No se pudieron cargar los tutoriales.');
        return;
      }

      videos = payload.data?.data || [];
      renderList();

      if (videos.length === 0) {
        selectedId = null;
        clearPlayer('No hay tutoriales disponibles', 'Agrega videos activos desde el panel de administracion.');
        return;
      }

      const current = videos.find((video) => String(video.id) === String(selectedId)) || videos[0];
      selectVideo(current.id);
    } catch (_error) {
      renderError('No se pudieron cargar los tutoriales.');
    }
  }

  function renderList() {
    if (!videos.length) {
      list.innerHTML = '<div class="p-4 text-sm text-[var(--c-muted)]">No hay tutoriales activos con esa busqueda.</div>';
      return;
    }

    list.innerHTML = videos.map((video) => `
      <button type="button" data-id="${video.id}" class="tutorial-item block w-full p-3 text-left hover:bg-[var(--c-elev)] ${String(video.id) === String(selectedId) ? 'bg-[var(--c-elev)]' : ''}">
        <div class="flex gap-3">
          <div class="h-16 w-28 shrink-0 overflow-hidden rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)]">
            <img src="${escapeHtml(video.youtube_thumbnail_url)}" alt="" class="h-full w-full object-cover">
          </div>
          <div class="min-w-0">
            <p class="line-clamp-2 text-sm font-semibold text-[var(--c-text)]">${escapeHtml(video.title)}</p>
            <p class="mt-1 line-clamp-2 text-xs text-[var(--c-muted)]">${escapeHtml(video.description || 'Sin descripcion')}</p>
          </div>
        </div>
      </button>
    `).join('');

    list.querySelectorAll('.tutorial-item').forEach((button) => {
      button.addEventListener('click', () => selectVideo(button.dataset.id));
    });
  }

  function selectVideo(id) {
    const video = videos.find((item) => String(item.id) === String(id));
    if (!video) return;

    selectedId = video.id;
    title.textContent = video.title;
    description.textContent = video.description || 'Sin descripcion.';
    player.src = video.youtube_embed_url;
    player.classList.remove('hidden');
    emptyPlayer.classList.add('hidden');
    renderList();
  }

  function clearPlayer(titleText, descriptionText) {
    title.textContent = titleText;
    description.textContent = descriptionText;
    player.src = '';
    player.classList.add('hidden');
    emptyPlayer.textContent = titleText;
    emptyPlayer.classList.remove('hidden');
  }

  function renderError(message) {
    list.innerHTML = `<div class="p-4 text-sm text-red-600">${escapeHtml(message)}</div>`;
    clearPlayer('No se pudo cargar', message);
  }

  search.addEventListener('input', debounce(loadVideos));
  refresh.addEventListener('click', loadVideos);

  loadVideos();
})();
</script>
@endsection
