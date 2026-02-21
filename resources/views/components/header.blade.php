<!-- ===== HEADER / TOPBAR ===== -->
<header id="dash-header" class="bg-[var(--c-bg)]/80 backdrop-blur border-b border-[var(--c-border)] flex items-center">
  <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 w-full">
    <!-- Botón hamburguesa (móvil) -->
    <button id="dash-menu-btn" class="lg:hidden inline-flex items-center gap-2 px-3 py-2 rounded-xl ring-1 ring-[var(--c-border)]">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/></svg>
      <span class="text-sm">Menú</span>
    </button>

    <!-- Migas / Título -->
    <div class="hidden sm:flex items-center gap-3 text-sm w-full min-w-0">
      @php
        $currentRoute = Route::currentRouteName();
        $breadcrumbs = [
          'dashboard' => [['Inicio', route('dashboard')], ['Dashboard', '#']],
          'funnel' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Funnel', '#']],
          'properties' => [['Inicio', route('dashboard')], ['Inmobiliaria', '#'], ['Propiedades', '#']],
          'users' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Usuarios', '#']],
          'rbac' => [['Inicio', route('dashboard')], ['Admin', '#'], ['RBAC', '#']],
          'currencies' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Monedas', '#']],
          'color-themes' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Temas de Color', '#']],
          'frontend-colors' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Colores Frontend', '#']],
          'mls-agents' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Agentes MLS', '#']],
          'mls-offices' => [['Inicio', route('dashboard')], ['Admin', '#'], ['Agencias MLS', '#']],
        ];
        $crumbs = $breadcrumbs[$currentRoute] ?? [['Inicio', '/'], ['Página', '#']];
      @endphp

      <div class="flex items-center gap-3 min-w-0">
        <div class="size-9 rounded-2xl grid place-items-center bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3l9 7.5"/>
            <path d="M12 3 3 10.5"/>
            <path d="M5 10v11h14V10"/>
            <path d="M10 21v-6h4v6"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-xs text-[var(--c-muted)] leading-tight">Sistema inmobiliario</p>
          <p class="text-sm font-semibold leading-tight truncate">
            {{ $crumbs[count($crumbs)-1][0] ?? 'Panel' }}
          </p>
        </div>
      </div>

      <span class="h-6 w-px bg-[var(--c-border)]"></span>

      @foreach($crumbs as $index => $crumb)
        @if($index > 0)
          <span class="opacity-50">/</span>
        @endif
        @if($crumb[1] !== '#')
          <a href="{{ $crumb[1] }}" class="text-[var(--c-muted)] hover:text-[var(--c-text)]">{{ $crumb[0] }}</a>
        @else
          <span class="font-medium">{{ $crumb[0] }}</span>
        @endif
      @endforeach
    </div>

    <!-- Acciones derechas -->
    <div class="flex items-center justify-end gap-2 sm:gap-3 ml-auto w-full">
      <div class="hidden md:flex items-center gap-2 rounded-2xl bg-[var(--c-elev)] px-3 py-2 ring-1 ring-[var(--c-border)] focus-within:ring-[var(--c-primary)]">
        <svg class="size-5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input id="dash-top-search" type="search" placeholder="Buscar en todo…" class="bg-transparent outline-none w-64 text-sm placeholder:text-[var(--c-muted)]" />
      </div>

      <div class="hidden sm:flex items-center gap-2">
        <button id="dash-action-new" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95 shadow-soft">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
          Nueva propiedad
        </button>
        <button class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded-xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h2"/><path d="M12 14h2"/></svg>
          Agendar visita
        </button>
      </div>

      <button id="dash-bell" class="inline-flex size-10 items-center justify-center rounded-xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.7 1.7 0 0 0 3.4 0"/></svg>
      </button>
      <button id="dash-avatar" class="inline-flex items-center gap-3 px-2 py-1 rounded-xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">
        @php
          $user = auth()->user()->load('profileImage');
        @endphp
        @if($user && $user->profileImage)
          <img alt="avatar" src="{{ $user->profileImage->serving_url ?? $user->profileImage->url }}" class="size-8 rounded-lg"/>
        @else
          <svg class="size-8 text-[var(--c-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        @endif
        <span class="hidden sm:block text-sm">{{ $user ? $user->name : 'Usuario' }}</span>
      </button>
    </div>
  </div>
</header>
