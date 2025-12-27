# Vistas: Componentes Reutilizables

**Directorio:** `resources/views/components/`
**Propósito:** Componentes Blade reutilizables en toda la aplicación

## Descripción General

Los componentes son piezas modulares de interfaz que se pueden reutilizar en múltiples vistas. Están construidos con Blade y Tailwind CSS, aprovechando el sistema de temas dinámicos.

## Lista de Componentes

| Componente | Archivo | Descripción |
|------------|---------|-------------|
| `footer` | `footer.blade.php` | Pie de página con enlaces legales |
| `header` | `header.blade.php` | Barra superior con navegación y acciones |
| `json-response-modal` | `json-response-modal.blade.php` | Modal para mostrar respuestas de API |
| `media-input` | `media-input.blade.php` | Input para selección de archivos multimedia |
| `media-picker` | `media-picker.blade.php` | Modal completo para gestión de medios |
| `preloader` | `preloader.blade.php` | Pantalla de carga con animación |

## Componente: Footer

**Archivo:** `resources/views/components/footer.blade.php`

### Descripción
Pie de página simple con enlaces legales y año dinámico.

### Uso
```blade
@include('components.footer')
```

### Estructura
```blade
<footer id="dash-footer" class="px-4 sm:px-6 py-6 border-t border-[var(--c-border)] text-sm text-[var(--c-muted)] flex items-center">
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 justify-between">
    <p>© <span id="dash-year"></span> Tu Empresa. Todos los derechos reservados.</p>
    <div class="flex items-center gap-3">
      <a href="#" class="hover:underline">Privacidad</a>
      <a href="#" class="hover:underline">Términos</a>
      <a href="#" class="hover:underline">Contacto</a>
    </div>
  </div>
</footer>
```

### Funcionalidades
- **Año dinámico**: Actualizado automáticamente via JavaScript
- **Enlaces legales**: Privacidad, Términos, Contacto
- **Responsive**: Layout vertical en móvil, horizontal en desktop

## Componente: Header

**Archivo:** `resources/views/components/header.blade.php`

### Descripción
Barra superior con navegación, búsqueda y acciones del usuario.

### Uso
```blade
@include('components.header')
```

### Estructura Principal
```blade
<header id="dash-header" class="bg-[var(--c-bg)]/80 backdrop-blur border-b border-[var(--c-border)] flex items-center">
  <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 w-full">
    <!-- Botón menú móvil -->
    <!-- Migas de pan -->
    <!-- Barra de búsqueda -->
    <!-- Botón acción -->
    <!-- Avatar usuario -->
  </div>
</header>
```

### Funcionalidades

#### Migas de Pan Dinámicas
```php
$currentRoute = Route::currentRouteName();
$breadcrumbs = [
  'dashboard' => [['Inicio', route('dashboard')], ['Dashboard', '#']],
  'funnel' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Funnel', '#']],
  // ...
];
```

#### Barra de Búsqueda
- Solo visible en desktop (`hidden md:flex`)
- Placeholder: "Buscar en todo…"
- Estilos de foco con variables CSS

#### Avatar de Usuario
```php
$user = auth()->user()->load('profileImage');
@if($user && $user->profileImage)
  <img alt="avatar" src="{{ $user->profileImage->url }}" class="size-8 rounded-lg"/>
@else
  <svg class="size-8 text-[var(--c-muted)]"><!-- Icono usuario --></svg>
@endif
```

## Componente: JSON Response Modal

**Archivo:** `resources/views/components/json-response-modal.blade.php`

### Descripción
Modal para mostrar respuestas de API con formato JSON y errores detallados.

### Uso
```javascript
window.dispatchEvent(new CustomEvent('api:response', {
  detail: {
    success: true,
    message: 'Operación exitosa',
    data: {...},
    errors: {...}
  }
}));
```

### Estructura
```blade
<div id="json-response-modal" class="fixed inset-0 z-[12000] hidden">
  <!-- Backdrop -->
  <!-- Modal container -->
  <div class="rounded-2xl overflow-hidden border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
    <!-- Header con icono y título -->
    <!-- Contenido con mensaje y errores -->
    <!-- JSON colapsable -->
    <!-- Footer con botones -->
  </div>
</div>
```

### Funcionalidades

#### Estados Visuales
- **Éxito**: Icono verde, fondo verde claro
- **Error**: Icono rojo, fondo rojo claro

#### Contenido Expandible
```html
<details class="rounded border border-[var(--c-border)] bg-[var(--c-elev)]">
  <summary>Ver detalles (JSON)</summary>
  <pre id="json-response-json"><!-- JSON pretty-printed --></pre>
</details>
```

#### Funciones JavaScript
- `show(payload)`: Muestra el modal con datos
- `hide()`: Oculta el modal
- Copiar JSON al portapapeles
- Escape key para cerrar

## Componente: Media Input

**Archivo:** `resources/views/components/media-input.blade.php`

### Descripción
Componente Blade para selección de archivos multimedia con integración completa.

### Uso Básico
```blade
<x-media-input name="thumbnail_id" mode="single" />
<x-media-input name="gallery_ids" mode="multiple" :max="6" />
```

### Atributos

