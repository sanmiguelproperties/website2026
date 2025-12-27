@extends('layouts.app')

@section('title', 'Embudo de Ventas • Dashboard Base')

@section('content')
<!-- ===================== VISTA: Embudo de Ventas ===================== -->
<header class="flex items-center justify-between">
  <div class="flex items-center gap-2 text-sm">
    <a href="#" class="text-[var(--c-muted)] hover:text-[var(--c-text)]">CRM</a>
    <span class="opacity-50">/</span>
    <span class="font-medium">Embudo de ventas</span>
  </div>
  <div class="flex items-center gap-2">
    <button id="dash-funnel-add" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded-xl bg-[var(--c-primary)] text-white shadow-soft">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
      Nuevo lead
    </button>
  </div>
</header>

<!-- Board scrollable en pantallas pequeñas; grid en xl -->
<section id="dash-funnel-board" class="relative overflow-x-auto pb-2">
  <div class="min-w-[1200px] grid grid-cols-7 gap-4 xl:min-w-0">
    <!-- Stages -->
    <template id="dash-funnel-card-tpl">
      <article class="dash-card select-none rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] p-3 shadow-soft cursor-move" draggable="true" role="article" aria-grabbed="false">
        <header class="flex items-center justify-between gap-2">
          <h4 class="text-sm font-semibold">Nuevo lead</h4>
          <span class="text-[10px] px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">S/ 0</span>
        </header>
        <p class="mt-1 text-xs text-[var(--c-muted)]">Sin asignar</p>
      </article>
    </template>

    <!-- Prospección -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="prospeccion">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Prospección</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-prospeccion" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Contacto inicial -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="contacto">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Contacto inicial</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-contacto" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Calificación -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="calificacion">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Calificación</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-calificacion" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Propuesta -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="propuesta">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Propuesta</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-propuesta" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Negociación -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="negociacion">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Negociación</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-negociacion" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Cierre (Ganado) -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="ganado">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Cierre ganado</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-ganado" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>

    <!-- Cierre (Perdido) -->
    <section class="dash-stage rounded-2xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] flex flex-col" data-stage="perdido">
      <header class="flex items-center justify-between px-4 py-3 border-b border-[var(--c-border)]">
        <h3 class="text-sm font-semibold">Cierre perdido</h3>
        <span class="dash-count text-xs px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">0</span>
      </header>
      <div id="dash-stage-perdido" class="dash-dropzone flex-1 overflow-y-auto p-3 space-y-3" role="list"></div>
      <div class="p-3 border-t border-[var(--c-border)]"><button class="dash-add text-xs px-2 py-1 rounded-lg ring-1 ring-[var(--c-border)]">Agregar</button></div>
    </section>
  </div>
</section>

<!-- Script de la vista (solo para el embudo; fácil de extraer luego) -->
<script id="dash-funnel-script">
  (function(){
    const board = document.getElementById('dash-funnel-board');
    const tpl = document.getElementById('dash-funnel-card-tpl');
    const addGlobal = document.getElementById('dash-funnel-add');
    const uid = () => 'dash-card-' + Math.random().toString(36).slice(2, 9);

    // Crea una tarjeta básica
    function createCard({title='Nuevo lead', owner='Sin asignar', amount='S/ 0'}={}){
      const node = tpl.content.firstElementChild.cloneNode(true);
      node.id = uid();
      node.querySelector('h4').textContent = title;
      node.querySelector('span').textContent = amount;
      node.querySelector('p').textContent = owner;
      return node;
    }

    // Inicial: añade ejemplos a Prospección
    const firstZone = document.getElementById('dash-stage-prospeccion');
    ['Acme S.A.', 'Globex', 'Soylent Corp.', 'Initech'].forEach((n,i)=>{
      firstZone.appendChild(createCard({title: n, owner: 'Sin asignar', amount: 'S/ ' + (i*500+500)}));
    });
    updateCounts();

    // Event delegation para drag & drop
    let dragId = null;
    board.addEventListener('dragstart', (e)=>{
      const card = e.target.closest('.dash-card');
      if(!card) return;
      dragId = card.id;
      e.dataTransfer.setData('text/plain', dragId);
      e.dataTransfer.effectAllowed = 'move';
      card.classList.add('opacity-70');
      card.setAttribute('aria-grabbed','true');
    });
    board.addEventListener('dragend', (e)=>{
      const card = e.target.closest('.dash-card');
      if(card){
        card.classList.remove('opacity-70');
        card.setAttribute('aria-grabbed','false');
      }
      dragId = null;
      clearHighlights();
      updateCounts();
    });

    board.addEventListener('dragover', (e)=>{
      const zone = e.target.closest('.dash-dropzone');
      if(!zone) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      zone.classList.add('ring-2','ring-[var(--c-primary)]/60');
    });

    board.addEventListener('dragleave', (e)=>{
      const zone = e.target.closest('.dash-dropzone');
      if(!zone) return;
      zone.classList.remove('ring-2','ring-[var(--c-primary)]/60');
    });

    board.addEventListener('drop', (e)=>{
      const zone = e.target.closest('.dash-dropzone');
      if(!zone) return;
      e.preventDefault();
      const id = e.dataTransfer.getData('text/plain') || dragId;
      const card = document.getElementById(id);
      if(card){ zone.appendChild(card); }
      zone.classList.remove('ring-2','ring-[var(--c-primary)]/60');
      updateCounts();
    });

    function clearHighlights(){
      document.querySelectorAll('.dash-dropzone').forEach(z=>z.classList.remove('ring-2','ring-[var(--c-primary)]/60'));
    }

    function updateCounts(){
      document.querySelectorAll('.dash-stage').forEach(stage=>{
        const count = stage.querySelectorAll('.dash-card').length;
        const badge = stage.querySelector('.dash-count');
        if(badge) badge.textContent = count;
      });
    }

    // Botón global y botones por columna para crear tarjetas
    addGlobal?.addEventListener('click', ()=>{
      firstZone.appendChild(createCard());
      updateCounts();
    });
    board.querySelectorAll('.dash-stage .dash-add').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const zone = btn.closest('.dash-stage').querySelector('.dash-dropzone');
        zone.appendChild(createCard());
        updateCounts();
      });
    });
  })();
</script>
@endsection