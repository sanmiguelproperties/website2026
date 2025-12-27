# Vista: Página de Bienvenida

**Archivo:** `resources/views/welcome.blade.php`
**Layout:** Ninguno (página completa)

## Descripción

La página de bienvenida es la landing page por defecto de Laravel, completamente rediseñada con Tailwind CSS v4.0.7 y soporte para modo oscuro. Incluye el logo de Laravel, enlaces a documentación y un diseño moderno responsivo.

## Estructura HTML

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Configuración Tailwind -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ["Inter var", "Inter", "system-ui", ...]
          },
          boxShadow: {
            soft: "0 1px 2px rgba(0,0,0,.04), 0 2px 12px rgba(0,0,0,.06)"
          }
        }
      }
    }
  </script>

  <!-- Estilos inline con todas las variables CSS de Tailwind v4 -->
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @else
    <style>
      /* CSS completo de Tailwind v4.0.7 inline */
    </style>
  @endif
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
  <!-- Header con navegación -->
  <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
    @if (Route::has('login'))
      <nav class="flex items-center justify-end gap-4">
        <!-- Enlaces de login/register -->
      </nav>
    @endif
  </header>

  <!-- Contenido principal -->
  <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
      <!-- Panel de texto -->
      <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
        <!-- Contenido de texto -->
      </div>

      <!-- Panel del logo -->
      <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
        <!-- Logo SVG de Laravel -->
      </div>
    </main>
  </div>

  <!-- Footer oculto en móviles -->
  @if (Route::has('login'))
    <div class="h-14.5 hidden lg:block"></div>
  @endif
