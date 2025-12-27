{{-- Media Input (Blade Component)
    Uso básico:
      <x-media-input name="thumbnail_id" mode="single" :max="1" />
      <x-media-input name="gallery_ids" mode="multiple" :max="6" />

    Atributos:
      - name:        nombre del input (opcional si solo usas el picker sin postear)
      - id:          id explícito del input
      - mode:        'single' | 'multiple' (default: single)
      - max:         máximo de seleccionados (solo en multiple). En single se forzará 1.
      - perPage:     cantidad por página en el picker (default: 10)
      - value:       valor inicial (id o ids separados por coma)
      - placeholder: placeholder del input
      - button:      texto del botón (default: "Seleccionar")
      - preview:     bool, si muestra contenedor de preview (default: true)
      - previewId:   id del contenedor de preview (si no se pasa, se autogenera)
      - readonly:    bool, hace el input no editable manualmente (default: true)
      - columns:     número de columnas para el grid de preview (default: 8)

    Requisitos:
      - Incluir el componente del picker en la vista (una sola vez):
          <x-media-picker />
      - El JS global para inputs ya fue agregado al layout:
          public/js/media-inputs.js
--}}
@props([
  'name' => null,
  'id' => null,
  'mode' => 'single',
  'max' => null,
  'perPage' => 10,
  'value' => '',
  'placeholder' => null,
  'button' => 'Seleccionar',
  'preview' => true,
  'previewId' => null,
  'readonly' => true,
  'inputClass' => '',
  'buttonClass' => '',
  'wrapperClass' => '',
  'previewClass' => '',
  'columns' => null,
])

@php
  $resolvedMode = in_array(strtolower($mode), ['single','multiple']) ? strtolower($mode) : 'single';
  $scopeId = 'mi_' . uniqid();
  $hasPreview = filter_var($preview, FILTER_VALIDATE_BOOLEAN);
  $resolvedId = $id ?: 'media_input_' . uniqid();
  $resolvedPreviewId = $hasPreview
      ? ($previewId ?: ($resolvedId . '_preview'))
      : null;

  $wrapperBase = 'rounded-2xl border border-slate-200/60 dark:border-slate-700/60 p-4 bg-white dark:bg-slate-900';
  $inputBase = 'w-full rounded-lg border px-3 py-2 bg-white dark:bg-slate-800';
  $buttonBase = 'px-3 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow';

  // Atributos data-* comunes
  $dataAttrs = [
    'data-filepicker' => $resolvedMode,
    'data-fp-per-page' => (int) $perPage,
  ];
  if ($resolvedMode === 'single') {
    $dataAttrs['data-fp-max'] = 1;
  } else {
    if (!is_null($max)) $dataAttrs['data-fp-max'] = (int) $max;
  }
  if ($hasPreview && $resolvedPreviewId) {
    $dataAttrs['data-fp-preview'] = '#' . $resolvedPreviewId;
  }
  if (!is_null($columns)) {
    $dataAttrs['data-fp-columns'] = (int) $columns;
  }
@endphp

<div data-fp-scope {{ $attributes->merge(['class' => trim($wrapperBase . ' ' . $wrapperClass)]) }}>
  <div class="flex items-center gap-3">
    <input
      type="text"
      @if($name) name="{{ $name }}" @endif
      id="{{ $resolvedId }}"
      value="{{ old($name ?? $resolvedId, $value) }}"
      @if($placeholder) placeholder="{{ $placeholder }}" @endif
      @if($readonly) readonly @endif
      class="{{ trim($inputBase . ' ' . $inputClass) }}"
      @foreach($dataAttrs as $k => $v)
        {{ $k }}="{{ $v }}"
      @endforeach
    >
    <button
      type="button"
      class="{{ trim($buttonBase . ' ' . $buttonClass) }}"
      data-fp-open
      aria-controls="archive_manager-root"
    >
      {{ $button }}
    </button>
  </div>

  @if($hasPreview && $resolvedPreviewId)
    <div id="{{ $resolvedPreviewId }}" class="{{ $previewClass ? $previewClass : 'mt-3' }}"></div>
  @endif
</div>