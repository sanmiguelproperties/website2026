<div id="json-response-modal" class="fixed inset-0 z-[12000] hidden" aria-modal="true" role="dialog" aria-labelledby="json-response-title">
  <div data-js="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-16 w-[95%] max-w-xl">
    <div class="rounded-2xl overflow-hidden border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-[var(--c-border)]">
        <div id="json-response-icon" class="shrink-0 w-9 h-9 rounded-full grid place-items-center">
          <!-- icon set by JS -->
        </div>
        <div class="flex-1">
          <h3 id="json-response-title" class="text-lg font-semibold">Respuesta</h3>
          <div class="mt-0.5">
            <span id="json-response-code" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-[var(--c-elev)] text-[var(--c-text)]">CODE</span>
          </div>
        </div>
        <button type="button" data-js="btn-close" class="p-2 rounded-xl hover:bg-[var(--c-elev)] transition" aria-label="Cerrar">
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
        </button>
      </div>

      <div class="px-5 py-4 space-y-3">
        <p id="json-response-message" class="text-sm"></p>

        <div id="json-response-errors" class="hidden">
          <p class="text-sm font-medium">Errores</p>
          <div id="json-response-errors-list" class="mt-1 text-sm rounded border border-red-200 dark:border-red-900/40 bg-red-50/60 dark:bg-red-900/20 p-3 text-red-800 dark:text-red-200"></div>
        </div>

        <details class="rounded border border-[var(--c-border)] bg-[var(--c-elev)]">
          <summary class="cursor-pointer px-3 py-2 text-sm hover:bg-[var(--c-surface)] transition">Ver detalles (JSON)</summary>
          <pre id="json-response-json" class="overflow-x-auto text-xs p-3 max-h-[400px] bg-[var(--c-surface)] border-t border-[var(--c-border)]"></pre>
        </details>
      </div>

      <div class="px-5 py-4 border-t border-[var(--c-border)] flex items-center justify-end gap-2">
        <button type="button" data-js="btn-copy" class="rounded-lg border border-[var(--c-border)] px-4 py-2 hover:bg-[var(--c-elev)] transition text-sm">Copiar JSON</button>
        <button type="button" data-js="btn-ok" class="rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] px-4 py-2 hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--c-primary)] text-sm">Entendido</button>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const modal = document.getElementById('json-response-modal');
  if (!modal) return;
  const overlay = modal.querySelector('[data-js="overlay"]');
  const btnClose = modal.querySelector('[data-js="btn-close"]');
  const btnOk = modal.querySelector('[data-js="btn-ok"]');
  const btnCopy = modal.querySelector('[data-js="btn-copy"]');

  const elIcon = document.getElementById('json-response-icon');
  const elTitle = document.getElementById('json-response-title');
  const elCode = document.getElementById('json-response-code');
  const elMsg = document.getElementById('json-response-message');
  const elErrorsWrap = document.getElementById('json-response-errors');
  const elErrorsList = document.getElementById('json-response-errors-list');
  const elJson = document.getElementById('json-response-json');

  const CLASSES = {
    success: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-100',
    error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-100',
  };

  const ICONS = {
    success: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L8.75 11.586l6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>',
    error: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-5.25a.75.75 0 011.5 0 .75.75 0 01-1.5 0zM10 6a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 6z" clip-rule="evenodd"/></svg>',
  };

  function show(payload) {
    const {
      success = false,
      message = 'Respuesta recibida',
      code = null,
      data = null,
      errors = null,
      status = null,
      raw = null,
    } = payload || {};

    // Icon + header styles
    elIcon.className = 'shrink-0 w-9 h-9 rounded-full grid place-items-center ' + (success ? CLASSES.success : CLASSES.error);
    elIcon.innerHTML = success ? ICONS.success : ICONS.error;
    elTitle.textContent = success ? 'Operación exitosa' : 'Ocurrió un problema';

    // Code badge
    if (code || status !== null) {
      elCode.textContent = [code, status !== null ? `HTTP_${status}` : null].filter(Boolean).join(' • ');
      elCode.parentElement.classList.remove('hidden');
    } else {
      elCode.textContent = '';
      elCode.parentElement.classList.add('hidden');
    }

    // Message
    elMsg.textContent = message || (success ? 'Operación realizada correctamente.' : 'Ha ocurrido un error.');

    // Errors
    if (errors && typeof errors === 'object') {
      const parts = [];
      Object.entries(errors).forEach(([field, arr]) => {
        const msgs = Array.isArray(arr) ? arr : [String(arr)];
        parts.push(`<div class="mb-1"><span class="font-semibold">${field}:</span> ${msgs.join(', ')}</div>`);
      });
      elErrorsList.innerHTML = parts.join('') || '—';
      elErrorsWrap.classList.remove('hidden');
    } else {
      elErrorsList.innerHTML = '';
      elErrorsWrap.classList.add('hidden');
    }

    // JSON pretty
    const jsonPretty = JSON.stringify(raw ?? { success, message, code, data, errors, status }, null, 2);
    elJson.textContent = jsonPretty;

    modal.classList.remove('hidden');
  }

  function hide() {
    modal.classList.add('hidden');
  }

  overlay.addEventListener('click', hide);
  btnClose.addEventListener('click', hide);
  btnOk.addEventListener('click', hide);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) hide();
  });

  btnCopy.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(document.getElementById('json-response-json').textContent);
      btnCopy.textContent = 'Copiado';
      setTimeout(() => (btnCopy.textContent = 'Copiar JSON'), 1200);
    } catch (_e) {
      // noop
    }
  });

  // Escucha global para todas las vistas que deseen dispararlo
  window.addEventListener('api:response', (e) => show(e.detail || {}));
})();
</script>