@extends('layouts.app')

@section('title', 'Cliente - ' . $client->name)

@section('content')
@php
  $sourceLabel = static fn (?string $value): string => $sourceOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin origen');
  $statusLabel = static fn (?string $value): string => $statusOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin estado');
  $contactTypeLabel = static fn (?string $value): string => $contactTypeOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin tipo');
  $visitStatusLabel = static fn (?string $value): string => $visitStatusOptions[$value ?? ''] ?? ($value ? ucfirst(str_replace('_', ' ', $value)) : 'Sin estado');
  $statusBadge = static function (?string $value): string {
      return match ($value) {
          'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
          'inactive' => 'bg-amber-100 text-amber-800 border-amber-200',
          'archived' => 'bg-slate-100 text-slate-700 border-slate-200',
          default => 'bg-[var(--c-elev)] text-[var(--c-muted)] border-[var(--c-border)]',
      };
  };
  $visitBadge = static function (?string $value): string {
      return match ($value) {
          'scheduled' => 'bg-sky-100 text-sky-800 border-sky-200',
          'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
          'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
          default => 'bg-[var(--c-elev)] text-[var(--c-muted)] border-[var(--c-border)]',
      };
  };
  $assignedUser = $client->owner ?: $client->contactRequest?->owner;
  $editingCommentId = session('editing_comment_id');
  $editingVisitId = session('editing_visit_id');
  $useClientOld = (bool) session('editing_client');
  $clientValue = static fn (string $key, mixed $default = null): mixed => $useClientOld ? old($key, $default) : $default;
@endphp

