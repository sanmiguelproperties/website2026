<!doctype html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Dashboard Base • Tailwind + Dark Mode')</title>

  @if(session('passport_token'))
    <meta name="api-token" content="{{ session('passport_token') }}">
  @endif

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Tailwind config (opcional): habilita clases arbitrarias sin tema extra
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ["Inter var", "Inter", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Ubuntu", "Cantarell", "Noto Sans", "Helvetica Neue", "Arial", "\"Apple Color Emoji\"", "\"Segoe UI Emoji\"", "\"Segoe UI Symbol\""]
          },
          boxShadow: {
            soft: "0 1px 2px rgba(0,0,0,.04), 0 2px 12px rgba(0,0,0,.06)"
          }
        }
      }
    }
  </script>

  <!-- Variables de color dinámicas del tema del usuario -->
  <style>
    @php
      $colorThemeService = app(\App\Services\ColorThemeService::class);
      $userTheme = $colorThemeService->getUserTheme();
    @endphp

    :root {
      /* Variables dinámicas del tema del usuario */
      @if($userTheme)
        @foreach($userTheme->colors as $key => $value)
          --c-{{ $key }}: {{ $value }};
        @endforeach
      @else
        /* Fallback por defecto (oscuro) */
        --c-bg: oklch(0.17 0.02 255);
        --c-surface: oklch(0.21 0.02 255);
        --c-elev: oklch(0.25 0.02 255);
        --c-text: oklch(0.93 0.02 255);
        --c-muted: oklch(0.74 0.02 255);
        --c-border: oklch(0.35 0.02 255);
        --c-primary: oklch(0.72 0.14 260);
        --c-primary-ink: oklch(0.12 0.02 260);
        --c-accent: oklch(0.75 0.13 170);
        --c-danger: oklch(0.68 0.21 25);
      @endif
      --radius: 14px;
      color-scheme: dark; /* hint al navegador */
    }

    /* Scrollbar sutil */
    ::-webkit-scrollbar{width:10px;height:10px}
    ::-webkit-scrollbar-thumb{background:var(--c-border);border-radius:999px}
    ::-webkit-scrollbar-track{background:transparent}

    /* Estilo para enlace activo */
    #dash-accordion a.active {
      background-color: var(--c-elev);
      outline: 1px solid var(--c-primary);
      outline-offset: -1px;
      font-weight: 600;
    }

    /* Evita saltos al aparecer la barra de scroll */
    html { scrollbar-gutter: stable; }

    /* will-change sólo durante animación del sidebar */
    #dash-sidebar.animating { will-change: transform; }

    /* Pintar sólo lo visible dentro de los paneles del acordeón */
    @supports (content-visibility: auto) {
      .cv-auto { content-visibility: auto; contain-intrinsic-size: 1px 400px; }
    }

    /* Animación ligera para abrir/cerrar acordeón (barata) */
    .acc-enter { transition: opacity .18s ease, transform .18s ease; opacity: 0; transform: scaleY(.98); }
    .acc-enter.act { opacity: 1; transform: scaleY(1); }
  </style>
