@php
  $visit = $visit ?? null;
  $client = $client ?? null;
  $method = $method ?? 'POST';
  $useOld = $useOld ?? false;
  $value = static fn (string $key, mixed $default = null): mixed => $useOld ? old($key, $default) : $default;
  $showClientSelect = $showClientSelect ?? false;
  $clientOptions = collect($clientOptions ?? []);
  $selectedClientId = $value('client_id', $client?->id ?? $visit?->client_id);
  $propertyOptions = collect([$client?->property, $visit?->property])->filter()->unique('id');
  $selectedPropertyId = $value('property_id', $visit?->property_id ?? $client?->property_id);
  $selectedProperty = $propertyOptions->first(fn ($property) => (string) $property->id === (string) $selectedPropertyId);
  $propertyPickerId = 'property-picker-' . str_replace('.', '-', uniqid('', true));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
  @csrf
  @if($method !== 'POST')
    @method($method)
  @endif

  @if($showClientSelect)
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Cliente</label>
      <select name="client_id" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm" required>
        <option value="">Selecciona un cliente</option>
        @foreach($clientOptions as $clientOption)
          <option value="{{ $clientOption->id }}" @selected((string) $selectedClientId === (string) $clientOption->id)>
            {{ $clientOption->name }}
            @if($clientOption->email)
              - {{ $clientOption->email }}
            @elseif($clientOption->phone)
              - {{ $clientOption->phone }}
            @endif
          </option>
        @endforeach
      </select>
    </div>
  @endif

  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Fecha</label>
      <input name="scheduled_date" type="date" value="{{ $value('scheduled_date', $visit?->scheduled_at?->format('Y-m-d') ?? now()->toDateString()) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Hora</label>
      <input name="scheduled_time" type="time" value="{{ $value('scheduled_time', $visit?->scheduled_at?->format('H:i') ?? now()->format('H:i')) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Duracion min.</label>
      <input name="duration_minutes" type="number" min="15" max="1440" step="15" value="{{ $value('duration_minutes', $visit?->duration_minutes ?? 60) }}" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Estado</label>
      <select name="status" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        @foreach($visitStatusOptions as $statusValue => $statusLabel)
          <option value="{{ $statusValue }}" @selected($value('status', $visit?->status ?? 'scheduled') === $statusValue)>{{ $statusLabel }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Motivo</label>
      <input name="reason" type="text" value="{{ $value('reason', $visit?->reason) }}" placeholder="Mostrar propiedad, seguimiento, firma..." class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Responsable</label>
      <select name="assigned_user_id" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
        <option value="">Sin responsable</option>
        @foreach($assignableUsers as $user)
          <option value="{{ $user->id }}" @selected((string) $value('assigned_user_id', $visit?->assigned_user_id) === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <div class="sm:col-span-2">
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Propiedad relacionada</label>
      <div id="{{ $propertyPickerId }}" class="property-lookup relative" data-search-url="{{ route('properties.search') }}">
        <input type="hidden" name="property_id" data-property-value value="{{ $selectedPropertyId }}">
        <div class="rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-2">
          <input type="search" data-property-search value="{{ $selectedProperty?->title }}" placeholder="Buscar por nombre, ubicacion, MLS Public ID, MLS ID u oficina MLS" autocomplete="off" class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--c-primary)]">
          <div class="mt-2 flex flex-col gap-2 rounded-lg bg-[var(--c-surface)] px-3 py-2 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <p data-property-selected class="break-words font-semibold text-[var(--c-text)]">
                @if($selectedProperty)
                  {{ $selectedProperty->title ?: 'Propiedad #' . $selectedProperty->id }}
                @elseif($selectedPropertyId)
                  Propiedad #{{ $selectedPropertyId }}
                @else
                  Sin propiedad
                @endif
              </p>
              <p data-property-meta class="mt-0.5 break-words text-xs text-[var(--c-muted)]">
                @if($selectedProperty)
                  ID interno {{ $selectedProperty->id }}
                  @if($selectedProperty->mls_public_id)
                    - MLS Public ID {{ $selectedProperty->mls_public_id }}
                  @endif
                @else
                  Selecciona una propiedad de los resultados
                @endif
              </p>
            </div>
            <button type="button" data-property-clear class="shrink-0 rounded-lg border border-[var(--c-border)] px-2.5 py-1.5 text-xs font-semibold hover:bg-[var(--c-elev)] transition">Limpiar</button>
          </div>
        </div>
        <div data-property-results class="absolute z-30 mt-2 hidden max-h-72 w-full overflow-auto rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-2 shadow-soft"></div>
      </div>
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Lugar</label>
      <input name="location" type="text" value="{{ $value('location', $visit?->location) }}" placeholder="Oficina, direccion o punto de encuentro" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">
    </div>
  </div>

  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Notas</label>
      <textarea name="notes" rows="3" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">{{ $value('notes', $visit?->notes) }}</textarea>
    </div>
    <div>
      <label class="mb-1 block text-xs text-[var(--c-muted)]">Resultado</label>
      <textarea name="outcome" rows="3" class="w-full rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2 text-sm">{{ $value('outcome', $visit?->outcome) }}</textarea>
    </div>
  </div>

  <div class="flex justify-end">
    <button type="submit" class="rounded-xl bg-[var(--c-primary)] px-4 py-2 text-sm font-semibold text-[var(--c-primary-ink)] hover:opacity-95 transition">{{ $submitLabel }}</button>
  </div>
