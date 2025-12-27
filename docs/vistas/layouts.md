# Vistas: Layouts del Sistema

**Directorio:** `resources/views/layouts/`
**Propósito:** Layouts base que proporcionan estructura y funcionalidad común

## Descripción General

Los layouts definen la estructura HTML base de la aplicación, incluyendo headers, sidebars, footers y sistemas de navegación. Utilizan el sistema de temas dinámicos y están optimizados para rendimiento y accesibilidad.

## Lista de Layouts

| Layout | Archivo | Descripción |
|--------|---------|-------------|
| `app` | `app.blade.php` | Layout principal del dashboard |
| `guest` | `guest.blade.php` | Layout simplificado para páginas públicas |

## Layout: App (Principal)

**Archivo:** `resources/views/layouts/app.blade.php`

### Descripción
Layout completo del dashboard con sidebar, header, main content y footer. Incluye sistema de temas dinámico, navegación y componentes modulares.

### Estructura HTML Completa

```blade
<!doctype html>
<html lang="es" data-theme="dark">
<head>
  <!-- Meta tags, title, CSRF token -->
  <meta name="api-token" content="{{ session('passport_token') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Configuración Tailwind -->
  <script>
    tailwind.config = { /* ... */ }
  </script>

  <!-- Variables CSS dinámicas del tema -->
  <style>
    @php
      $colorThemeService = app(\App\Services\ColorThemeService::class);
      $userTheme = $colorThemeService->getUserTheme();
    @endphp

    :root {
      @if($userTheme)
        @foreach($userTheme->colors as $key => $value)
          --c-{{ $key }}: {{ $value }};
        @endforeach
      @else
        /* Fallback colors */
      @endif
    }

    /* Scrollbar styles */
    ::-webkit-scrollbar { /* ... */ }

    /* Custom styles */
  </style>
</head>

<body class="min-h-screen bg-[var(--c-bg)] text-[var(--c-text)] font-sans">
  <!-- Preloader -->
  @include('components.preloader')

  <!-- Backdrop móvil -->
  <div id="dash-backdrop" class="lg:hidden fixed inset-0 bg-black/40 hidden"></div>

  <!-- Grid layout principal -->
  <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] h-screen">
    <!-- Sidebar -->
    <aside id="dash-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-[80%] max-w-[320px] lg:w-auto translate-x-[-100%] lg:translate-x-0 transition-transform duration-300 ease-out bg-[var(--c-surface)] border-r border-[var(--c-border)]">
      <!-- Sidebar content -->
    </aside>

    <!-- Main content area -->
    <div class="lg:col-start-2 flex flex-col h-screen">
      <!-- Header -->
      <header class="h-[10vh]">
        @include('components.header')
      </header>

      <!-- Main -->
      <main id="dash-main" class="h-[80vh] overflow-auto px-4 sm:px-6 py-6">
        @yield('content')
      </main>

      <!-- Footer -->
      <footer class="h-[10vh]">
        @include('components.footer')
      </footer>
    </div>
  </div>

  <!-- Modals -->
  @include('components.json-response-modal')
  <x-media-picker />

  <!-- Scripts -->
  <script src="{{ asset('js/media-picker.js') }}"></script>
  <script>
    // JavaScript del layout
  </script>
</body>
</html>
```

### Sistema de Temas Dinámico

#### Servicio de Temas
```php
$colorThemeService = app(\App\Services\ColorThemeService::class);
$userTheme = $colorThemeService->getUserTheme();
```

#### Variables CSS
```css
:root {
  --c-bg: oklch(0.17 0.02 255);        /* Fondo */
  --c-surface: oklch(0.21 0.02 255);   /* Superficies */
  --c-elev: oklch(0.25 0.02 255);      /* Elevaciones */
  --c-text: oklch(0.93 0.02 255);      /* Texto principal */
  --c-muted: oklch(0.74 0.02 255);     /* Texto secundario */
  --c-border: oklch(0.35 0.02 255);    /* Bordes */
  --c-primary: oklch(0.72 0.14 260);   /* Color primario */
  --c-primary-ink: oklch(0.12 0.02 260); /* Texto sobre primario */
  --c-accent: oklch(0.75 0.13 170);    /* Acento */
  --c-danger: oklch(0.68 0.21 25);     /* Peligro/Error */
}
```

