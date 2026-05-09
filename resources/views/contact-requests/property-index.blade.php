@extends('layouts.app')

@section('title', 'Leads')

@section('content')
@php
  $statusLabel = static fn (?string $value): string => $statusOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin estado');
  $assignmentLabel = static fn (?string $value): string => $assignmentOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin asignacion');
  $contactTypeLabel = static fn (?string $value): string => $contactTypeOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin tipo');
  $badgeClass = static function (?string $value): string {
      return match ($value) {
          'assigned', 'converted' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
          'pending_assignment' => 'bg-amber-100 text-amber-800 border-amber-200',
          'new' => 'bg-sky-100 text-sky-800 border-sky-200',
          'contacted', 'qualified' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
          'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
          default => 'bg-[var(--c-elev)] text-[var(--c-muted)] border-[var(--c-border)]',
      };
  };
  $missingClientFields = static function ($lead): array {
      $fields = [];
      if (blank($lead->name)) $fields[] = 'nombre';
      if (blank($lead->email)) $fields[] = 'email';
      if (blank($lead->phone)) $fields[] = 'telefono';
      if ($lead->property_context === \App\Models\ContactRequest::PROPERTY_CONTEXT_EXISTING_LISTING && blank($lead->property_id)) $fields[] = 'propiedad';
      if (blank($lead->owner_id)) $fields[] = 'usuario asignado';
      return $fields;
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
      <h1 class="mt-2 text-2xl font-bold text-[var(--c-text)]">Leads</h1>
      <p class="mt-1 text-[var(--c-muted)]">Registros captados desde formularios publicos, clasificados por tipo, propiedad y origen.</p>
    </div>

    <a href="{{ route('public.properties.index') }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--c-elev)] border border-[var(--c-border)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">
      Ver propiedades
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
    </a>
  </div>

  @if(session('status'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('status') }}
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      <p class="font-semibold">Revisa los datos del formulario.</p>
      <ul class="mt-2 list-disc pl-5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-7">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Total leads</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['total']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Hoy</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['today']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Asignadas</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['assigned']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Pendientes</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['pending_assignment']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Compradores</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['buyers']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Vendedores</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['sellers']) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Ambos</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($stats['buyer_sellers']) }}</p>
    </article>
  </section>

  <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
    <form method="GET" action="{{ route('property-contact-requests') }}" class="border-b border-[var(--c-border)] p-5">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
        <div class="lg:col-span-3">
          <label for="filter-search" class="block text-xs text-[var(--c-muted)] mb-1">Buscar</label>
          <input id="filter-search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="Nombre, email, telefono o propiedad" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--c-primary)]">
        </div>
        <div class="lg:col-span-2">
          <label for="filter-contact-type" class="block text-xs text-[var(--c-muted)] mb-1">Tipo</label>
          <select id="filter-contact-type" name="contact_type" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($contactTypeOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['contact_type'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-2">
          <label for="filter-lead-type" class="block text-xs text-[var(--c-muted)] mb-1">Subtipo</label>
          <select id="filter-lead-type" name="lead_type" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($leadTypeOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['lead_type'] === $value)>{{ $label }}</option>
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
          <label for="filter-status" class="block text-xs text-[var(--c-muted)] mb-1">Estado</label>
          <select id="filter-status" name="status" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($statusOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-3">
          <label for="filter-assignment" class="block text-xs text-[var(--c-muted)] mb-1">Asignacion</label>
          <select id="filter-assignment" name="assignment_status" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            <option value="">Todas</option>
            @foreach($assignmentOptions as $value => $label)
              <option value="{{ $value }}" @selected($filters['assignment_status'] === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-6">
          <label for="filter-date-from" class="block text-xs text-[var(--c-muted)] mb-1">Desde</label>
          <input id="filter-date-from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </div>
        <div class="lg:col-span-6">
          <label for="filter-date-to" class="block text-xs text-[var(--c-muted)] mb-1">Hasta</label>
          <input id="filter-date-to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-[var(--c-muted)]">
          Mostrando {{ $leads->firstItem() ?? 0 }}-{{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }} registros
        </p>
        <div class="flex flex-wrap items-center gap-2">
          <a href="{{ route('property-contact-requests') }}" class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">Limpiar</a>
          <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Filtrar</button>
        </div>
      </div>
    </form>

    <div class="hidden overflow-x-auto lg:block">
      <table class="min-w-full text-sm">
        <thead class="bg-[var(--c-elev)] text-left text-xs uppercase text-[var(--c-muted)]">
          <tr>
            <th class="px-5 py-3">Fecha</th>
            <th class="px-5 py-3">Contacto</th>
            <th class="px-5 py-3">Tipo / origen</th>
            <th class="px-5 py-3">Propiedad / contexto</th>
            <th class="px-5 py-3">Estado</th>
            <th class="px-5 py-3">Usuario asignado</th>
            <th class="px-5 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--c-border)]">
          @forelse($leads as $lead)
            @php
              $propertyName = $lead->property_form_name ?: ($lead->property_context === \App\Models\ContactRequest::PROPERTY_CONTEXT_NONE ? 'Sin propiedad' : 'Propiedad sin nombre');
              $propertyId = $lead->property_id ?: data_get($lead->raw_payload, 'property_id');
              $missing = $missingClientFields($lead);
              $canConvertThisLead = $canConvertLeads && !$lead->converted_client_id && $missing === [];
              $sourceUrl = $lead->source_url ?: data_get($lead->raw_payload, 'submitted_from');
            @endphp
            <tr class="align-top">
              <td class="px-5 py-4 whitespace-nowrap text-[var(--c-muted)]">
                <div>{{ $lead->created_at?->format('d/m/Y') }}</div>
                <div class="text-xs">{{ $lead->created_at?->format('H:i') }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-[var(--c-text)]">{{ $lead->name ?: 'Sin nombre' }}</div>
                <div class="mt-1 text-xs text-[var(--c-muted)]">{{ $lead->email ?: 'Sin email' }}</div>
                <div class="text-xs text-[var(--c-muted)]">{{ $lead->phone ?: 'Sin telefono' }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-[var(--c-text)]">{{ $contactTypeLabel($lead->contact_type) }}</div>
                <div class="mt-1 text-xs text-[var(--c-muted)]">{{ $lead->lead_type_label }} - {{ $lead->source_label }}</div>
                @if($lead->utm_source || $lead->utm_campaign)
                  <div class="mt-2 text-xs text-[var(--c-muted)]">UTM: {{ collect([$lead->utm_source, $lead->utm_campaign])->filter()->join(' / ') }}</div>
                @endif
                @if($sourceUrl)
                  <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver origen</a>
                @endif
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-[var(--c-text)]">{{ $propertyName }}</div>
                <div class="mt-1 text-xs text-[var(--c-muted)]">{{ $lead->property_context_label }}</div>
                @if($propertyId)
                  <div class="text-xs text-[var(--c-muted)]">ID interno: {{ $propertyId }}</div>
                @endif
                @if($lead->property_address)
                  <div class="text-xs text-[var(--c-muted)]">{{ $lead->property_address }}</div>
                @endif
                @if($lead->property)
                  <a href="{{ route('public.properties.show', $lead->property_id) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver propiedad</a>
                @endif
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass($lead->status) }}">{{ $statusLabel($lead->status) }}</span>
                <div class="mt-2">
                  <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass($lead->assignment_status) }}">{{ $assignmentLabel($lead->assignment_status) }}</span>
                </div>
              </td>
              <td class="px-5 py-4">
                @if($lead->owner)
                  <div class="font-semibold text-[var(--c-text)]">{{ $lead->owner->name }}</div>
                  <div class="text-xs text-[var(--c-muted)]">{{ $lead->owner->email }}</div>
                @else
                  <span class="text-xs text-[var(--c-muted)]">Sin usuario asignado</span>
                @endif
              </td>
              <td class="px-5 py-4">
                <div class="flex flex-col gap-2">
                  @if($canManageLeads)
                    <button
                      type="button"
                      class="js-edit-lead inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-xs font-semibold hover:bg-[var(--c-surface)] transition"
                      data-id="{{ $lead->id }}"
                      data-action="{{ route('property-contact-requests.update', $lead) }}"
                      data-name="{{ $lead->name }}"
                      data-email="{{ $lead->email }}"
                      data-phone="{{ $lead->phone }}"
                      data-contact-type="{{ $lead->contact_type }}"
                      data-status="{{ $lead->status }}"
                      data-owner-id="{{ $lead->owner_id }}"
                      data-message="{{ $lead->message }}"
                    >
                      Editar
                    </button>
                  @endif

                  @if($lead->convertedClient)
                    <span class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">Cliente #{{ $lead->convertedClient->id }}</span>
                  @elseif($canConvertThisLead)
                    <form method="POST" action="{{ route('property-contact-requests.convert-client', $lead) }}" onsubmit="return confirm('Convertir este lead en cliente?');">
                      @csrf
                      <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[var(--c-primary)] px-3 py-2 text-xs font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">
                        Convertir en cliente
                      </button>
                    </form>
                  @elseif($canConvertLeads)
                    <span class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-xs text-[var(--c-muted)]" title="Faltan: {{ implode(', ', $missing) }}">
                      Faltan datos
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center">
                <p class="font-semibold text-[var(--c-text)]">Aun no hay leads publicos</p>
                <p class="mt-1 text-sm text-[var(--c-muted)]">Cuando alguien envie un formulario publico, aparecera aqui.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="space-y-3 p-4 lg:hidden">
      @forelse($leads as $lead)
        @php
          $propertyName = $lead->property_form_name ?: ($lead->property_context === \App\Models\ContactRequest::PROPERTY_CONTEXT_NONE ? 'Sin propiedad' : 'Propiedad sin nombre');
          $propertyId = $lead->property_id ?: data_get($lead->raw_payload, 'property_id');
          $missing = $missingClientFields($lead);
          $canConvertThisLead = $canConvertLeads && !$lead->converted_client_id && $missing === [];
          $sourceUrl = $lead->source_url ?: data_get($lead->raw_payload, 'submitted_from');
        @endphp
        <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-[var(--c-text)]">{{ $lead->name ?: 'Sin nombre' }}</p>
              <p class="text-xs text-[var(--c-muted)]">{{ $lead->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass($lead->status) }}">{{ $statusLabel($lead->status) }}</span>
          </div>
          <div class="mt-3 space-y-1 text-sm text-[var(--c-muted)]">
            <p>{{ $lead->email ?: 'Sin email' }}</p>
            <p>{{ $lead->phone ?: 'Sin telefono' }}</p>
          </div>
          <div class="mt-4 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3">
            <p class="text-xs text-[var(--c-muted)]">Tipo / origen</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $contactTypeLabel($lead->contact_type) }}</p>
            <p class="text-xs text-[var(--c-muted)]">{{ $lead->lead_type_label }} - {{ $lead->source_label }}</p>
            @if($sourceUrl)
              <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver origen</a>
            @endif
          </div>
          <div class="mt-4 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3">
            <p class="text-xs text-[var(--c-muted)]">Propiedad / contexto</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $propertyName }}</p>
            <p class="text-xs text-[var(--c-muted)]">{{ $lead->property_context_label }}</p>
            @if($propertyId)
              <p class="text-xs text-[var(--c-muted)]">ID interno: {{ $propertyId }}</p>
            @endif
            @if($lead->property_address)
              <p class="text-xs text-[var(--c-muted)]">{{ $lead->property_address }}</p>
            @endif
          </div>
          <div class="mt-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3">
            <p class="text-xs text-[var(--c-muted)]">Usuario asignado</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $lead->owner?->name ?: 'Sin usuario asignado' }}</p>
          </div>
          <div class="mt-4 flex flex-col gap-2">
            @if($canManageLeads)
              <button
                type="button"
                class="js-edit-lead inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-xs font-semibold transition"
                data-id="{{ $lead->id }}"
                data-action="{{ route('property-contact-requests.update', $lead) }}"
                data-name="{{ $lead->name }}"
                data-email="{{ $lead->email }}"
                data-phone="{{ $lead->phone }}"
                data-contact-type="{{ $lead->contact_type }}"
                data-status="{{ $lead->status }}"
                data-owner-id="{{ $lead->owner_id }}"
                data-message="{{ $lead->message }}"
              >
                Editar lead
              </button>
            @endif

            @if($lead->convertedClient)
              <span class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">Cliente #{{ $lead->convertedClient->id }}</span>
            @elseif($canConvertThisLead)
              <form method="POST" action="{{ route('property-contact-requests.convert-client', $lead) }}" onsubmit="return confirm('Convertir este lead en cliente?');">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[var(--c-primary)] px-3 py-2 text-xs font-semibold text-[var(--c-primary-ink)]">
                  Convertir en cliente
                </button>
              </form>
            @elseif($canConvertLeads)
              <span class="inline-flex items-center justify-center rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-xs text-[var(--c-muted)]">
                Faltan datos: {{ implode(', ', $missing) }}
              </span>
            @endif
          </div>
        </article>
      @empty
        <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-8 text-center">
          <p class="font-semibold text-[var(--c-text)]">Aun no hay leads publicos</p>
          <p class="mt-1 text-sm text-[var(--c-muted)]">Cuando alguien envie un formulario publico, aparecera aqui.</p>
        </div>
      @endforelse
    </div>

    @if($leads->hasPages())
      <div class="border-t border-[var(--c-border)] px-5 py-4">
        {{ $leads->links() }}
      </div>
    @endif
  </section>
