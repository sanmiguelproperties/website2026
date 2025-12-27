@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="relative min-h-screen overflow-hidden">
  <!-- Fondo decorativo (gradiente + manchas difusas) -->
  <div class="pointer-events-none absolute inset-0">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-emerald-50"></div>
    <div class="absolute -top-32 -left-32 h-[420px] w-[420px] rounded-full bg-indigo-200/50 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-32 h-[520px] w-[520px] rounded-full bg-emerald-200/50 blur-3xl"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.06)_1px,transparent_0)] [background-size:22px_22px]"></div>
  </div>

  <div class="relative mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid w-full grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">

      <!-- Panel de marca (solo desktop) -->
      <div class="hidden lg:flex">
        <div class="w-full rounded-3xl border border-white/60 bg-white/40 p-10 shadow-soft backdrop-blur">
          <div class="flex items-center gap-3">
            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-emerald-500 text-white shadow-soft">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 21h18" />
                <path d="M6 21V7a2 2 0 0 1 2-2h3" />
                <path d="M11 21V11a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v10" />
                <path d="M9 9h2" />
                <path d="M9 13h2" />
                <path d="M9 17h2" />
                <path d="M15 13h2" />
                <path d="M15 17h2" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold tracking-wide text-slate-900">San Miguel Properties</p>
              <p class="text-sm text-slate-600">Portal inmobiliario • Acceso al panel</p>
            </div>
          </div>

          <div class="mt-10 space-y-6">
            <h1 class="text-3xl font-semibold leading-tight text-slate-900">
              Gestiona propiedades, leads y tu operación
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500">en un solo lugar</span>.
            </h1>
            <p class="text-slate-600">
              Inicia sesión para acceder al panel de administración del portal inmobiliario.
              Mantén tu catálogo actualizado y responde a solicitudes con rapidez.
            </p>

            <div class="grid grid-cols-1 gap-3">
              <div class="flex items-start gap-3 rounded-2xl border border-white/60 bg-white/50 p-4">
                <div class="mt-0.5 grid h-9 w-9 place-items-center rounded-xl bg-indigo-600/10 text-indigo-700">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z" />
                    <path d="M9 12l2 2 4-4" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">Acceso seguro</p>
                  <p class="text-sm text-slate-600">Sesión protegida con tokens y control de permisos.</p>
                </div>
              </div>
              <div class="flex items-start gap-3 rounded-2xl border border-white/60 bg-white/50 p-4">
                <div class="mt-0.5 grid h-9 w-9 place-items-center rounded-xl bg-emerald-600/10 text-emerald-700">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3v18h18" />
                    <path d="M7 14v4" />
                    <path d="M11 10v8" />
                    <path d="M15 6v12" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">Operación centralizada</p>
                  <p class="text-sm text-slate-600">Propiedades, embudo y administración desde un mismo panel.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulario -->
      <div class="flex items-center">
        <div class="w-full">
          <div class="mx-auto w-full max-w-md rounded-3xl border border-white/70 bg-white/70 p-6 shadow-soft backdrop-blur sm:p-8">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Iniciar sesión</h2>
                <p class="mt-1 text-sm text-slate-600">Accede al panel de tu portal inmobiliario.</p>
              </div>
              <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-900/5">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M15 18l-6-6 6-6" />
                </svg>
                Sitio
              </a>
            </div>

            @if ($errors->any())
              <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" aria-live="polite">
                <p class="font-medium">No pudimos iniciar sesión</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form class="mt-6 space-y-5" action="{{ route('login') }}" method="POST">
              @csrf

              <!-- Checkbox compatible con boolean() del backend -->
              <input type="hidden" name="remember" value="0" />

              <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
                <div class="mt-2 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <path d="M4 6h16" />
                      <path d="M4 6l8 7 8-7" />
                      <path d="M4 6v12h16V6" />
                    </svg>
                  </span>
                  <input
                    id="email"
                    name="email"
                    type="email"
                    inputmode="email"
                    autocomplete="email"
                    required
                    autofocus
                    value="{{ old('email') }}"
                    placeholder="correo@ejemplo.com"
                    aria-invalid="@error('email') true @else false @enderror"
                    class="block w-full rounded-2xl border border-slate-200 bg-white/80 py-3 pl-11 pr-3 text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"
                  >
                </div>
                @error('email')
                  <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                <div class="mt-2 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                      <path d="M17 8V7a5 5 0 0 0-10 0v1" />
                      <path d="M6 8h12v12H6z" />
                    </svg>
                  </span>
                  <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                    aria-invalid="@error('password') true @else false @enderror"
                    class="block w-full rounded-2xl border border-slate-200 bg-white/80 py-3 pl-11 pr-3 text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"
                  >
                </div>
                @error('password')
                  <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>

              <div class="flex items-center justify-between gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                  <input id="remember-me" name="remember" value="1" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                  Recordarme
                </label>

                @if (\Illuminate\Support\Facades\Route::has('password.request'))
                  <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-600">¿Olvidaste tu contraseña?</a>
                @else
                  <span class="text-sm text-slate-400" title="Ruta no disponible">¿Olvidaste tu contraseña?</span>
                @endif
              </div>

              <button type="submit" class="group inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-soft transition hover:opacity-95 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                <svg class="h-5 w-5 opacity-90" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                Iniciar sesión
              </button>

              <p class="text-center text-xs text-slate-500">
                Al continuar aceptas el uso interno del sistema para gestión inmobiliaria.
              </p>
            </form>
          </div>

          <!-- Marca en mobile -->
          <div class="mx-auto mt-6 flex max-w-md items-center justify-center gap-2 text-sm text-slate-600 lg:hidden">
            <span class="font-semibold text-slate-900">San Miguel Properties</span>
            <span class="text-slate-400">•</span>
            <span>Panel inmobiliario</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
