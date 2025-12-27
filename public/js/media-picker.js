(() => {
  // ===== Utils =====
  const $ = (sel, el = document) => el.querySelector(sel);
  const $$ = (sel, el = document) => Array.from(el.querySelectorAll(sel));

  const token = $('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrf  = $('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const API   = '/api/media';

  const headers = () => {
    const h = { Accept: 'application/json' };
    if (token) h['Authorization'] = `Bearer ${token}`;
    if (csrf)  h['X-CSRF-TOKEN'] = csrf;
    return h;
  };

  // Wrapper fetch estándar con popup JSON
  async function apiFetch(url, options = {}) {
    const { triggerPopup = false, ...rest } = options;
    const method = (rest.method || 'GET').toUpperCase();
    const isMutation = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

    const mergedHeaders = { Accept: 'application/json', ...(rest.headers || {}) };
    if (token) mergedHeaders['Authorization'] = `Bearer ${token}`;
    if (isMutation && csrf) mergedHeaders['X-CSRF-TOKEN'] = csrf;

    const isFormData = typeof FormData !== 'undefined' && rest.body instanceof FormData;
    if (!isFormData && rest.body && !mergedHeaders['Content-Type']) {
      mergedHeaders['Content-Type'] = 'application/json';
    }

    const res = await fetch(url, { ...rest, method, headers: mergedHeaders });

    let json = null;
    try { json = await res.clone().json(); } catch (_) {}

    if (!res.ok) {
      const message = json?.message || res.statusText || 'Error de API';
      const code =
        json?.code ||
        (res.status === 403 ? 'FORBIDDEN' :
         res.status === 404 ? 'NOT_FOUND' :
         res.status === 422 ? 'VALIDATION_ERROR' : 'SERVER_ERROR');
      const errors = json?.errors || null;

      const detail = { success: false, message, code, data: json?.data ?? null, errors, status: res.status, raw: json };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
      throw detail;
    }

    const payload = json ?? { success: true, message: 'OK', code: null, data: null, errors: null };

    if (triggerPopup) {
      const detail = {
        success: true,
        message: payload.message || 'Operación realizada correctamente',
        code: payload.code || null,
        data: payload.data ?? null,
        errors: null,
        status: res.status,
        raw: payload
      };
      window.dispatchEvent(new CustomEvent('api:response', { detail }));
    }

    return payload;
  }

  const uid = (p='archive_manager') =>
    `${p}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2,8)}`;

  const formatBytes = (b) => {
    if (b == null) return '';
    const u=['B','KB','MB','GB']; let i=0, n=b;
    while (n>=1024 && i<u.length-1) { n/=1024; i++; }
    return `${n.toFixed(1)} ${u[i]}`;
  };

  const toast = (msg, ms=2200) => {
    const t = $('#archive_manager-toast') || $('#archive_manager-root #archive_manager-toast');
    if (!t) return;
    t.textContent = msg; t.classList.remove('hidden');
    clearTimeout(t._h); t._h = setTimeout(() => t.classList.add('hidden'), ms);
  };

  const lockScroll = (on=true) => {
    document.documentElement.style.overflow = on ? 'hidden' : '';
    document.body.style.overflow = on ? 'hidden' : '';
  };

  const animateIn  = (card) => { if (!card) return; requestAnimationFrame(() => { card.classList.remove('translate-y-2','opacity-0'); card.classList.add('opacity-100','translate-y-0'); }); };
  const animateOut = (card, done) => { if (!card) return done?.(); card.classList.remove('opacity-100','translate-y-0'); card.classList.add('translate-y-2','opacity-0'); setTimeout(() => done?.(), 180); };
  const openModal  = (id) => { const m=$(id); if(!m) return; m.classList.remove('hidden'); const c=m.querySelector('.transform'); if (c){ c.classList.add('translate-y-2','opacity-0'); animateIn(c);} lockScroll(true); };
  const closeModal = (id) => { const m=$(id); if(!m) return; const c=m.querySelector('.transform'); if(c){ animateOut(c,()=>{ m.classList.add('hidden'); lockScroll(false);}); } else { m.classList.add('hidden'); lockScroll(false);} };

  // ====== MediaFinder ======
  class MediaFinder {
    static active = null;
    static globalsBound = false;

    constructor({ input }) {
      this.input = input;
      this.instanceId = uid();
      this.mode = (input.getAttribute('data-filepicker') || 'single').toLowerCase();
      this.max  = (() => {
        const raw = input.getAttribute('data-fp-max');
        if (raw == null) return this.mode === 'multiple' ? Infinity : 1;
        const n = parseInt(raw, 10);
        return Number.isFinite(n) && n>0 ? n : (this.mode === 'multiple' ? Infinity : 1);
      })();
      const perAttr = parseInt(input.getAttribute('data-fp-per-page') || '10', 10);
      this.perPage  = Number.isFinite(perAttr) ? Math.max(1, Math.min(50, perAttr)) : 10;

      this.previewSelector = input.getAttribute('data-fp-preview') || '';
      this.previewEl = this.previewSelector ? $(this.previewSelector) : null;

      this.previewColumns = parseInt(input.getAttribute('data-fp-columns') || '8', 10);

      this.q=''; this.type=''; this.page=1; this.selected = new Set();

      // DOM
      this.root=$('#archive_manager-root');
      this.grid=$('[data-mf="grid"]',this.root);
      this.pageInfo=$('[data-mf="pageinfo"]',this.root);
      this.selInfo=$('[data-mf="selection"]',this.root);
      this.instanceLabel=$('[data-mf="instance"]',this.root);
      this.pickedWrap=$('[data-mf="picked-wrap"]',this.root);
      this.pickedGrid=$('[data-mf="picked"]',this.root);

      this._abort=null;
      if (this.instanceLabel) this.instanceLabel.textContent = `instancia ${this.instanceId}`;
      if (!MediaFinder.globalsBound) { MediaFinder.bindGlobalHandlers(); MediaFinder.globalsBound = true; }
    }

    static bindGlobalHandlers(){
      const root=$('#archive_manager-root');
      const uploadModal=$('#archive_manager-upload');
      const urlModal=$('#archive_manager-url');
      const editor=$('#archive_manager-editor');

      $('[data-mf="prev"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; if(i&&i.page>1){i.page--;i.load();}});
      $('[data-mf="next"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; if(i){i.page++;i.load();}});
      $('[data-mf="refresh"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.load();});
      $('[data-mf="use"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.applySelection();});
      $('[data-mf="clear"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.applySelection(true);});
      $('[data-mf="close"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.close();});
      $('[data-mf="backdrop"]',root)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.close();});

      $('[data-mf="search"]',root)?.addEventListener('input',(e)=>{const i=MediaFinder.active; if(!i) return; i.q=e.target.value; i.page=1; i.loadDebounced();});
      $('[data-mf="type"]',root)?.addEventListener('change',(e)=>{const i=MediaFinder.active; if(!i) return; i.type=e.target.value; i.page=1; i.load();});

      $('[data-mf="open-upload"]',root)?.addEventListener('click',()=>openModal('#archive_manager-upload'));
      $('[data-mf="open-url"]',root)?.addEventListener('click',()=>openModal('#archive_manager-url'));

      $('[data-up="save"]',uploadModal)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.uploadFromModal();});
      ['cancel','close','backdrop'].forEach(k=>{$(`[data-up="${k}"]`,uploadModal)?.addEventListener('click',()=>closeModal('#archive_manager-upload'));});

      $('[data-ur="save"]',urlModal)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.addUrlFromModal();});
      ['cancel','close','backdrop'].forEach(k=>{$(`[data-ur="${k}"]`,urlModal)?.addEventListener('click',()=>closeModal('#archive_manager-url'));});

      $('[data-ed="save"]',editor)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.saveEditor();});
      $('[data-ed="delete"]',editor)?.addEventListener('click',()=>{const i=MediaFinder.active; i&&i.deleteCurrent();});
      ['close','cancel'].forEach(k=>{$(`[data-ed="${k}"]`,editor)?.addEventListener('click',()=>$('#archive_manager-editor').classList.add('translate-x-full'));});

      document.addEventListener('keydown',(e)=>{ if(e.key!=='Escape') return;
        if (!$('#archive_manager-upload').classList.contains('hidden')) { closeModal('#archive_manager-upload'); return; }
        if (!$('#archive_manager-url').classList.contains('hidden'))   { closeModal('#archive_manager-url');   return; }
        if (!$('#archive_manager-root').classList.contains('hidden'))  { const i=MediaFinder.active; i&&i.close(); }
      });
    }

    // === estado/IO ===
    syncFromInput(){
      const raw=(this.input.value||'').trim();
      const ids=raw?raw.split(',').map(s=>s.trim()).filter(Boolean):[];
      this.selected=new Set(ids);
      if (Number.isFinite(this.max) && this.selected.size>this.max){
        this.selected=new Set(Array.from(this.selected).slice(0,this.max));
        toast(`Se limitaron a ${this.max} elementos (límite del campo)`);
      }
      const c=this.selected.size; this.selInfo.textContent=`${c} seleccionado${c===1?'':'s'}`;
    }

    open(){ MediaFinder.active=this; this.root.classList.remove('hidden'); lockScroll(true); this.syncFromInput(); this.renderPicked(); this.load(); }
    close(){ this.root.classList.add('hidden'); lockScroll(false); if(this._abort){this._abort.abort(); this._abort=null;} }

    // === UI helpers ===
    renderSkeleton(n=12){
      return Array.from({length:n}).map(()=>`
        <div class="rounded-xl border border-[var(--c-border)] overflow-hidden animate-pulse">
          <div class="aspect-square bg-[var(--c-elev)]"></div>
          <div class="p-2 space-y-2">
            <div class="h-3 rounded bg-[var(--c-border)]"></div>
            <div class="h-3 w-1/2 rounded bg-[var(--c-border)]/80"></div>
          </div>
        </div>
      `).join('');
    }
    renderEmpty(){
      return `
        <div class="col-span-full py-14 text-center">
          <div class="text-5xl mb-2">🗂️</div>
          <div class="text-base font-medium text-[var(--c-text)]">No hay medios aún</div>
          <div class="text-sm text-[var(--c-muted)]">Sube un archivo o agrega una URL externa</div>
        </div>
      `;
    }
    loadDebounced(){ clearTimeout(this._t); this._t=setTimeout(()=>this.load(),300); }

    updateDisabledState(){
      if(!this.grid) return;
      if (this.mode!=='multiple' || !Number.isFinite(this.max)) return;
      const atMax=this.selected.size>=this.max;
      $$('[data-mf-item]',this.grid).forEach(el=>{
        const act=el.classList.contains('mf-active');
        if(atMax && !act){ el.classList.add('pointer-events-none','opacity-50'); el.setAttribute('aria-disabled','true'); }
        else { el.classList.remove('pointer-events-none','opacity-50'); el.removeAttribute('aria-disabled'); }
      });
    }

    async load(){
      if(!this.grid) return;
      if(this._abort) this._abort.abort();
      this._abort=new AbortController();
      const {signal}=this._abort;

      this.grid.innerHTML=this.renderSkeleton();
      try{
        const p=new URLSearchParams({ page:String(this.page), per_page:String(this.perPage) });
        if(this.q)   p.append('search',this.q);
        if(this.type)p.append('type',this.type);

        const payload = await apiFetch(`${API}?${p.toString()}`, { signal });
        const data = payload?.data || {};

        this.pageInfo.textContent=`Página ${data.current_page} de ${data.last_page||1}`;
        $('[data-mf="prev"]').disabled = (data.current_page || 1) <= 1;
        $('[data-mf="next"]').disabled = (data.current_page || 1) >= (data.last_page || 1);

        const items=data.data||[];
        if (!items.length){ this.grid.innerHTML=this.renderEmpty(); }
        else{
          this.grid.innerHTML = items.map(it => this.renderItem(it)).join('');

          $$('[data-mf-item]',this.grid).forEach(card=>{
            card.addEventListener('click',()=>this.toggle(card.dataset.id));
          });

          $$('[data-mf-item]',this.grid).forEach(card=>{
            if(this.selected.has(card.dataset.id)){
              card.classList.add('mf-active','ring-2','ring-[var(--c-primary)]');
              card.setAttribute('aria-pressed','true');
            } else { card.setAttribute('aria-pressed','false'); }
          });

          $$('[data-mf-edit]',this.grid).forEach(btn=>{
            btn.addEventListener('click',(e)=>{ e.stopPropagation(); const id=btn.getAttribute('data-mf-edit'); this.openEditor(id); });
          });

          this.updateDisabledState();
        }
      }catch(err){
        if (err.name==='AbortError') return;
        console.error(err);
        // apiFetch ya mostró popup de error
        this.grid.innerHTML = `<div class="col-span-full text-center text-sm text-red-500">No se pudieron cargar los medios.</div>`;
      } finally { this._abort=null; }
    }

    // ==== PICKED (miniaturas que respetan tamaño/proporción) ====
    async renderPicked(){
      const wrap=this.pickedWrap, grid=this.pickedGrid;
      if(!wrap || !grid) return;
      const ids=Array.from(this.selected);
      if(!ids.length){ wrap.classList.add('hidden'); grid.innerHTML=''; return; }
      wrap.classList.remove('hidden');

      const items=(await Promise.all(
        ids.slice(0,100).map(id =>
          apiFetch(`${API}/${id}`).then(p=>p?.data ?? null).catch(()=>null)
        )
      )).filter(Boolean);

      const card = (it) => {
        const thumb = this.thumbFor(it, 'contain'); // 👈 importante: contain
        return `
          <div class="relative border border-[var(--c-border)] rounded-lg overflow-hidden" role="listitem" data-pk="${it.id}">
            <button type="button" class="absolute -top-1 -right-1 z-10 px-1.5 py-0.5 rounded-md bg-[var(--c-surface)]/90 border border-[var(--c-border)] text-[10px] shadow text-[var(--c-text)]" data-pk-remove="${it.id}" aria-label="Quitar">✕</button>
            <!-- Alturas fijas por breakpoint; la imagen se adapta sin recortarse -->
            <div class="w-full h-14 sm:h-16 md:h-20 lg:h-24 bg-[var(--c-elev)] flex items-center justify-center overflow-hidden">
              ${thumb}
            </div>
          </div>
        `;
      };

      grid.innerHTML = items.map(card).join('');
      $$('[data-pk-remove]',grid).forEach(btn=>{
        btn.addEventListener('click',(e)=>{ e.stopPropagation(); const id=btn.getAttribute('data-pk-remove'); this.removeFromSelection(id); });
      });
    }

    removeFromSelection(id){
      if(!this.selected.has(String(id))) return;
      this.selected.delete(String(id));
      const ids=Array.from(this.selected);
      this.input.value = (this.mode==='single'||this.max===1) ? (ids[0]||'') : ids.join(',');
      try{ this.input.dispatchEvent(new Event('change',{bubbles:true})); }catch(_){}
      const c=this.selected.size; this.selInfo.textContent=`${c} seleccionado${c===1?'':'s'}`;
      const card=$(`[data-mf-item][data-id="${id}"]`,this.grid); if(card){ card.classList.remove('mf-active','ring-2','ring-[var(--c-primary)]'); card.setAttribute('aria-pressed','false'); }
      this.updateDisabledState(); this.renderPicked();
    }

    toggle(id){
      if (this.mode==='single' || this.max===1){
        this.selected.clear(); this.selected.add(id);
        $$('[data-mf-item].mf-active',this.grid).forEach(el=>{el.classList.remove('mf-active','ring-2','ring-[var(--c-primary)]'); el.setAttribute('aria-pressed','false');});
        const c=$(`[data-mf-item][data-id="${id}"]`,this.grid);
        if(c){ c.classList.add('mf-active','ring-2','ring-[var(--c-primary)]'); c.setAttribute('aria-pressed','true'); }
        this.selInfo.textContent='1 seleccionado';
        this.input.value=id; try{ this.input.dispatchEvent(new Event('change',{bubbles:true})); }catch(_){}
        this.renderPicked(); this.updateDisabledState(); return;
      }
      const already=this.selected.has(id);
      if(!already && this.selected.size>=this.max){ toast(`Límite: puedes seleccionar hasta ${this.max}`); this.updateDisabledState(); return; }
      if (already) this.selected.delete(id); else this.selected.add(id);

      const card=$(`[data-mf-item][data-id="${id}"]`,this.grid);
      if(card){
        card.classList.toggle('mf-active'); card.classList.toggle('ring-2'); card.classList.toggle('ring-[var(--c-primary)]');
        card.setAttribute('aria-pressed', card.classList.contains('mf-active') ? 'true' : 'false');
      }

      const ids=Array.from(this.selected);
      this.input.value=ids.join(',');
      try{ this.input.dispatchEvent(new Event('change',{bubbles:true})); }catch(_){}
      this.renderPicked();
      this.selInfo.textContent=`${this.selected.size} seleccionados`;
      this.updateDisabledState();
    }

    applySelection(clear=false){
      if (clear) this.selected.clear();
      let ids=Array.from(this.selected);
      if (this.mode!=='single' && Number.isFinite(this.max) && ids.length>this.max){
        ids=ids.slice(0,this.max); this.selected=new Set(ids); toast(`Se aplicaron solo ${this.max} elementos (límite)`);
      }
      this.input.value = (this.mode==='single'||this.max===1) ? (clear?'':(ids[0]||'')) : (clear?'':ids.join(','));
      try{ this.input.dispatchEvent(new Event('change',{bubbles:true})); }catch(_){}
      if (!(window && window.MediaInputsExtActive)) {
        this.renderPreview(ids);
      }
      if (!clear){ this.close(); toast('Selección aplicada'); } else { this.selInfo.textContent='0 seleccionados'; }
      this.updateDisabledState(); this.renderPicked();
    }

    // ==== PREVIEW de inputs (también contain y mini) ====
    async renderPreview(ids){
      if(!this.previewEl) return;
      this.previewEl.innerHTML='';
      if(!ids.length){ this.previewEl.innerHTML = `<div class="text-sm text-[var(--c-muted)]">Sin selección</div>`; return; }

      const items=(await Promise.all(
        ids.slice(0,48).map(id =>
          apiFetch(`${API}/${id}`).then(p=>p?.data ?? null).catch(()=>null)
        )
      )).filter(Boolean);

      const card = (it) => {
        const thumb = this.thumbFor(it, 'contain'); // 👈 contain
        return `
          <div class="rounded-lg overflow-hidden border border-[var(--c-border)]">
            <div class="w-full h-14 sm:h-16 md:h-20 bg-[var(--c-elev)] flex items-center justify-center overflow-hidden">
              ${thumb}
            </div>
          </div>
        `;
      };

      this.previewEl.classList.add('grid', `grid-cols-${this.previewColumns}`, 'gap-2');
      this.previewEl.innerHTML = items.map(card).join('');
    }

    // fit: 'cover' (cards) | 'contain' (miniaturas que respetan tamaño)
    thumbFor(it, fit='cover'){
      const url = it.url || '';
      const t = it.type;
      if (t === 'image'){
        if (fit === 'contain') return `<img src="${url}" alt="" class="max-h-full max-w-full object-contain">`;
        return `<img src="${url}" alt="" class="w-full h-full object-cover">`;
      }
      if (t === 'video') return `<div class="text-center text-xs"><div class="text-2xl">🎬</div><div class="text-[var(--c-muted)]">${it.provider||'video'}</div></div>`;
      if (t === 'audio') return `<div class="text-2xl">🎵</div>`;
      return `<div class="text-2xl">📄</div>`;
    }

    // grid principal (cards cuadradas con cover)
    renderItem(it){
      const thumb = this.thumbFor(it, 'cover');
      const size  = formatBytes(it.size_bytes);
      const id    = it.id;
      const name  = it.name ? `<div class="text-[11px] text-[var(--c-text)] line-clamp-1" title="${it.name}">${it.name}</div>` : '';
      return `
        <div class="group relative" role="listitem">
          <button type="button" class="w-full text-left rounded-xl overflow-hidden border border-[var(--c-border)] hover:shadow transition ring-offset-2" data-mf-item data-id="${id}" aria-pressed="false">
            <div class="aspect-square bg-[var(--c-elev)] flex items-center justify-center overflow-hidden">
              ${thumb}
            </div>
            <div class="p-2">
              <div class="text-xs font-medium line-clamp-1 text-[var(--c-text)]">#${id} • ${it.type||''}</div>
              ${name}
              <div class="text-[11px] text-[var(--c-muted)]">${size||''}</div>
            </div>
          </button>
          <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
            <button type="button" class="px-2 py-1 rounded-md bg-[var(--c-surface)]/90 border border-[var(--c-border)] shadow text-[var(--c-text)]" data-mf-edit="${id}" aria-label="Editar">⋯</button>
          </div>
        </div>
      `;
    }

    // === Subir / URL / Editor (igual que antes) ===
    async uploadFromModal(){
      const up=$('#archive_manager-upload');
      const file=$('[data-up="file"]',up)?.files?.[0];
      const name=$('[data-up="name"]',up)?.value?.trim();
      const alt =$('[data-up="alt"]',up)?.value?.trim();
      if (!file){
        window.dispatchEvent(new CustomEvent('api:response', {
          detail: {
            success: false,
            message: 'Selecciona un archivo',
            code: 'CLIENT_VALIDATION',
            data: null,
            errors: { file: ['Requerido'] },
            status: 0,
            raw: null
          }
        }));
        return;
      }
      const fd=new FormData(); fd.append('file',file); if(name) fd.append('name',name); if(alt) fd.append('alt',alt);
      try{
        await apiFetch(API,{ method:'POST', body: fd, triggerPopup: true });
        $('[data-up="file"]',up).value=''; $('[data-up="name"]',up).value=''; $('[data-up="alt"]',up).value='';
        closeModal('#archive_manager-upload'); this.page=1; this.load();
      }catch(e){ console.error(e); /* popup ya mostrado */ }
    }

    async addUrlFromModal(){
      const um=$('#archive_manager-url');
      const url=$('[data-ur="url"]',um)?.value?.trim();
      const type=$('[data-ur="type"]',um)?.value || 'video';
      const provider=($('[data-ur="provider"]',um)?.value || 'external').trim() || 'external';
      const name=$('[data-ur="name"]',um)?.value?.trim();
      const alt =$('[data-ur="alt"]',um)?.value?.trim();
      if(!url) {
        window.dispatchEvent(new CustomEvent('api:response', {
          detail: {
            success: false,
            message: 'Ingresa una URL válida',
            code: 'CLIENT_VALIDATION',
            data: null,
            errors: { url: ['URL inválida'] },
            status: 0,
            raw: null
          }
        }));
        return;
      }
      try{
        const body={url,type,provider}; if(name) body.name=name; if(alt) body.alt=alt;
        await apiFetch(API,{ method:'POST', body: JSON.stringify(body), triggerPopup: true });
        $('[data-ur="url"]',um).value=''; $('[data-ur="name"]',um).value=''; $('[data-ur="alt"]',um).value=''; $('[data-ur="provider"]',um).value='vimeo';
        closeModal('#archive_manager-url'); this.page=1; this.load();
      }catch(e){ console.error(e); /* popup ya mostrado */ }
    }

    openEditor(id){ this.loadEditor(id); $('#archive_manager-editor').classList.remove('translate-x-full'); }
    closeEditor(){ $('#archive_manager-editor').classList.add('translate-x-full'); }
    toggleVideoUrlVisibility(isVideo){ const wrap=$('[data-ed="url-wrap"]',$('#archive_manager-editor')); if(!wrap) return; wrap.classList.toggle('hidden',!isVideo); }

    async loadEditor(id){
      const ed=$('#archive_manager-editor'); ed.dataset.currentId=id;
      try{
        const payload = await apiFetch(`${API}/${id}`);
        const it = payload?.data || {};
        $('[data-ed="id"]',ed).value=it.id;
        $('[data-ed="type"]',ed).value=it.type || 'document';
        $('[data-ed="provider"]',ed).value=it.provider || '';
        $('[data-ed="name"]',ed).value=it.name || '';
        $('[data-ed="alt"]',ed).value=it.alt || '';
        $('[data-ed="url"]',ed).value=it.url || '';
        this.toggleVideoUrlVisibility((it.type||'')==='video');
        $('[data-ed="thumb"]',ed).innerHTML = `
          <div class="aspect-video bg-[var(--c-elev)] flex items-center justify-center overflow-hidden">
            ${this.thumbFor(it,'cover')}
          </div>
          <div class="p-2 text-xs text-[var(--c-muted)]">#${it.id} • ${it.mime_type||''} • ${formatBytes(it.size_bytes)||''}</div>
        `;
      }catch(e){ console.error(e); /* popup ya mostrado */ }
    }

    async saveEditor(){
      const ed=$('#archive_manager-editor');
      const id=ed.dataset.currentId;
      const type=$('[data-ed="type"]',ed).value;
      const name=$('[data-ed="name"]',ed).value.trim();
      const alt =$('[data-ed="alt"]',ed).value.trim();
      const url =$('[data-ed="url"]',ed).value.trim();
      const payload={};
      if(name!=='') payload.name=name;
      if(alt!=='')  payload.alt =alt;
      if(type==='video' && url!=='') payload.url=url;
      if(!Object.keys(payload).length){
        window.dispatchEvent(new CustomEvent('api:response', {
          detail: {
            success: false,
            message: 'Nada para guardar',
            code: 'NO_CHANGES',
            data: null,
            errors: null,
            status: 0,
            raw: null
          }
        }));
        return;
      }
      try{
        await apiFetch(`${API}/${id}`,{ method:'PATCH', body: JSON.stringify(payload), triggerPopup: true });
        this.closeEditor(); this.load();
      }catch(e){ console.error(e); /* popup ya mostrado */ }
    }

    async deleteCurrent(){
      const ed=$('#archive_manager-editor'); const id=ed.dataset.currentId;
      if(!confirm('¿Eliminar este archivo? Esta acción no se puede deshacer.')) return;
      try{
        await apiFetch(`${API}/${id}`,{ method:'DELETE', triggerPopup: true });
        this.closeEditor();
        this.selected.delete(String(id));
        this.selInfo.textContent=`${this.selected.size} seleccionados`;
        this.input.value=Array.from(this.selected).join(',');
        try{ this.input.dispatchEvent(new Event('change',{bubbles:true})); }catch(_){}
        this.renderPicked();
        const card=$(`[data-mf-item][data-id="${id}"]`,this.grid);
        card?.classList.remove('mf-active','ring-2','ring-[var(--c-primary)]');
        this.load();
      }catch(e){ console.error(e); /* popup ya mostrado */ }
    }
  }

  // Instancias por input
  const instances=new Map();
  const openFor=(input)=>{ const id=input.id||uid('input'); if(!input.id) input.id=id; if(!instances.has(id)) instances.set(id,new MediaFinder({input})); instances.get(id).open(); };
  window.openMediaPickerFor = (input) => openFor(input);

  // Abrir desde botón
  $$('[data-fp-open]').forEach(btn=>btn.addEventListener('click',()=>{
    const explicit=btn.getAttribute('data-fp-open');
    if(explicit && explicit.trim()){
      const input=document.querySelector(explicit); if(input){ openFor(input); return; }
    }
    const scope=btn.closest('[data-fp-scope]')||btn.parentElement||document;
    let input=scope.querySelector('input[data-filepicker]');
    if(!input) input=document.querySelector('input[data-filepicker]');
    if(input) openFor(input);
  }));

  // Doble click en input
  $$('input[data-filepicker]').forEach(inp=>{
    inp.addEventListener('dblclick',()=>openFor(inp));
  });
})();

