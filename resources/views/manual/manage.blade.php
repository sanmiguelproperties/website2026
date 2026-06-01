@extends('layouts.app')

@section('title', 'Administrar manual')

@section('content')
@php
  $canManageTutorials = \App\Support\Rbac::canAny(auth()->user(), 'tutorials.manage');
@endphp
<style>
  #manual-editor h1,#manual-editor h2,#manual-editor h3{font-weight:700;margin:1rem 0 .5rem}
  #manual-editor h1{font-size:1.45rem}#manual-editor h2{font-size:1.15rem}#manual-editor h3{font-size:1rem}
  #manual-editor p{margin:.55rem 0}#manual-editor ul,#manual-editor ol{margin:.5rem 0;padding-left:1.4rem}
  #manual-editor ul{list-style:disc}#manual-editor ol{list-style:decimal}
</style>

<div class="space-y-5">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar manual</h1>
      <p class="mt-1 text-[var(--c-muted)]">Mantiene capitulos, articulos, permisos y videos relacionados.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('manual') }}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">Ver manual</a>
      @if($canManageTutorials)
        <a href="{{ route('tutorial-videos') }}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">Administrar videos</a>
      @endif
      <button id="manual-refresh" type="button" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">Actualizar</button>
    </div>
  </div>

  <section class="grid grid-cols-1 gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
    <aside class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] p-4">
        <div>
          <h2 class="font-semibold">Capitulos</h2>
          <p class="text-xs text-[var(--c-muted)]">Orden general del manual</p>
        </div>
        <button id="manual-section-create" type="button" class="rounded-lg bg-[var(--c-primary)] px-3 py-2 text-xs font-semibold text-[var(--c-primary-ink)]">Nuevo</button>
      </div>
      <div id="manual-section-list" class="max-h-[68vh] overflow-y-auto p-2">
        <p class="p-3 text-sm text-[var(--c-muted)]">Cargando capitulos...</p>
      </div>
    </aside>

    <div class="overflow-hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)]">
      <div class="flex flex-col gap-3 border-b border-[var(--c-border)] p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 id="manual-current-section" class="font-semibold">Articulos</h2>
          <p class="text-xs text-[var(--c-muted)]">Selecciona un capitulo para administrar su contenido.</p>
        </div>
        <button id="manual-article-create" type="button" class="rounded-lg bg-[var(--c-primary)] px-3 py-2 text-sm font-semibold text-[var(--c-primary-ink)] disabled:opacity-40" disabled>Nuevo articulo</button>
      </div>
      <div id="manual-article-list" class="divide-y divide-[var(--c-border)]">
        <p class="p-5 text-sm text-[var(--c-muted)]">Selecciona un capitulo.</p>
      </div>
    </div>
  </section>
</div>