<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
      <div class="flex items-center gap-2 text-sm text-[var(--c-muted)]">
        <a href="{{ route('dashboard') }}" class="hover:text-[var(--c-text)]">Dashboard</a>
        <span>/</span>
        <a href="{{ route('clients') }}" class="hover:text-[var(--c-text)]">Clientes</a>
        <span>/</span>
        <span>{{ $client->name }}</span>
      </div>
      <h1 class="mt-2 text-2xl font-bold text-[var(--c-text)]">{{ $client->name }}</h1>
      <p class="mt-1 text-[var(--c-muted)]">Historial interno de comentarios y visitas del cliente.</p>
    </div>

    <a href="{{ route('clients') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-4 py-2 text-sm hover:bg-[var(--c-surface)] transition">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
      Volver
    </a>
  </div>

  @if(session('status'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
  @endif

  @if(session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      <p class="font-semibold">Revisa los datos del formulario.</p>
      <ul class="mt-2 list-disc space-y-1 pl-5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Estado</p>
      <span class="mt-3 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge($client->status) }}">{{ $statusLabel($client->status) }}</span>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Tipo</p>
      <p class="mt-2 font-semibold text-[var(--c-text)]">{{ $contactTypeLabel($client->contact_type) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Origen</p>
      <p class="mt-2 font-semibold text-[var(--c-text)]">{{ $sourceLabel($client->source) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Comentarios</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($comments->count()) }}</p>
    </article>
    <article class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
      <p class="text-sm text-[var(--c-muted)]">Visitas</p>
      <p class="mt-2 text-3xl font-bold text-[var(--c-text)]">{{ number_format($visits->count()) }}</p>
    </article>
  </section>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <aside class="space-y-6 xl:col-span-4">
      <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-[var(--c-text)]">Datos del cliente</h2>
            <p class="mt-1 text-sm text-[var(--c-muted)]">Informacion principal y responsable asignado.</p>
          </div>
        </div>

        @if($canEditClient)
          <form method="POST" action="{{ route('clients.update', $client) }}" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Nombre</label>
              <input name="name" type="text" value="{{ $clientValue('name', $client->name) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Email</label>
              <input name="email" type="email" value="{{ $clientValue('email', $client->email) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Telefono</label>
              <input name="phone" type="text" value="{{ $clientValue('phone', $client->phone) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Tipo</label>
              <select name="contact_type" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
                @foreach($contactTypeOptions as $value => $label)
                  <option value="{{ $value }}" @selected($clientValue('contact_type', $client->contact_type) === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Estado</label>
              <select name="status" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
                @foreach($statusOptions as $value => $label)
                  <option value="{{ $value }}" @selected($clientValue('status', $client->status) === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Usuario asignado</label>
              <select name="owner_id" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
                <option value="">Sin usuario asignado</option>
                @foreach($assignableUsers as $user)
                  <option value="{{ $user->id }}" @selected((string) $clientValue('owner_id', $client->owner_id) === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs text-[var(--c-muted)]">Notas generales</label>
              <textarea name="notes" rows="5" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">{{ $clientValue('notes', $client->notes) }}</textarea>
            </div>

            <button type="submit" class="w-full rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Guardar cliente</button>
          </form>
        @else
          <div class="mt-5 space-y-4 text-sm">
            <div>
              <p class="text-xs text-[var(--c-muted)]">Email</p>
              <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->email ?: 'Sin email' }}</p>
            </div>
            <div>
              <p class="text-xs text-[var(--c-muted)]">Telefono</p>
              <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->phone ?: 'Sin telefono' }}</p>
            </div>
            <div>
              <p class="text-xs text-[var(--c-muted)]">Tipo</p>
              <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $contactTypeLabel($client->contact_type) }}</p>
            </div>
            <div>
              <p class="text-xs text-[var(--c-muted)]">Usuario asignado</p>
              <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $assignedUser?->name ?: 'Sin usuario asignado' }}</p>
            </div>
            <div>
              <p class="text-xs text-[var(--c-muted)]">Notas generales</p>
              <p class="mt-1 whitespace-pre-line text-[var(--c-text)]">{{ $client->notes ?: 'Sin notas generales' }}</p>
            </div>
          </div>
        @endif
      </section>

      <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        <h2 class="text-lg font-semibold text-[var(--c-text)]">Contexto</h2>
        <div class="mt-4 space-y-4 text-sm">
          <div>
            <p class="text-xs text-[var(--c-muted)]">Propiedad inicial</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->property?->title ?: data_get($client->raw_payload, 'property_name', 'Sin propiedad') }}</p>
            @if($client->property)
              <a href="{{ route('public.properties.show', $client->property_id) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-[var(--c-primary)] hover:underline">Ver propiedad publica</a>
            @endif
          </div>
          <div>
            <p class="text-xs text-[var(--c-muted)]">Alta</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->created_at?->format('d/m/Y H:i') }}</p>
          </div>
          <div>
            <p class="text-xs text-[var(--c-muted)]">Ultima actualizacion</p>
            <p class="mt-1 font-semibold text-[var(--c-text)]">{{ $client->updated_at?->format('d/m/Y H:i') }}</p>
          </div>
          @if($client->contactRequest)
            <div>
              <p class="text-xs text-[var(--c-muted)]">Lead original</p>
              <a href="{{ route('property-contact-requests', ['search' => $client->contactRequest->id]) }}" class="mt-1 inline-flex text-sm font-semibold text-[var(--c-primary)] hover:underline">Ver lead #{{ $client->contactRequest->id }}</a>
            </div>
          @endif
        </div>
      </section>
    </aside>

    <main class="space-y-6 xl:col-span-8">
      <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-[var(--c-text)]">Comentarios</h2>
            <p class="mt-1 text-sm text-[var(--c-muted)]">Cada comentario conserva fecha de creacion y ultima modificacion.</p>
          </div>
        </div>

        @if($canEditClient)
          <form method="POST" action="{{ route('clients.comments.store', $client) }}" class="mt-5 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
            @csrf
            <label class="mb-1 block text-xs text-[var(--c-muted)]">Nuevo comentario</label>
            <textarea name="body" rows="4" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-sm">{{ session('comment_form') ? old('body') : '' }}</textarea>
            <div class="mt-3 flex justify-end">
              <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Agregar comentario</button>
            </div>
          </form>
        @endif

        <div class="mt-5 space-y-3">
          @forelse($comments as $comment)
            @php
              $isEditingComment = (int) $editingCommentId === (int) $comment->id;
            @endphp
            <article class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <p class="font-semibold text-[var(--c-text)]">{{ $comment->user?->name ?: 'Sistema' }}</p>
                  <p class="text-xs text-[var(--c-muted)]">
                    Creado {{ $comment->created_at?->format('d/m/Y H:i') }}
                    @if($comment->updated_at && $comment->created_at && !$comment->updated_at->equalTo($comment->created_at))
                      - Modificado {{ $comment->updated_at->format('d/m/Y H:i') }}
                    @endif
                  </p>
                </div>
                @if($canEditClient)
                  <form method="POST" action="{{ route('clients.comments.destroy', [$client, $comment]) }}" onsubmit="return confirm('Eliminar este comentario?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Eliminar</button>
                  </form>
                @endif
              </div>

              <div class="mt-3 whitespace-pre-line text-sm text-[var(--c-text)]">{{ $comment->body }}</div>

              @if($canEditClient)
                <details class="mt-4" @if($isEditingComment) open @endif>
                  <summary class="cursor-pointer text-xs font-semibold text-[var(--c-primary)]">Editar comentario</summary>
                  <form method="POST" action="{{ route('clients.comments.update', [$client, $comment]) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('PATCH')
                    <textarea name="body" rows="4" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-sm">{{ $isEditingComment ? old('body', $comment->body) : $comment->body }}</textarea>
                    <div class="flex justify-end">
                      <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">Guardar comentario</button>
                    </div>
                  </form>
                </details>
              @endif
            </article>
          @empty
            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-6 text-center">
              <p class="font-semibold text-[var(--c-text)]">Sin comentarios todavia</p>
              <p class="mt-1 text-sm text-[var(--c-muted)]">El historial aparecera aqui cuando se agregue el primer comentario.</p>
            </div>
          @endforelse
        </div>
      </section>

      <section class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] p-5 shadow-soft">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-[var(--c-text)]">Visitas</h2>
            <p class="mt-1 text-sm text-[var(--c-muted)]">Agenda y resultado de visitas pautadas o realizadas.</p>
          </div>
        </div>

        @if($canEditClient)
          <div class="mt-5 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
            <h3 class="mb-3 text-sm font-semibold text-[var(--c-text)]">Nueva visita</h3>
            @include('clients.partials.visit-form', [
                'client' => $client,
                'visit' => null,
                'action' => route('clients.visits.store', $client),
                'method' => 'POST',
                'submitLabel' => 'Registrar visita',
                'assignableUsers' => $assignableUsers,
                'visitStatusOptions' => $visitStatusOptions,
                'useOld' => (bool) session('visit_form'),
            ])
          </div>
        @endif

        <div class="mt-5 space-y-3">
          @forelse($visits as $visit)
            @php
              $isEditingVisit = (int) $editingVisitId === (int) $visit->id;
            @endphp
            <article class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-semibold text-[var(--c-text)]">{{ $visit->reason }}</h3>
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $visitBadge($visit->status) }}">{{ $visitStatusLabel($visit->status) }}</span>
                  </div>
                  <p class="mt-1 text-sm text-[var(--c-muted)]">
                    {{ $visit->scheduled_at?->format('d/m/Y H:i') }} - {{ $visit->duration_minutes }} min.
                  </p>
                </div>
                @if($canEditClient)
                  <form method="POST" action="{{ route('clients.visits.destroy', [$client, $visit]) }}" onsubmit="return confirm('Eliminar esta visita?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Eliminar</button>
                  </form>
                @endif
              </div>

              <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Responsable</dt>
                  <dd class="mt-1 font-semibold text-[var(--c-text)]">{{ $visit->assignedUser?->name ?: 'Sin responsable' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Propiedad</dt>
                  <dd class="mt-1 font-semibold text-[var(--c-text)]">{{ $visit->property?->title ?: 'Sin propiedad relacionada' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Lugar</dt>
                  <dd class="mt-1 text-[var(--c-text)]">{{ $visit->location ?: 'Sin lugar indicado' }}</dd>
                </div>
                <div>
                  <dt class="text-xs text-[var(--c-muted)]">Registro</dt>
                  <dd class="mt-1 text-[var(--c-text)]">
                    Creada {{ $visit->created_at?->format('d/m/Y H:i') }}
                    @if($visit->updated_at && $visit->created_at && !$visit->updated_at->equalTo($visit->created_at))
                      <br>Modificada {{ $visit->updated_at->format('d/m/Y H:i') }}
                    @endif
                  </dd>
                </div>
              </dl>

              @if($visit->notes || $visit->outcome || $visit->completed_at)
                <div class="mt-4 grid grid-cols-1 gap-3 text-sm lg:grid-cols-2">
                  @if($visit->notes)
                    <div>
                      <p class="text-xs text-[var(--c-muted)]">Notas</p>
                      <p class="mt-1 whitespace-pre-line text-[var(--c-text)]">{{ $visit->notes }}</p>
                    </div>
                  @endif
                  @if($visit->outcome)
                    <div>
                      <p class="text-xs text-[var(--c-muted)]">Resultado</p>
                      <p class="mt-1 whitespace-pre-line text-[var(--c-text)]">{{ $visit->outcome }}</p>
                    </div>
                  @endif
                  @if($visit->completed_at)
                    <div>
                      <p class="text-xs text-[var(--c-muted)]">Realizada</p>
                      <p class="mt-1 text-[var(--c-text)]">{{ $visit->completed_at->format('d/m/Y H:i') }}</p>
                    </div>
                  @endif
                </div>
              @endif

              @if($canEditClient)
                <details class="mt-4" @if($isEditingVisit) open @endif>
                  <summary class="cursor-pointer text-xs font-semibold text-[var(--c-primary)]">Editar visita</summary>
                  <div class="mt-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-4">
                    @include('clients.partials.visit-form', [
                        'client' => $client,
                        'visit' => $visit,
                        'action' => route('clients.visits.update', [$client, $visit]),
                        'method' => 'PATCH',
                        'submitLabel' => 'Guardar visita',
                        'assignableUsers' => $assignableUsers,
                        'visitStatusOptions' => $visitStatusOptions,
                        'useOld' => $isEditingVisit,
                    ])
                  </div>
                </details>
              @endif
            </article>
          @empty
            <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-6 text-center">
              <p class="font-semibold text-[var(--c-text)]">Sin visitas registradas</p>
              <p class="mt-1 text-sm text-[var(--c-muted)]">Las visitas pautadas y realizadas apareceran aqui.</p>
            </div>
          @endforelse
        </div>
      </section>
    </main>
  </div>
</div>
@endsection