</div>

<div id="leadEditModal" class="fixed inset-0 z-[11000] hidden" role="dialog" aria-modal="true" aria-labelledby="leadEditTitle">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-lead-edit-close></div>
  <div class="relative mx-auto mt-6 w-full max-w-2xl px-4">
    <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] shadow-2xl">
      <div class="flex items-start justify-between gap-3 border-b border-[var(--c-border)] px-6 py-4">
        <div>
          <h2 id="leadEditTitle" class="text-lg font-semibold text-[var(--c-text)]">Editar lead</h2>
          <p class="mt-1 text-xs text-[var(--c-muted)]">Actualiza datos, estado y usuario responsable.</p>
        </div>
        <button type="button" data-lead-edit-close class="rounded-xl bg-[var(--c-elev)] p-2 hover:bg-[var(--c-surface)] transition" aria-label="Cerrar">
          <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.415L11.414 10l4.95 4.95a1 1 0 11-1.414 1.415L10 11.414l-4.95 4.95a1 1 0 11-1.415-1.415L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
        </button>
      </div>

      <form id="leadEditForm" method="POST" action="#" class="space-y-4 px-6 py-5">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="lead-edit-name" class="block text-sm font-medium mb-1">Nombre completo</label>
            <input id="lead-edit-name" name="name" type="text" required class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
          </div>
          <div>
            <label for="lead-edit-phone" class="block text-sm font-medium mb-1">Telefono</label>
            <input id="lead-edit-phone" name="phone" type="text" required class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
          </div>
        </div>

        <div>
          <label for="lead-edit-email" class="block text-sm font-medium mb-1">Email</label>
          <input id="lead-edit-email" name="email" type="email" required class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="lead-edit-status" class="block text-sm font-medium mb-1">Estado</label>
            <select id="lead-edit-status" name="status" required class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
              @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="lead-edit-contact-type" class="block text-sm font-medium mb-1">Tipo</label>
            <select id="lead-edit-contact-type" name="contact_type" required class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
              @foreach($contactTypeOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="lead-edit-owner" class="block text-sm font-medium mb-1">Usuario asignado</label>
            <select id="lead-edit-owner" name="owner_id" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
              <option value="">Sin usuario asignado</option>
              @foreach($assignableUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}</option>
              @endforeach
            </select>
            <p class="mt-1 text-xs text-[var(--c-muted)]">
              @if($assignableUsers->isNotEmpty())
                Este usuario sera el responsable de ver y gestionar el lead.
              @else
                No hay usuarios activos disponibles para asignar.
              @endif
            </p>
          </div>
        </div>

        <div>
          <label for="lead-edit-message" class="block text-sm font-medium mb-1">Mensaje interno</label>
          <textarea id="lead-edit-message" name="message" rows="3" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm"></textarea>
        </div>

        <div class="flex justify-end gap-2 border-t border-[var(--c-border)] pt-4">
          <button type="button" data-lead-edit-close class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">Cancelar</button>
          <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('leadEditModal');
  const form = document.getElementById('leadEditForm');
  const title = document.getElementById('leadEditTitle');
  const fields = {
    name: document.getElementById('lead-edit-name'),
    email: document.getElementById('lead-edit-email'),
    phone: document.getElementById('lead-edit-phone'),
    contactType: document.getElementById('lead-edit-contact-type'),
    status: document.getElementById('lead-edit-status'),
    owner: document.getElementById('lead-edit-owner'),
    message: document.getElementById('lead-edit-message'),
  };

  function openModal(button) {
    form.action = button.dataset.action || '#';
    title.textContent = `Editar lead #${button.dataset.id || ''}`;
    fields.name.value = button.dataset.name || '';
    fields.email.value = button.dataset.email || '';
    fields.phone.value = button.dataset.phone || '';
    fields.contactType.value = button.dataset.contactType || 'buyer';
    fields.status.value = button.dataset.status || 'new';
    fields.owner.value = button.dataset.ownerId || '';
    fields.message.value = button.dataset.message || '';
    modal.classList.remove('hidden');
    fields.name.focus();
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  document.querySelectorAll('.js-edit-lead').forEach((button) => {
    button.addEventListener('click', () => openModal(button));
  });

  document.querySelectorAll('[data-lead-edit-close]').forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeModal();
    }
  });

  const editingLeadId = @json(session('editing_lead_id'));
  if (editingLeadId) {
    document.querySelector(`.js-edit-lead[data-id="${editingLeadId}"]`)?.click();
  }
});
</script>
@endsection