### Sidebar (Navegación)

#### Estructura
```blade
<aside id="dash-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-[80%] max-w-[320px] lg:w-auto translate-x-[-100%] lg:translate-x-0 transition-transform duration-300 ease-out bg-[var(--c-surface)] border-r border-[var(--c-border)]">
  <div class="h-full flex flex-col">
    <!-- Branding -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-[var(--c-border)]">
      <div class="size-9 rounded-xl grid place-items-center bg-[var(--c-primary)] text-white font-bold shadow-soft">D</div>
      <div>
        <h1 class="text-base font-semibold leading-tight">Dashboard Base</h1>
        <p class="text-xs text-[var(--c-muted)] leading-tight">Layout modular</p>
      </div>
    </div>

    <!-- Buscador -->
    <div class="px-4 py-3 border-b border-[var(--c-border)]">
      <label for="dash-sidebar-search" class="sr-only">Buscar</label>
      <div class="flex items-center gap-2 rounded-2xl bg-[var(--c-elev)] px-3 py-2 ring-1 ring-[var(--c-border)] focus-within:ring-[var(--c-primary)]">
        <svg class="size-5 opacity-70"><!-- Icon --></svg>
        <input id="dash-sidebar-search" type="search" placeholder="Buscar…" class="bg-transparent outline-none w-full text-sm placeholder:text-[var(--c-muted)]" />
      </div>
    </div>

    <!-- Menú Acordeón -->
    <nav id="dash-accordion" class="flex-1 overflow-y-auto p-2 space-y-2">
      <!-- Grupos de navegación -->
    </nav>

    <!-- Footer del sidebar -->
    <div class="mt-auto px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-between">
      <a href="#" class="text-xs text-[var(--c-muted)] hover:underline">v1.0</a>
      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit"><!-- Botón logout --></button>
      </form>
    </div>
  </div>
</aside>
```

#### Acordeón de Navegación

##### Grupos de Menú
```blade
<section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
  <button id="dash-acc-btn-1" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-1" aria-expanded="true">
    <span class="flex items-center gap-3">
      <svg class="size-5"><!-- Icon --></svg>
      <span class="text-sm font-medium">General</span>
    </span>
    <svg class="size-4 rotate-0 transition-transform"><!-- Chevron --></svg>
  </button>
  <div id="dash-acc-panel-1" class="hidden cv-auto">
    <div class="p-2 bg-[var(--c-surface)] min-h-0">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
        <span class="size-6 grid place-items-center rounded-lg ring-1 ring-[var(--c-border)]">🏠</span>
        Inicio
      </a>
      <!-- Más enlaces -->
    </div>
  </div>
</section>
```

##### JavaScript del Acordeón
```javascript
const ACCORDION_SINGLE_OPEN = true;
const currentGroup = routeToGroup[currentRoute] || 1;

// Inicialización y event listeners
```

### Header y Footer

#### Header
```blade
<header class="h-[10vh]">
  @include('components.header')
</header>
```

#### Footer
```blade
<footer class="h-[10vh]">
  @include('components.footer')
</footer>
```

### Área de Contenido Principal

#### Main Content
```blade
<main id="dash-main" class="h-[80vh] overflow-auto px-4 sm:px-6 py-6">
  @yield('content')
</main>
```

### JavaScript del Layout

#### Variables Globales
```javascript
const currentRoute = '{{ request()->route()->getName() }}';
const routeToGroup = {
  'users': 2,
  'currencies': 2,
  'color-themes': 2,
  'rbac': 2,
};
```

#### Funciones de Sidebar
```javascript
const openSidebar = () => { /* ... */ };
const closeSidebar = () => { /* ... */ };
```