<div id="manual-section-modal" class="fixed inset-0 z-[13000] hidden overflow-y-auto" aria-modal="true" role="dialog">
  <div class="absolute inset-0 bg-black/50"></div>
  <div class="relative mx-auto my-5 w-full max-w-xl px-3">
    <form id="manual-section-form" class="space-y-4 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-2xl">
      <div class="flex items-center justify-between gap-3">
        <h2 id="manual-section-modal-title" class="text-lg font-semibold">Nuevo capitulo</h2>
        <button type="button" data-close-section class="rounded-lg border border-[var(--c-border)] px-3 py-2 text-sm">Cerrar</button>
      </div>
      <input id="manual-section-id" type="hidden">
      <label class="block space-y-1"><span class="text-sm font-semibold">Titulo *</span><input id="manual-section-title" required maxlength="180" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
      <label class="block space-y-1"><span class="text-sm font-semibold">Slug</span><input id="manual-section-slug" maxlength="180" placeholder="Se genera automaticamente" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
      <label class="block space-y-1"><span class="text-sm font-semibold">Descripcion</span><textarea id="manual-section-description" rows="3" maxlength="2000" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></textarea></label>
      <div class="grid grid-cols-2 gap-3">
        <label class="block space-y-1"><span class="text-sm font-semibold">Icono</span><input id="manual-section-icon" maxlength="60" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
        <label class="block space-y-1"><span class="text-sm font-semibold">Orden</span><input id="manual-section-order" type="number" min="0" max="1000000" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
      </div>
      <label class="block space-y-1">
        <span class="text-sm font-semibold">Permiso requerido</span>
        <select id="manual-section-permission" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
          <option value="">Visible para cualquier usuario con acceso al manual</option>
          @foreach(config('rbac.permissions', []) as $permission)
            <option value="{{ $permission }}">{{ $permission }}</option>
          @endforeach
        </select>
      </label>
      <label class="block space-y-1">
        <span class="text-sm font-semibold">Video tutorial del capitulo</span>
        <select id="manual-section-video" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
          <option value="">Sin video tutorial</option>
        </select>
        <span class="block text-xs text-[var(--c-muted)]">Opcional. Selecciona uno de los videos tutoriales existentes.</span>
      </label>
      <label class="flex items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2"><input id="manual-section-active" type="checkbox" checked><span class="text-sm font-semibold">Activo</span></label>
      <div class="flex justify-end gap-2 border-t border-[var(--c-border)] pt-4">
        <button type="button" data-close-section class="rounded-lg border border-[var(--c-border)] px-4 py-2 text-sm font-semibold">Cancelar</button>
        <button class="rounded-lg bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)]">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div id="manual-article-modal" class="fixed inset-0 z-[13000] hidden overflow-y-auto" aria-modal="true" role="dialog">
  <div class="fixed inset-0 bg-black/50"></div>
  <div class="relative mx-auto my-5 w-full max-w-5xl px-3">
    <form id="manual-article-form" class="space-y-4 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-2xl">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 id="manual-article-modal-title" class="text-lg font-semibold">Nuevo articulo</h2>
          <p class="text-xs text-[var(--c-muted)]">El texto se mostrara como la fuente principal de ayuda.</p>
        </div>
        <button type="button" data-close-article class="rounded-lg border border-[var(--c-border)] px-3 py-2 text-sm">Cerrar</button>
      </div>
      <input id="manual-article-id" type="hidden">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <label class="block space-y-1"><span class="text-sm font-semibold">Capitulo *</span><select id="manual-article-section" required class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></select></label>
        <label class="block space-y-1"><span class="text-sm font-semibold">Titulo *</span><input id="manual-article-title" required maxlength="180" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
      </div>
      <label class="block space-y-1"><span class="text-sm font-semibold">Resumen</span><textarea id="manual-article-summary" rows="2" maxlength="2000" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></textarea></label>

      <div class="space-y-1">
        <span class="text-sm font-semibold">Contenido *</span>
        <div class="flex flex-wrap gap-1 rounded-t-lg border border-b-0 border-[var(--c-border)] bg-[var(--c-elev)] p-2">
          <button type="button" data-editor-command="formatBlock" data-editor-value="p" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs font-semibold">Parrafo</button>
          <button type="button" data-editor-command="formatBlock" data-editor-value="h2" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs font-semibold">Titulo</button>
          <button type="button" data-editor-command="bold" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs font-bold">Negrita</button>
          <button type="button" data-editor-command="italic" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs italic">Cursiva</button>
          <button type="button" data-editor-command="insertUnorderedList" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs">Lista</button>
          <button type="button" data-editor-command="insertOrderedList" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs">Pasos</button>
          <button type="button" id="manual-editor-link" class="rounded border border-[var(--c-border)] px-2 py-1 text-xs">Enlace</button>
        </div>
        <div id="manual-editor" contenteditable="true" class="min-h-[280px] rounded-b-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-3 text-sm outline-none"></div>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <label class="block space-y-1">
          <span class="text-sm font-semibold">Permiso requerido</span>
          <select id="manual-article-permission" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Visible para cualquier usuario con acceso al manual</option>
            @foreach(config('rbac.permissions', []) as $permission)
              <option value="{{ $permission }}">{{ $permission }}</option>
            @endforeach
          </select>
        </label>
        <label class="block space-y-1"><span class="text-sm font-semibold">Ruta relacionada</span><input id="manual-article-route" maxlength="180" list="manual-route-options" placeholder="Ejemplo: properties" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
      </div>
      <datalist id="manual-route-options">
        @foreach(['dashboard','properties','zones','clients','property-contact-requests','calendar','users','rbac','easybroker.mls-export','mls','mls-agents','mls-offices','corporate-email.configuration','corporate-email.inbox','corporate-email.outbox','corporate-email.compose','cms.pages','cms.posts','cms.menus','cms.settings','manual','manual-articles','tutorials','tutorial-videos','currencies','color-themes','frontend-colors','easybroker','notifications'] as $routeName)
          <option value="{{ $routeName }}">
        @endforeach
      </datalist>
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <label class="block space-y-1"><span class="text-sm font-semibold">Slug</span><input id="manual-article-slug" maxlength="180" placeholder="Se genera automaticamente" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
        <label class="block space-y-1"><span class="text-sm font-semibold">Orden</span><input id="manual-article-order" type="number" min="0" max="1000000" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></label>
        <label class="flex items-end"><span class="flex w-full items-center gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2"><input id="manual-article-active" type="checkbox" checked><span class="text-sm font-semibold">Activo</span></span></label>
      </div>

      <div class="flex justify-end gap-2 border-t border-[var(--c-border)] pt-4">
        <button type="button" data-close-article class="rounded-lg border border-[var(--c-border)] px-4 py-2 text-sm font-semibold">Cancelar</button>
        <button class="rounded-lg bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)]">Guardar articulo</button>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const sectionList = document.getElementById('manual-section-list');
  const articleList = document.getElementById('manual-article-list');
  const currentSectionTitle = document.getElementById('manual-current-section');
  const sectionModal = document.getElementById('manual-section-modal');
  const articleModal = document.getElementById('manual-article-modal');
  const editor = document.getElementById('manual-editor');
  let sections = [];
  let videos = [];
  let selectedSectionId = null;

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  const headers = (json = false) => ({
    Accept: 'application/json',
    Authorization: `Bearer ${API_TOKEN}`,
    'X-CSRF-TOKEN': CSRF_TOKEN,
    ...(json ? { 'Content-Type': 'application/json' } : {}),
  });
  const notify = (success, message, payload = {}) => window.dispatchEvent(new CustomEvent('api:response', { detail: { success, message, ...payload } }));

  async function request(url, options = {}) {
    const response = await fetch(url, { ...options, headers: { ...headers(Boolean(options.body)), ...(options.headers || {}) } });
    const payload = await response.json().catch(() => null);
    if (!response.ok || !payload?.success) {
      const error = new Error(payload?.message || 'No se pudo completar la operacion.');
      error.payload = payload;
      throw error;
    }
    return payload;
  }

  async function loadAll() {
    if (!API_TOKEN) return notify(false, 'No se encontro un token de acceso. Inicia sesion nuevamente.');
    try {
      const [sectionsPayload, videosPayload] = await Promise.all([
        request('/api/manual/sections?include_inactive=1'),
        request('/api/manual/videos'),
      ]);
      sections = sectionsPayload.data || [];
      videos = videosPayload.data || [];
      if (!selectedSectionId || !sections.some((section) => String(section.id) === String(selectedSectionId))) selectedSectionId = sections[0]?.id || null;
      render();
    } catch (error) {
      notify(false, error.message, { errors: error.payload?.errors || null });
    }
  }

  function render() {
    renderSections();
    renderArticles();
    fillSectionOptions();
    fillSectionVideoOptions();
  }

  function renderSections() {
    if (!sections.length) {
      sectionList.innerHTML = '<p class="p-3 text-sm text-[var(--c-muted)]">No hay capitulos.</p>';
      return;
    }
    sectionList.innerHTML = sections.map((section) => `
      <div class="mb-2 rounded-lg border border-[var(--c-border)] ${String(section.id) === String(selectedSectionId) ? 'bg-[var(--c-elev)] ring-1 ring-[var(--c-primary)]' : ''}">
        <button type="button" data-select-section="${section.id}" class="block w-full px-3 py-3 text-left">
          <span class="flex items-center justify-between gap-2">
            <span class="text-sm font-semibold">${escapeHtml(section.title)}</span>
            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold ${section.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${section.is_active ? 'Activo' : 'Inactivo'}</span>
          </span>
          <span class="mt-1 block text-xs text-[var(--c-muted)]">${section.article_count} articulos | Orden ${section.sort_order}</span>
          ${section.video ? `<span class="mt-1 block text-xs text-[var(--c-muted)]">Video: ${escapeHtml(section.video.title)}</span>` : ''}
        </button>
        <div class="flex gap-2 border-t border-[var(--c-border)] px-3 py-2">
          <button type="button" data-edit-section="${section.id}" class="text-xs font-semibold text-[var(--c-primary)]">Editar</button>
          <button type="button" data-delete-section="${section.id}" class="text-xs font-semibold text-red-600">Eliminar</button>
        </div>
      </div>
    `).join('');
    sectionList.querySelectorAll('[data-select-section]').forEach((button) => button.addEventListener('click', () => { selectedSectionId = button.dataset.selectSection; render(); }));
    sectionList.querySelectorAll('[data-edit-section]').forEach((button) => button.addEventListener('click', () => openSection(sections.find((section) => String(section.id) === String(button.dataset.editSection)))));
    sectionList.querySelectorAll('[data-delete-section]').forEach((button) => button.addEventListener('click', () => deleteSection(button.dataset.deleteSection)));
  }

  function renderArticles() {
    const section = sections.find((item) => String(item.id) === String(selectedSectionId));
    currentSectionTitle.textContent = section ? `Articulos: ${section.title}` : 'Articulos';
    document.getElementById('manual-article-create').disabled = !section;
    if (!section) return articleList.innerHTML = '<p class="p-5 text-sm text-[var(--c-muted)]">Selecciona o crea un capitulo.</p>';
    if (!section.articles.length) return articleList.innerHTML = '<p class="p-5 text-sm text-[var(--c-muted)]">Este capitulo todavia no tiene articulos.</p>';
    articleList.innerHTML = section.articles.map((article) => `
      <article class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="font-semibold">${escapeHtml(article.title)}</h3>
            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold ${article.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${article.is_active ? 'Activo' : 'Inactivo'}</span>
            <span class="rounded-full bg-[var(--c-elev)] px-2 py-0.5 text-[10px] text-[var(--c-muted)]">Orden ${article.sort_order}</span>
          </div>
          <p class="mt-1 text-sm text-[var(--c-muted)]">${escapeHtml(article.summary || 'Sin resumen')}</p>
          ${article.required_permission ? `<p class="mt-1 text-xs text-[var(--c-muted)]">Permiso: ${escapeHtml(article.required_permission)}</p>` : ''}
        </div>
        <div class="flex shrink-0 gap-2">
          <button type="button" data-edit-article="${article.id}" class="rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm font-semibold">Editar</button>
          <button type="button" data-delete-article="${article.id}" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white">Eliminar</button>
        </div>
      </article>
    `).join('');
    articleList.querySelectorAll('[data-edit-article]').forEach((button) => button.addEventListener('click', () => editArticle(button.dataset.editArticle)));
    articleList.querySelectorAll('[data-delete-article]').forEach((button) => button.addEventListener('click', () => deleteArticle(button.dataset.deleteArticle)));
  }

  function fillSectionOptions() {
    document.getElementById('manual-article-section').innerHTML = sections.map((section) => `<option value="${section.id}">${escapeHtml(section.title)}</option>`).join('');
  }

  function fillSectionVideoOptions() {
    document.getElementById('manual-section-video').innerHTML = [
      '<option value="">Sin video tutorial</option>',
      ...videos.map((video) => `<option value="${video.id}">${escapeHtml(video.title)}${video.is_active ? '' : ' (inactivo)'}</option>`),
    ].join('');
  }

  function openSection(section = null) {
    document.getElementById('manual-section-modal-title').textContent = section ? 'Editar capitulo' : 'Nuevo capitulo';
    document.getElementById('manual-section-id').value = section?.id || '';
    document.getElementById('manual-section-title').value = section?.title || '';
    document.getElementById('manual-section-slug').value = section?.slug || '';
    document.getElementById('manual-section-description').value = section?.description || '';
    document.getElementById('manual-section-icon').value = section?.icon || '';
    document.getElementById('manual-section-order').value = section?.sort_order ?? '';
    document.getElementById('manual-section-permission').value = section?.required_permission || '';
    document.getElementById('manual-section-video').value = section?.tutorial_video_id || '';
    document.getElementById('manual-section-active').checked = section ? Boolean(section.is_active) : true;
    sectionModal.classList.remove('hidden');
  }

  function closeSection() { sectionModal.classList.add('hidden'); document.getElementById('manual-section-form').reset(); }

  async function saveSection(event) {
    event.preventDefault();
    const id = document.getElementById('manual-section-id').value;
    const body = {
      title: document.getElementById('manual-section-title').value.trim(),
      slug: document.getElementById('manual-section-slug').value.trim() || null,
      description: document.getElementById('manual-section-description').value.trim() || null,
      icon: document.getElementById('manual-section-icon').value.trim() || null,
      required_permission: document.getElementById('manual-section-permission').value || null,
      tutorial_video_id: document.getElementById('manual-section-video').value === '' ? null : Number(document.getElementById('manual-section-video').value),
      sort_order: document.getElementById('manual-section-order').value === '' ? null : Number(document.getElementById('manual-section-order').value),
      is_active: document.getElementById('manual-section-active').checked,
    };
    try {
      const payload = await request(id ? `/api/manual/sections/${id}` : '/api/manual/sections', { method: id ? 'PUT' : 'POST', body: JSON.stringify(body) });
      closeSection(); notify(true, payload.message); await loadAll();
    } catch (error) { notify(false, error.message, { errors: error.payload?.errors || null }); }
  }

  async function deleteSection(id) {
    if (!window.confirm('Eliminar este capitulo? Solo es posible si no contiene articulos.')) return;
    try { const payload = await request(`/api/manual/sections/${id}`, { method: 'DELETE' }); notify(true, payload.message); await loadAll(); }
    catch (error) { notify(false, error.message, { errors: error.payload?.errors || null }); }
  }

  function openArticle(article = null) {
    document.getElementById('manual-article-modal-title').textContent = article ? 'Editar articulo' : 'Nuevo articulo';
    document.getElementById('manual-article-id').value = article?.id || '';
    document.getElementById('manual-article-section').value = article?.manual_section_id || selectedSectionId || '';
    document.getElementById('manual-article-title').value = article?.title || '';
    document.getElementById('manual-article-summary').value = article?.summary || '';
    document.getElementById('manual-article-permission').value = article?.required_permission || '';
    document.getElementById('manual-article-route').value = article?.related_route_name || '';
    document.getElementById('manual-article-slug').value = article?.slug || '';
    document.getElementById('manual-article-order').value = article?.sort_order ?? '';
    document.getElementById('manual-article-active').checked = article ? Boolean(article.is_active) : true;
    editor.innerHTML = article?.content || '<h2>Objetivo</h2><p></p><h2>Antes de comenzar</h2><ul><li></li></ul><h2>Pasos</h2><ol><li></li></ol><h2>Resultado esperado</h2><p></p>';
    articleModal.classList.remove('hidden');
  }

  async function editArticle(id) {
    try { const payload = await request(`/api/manual/articles/${id}`); openArticle(payload.data); }
    catch (error) { notify(false, error.message); }
  }

  function closeArticle() { articleModal.classList.add('hidden'); document.getElementById('manual-article-form').reset(); editor.innerHTML = ''; }

  async function saveArticle(event) {
    event.preventDefault();
    const id = document.getElementById('manual-article-id').value;
    const body = {
      manual_section_id: Number(document.getElementById('manual-article-section').value),
      title: document.getElementById('manual-article-title').value.trim(),
      summary: document.getElementById('manual-article-summary').value.trim() || null,
      content: editor.innerHTML.trim(),
      required_permission: document.getElementById('manual-article-permission').value || null,
      related_route_name: document.getElementById('manual-article-route').value.trim() || null,
      slug: document.getElementById('manual-article-slug').value.trim() || null,
      sort_order: document.getElementById('manual-article-order').value === '' ? null : Number(document.getElementById('manual-article-order').value),
      is_active: document.getElementById('manual-article-active').checked,
    };
    try {
      const payload = await request(id ? `/api/manual/articles/${id}` : '/api/manual/articles', { method: id ? 'PUT' : 'POST', body: JSON.stringify(body) });
      closeArticle(); notify(true, payload.message); await loadAll();
    } catch (error) { notify(false, error.message, { errors: error.payload?.errors || null }); }
  }

  async function deleteArticle(id) {
    if (!window.confirm('Eliminar este articulo del manual?')) return;
    try { const payload = await request(`/api/manual/articles/${id}`, { method: 'DELETE' }); notify(true, payload.message); await loadAll(); }
    catch (error) { notify(false, error.message); }
  }

  document.getElementById('manual-refresh').addEventListener('click', loadAll);
  document.getElementById('manual-section-create').addEventListener('click', () => openSection());
  document.getElementById('manual-article-create').addEventListener('click', () => openArticle());
  document.getElementById('manual-section-form').addEventListener('submit', saveSection);
  document.getElementById('manual-article-form').addEventListener('submit', saveArticle);
  document.querySelectorAll('[data-close-section]').forEach((button) => button.addEventListener('click', closeSection));
  document.querySelectorAll('[data-close-article]').forEach((button) => button.addEventListener('click', closeArticle));
  document.querySelectorAll('[data-editor-command]').forEach((button) => button.addEventListener('click', () => {
    editor.focus(); document.execCommand(button.dataset.editorCommand, false, button.dataset.editorValue || null);
  }));
  document.getElementById('manual-editor-link').addEventListener('click', () => {
    const url = window.prompt('URL del enlace:');
    if (url) { editor.focus(); document.execCommand('createLink', false, url); }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (!articleModal.classList.contains('hidden')) closeArticle();
    else if (!sectionModal.classList.contains('hidden')) closeSection();
  });
  loadAll();
})();
</script>
@endsection
