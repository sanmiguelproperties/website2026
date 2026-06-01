@extends('layouts.app')

@section('title', 'Manual de uso')

@section('content')
@php
  $manualUser = auth()->user();
  $canManageManual = \App\Support\Rbac::canAny($manualUser, 'manual.manage');
  $canViewTutorials = \App\Support\Rbac::canAny($manualUser, 'tutorials.view|tutorials.manage');
@endphp

<style>
  .manual-content h1,.manual-content h2,.manual-content h3{color:var(--c-text);font-weight:700;line-height:1.25;margin:1.5rem 0 .65rem}
  .manual-content h1{font-size:1.5rem}.manual-content h2{font-size:1.15rem}.manual-content h3{font-size:1rem}
  .manual-content p{margin:.7rem 0;line-height:1.75}.manual-content ul,.manual-content ol{margin:.7rem 0;padding-left:1.4rem}
  .manual-content ul{list-style:disc}.manual-content ol{list-style:decimal}.manual-content li{margin:.45rem 0;line-height:1.65}
  .manual-content a{color:var(--c-primary);text-decoration:underline}.manual-content blockquote{border-left:3px solid var(--c-primary);padding-left:1rem;color:var(--c-muted)}
  .manual-content code{border-radius:.35rem;background:var(--c-elev);padding:.12rem .3rem;font-size:.85em}.manual-content pre{overflow:auto;border-radius:.65rem;background:var(--c-elev);padding:1rem}
</style>

<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Manual de uso</h1>
      <p class="mt-1 text-[var(--c-muted)]">Guia interna para operar el sistema. Los videos son material complementario.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      @if($canViewTutorials)
        <a href="{{ route('tutorials') }}" class="inline-flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 8.5v7a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 15.5v-7A2.5 2.5 0 0 1 4.5 6h15A2.5 2.5 0 0 1 22 8.5Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>
          Videotutoriales
        </a>
      @endif
      @if($canManageManual)
        <a href="{{ route('manual-articles') }}" class="inline-flex items-center gap-2 rounded-lg bg-[var(--c-primary)] px-3 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          Administrar manual
        </a>
      @endif
    </div>
  </div>

  <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
    <label class="block">
      <span class="text-xs font-semibold text-[var(--c-muted)]">Buscar en el manual</span>
      <div class="mt-1 flex items-center gap-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3">
        <svg class="size-4 shrink-0 text-[var(--c-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input id="manual-search" type="search" class="w-full bg-transparent py-3 text-sm outline-none" placeholder="Ejemplo: publicar propiedad, convertir lead o agendar visita">
      </div>
    </label>
  </section>

  <section class="grid min-h-[62vh] grid-cols-1 gap-4 xl:grid-cols-[290px_minmax(0,1fr)_280px]">
    <aside class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="border-b border-[var(--c-border)] p-4">
        <h2 class="font-semibold text-[var(--c-text)]">Contenido</h2>
        <p id="manual-result-count" class="mt-1 text-xs text-[var(--c-muted)]">Cargando articulos...</p>
      </div>
      <div id="manual-sections" class="max-h-[68vh] overflow-y-auto p-2">
        <p class="p-3 text-sm text-[var(--c-muted)]">Cargando manual...</p>
      </div>
    </aside>

    <article class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div id="manual-article-head" class="border-b border-[var(--c-border)] p-5">
        <p class="text-sm text-[var(--c-muted)]">Selecciona un articulo para comenzar.</p>
      </div>
      <div id="manual-article-content" class="manual-content p-5 text-sm text-[var(--c-text)] sm:p-7">
        <p class="text-[var(--c-muted)]">Cargando contenido...</p>
      </div>
      <div id="manual-article-pagination" class="flex items-center justify-between gap-3 border-t border-[var(--c-border)] p-4"></div>
    </article>

    <aside class="space-y-4">
      <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold text-[var(--c-text)]">Acciones</h2>
        <div id="manual-actions" class="mt-3 space-y-2 text-sm text-[var(--c-muted)]">
          <p>Selecciona un articulo.</p>
        </div>
      </section>
      <section class="rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <h2 class="font-semibold text-[var(--c-text)]">Video del capitulo</h2>
        <div id="manual-videos" class="mt-3 space-y-3 text-sm text-[var(--c-muted)]">
          <p>Selecciona un articulo.</p>
        </div>
      </section>
    </aside>
  </section>
