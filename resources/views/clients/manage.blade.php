@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
@php
  $statusLabel = static fn (?string $value): string => $statusOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin estado');
  $sourceLabel = static fn (?string $value): string => $sourceOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin origen');
  $statusBadge = static function (?string $value): string {
      return match ($value) {
          'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
          'inactive' => 'bg-amber-100 text-amber-800 border-amber-200',
          'archived' => 'bg-slate-100 text-slate-700 border-slate-200',
          default => 'bg-[var(--c-elev)] text-[var(--c-muted)] border-[var(--c-border)]',
      };
  };
@endphp

<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
      <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
        <a href="{{ route('dashboard') }}" class="hover:text-[var(--c-text)]">Dashboard</a>
        <span>/</span>
        <span>CRM</span>
      </div>
      <h1 class="mt-2 text-2xl font-bold text-[var(--c-text)]">Clientes</h1>
      <p class="mt-1 text-[var(--c-muted)]">Consulta los clientes creados desde solicitudes y formularios de propiedad.</p>
    </div>

    <a href="{{ route('property-contact-requests') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">
      Ver solicitudes
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
    </a>
  </div>

  <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Total clientes</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['total']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Activos</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['active']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Desde propiedades</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['from_property_forms']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Este mes</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['this_month']) }}</p>
    </article>
  </section>

  <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
    <form method="GET" action="{{ route('clients') }}" class="border-b border-[var(--c-border)] p-5">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
        <div class="lg:col-span-4">
          <label for="filter-search" class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <input id="filter-search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="Nombre, email, telefono, propiedad o usuario" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--c-primary)]">
        </div>
        <div class="lg:col-span-2">
          <label for="filter-status" class="block text-xs text-[var(--c-muted)] mb-1">Estado</label>
          <select id="filter-status" name="status" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($statusOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-2">
          <label for="filter-source" class="block text-xs text-[var(--c-muted)] mb-1">Origen</label>
          <select id="filter-source" name="source" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($sourceOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['source'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-2">
          <label for="filter-date-from" class="block text-xs text-[var(--c-muted)] mb-1">Desde</label>
          <input id="filter-date-from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </div>
        <div class="lg:col-span-2">
          <label for="filter-date-to" class="block text-xs text-[var(--c-muted)] mb-1">Hasta</label>
          <input id="filter-date-to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-[var(--c-muted)]">
          Mostrando {{ $clients->firstItem() ?? 0 }}-{{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} clientes
        </p>
        <div class="flex flex-wrap items-center gap-2">
          <a href="{{ route('clients') }}" class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">Limpiar</a>
          <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Filtrar</button>
        </div>
      </div>
    </form>

    <div class="hidden overflow-x-auto lg:block">
      <table class="min-w-full text-sm">
        <thead class="bg-[var(--c-elev)] text-left text-xs uppercase text-[var(--c-muted)]">
          <tr>
            <th class="px-5 py-3">Cliente</th>
            <th class="px-5 py-3">Propiedad</th>
            <th class="px-5 py-3">Usuario asignado</th>
            <th class="px-5 py-3">Estado</th>
            <th class="px-5 py-3">Origen</th>
            <th class="px-5 py-3">Alta</th>
            <th class="px-5 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--c-border)]">
          @forelse($clients as $client)
            @php
              $assignedUser = $client->owner ?: $client->contactRequest?->owner;
            @endphp
            <tr class="align-top">
              <td class="px-5 py-4">
                <div class="font-semibold text-[var(--c-text)]">{{ $client->name }}</div>
                <div class="mt-1 text-xs text-[var(--c-muted)]">{{ $client->email ?: 'Sin email' }}</div>
                <div class="text-xs text-[var(--c-muted)]">{{ $client->phone ?: 'Sin telefono' }}</div>
                <div class="mt-2 text-xs text-[var(--c-muted)]">{{ $client->comments_count }} comentarios - {{ $client->visits_count }} visitas</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-[var(--c-text)]">{{ $client->property?->title ?: data_get($client->raw_payload, 'property_name', 'Sin propiedad') }}</div>
                @if($client->property)
                  <div class="mt-1 text-xs text-[var(--c-muted)]">ID interno: {{ $client->property_id }}</div>
                  <a href="{{ route('public.properties.show', $client->property_id) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver propiedad</a>
                @endif
              </td>
              <td class="px-5 py-4">
                @if($assignedUser)
                  <div class="font-semibold text-[var(--c-text)]">{{ $assignedUser->name }}</div>
                  <div class="text-xs text-[var(--c-muted)]">{{ $assignedUser->email }}</div>
                @else
                  <span class="text-xs text-[var(--c-muted)]">Sin usuario asignado</span>
                @endif
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge($client->status) }}">{{ $statusLabel($client->status) }}</span>
              </td>
              <td class="px-5 py-4 text-[var(--c-muted)]">{{ $sourceLabel($client->source) }}</td>
              <td class="px-5 py-4 whitespace-nowrap text-[var(--c-muted)]">
                <div>{{ $client->created_at?->format('d/m/Y') }}</div>
                <div class="text-xs">{{ $client->created_at?->format('H:i') }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="flex flex-col gap-2">
                  <a href="{{ route('clients.show', $client) }}" class="inline-flex items-center justify-center rounded-xl bg-[var(--c-primary)] px-3 py-2 text-xs font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Gestionar</a>
                  @if($client->contactRequest)
                    <a href="{{ route('property-contact-requests', ['search' => $client->contactRequest->id]) }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-xs font-semibold hover:bg-[var(--c-surface)] transition">Ver solicitud</a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center">
                <p class="font-semibold text-[var(--c-text)]">Aun no hay clientes registrados</p>
                <p class="mt-1 text-sm text-[var(--c-muted)]">Cuando conviertas una solicitud en cliente, aparecera aqui.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="space-y-3 p-4 lg:hidden">
      @forelse($clients as $client)
        @php
          $assignedUser = $client->owner ?: $client->contactRequest?->owner;
        @endphp
        <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-[var(--c-text)]">{{ $client->name }}</p>
              <p class="text-xs text-[var(--c-muted)]">{{ $client->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge($client->status) }}">{{ $statusLabel($client->status) }}</span>
          </div>

          <div class="mt-3 space-y-1 text-sm text-[var(--c-muted)]">
            <p>{{ $client->email ?: 'Sin email' }}</p>
            <p>{{ $client->phone ?: 'Sin telefono' }}</p>
            <p>{{ $client->comments_count }} comentarios - {{ $client->visits_count }} visitas</p>
          </div>

          <div class="mt-4 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3">
            <p class="text-xs text-[var(--c-muted)]">Propiedad</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->property?->title ?: data_get($client->raw_payload, 'property_name', 'Sin propiedad') }}</p>
          </div>

          <div class="mt-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3">
            <p class="text-xs text-[var(--c-muted)]">Usuario asignado</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $assignedUser?->name ?: 'Sin usuario asignado' }}</p>
          </div>

          <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <a href="{{ route('clients.show', $client) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-[var(--c-primary)] px-3 py-2 text-xs font-semibold text-[var(--c-primary-ink)] transition">Gestionar</a>
            @if($client->contactRequest)
              <a href="{{ route('property-contact-requests', ['search' => $client->contactRequest->id]) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-xs font-semibold transition">Ver solicitud</a>
            @endif
          </div>
        </article>
      @empty
        <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-8 text-center">
          <p class="font-semibold text-[var(--c-text)]">Aun no hay clientes registrados</p>
          <p class="mt-1 text-sm text-[var(--c-muted)]">Cuando conviertas una solicitud en cliente, aparecera aqui.</p>
        </div>
      @endforelse
    </div>

    @if($clients->hasPages())
      <div class="border-t border-[var(--c-border)] px-5 py-4">
        {{ $clients->links() }}
      </div>
    @endif
  </section>
</div>
@endsection
