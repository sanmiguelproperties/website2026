@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $user = Auth::user();
@endphp

<!-- ========================= HERO / RESUMEN ========================= -->
<section class="relative overflow-hidden rounded-3xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)]">
  <div class="absolute inset-0 opacity-60" style="background:
    radial-gradient(800px 260px at 12% 0%, var(--c-primary) 0%, transparent 55%),
    radial-gradient(900px 320px at 90% 10%, var(--c-accent) 0%, transparent 55%);
  "></div>
  <div class="relative p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center gap-6">
    <div class="min-w-0">
      <p class="text-xs text-[var(--c-muted)]">Panel inmobiliario</p>
      <h2 class="text-xl sm:text-2xl font-semibold leading-tight">
        Bienvenido{{ $user ? ", " . $user->name : '' }}
      </h2>
      <p class="mt-1 text-sm text-[var(--c-muted)] max-w-2xl">
        Vista general de propiedades, leads y actividad reciente. Los módulos se mantienen igual, solo mejora el diseño y legibilidad.
      </p>

      <div class="mt-5 flex flex-wrap items-center gap-2">
        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] shadow-soft hover:opacity-95">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
          Publicar propiedad
        </a>
        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)] hover:bg-[var(--c-elev)]/80">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Registrar cliente
        </a>
        <a href="{{ route('funnel') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 4h18l-7 8v7l-4 2v-9L3 4z"/></svg>
          Ver funnel
        </a>
      </div>
    </div>

    <div class="lg:ml-auto grid grid-cols-2 gap-3 w-full lg:w-auto">
      <div class="rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)] p-4">
        <p class="text-xs text-[var(--c-muted)]">Leads nuevos</p>
        <p class="mt-1 text-2xl font-semibold">12</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Últimas 24h</p>
      </div>
      <div class="rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-elev)] p-4">
        <p class="text-xs text-[var(--c-muted)]">Visitas hoy</p>
        <p class="mt-1 text-2xl font-semibold">3</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Agendadas</p>
      </div>
    </div>
  </div>
</section>

<!-- ========================= KPIs ========================= -->
<section class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
  @php
    $kpis = [
      ['label' => 'Propiedades activas', 'value' => '48', 'hint' => 'Publicadas', 'icon' => 'home'],
      ['label' => 'En venta', 'value' => '31', 'hint' => 'Disponibles', 'icon' => 'tag'],
      ['label' => 'En arriendo', 'value' => '17', 'hint' => 'Disponibles', 'icon' => 'key'],
      ['label' => 'Cierres del mes', 'value' => '5', 'hint' => 'Contratos firmados', 'icon' => 'file'],
    ];
  @endphp

  @foreach($kpis as $kpi)
    <div class="rounded-3xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs text-[var(--c-muted)]">{{ $kpi['label'] }}</p>
          <p class="mt-1 text-3xl font-semibold tracking-tight">{{ $kpi['value'] }}</p>
          <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $kpi['hint'] }}</p>
        </div>
        <div class="size-11 rounded-2xl grid place-items-center bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">
          @if($kpi['icon'] === 'home')
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v11h14V10"/><path d="M9 21v-6h6v6"/></svg>
          @elseif($kpi['icon'] === 'tag')
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.59 13.41 12 22 2 12l8.59-8.59A2 2 0 0 1 12 2h6a2 2 0 0 1 2 2v6a2 2 0 0 1-.59 1.41z"/><circle cx="17" cy="7" r="1"/></svg>
          @elseif($kpi['icon'] === 'key')
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 2l-2 2"/><path d="M7 20l-1 1"/><path d="M4 17l-1 1"/><path d="M10.5 13.5a4.5 4.5 0 1 1 1-7.8L21 2l1 1-3.7 9.5a4.5 4.5 0 0 1-7.8 1z"/></svg>
          @else
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h8"/></svg>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</section>