| Atributo | Tipo | Default | Descripción |
|----------|------|---------|-------------|
| `name` | string | null | Nombre del campo |
| `mode` | string | 'single' | 'single' o 'multiple' |
| `max` | int | null | Máximo archivos (multiple) |
| `placeholder` | string | null | Placeholder del input |
| `button` | string | 'Seleccionar' | Texto del botón |
| `preview` | bool | true | Mostrar preview |
| `readonly` | bool | true | Input no editable |

### Estructura
```blade
<div data-fp-scope>
  <div class="flex items-center gap-3">
    <input type="text" readonly data-filepicker="single|multiple" />
    <button type="button" data-fp-open>Seleccionar</button>
  </div>
  <div id="preview-container"></div>
</div>
```

## Componente: Media Picker

**Archivo:** `resources/views/components/media-picker.blade.php`

### Descripción
Modal completo para gestión de biblioteca multimedia.

### Estructura Principal
```blade
<div id="archive_manager-root" class="fixed inset-0 z-[9999] hidden">
  <!-- Backdrop -->
  <div class="rounded-none md:rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)]/90 backdrop-blur-xl shadow-2xl overflow-hidden flex flex-col h-[100vh] md:h-[80vh]">
    <!-- Header -->
    <!-- Toolbar -->
    <!-- Cuerpo con grid -->
    <!-- Footer -->
  </div>

  <!-- Panel editor -->
  <!-- Modal upload -->
  <!-- Modal URL -->
</div>
```

### Funcionalidades

#### Biblioteca
- Grid responsivo de archivos
- Filtros por tipo (imagen, video, audio, documento)
- Búsqueda por nombre/URL/MIME
- Paginación

#### Upload
- Drag & drop de archivos
- Selección múltiple
- Validación de tipos y tamaños

#### Editor
- Panel lateral para editar metadatos
- Preview de archivos
- Eliminación de archivos

## Componente: Preloader

**Archivo:** `resources/views/components/preloader.blade.php`

### Descripción
Pantalla de carga con animación CSS que bloquea la interfaz.

### Uso
```javascript
window.showPreloader(); // Mostrar
window.hidePreloader(); // Ocultar
```

### Estructura
```blade
<style>
  .preloader-overlay {
    position: fixed; inset: 0;
    background: #000; display: none;
    align-items: center; justify-content: center;
    z-index: 9999;
  }
  .preloader-overlay.is-visible { display: flex; }
  .preloader-spinner {
    border: 4px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: preloader-spin 1s ease-in-out infinite;
  }
</style>

<div id="preloader" class="preloader-overlay is-visible">
  <div class="preloader-spinner"></div>
</div>
```

### Funcionalidades

#### Control Programático
```javascript
window.showPreloader = function() {
  // Mostrar con transición
};

window.hidePreloader = function() {
  // Ocultar con mínimo 500ms visible
};
```

#### Características
- **Mínimo visible**: 500ms para evitar parpadeo
- **Body scroll lock**: Previene scroll cuando activo
- **Accesibilidad**: Atributos ARIA apropiados
- **No JS fallback**: CSS oculta el preloader si JS está deshabilitado

## Sistema de Temas

Todos los componentes utilizan variables CSS del sistema de temas:

```css
--c-bg: oklch(...);        /* Fondo */
--c-surface: oklch(...);   /* Superficies */
--c-elev: oklch(...);      /* Elevaciones */
--c-text: oklch(...);      /* Texto principal */
--c-muted: oklch(...);     /* Texto secundario */
--c-border: oklch(...);    /* Bordes */
--c-primary: oklch(...);   /* Color primario */
```

## JavaScript Integration

### Eventos Globales
```javascript
// Media picker
window.addEventListener('media:selected', (e) => {
  // Manejar selección
});

// API responses
window.addEventListener('api:response', (e) => {
  // Mostrar modal de respuesta
});
```

### Data Attributes
Los componentes usan atributos `data-*` para configuración:
- `data-fp-scope`: Scope del media input
- `data-filepicker`: Modo del picker
- `data-js`: Selectores para JavaScript

## Consideraciones Técnicas

### Rendimiento
- **Lazy loading** de componentes pesados
- **Event delegation** para mejor performance
- **CSS containment** donde es posible

### Accesibilidad
- **ARIA labels** apropiadas
- **Navegación por teclado**
- **Contraste adecuado** con temas dinámicos

### Compatibilidad
- **CSS Grid y Flexbox** para layouts modernos
- **ES6+ JavaScript** con fallbacks
- **CSS Variables** con soporte amplio

## Personalización

### Modificar Estilos
```css
/* Override component styles */
#dash-header {
  background: var(--custom-bg);
}
```

### Extender Funcionalidad
```javascript
// Agregar funcionalidad personalizada
document.addEventListener('DOMContentLoaded', () => {
  // Custom code here
});
```

## Testing

### Componentes para Probar
- **Visual regression**: En diferentes temas
- **Responsive**: Múltiples tamaños de pantalla
- **Accessibility**: Con lectores de pantalla
- **JavaScript**: Funcionalidad interactiva

## Próximas Mejoras

1. **Lazy loading**: Componentes cargados bajo demanda
2. **TypeScript**: Definiciones de tipos
3. **Storybook**: Catálogo de componentes
4. **Testing**: Suite completa de pruebas
5. **Documentation**: Storybook integration