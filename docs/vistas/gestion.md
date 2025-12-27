# Vistas: Gestión del Sistema

**Directorios:** `resources/views/{color-themes, currencies, rbac, users}/`
**Propósito:** Interfaces de administración para entidades del sistema

## Descripción General

Las vistas de gestión proporcionan interfaces completas CRUD (Crear, Leer, Actualizar, Eliminar) para las entidades principales del sistema: usuarios, monedas, temas de color y roles/permisos. Todas siguen un patrón consistente con:

- Listado con paginación y búsqueda
- Modales para crear/editar
- API-first approach con JavaScript
- Diseño responsivo con temas dinámicos

## Patrón Común de las Vistas de Gestión

### Estructura Base

```blade
@extends('layouts.app')

@section('title', 'Administrar [Entidad]')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar [Entidad]</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona las [entidades] del sistema</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-[entity]" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        Nuevo [Entidad]
      </button>
    </div>
  </div>

  <!-- Contenedor principal -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6">
    <!-- Header con búsqueda -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-[var(--c-text)]">[Entidades] del Sistema</h2>
      <div class="flex items-center gap-2">
        <input type="text" id="search-[entities]" placeholder="Buscar [entidades]..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
        <button id="btn-refresh-[entities]" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
          <!-- Icono refresh -->
        </button>
      </div>
    </div>

    <!-- Lista -->
    <div id="[entities]-list" class="space-y-3">
      <!-- Items cargados dinámicamente -->
    </div>

    <!-- Paginación -->
    <div id="[entities]-pagination" class="flex justify-between items-center mt-6">
      <!-- Paginación cargada dinámicamente -->
    </div>
  </div>
</div>

<!-- Modal crear/editar -->
<div id="[entity]-modal" class="fixed inset-0 z-50 hidden">
  <!-- Modal content -->
</div>

<script>
  // JavaScript para gestión
</script>
@endsection
```

### Patrón JavaScript Común

```javascript
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api/[entities]';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;

  // Verificar token
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró token de acceso');
    return;
  }

  // Load initial data
  load[Entities]();

  // Event listeners
  document.getElementById('btn-create-[entity]').addEventListener('click', () => open[Entity]Modal());
  document.getElementById('btn-refresh-[entities]').addEventListener('click', load[Entities]);
  document.getElementById('search-[entities]').addEventListener('input', debounce(load[Entities], 300));

  // Form submissions
  document.getElementById('[entity]-form').addEventListener('submit', save[Entity]);

  // Modal close
  document.getElementById('btn-cancel-[entity]').addEventListener('click', () => close[Entity]Modal());

  // Functions
  async function load[Entities](page = 1) {
    const search = document.getElementById('search-[entities]').value;
    const url = `${API_BASE}?page=${page}&per_page=15&search=${encodeURIComponent(search)}`;

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
        render[Entities](data.data);
        renderPagination(data.data);
      } else {
        showApiError('Error al cargar [entidades]', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar las [entidades]');
    }
  }

  function render[Entities]([entities]Data) {
    const container = document.getElementById('[entities]-list');
    container.innerHTML = '';

    if ([entities]Data.data.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron [entidades]</p>';
      return;
    }

    [entities]Data.data.forEach([entity] => {
      const [entity]El = document.createElement('div');
      [entity]El.className = 'flex items-center justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';

      [entity]El.innerHTML = `
        <div class="flex items-center gap-4">
          <!-- Avatar/Icon -->
          <div>
            <h3 class="font-medium text-[var(--c-text)]">${[entity].name}</h3>
            <p class="text-sm text-[var(--c-muted)]">[additional info]</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button class="edit-[entity]-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${[entity].id}">Editar</button>
          <button class="delete-[entity]-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${[entity].id}">Eliminar</button>
        </div>
      `;
      container.appendChild([entity]El);
    });

    // Add event listeners
    container.querySelectorAll('.edit-[entity]-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        edit[Entity](id);
      });
    });

    container.querySelectorAll('.delete-[entity]-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        delete[Entity](id);
      });
    });
  }

  // Más funciones: renderPagination, open[Entity]Modal, save[Entity], delete[Entity], etc.
});
```

## Vista: Gestión de Usuarios

**Archivo:** `resources/views/users/manage.blade.php`
**API Base:** `/api/users`

### Funcionalidades Específicas

#### Campos del Usuario
- **Imagen de perfil**: Componente `x-media-input` con preview
- **Nombre**: Campo de texto requerido
- **Email**: Campo email requerido
- **Contraseña**: Solo en creación (opcional en edición)

#### Avatar en Lista
```javascript
let profileImageHtml = '';
if (user.profile_image) {
  profileImageHtml = `<img src="${user.profile_image.url}" alt="${user.profile_image.alt || user.name}" class="w-10 h-10 rounded-full object-cover">`;
} else {
  profileImageHtml = `<div class="w-10 h-10 rounded-full bg-[var(--c-primary)] flex items-center justify-center text-white font-bold text-lg">${user.name.charAt(0).toUpperCase()}</div>`;
}
```