<!-- ========================= GRID PRINCIPAL ========================= -->
<section class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-4">
  <!-- Actividad reciente -->
  <div class="xl:col-span-1 rounded-3xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] shadow-soft overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)] flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold">Actividad reciente</p>
        <p class="text-xs text-[var(--c-muted)]">Movimientos del sistema</p>
      </div>
      <button class="text-xs px-3 py-2 rounded-xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">Ver todo</button>
    </div>

    <ul class="divide-y divide-[var(--c-border)]">
      @php
        $activity = [
          ['t' => 'Se publicó “Apartamento Laureles 302”', 's' => 'hace 12 min', 'k' => 'primary'],
          ['t' => 'Nuevo lead: “Carlos M.” (compra)', 's' => 'hace 1 h', 'k' => 'accent'],
          ['t' => 'Visita agendada: Casa Campestre • 4:30 PM', 's' => 'hoy', 'k' => 'muted'],
          ['t' => 'Contrato generado: Arriendo • Local 12', 's' => 'ayer', 'k' => 'muted'],
        ];
      @endphp

      @foreach($activity as $row)
        <li class="p-5 flex items-start gap-3">
          <span class="mt-1 size-2 rounded-full"
            style="background: {{ $row['k']==='primary' ? 'var(--c-primary)' : ($row['k']==='accent' ? 'var(--c-accent)' : 'var(--c-border)') }}"
            aria-hidden="true"></span>
          <div class="min-w-0">
            <p class="text-sm leading-snug">{{ $row['t'] }}</p>
            <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $row['s'] }}</p>
          </div>
        </li>
      @endforeach
    </ul>
  </div>

  <!-- Propiedades destacadas / tabla -->
  <div class="xl:col-span-2 rounded-3xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] shadow-soft overflow-hidden">
    <div class="p-5 border-b border-[var(--c-border)] flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
      <div>
        <p class="text-sm font-semibold">Propiedades</p>
        <p class="text-xs text-[var(--c-muted)]">Listado rápido (mock) para diseño</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="text-xs px-3 py-2 rounded-xl ring-1 ring-[var(--c-border)] hover:ring-[var(--c-primary)]">Filtrar</button>
        <button class="text-xs px-3 py-2 rounded-xl bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:opacity-95">Nueva</button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-[var(--c-elev)] text-[var(--c-muted)]">
          <tr>
            <th class="text-left font-medium px-5 py-3">Propiedad</th>
            <th class="text-left font-medium px-5 py-3">Tipo</th>
            <th class="text-left font-medium px-5 py-3">Estado</th>
            <th class="text-right font-medium px-5 py-3">Precio</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--c-border)]">
          @php
            $rows = [
              ['name' => 'Apartamento Laureles 302', 'type' => 'Venta', 'status' => 'Activo', 'price' => '$ 420.000.000'],
              ['name' => 'Casa Campestre • Envigado', 'type' => 'Arriendo', 'status' => 'Visitas', 'price' => '$ 4.800.000 / mes'],
              ['name' => 'Local Comercial • Centro', 'type' => 'Venta', 'status' => 'En negociación', 'price' => '$ 690.000.000'],
              ['name' => 'Apartaestudio • El Poblado', 'type' => 'Arriendo', 'status' => 'Activo', 'price' => '$ 2.300.000 / mes'],
            ];
          @endphp

          @foreach($rows as $r)
            <tr class="hover:bg-[var(--c-elev)]/70 transition">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="size-10 rounded-2xl bg-[var(--c-elev)] ring-1 ring-[var(--c-border)] grid place-items-center">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v11h14V10"/><path d="M9 21v-6h6v6"/></svg>
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium truncate">{{ $r['name'] }}</p>
                    <p class="text-xs text-[var(--c-muted)]">Medellín • CO</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4 text-[var(--c-muted)]">{{ $r['type'] }}</td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full ring-1 ring-[var(--c-border)] bg-[var(--c-elev)]">
                  <span class="size-2 rounded-full" style="background: {{ $r['status']==='Activo' ? 'var(--c-accent)' : ($r['status']==='Visitas' ? 'var(--c-primary)' : 'var(--c-border)') }}" aria-hidden="true"></span>
                  <span class="text-xs">{{ $r['status'] }}</span>
                </span>
              </td>
              <td class="px-5 py-4 text-right font-medium">{{ $r['price'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
