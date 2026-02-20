@extends('layouts.app')

@section('title', 'CMS - Configuración del Sitio')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">⚙️ Configuración del Sitio</h1>
      <p class="text-[var(--c-muted)] mt-1">Información de contacto, redes sociales, logos, SEO y datos de la empresa (bilingüe ES/EN)</p>
    </div>
    <button id="btn-save-all" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 transition shadow-soft">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
      Guardar todo
    </button>
  </div>

  <div id="settings-container" class="space-y-6">
    <div id="settings-loading" class="py-10"><div class="animate-pulse space-y-3"><div class="h-24 rounded-2xl bg-[var(--c-elev)]"></div><div class="h-24 rounded-2xl bg-[var(--c-elev)]"></div></div></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API = '/api';
  const TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  if (!TOKEN) return;

  const $ = (s, e = document) => e.querySelector(s);
  const $$ = (s, e = document) => Array.from(e.querySelectorAll(s));
  const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

  async function api(url, opts = {}) {
    const method = (opts.method || 'GET').toUpperCase();
    const headers = { 'Accept': 'application/json', 'Authorization': `Bearer ${TOKEN}`, ...(opts.headers || {}) };
    if (opts.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
    const res = await fetch(url, { ...opts, method, headers });
    const json = await res.json().catch(() => null);
    if (!res.ok) throw json;
    return json;
  }

  const groupLabels = {
    contact: { icon: '📞', name: 'Contacto', desc: 'Teléfono, email, WhatsApp, dirección' },
    social: { icon: '🌐', name: 'Redes Sociales', desc: 'Facebook, Instagram, Twitter, LinkedIn' },
    general: { icon: '🏠', name: 'General', desc: 'Nombre del sitio, tagline, logos, copyright' },
    seo: { icon: '🔍', name: 'SEO', desc: 'Meta tags por defecto, Google Analytics' },
    company: { icon: '🏢', name: 'Empresa', desc: 'Nombre legal, horario de oficina' },
  };

  const nonTranslatable = ['phone', 'email', 'boolean', 'image'];

  async function loadSettings() {
    try {
      const res = await api(`${API}/cms/settings`);
      if (res?.success) renderSettings(res.data);
    } catch (e) {
      $('#settings-container').innerHTML = `<div class="text-center py-8 text-[var(--c-muted)]">Error cargando settings</div>`;
    }
  }

  function renderSettings(groupedSettings) {
    const container = $('#settings-container');
    container.innerHTML = '';

    for (const [group, settings] of Object.entries(groupedSettings)) {
      const meta = groupLabels[group] || { icon: '⚙️', name: group, desc: '' };
      const fieldsHtml = settings.map(s => renderSettingField(s)).join('');

      const html = `
        <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
          <div class="px-5 py-3 border-b border-[var(--c-border)] bg-[var(--c-elev)]">
            <h3 class="font-semibold text-[var(--c-text)]">${meta.icon} ${esc(meta.name)}</h3>
            <p class="text-xs text-[var(--c-muted)]">${esc(meta.desc)}</p>
          </div>
          <div class="p-5 space-y-4">
            ${fieldsHtml}
          </div>
        </div>`;
      container.insertAdjacentHTML('beforeend', html);
    }

    // Inicializar media inputs después de renderizar
    initMediaInputs();
  }

  function renderSettingField(s) {
    const translatable = !nonTranslatable.includes(s.type);

    // Campo tipo IMAGE → usa media-input picker
    if (s.type === 'image') {
      const mediaId = s.media_asset_id || '';
      const mediaUrl = s.media_asset?.url || '';
      const uniqueId = 'setting_img_' + s.setting_key;

      return `
        <div class="setting-field" data-setting-key="${esc(s.setting_key)}" data-type="image">
          <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
            🖼️ ${esc(s.label_es)}
            <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(s.setting_key)}]</span>
          </label>
          ${mediaUrl ? `<div class="mb-2"><img src="${esc(mediaUrl)}" class="h-16 rounded-xl border border-[var(--c-border)] object-contain" alt="Preview" /></div>` : ''}
          <div class="media-input-wrapper">
            <x-media-input
              name="${uniqueId}"
              mode="single"
              :max="1"
              value="${mediaId}"
              placeholder="ID del media asset"
              button="Seleccionar imagen"
              preview="true"
              columns="4"
            />
          </div>
        </div>`;
    }

    // Campo tipo TEXTAREA
    if (s.type === 'textarea') {
      if (translatable) {
        return `<div><label class="block text-sm font-medium text-[var(--c-text)] mb-1">${esc(s.label_es)} <span class="text-xs text-[var(--c-muted)] font-normal">[${esc(s.setting_key)}]</span></label><div class="grid grid-cols-1 md:grid-cols-2 gap-3"><div><span class="text-xs text-[var(--c-muted)]">🇪🇸 ES</span><textarea data-setting-key="${esc(s.setting_key)}" data-lang="es" rows="2" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">${esc(s.value_es)}</textarea></div><div><span class="text-xs text-[var(--c-muted)]">🇺🇸 EN</span><textarea data-setting-key="${esc(s.setting_key)}" data-lang="en" rows="2" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">${esc(s.value_en)}</textarea></div></div></div>`;
      }
      return `<div><label class="block text-sm font-medium text-[var(--c-text)] mb-1">${esc(s.label_es)}</label><textarea data-setting-key="${esc(s.setting_key)}" data-lang="es" rows="2" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm">${esc(s.value_es)}</textarea></div>`;
    }

    // Campos de texto (translatable o no)
    if (translatable) {
      return `<div><label class="block text-sm font-medium text-[var(--c-text)] mb-1">${esc(s.label_es)} <span class="text-xs text-[var(--c-muted)] font-normal">[${esc(s.setting_key)}]</span></label><div class="grid grid-cols-1 md:grid-cols-2 gap-3"><div><span class="text-xs text-[var(--c-muted)]">🇪🇸 ES</span><input data-setting-key="${esc(s.setting_key)}" data-lang="es" value="${esc(s.value_es)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" /></div><div><span class="text-xs text-[var(--c-muted)]">🇺🇸 EN</span><input data-setting-key="${esc(s.setting_key)}" data-lang="en" value="${esc(s.value_en)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" /></div></div></div>`;
    }
    return `<div><label class="block text-sm font-medium text-[var(--c-text)] mb-1">${esc(s.label_es)} <span class="text-xs text-[var(--c-muted)] font-normal">[${esc(s.type)}]</span></label><input data-setting-key="${esc(s.setting_key)}" data-lang="es" value="${esc(s.value_es)}" class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm" /></div>`;
  }

  /**
   * Inicializa los campos de imagen con el media picker.
   * Los inputs se crean dinámicamente, así que necesitamos:
   * 1. Crear el HTML del input + botón
   * 2. Bindear el botón para abrir el picker manualmente
   * 3. El MutationObserver de media-inputs.js se encarga del preview
   */
  function initMediaInputs() {
    $$('.setting-field[data-type="image"]').forEach(wrapper => {
      const key = wrapper.dataset.settingKey;
      const mediaInputWrapper = wrapper.querySelector('.media-input-wrapper');
      if (!mediaInputWrapper || mediaInputWrapper.dataset.initialized === '1') return;
      mediaInputWrapper.dataset.initialized = '1';

      // Obtener el valor actual del media_asset_id
      const existingInput = mediaInputWrapper.querySelector('input[type="text"]');
      const currentValue = existingInput?.value || '';

      // Crear el HTML del input + botón
      const uniqueId = 'setting_img_' + key;
      mediaInputWrapper.innerHTML = `
        <div data-fp-scope class="rounded-2xl border border-[var(--c-border)] p-4 bg-[var(--c-surface)]">
          <div class="flex items-center gap-3">
            <input
              type="text"
              name="${uniqueId}"
              id="${uniqueId}"
              value="${esc(currentValue)}"
              placeholder="ID del media asset"
              readonly
              class="w-full rounded-lg border px-3 py-2 bg-[var(--c-elev)] border-[var(--c-border)] text-sm"
              data-filepicker="single"
              data-fp-max="1"
              data-fp-per-page="10"
              data-fp-preview="#${uniqueId}_preview"
              data-fp-columns="4"
              data-setting-image-key="${esc(key)}"
            >
            <button
              type="button"
              class="cms-fp-open-btn px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 text-sm whitespace-nowrap"
              data-fp-open
              aria-controls="archive_manager-root"
            >
              Seleccionar
            </button>
          </div>
          <div id="${uniqueId}_preview" class="mt-3"></div>
        </div>`;

      // Bindear el botón para abrir el media picker usando la API global
      // media-picker.js expone window.openMediaPickerFor(input)
      const btn = mediaInputWrapper.querySelector('.cms-fp-open-btn');
      const input = mediaInputWrapper.querySelector('input[data-filepicker]');
      if (btn && input) {
        btn.addEventListener('click', () => {
          if (typeof window.openMediaPickerFor === 'function') {
            window.openMediaPickerFor(input);
          } else {
            console.error('Media Picker no disponible. Asegúrate de que media-picker.js esté cargado.');
          }
        });
      }
    });
  }

  async function saveAll() {
    const settings = {};

    // Campos de texto/textarea
    $$('[data-setting-key]:not([data-setting-image-key])').forEach(el => {
      const key = el.dataset.settingKey;
      const lang = el.dataset.lang;
      if (!settings[key]) settings[key] = {};
      settings[key][`value_${lang}`] = el.value;
    });

    // Campos de imagen (media_asset_id)
    $$('[data-setting-image-key]').forEach(el => {
      const key = el.dataset.settingImageKey;
      const mediaId = el.value ? parseInt(el.value) : null;
      if (!settings[key]) settings[key] = {};
      settings[key]['media_asset_id'] = mediaId;
    });

    try {
      const res = await api(`${API}/cms/settings/bulk`, {
        method: 'PUT',
        body: JSON.stringify({ settings })
      });
      if (res?.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: { success: true, message: res.data?.updated ? `${res.data.updated} settings actualizados` : 'Settings guardados' } }));
      }
    } catch (e) {
      window.dispatchEvent(new CustomEvent('api:response', { detail: { success: false, message: e?.message || 'Error guardando settings' } }));
    }
  }

  $('#btn-save-all').addEventListener('click', saveAll);
  loadSettings();
});
</script>
@endsection
