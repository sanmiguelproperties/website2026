@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $user = $dashboardUser ?? Auth::user();
  $stats = $dashboardStats ?? [];
  $permissions = $dashboardPermissions ?? [];
  $stat = static fn (string $key): int|float => $stats[$key] ?? 0;
  $number = static fn (int|float $value): string => number_format($value);
  $percent = static fn (int|float $value): string => rtrim(rtrim(number_format((float) $value, 1), '0'), '.') . '%';
  $scope = $dashboardScope ?? [
      'label' => 'Tus datos',
      'description' => 'Metricas asociadas a tu usuario.',
      'is_global' => false,
  ];

  $operationLabel = static function ($property): string {
      $labels = $property->operations
          ->pluck('operation_type')
          ->filter()
          ->map(fn ($type) => match ($type) {
              'sale' => 'Venta',
              'rent', 'rental' => 'Renta',
              default => ucfirst(str_replace('_', ' ', (string) $type)),
          })
          ->unique()
          ->values()
          ->join(' / ');

      return $labels !== '' ? $labels : ((bool) $property->for_rent ? 'Renta' : 'Venta');
  };

  $propertyPrice = static function ($property): string {
      $operation = $property->operations->first();

      return $operation?->formatted_amount ?: 'Sin precio';
  };

  $statusLabel = static fn (?string $status): string => match ($status) {
      'new' => 'Nuevo',
      'pending_assignment' => 'Pendiente',
      'contacted' => 'Contactado',
      'qualified' => 'Calificado',
      'converted' => 'Convertido',
      'closed' => 'Cerrado',
      default => $status ? ucfirst(str_replace('_', ' ', $status)) : 'Sin estado',
  };

  $visitStatusLabel = static fn (?string $status): string => match ($status) {
      \App\Models\ClientVisit::STATUS_SCHEDULED => 'Pautada',
      \App\Models\ClientVisit::STATUS_COMPLETED => 'Realizada',
      \App\Models\ClientVisit::STATUS_CANCELLED => 'Cancelada',
      default => 'Sin estado',
  };

  $primaryKpis = [
      [
          'label' => 'Propiedades publicadas',
          'value' => $number($stat('properties_published')),
          'hint' => $number($stat('properties_total')) . ' en inventario',
          'icon' => 'home',
      ],
      [
          'label' => 'Leads totales',
          'value' => $number($stat('leads_total')),
          'hint' => $number($stat('leads_this_month')) . ' este mes',
          'icon' => 'lead',
      ],
      [
          'label' => 'Clientes activos',
          'value' => $number($stat('clients_active')),
          'hint' => $number($stat('clients_this_month')) . ' nuevos este mes',
          'icon' => 'client',
      ],
      [
          'label' => 'Conversion de leads',
          'value' => $percent($stat('conversion_rate')),
          'hint' => $number($stat('leads_converted')) . ' convertidos',
          'icon' => 'trend',
      ],
  ];

  $secondaryKpis = [
      ['label' => 'Leads hoy', 'value' => $number($stat('leads_today')), 'hint' => 'Captados hoy'],
      ['label' => 'Leads pendientes', 'value' => $number($stat('leads_pending')), 'hint' => 'Sin asignacion'],
      ['label' => 'Visitas hoy', 'value' => $number($stat('visits_today')), 'hint' => 'En agenda'],
      ['label' => 'Visitas proximas', 'value' => $number($stat('visits_upcoming')), 'hint' => 'Pendientes'],
      ['label' => 'Propiedades en venta', 'value' => $number($stat('properties_for_sale')), 'hint' => 'Operacion venta'],
      ['label' => 'Propiedades en renta', 'value' => $number($stat('properties_for_rent')), 'hint' => 'Operacion renta'],
      ['label' => 'Visitas del mes', 'value' => $number($stat('visits_this_month')), 'hint' => 'Total mensual'],
      ['label' => 'Visitas realizadas', 'value' => $number($stat('visits_completed_this_month')), 'hint' => 'Este mes'],
  ];