</form>

<script>
  window.smpPropertyLookup = window.smpPropertyLookup || {
    init(root) {
      if (!root || root.dataset.initialized === '1') return;
      root.dataset.initialized = '1';

      const searchUrl = root.dataset.searchUrl;
      const valueInput = root.querySelector('[data-property-value]');
      const searchInput = root.querySelector('[data-property-search]');
      const selectedLabel = root.querySelector('[data-property-selected]');
      const selectedMeta = root.querySelector('[data-property-meta]');
      const results = root.querySelector('[data-property-results]');
      const clearButton = root.querySelector('[data-property-clear]');
      let timer = null;
      let abortController = null;

      const hideResults = () => {
        results.classList.add('hidden');
        results.innerHTML = '';
      };

      const renderMessage = (message) => {
        results.replaceChildren();
        const messageEl = document.createElement('div');
        messageEl.className = 'px-3 py-2 text-sm text-[var(--c-muted)]';
        messageEl.textContent = message;
        results.appendChild(messageEl);
        results.classList.remove('hidden');
      };

      const propertyMeta = (property) => {
        const pieces = [`ID interno ${property.id}`];
        if (property.subtitle) pieces.push(property.subtitle);
        if (property.mls_public_id) pieces.push(`MLS Public ID ${property.mls_public_id}`);
        if (property.mls_id) pieces.push(`MLS ID ${property.mls_id}`);
        if (property.mls_office_id) pieces.push(`MLS Office ID ${property.mls_office_id}`);
        return pieces.join(' - ');
      };

      const chooseProperty = (property) => {
        valueInput.value = property.id;
        searchInput.value = property.title;
        selectedLabel.textContent = property.title;
        selectedMeta.textContent = propertyMeta(property);
        hideResults();
      };

      const renderResults = (items) => {
        if (!items.length) {
          renderMessage('No se encontraron propiedades.');
          return;
        }

        results.replaceChildren();
        items.forEach((property) => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = 'w-full rounded-lg px-3 py-2 text-left hover:bg-[var(--c-elev)] transition';

          const title = document.createElement('span');
          title.className = 'block text-sm font-semibold text-[var(--c-text)]';
          title.textContent = property.title;

          const meta = document.createElement('span');
          meta.className = 'mt-0.5 block text-xs text-[var(--c-muted)]';
          meta.textContent = propertyMeta(property);

          button.append(title, meta);
          button.addEventListener('click', () => chooseProperty(property));
          results.appendChild(button);
        });
        results.classList.remove('hidden');
      };

      const searchProperties = (term) => {
        if (abortController) abortController.abort();
        abortController = new AbortController();
        const url = new URL(searchUrl, window.location.origin);
        url.searchParams.set('q', term);

        fetch(url, {
          headers: { 'Accept': 'application/json' },
          signal: abortController.signal,
        })
          .then((response) => response.ok ? response.json() : Promise.reject(response))
          .then((payload) => renderResults(payload.data || []))
          .catch((error) => {
            if (error.name === 'AbortError') return;
            renderMessage('No se pudo buscar propiedades.');
          });
      };

      searchInput.addEventListener('input', () => {
        const term = searchInput.value.trim();
        window.clearTimeout(timer);
        if (term.length < 2) {
          hideResults();
          return;
        }
        timer = window.setTimeout(() => searchProperties(term), 250);
      });

      searchInput.addEventListener('focus', () => {
        const term = searchInput.value.trim();
        if (term.length >= 2) searchProperties(term);
      });

      clearButton.addEventListener('click', () => {
        valueInput.value = '';
        searchInput.value = '';
        selectedLabel.textContent = 'Sin propiedad';
        selectedMeta.textContent = 'Selecciona una propiedad de los resultados';
        hideResults();
        searchInput.focus();
      });

      document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) hideResults();
      });
    },
  };

  window.smpPropertyLookup.init(document.getElementById('{{ $propertyPickerId }}'));
</script>