#### Sistema de Acordeón
```javascript
const openPanel = (panelEl, btnEl, iconEl) => { /* ... */ };
const closePanel = (panelEl, btnEl, iconEl) => { /* ... */ };
```

#### Marcado Activo
```javascript
const links = document.querySelectorAll('#dash-accordion a[data-route]');
links.forEach(link => {
  const route = link.getAttribute('data-route');
  if(route === currentRoute){
    link.classList.add('active');
  }
});
```

## Layout: Guest

**Archivo:** `resources/views/layouts/guest.blade.php`

### Descripción
Layout simplificado para páginas públicas como login, registro, etc.

### Estructura
```blade
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Iniciar Sesión')</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50">
  @yield('content')
</body>
</html>
```

### Características
- **Sin sidebar**: Layout minimalista
- **Sin temas dinámicos**: Colores estáticos
- **Sin JavaScript**: Solo HTML y CSS
- **Responsive**: Diseño adaptable

## Sistema de Grid Layout

### Desktop (lg+)
```css
.grid-cols-1 lg:grid-cols-[280px_1fr]
```

- **Sidebar**: 280px fijo
- **Content**: Resto del espacio (`1fr`)

### Mobile
```css
.grid-cols-1
```

- **Sidebar**: Overlay con `translate-x-[-100%]`
- **Content**: Ancho completo

## Variables y Configuración

### Meta Tags
```blade
@if(session('passport_token'))
  <meta name="api-token" content="{{ session('passport_token') }}">
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Configuración Tailwind
```javascript
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ["Inter var", "Inter", ...]
      },
      boxShadow: {
        soft: "0 1px 2px rgba(0,0,0,.04), 0 2px 12px rgba(0,0,0,.06)"
      }
    }
  }
}
```

## Consideraciones de Rendimiento

### Optimizaciones Implementadas

1. **CSS Containment**
   ```css
   @supports (content-visibility: auto) {
     .cv-auto {
       content-visibility: auto;
       contain-intrinsic-size: 1px 400px;
     }
   }
   ```

2. **Will-change para animaciones**
   ```css
   #dash-sidebar.animating { will-change: transform; }
   ```

3. **Scrollbar-gutter**
   ```css
   html { scrollbar-gutter: stable; }
   ```

## Accesibilidad

### Características de Accesibilidad

- **ARIA attributes**: `aria-controls`, `aria-expanded`
- **Semantic HTML**: Header, nav, main, aside
- **Keyboard navigation**: Enlaces y botones focusables
- **Screen readers**: Labels y descripciones apropiadas

## Personalización

### Modificar Tema
```php
// En app.blade.php
$userTheme = $colorThemeService->getUserTheme();
// Modificar colores según necesidades
```

### Agregar Nuevos Grupos de Navegación
```blade
<section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
  <button id="dash-acc-btn-X"><!-- Nuevo grupo --></button>
  <div id="dash-acc-panel-X" class="hidden cv-auto">
    <!-- Nuevos enlaces -->
  </div>
</section>
```

### Cambiar Layout Proportions
```css
/* Modificar alturas */
.h-[10vh] -> .h-[12vh]  /* Header más alto */
.h-[80vh] -> .h-[78vh]  /* Main más pequeño */
.h-[10vh] -> .h-[10vh]  /* Footer igual */
```

## Testing

### Casos de Prueba
- **Responsive**: Diferentes tamaños de pantalla
- **Navegación**: Sidebar móvil y desktop
- **Temas**: Cambio dinámico de colores
- **Accesibilidad**: Navegación por teclado

## Próximas Mejoras

1. **PWA Support**: Service workers y manifest
2. **Offline Mode**: Funcionalidad offline
3. **Performance**: Code splitting y lazy loading
4. **Internationalization**: Soporte multiidioma
5. **Dark Mode**: Toggle manual de modo oscuro
6. **Customizable Layout**: Layouts por usuario