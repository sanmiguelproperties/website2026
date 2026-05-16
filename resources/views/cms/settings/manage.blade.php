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
  const HERO_SLIDER_KEYS = {
    sourceType: 'hero_slider_source_type',
    propertyIds: 'hero_slider_property_ids',
    imageIds: 'hero_slider_image_ids',
  };
  const HERO_SLIDER_MAX_PROPERTIES = 10;
  const HERO_SLIDER_MAX_IMAGES = 10;
  const HOME_FEATURED_PROPERTY_IDS_KEY = 'home_featured_property_ids';
  const HOME_FEATURED_MAX_PROPERTIES = 6;
  const heroPropertyCache = new Map();
  const normalizeSourceType = (value) => String(value || '').toLowerCase() === 'images' ? 'images' : 'properties';

  function parseIdList(value, max = Infinity) {
    const source = Array.isArray(value) ? value.join(',') : String(value ?? '');
    const seen = new Set();
    const ids = [];

    source.split(',').forEach(part => {
      const parsed = parseInt(String(part).trim(), 10);
      if (!Number.isFinite(parsed) || parsed <= 0 || seen.has(parsed)) return;
      seen.add(parsed);
      ids.push(parsed);
    });

    return ids.slice(0, Math.max(0, max));
  }

  function idsToCsv(ids, max = Infinity) {
    return parseIdList(ids, max).join(',');
  }

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
    header: { icon: 'H', name: 'Header', desc: 'Logo en desktop y móvil' },
    seo: { icon: '🔍', name: 'SEO', desc: 'Meta tags por defecto, Google Analytics' },
    company: { icon: '🏢', name: 'Empresa', desc: 'Nombre legal, horario de oficina' },
  };

  const nonTranslatable = ['phone', 'email', 'boolean', 'image', 'number'];
  const numericFieldConfig = {
    header_logo_height_desktop: {
      min: 24,
      max: 96,
      hint: 'Valor en px. Recomendado entre 40 y 64.',
    },
    header_logo_height_mobile: {
      min: 20,
      max: 80,
      hint: 'Valor en px para teléfono. Recomendado entre 28 y 48.',
    },
    header_height_desktop: {
      min: 80,
      max: 200,
      hint: 'Altura del header en escritorio. En móvil se mantiene la altura actual.',
    },
  };

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

    // Inicializar media inputs despues de renderizar
    initMediaInputs();
    initHeroSliderControls();
    initHomeFeaturedPropertyPickers();
  }

  function renderHeroSliderSourceField(s) {
    const current = normalizeSourceType(s.value_es);

    return `
      <div class="setting-field" data-setting-key="${esc(s.setting_key)}" data-type="hero-slider-source">
        <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
          ${esc(s.label_es)}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(s.setting_key)}]</span>
        </label>
        <select
          data-setting-key="${esc(s.setting_key)}"
          data-lang="es"
          data-hero-slider-mode-select
          class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"
        >
          <option value="properties" ${current === 'properties' ? 'selected' : ''}>Propiedades seleccionadas</option>
          <option value="images" ${current === 'images' ? 'selected' : ''}>Imagenes manuales</option>
        </select>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Elige si el hero usa propiedades publicadas o imágenes del media manager.</p>
      </div>`;
  }

  function renderHeroSliderPropertyField(s) {
    const initialCsv = idsToCsv(s.value_es, HERO_SLIDER_MAX_PROPERTIES);

    return `
      <div class="setting-field" data-setting-key="${esc(s.setting_key)}" data-type="hero-slider-properties" data-hero-slider-role="properties">
        <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
          ${esc(s.label_es)}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(s.setting_key)}]</span>
        </label>
        <input type="hidden" data-setting-key="${esc(s.setting_key)}" data-lang="es" data-hero-prop-ids-input value="${esc(initialCsv)}" />
        <div class="rounded-2xl border border-[var(--c-border)] p-4 bg-[var(--c-surface)] space-y-3">
          <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <input
              type="search"
              data-hero-prop-search
              placeholder="Buscar propiedad por título o ID..."
              class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"
            />
            <button
              type="button"
              data-hero-prop-search-btn
              class="px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 text-sm whitespace-nowrap"
            >
              Buscar
            </button>
          </div>
          <p class="text-xs text-[var(--c-muted)]">
            Selecciona hasta ${HERO_SLIDER_MAX_PROPERTIES} propiedades publicadas. El slider usará la imagen destacada de cada una.
          </p>
          <div data-hero-prop-selected class="flex flex-wrap gap-2"></div>
          <div data-hero-prop-results class="space-y-2 hidden"></div>
        </div>
      </div>`;
  }

  function renderHomeFeaturedPropertyField(s) {
    const initialCsv = idsToCsv(s.value_es, HOME_FEATURED_MAX_PROPERTIES);

    return `
      <div class="setting-field" data-setting-key="${esc(s.setting_key)}" data-type="home-featured-properties">
        <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
          ${esc(s.label_es)}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(s.setting_key)}]</span>
        </label>
        <input type="hidden" data-setting-key="${esc(s.setting_key)}" data-lang="es" data-home-featured-prop-ids-input value="${esc(initialCsv)}" />
        <div class="rounded-2xl border border-[var(--c-border)] p-4 bg-[var(--c-surface)] space-y-3">
          <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <input
              type="search"
              data-home-featured-prop-search
              placeholder="Buscar propiedad por titulo o ID..."
              class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"
            />
            <button
              type="button"
              data-home-featured-prop-search-btn
              class="px-4 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 text-sm whitespace-nowrap"
            >
              Buscar
            </button>
          </div>
          <p class="text-xs text-[var(--c-muted)]">
            Selecciona hasta ${HOME_FEATURED_MAX_PROPERTIES} propiedades publicadas. Estas seran las primeras tarjetas de la home mientras no haya filtros ni orden personalizado.
          </p>
          <div data-home-featured-prop-selected class="flex flex-wrap gap-2"></div>
          <div data-home-featured-prop-results class="space-y-2 hidden"></div>
        </div>
      </div>`;
  }

  function renderHeroSliderImageField(s) {
    const key = s.setting_key;
    const uniqueId = `setting_img_${key}`;
    const initialCsv = idsToCsv(s.value_es, HERO_SLIDER_MAX_IMAGES);

    return `
      <div class="setting-field" data-setting-key="${esc(key)}" data-type="hero-slider-images" data-hero-slider-role="images">
        <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
          ${esc(s.label_es)}
          <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(key)}]</span>
        </label>
        <div class="media-input-wrapper">
          <div data-fp-scope class="rounded-2xl border border-[var(--c-border)] p-4 bg-[var(--c-surface)]">
            <div class="flex items-center gap-3">
              <input
                type="text"
                id="${uniqueId}"
                value="${esc(initialCsv)}"
                placeholder="IDs separados por coma"
                readonly
                class="w-full rounded-lg border px-3 py-2 bg-[var(--c-elev)] border-[var(--c-border)] text-sm"
                data-filepicker="multiple"
                data-fp-max="${HERO_SLIDER_MAX_IMAGES}"
                data-fp-per-page="10"
                data-fp-preview="#${uniqueId}_preview"
                data-fp-columns="5"
                data-setting-key="${esc(key)}"
                data-lang="es"
              />
              <button
                type="button"
                class="cms-fp-open-btn px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 text-sm whitespace-nowrap"
                data-hero-slider-open-media
                aria-controls="archive_manager-root"
              >
                Seleccionar imágenes
              </button>
            </div>
            <div id="${uniqueId}_preview" class="mt-3"></div>
          </div>
        </div>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Selecciona hasta ${HERO_SLIDER_MAX_IMAGES} imágenes para el fondo del hero.</p>
      </div>`;
  }

  function renderSettingField(s) {
    if (s.setting_key === HERO_SLIDER_KEYS.sourceType) {
      return renderHeroSliderSourceField(s);
    }
    if (s.setting_key === HERO_SLIDER_KEYS.propertyIds) {
      return renderHeroSliderPropertyField(s);
    }
    if (s.setting_key === HERO_SLIDER_KEYS.imageIds) {
      return renderHeroSliderImageField(s);
    }
    if (s.setting_key === HOME_FEATURED_PROPERTY_IDS_KEY) {
      return renderHomeFeaturedPropertyField(s);
    }

    const translatable = !nonTranslatable.includes(s.type);
    const settingIsTruthy = (value) => ['1', 'true', 'yes', 'on', 'si'].includes(String(value ?? '').trim().toLowerCase());

    // Campo tipo IMAGE -> usa media-input picker
    if (s.type === 'image') {
      const mediaId = s.media_asset_id || '';
      const mediaUrl = s.media_asset?.serving_url || s.media_asset?.url || '';

      return `
        <div class="setting-field" data-setting-key="${esc(s.setting_key)}" data-type="image">
          <label class="block text-sm font-medium text-[var(--c-text)] mb-2">
            🖼️ ${esc(s.label_es)}
            <span class="text-xs text-[var(--c-muted)] font-normal ml-2">[${esc(s.setting_key)}]</span>
          </label>
          ${mediaUrl ? `<div class="mb-2"><img src="${esc(mediaUrl)}" class="h-16 rounded-xl border border-[var(--c-border)] object-contain" alt="Preview" /></div>` : ''}
          <div class="media-input-wrapper">
            <input type="text" value="${esc(mediaId)}" hidden />
          </div>
        </div>`;
    }

    // Campo tipo BOOLEAN
    if (s.type === 'boolean') {
      const checked = settingIsTruthy(s.value_es) ? 'checked' : '';
      return `
        <div>
          <label class="flex items-center gap-3 text-sm font-medium text-[var(--c-text)]">
            <input
              type="checkbox"
              data-setting-key="${esc(s.setting_key)}"
              data-lang="es"
              ${checked}
              class="h-4 w-4 rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]"
            />
            <span>${esc(s.label_es)} <span class="text-xs text-[var(--c-muted)] font-normal">[${esc(s.setting_key)}]</span></span>
          </label>
        </div>`;
    }

    // Campo tipo NUMBER
    if (s.type === 'number') {
      const config = numericFieldConfig[s.setting_key] || { min: 0, max: 9999, hint: 'Valor numérico.' };
      return `
        <div>
          <label class="block text-sm font-medium text-[var(--c-text)] mb-1">
            ${esc(s.label_es)}
            <span class="text-xs text-[var(--c-muted)] font-normal">[${esc(s.setting_key)}]</span>
          </label>
          <div class="flex items-center gap-2">
            <input
              type="number"
              data-setting-key="${esc(s.setting_key)}"
              data-lang="es"
              value="${esc(s.value_es)}"
              min="${config.min}"
              max="${config.max}"
              step="1"
              class="w-full px-3 py-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] text-sm"
            />
            <span class="text-xs text-[var(--c-muted)]">px</span>
          </div>
          <p class="mt-1 text-xs text-[var(--c-muted)]">${esc(config.hint)}</p>
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

  function bindMediaPickerButton(button, input) {
    if (!button || !input || button.dataset.fpBound === '1') return;
    button.dataset.fpBound = '1';

    button.addEventListener('click', () => {
      if (typeof window.openMediaPickerFor === 'function') {
        window.openMediaPickerFor(input);
      } else {
        console.error('Media Picker no disponible. Asegurate de que media-picker.js este cargado.');
      }
    });
  }

  /**
   * Inicializa los campos de imagen con el media picker.
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

      // Crear el HTML del input + boton
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
              aria-controls="archive_manager-root"
            >
              Seleccionar
            </button>
          </div>
          <div id="${uniqueId}_preview" class="mt-3"></div>
        </div>`;

      const btn = mediaInputWrapper.querySelector('.cms-fp-open-btn');
      const input = mediaInputWrapper.querySelector('input[data-filepicker]');
      bindMediaPickerButton(btn, input);
    });
  }

  function initHeroSliderControls() {
    initHeroSliderModeToggle();
    initHeroSliderImagePickers();
    initHeroSliderPropertyPickers();
  }

  function initHeroSliderModeToggle() {
    const modeInput = $('[data-hero-slider-mode-select]');
    if (!modeInput) return;

    const applyModeVisibility = () => {
      const activeMode = normalizeSourceType(modeInput.value);
      $$('[data-hero-slider-role]').forEach(field => {
        const role = field.dataset.heroSliderRole;
        field.classList.toggle('hidden', role !== activeMode);
      });
    };

    if (modeInput.dataset.initialized !== '1') {
      modeInput.dataset.initialized = '1';
      modeInput.addEventListener('change', applyModeVisibility);
    }

    applyModeVisibility();
  }

  function initHeroSliderImagePickers() {
    $$('.setting-field[data-type="hero-slider-images"]').forEach(wrapper => {
      if (wrapper.dataset.initialized === '1') return;
      wrapper.dataset.initialized = '1';

      const btn = wrapper.querySelector('[data-hero-slider-open-media]');
      const input = wrapper.querySelector('input[data-filepicker]');
      bindMediaPickerButton(btn, input);
    });
  }

  function normalizePropertySummary(property) {
    if (!property || !property.id) return null;

    return {
      id: Number(property.id),
      title: property.title || `Propiedad #${property.id}`,
      property_type_name: property.property_type_name || '',
      published: property.published === undefined ? true : Boolean(property.published),
      cover_url: property.cover_media_asset?.serving_url || property.cover_media_asset?.url || '',
      city: property.location?.city || '',
      city_area: property.location?.city_area || '',
    };
  }

  async function fetchPropertySummaryById(id) {
    const normalizedId = Number(id);
    if (!Number.isFinite(normalizedId) || normalizedId <= 0) return null;
    if (heroPropertyCache.has(normalizedId)) return heroPropertyCache.get(normalizedId);

    try {
      const response = await api(`${API}/properties/${normalizedId}`);
      if (!response?.success || !response?.data) return null;
      const summary = normalizePropertySummary(response.data);
      if (summary) heroPropertyCache.set(normalizedId, summary);
      return summary;
    } catch (_error) {
      return null;
    }
  }

  async function searchPublishedProperties(searchTerm = '') {
    const params = new URLSearchParams({
      per_page: '8',
      published: '1',
      order: 'updated_at',
      sort: 'desc',
    });

    if (searchTerm.trim()) {
      params.set('search', searchTerm.trim());
    }

    const response = await api(`${API}/properties?${params.toString()}`);
    const rows = response?.data?.data || [];

    return rows
      .map(normalizePropertySummary)
      .filter(Boolean);
  }

  function emitClientMessage(success, message) {
    window.dispatchEvent(new CustomEvent('api:response', { detail: { success, message } }));
  }

  function initHeroSliderPropertyPickers() {
    $$('.setting-field[data-type="hero-slider-properties"]').forEach(wrapper => {
      if (wrapper.dataset.initialized === '1') return;
      wrapper.dataset.initialized = '1';

      const hiddenInput = wrapper.querySelector('[data-hero-prop-ids-input]');
      const searchInput = wrapper.querySelector('[data-hero-prop-search]');
      const searchBtn = wrapper.querySelector('[data-hero-prop-search-btn]');
      const selectedContainer = wrapper.querySelector('[data-hero-prop-selected]');
      const resultsContainer = wrapper.querySelector('[data-hero-prop-results]');
      if (!hiddenInput || !searchInput || !searchBtn || !selectedContainer || !resultsContainer) return;

      const state = {
        selectedIds: parseIdList(hiddenInput.value, HERO_SLIDER_MAX_PROPERTIES),
        selectedMap: new Map(),
        searchRows: [],
        debounceTimer: null,
      };

      const syncHiddenInput = () => {
        hiddenInput.value = idsToCsv(state.selectedIds, HERO_SLIDER_MAX_PROPERTIES);
      };

      const renderSelected = () => {
        if (!state.selectedIds.length) {
          selectedContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">No hay propiedades seleccionadas.</p>`;
          return;
        }

        selectedContainer.innerHTML = state.selectedIds.map(id => {
          const property = state.selectedMap.get(id);
          const label = property?.title || `Propiedad #${id}`;
          const location = [property?.city, property?.city_area].filter(Boolean).join(', ');
          const status = property && property.published === false
            ? `<span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-700">No publicada</span>`
            : '';

          return `
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)]">
              <div class="min-w-0">
                <p class="text-xs font-medium text-[var(--c-text)] truncate">${esc(label)}</p>
                ${location ? `<p class="text-[11px] text-[var(--c-muted)] truncate">${esc(location)}</p>` : ''}
              </div>
              ${status}
              <button type="button" data-hero-prop-remove="${id}" class="text-[var(--c-muted)] hover:text-red-600 text-sm leading-none" title="Quitar">x</button>
            </div>
          `;
        }).join('');

        $$('[data-hero-prop-remove]', selectedContainer).forEach(btn => {
          btn.addEventListener('click', () => {
            const id = Number(btn.dataset.heroPropRemove);
            state.selectedIds = state.selectedIds.filter(currentId => currentId !== id);
            state.selectedMap.delete(id);
            syncHiddenInput();
            renderSelected();
            renderSearchResults(state.searchRows);
          });
        });
      };

      const renderSearchResults = (rows) => {
        state.searchRows = rows;
        resultsContainer.classList.remove('hidden');

        if (!rows.length) {
          resultsContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">No se encontraron propiedades publicadas.</p>`;
          return;
        }

        resultsContainer.innerHTML = rows.map(row => {
          const alreadySelected = state.selectedIds.includes(row.id);
          const location = [row.city, row.city_area].filter(Boolean).join(', ');
          const subtitle = [row.property_type_name, location].filter(Boolean).join(' • ');
          const canAdd = !alreadySelected && state.selectedIds.length < HERO_SLIDER_MAX_PROPERTIES;

          return `
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)]">
              <div class="min-w-0">
                <p class="text-sm font-medium text-[var(--c-text)] truncate">${esc(row.title)}</p>
                <p class="text-xs text-[var(--c-muted)] truncate">${esc(subtitle || `ID ${row.id}`)}</p>
              </div>
              <button
                type="button"
                data-hero-prop-add="${row.id}"
                class="px-3 py-1.5 rounded-lg text-xs ${canAdd ? 'bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95' : 'bg-[var(--c-surface)] text-[var(--c-muted)] cursor-not-allowed'}"
                ${canAdd ? '' : 'disabled'}
              >
                ${alreadySelected ? 'Agregada' : 'Agregar'}
              </button>
            </div>
          `;
        }).join('');

        $$('[data-hero-prop-add]', resultsContainer).forEach(btn => {
          btn.addEventListener('click', () => {
            const id = Number(btn.dataset.heroPropAdd);
            if (!Number.isFinite(id) || id <= 0) return;
            if (state.selectedIds.includes(id)) return;

            if (state.selectedIds.length >= HERO_SLIDER_MAX_PROPERTIES) {
              emitClientMessage(false, `Solo puedes seleccionar ${HERO_SLIDER_MAX_PROPERTIES} propiedades.`);
              return;
            }

            state.selectedIds.push(id);
            const row = state.searchRows.find(item => item.id === id);
            if (row) state.selectedMap.set(id, row);
            syncHiddenInput();
            renderSelected();
            renderSearchResults(state.searchRows);
          });
        });
      };

      const hydrateSelectedIds = async () => {
        if (!state.selectedIds.length) return;

        await Promise.all(state.selectedIds.map(async (id) => {
          const property = await fetchPropertySummaryById(id);
          if (property) state.selectedMap.set(id, property);
        }));

        renderSelected();
      };

      const runSearch = async () => {
        const term = searchInput.value || '';
        resultsContainer.classList.remove('hidden');
        resultsContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">Buscando...</p>`;

        try {
          const rows = await searchPublishedProperties(term);
          rows.forEach(row => heroPropertyCache.set(row.id, row));
          renderSearchResults(rows);
        } catch (_error) {
          resultsContainer.innerHTML = `<p class="text-xs text-red-600">No se pudo cargar la lista de propiedades.</p>`;
        }
      };

      searchBtn.addEventListener('click', runSearch);
      searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        runSearch();
      });
      searchInput.addEventListener('input', () => {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(runSearch, 350);
      });

      syncHiddenInput();
      renderSelected();
      hydrateSelectedIds();
      runSearch();
    });
  }

  function initHomeFeaturedPropertyPickers() {
    $$('.setting-field[data-type="home-featured-properties"]').forEach(wrapper => {
      if (wrapper.dataset.initialized === '1') return;
      wrapper.dataset.initialized = '1';

      const hiddenInput = wrapper.querySelector('[data-home-featured-prop-ids-input]');
      const searchInput = wrapper.querySelector('[data-home-featured-prop-search]');
      const searchBtn = wrapper.querySelector('[data-home-featured-prop-search-btn]');
      const selectedContainer = wrapper.querySelector('[data-home-featured-prop-selected]');
      const resultsContainer = wrapper.querySelector('[data-home-featured-prop-results]');
      if (!hiddenInput || !searchInput || !searchBtn || !selectedContainer || !resultsContainer) return;

      const state = {
        selectedIds: parseIdList(hiddenInput.value, HOME_FEATURED_MAX_PROPERTIES),
        selectedMap: new Map(),
        searchRows: [],
        debounceTimer: null,
      };

      const syncHiddenInput = () => {
        hiddenInput.value = idsToCsv(state.selectedIds, HOME_FEATURED_MAX_PROPERTIES);
      };

      const renderSelected = () => {
        if (!state.selectedIds.length) {
          selectedContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">No hay propiedades seleccionadas para la home.</p>`;
          return;
        }

        selectedContainer.innerHTML = state.selectedIds.map((id, index) => {
          const property = state.selectedMap.get(id);
          const label = property?.title || `Propiedad #${id}`;
          const location = [property?.city, property?.city_area].filter(Boolean).join(', ');
          const status = property && property.published === false
            ? `<span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-700">No publicada</span>`
            : '';

          return `
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)]">
              <span class="text-[11px] font-semibold text-[var(--c-muted)]">#${index + 1}</span>
              <div class="min-w-0">
                <p class="text-xs font-medium text-[var(--c-text)] truncate">${esc(label)}</p>
                ${location ? `<p class="text-[11px] text-[var(--c-muted)] truncate">${esc(location)}</p>` : ''}
              </div>
              ${status}
              <button type="button" data-home-featured-prop-remove="${id}" class="text-[var(--c-muted)] hover:text-red-600 text-sm leading-none" title="Quitar">x</button>
            </div>
          `;
        }).join('');

        $$('[data-home-featured-prop-remove]', selectedContainer).forEach(btn => {
          btn.addEventListener('click', () => {
            const id = Number(btn.dataset.homeFeaturedPropRemove);
            state.selectedIds = state.selectedIds.filter(currentId => currentId !== id);
            state.selectedMap.delete(id);
            syncHiddenInput();
            renderSelected();
            renderSearchResults(state.searchRows);
          });
        });
      };

      const renderSearchResults = (rows) => {
        state.searchRows = rows;
        resultsContainer.classList.remove('hidden');

        if (!rows.length) {
          resultsContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">No se encontraron propiedades publicadas.</p>`;
          return;
        }

        resultsContainer.innerHTML = rows.map(row => {
          const alreadySelected = state.selectedIds.includes(row.id);
          const location = [row.city, row.city_area].filter(Boolean).join(', ');
          const subtitle = [row.property_type_name, location].filter(Boolean).join(' - ');
          const canAdd = !alreadySelected && state.selectedIds.length < HOME_FEATURED_MAX_PROPERTIES;

          return `
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)]">
              <div class="min-w-0">
                <p class="text-sm font-medium text-[var(--c-text)] truncate">${esc(row.title)}</p>
                <p class="text-xs text-[var(--c-muted)] truncate">${esc(subtitle || `ID ${row.id}`)}</p>
              </div>
              <button
                type="button"
                data-home-featured-prop-add="${row.id}"
                class="px-3 py-1.5 rounded-lg text-xs ${canAdd ? 'bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95' : 'bg-[var(--c-surface)] text-[var(--c-muted)] cursor-not-allowed'}"
                ${canAdd ? '' : 'disabled'}
              >
                ${alreadySelected ? 'Agregada' : 'Agregar'}
              </button>
            </div>
          `;
        }).join('');

        $$('[data-home-featured-prop-add]', resultsContainer).forEach(btn => {
          btn.addEventListener('click', () => {
            const id = Number(btn.dataset.homeFeaturedPropAdd);
            if (!Number.isFinite(id) || id <= 0) return;
            if (state.selectedIds.includes(id)) return;

            if (state.selectedIds.length >= HOME_FEATURED_MAX_PROPERTIES) {
              emitClientMessage(false, `Solo puedes seleccionar ${HOME_FEATURED_MAX_PROPERTIES} propiedades.`);
              return;
            }

            state.selectedIds.push(id);
            const row = state.searchRows.find(item => item.id === id);
            if (row) state.selectedMap.set(id, row);
            syncHiddenInput();
            renderSelected();
            renderSearchResults(state.searchRows);
          });
        });
      };

      const hydrateSelectedIds = async () => {
        if (!state.selectedIds.length) return;

        await Promise.all(state.selectedIds.map(async (id) => {
          const property = await fetchPropertySummaryById(id);
          if (property) state.selectedMap.set(id, property);
        }));

        renderSelected();
      };

      const runSearch = async () => {
        const term = searchInput.value || '';
        resultsContainer.classList.remove('hidden');
        resultsContainer.innerHTML = `<p class="text-xs text-[var(--c-muted)]">Buscando...</p>`;

        try {
          const rows = await searchPublishedProperties(term);
          rows.forEach(row => heroPropertyCache.set(row.id, row));
          renderSearchResults(rows);
        } catch (_error) {
          resultsContainer.innerHTML = `<p class="text-xs text-red-600">No se pudo cargar la lista de propiedades.</p>`;
        }
      };

      searchBtn.addEventListener('click', runSearch);
      searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        runSearch();
      });
      searchInput.addEventListener('input', () => {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(runSearch, 350);
      });

      syncHiddenInput();
      renderSelected();
      hydrateSelectedIds();
      runSearch();
    });
  }

  async function saveAll() {
    const settings = {};

    // Campos de texto/textarea
    $$('input[data-setting-key], textarea[data-setting-key], select[data-setting-key]').forEach(el => {
      const key = el.dataset.settingKey;
      const lang = el.dataset.lang;
      if (!settings[key]) settings[key] = {};
      const value = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
      settings[key][`value_${lang}`] = value;
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