@endphp

<div class="space-y-6">
  <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft sm:p-6">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <span class="inline-flex rounded-full border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-1 text-xs font-semibold text-[var(--c-muted)]">
            {{ $scope['label'] }}
          </span>
          @if($scope['is_global'])
            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">Superadmin</span>
          @endif
        </div>
        <h1 class="mt-3 text-2xl font-bold leading-tight text-[var(--c-text)] sm:text-3xl">
          Dashboard{{ $user ? ', ' . $user->name : '' }}
        </h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--c-muted)]">
          {{ $scope['description'] }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @if($permissions['can_create_property'] ?? false)
          <a href="{{ route('properties', ['action' => 'create']) }}" class="inline-flex items-center gap-2 rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] shadow-soft hover:opacity-95">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Nueva propiedad
          </a>
        @endif
        @if($permissions['can_view_leads'] ?? false)
          <a href="{{ route('property-contact-requests') }}" class="inline-flex items-center gap-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
            Ver leads
          </a>
        @endif
        @if($permissions['can_create_visit'] ?? false)
          <a href="{{ route('calendar', ['action' => 'create']) }}#new-visit" class="inline-flex items-center gap-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm font-semibold hover:bg-[var(--c-surface)]">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
            Agendar visita
          </a>
        @endif
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($primaryKpis as $kpi)
      <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="text-sm text-[var(--c-muted)]">{{ $kpi['label'] }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-[var(--c-text)]">{{ $kpi['value'] }}</p>
            <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $kpi['hint'] }}</p>
          </div>
          <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)]">
            @if($kpi['icon'] === 'home')
              <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v11h14V10"/><path d="M9 21v-6h6v6"/></svg>
            @elseif($kpi['icon'] === 'lead')
              <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
            @elseif($kpi['icon'] === 'client')
              <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            @else
              <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            @endif
          </span>
        </div>
      </article>
    @endforeach
  </section>

  <section class="grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-8">
    @foreach($secondaryKpis as $kpi)
      <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
        <p class="text-xs text-[var(--c-muted)]">{{ $kpi['label'] }}</p>
        <p class="mt-1 text-2xl font-bold text-[var(--c-text)]">{{ $kpi['value'] }}</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $kpi['hint'] }}</p>
      </article>
    @endforeach
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="border-b border-[var(--c-border)] p-5">
        <p class="text-sm font-semibold text-[var(--c-text)]">Leads por origen</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Canales que estan generando registros.</p>
      </div>
      <div class="space-y-4 p-5">
        @forelse($leadSources as $source)
          <div>
            <div class="flex items-center justify-between gap-3 text-sm">
              <span class="font-medium text-[var(--c-text)]">{{ $source['label'] }}</span>
              <span class="text-[var(--c-muted)]">{{ $number($source['total']) }}</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-[var(--c-elev)]">
              <div class="h-full rounded-full bg-[var(--c-primary)]" style="width: {{ $source['percentage'] }}%"></div>
            </div>
          </div>
        @empty
          <p class="text-sm text-[var(--c-muted)]">Aun no hay leads para medir.</p>
        @endforelse
      </div>
    </article>

    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="border-b border-[var(--c-border)] p-5">
        <p class="text-sm font-semibold text-[var(--c-text)]">Leads por tipo</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Intencion principal declarada por el contacto.</p>
      </div>
      <div class="space-y-4 p-5">
        @forelse($leadTypes as $type)
          <div>
            <div class="flex items-center justify-between gap-3 text-sm">
              <span class="font-medium text-[var(--c-text)]">{{ $type['label'] }}</span>
              <span class="text-[var(--c-muted)]">{{ $number($type['total']) }}</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-[var(--c-elev)]">
              <div class="h-full rounded-full bg-[var(--c-accent)]" style="width: {{ $type['percentage'] }}%"></div>
            </div>
          </div>
        @empty
          <p class="text-sm text-[var(--c-muted)]">Aun no hay tipos de lead para medir.</p>
        @endforelse
      </div>
    </article>

    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="border-b border-[var(--c-border)] p-5">
        <p class="text-sm font-semibold text-[var(--c-text)]">Propiedades con mas leads</p>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Inventario que concentra mayor demanda.</p>
      </div>
      <div class="divide-y divide-[var(--c-border)]">
        @forelse($topLeadProperties as $row)
          <div class="flex items-center justify-between gap-4 p-5">
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-[var(--c-text)]">{{ $row->property?->title ?: 'Propiedad #' . $row->property_id }}</p>
              <p class="mt-1 text-xs text-[var(--c-muted)]">ID interno: {{ $row->property_id }}</p>
            </div>
            <span class="rounded-full border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-1 text-xs font-semibold">{{ $number((int) $row->leads_count) }}</span>
          </div>
        @empty
          <div class="p-5">
            <p class="text-sm text-[var(--c-muted)]">Aun no hay leads asociados a propiedades.</p>
          </div>
        @endforelse
      </div>
    </article>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft xl:col-span-1">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] p-5">
        <div>
          <p class="text-sm font-semibold text-[var(--c-text)]">Actividad reciente</p>
          <p class="mt-1 text-xs text-[var(--c-muted)]">Eventos reales del CRM.</p>
        </div>
      </div>
      <div class="divide-y divide-[var(--c-border)]">
        @forelse($recentActivity as $activity)
          <a href="{{ $activity['route'] }}" class="flex items-start gap-3 p-5 hover:bg-[var(--c-elev)]/60">
            <span class="mt-1 size-2 rounded-full {{ $activity['tone'] === 'primary' ? 'bg-[var(--c-primary)]' : ($activity['tone'] === 'accent' ? 'bg-[var(--c-accent)]' : 'bg-[var(--c-border)]') }}"></span>
            <span class="min-w-0">
              <span class="block text-xs font-semibold uppercase text-[var(--c-muted)]">{{ $activity['label'] }}</span>
              <span class="mt-1 block truncate text-sm font-semibold text-[var(--c-text)]">{{ $activity['title'] }}</span>
              <span class="mt-1 block text-xs text-[var(--c-muted)]">{{ $activity['detail'] }}</span>
              <span class="mt-1 block text-xs text-[var(--c-muted)]">{{ $activity['date']?->format('d/m/Y H:i') }}</span>
            </span>
          </a>
        @empty
          <div class="p-5">
            <p class="text-sm text-[var(--c-muted)]">Sin actividad reciente para mostrar.</p>
          </div>
        @endforelse
      </div>
    </article>

    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft xl:col-span-2">
      <div class="flex flex-col gap-2 border-b border-[var(--c-border)] p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-[var(--c-text)]">Propiedades recientes</p>
          <p class="mt-1 text-xs text-[var(--c-muted)]">Ultimas propiedades actualizadas dentro de tu alcance.</p>
        </div>
        @if($permissions['can_create_property'] ?? false)
          <a href="{{ route('properties') }}" class="text-xs font-semibold text-[var(--c-primary)] hover:underline">Gestionar propiedades</a>
        @endif
      </div>
      <div class="hidden overflow-x-auto lg:block">
        <table class="min-w-full text-sm">
          <thead class="bg-[var(--c-elev)] text-left text-xs uppercase text-[var(--c-muted)]">
            <tr>
              <th class="px-5 py-3">Propiedad</th>
              <th class="px-5 py-3">Operacion</th>
              <th class="px-5 py-3">Estado</th>
              <th class="px-5 py-3 text-right">Precio</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--c-border)]">
            @forelse($recentProperties as $property)
              <tr>
                <td class="px-5 py-4">
                  <p class="font-semibold text-[var(--c-text)]">{{ $property->title ?: 'Propiedad #' . $property->id }}</p>
                  <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $property->property_type_name ?: 'Sin tipo' }}</p>
                </td>
                <td class="px-5 py-4 text-[var(--c-muted)]">{{ $operationLabel($property) }}</td>
                <td class="px-5 py-4">
                  <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $property->published ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                    {{ $property->published ? 'Publicada' : 'No publicada' }}
                  </span>
                </td>
                <td class="px-5 py-4 text-right font-semibold text-[var(--c-text)]">{{ $propertyPrice($property) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-5 py-10 text-center text-sm text-[var(--c-muted)]">No hay propiedades dentro de este alcance.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="space-y-3 p-4 lg:hidden">
        @forelse($recentProperties as $property)
          <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
            <p class="font-semibold text-[var(--c-text)]">{{ $property->title ?: 'Propiedad #' . $property->id }}</p>
            <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $operationLabel($property) }} - {{ $propertyPrice($property) }}</p>
          </div>
        @empty
          <p class="text-sm text-[var(--c-muted)]">No hay propiedades dentro de este alcance.</p>
        @endforelse
      </div>
    </article>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] p-5">
        <div>
          <p class="text-sm font-semibold text-[var(--c-text)]">Leads recientes</p>
          <p class="mt-1 text-xs text-[var(--c-muted)]">Ultimos registros captados.</p>
        </div>
        @if($permissions['can_view_leads'] ?? false)
          <a href="{{ route('property-contact-requests') }}" class="text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver todos</a>
        @endif
      </div>
      <div class="divide-y divide-[var(--c-border)]">
        @forelse($recentLeads as $lead)
          <a href="{{ route('property-contact-requests', ['search' => $lead->id]) }}" class="block p-5 hover:bg-[var(--c-elev)]/60">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-[var(--c-text)]">{{ $lead->name ?: 'Sin nombre' }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $lead->email ?: 'Sin email' }}{{ $lead->phone ? ' - ' . $lead->phone : '' }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $lead->property_form_name ?: 'Sin propiedad asociada' }}</p>
              </div>
              <span class="shrink-0 rounded-full border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-1 text-xs font-semibold">{{ $statusLabel($lead->status) }}</span>
            </div>
          </a>
        @empty
          <div class="p-5">
            <p class="text-sm text-[var(--c-muted)]">Aun no hay leads dentro de este alcance.</p>
          </div>
        @endforelse
      </div>
    </article>

    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-soft">
      <div class="flex items-center justify-between gap-3 border-b border-[var(--c-border)] p-5">
        <div>
          <p class="text-sm font-semibold text-[var(--c-text)]">Proximas visitas</p>
          <p class="mt-1 text-xs text-[var(--c-muted)]">Agenda pendiente y medible.</p>
        </div>
        @if($permissions['can_view_calendar'] ?? false)
          <a href="{{ route('calendar') }}" class="text-xs font-semibold text-[var(--c-primary)] hover:underline">Abrir agenda</a>
        @endif
      </div>
      <div class="divide-y divide-[var(--c-border)]">
        @forelse($upcomingVisits as $visit)
          <a href="{{ route('calendar', ['month' => $visit->scheduled_at?->format('Y-m'), 'visit' => $visit->id]) }}" class="block p-5 hover:bg-[var(--c-elev)]/60">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-[var(--c-text)]">{{ $visit->client?->name ?: 'Cliente sin nombre' }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $visit->reason }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $visit->property?->title ?: 'Sin propiedad asociada' }}</p>
              </div>
              <div class="shrink-0 text-right">
                <p class="text-xs font-semibold text-[var(--c-text)]">{{ $visit->scheduled_at?->format('d/m/Y') }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $visit->scheduled_at?->format('H:i') }}</p>
                <p class="mt-1 text-xs text-[var(--c-muted)]">{{ $visitStatusLabel($visit->status) }}</p>
              </div>
            </div>
          </a>
        @empty
          <div class="p-5">
            <p class="text-sm text-[var(--c-muted)]">No hay visitas proximas dentro de este alcance.</p>
          </div>
        @endforelse
      </div>
    </article>
  </section>
</div>
@endsection
