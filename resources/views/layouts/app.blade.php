<!doctype html>
<html lang="es" data-theme="light">
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
        /* Fallback por defecto SMP */
        --c-bg: #ffffff;
        --c-surface: #ffffff;
        --c-elev: #f7f4ee;
        --c-text: #111111;
        --c-muted: #6f675d;
        --c-border: #e6dfd2;
        --c-primary: #c9a646;
        --c-primary-ink: #111111;
        --c-accent: #111111;
        --c-accent-ink: #ffffff;
        --c-danger: #b42318;
      @endif
      --radius: 14px;
      color-scheme: light; /* hint al navegador */
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

    .smp-logo-mark {
      letter-spacing: .08em;
      font-weight: 800;
    }

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
          <div class="size-10 rounded-xl grid place-items-center bg-[var(--c-primary)] text-[var(--c-primary-ink)] shadow-soft ring-1 ring-black/10">
            <span class="smp-logo-mark text-sm">SMP</span>
          </div>
          <div>
            <h1 class="text-base font-semibold leading-tight">San Miguel Properties</h1>
            <p class="text-xs text-[var(--c-muted)] leading-tight">Panel inmobiliario</p>
          </div>
        </div>

        <!-- Menú (Acordeón modular) -->
        @php
          $dashMenuUser = auth()->user();
          $dashCanMenuItem = static fn (string $item): bool => \App\Support\AdminMenu::canAccessItem($dashMenuUser, $item);
          $dashCanMenuGroup = static fn (int $group): bool => \App\Support\AdminMenu::groupVisible($dashMenuUser, $group);
        @endphp
        <nav id="dash-accordion" class="flex-1 overflow-y-auto p-2 space-y-2">
          <!-- Grupo 1: Inmobiliaria -->
          @if($dashCanMenuGroup(1))
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
                @if($dashCanMenuItem('dashboard'))
                <a href="{{ route('dashboard') }}" data-route="dashboard" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14v4"/><path d="M11 10v8"/><path d="M15 6v12"/></svg>
                  </span>
                  Dashboard
                </a>
                @endif

                @if($dashCanMenuItem('properties'))
                <a href="{{ route('properties') }}" data-route="properties" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M6 21V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12"/><path d="M9 21v-6h6v6"/></svg>
                  </span>
                  Propiedades
                </a>
                @endif

                @if($dashCanMenuItem('zones'))
                <a href="{{ route('zones') }}" data-route="zones" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 2l8 4v6c0 5-3 9-8 10-5-1-8-5-8-10V6l8-4z"/>
                      <path d="M12 8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                      <path d="M12 14v4"/>
                    </svg>
                  </span>
                  Zonas SEO
                </a>
                @endif

              </div>
            </div>
          </section>

          <!-- Grupo 2: Administración -->
          @endif

          <!-- Grupo 6: CRM -->
          @if($dashCanMenuGroup(6))
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-6" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-6" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
                <span class="text-sm font-medium">CRM</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-6" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                @if($dashCanMenuItem('clients'))
                <a href="{{ route('clients') }}" data-route="clients" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </span>
                  Clientes
                </a>
                @endif

                @if($dashCanMenuItem('property-contact-requests'))
                <a href="{{ route('property-contact-requests') }}" data-route="property-contact-requests" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
                  </span>
                  Leads
                </a>
                @endif

                @if($dashCanMenuItem('calendar'))
                <a href="{{ route('calendar') }}" data-route="calendar" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h2"/><path d="M12 14h2"/><path d="M16 14h2"/></svg>
                  </span>
                  Agenda de visitas
                </a>
                @endif
              </div>
            </div>
          </section>
          @endif

          @if($dashCanMenuGroup(2))
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
                @if($dashCanMenuItem('users'))
                <a href="{{ route('users') }}" data-route="users" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </span>
                  Usuarios
                </a>
                @endif

                @if($dashCanMenuItem('rbac'))
                <a href="{{ route('rbac') }}" data-route="rbac" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                  </span>
                  Roles & Permisos
                </a>
                @endif

              </div>
            </div>
          </section>
          @endif

          <!-- Grupo 7: MLS -->
          @if($dashCanMenuGroup(7))
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-7" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-7" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M7 21V5l10-2v18"/><path d="M17 7h3v14"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/><path d="M14 9h1"/><path d="M14 13h1"/><path d="M14 17h1"/></svg>
                <span class="text-sm font-medium">MLS</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-7" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                @if($dashCanMenuItem('easybroker.mls-export'))
                <a href="{{ route('easybroker.mls-export') }}" data-route="easybroker.mls-export" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/></svg>
                  </span>
                  MLS -> EasyBroker
                </a>
                @endif

                @if($dashCanMenuItem('mls'))
                <a href="{{ route('mls') }}" data-route="mls" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
                  </span>
                  MLS AMPI Sync
                </a>
                @endif

                @if($dashCanMenuItem('mls-agents'))
                <a href="{{ route('mls-agents') }}" data-route="mls-agents" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </span>
                  Agentes MLS
                </a>
                @endif

                @if($dashCanMenuItem('mls-offices'))
                <a href="{{ route('mls-offices') }}" data-route="mls-offices" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/></svg>
                  </span>
                  Agencias MLS
                </a>
                @endif
              </div>
            </div>
          </section>
          @endif

          <!-- Grupo 5: Correos -->
          @if($dashCanMenuGroup(5))
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-5" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-5" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                <span class="text-sm font-medium">Correos</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-5" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                @if($dashCanMenuItem('corporate-email.configuration'))
                <a href="{{ route('corporate-email.configuration') }}" data-route="corporate-email.configuration" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  </span>
                  Configuracion
                </a>
                @endif

                @if($dashCanMenuItem('corporate-email.inbox'))
                <a href="{{ route('corporate-email.inbox') }}" data-route="corporate-email.inbox" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="M3 13h5l2 3h4l2-3h5"/></svg>
                  </span>
                  Bandeja de entrada
                </a>
                @endif

                @if($dashCanMenuItem('corporate-email.outbox'))
                <a href="{{ route('corporate-email.outbox') }}" data-route="corporate-email.outbox" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="M3 9l9 6 9-6"/><path d="m16 3 3 3-3 3"/></svg>
                  </span>
                  Bandeja de salida
                </a>
                @endif

                @if($dashCanMenuItem('corporate-email.compose'))
                <a href="{{ route('corporate-email.compose') }}" data-route="corporate-email.compose" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/><path d="m3 7 7 5 7-5"/></svg>
                  </span>
                  Redactar
                </a>
                @endif

              </div>
            </div>
          </section>

          <!-- Grupo 4: CMS (Contenido Administrable) -->
          @endif

          @if($dashCanMenuGroup(4))
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
                @if($dashCanMenuItem('cms.pages'))
                <a href="{{ route('cms.pages') }}" data-route="cms.pages" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </span>
                  Páginas
                </a>

                @endif

                @if($dashCanMenuItem('cms.posts'))
                <a href="{{ route('cms.posts') }}" data-route="cms.posts" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  </span>
                  Blog / Posts
                </a>

                @endif

                @if($dashCanMenuItem('cms.menus'))
                <a href="{{ route('cms.menus') }}" data-route="cms.menus" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                  </span>
                  Menús
                </a>

                @endif

                @if($dashCanMenuItem('cms.settings'))
                <a href="{{ route('cms.settings') }}" data-route="cms.settings" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                  </span>
                  Configuración
                </a>
                @endif
              </div>
            </div>
          </section>
          @endif

          @if($dashCanMenuGroup(8))
          <section class="rounded-2xl overflow-hidden ring-1 ring-[var(--c-border)]">
            <button id="dash-acc-btn-8" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80 transition" aria-controls="dash-acc-panel-8" aria-expanded="false">
              <span class="flex items-center gap-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 8.5v7a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 15.5v-7A2.5 2.5 0 0 1 4.5 6h15A2.5 2.5 0 0 1 22 8.5Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>
                <span class="text-sm font-medium">Ayuda interna</span>
              </span>
              <svg class="size-4 -rotate-90 transition-transform" id="dash-acc-icon-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div id="dash-acc-panel-8" class="hidden cv-auto">
              <div class="p-2 bg-[var(--c-surface)] min-h-0">
                @if($dashCanMenuItem('manual'))
                <a href="{{ route('manual') }}" data-route="manual" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="M8 7h8"/><path d="M8 11h6"/></svg>
                  </span>
                  Manual de uso
                </a>
                @endif

                @if($dashCanMenuItem('tutorials'))
                <a href="{{ route('tutorials') }}" data-route="tutorials" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 8.5v7a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 15.5v-7A2.5 2.5 0 0 1 4.5 6h15A2.5 2.5 0 0 1 22 8.5Z"/><path d="m10 9 5 3-5 3V9Z"/></svg>
                  </span>
                  Videotutoriales
                </a>
                @endif

                @if($dashCanMenuItem('manual-articles'))
                <a href="{{ route('manual-articles') }}" data-route="manual-articles" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/><path d="M4 8h7"/><path d="M4 12h4"/></svg>
                  </span>
                  Administrar manual
                </a>
                @endif

                @if($dashCanMenuItem('tutorial-videos'))
                <a href="{{ route('tutorial-videos') }}" data-route="tutorial-videos" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/><path d="m10 8 5 3-5 3V8Z"/></svg>
                  </span>
                  Administrar videos
                </a>
                @endif
              </div>
            </div>
          </section>
          @endif

          <!-- Grupo 3: Ajustes -->

          @if($dashCanMenuGroup(3))
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
                @if($dashCanMenuItem('currencies'))
                <a href="{{ route('currencies') }}" data-route="currencies" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                  </span>
                  Monedas
                </a>
                @endif

                @if($dashCanMenuItem('color-themes'))
                <a href="{{ route('color-themes') }}" data-route="color-themes" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="19" cy="17" r="2"/><circle cx="6" cy="17" r="3"/><path d="M9 17a4 4 0 0 1 7 0"/><path d="M15 8.5a6.5 6.5 0 0 1 4 6"/></svg>
                  </span>
                  Temas de Color
                </a>
                @endif

                @if($dashCanMenuItem('frontend-colors'))
                <a href="{{ route('frontend-colors') }}" data-route="frontend-colors" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>
                  </span>
                  Colores Frontend
                </a>
                @endif

                @if($dashCanMenuItem('easybroker'))
                <a href="{{ route('easybroker') }}" data-route="easybroker" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  </span>
                  EasyBroker Sync
                </a>
                @endif

                @if($dashCanMenuItem('notifications'))
                <a href="{{ route('notifications') }}" data-route="notifications" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--c-elev)] transition text-sm">
                  <span class="size-8 grid place-items-center rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M10.3 21a1.7 1.7 0 0 0 3.4 0"/></svg>
                  </span>
                  Notificaciones
                </a>
                @endif
              </div>
            </div>
          </section>
          @endif
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
    function normalizeDisplayCurrencyCode(currencyCode) {
      const code = String(currencyCode || '').trim().toUpperCase();
      if (code === 'MXN' || code === 'USD') return code;
      return code || 'MXN';
    }

    function toDisplayAmount(amount) {
      if (amount === null || amount === undefined || amount === '') return null;
      if (typeof amount === 'number') return Number.isFinite(amount) ? amount : null;

      const normalized = String(amount).replace(/,/g, '').trim();
      const parsed = Number.parseFloat(normalized);
      return Number.isFinite(parsed) ? parsed : null;
    }

    if (typeof window.formatDisplayPrice !== 'function') {
      window.formatDisplayPrice = function formatDisplayPrice(amount, currencyCode = 'MXN') {
        const numeric = toDisplayAmount(amount);
        if (numeric === null) return '';

        const rounded = Math.round((numeric + Number.EPSILON) * 100) / 100;
        const code = normalizeDisplayCurrencyCode(currencyCode);
        const hasCents = Math.abs(rounded - Math.trunc(rounded)) > 0.00001;
        const fixed = rounded.toFixed(hasCents ? 2 : 0);
        const [integer, decimals] = fixed.split('.');
        const integerWithThousands = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const decimalSuffix = decimals ? `.${decimals}` : '';
        const symbol = (code === 'MXN' || code === 'USD') ? '$' : '';

        return `${symbol}${integerWithThousands}${decimalSuffix} ${code}`.trim();
      };
    }

    (function(){
      // --- Ruta actual ---
      const currentRoute = '{{ request()->route()->getName() }}';

      // --- Mapa de rutas a grupos ---
      const routeToGroup = {
        'dashboard': 1,
        'properties': 1,
        'zones': 1,
        'clients': 6,
        'clients.show': 6,
        'property-contact-requests': 6,
        'calendar': 6,
        'users': 2,
        'currencies': 3,
        'color-themes': 3,
        'frontend-colors': 3,
        'rbac': 2,
        'easybroker': 3,
        'easybroker.mls-export': 7,
        'mls': 7,
        'mls-agents': 7,
        'mls-offices': 7,
        'corporate-email.configuration': 5,
        'corporate-email.inbox': 5,
        'corporate-email.outbox': 5,
        'corporate-email.compose': 5,
        'cms.pages': 4,
        'cms.posts': 4,
        'cms.menus': 4,
        'cms.settings': 4,
        'manual': 8,
        'manual-articles': 8,
        'tutorials': 8,
        'tutorial-videos': 8,
        'notifications': 3,
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
        { btn: 'dash-acc-btn-6', panel: 'dash-acc-panel-6', icon: 'dash-acc-icon-6', defaultOpen: currentGroup === 6 },
        { btn: 'dash-acc-btn-2', panel: 'dash-acc-panel-2', icon: 'dash-acc-icon-2', defaultOpen: currentGroup === 2 },
        { btn: 'dash-acc-btn-7', panel: 'dash-acc-panel-7', icon: 'dash-acc-icon-7', defaultOpen: currentGroup === 7 },
        { btn: 'dash-acc-btn-5', panel: 'dash-acc-panel-5', icon: 'dash-acc-icon-5', defaultOpen: currentGroup === 5 },
        { btn: 'dash-acc-btn-4', panel: 'dash-acc-panel-4', icon: 'dash-acc-icon-4', defaultOpen: currentGroup === 4 },
        { btn: 'dash-acc-btn-8', panel: 'dash-acc-panel-8', icon: 'dash-acc-icon-8', defaultOpen: currentGroup === 8 },
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
        if(route === currentRoute || (route === 'clients' && currentRoute.startsWith('clients.'))){
          link.classList.add('active');
        }
      });

    })();

    (function(){
      const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const root = document.getElementById('dash-notifications');
      const bell = document.getElementById('dash-bell');
      const badge = document.getElementById('dash-notification-badge');
      const panel = document.getElementById('dash-notification-panel');
      const list = document.getElementById('dash-notification-list');
      const summary = document.getElementById('dash-notification-summary');
      const readAll = document.getElementById('dash-notification-read-all');
      const refresh = document.getElementById('dash-notification-refresh');

      if (!token || !root || !bell || !badge || !panel || !list) {
        return;
      }

      let isOpen = false;
      let isLoading = false;

      const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

      const request = async (url, options = {}) => {
        const response = await fetch(url, {
          ...options,
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': csrf,
            ...(options.headers || {}),
          },
        });

        return response.json().catch(() => ({
          success: false,
          message: response.statusText || 'Error cargando notificaciones',
        }));
      };

      const updateBadge = (count) => {
        const unread = Number(count || 0);
        badge.textContent = unread > 99 ? '99+' : String(unread);
        badge.classList.toggle('hidden', unread <= 0);
        bell.classList.toggle('text-[var(--c-primary)]', unread > 0);
        bell.setAttribute('aria-label', unread > 0 ? `${unread} notificaciones sin leer` : 'Notificaciones');
        if (summary) {
          summary.textContent = unread > 0 ? `${unread} sin leer` : 'Todo al dia';
        }
      };

      const renderNotifications = (items) => {
        if (!items.length) {
          list.innerHTML = '<div class="p-4 text-sm text-[var(--c-muted)]">No tienes notificaciones.</div>';
          return;
        }

        list.innerHTML = items.map((item) => {
          const unreadClass = item.read_at ? '' : 'bg-[var(--c-elev)]/70';
          const createdAt = item.created_at_human || item.created_at || '';
          const url = item.action_url || '#';

          return `
            <button type="button" data-notification-id="${escapeHtml(item.id)}" data-notification-url="${escapeHtml(url)}" class="dash-notification-item flex w-full gap-3 border-b border-[var(--c-border)] px-4 py-3 text-left last:border-b-0 hover:bg-[var(--c-elev)] ${unreadClass}">
              <span class="mt-1 size-2 shrink-0 rounded-full ${item.read_at ? 'bg-[var(--c-border)]' : 'bg-[var(--c-primary)]'}"></span>
              <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-[var(--c-text)]">${escapeHtml(item.title)}</span>
                <span class="mt-1 block text-xs leading-5 text-[var(--c-muted)]">${escapeHtml(item.message)}</span>
                <span class="mt-2 block text-[11px] font-semibold text-[var(--c-muted)]">${escapeHtml(createdAt)}</span>
              </span>
            </button>
          `;
        }).join('');
      };

      const loadNotifications = async () => {
        if (isLoading) return;
        isLoading = true;

        const payload = await request('/api/notifications?per_page=8');
        isLoading = false;

        if (!payload?.success) {
          list.innerHTML = `<div class="p-4 text-sm text-red-600">${escapeHtml(payload?.message || 'No se pudieron cargar las notificaciones.')}</div>`;
          return;
        }

        updateBadge(payload.unread_count || 0);
        renderNotifications(payload.data?.data || []);
      };

      const markAsRead = async (id) => {
        if (!id) return null;

        const payload = await request(`/api/notifications/${encodeURIComponent(id)}`, {
          method: 'PATCH',
        });

        if (payload?.success) {
          updateBadge(payload.unread_count || 0);
        }

        return payload;
      };

      const setOpen = (open) => {
        isOpen = open;
        panel.classList.toggle('hidden', !open);
        bell.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open) {
          loadNotifications();
        }
      };

      bell.addEventListener('click', (event) => {
        event.stopPropagation();
        setOpen(!isOpen);
      });

      panel.addEventListener('click', async (event) => {
        const item = event.target.closest('.dash-notification-item');
        if (!item) return;

        const id = item.getAttribute('data-notification-id');
        const url = item.getAttribute('data-notification-url') || '#';
        await markAsRead(id);

        if (url && url !== '#') {
          window.location.href = url;
        } else {
          await loadNotifications();
        }
      });

      readAll?.addEventListener('click', async (event) => {
        event.stopPropagation();
        await request('/api/notifications/read-all', { method: 'PATCH' });
        await loadNotifications();
      });

      refresh?.addEventListener('click', (event) => {
        event.stopPropagation();
        loadNotifications();
      });

      document.addEventListener('click', (event) => {
        if (isOpen && !root.contains(event.target)) {
          setOpen(false);
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
          setOpen(false);
        }
      });

      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          loadNotifications();
        }
      });

      loadNotifications();
      window.setInterval(loadNotifications, 60000);
    })();
  </script>
</body>
</html>
