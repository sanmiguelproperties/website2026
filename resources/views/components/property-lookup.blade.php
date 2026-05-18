@props([
  'id' => 'property-lookup-' . str_replace('.', '-', uniqid('', true)),
  'name' => 'property_id',
  'inputId' => null,
  'selectedProperty' => null,
  'selectedPropertyId' => null,
  'searchUrl' => route('properties.search'),
  'placeholder' => 'Buscar por nombre, ID interno, LMS/MLS ID o EasyBroker ID',
  'surfaceClass' => 'bg-[var(--c-elev)]',
])

@php
  $selectedPropertyId = $selectedPropertyId ?? $selectedProperty?->id;
@endphp

<div id="{{ $id }}" class="property-lookup relative" data-search-url="{{ $searchUrl }}">
  <input
    type="hidden"
    name="{{ $name }}"
    @if($inputId) id="{{ $inputId }}" @endif
    data-property-value
    value="{{ $selectedPropertyId }}"
  >
  <div class="rounded-xl border border-[var(--c-border)] {{ $surfaceClass }} p-2">
    <input
      type="search"
      data-property-search
      value="{{ $selectedProperty?->title }}"
      placeholder="{{ $placeholder }}"
      autocomplete="off"
      class="w-full rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--c-primary)]"
    >
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
            @if($selectedProperty->mls_id)
              - MLS ID {{ $selectedProperty->mls_id }}
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

@once
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
          if (!property || !property.id) {
            return 'Selecciona una propiedad de los resultados';
          }

          const pieces = [`ID interno ${property.id}`];
          if (property.subtitle) pieces.push(property.subtitle);
          if (property.mls_public_id) pieces.push(`MLS Public ID ${property.mls_public_id}`);
          if (property.mls_id) pieces.push(`MLS ID ${property.mls_id}`);
          if (property.mls_office_id) pieces.push(`MLS Office ID ${property.mls_office_id}`);
          if (property.easybroker_public_id) pieces.push(`EasyBroker ${property.easybroker_public_id}`);
          return pieces.join(' - ');
        };

        const canSearch = (term) => term.length >= 2 || /^\d+$/.test(term);

        const setSelected = (property) => {
          if (!property || !property.id) {
            valueInput.value = '';
            searchInput.value = '';
            selectedLabel.textContent = 'Sin propiedad';
            selectedMeta.textContent = 'Selecciona una propiedad de los resultados';
            hideResults();
            root.dispatchEvent(new CustomEvent('property-lookup:cleared', { bubbles: true }));
            return;
          }

          const title = property.title || `Propiedad #${property.id}`;
          valueInput.value = property.id;
          searchInput.value = title;
          selectedLabel.textContent = title;
          selectedMeta.textContent = propertyMeta(property);
          hideResults();
          root.dispatchEvent(new CustomEvent('property-lookup:selected', { detail: property, bubbles: true }));
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
            title.textContent = property.title || `Propiedad #${property.id}`;

            const meta = document.createElement('span');
            meta.className = 'mt-0.5 block text-xs text-[var(--c-muted)]';
            meta.textContent = propertyMeta(property);

            button.append(title, meta);
            button.addEventListener('click', () => setSelected(property));
            results.appendChild(button);
          });
          results.classList.remove('hidden');
        };

        const searchProperties = (term, selectedId = null) => {
          if (abortController) abortController.abort();
          abortController = new AbortController();
          const url = new URL(searchUrl, window.location.origin);
          if (term) {
            url.searchParams.set('q', term);
          }
          if (selectedId) {
            url.searchParams.set('selected', selectedId);
          }

          fetch(url, {
            headers: { 'Accept': 'application/json' },
            signal: abortController.signal,
          })
            .then((response) => response.ok ? response.json() : Promise.reject(response))
            .then((payload) => {
              const items = payload.data || [];
              if (selectedId && !term) {
                if (items[0]) setSelected(items[0]);
                return;
              }
              renderResults(items);
            })
            .catch((error) => {
              if (error.name === 'AbortError') return;
              renderMessage('No se pudo buscar propiedades.');
            });
        };

        searchInput.addEventListener('input', () => {
          const term = searchInput.value.trim();
          window.clearTimeout(timer);
          if (!canSearch(term)) {
            hideResults();
            return;
          }
          timer = window.setTimeout(() => searchProperties(term), 250);
        });

        searchInput.addEventListener('focus', () => {
          const term = searchInput.value.trim();
          if (canSearch(term)) searchProperties(term);
        });

        clearButton.addEventListener('click', () => {
          setSelected(null);
          searchInput.focus();
        });

        document.addEventListener('click', (event) => {
          if (!root.contains(event.target)) hideResults();
        });

        root.smpPropertyLookup = {
          setSelected,
          clear: () => setSelected(null),
          searchSelected: () => {
            if (valueInput.value && !searchInput.value.trim()) {
              searchProperties('', valueInput.value);
            }
          },
        };

        root.smpPropertyLookup.searchSelected();
      },
    };
  </script>
@endonce

<script>
  window.smpPropertyLookup?.init(document.getElementById(@json($id)));
</script>