</head>
<body class="min-h-screen bg-[var(--c-bg)] text-[var(--c-text)] font-sans">
   <!-- Preloader -->
  @include('components.preloader')
  <!--
    -----------------------------------------------------------
    LAYOUT PRINCIPAL
    - Aside (Barra lateral)
    - Header (Barra superior)
    - Main (Contenido del dashboard)
    - Footer (Pie de página)
    -----------------------------------------------------------
  -->

  <!-- Backdrop móvil para el sidebar -->
  <div id="dash-backdrop" class="lg:hidden fixed inset-0 bg-black/40 hidden"></div>

  <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] h-screen">

    <!-- ========================= ASIDE / SIDEBAR ========================= -->
    <aside id="dash-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-[80%] max-w-[320px] lg:w-auto translate-x-[-100%] lg:translate-x-0 transition-transform duration-300 ease-out bg-[var(--c-surface)] border-r border-[var(--c-border)]">
      <div class="h-full flex flex-col">
        <!-- Branding -->
        <div class="flex items-center gap-3 px-5 py-4 border-b border-[var(--c-border)]">
          <div class="size-10 rounded-2xl grid place-items-center bg-[var(--c-primary)] text-[var(--c-primary-ink)] shadow-soft">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 21h18"/>
              <path d="M6 21V7a2 2 0 0 1 2-2h3"/>
              <path d="M11 21V11a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v10"/>
              <path d="M9 9h2"/>
              <path d="M9 13h2"/>
              <path d="M9 17h2"/>
              <path d="M15 13h2"/>
              <path d="M15 17h2"/>
            </svg>
          </div>
          <div>
            <h1 class="text-base font-semibold leading-tight">San Miguel Properties</h1>
            <p class="text-xs text-[var(--c-muted)] leading-tight">Panel inmobiliario</p>
          </div>
        </div>

        <!-- Buscador en el sidebar -->
        <div class="px-4 py-3 border-b border-[var(--c-border)]">
          <label for="dash-sidebar-search" class="sr-only">Buscar</label>
          <div class="flex items-center gap-2 rounded-2xl bg-[var(--c-elev)] px-3 py-2 ring-1 ring-[var(--c-border)] focus-within:ring-[var(--c-primary)]">
            <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            <input id="dash-sidebar-search" type="search" placeholder="Buscar…" class="bg-transparent outline-none w-full text-sm placeholder:text-[var(--c-muted)]" />
          </div>
        </div>

        <!-- Menú (Acordeón modular) -->
        <nav id="dash-accordion" class="flex-1 overflow-y-auto p-2 space-y-2">
          <!-- Grupo 1: Inmobiliaria -->
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-1" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-1" aria-expanded="true">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M3 10.5 12 3l9 7.5"/>
                  <path d="M5 10v11h14V10"/>
                  <path d="M9 21v-6h6v6"/>
                </svg>
                <span class="text-sm font-medium">Inmobiliaria</span>
              </span>
              <svg class="size-4 rotate-0 transition-transform" id="dash-acc-icon-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-1" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                <a href="{{ route('dashboard') }}" data-route="dashboard" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14v4"/><path d="M11 10v8"/><path d="M15 6v12"/></svg>
                  </span>
                  Dashboard
                </a>

                <a href="{{ route('properties') }}" data-route="properties" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M6 21V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12"/><path d="M9 21v-6h6v6"/></svg>
                  </span>
                  Propiedades
                </a>

                <a href="{{ route('agencies') }}" data-route="agencies" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M7 21V8"/><path d="M17 21V8"/><path d="M7 8l5-5 5 5"/></svg>
                  </span>
                  Agencias
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </span>
                  Clientes
                </a>

                <a href="{{ route('funnel') }}" data-route="funnel" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18l-7 8v7l-4 2v-9L3 4z"/></svg>
                  </span>
                  Leads / Funnel
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h2"/><path d="M12 14h2"/><path d="M16 14h2"/></svg>
                  </span>
                  Agenda de visitas
                </a>
              </div>
            </div>
          </section>

          <!-- Grupo 2: Administración -->
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-2" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-2" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 8h10"/><path d="M7 12h10"/><path d="M7 16h10"/></svg>
                <span class="text-sm font-medium">Administración</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-2" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                <a href="{{ route('users') }}" data-route="users" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </span>
                  Usuarios
                </a>

                <a href="{{ route('currencies') }}" data-route="currencies" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                  </span>
                  Monedas
                </a>

                <a href="{{ route('color-themes') }}" data-route="color-themes" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="19" cy="17" r="2"/><circle cx="6" cy="17" r="3"/><path d="M9 17a4 4 0 0 1 7 0"/><path d="M15 8.5a6.5 6.5 0 0 1 4 6"/></svg>
                  </span>
                  Temas de Color
                </a>

                <a href="{{ route('frontend-colors') }}" data-route="frontend-colors" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>
                  </span>
                  Colores Frontend
                </a>

                <a href="{{ route('rbac') }}" data-route="rbac" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                  </span>
                  Roles & Permisos
                </a>

                <a href="{{ route('easybroker') }}" data-route="easybroker" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  </span>
                  EasyBroker Sync
                </a>

                <a href="{{ route('mls') }}" data-route="mls" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
                  </span>
                  MLS AMPI Sync
                </a>

                <a href="{{ route('mls-agents') }}" data-route="mls-agents" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </span>
                  Agentes MLS
                </a>

                <a href="{{ route('mls-offices') }}" data-route="mls-offices" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/></svg>
                  </span>
                  Agencias MLS
                </a>
              </div>
            </div>
          </section>

          <!-- Grupo 4: CMS (Contenido Administrable) -->
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-4" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-4" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                <span class="text-sm font-medium">CMS</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-4" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                <a href="{{ route('cms.pages') }}" data-route="cms.pages" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </span>
                  Páginas
                </a>

                <a href="{{ route('cms.posts') }}" data-route="cms.posts" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  </span>
                  Blog / Posts
                </a>

                <a href="{{ route('cms.menus') }}" data-route="cms.menus" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                  </span>
                  Menús
                </a>

                <a href="{{ route('cms.settings') }}" data-route="cms.settings" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                  </span>
                  Configuración
                </a>
              </div>
            </div>
          </section>

          <!-- Grupo 3: Ajustes -->
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-3" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-3" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M2 12h20"/></svg>
                <span class="text-sm font-medium">Ajustes</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-3" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v4"/><path d="M3 12h4"/><path d="M17 12h4"/><path d="M12 17v4"/><path d="M5.6 5.6l2.8 2.8"/><path d="M15.6 15.6l2.8 2.8"/><path d="M18.4 5.6l-2.8 2.8"/><path d="M8.4 15.6l-2.8 2.8"/><circle cx="12" cy="12" r="3"/></svg>
                  </span>
                  Preferencias
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M10.3 21a1.7 1.7 0 0 0 3.4 0"/></svg>
                  </span>
                  Notificaciones
                </a>
              </div>
            </div>
          </section>
        </nav>

        <!-- Footer del sidebar -->
        <div class="mt-auto px-4 py-3 border-t border-[var(--c-border)]">
          <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="text-xs text-[var(--c-muted)] truncate">Gestión inmobiliaria</p>
              <a href="#" class="text-xs text-[var(--c-muted)] hover:underline">v1.0</a>
            </div>
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-transparent border border-[var(--c-danger)] text-[var(--c-danger)] hover:bg-[var(--c-danger)] hover:text-white transition">
              <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              <span class="text-sm">Salir</span>
            </button>
          </form>
          </div>
        </div>
      </div>
    </aside>

    <!-- ========================= CONTENIDO (Header + Main + Footer) ========================= -->
    <div class="lg:col-start-2 flex flex-col h-screen">
      <!-- ===== HEADER / TOPBAR ===== -->
      <header class="h-[10vh]">
        @include('components.header')
      </header>

      <!-- ===== MAIN ===== -->
      <main id="dash-main" class="h-[80vh] overflow-auto px-4 sm:px-6 py-6">
        @yield('content')
      </main>

      <!-- ===== FOOTER ===== -->
      <footer class="h-[10vh]">
        @include('components.footer')
      </footer>
    </div>
  <!-- JSON Response Modal -->
  @include('components.json-response-modal')

  <!-- Media Picker Modal -->
  <x-media-picker />

  </div>
  
  
  <!-- ========================= SCRIPTS (IDs únicos prefijados con dash-) ========================= -->
  <script src="{{ asset('js/media-inputs.js') }}"></script>
  <script src="{{ asset('js/media-picker.js') }}"></script>
  <script>
    (function(){
      // --- Ruta actual ---
      const currentRoute = '{{ request()->route()->getName() }}';

      // --- Mapa de rutas a grupos ---
      const routeToGroup = {
        'dashboard': 1,
        'funnel': 1,
        'properties': 1,
        'agencies': 1,
        'users': 2,
        'currencies': 2,
        'color-themes': 2,
        'frontend-colors': 2,
        'rbac': 2,
        'easybroker': 2,
        'mls': 2,
        'mls-agents': 2,
        'mls-offices': 2,
        'cms.pages': 4,
        'cms.posts': 4,
        'cms.menus': 4,
        'cms.settings': 4,
      };

      // --- Año del footer ---
      const y = document.getElementById('dash-year');
      if (y) y.textContent = new Date().getFullYear();

      // --- Sidebar responsive ---
      const sidebar  = document.getElementById('dash-sidebar');
      const menuBtn  = document.getElementById('dash-menu-btn');
      const backdrop = document.getElementById('dash-backdrop');

      // Backdrop blur condicional (solo si el navegador soporta)
      const enableBlur = CSS.supports && CSS.supports('backdrop-filter: blur(4px)');
      if (enableBlur) {
        const s = document.createElement('style');
        s.textContent = '#dash-backdrop.blur{backdrop-filter:blur(4px)}';
        document.head.appendChild(s);
      }

      const openSidebar = () => {
        sidebar.classList.add('animating');
        if (enableBlur) backdrop.classList.add('blur');
        sidebar.style.transform = 'translateX(0)';
        backdrop.classList.remove('hidden');
        sidebar.addEventListener('transitionend', () => sidebar.classList.remove('animating'), { once: true });
      };
      const closeSidebar = () => {
        sidebar.classList.add('animating');
        sidebar.style.transform = '';
        sidebar.addEventListener('transitionend', () => sidebar.classList.remove('animating'), { once: true });
        if (enableBlur) backdrop.classList.remove('blur');
        backdrop.classList.add('hidden');
      };
      menuBtn?.addEventListener('click', openSidebar);
      backdrop?.addEventListener('click', closeSidebar);
      window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeSidebar(); });

      // --- Acordeón modular optimizado (sin max-height) ---
      const ACCORDION_SINGLE_OPEN = true; // solo un módulo abierto a la vez
      const currentGroup = routeToGroup[currentRoute] || 1;
      const accordionItems = [
        { btn: 'dash-acc-btn-1', panel: 'dash-acc-panel-1', icon: 'dash-acc-icon-1', defaultOpen: currentGroup === 1 },
        { btn: 'dash-acc-btn-2', panel: 'dash-acc-panel-2', icon: 'dash-acc-icon-2', defaultOpen: currentGroup === 2 },
        { btn: 'dash-acc-btn-4', panel: 'dash-acc-panel-4', icon: 'dash-acc-icon-4', defaultOpen: currentGroup === 4 },
        { btn: 'dash-acc-btn-3', panel: 'dash-acc-panel-3', icon: 'dash-acc-icon-3', defaultOpen: currentGroup === 3 },
      ];

      const openPanel = (panelEl, btnEl, iconEl) => {
        panelEl.classList.remove('hidden');
        panelEl.classList.add('acc-enter');
        // sube a estado visible en el siguiente frame (transición 0 -> 1)
        requestAnimationFrame(() => {
          panelEl.classList.add('act');
        });
        btnEl.setAttribute('aria-expanded', 'true');
        if (iconEl) iconEl.style.transform = 'rotate(0deg)';
      };

      const closePanel = (panelEl, btnEl, iconEl) => {
        // garantiza transición también al cerrar (1 -> 0)
        panelEl.classList.add('acc-enter');
        panelEl.classList.add('act');
        requestAnimationFrame(() => {
          panelEl.classList.remove('act');
        });
        const onEnd = () => {
          panelEl.classList.add('hidden');
          panelEl.classList.remove('acc-enter');
          panelEl.removeEventListener('transitionend', onEnd);
        };
        panelEl.addEventListener('transitionend', onEnd);
        btnEl.setAttribute('aria-expanded', 'false');
        if (iconEl) iconEl.style.transform = 'rotate(-90deg)';
      };

      // Inicialización: abre el grupo correspondiente y cierra otros
      const instances = accordionItems.map(({btn, panel, icon, defaultOpen}) => {
        const btnEl   = document.getElementById(btn);
        const panelEl = document.getElementById(panel);
        const iconEl  = icon ? document.getElementById(icon) : null;
        if(!btnEl || !panelEl) return null;

        if(defaultOpen) {
          panelEl.classList.remove('hidden'); // abierto de inicio (sin animar)
          btnEl.setAttribute('aria-expanded','true');
          if(iconEl) iconEl.style.transform = 'rotate(0deg)';
        } else {
          panelEl.classList.add('hidden');
          btnEl.setAttribute('aria-expanded','false');
          if(iconEl) iconEl.style.transform = 'rotate(-90deg)';
        }

        btnEl.addEventListener('click', () => {
          const isOpen = btnEl.getAttribute('aria-expanded') === 'true';
          if(isOpen){
            closePanel(panelEl, btnEl, iconEl);
          } else {
            if (ACCORDION_SINGLE_OPEN) {
              accordionItems.forEach(({btn, panel, icon}) => {
                const otherBtn   = document.getElementById(btn);
                const otherPanel = document.getElementById(panel);
                const otherIcon  = icon ? document.getElementById(icon) : null;
                if(otherPanel && otherPanel !== panelEl && !otherPanel.classList.contains('hidden')) {
                  closePanel(otherPanel, otherBtn, otherIcon);
                }
              });
            }
            openPanel(panelEl, btnEl, iconEl);
          }
        });

        return { btnEl, panelEl, iconEl };
      }).filter(Boolean);

      // --- Marcar enlace activo ---
      const links = document.querySelectorAll('#dash-accordion a[data-route]');
      links.forEach(link => {
        const route = link.getAttribute('data-route');
        if(route === currentRoute){
          link.classList.add('active');
        }
      });

      // --- Acción global "Nuevo" ---
      // Cada vista puede definir `window.dashNewAction = () => { ... }`
      // para reutilizar el botón del header sin romper el estándar.
      document.getElementById('dash-action-new')?.addEventListener('click', (e)=>{
        if (typeof window.dashNewAction === 'function') {
          e.preventDefault();
          window.dashNewAction();
          return;
        }
        alert('Acción: Crear nuevo registro');
      });
    })();
  </script>
</body>
</html>
