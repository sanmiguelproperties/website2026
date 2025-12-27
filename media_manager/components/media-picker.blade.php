{{-- MODAL raíz (biblioteca) --}}
<div id="archive_manager-root" class="fixed inset-0 z-[9999] hidden" aria-modal="true" role="dialog">
  <div class="fixed inset-0 bg-black/45 backdrop-blur-sm" data-mf="backdrop"></div>

   <div class="relative mx-auto my-0 w-screen max-w-[98vw] md:my-10 md:w-[98vw]">
     {{-- Móvil 100vh; Escritorio altura fija para que NO se salga --}}
     <div class="rounded-none md:rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)]/90 backdrop-blur-xl shadow-2xl overflow-hidden flex flex-col h-[100vh] md:h-[80vh] md:max-h-[80vh]">
      {{-- Header --}}
      <div class="flex items-center justify-between px-3 md:px-4 py-3 border-b border-[var(--c-border)]">
        <div class="flex items-center gap-3">
          <div class="text-lg font-semibold text-[var(--c-text)]">Biblioteca de medios</div>
          <span class="hidden md:inline text-xs px-2 py-0.5 rounded-full bg-[var(--c-elev)] text-[var(--c-text)]" data-mf="instance"></span>
        </div>
        <button type="button" class="p-2 rounded-lg hover:bg-[var(--c-elev)] text-[var(--c-text)]" data-mf="close" aria-label="Cerrar">✕</button>
      </div>

      {{-- Toolbar (sticky) --}}
      <div class="px-3 md:px-4 py-3 border-b border-[var(--c-border)] sticky top-0 bg-[var(--c-surface)]/90 backdrop-blur-xl z-10">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 items-stretch">
          <div class="flex flex-wrap gap-2 content-start">
            <input type="text" data-mf="search" placeholder="Buscar por nombre, URL, MIME…" class="min-w-[160px] flex-1 rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)] placeholder:text-[var(--c-muted)]" aria-label="Buscar">
            <select data-mf="type" class="rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" aria-label="Filtrar por tipo">
              <option value="">Todos</option>
              <option value="image">Imágenes</option>
              <option value="video">Videos</option>
              <option value="audio">Audios</option>
              <option value="document">Documentos</option>
            </select>
            <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-mf="refresh">Actualizar</button>
          </div>
          <div class="flex flex-wrap gap-2 content-start">
            <button class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:bg-[var(--c-primary)]/90 shadow" data-mf="open-upload">Subir archivo</button>
            <button class="px-3 py-2 rounded-lg bg-[var(--c-accent)] text-[var(--c-primary-ink)] hover:opacity-90 shadow" data-mf="open-url">Agregar URL / Video</button>
          </div>
        </div>
      </div>

      {{-- Cuerpo con scroll interno y sin desbordes --}}
      <div class="px-3 md:px-4 pt-3 pb-2 flex-1 min-h-0 overflow-y-auto">
        {{-- Panel seleccionados: ahora respeta el tamaño de la imagen (object-contain) y tiene scroll --}}
        <div class="mb-3 rounded-xl border border-[var(--c-border)] p-3 bg-[var(--c-surface)]/70 hidden" data-mf="picked-wrap">
          <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-medium text-[var(--c-text)]">Archivos seleccionados</div>
            <button type="button" class="text-xs md:text-sm px-2 py-1 rounded bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-mf="picked-clear">Quitar todo</button>
          </div>
          <div class="max-h-36 md:max-h-28 ">
            {{-- grid densa; cada celda centra la imagen y NO la recorta --}}
            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 min-w-0" data-mf="picked" role="list"></div>
          </div>
        </div>

        {{-- Grilla principal (se mantiene cover para cards bonitas) --}}
        <div class="h-[calc(100vh-20rem)] md:h-[calc(80vh-14rem)] max-h-full ">
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3" data-mf="grid" role="list"></div>

          <div class="flex justify-between items-center mt-4 pb-2">
            <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50" data-mf="prev">Anterior</button>
            <div class="text-sm text-[var(--c-muted)]" data-mf="pageinfo">Página 1</div>
            <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50" data-mf="next">Siguiente</button>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="px-3 md:px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-between">
        <div class="text-sm text-[var(--c-muted)]" data-mf="selection">0 seleccionados</div>
        <div class="flex items-center gap-2">
          <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-mf="clear">Limpiar</button>
          <button class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:bg-[var(--c-primary)]/90 shadow" data-mf="use">Usar selección</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Panel Editor --}}
  <div id="archive_manager-editor" class="fixed right-0 top-0 h-full w-full max-w-md bg-[var(--c-surface)] shadow-2xl border-l border-[var(--c-border)] translate-x-full transition-transform duration-200 z-[75] flex flex-col">
    <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
      <div class="font-semibold text-[var(--c-text)]">Editar medio</div>
      <button class="p-2 rounded-lg hover:bg-[var(--c-elev)] text-[var(--c-text)]" data-ed="close" aria-label="Cerrar">✕</button>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-4">
      <div class="rounded-xl overflow-hidden border border-[var(--c-border)]" data-ed="thumb"></div>
      <div class="grid gap-3">
        <div>
          <label class="text-sm text-[var(--c-muted)]">ID</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-elev)] text-[var(--c-text)]" data-ed="id" readonly>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-[var(--c-muted)]">Tipo</label>
            <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-elev)] text-[var(--c-text)]" data-ed="type" readonly>
          </div>
          <div>
            <label class="text-sm text-[var(--c-muted)]">Proveedor</label>
            <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-elev)] text-[var(--c-text)]" data-ed="provider" readonly>
          </div>
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Nombre</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ed="name" placeholder="Nombre legible">
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Texto alternativo (alt)</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ed="alt" placeholder="Descripción corta para accesibilidad">
        </div>
        <div data-ed="url-wrap" class="hidden">
          <label class="text-sm text-[var(--c-muted)]">URL (solo para videos embebidos)</label>
          <input type="url" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ed="url" placeholder="https://…">
          <p class="text-xs text-[var(--c-muted)] mt-1">Solo modificable cuando el tipo es <strong>video</strong>.</p>
        </div>
      </div>
      <div class="flex items-center justify-between pt-2">
        <button class="px-3 py-2 rounded-lg bg-[var(--c-danger)] text-white hover:bg-[var(--c-danger)]/90" data-ed="delete">Eliminar</button>
        <div class="flex gap-2">
          <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-ed="cancel">Cancelar</button>
          <button class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:bg-[var(--c-primary)]/90" data-ed="save">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Toast --}}
  <div id="archive_manager-toast" class="fixed left-1/2 -translate-x-1/2 bottom-6 px-4 py-2 rounded-xl text-[var(--c-primary-ink)] bg-[var(--c-surface)]/90 hidden" role="status" aria-live="polite"></div>
