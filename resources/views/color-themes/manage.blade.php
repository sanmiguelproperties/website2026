@extends('layouts.app')

@section('title', 'Administrar Temas de Color')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Temas de Color</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona los temas de colores del sistema</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-theme" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nuevo Tema
      </button>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-[var(--c-text)]">Temas de Color del Sistema</h2>
      <div class="flex items-center gap-2">
        <input type="text" id="search-themes" placeholder="Buscar temas..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
        <button id="btn-refresh-themes" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Themes List -->
    <div id="themes-list" class="space-y-3">
      <!-- Themes will be loaded here -->
    </div>

    <!-- Pagination -->
    <div id="themes-pagination" class="flex justify-between items-center mt-6">
      <!-- Pagination will be loaded here -->
    </div>
  </div>
</div>

<!-- Create/Edit Theme Modal -->
<div id="theme-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="theme-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Tema</h3>
      </div>
      <form id="theme-form" class="p-6">
        <input type="hidden" id="theme-id" name="id">

        <!-- Name -->
        <div class="mb-4">
          <label for="theme-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre</label>
          <input type="text" id="theme-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>

        <!-- Description -->
        <div class="mb-6">
          <label for="theme-description" class="block text-sm font-medium text-[var(--c-text)] mb-1">Descripción</label>
          <textarea id="theme-description" name="description" rows="3" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" placeholder="Descripción opcional del tema"></textarea>
        </div>

        <!-- Colors -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-[var(--c-text)] mb-3">Colores del Tema</label>

          <!-- Helper -->
          <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-3 text-xs text-[var(--c-muted)] mb-4">
            Usa el selector para elegir colores. Se guardan como <code>oklch(L C H)</code> automáticamente.
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="color-fields">
            @php
              $colorLabels = [
                'bg' => 'Fondo (bg)',
                'surface' => 'Superficie (surface)',
                'elev' => 'Elevación (elev)',
                'text' => 'Texto (text)',
                'muted' => 'Texto secundario (muted)',
                'border' => 'Bordes (border)',
                'primary' => 'Primario (primary)',
                'primary-ink' => 'Texto primario (primary-ink)',
                'accent' => 'Acento (accent)',
                'danger' => 'Peligro (danger)',
              ];
            @endphp

            @foreach ($colorLabels as $key => $label)
              <div class="space-y-1">
                <label for="color-{{ $key }}" class="block text-xs font-medium text-[var(--c-muted)]">{{ $label }}</label>
                <div class="flex items-center gap-2">
                  <!-- Color picker (hex) -->
                  <input
                    type="color"
                    class="color-picker h-10 w-12 p-1 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)]"
                    data-target="color-{{ $key }}"
                    aria-label="Selector de color {{ $label }}"
                  >
                  <!-- OKLCH text (real value sent to backend) -->
                  <input
                    type="text"
                    id="color-{{ $key }}"
                    name="colors[{{ $key }}]"
                    class="flex-1 px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent oklch-input"
                    placeholder="oklch(0.97 0.008 120)"
                    required
                  >
                </div>
                <p class="text-[10px] text-[var(--c-muted)]">Formato requerido: <code>oklch(L C H)</code></p>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-theme" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;

  // Verificar token antes de cargar datos
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido. Por favor, inicia sesión nuevamente.');
    return;
  }

  // ---------- OKLCH <-> sRGB helpers ----------
  // Numerics
  const clamp = (x, min, max) => Math.min(Math.max(x, min), max);
  const deg2rad = d => d * Math.PI / 180;
  const rad2deg = r => r * 180 / Math.PI;
  const srgbToLinear = v => (v <= 0.04045) ? v/12.92 : Math.pow((v + 0.055)/1.055, 2.4);
  const linearToSrgb = v => (v <= 0.0031308) ? 12.92*v : 1.055*Math.pow(v, 1/2.4) - 0.055;

  // Hex <-> sRGB
  function hexToRgb(hex) {
    const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!m) return {r:0,g:0,b:0};
    return { r: parseInt(m[1],16)/255, g: parseInt(m[2],16)/255, b: parseInt(m[3],16)/255 };
  }
  function rgbToHex(r, g, b) {
    const toHex = v => {
      const n = clamp(Math.round(v*255), 0, 255).toString(16).padStart(2,'0');
      return n;
    };
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
  }

  // sRGB (gamma) -> OKLCH
  function srgbToOklch(hex) {
    const {r, g, b} = hexToRgb(hex);
    // to linear
    const rl = srgbToLinear(r);
    const gl = srgbToLinear(g);
    const bl = srgbToLinear(b);
    // linear sRGB -> LMS -> OKLab
    const l_ = 0.4122214708*rl + 0.5363325363*gl + 0.0514459929*bl;
    const m_ = 0.2119034982*rl + 0.6806995451*gl + 0.1073969566*bl;
    const s_ = 0.0883024619*rl + 0.2817188376*gl + 0.6299787005*bl;

    const l = Math.cbrt(l_);
    const m = Math.cbrt(m_);
    const s = Math.cbrt(s_);

    const L = 0.2104542553*l + 0.7936177850*m - 0.0040720468*s;
    const a = 1.9779984951*l - 2.4285922050*m + 0.4505937099*s;
    const b2 = 0.0259040371*l + 0.7827717662*m - 0.8086757660*s;

    const C = Math.sqrt(a*a + b2*b2);
    let H = Math.atan2(b2, a); // radians
    H = (rad2deg(H) + 360) % 360;

    return { L: clamp(L, 0, 1), C: Math.max(0, C), H };
  }

  // OKLCH -> sRGB (gamma) (with gamut clipping by sRGB clamp)
  function oklchToSrgbHex(L, C, H) {
    // OKLCH -> OKLab
    const a = C * Math.cos(deg2rad(H));
    const b = C * Math.sin(deg2rad(H));

    // OKLab -> LMS
    const l = L + 0.3963377774*a + 0.2158037573*b;
    const m = L - 0.1055613458*a - 0.0638541728*b;
    const s = L - 0.0894841775*a - 1.2914855480*b;

    const l3 = l*l*l;
    const m3 = m*m*m;
    const s3 = s*s*s;

    // LMS -> linear sRGB
    let rl = +4.0767416621*l3 - 3.3077115913*m3 + 0.2309699292*s3;
    let gl = -1.2684380046*l3 + 2.6097574011*m3 - 0.3413193965*s3;
    let bl = -0.0041960863*l3 - 0.7034186147*m3 + 1.7076147010*s3;

    // Clamp linear, then encode
    rl = clamp(rl, 0, 1);
    gl = clamp(gl, 0, 1);
    bl = clamp(bl, 0, 1);

    const r = clamp(linearToSrgb(rl), 0, 1);
    const g = clamp(linearToSrgb(gl), 0, 1);
    const b2 = clamp(linearToSrgb(bl), 0, 1);

    return rgbToHex(r, g, b2);
  }

  // Parse & format OKLCH
  function parseOKLCH(str) {
    const m = /^oklch\(\s*([0-9]*\.?[0-9]+)\s+([0-9]*\.?[0-9]+)\s+([0-9]*\.?[0-9]+)(?:\s*\/\s*([0-9]*\.?[0-9]+))?\s*\)$/i.exec(String(str).trim());
    if (!m) return null;
    const L = parseFloat(m[1]);
    const C = parseFloat(m[2]);
    const H = parseFloat(m[3]);
    return { L, C, H };
  }
  function formatOKLCH(L, C, H) {
    const Lf = (Math.round(L*1000)/1000).toFixed(3);
    const Cf = (Math.round(C*1000)/1000).toFixed(3);
    const Hf = Math.round(H); // entero como en los seeders
    return `oklch(${Lf} ${Cf} ${Hf})`;
  }

  // Sincroniza picker <-> input (oklch)
  function syncPickerFromInput(inputEl, pickerEl) {
    const val = inputEl.value;
    const parsed = parseOKLCH(val);
    // Si es OKLCH válido, convierto a hex y seteo el picker
    if (parsed) {
      const hex = oklchToSrgbHex(parsed.L, parsed.C, parsed.H);
      pickerEl.value = hex;
      return;
    }
    // Si alguien puso un hex (#...), sincronizo inverso
    if (/^#([0-9a-f]{6})$/i.test(val)) {
      const {L, C, H} = srgbToOklch(val);
      inputEl.value = formatOKLCH(L, C, H);
      pickerEl.value = val;
      return;
    }
    // Valor desconocido -> usa un gris por defecto
    pickerEl.value = '#888888';
  }

  function syncInputFromPicker(pickerEl, inputEl) {
    const hex = pickerEl.value;
    const {L, C, H} = srgbToOklch(hex);
    inputEl.value = formatOKLCH(L, C, H);
  }

  // Inicializa pickers para todos los campos de color
  function attachColorPickers(scope = document) {
    const pairs = [];
    scope.querySelectorAll('.color-picker').forEach(picker => {
      const id = picker.getAttribute('data-target');
      const input = scope.getElementById(id);
      if (!input) return;
      // Sync inicial (si viene de editar)
      syncPickerFromInput(input, picker);

      // Cuando cambia el picker, actualizo el input OKLCH
      picker.addEventListener('input', () => syncInputFromPicker(picker, input));
      // Cuando el usuario edita el input manualmente, normalizo al salir de foco
      input.addEventListener('blur', () => {
        // Si puso hex, lo paso a OKLCH
        const raw = input.value.trim();
        if (/^#([0-9a-f]{6})$/i.test(raw)) {
          const {L, C, H} = srgbToOklch(raw);
          input.value = formatOKLCH(L, C, H);
        }
        // Re-sincronizo el picker según lo que haya quedado
        syncPickerFromInput(input, picker);
      });

      pairs.push([picker, input]);
    });
    return pairs;
  }

  // Normaliza todos los colores antes de enviar al backend
  function normalizeAllColorsBeforeSubmit(form) {
    form.querySelectorAll('input[name^="colors["]').forEach(input => {
      const parsed = parseOKLCH(input.value);
      if (parsed) {
        input.value = formatOKLCH(parsed.L, parsed.C, parsed.H);
      } else if (/^#([0-9a-f]{6})$/i.test(input.value.trim())) {
        const {L, C, H} = srgbToOklch(input.value.trim());
        input.value = formatOKLCH(L, C, H);
      } else {
        // Si no es válido, intento forzar un valor seguro (gris medio)
        const {L, C, H} = srgbToOklch('#888888');
        input.value = formatOKLCH(L, C, H);
      }
    });
  }
  // ---------- FIN helpers OKLCH ----------

  // Load initial data
  loadThemes();

  // Event listeners
  document.getElementById('btn-create-theme').addEventListener('click', () => openThemeModal());
  document.getElementById('btn-refresh-themes').addEventListener('click', loadThemes);
  document.getElementById('search-themes').addEventListener('input', debounce(loadThemes, 300));

  // Form submissions
  document.getElementById('theme-form').addEventListener('submit', saveTheme);

  // Modal close buttons
  document.getElementById('btn-cancel-theme').addEventListener('click', () => closeThemeModal());

  // Functions
  async function loadThemes(page = 1) {
    const search = document.getElementById('search-themes').value;
    const url = `${API_BASE}/color-themes?page=${page}&per_page=15&search=${encodeURIComponent(search)}`;

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        renderThemes(data.data);
        renderPagination(data.data);
      } else {
        showApiError('Error al cargar temas', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar los temas. Verifica tu conexión a internet.');
    }
  }

  function renderThemes(themesData) {
    const container = document.getElementById('themes-list');
    container.innerHTML = '';

    if (themesData.data.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron temas</p>';
      return;
    }

    themesData.data.forEach(theme => {
      const themeEl = document.createElement('div');
      themeEl.className = 'flex items-center justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';

      // Color preview
      const colorPreview = Object.values(theme.colors).slice(0, 5).map(color =>
        `<div class="w-4 h-4 rounded-full border border-[var(--c-border)]" style="background-color: ${color.replace(/oklch\(([^)]+)\)/, 'oklch($1 / 1)')}"></div>`
      ).join('');

      themeEl.innerHTML = `
        <div class="flex items-center gap-4">
          <div class="flex gap-1">
            ${colorPreview}
          </div>
          <div>
            <h3 class="font-medium text-[var(--c-text)]">${theme.name}</h3>
            <p class="text-sm text-[var(--c-muted)]">${theme.description || 'Sin descripción'}</p>
            ${theme.is_active ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">Activo</span>' : ''}
            ${theme.is_default ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">Por defecto</span>' : ''}
          </div>
        </div>
        <div class="flex gap-2">
          <button class="activate-theme-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition ${theme.is_active ? 'opacity-50 cursor-not-allowed' : ''}" data-id="${theme.id}" ${theme.is_active ? 'disabled' : ''}>Activar</button>
          <button class="edit-theme-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${theme.id}">Editar</button>
          <button class="delete-theme-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition ${theme.is_default ? 'opacity-50 cursor-not-allowed' : ''}" data-id="${theme.id}" ${theme.is_default ? 'disabled' : ''}>Eliminar</button>
        </div>
      `;
      container.appendChild(themeEl);
    });

    // Add event listeners
    container.querySelectorAll('.activate-theme-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        activateTheme(id);
      });
    });

    container.querySelectorAll('.edit-theme-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        editTheme(id);
      });
    });

    container.querySelectorAll('.delete-theme-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        deleteTheme(id);
      });
    });
  }

  function renderPagination(themesData) {
    const container = document.getElementById('themes-pagination');
    container.innerHTML = '';

    if (themesData.last_page <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Anterior';
    prevBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    prevBtn.disabled = !themesData.prev_page_url;
    prevBtn.addEventListener('click', () => loadThemes(themesData.current_page - 1));

    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Siguiente';
    nextBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    nextBtn.disabled = !themesData.next_page_url;
    nextBtn.addEventListener('click', () => loadThemes(themesData.current_page + 1));

    const pageInfo = document.createElement('div');
    pageInfo.textContent = `Página ${themesData.current_page} de ${themesData.last_page}`;
    pageInfo.className = 'text-sm text-[var(--c-muted)]';

    container.appendChild(prevBtn);
    container.appendChild(pageInfo);
    container.appendChild(nextBtn);
  }

  function openThemeModal(theme = null) {
    const modal = document.getElementById('theme-modal');
    const title = document.getElementById('theme-modal-title');
    const idField = document.getElementById('theme-id');
    const nameField = document.getElementById('theme-name');
    const descriptionField = document.getElementById('theme-description');

    // Limpiar campos
    document.querySelectorAll('#color-fields .oklch-input').forEach(i => i.value = '');
    const pickerPairs = attachColorPickers(document); // asegura que existen listeners

    if (theme) {
      title.textContent = 'Editar Tema';
      idField.value = theme.id;
      nameField.value = theme.name;
      descriptionField.value = theme.description || '';

      // Set color values + sincronizar pickers
      Object.keys(theme.colors).forEach(key => {
        const input = document.getElementById(`color-${key}`);
        if (input) {
          input.value = theme.colors[key];
          const picker = document.querySelector(`.color-picker[data-target="color-${key}"]`);
          if (picker) syncPickerFromInput(input, picker);
        }
      });
    } else {
      title.textContent = 'Crear Tema';
      idField.value = '';
      nameField.value = '';
      descriptionField.value = '';

      // Valores iniciales suaves para pickers (no se envían hasta que se guarden)
      document.querySelectorAll('.color-picker').forEach(p => p.value = '#888888');
    }

    modal.classList.remove('hidden');
  }

  function closeThemeModal() {
    document.getElementById('theme-modal').classList.add('hidden');
  }

  async function saveTheme(e) {
    e.preventDefault();

    const id = document.getElementById('theme-id').value;
    const name = document.getElementById('theme-name').value;
    const description = document.getElementById('theme-description').value;

    // Normaliza y colecta colores
    normalizeAllColorsBeforeSubmit(document.getElementById('theme-form'));
    const colors = {};
    document.querySelectorAll('input[name^="colors["]').forEach(input => {
      const key = input.name.match(/colors\[(.+)\]/)[1];
      colors[key] = input.value;
    });

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/color-themes/${id}` : `${API_BASE}/color-themes`;

    const formData = { name, description, colors };

    try {
      const response = await fetch(url, {
        method: method,
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closeThemeModal();
        loadThemes();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al guardar tema', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo guardar el tema. Verifica tu conexión a internet.');
    }
  }

  async function activateTheme(id) {
    try {
      const response = await fetch(`${API_BASE}/color-themes/${id}/activate`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadThemes();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        // Reload page to apply new theme
        setTimeout(() => window.location.reload(), 1000);
      } else {
        showApiError('Error al activar tema', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo activar el tema. Verifica tu conexión a internet.');
    }
  }

  async function editTheme(id) {
    try {
      const response = await fetch(`${API_BASE}/color-themes/${id}`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        openThemeModal(data.data);
      } else {
        showApiError('Error al cargar tema', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo cargar el tema. Verifica tu conexión a internet.');
    }
  }

  async function deleteTheme(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este tema?')) return;

    try {
      const response = await fetch(`${API_BASE}/color-themes/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadThemes();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al eliminar tema', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo eliminar el tema. Verifica tu conexión a internet.');
    }
  }

  function showError(title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: 'CLIENT_ERROR',
        errors: { general: [message] }
      }
    }));
  }

  function showApiError(title, apiResponse) {
    console.error('API Error:', apiResponse);

    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: apiResponse.message || 'Error desconocido',
        code: apiResponse.code || 'UNKNOWN_ERROR',
        errors: apiResponse.errors || null,
        status: apiResponse.status || null,
        raw: apiResponse
      }
    }));
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Inicializa pickers para el formulario desde el inicio (por si abres "Crear")
  attachColorPickers(document);
});
</script>
@endsection