</body>
</html>
```

## Características Principales

### 1. Diseño Responsivo
- **Móvil**: Layout vertical (logo arriba, texto abajo)
- **Desktop**: Layout horizontal (texto izquierda, logo derecha)
- Breakpoint principal en `lg` (1024px)

### 2. Modo Oscuro
- Detección automática del sistema operativo
- Variables CSS para colores claros y oscuros
- Transiciones suaves entre modos

### 3. Logo de Laravel Animado
- SVG completo del logo de Laravel
- Animación de entrada con `translate-y-6` a `translate-y-0`
- Opacidad de 0 a 100% con duración de 750ms
- Delay de 300ms para elementos decorativos

### 4. Tipografía Moderna
- Fuente "Instrument Sans" de Google Fonts
- Tamaños de fuente responsive
- Interlineado optimizado

## Sistema de Colores

### Modo Claro
```css
--color-red-50: oklch(.971 .013 17.38);
--color-red-500: oklch(.637 .237 25.331);
--color-red-600: oklch(.577 .245 27.325);
/* ... más colores ... */
```

### Modo Oscuro
```css
.dark\:bg-\[\#0a0a0a\]: background-color: #0a0a0a;
.dark\:bg-\[\#161615\]: background-color: #161615;
.dark\:text-\[\#EDEDEC\]: color: #ededec;
```

## Animaciones y Transiciones

### Animación del Logo
```css
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(.4,0,.2,1);
  transition-duration: 750ms;
}

.starting\:translate-y-6 {
  --tw-translate-y: 1.5rem;
}

.starting\:opacity-0 {
  opacity: 0;
}
```

### Efectos de Hover
- Botones con `hover:opacity-95`
- Enlaces con `hover:underline`
- Fondos con `hover:bg-black`

## Navegación Condicional

### Header
Solo se muestra si existe la ruta `login`:

```blade
@if (Route::has('login'))
  <nav class="flex items-center justify-end gap-4">
    @auth
      <a href="{{ url('/dashboard') }}">Dashboard</a>
    @else
      <a href="{{ route('login') }}">Log in</a>
      @if (Route::has('register'))
        <a href="{{ route('register') }}">Register</a>
      @endif
    @endauth
  </nav>
@endif
```

## Contenido del Panel de Texto

### Título
```html
<h1 class="mb-1 font-medium">Let's get started</h1>
<p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
  Laravel has an incredibly rich ecosystem. <br>
  We suggest starting with the following.
</p>
```

### Lista de Enlaces
```html
<ul class="flex flex-col mb-4 lg:mb-6">
  <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
    <span class="relative py-1 bg-white dark:bg-[#161615]">
      <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
        <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
      </span>
    </span>
    <span>
      Read the
      <a href="https://laravel.com/docs" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433]">
        <span>Documentation</span>
        <svg>...</svg>
      </a>
    </span>
  </li>
  <!-- Más elementos -->
</ul>
```

### Botón de Acción
```html
<ul class="flex gap-3 text-sm leading-normal">
  <li>
    <a href="https://cloud.laravel.com" target="_blank" class="inline-block dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-black hover:border-black px-5 py-1.5 bg-[#1b1b18] rounded-sm border border-black text-white text-sm leading-normal">
      Deploy now
    </a>
  </li>
</ul>
```

## Panel del Logo

### Contenedor
```html
<div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
  {{-- Laravel Logo --}}
  <svg class="w-full text-[#F53003] dark:text-[#F61500] transition-all translate-y-0 opacity-100 max-w-none duration-750 starting:opacity-0 starting:translate-y-6" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Paths del logo -->
  </svg>

  {{-- Elementos decorativos SVG --}}
  <svg class="w-[448px] max-w-none relative -mt-[4.9rem] -ml-8 lg:ml-0 lg:-mt-[6.6rem] dark:hidden" viewBox="0 0 440 376" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Elementos decorativos para modo claro -->
  </svg>

  <svg class="w-[448px] max-w-none relative -mt-[4.9rem] -ml-8 lg:ml-0 lg:-mt-[6.6rem] hidden dark:block" viewBox="0 0 440 376" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Elementos decorativos para modo oscuro -->
  </svg>

  {{-- Overlay --}}
  <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"></div>
</div>
```

## JavaScript

No incluye JavaScript personalizado. Las animaciones se manejan con CSS puro.

## SEO y Meta Tags

```html
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ config('app.name', 'Laravel') }}</title>
```

## Rendimiento

### Optimizaciones
- **Preconnect** para Google Fonts
- **Tailwind CSS** desde CDN
- **CSS inline** para first paint
- **Lazy loading** implícito con `@vite`

### Tamaños
- **Móvil**: `max-w-[335px]`
- **Desktop**: `max-w-4xl` (56rem/896px)

## Accesibilidad

- **Contraste adecuado** en ambos modos
- **Navegación por teclado** en enlaces
- **Etiquetas semánticas** (header, nav, main)
- **Atributos ARIA** donde es necesario

## Personalización

### Cambiar Colores
```css
/* Modificar variables CSS */
--color-red-500: oklch(0.6 0.25 30); /* Rojo más saturado */
```

### Modificar Contenido
```blade
<!-- Cambiar el título -->
<h1 class="mb-1 font-medium">Bienvenido a Mi App</h1>

<!-- Agregar nuevos enlaces -->
<li class="flex items-center gap-4 py-2 relative...">
  <span>Visita nuestro</span>
  <a href="/blog">Blog</a>
</li>
```

### Cambiar Logo
```blade
{{-- Reemplazar el SVG completo --}}
<svg viewBox="0 0 100 100">
  <!-- Tu logo personalizado -->
</svg>
```

## Compatibilidad

- **Navegadores modernos** con soporte CSS Grid y Flexbox
- **Tailwind CSS v4.0.7**
- **Laravel 11+**
- **Vite** para desarrollo (opcional)

## Próximas Mejoras

1. **Internacionalización**: Soporte multiidioma
2. **CMS Integration**: Contenido dinámico
3. **Analytics**: Tracking de conversiones
4. **A/B Testing**: Variantes de la página
5. **Performance**: Optimización de imágenes SVG