</div>

{{-- MODAL: Subir archivo --}}
<div id="archive_manager-upload" class="fixed inset-0 z-[10000] hidden">
  <div class="fixed inset-0 bg-black/45 backdrop-blur-sm" data-up="backdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-lg">
    <div class="transform transition duration-200 ease-out translate-y-2 opacity-0 rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)]/90 backdrop-blur-xl shadow-2xl overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <div class="font-semibold text-[var(--c-text)]">Subir archivo</div>
        <button class="p-2 rounded-lg hover:bg-[var(--c-elev)] text-[var(--c-text)]" data-up="close" aria-label="Cerrar">✕</button>
      </div>
      <div class="p-4 grid gap-3">
        <div>
          <label class="text-sm text-[var(--c-muted)]">Archivo</label>
          <input type="file" class="block w-full text-sm text-[var(--c-text)]" data-up="file">
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Nombre (opcional)</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-up="name" placeholder="Nombre legible">
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Alt (opcional)</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-up="alt" placeholder="Texto alternativo">
        </div>
      </div>
      <div class="px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-end gap-2">
        <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-up="cancel">Cancelar</button>
        <button class="px-3 py-2 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] hover:bg-[var(--c-primary)]/90" data-up="save">Subir</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Agregar URL / Video --}}
<div id="archive_manager-url" class="fixed inset-0 z-[10000] hidden">
  <div class="fixed inset-0 bg-black/45 backdrop-blur-sm" data-ur="backdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-lg">
    <div class="transform transition duration-200 ease-out translate-y-2 opacity-0 rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)]/90 backdrop-blur-xl shadow-2xl overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <div class="font-semibold text-[var(--c-text)]">Agregar URL / Video</div>
        <button class="p-2 rounded-lg hover:bg-[var(--c-elev)] text-[var(--c-text)]" data-ur="close" aria-label="Cerrar">✕</button>
      </div>
      <div class="p-4 grid gap-3">
        <div>
          <label class="text-sm text-[var(--c-muted)]">URL</label>
          <input type="url" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ur="url" placeholder="https://vimeo.com/…">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-[var(--c-muted)]">Tipo</label>
            <select class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ur="type">
              <option value="video" selected>video</option>
              <option value="image">image</option>
              <option value="audio">audio</option>
              <option value="document">document</option>
            </select>
          </div>
          <div>
            <label class="text-sm text-[var(--c-muted)]">Proveedor</label>
            <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ur="provider" value="vimeo" placeholder="vimeo / youtube / external">
          </div>
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Nombre (opcional)</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ur="name" placeholder="Nombre legible">
        </div>
        <div>
          <label class="text-sm text-[var(--c-muted)]">Alt (opcional)</label>
          <input type="text" class="w-full rounded-lg border border-[var(--c-border)] px-3 py-2 bg-[var(--c-surface)] text-[var(--c-text)]" data-ur="alt" placeholder="Texto alternativo">
        </div>
      </div>
      <div class="px-4 py-3 border-t border-[var(--c-border)] flex items-center justify-end gap-2">
        <button class="px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80" data-ur="cancel">Cancelar</button>
        <button class="px-3 py-2 rounded-lg bg-[var(--c-accent)] text-[var(--c-primary-ink)] hover:opacity-90" data-ur="save">Agregar</button>
      </div>
    </div>
  </div>
</div>