#### Modal de Creación/Edición
```blade
<form id="user-form" class="p-6">
  <input type="hidden" id="user-id" name="id">

  <!-- Profile Image -->
  <div>
    <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Imagen de Perfil</label>
    <x-media-input name="profile_image_id" mode="single" :max="1" placeholder="Seleccionar imagen de perfil" button="Seleccionar Imagen" preview="true" value="{{ old('profile_image_id', $user->profile_image_id ?? '') }}" />
  </div>

  <!-- Name -->
  <div>
    <label for="user-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre</label>
    <input type="text" id="user-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
  </div>

  <!-- Email -->
  <div>
    <label for="user-email" class="block text-sm font-medium text-[var(--c-text)] mb-1">Correo Electrónico</label>
    <input type="email" id="user-email" name="email" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
  </div>

  <!-- Password -->
  <div>
    <label for="user-password" class="block text-sm font-medium text-[var(--c-text)] mb-1">Contraseña</label>
    <input type="password" id="user-password" name="password" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
    <p class="text-xs text-[var(--c-muted)] mt-1">Mínimo 8 caracteres</p>
  </div>

  <!-- Password Confirmation (create only) -->
  <div id="password-confirm-container" style="display: none;">
    <label for="user-password-confirm" class="block text-sm font-medium text-[var(--c-text)] mb-1">Confirmar Contraseña</label>
    <input type="password" id="user-password-confirm" name="password_confirmation" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent">
  </div>

  <!-- Actions -->
  <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
    <button type="button" id="btn-cancel-user" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
    <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
  </div>
</form>
```

## Vista: Gestión de Monedas

**Archivo:** `resources/views/currencies/manage.blade.php`
**API Base:** `/api/currencies`

### Funcionalidades Específicas

#### Campos de Moneda
- **Nombre**: Nombre completo de la moneda
- **Código**: Código de 3 letras (USD, EUR, PEN)
- **Símbolo**: Símbolo gráfico ($ , €, S/)
- **Tipo de cambio**: Valor relativo a moneda base
- **Moneda base**: Checkbox para marcar como base

#### Validaciones
```javascript
// Limitar decimales en tipo de cambio
function limitDecimalPlaces(input, maxDecimals) {
  const value = input.value;
  const parts = value.split('.');

  if (parts.length > 1) {
    const decimalPart = parts[1];
    if (decimalPart.length > maxDecimals) {
      input.value = parts[0] + '.' + decimalPart.substring(0, maxDecimals);
    }
  }
}
```

#### Icono en Lista
```javascript
<div class="w-10 h-10 rounded-full bg-[var(--c-primary)] flex items-center justify-center text-white font-bold text-lg">
  ${currency.symbol}
</div>
```

## Vista: Gestión de Temas de Color

**Archivo:** `resources/views/color-themes/manage.blade.php`
**API Base:** `/api/color-themes`

### Funcionalidades Específicas

#### Selector de Colores OKLCH
- **Color picker HTML5**: Para selección visual
- **Input OKLCH**: Para valores precisos
- **Conversión automática**: RGB ↔ OKLCH
- **Preview en tiempo real**: Aplicación inmediata

#### Funciones de Conversión
```javascript
// Hex -> OKLCH
function srgbToOklch(hex) {
  const {r, g, b} = hexToRgb(hex);
  // Conversión completa sRGB -> LMS -> OKLab -> OKLCH
  return { L, C, H };
}

// OKLCH -> Hex
function oklchToSrgbHex(L, C, H) {
  // Conversión completa OKLCH -> OKLab -> LMS -> linear sRGB -> sRGB
  return rgbToHex(r, g, b);
}
```

#### Campos de Color
```blade
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
      <!-- Color picker -->
      <input type="color" class="color-picker h-10 w-12 p-1 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)]" data-target="color-{{ $key }}" aria-label="Selector de color {{ $label }}">

      <!-- OKLCH input -->
      <input type="text" id="color-{{ $key }}" name="colors[{{ $key }}]" class="flex-1 px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent oklch-input" placeholder="oklch(0.97 0.008 120)" required>
    </div>
    <p class="text-[10px] text-[var(--c-muted)]">Formato requerido: <code>oklch(L C H)</code></p>
  </div>
@endforeach
```

#### Preview de Tema
```javascript
// Preview de colores en la lista
const colorPreview = Object.values(theme.colors).slice(0, 5).map(color =>
  `<div class="w-4 h-4 rounded-full border border-[var(--c-border)]" style="background-color: ${color.replace(/oklch\(([^)]+)\)/, 'oklch($1 / 1)')}"></div>`
).join('');
```

