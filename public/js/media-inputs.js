(() => {
  // Extensión ligera para que los <x-media-input> muestren preview
  // automáticamente cuando cambia el value (incluye valores iniciales).
  //
  // - Se apoya en el API /api/media/{id}
  // - Respeta el estándar de popup JSON usando el evento `api:response`
  // - Activa `window.MediaInputsExtActive` para que `media-picker.js`
  //   no duplique previews.

  window.MediaInputsExtActive = true;

  const $ = (sel, el = document) => el.querySelector(sel);

  const token = $('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf = $('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const API = '/api/media';

  const headers = (isMutation = false) => {
    const h = { Accept: 'application/json' };
    if (token) h['Authorization'] = `Bearer ${token}`;
    if (isMutation && csrf) h['X-CSRF-TOKEN'] = csrf;
    return h;
  };

  async function apiGet(url) {
    const res = await fetch(url, { headers: headers(false) });

    let json = null;
    try {
      json = await res.clone().json();
    } catch (_e) {}

    if (!res.ok) {
      const detail = {
        success: false,
        message: json?.message || res.statusText || 'Error de API',
        code: json?.code || (res.status === 404 ? 'NOT_FOUND' : 'SERVER_ERROR'),
        errors: json?.errors || null,
        status: res.status,
        raw: json,
      };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    return json;
  }

  const parseIds = (input) => {
    const raw = (input?.value || '').trim();
    if (!raw) return [];
    return raw
      .split(',')
      .map((s) => s.trim())
      .filter(Boolean)
      .map((s) => parseInt(s, 10))
      .filter((n) => Number.isFinite(n) && n > 0);
  };

  const thumbFor = (it) => {
    const url = it?.url || '';
    const type = it?.type;
    if (type === 'image' && url) {
      return `<img src="${url}" alt="" class="max-h-full max-w-full object-contain">`;
    }
    if (type === 'video') return `<div class="text-center text-xs"><div class="text-2xl">🎬</div><div class="text-[var(--c-muted)]">${it?.provider || 'video'}</div></div>`;
    if (type === 'audio') return `<div class="text-2xl">🎵</div>`;
    return `<div class="text-2xl">📄</div>`;
  };

  const renderPreview = async (input) => {
    const previewSelector = input.getAttribute('data-fp-preview') || '';
    if (!previewSelector) return;
    const previewEl = $(previewSelector);
    if (!previewEl) return;

    const ids = parseIds(input);
    if (!ids.length) {
      previewEl.className = (previewEl.className || '').replace(/\bgrid\b/g, '');
      previewEl.innerHTML = `<div class="text-sm text-[var(--c-muted)]">Sin selección</div>`;
      return;
    }

    const columns = parseInt(input.getAttribute('data-fp-columns') || '8', 10);
    const col = Number.isFinite(columns) ? Math.max(2, Math.min(12, columns)) : 8;

    previewEl.className = `grid grid-cols-${col} gap-2`;
    previewEl.innerHTML = ids
      .slice(0, 48)
      .map(
        () =>
          `<div class="rounded-lg overflow-hidden border border-[var(--c-border)]"><div class="w-full h-14 sm:h-16 md:h-20 bg-[var(--c-elev)]"></div></div>`
      )
      .join('');

    const items = (await Promise.all(
      ids.slice(0, 48).map((id) => apiGet(`${API}/${id}`).then((p) => p?.data ?? null).catch(() => null))
    )).filter(Boolean);

    previewEl.innerHTML = items
      .map(
        (it) => `
          <div class="rounded-lg overflow-hidden border border-[var(--c-border)]">
            <div class="w-full h-14 sm:h-16 md:h-20 bg-[var(--c-elev)] flex items-center justify-center overflow-hidden">
              ${thumbFor(it)}
            </div>
          </div>
        `
      )
      .join('');
  };

  const bindOne = (input) => {
    if (input.dataset.fpPreviewBound === '1') return;
    input.dataset.fpPreviewBound = '1';

    // Render inicial
    renderPreview(input);

    // Render al cambiar
    input.addEventListener('change', () => renderPreview(input));
  };

  const bindAll = () => {
    document.querySelectorAll('input[data-filepicker][data-fp-preview]').forEach(bindOne);
  };

  // Listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindAll);
  } else {
    bindAll();
  }

  // Si el DOM cambia (drawer/modals), vuelve a intentar bindear
  const mo = new MutationObserver(() => bindAll());
  mo.observe(document.documentElement, { subtree: true, childList: true });
})();

