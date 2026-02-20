@extends('layouts.app')

@section('title', 'CMS - Gestionar Páginas')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">📄 Páginas CMS</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona las páginas del sitio y sus campos administrables (bilingüe ES/EN)</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button id="btn-refresh" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Actualizar
      </button>
    </div>
  </div>

  <!-- Pages list -->
  <div id="pages-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Se llena dinámicamente -->
  </div>
  <div id="pages-loading" class="hidden py-10"><div class="animate-pulse space-y-3"><div class="h-24 rounded-2xl bg-[var(--c-elev)]"></div><div class="h-24 rounded-2xl bg-[var(--c-elev)]"></div></div></div>
</div>

<!-- Drawer: Editar campos de una página -->
<div id="page-drawer" class="fixed inset-0 z-[11000] hidden" aria-modal="true" role="dialog">
  <div data-js="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="absolute right-0 top-0 h-full w-full max-w-5xl">
    <div class="h-full bg-[var(--c-surface)] border-l border-[var(--c-border)] shadow-2xl flex flex-col">
      <!-- Drawer header -->
      <div class="px-6 py-4 border-b border-[var(--c-border)] flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h3 id="drawer-title" class="text-lg font-semibold truncate">Editar página</h3>
          <p id="drawer-subtitle" class="text-xs text-[var(--c-muted)]">Edita los campos de esta página</p>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)]">
            <span class="text-xs font-medium text-[var(--c-muted)]">Idioma:</span>
            <button id="btn-lang-es" class="px-2 py-1 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]">ES</button>
            <button id="btn-lang-en" class="px-2 py-1 text-xs rounded-lg text-[var(--c-muted)]">EN</button>
          </div>
          <button id="btn-save" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
            Guardar todo
          </button>
          <button id="btn-close" class="p-2 rounded-xl hover:bg-[var(--c-elev)] transition" aria-label="Cerrar">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>

      <!-- Drawer body: Field groups + fields -->
      <div id="drawer-body" class="flex-1 min-h-0 overflow-y-auto p-6 space-y-6">
        <!-- Campos dinámicos se insertan aquí -->
      </div>

      <div class="px-6 py-4 border-t border-[var(--c-border)] flex items-center justify-between gap-3">
        <span class="text-xs text-[var(--c-muted)]">Los campos de tipo repeater permiten agregar/eliminar filas.</span>
        <button id="btn-close-bottom" class="px-4 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] hover:bg-[var(--c-surface)] transition">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API = '/api';
  const TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  if (!TOKEN) return;

  let currentPageId = null;
  let currentLang = 'es';
  let fieldValues = {};
  let fieldGroups = [];

  const $ = (s, e = document) => e.querySelector(s);
  const $$ = (s, e = document) => Array.from(e.querySelectorAll(s));
  const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

  async function api(url, opts = {}) {
    const method = (opts.method || 'GET').toUpperCase();
    const headers = { 'Accept': 'application/json', 'Authorization': `Bearer ${TOKEN}`, ...(opts.headers || {}) };
    if (opts.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
    const res = await fetch(url, { ...opts, method, headers });
    const json = await res.json().catch(() => null);
    if (!res.ok) { window.dispatchEvent(new CustomEvent('api:response', { detail: { success: false, message: json?.message || res.statusText } })); throw json; }
    return json;
  }

  // ── Cargar lista de páginas ──
  async function loadPages() {
    $('#pages-loading').classList.remove('hidden');
    try {
      const res = await api(`${API}/cms/pages?per_page=50`);
      if (res?.success) renderPageCards(res.data?.data || res.data || []);
    } finally {
      $('#pages-loading').classList.add('hidden');
    }
  }

  function renderPageCards(pages) {
    const container = $('#pages-list');
    container.innerHTML = pages.map(p => `
      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 hover:shadow-lg transition cursor-pointer" data-page-id="${p.id}" data-page-slug="${esc(p.slug)}">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-lg">📄</span>
              <h3 class="font-semibold text-[var(--c-text)] truncate">${esc(p.title_es)}</h3>
            </div>
            <p class="text-xs text-[var(--c-muted)] mt-1">/${esc(p.slug)}</p>
            ${p.title_en ? `<p class="text-xs text-[var(--c-muted)]">EN: ${esc(p.title_en)}</p>` : ''}
          </div>
          <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium ${p.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'}">${p.status}</span>
        </div>
        <div class="mt-3 flex items-center gap-2">
          <button class="btn-edit-fields px-3 py-1.5 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition" data-id="${p.id}" data-slug="${esc(p.slug)}" data-title="${esc(p.title_es)}">
            ✏️ Editar campos
          </button>
        </div>
      </div>
    `).join('');

    $$('.btn-edit-fields', container).forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        openDrawer(parseInt(btn.dataset.id), btn.dataset.slug, btn.dataset.title);
      });
    });
  }

  // ── Drawer: abrir y cargar campos ──
  async function openDrawer(pageId, slug, title) {
    currentPageId = pageId;
    $('#drawer-title').textContent = `Editar: ${title}`;
    $('#drawer-subtitle').textContent = `Slug: /${slug} • ID: ${pageId}`;
    $('#page-drawer').classList.remove('hidden');
    document.documentElement.style.overflow = 'hidden';

    // Cargar field groups + values
    try {
      const [groupsRes, valuesRes] = await Promise.all([
        api(`${API}/cms/field-groups?location_type=page&location_identifier=${slug}`),
        api(`${API}/cms/field-values/page/${pageId}`)
      ]);

      fieldGroups = groupsRes?.data || [];
      fieldValues = valuesRes?.data || {};
      renderFieldGroups();
    } catch (e) {
      $('#drawer-body').innerHTML = `<div class="text-center py-8 text-[var(--c-muted)]">Error cargando campos: ${e?.message || 'Error desconocido'}</div>`;
    }
  }

  function closeDrawer() {
    $('#page-drawer').classList.add('hidden');
    document.documentElement.style.overflow = '';
  }

  // ── Renderizar field groups con campos ──
  function renderFieldGroups() {
    const body = $('#drawer-body');
    if (!fieldGroups.length) {
      body.innerHTML = `<div class="text-center py-12"><p class="text-[var(--c-muted)]">Esta página no tiene field groups definidos.</p></div>`;
      return;
    }

    body.innerHTML = fieldGroups.map(group => `
      <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] overflow-hidden" data-group-slug="${esc(group.slug)}">
        <div class="px-5 py-3 border-b border-[var(--c-border)] bg-[var(--c-surface)]">
          <h4 class="font-semibold text-[var(--c-text)]">${esc(group.name)}</h4>
          ${group.description ? `<p class="text-xs text-[var(--c-muted)]">${esc(group.description)}</p>` : ''}
        </div>
        <div class="p-5 space-y-4">
          ${(group.field_definitions || []).map(fd => renderField(fd, group.slug)).join('')}
        </div>
      </div>
    `).join('');

    // Agregar event listeners para repeaters
    $$('[data-repeater-key]', body).forEach(container => {
      const key = container.dataset.repeaterKey;
      const addBtn = $(`[data-add-row="${key}"]`, body);
      if (addBtn) addBtn.addEventListener('click', () => addRepeaterRow(key, container));
    });

    $$('.btn-delete-row', body).forEach(btn => {
      btn.addEventListener('click', () => {
        btn.closest('.repeater-row').remove();
      });
    });
  }

  function renderField(fd, groupSlug) {
    const val = getFieldValue(fd.field_key, groupSlug);

    if (fd.type === 'repeater') {
      return renderRepeater(fd, groupSlug);
    }

    const valueEs = val?.value_es || '';
    const valueEn = val?.value_en || '';
    const isTranslatable = fd.is_translatable;

    let input = '';
    switch (fd.type) {
      case 'text':
      case 'url':
      case 'email':
      case 'phone':
      case 'number':
      case 'color':
      case 'date':
        const inputType = fd.type === 'number' ? 'number' : (fd.type === 'email' ? 'email' : (fd.type === 'url' ? 'url' : (fd.type === 'color' ? 'color' : (fd.type === 'date' ? 'date' : 'text'))));
        if (isTranslatable) {
          input = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <span class="text-xs text-[var(--c-muted)] mb-1 block">🇪🇸 Español</span>
                <input type="${inputType}" data-field-key="${esc(fd.field_key)}" data-lang="es" value="${esc(valueEs)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />
              </div>
              <div>
                <span class="text-xs text-[var(--c-muted)] mb-1 block">🇺🇸 English</span>
                <input type="${inputType}" data-field-key="${esc(fd.field_key)}" data-lang="en" value="${esc(valueEn)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />
              </div>
            </div>`;
        } else {
          input = `<input type="${inputType}" data-field-key="${esc(fd.field_key)}" data-lang="es" value="${esc(valueEs)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />`;
        }
        break;
      case 'textarea':
      case 'wysiwyg':
        if (isTranslatable) {
          input = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <span class="text-xs text-[var(--c-muted)] mb-1 block">🇪🇸 Español</span>
                <textarea data-field-key="${esc(fd.field_key)}" data-lang="es" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm">${esc(valueEs)}</textarea>
              </div>
              <div>
                <span class="text-xs text-[var(--c-muted)] mb-1 block">🇺🇸 English</span>
                <textarea data-field-key="${esc(fd.field_key)}" data-lang="en" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm">${esc(valueEn)}</textarea>
              </div>
            </div>`;
        } else {
          input = `<textarea data-field-key="${esc(fd.field_key)}" data-lang="es" rows="3" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm">${esc(valueEs)}</textarea>`;
        }
        break;
      case 'boolean':
        input = `<label class="inline-flex items-center gap-2"><input type="checkbox" data-field-key="${esc(fd.field_key)}" data-lang="es" ${valueEs === '1' ? 'checked' : ''} class="rounded border-[var(--c-border)] text-[var(--c-primary)]" /><span class="text-sm">Activado</span></label>`;
        break;
      default:
        input = `<input type="text" data-field-key="${esc(fd.field_key)}" data-lang="es" value="${esc(valueEs)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-surface)] border border-[var(--c-border)] text-sm" />`;
    }

    return `
      <div class="field-wrapper">
        <label class="block text-sm font-medium text-[var(--c-text)] mb-1">
          ${esc(fd.label_es)} ${fd.is_required ? '<span class="text-red-400">*</span>' : ''}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(fd.type)}] ${esc(fd.field_key)}</span>
        </label>
        ${fd.instructions_es ? `<p class="text-xs text-[var(--c-muted)] mb-2">${esc(fd.instructions_es)}</p>` : ''}
        ${input}
      </div>`;
  }

  function renderRepeater(fd, groupSlug) {
    const val = getFieldValue(fd.field_key, groupSlug);
    const rows = val?.rows || [];
    const subFields = fd.children || [];

    return `
      <div class="field-wrapper">
        <label class="block text-sm font-semibold text-[var(--c-text)] mb-2">
          🔁 ${esc(fd.label_es)}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[repeater] ${esc(fd.field_key)}</span>
        </label>
        <div data-repeater-key="${esc(fd.field_key)}" class="space-y-3">
          ${rows.map((row, idx) => renderRepeaterRow(fd.field_key, subFields, row, idx)).join('')}
        </div>
        <button type="button" data-add-row="${esc(fd.field_key)}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
          Agregar fila
        </button>
      </div>`;
  }

  function renderRepeaterRow(repeaterKey, subFields, rowData, rowIndex) {
    const fieldsHtml = subFields.map(sf => {
      const subVal = rowData?.[sf.field_key] || {};
      const valEs = subVal?.value_es || '';
      const valEn = subVal?.value_en || '';
      const isTranslatable = sf.is_translatable;

      if (sf.type === 'image' || sf.type === 'file') {
        return `<div class="text-xs text-[var(--c-muted)]">[${sf.type}] ${esc(sf.label_es)} — Media Picker</div>`;
      }

      if (sf.type === 'textarea') {
        if (isTranslatable) {
          return `<div><label class="text-xs font-medium">${esc(sf.label_es)}</label><div class="grid grid-cols-2 gap-2"><textarea data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="es" rows="2" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs">${esc(valEs)}</textarea><textarea data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="en" rows="2" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs">${esc(valEn)}</textarea></div></div>`;
        }
        return `<div><label class="text-xs font-medium">${esc(sf.label_es)}</label><textarea data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="es" rows="2" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs">${esc(valEs)}</textarea></div>`;
      }

      if (isTranslatable) {
        return `<div><label class="text-xs font-medium">${esc(sf.label_es)}</label><div class="grid grid-cols-2 gap-2"><input data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="es" value="${esc(valEs)}" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs" placeholder="ES" /><input data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="en" value="${esc(valEn)}" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs" placeholder="EN" /></div></div>`;
      }
      return `<div><label class="text-xs font-medium">${esc(sf.label_es)}</label><input data-repeater="${esc(repeaterKey)}" data-row="${rowIndex}" data-sub-key="${esc(sf.field_key)}" data-lang="es" value="${esc(valEs)}" class="w-full px-2 py-1 rounded-lg bg-[var(--c-surface)] border border-[var(--c-border)] text-xs" /></div>`;
    }).join('');

    return `
      <div class="repeater-row rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4" data-row-index="${rowIndex}">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-medium text-[var(--c-muted)]">#${rowIndex + 1}</span>
          <button type="button" class="btn-delete-row text-xs text-red-500 hover:text-red-700">✕ Eliminar</button>
        </div>
        <div class="space-y-3">${fieldsHtml}</div>
      </div>`;
  }

  function addRepeaterRow(repeaterKey, container) {
    const group = fieldGroups.find(g => g.field_definitions?.some(fd => fd.field_key === repeaterKey));
    if (!group) return;
    const fd = group.field_definitions.find(f => f.field_key === repeaterKey);
    if (!fd) return;
    const existingRows = $$('.repeater-row', container);
    const newIndex = existingRows.length;
    const newRow = document.createElement('div');
    newRow.innerHTML = renderRepeaterRow(repeaterKey, fd.children || [], {}, newIndex);
    container.appendChild(newRow.firstElementChild);
    // Add delete listener
    const delBtn = container.lastElementChild.querySelector('.btn-delete-row');
    if (delBtn) delBtn.addEventListener('click', () => delBtn.closest('.repeater-row').remove());
  }

  function getFieldValue(fieldKey, groupSlug) {
    const groupValues = fieldValues[groupSlug];
    return groupValues?.[fieldKey] || null;
  }

  // ── Guardar todos los campos ──
  async function saveAllFields() {
    const fields = {};

    // Campos simples
    $$('[data-field-key]', $('#drawer-body')).forEach(el => {
      const key = el.dataset.fieldKey;
      const lang = el.dataset.lang;
      if (!fields[key]) fields[key] = {};
      if (el.type === 'checkbox') {
        fields[key][`value_${lang}`] = el.checked ? '1' : '0';
      } else {
        fields[key][`value_${lang}`] = el.value;
      }
    });

    // Campos de repeater
    $$('[data-repeater]', $('#drawer-body')).forEach(el => {
      const repeaterKey = el.dataset.repeater;
      const rowIdx = parseInt(el.dataset.row);
      const subKey = el.dataset.subKey;
      const lang = el.dataset.lang;

      if (!fields[repeaterKey]) fields[repeaterKey] = { rows: [] };
      if (!fields[repeaterKey].rows) fields[repeaterKey].rows = [];
      while (fields[repeaterKey].rows.length <= rowIdx) fields[repeaterKey].rows.push({});
      if (!fields[repeaterKey].rows[rowIdx][subKey]) fields[repeaterKey].rows[rowIdx][subKey] = {};
      fields[repeaterKey].rows[rowIdx][subKey][`value_${lang}`] = el.value;
    });

    try {
      const res = await api(`${API}/cms/field-values/page/${currentPageId}`, {
        method: 'PUT',
        body: JSON.stringify({ fields })
      });
      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: { success: true, message: 'Campos guardados correctamente' } }));
      }
    } catch (e) { /* error dispatched */ }
  }

  // ── Event listeners ──
  $('#btn-refresh').addEventListener('click', loadPages);
  $('[data-js="overlay"]', $('#page-drawer')).addEventListener('click', closeDrawer);
  $('#btn-close').addEventListener('click', closeDrawer);
  $('#btn-close-bottom').addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !$('#page-drawer').classList.contains('hidden')) closeDrawer(); });
  $('#btn-save').addEventListener('click', saveAllFields);

  // Lang toggle (visual only for now)
  $('#btn-lang-es').addEventListener('click', () => { currentLang = 'es'; $('#btn-lang-es').className = 'px-2 py-1 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]'; $('#btn-lang-en').className = 'px-2 py-1 text-xs rounded-lg text-[var(--c-muted)]'; });
  $('#btn-lang-en').addEventListener('click', () => { currentLang = 'en'; $('#btn-lang-en').className = 'px-2 py-1 text-xs rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)]'; $('#btn-lang-es').className = 'px-2 py-1 text-xs rounded-lg text-[var(--c-muted)]'; });

  // ── Init ──
  loadPages();
});
</script>
@endsection