## Vista: Gestión RBAC (Roles y Permisos)

**Archivo:** `resources/views/rbac/manage.blade.php`
**API Base:** `/api/rbac`

### Funcionalidades Específicas

#### Tres Pestañas
1. **Roles**: Gestión de roles del sistema
2. **Permisos**: Gestión de permisos individuales
3. **Asignaciones**: Asignar permisos a roles

#### Pestañas con JavaScript
```javascript
const tabs = document.querySelectorAll('.tab-btn');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => {
      t.classList.remove('active', 'border-b-2', 'border-[var(--c-primary)]', 'text-[var(--c-primary)]');
      t.classList.add('text-[var(--c-muted)]');
    });
    contents.forEach(c => c.classList.add('hidden'));

    tab.classList.add('active', 'border-b-2', 'border-[var(--c-primary)]', 'text-[var(--c-primary)]');
    tab.classList.remove('text-[var(--c-muted)]');

    const target = tab.id.replace('tab-', '') + '-content';
    document.getElementById(target).classList.remove('hidden');
  });
});
```

#### Asignación de Permisos
```blade
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div>
    <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Seleccionar Rol</label>
    <select id="role-select" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg">
      <option value="">Selecciona un rol...</option>
    </select>
  </div>
  <div>
    <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Permisos Disponibles</label>
    <div id="permissions-checkboxes" class="w-full max-h-64 overflow-auto bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg p-3 space-y-2">
      <!-- Checkboxes cargados dinámicamente -->
    </div>
  </div>
</div>
```

#### Funciones de Asignación
```javascript
async function assignPermissions() {
  const roleId = document.getElementById('role-select').value;
  const checkboxes = document.querySelectorAll('#permissions-checkboxes input[type="checkbox"]:checked');
  const selectedPermissions = Array.from(checkboxes).map(checkbox => checkbox.value);

  // API call to assign permissions
}

async function syncPermissions() {
  // Replace all permissions for the role
}
```

## Funciones Compartidas

### Manejo de Errores
```javascript
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
```

### Debounce para Búsqueda
```javascript
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
```

### Paginación
```javascript
function renderPagination([entities]Data) {
  const container = document.getElementById('[entities]-pagination');
  container.innerHTML = '';

  if ([entities]Data.last_page <= 1) return;

  const prevBtn = document.createElement('button');
  prevBtn.textContent = 'Anterior';
  prevBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
  prevBtn.disabled = ![entities]Data.prev_page_url;
  prevBtn.addEventListener('click', () => load[Entities]([entities]Data.current_page - 1));

  const nextBtn = document.createElement('button');
  nextBtn.textContent = 'Siguiente';
  nextBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
  nextBtn.disabled = ![entities]Data.next_page_url;
  nextBtn.addEventListener('click', () => load[Entities]([entities]Data.current_page + 1));

  const pageInfo = document.createElement('div');
  pageInfo.textContent = `Página ${[entities]Data.current_page} de ${[entities]Data.last_page}`;
  pageInfo.className = 'text-sm text-[var(--c-muted)]';

  container.appendChild(prevBtn);
  container.appendChild(pageInfo);
  container.appendChild(nextBtn);
}
```

## Consideraciones Técnicas

### Rendimiento
- **Paginación**: 15 items por página
- **Debounce**: 300ms para búsqueda
- **Lazy loading**: Contenido cargado bajo demanda

### Seguridad
- **CSRF tokens**: Incluidos en todas las requests
- **API tokens**: Verificación de autenticación
- **Validación**: Tanto frontend como backend

### Accesibilidad
- **ARIA labels**: Para elementos interactivos
- **Keyboard navigation**: En listas y formularios
- **Screen readers**: Descripciones apropiadas

## Personalización

### Agregar Nuevos Campos
```blade
<!-- Nuevo campo en modal -->
<div>
  <label for="[entity]-[field]" class="block text-sm font-medium text-[var(--c-text)] mb-1">[Label]</label>
  <input type="[type]" id="[entity]-[field]" name="[field]" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" [required]>
</div>
```

### Modificar Validaciones
```javascript
// Agregar validación personalizada
function validate[Entity]Form(formData) {
  // Lógica de validación personalizada
  return { isValid: true, errors: {} };
}
```

## Testing

### Casos de Prueba
- **CRUD operations**: Crear, leer, actualizar, eliminar
- **Search**: Funcionalidad de búsqueda
- **Pagination**: Navegación entre páginas
- **Validation**: Manejo de errores
- **Responsive**: Diseño en móviles

## Próximas Mejoras

1. **Bulk operations**: Acciones masivas
2. **Export/Import**: Datos CSV/Excel
3. **Advanced filters**: Filtros complejos
4. **Audit logs**: Historial de cambios
5. **Soft delete**: Eliminación lógica
6. **Relations**: Gestión de relaciones entre entidades