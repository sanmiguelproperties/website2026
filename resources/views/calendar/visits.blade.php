@extends('layouts.app')

@section('title', 'Agenda de visitas')

@section('content')
@php
  $monthNames = [
      1 => 'Enero',
      2 => 'Febrero',
      3 => 'Marzo',
      4 => 'Abril',
      5 => 'Mayo',
      6 => 'Junio',
      7 => 'Julio',
      8 => 'Agosto',
      9 => 'Septiembre',
      10 => 'Octubre',
      11 => 'Noviembre',
      12 => 'Diciembre',
  ];
  $weekdayNames = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
  $visitStatusLabel = static fn (?string $value): string => $visitStatusOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin estado');
  $visitBadge = static function (?string $value): string {
      return match ($value) {
          'scheduled' => 'bg-sky-100 text-sky-800 border-sky-200',
          'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
          'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
          default => 'bg-[var(--c-elev)] text-[var(--c-muted)] border-[var(--c-border)]',
      };
  };
  $monthTitle = $monthNames[(int) $month->format('n')] . ' ' . $month->format('Y');
  $editingVisitId = session('editing_visit_id');
  $showCreateVisitForm = request('action') === 'create' || session('calendar_visit_form');
@endphp

<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
      <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
        <a href="{{ route('dashboard') }}" class="hover:text-[var(--c-text)]">Dashboard</a>
        <span>/</span>
        <span>CRM</span>
      </div>
      <h1 class="mt-2 text-2xl font-bold text-[var(--c-text)]">Agenda de visitas</h1>
      <p class="mt-1 text-[var(--c-muted)]">Calendario mensual de citas pautadas y realizadas.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      @if($canCreateVisit)
        <a href="{{ route('calendar', ['month' => $month->format('Y-m'), 'action' => 'create']) }}#new-visit" class="inline-flex items-center justify-center rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">
          Nueva visita
        </a>
      @endif
      <a href="{{ route('calendar', ['month' => $previousMonth->format('Y-m')]) }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm hover:bg-[var(--c-surface)] transition">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
      </a>
      <a href="{{ route('calendar') }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">Hoy</a>
      <a href="{{ route('calendar', ['month' => $nextMonth->format('Y-m')]) }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm hover:bg-[var(--c-surface)] transition">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </div>
  </div>

  @if(session('status'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      <p class="font-semibold">Revisa los datos de la cita.</p>
      <ul class="mt-2 list-disc space-y-1 pl-5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Total del mes</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['total']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Pautadas</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['scheduled']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Realizadas</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['completed']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Canceladas</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['cancelled']) }}</p>
    </article>
  </section>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft xl:col-span-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-semibold text-[var(--c-text)]">{{ $monthTitle }}</h2>
        <p class="text-sm text-[var(--c-muted)]">{{ $visits->count() }} visitas en este mes</p>
      </div>

      <div class="mt-5 hidden overflow-hidden rounded-xl border border-[var(--c-border)] lg:block">
        <div class="grid grid-cols-7 bg-[var(--c-elev)] text-xs font-semibold uppercase text-[var(--c-muted)]">
          @foreach($weekdayNames as $weekday)
            <div class="border-r border-[var(--c-border)] px-3 py-2 last:border-r-0">{{ $weekday }}</div>
          @endforeach
        </div>

        <div class="grid grid-cols-7 bg-[var(--c-surface)]">
          @foreach($calendarDays as $day)
            @php
              $dayVisits = $eventsByDate->get($day->toDateString(), collect());
              $isCurrentMonth = $day->isSameMonth($month);
              $isToday = $day->isSameDay($today);
            @endphp
            <div class="min-h-[132px] border-r border-t border-[var(--c-border)] p-2 last:border-r-0 {{ $isCurrentMonth ? '' : 'bg-[var(--c-elev)]/45 text-[var(--c-muted)]' }}">
              <div class="flex items-center justify-between">
                <span class="grid size-7 place-items-center rounded-full text-sm font-semibold {{ $isToday ? 'bg-[var(--c-primary)] text-[var(--c-primary-ink)]' : 'text-[var(--c-text)]' }}">{{ $day->format('j') }}</span>
                @if($dayVisits->count() > 0)
                  <span class="text-xs text-[var(--c-muted)]">{{ $dayVisits->count() }}</span>
                @endif
              </div>

              <div class="mt-2 space-y-1">
                @foreach($dayVisits as $visit)
                  <a href="{{ route('calendar', ['month' => $month->format('Y-m'), 'visit' => $visit->id]) }}#visit-detail" class="block rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-2 py-1.5 text-xs hover:border-[var(--c-primary)] hover:bg-[var(--c-surface)] transition">
                    <span class="font-semibold text-[var(--c-text)]">{{ $visit->scheduled_at?->format('H:i') }}</span>
                    <span class="ml-1 text-[var(--c-muted)]">{{ $visit->client?->name ?: 'Cliente sin nombre' }}</span>
                  </a>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="mt-5 space-y-3 lg:hidden">
        @forelse($visits as $visit)
          <a href="{{ route('calendar', ['month' => $month->format('Y-m'), 'visit' => $visit->id]) }}#visit-detail" class="block rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4 hover:border-[var(--c-primary)] transition">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-[var(--c-text)]">{{ $visit->client?->name ?: 'Cliente sin nombre' }}</p>
                <p class="mt-1 text-sm text-[var(--c-muted)]">{{ $visit->scheduled_at?->format('d/m/Y H:i') }} - {{ $visit->duration_minutes }} min.</p>
              </div>
              <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $visitBadge($visit->status) }}">{{ $visitStatusLabel($visit->status) }}</span>
            </div>
            <p class="mt-2 text-sm text-[var(--c-muted)]">{{ $visit->reason }}</p>
          </a>
        @empty
          <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-8 text-center">
            <p class="font-semibold text-[var(--c-text)]">No hay visitas este mes</p>
          </div>
        @endforelse
      </div>
    </section>

    <aside id="visit-detail" class="space-y-6 xl:col-span-4">
      @if($canCreateVisit)
        <section id="new-visit" class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft scroll-mt-24">
          <details @if($showCreateVisitForm) open @endif>
            <summary class="cursor-pointer text-lg font-semibold text-[var(--c-text)]">Agendar visita</summary>
            <div class="mt-4">
              @if($visitClientOptions->isEmpty())
                <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4 text-sm text-[var(--c-muted)]">
                  No hay clientes activos disponibles para agendar una visita.
                </div>
              @else
                @include('clients.partials.visit-form', [
                    'client' => null,
                    'visit' => null,
                    'clientOptions' => $visitClientOptions,
                    'showClientSelect' => true,
                    'action' => route('calendar.visits.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Agendar visita',
                    'assignableUsers' => $assignableUsers,
                    'visitStatusOptions' => $visitStatusOptions,
                    'useOld' => $showCreateVisitForm,
                ])
              @endif
            </div>
          </details>
        </section>
      @endif

      <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        @if($selectedVisit)
          <div class="flex flex-col gap-3">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-semibold text-[var(--c-text)]">{{ $selectedVisit->client?->name ?: 'Cliente sin nombre' }}</h2>
                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $visitBadge($selectedVisit->status) }}">{{ $visitStatusLabel($selectedVisit->status) }}</span>
              </div>
              <p class="mt-1 text-sm text-[var(--c-muted)]">{{ $selectedVisit->scheduled_at?->format('d/m/Y H:i') }} - {{ $selectedVisit->duration_minutes }} min.</p>
            </div>

            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4 text-sm">
              <dl class="space-y-3">
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Motivo</dt>
                  <dd class="mt-1 font-semibold text-[var(--c-text)]">{{ $selectedVisit->reason }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Responsable de visita</dt>
                  <dd class="mt-1 text-[var(--c-text)]">{{ $selectedVisit->assignedUser?->name ?: 'Sin responsable' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Usuario asignado al cliente</dt>
                  <dd class="mt-1 text-[var(--c-text)]">{{ $selectedVisit->client?->owner?->name ?: $selectedVisit->client?->contactRequest?->owner?->name ?: 'Sin usuario asignado' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Propiedad</dt>
                  <dd class="mt-1 text-[var(--c-text)]">{{ $selectedVisit->property?->title ?: $selectedVisit->client?->property?->title ?: 'Sin propiedad relacionada' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Lugar</dt>
                  <dd class="mt-1 text-[var(--c-text)]">{{ $selectedVisit->location ?: 'Sin lugar indicado' }}</dd>
                </div>
              </dl>

              @if($selectedVisit->notes || $selectedVisit->outcome)
                <div class="mt-4 space-y-3 border-t border-[var(--c-border)] pt-4">
                  @if($selectedVisit->notes)
                    <div>
                      <p class="text-xs text-[var(--c-muted)]">Notas</p>
                      <p class="mt-1 whitespace-pre-line text-[var(--c-text)]">{{ $selectedVisit->notes }}</p>
                    </div>
                  @endif
                  @if($selectedVisit->outcome)
                    <div>
                      <p class="text-xs text-[var(--c-muted)]">Resultado</p>
                      <p class="mt-1 whitespace-pre-line text-[var(--c-text)]">{{ $selectedVisit->outcome }}</p>
                    </div>
                  @endif
                </div>
              @endif
            </div>

            <div class="flex flex-wrap gap-2">
              @if($selectedVisit->client)
                <a href="{{ route('clients.show', $selectedVisit->client) }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-xs font-semibold hover:bg-[var(--c-surface)] transition">Ver cliente</a>
              @endif
            </div>
          </div>

          @if($canEditSelectedVisit)
            <details class="mt-5 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4" @if((int) $editingVisitId === (int) $selectedVisit->id) open @endif>
              <summary class="cursor-pointer text-sm font-semibold text-[var(--c-primary)]">Editar cita</summary>
              <div class="mt-4">
                @include('clients.partials.visit-form', [
                    'client' => $selectedVisit->client,
                    'visit' => $selectedVisit,
                    'action' => route('calendar.visits.update', $selectedVisit),
                    'method' => 'PATCH',
                    'submitLabel' => 'Guardar cambios',
                    'assignableUsers' => $assignableUsers,
                    'visitStatusOptions' => $visitStatusOptions,
                    'useOld' => (int) $editingVisitId === (int) $selectedVisit->id,
                ])
              </div>
            </details>
          @else
            <div class="mt-5 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4 text-sm text-[var(--c-muted)]">
              Solo el super administrador o el usuario asignado al cliente puede editar esta cita.
            </div>
          @endif
        @else
          <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-8 text-center">
            <p class="font-semibold text-[var(--c-text)]">Selecciona una visita</p>
            <p class="mt-1 text-sm text-[var(--c-muted)]">Haz click en una cita del calendario para ver su detalle.</p>
          </div>
        @endif
      </section>
    </aside>
  </div>
</div>
@endsection