</div>

<div id="manual-video-modal" class="fixed inset-0 z-[13000] hidden" aria-modal="true" role="dialog" aria-labelledby="manual-video-title">
  <div class="absolute inset-0 bg-black/60"></div>
  <div class="relative mx-auto mt-8 w-full max-w-4xl px-3">
    <div class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-2xl">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] px-4 py-3">
        <h2 id="manual-video-title" class="font-semibold">Video relacionado</h2>
        <button id="manual-video-close" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold">Cerrar</button>
      </div>
      <div class="aspect-video bg-black">
        <iframe id="manual-video-player" class="h-full w-full" title="Video tutorial" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const sectionsRoot = document.getElementById('manual-sections');
  const count = document.getElementById('manual-result-count');
  const search = document.getElementById('manual-search');
  const head = document.getElementById('manual-article-head');
  const content = document.getElementById('manual-article-content');
  const pagination = document.getElementById('manual-article-pagination');
  const actions = document.getElementById('manual-actions');
  const videosRoot = document.getElementById('manual-videos');
  const videoModal = document.getElementById('manual-video-modal');
  const videoPlayer = document.getElementById('manual-video-player');
  const videoTitle = document.getElementById('manual-video-title');
  let sections = [];
  let selectedId = null;

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

  const headers = () => ({ Accept: 'application/json', Authorization: `Bearer ${API_TOKEN}` });
  const allArticles = () => sections.flatMap((section) => section.articles || []);
  const filteredSections = () => {
    const term = search.value.trim().toLowerCase();
    if (!term) return sections;

    return sections.map((section) => ({
      ...section,
      articles: (section.articles || []).filter((article) =>
        `${article.title} ${article.summary || ''}`.toLowerCase().includes(term)
      ),
    })).filter((section) => section.articles.length);
  };

  async function request(url) {
    const response = await fetch(url, { headers: headers() });
    const payload = await response.json().catch(() => null);
    if (!response.ok || !payload?.success) throw new Error(payload?.message || 'No se pudo cargar el manual.');
    return payload;
  }

  async function loadSections() {
    if (!API_TOKEN) return renderError('No se encontro un token de acceso. Inicia sesion nuevamente.');

    try {
      const payload = await request('/api/manual/sections');
      sections = payload.data || [];
      renderSections();
      const requested = new URLSearchParams(window.location.search).get('article');
      const initial = allArticles().find((article) => article.slug === requested) || allArticles()[0];
      if (initial) selectArticle(initial.id);
      else renderError('No hay articulos disponibles para tu perfil.');
    } catch (error) {
      renderError(error.message);
    }
  }

  function renderSections() {
    const visibleSections = filteredSections();
    const total = visibleSections.reduce((sum, section) => sum + section.articles.length, 0);
    count.textContent = `${total} articulo${total === 1 ? '' : 's'} disponible${total === 1 ? '' : 's'}`;

    if (!visibleSections.length) {
      sectionsRoot.innerHTML = '<p class="p-3 text-sm text-[var(--c-muted)]">No hay resultados con esa busqueda.</p>';
      return;
    }

    sectionsRoot.innerHTML = visibleSections.map((section) => `
      <section class="mb-2 overflow-hidden rounded-lg border border-[var(--c-border)]">
        <div class="bg-[var(--c-elev)] px-3 py-2">
          <p class="text-xs font-bold uppercase tracking-wide text-[var(--c-muted)]">${escapeHtml(section.title)}</p>
        </div>
        <div>
          ${section.articles.map((article) => `
            <button type="button" data-article-id="${article.id}" class="manual-article-link block w-full border-t border-[var(--c-border)] px-3 py-2 text-left text-sm hover:bg-[var(--c-elev)] ${String(article.id) === String(selectedId) ? 'bg-[var(--c-elev)] font-semibold' : ''}">
              ${escapeHtml(article.title)}
            </button>
          `).join('')}
        </div>
      </section>
    `).join('');

    sectionsRoot.querySelectorAll('[data-article-id]').forEach((button) => {
      button.addEventListener('click', () => selectArticle(button.dataset.articleId));
    });
  }

  async function selectArticle(id) {
    try {
      const payload = await request(`/api/manual/articles/${id}`);
      const article = payload.data;
      selectedId = article.id;
      renderSections();
      renderArticle(article);
      const url = new URL(window.location.href);
      url.searchParams.set('article', article.slug);
      window.history.replaceState({}, '', url);
    } catch (error) {
      renderError(error.message);
    }
  }

  function renderArticle(article) {
    const updated = article.updated_at ? new Date(article.updated_at).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Sin fecha';
    head.innerHTML = `
      <p class="text-xs font-bold uppercase tracking-wide text-[var(--c-primary)]">${escapeHtml(article.section?.title || '')}</p>
      <h2 class="mt-2 text-2xl font-bold text-[var(--c-text)]">${escapeHtml(article.title)}</h2>
      <p class="mt-2 text-sm leading-6 text-[var(--c-muted)]">${escapeHtml(article.summary || '')}</p>
    `;
    content.innerHTML = article.content_html || '<p>Este articulo todavia no tiene contenido.</p>';
    actions.innerHTML = `
      ${article.related_route_url ? `<a href="${escapeHtml(article.related_route_url)}" class="block rounded-lg bg-[var(--c-primary)] px-3 py-2 text-center font-semibold text-[var(--c-primary-ink)] hover:opacity-95">Ir al modulo</a>` : ''}
      <p class="pt-2 text-xs text-[var(--c-muted)]">Actualizado: ${escapeHtml(updated)}</p>
    `;
    const section = sections.find((item) => String(item.id) === String(article.manual_section_id));
    renderVideo(section?.video || null);
    renderPagination();
  }

  function renderVideo(video) {
    if (!video) {
      videosRoot.innerHTML = '<p>Este capitulo no tiene un video asociado. Sigue los pasos escritos del articulo.</p>';
      return;
    }

    videosRoot.innerHTML = `
      <button type="button" data-video-id="${video.id}" class="manual-video block w-full overflow-hidden rounded-lg border border-[var(--c-border)] text-left hover:bg-[var(--c-elev)]">
        <img src="${escapeHtml(video.youtube_thumbnail_url)}" alt="" class="aspect-video w-full object-cover">
        <span class="block p-2 text-xs font-semibold text-[var(--c-text)]">${escapeHtml(video.title)}</span>
      </button>
    `;

    videosRoot.querySelector('[data-video-id]')?.addEventListener('click', () => openVideo(video));
  }

  function renderPagination() {
    const articles = allArticles();
    const index = articles.findIndex((article) => String(article.id) === String(selectedId));
    const previous = index > 0 ? articles[index - 1] : null;
    const next = index >= 0 && index < articles.length - 1 ? articles[index + 1] : null;
    pagination.innerHTML = `
      <button type="button" data-page-id="${previous?.id || ''}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold disabled:opacity-40" ${previous ? '' : 'disabled'}>Anterior</button>
      <button type="button" data-page-id="${next?.id || ''}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold disabled:opacity-40" ${next ? '' : 'disabled'}>Siguiente</button>
    `;
    pagination.querySelectorAll('[data-page-id]').forEach((button) => {
      if (button.dataset.pageId) button.addEventListener('click', () => selectArticle(button.dataset.pageId));
    });
  }

  function openVideo(video) {
    videoTitle.textContent = video.title;
    videoPlayer.src = video.youtube_embed_url;
    videoModal.classList.remove('hidden');
  }

  function closeVideo() {
    videoPlayer.src = '';
    videoModal.classList.add('hidden');
  }

  function renderError(message) {
    sectionsRoot.innerHTML = `<p class="p-3 text-sm text-red-600">${escapeHtml(message)}</p>`;
    head.innerHTML = '<h2 class="text-lg font-semibold">No se pudo cargar el manual</h2>';
    content.innerHTML = `<p class="text-red-600">${escapeHtml(message)}</p>`;
  }

  search.addEventListener('input', renderSections);
  document.getElementById('manual-video-close').addEventListener('click', closeVideo);
  videoModal.addEventListener('click', (event) => { if (event.target === videoModal.firstElementChild) closeVideo(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeVideo(); });
  loadSections();
})();
</script>
@endsection
