@extends('layouts.public')

@section('title', 'Agentes')

@section('content')
  <div class="pt-24">
    {{-- Hero / header (similar a agencias) --}}
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-from, rgba(79,70,229,.35));"></div>
        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-35" style="background-color: var(--fe-primary-to, rgba(16,185,129,.35));"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold" style="background-color: var(--fe-properties-badge_bg, #eef2ff); color: var(--fe-properties-badge_text, #4f46e5);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21a8 8 0 10-16 0" />
            </svg>
            Agentes MLS
          </div>

          <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
            Conoce a nuestros <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">agentes</span>
          </h1>
          <p class="mt-4 text-lg text-slate-600">
            Busca agentes, filtra por agencia y revisa sus propiedades.
          </p>
        </div>
      </div>
    </section>

    <section class="py-12 lg:py-16" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="mlsAgentsIndex()" x-init="init()">
        {{-- Search bar --}}
        <div class="rounded-2xl border p-4 sm:p-6 shadow-sm mb-8" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
          <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-end">
            <div class="flex-1">
              <label class="block text-xs font-semibold text-slate-600 mb-2">Buscar agente</label>
              <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-properties-filter_icon, #94a3b8);">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       x-model="filters.search"
                       @input.debounce.300ms="applyFilters()"
                       placeholder="Nombre, email, agencia, MLS ID…"
                       class="w-full pl-12 pr-4 py-3 rounded-xl transition-all focus:outline-none"
                       style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
              </div>
            </div>

            <div class="min-w-[220px]">
              <label class="block text-xs font-semibold text-slate-600 mb-2">Agencia (MLS Office ID)</label>
              <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 21V7l8-4v18" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V11l-6-4" />
                </svg>
                <input type="number"
                       x-model="filters.office_id"
                       @input.debounce.400ms="applyFilters()"
                       placeholder="Ej: 123"
                       class="w-full pl-12 pr-4 py-3 rounded-xl transition-all focus:outline-none"
                       style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
              </div>
              <p class="mt-2 text-xs text-slate-500">Si no lo sabes, déjalo vacío.</p>
            </div>

            <div class="min-w-[190px]">
              <label class="block text-xs font-semibold text-slate-600 mb-2">Orden</label>
              <select x-model="filters.order" @change="applyFilters()"
                      class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
                      style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
                <option value="name">Nombre</option>
                <option value="office_name">Agencia</option>
                <option value="email">Email</option>
                <option value="mls_agent_id">MLS ID</option>
                <option value="updated_at">Actualizado</option>
              </select>
            </div>

            <div class="min-w-[160px]">
              <label class="block text-xs font-semibold text-slate-600 mb-2">Dirección</label>
              <select x-model="filters.sort" @change="applyFilters()"
                      class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
                      style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #0f172a);">
                <option value="asc">Asc</option>
                <option value="desc">Desc</option>
              </select>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-slate-600">
              Mostrando <span x-text="pagination?.from || 0"></span> - <span x-text="pagination?.to || 0"></span>
              de <span x-text="pagination?.total || 0"></span>
            </div>

            <button @click="clearFilters()" x-show="hasFilters()"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar
            </button>
          </div>
        </div>

        {{-- Grid --}}
        <div id="agentsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"></div>

        {{-- Empty --}}
        <div id="agentsEmpty" class="hidden text-center py-16">
          <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21a8 8 0 10-16 0" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">No se encontraron agentes</h3>
          <p class="text-slate-600">Intenta ajustar la búsqueda o el filtro de agencia.</p>
        </div>

        {{-- Pagination --}}
        <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
          <button @click="goToPage((pagination?.current_page || 1) - 1)" :disabled="!(pagination?.current_page > 1)"
                  class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Anterior
          </button>
          <div class="text-sm text-slate-600">
            Página <span x-text="pagination?.current_page || 1"></span> de <span x-text="pagination?.last_page || 1"></span>
          </div>
          <button @click="goToPage((pagination?.current_page || 1) + 1)" :disabled="!(pagination?.current_page < pagination?.last_page)"
                  class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Siguiente
          </button>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    function mlsAgentsIndex() {
      return {
        filters: {
          search: '',
          office_id: '',
          order: 'name',
          sort: 'asc',
          per_page: 12,
          page: 1,
        },
        pagination: null,
        agents: [],

        init() {
          this.loadFiltersFromUrl();
          this.loadAgents();
          window.addEventListener('popstate', () => {
            this.loadFiltersFromUrl();
            this.loadAgents();
          });
        },

        loadFiltersFromUrl() {
          const p = new URLSearchParams(window.location.search);
          ['search', 'office_id', 'order', 'sort', 'per_page', 'page'].forEach((k) => {
            if (!p.has(k)) return;
            const v = p.get(k);
            if (['per_page', 'page', 'office_id'].includes(k)) this.filters[k] = v ? (parseInt(v, 10) || v) : '';
            else this.filters[k] = v;
          });
        },

        updateUrl() {
          const p = new URLSearchParams();
          Object.keys(this.filters).forEach((k) => {
            const v = this.filters[k];
            if (v === '' || v === null || v === undefined) return;
            if (k === 'order' && v === 'name') return;
            if (k === 'sort' && v === 'asc') return;
            if (k === 'per_page' && v === 12) return;
            if (k === 'page' && v === 1) return;
            p.set(k, String(v));
          });
          const url = p.toString() ? `${window.location.pathname}?${p.toString()}` : window.location.pathname;
          window.history.pushState(null, '', url);
        },

        hasFilters() {
          return !!(this.filters.search || this.filters.office_id);
        },

        applyFilters() {
          this.filters.page = 1;
          this.updateUrl();
          this.loadAgents();
        },

        clearFilters() {
          this.filters.search = '';
          this.filters.office_id = '';
          this.filters.order = 'name';
          this.filters.sort = 'asc';
          this.filters.per_page = 12;
          this.filters.page = 1;
          this.updateUrl();
          this.loadAgents();
        },

        goToPage(page) {
          if (!this.pagination) return;
          if (page < 1 || page > this.pagination.last_page) return;
          this.filters.page = page;
          this.updateUrl();
          this.loadAgents();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async loadAgents() {
          const grid = document.getElementById('agentsGrid');
          const empty = document.getElementById('agentsEmpty');

          grid.innerHTML = Array.from({ length: 6 }).map(() => `
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
              <div class="p-6 flex gap-4">
                <div class="size-14 skeleton rounded-2xl"></div>
                <div class="flex-1 space-y-3">
                  <div class="h-4 w-2/3 skeleton rounded"></div>
                  <div class="h-4 w-1/2 skeleton rounded"></div>
                  <div class="h-10 skeleton rounded-xl"></div>
                </div>
              </div>
            </div>
          `).join('');

          try {
            const p = new URLSearchParams();
            Object.keys(this.filters).forEach((k) => {
              const v = this.filters[k];
              if (v === '' || v === null || v === undefined) return;
              p.set(k, String(v));
            });

            const res = await fetch(`/api/public/mls-agents?${p.toString()}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (!res.ok || !data?.success) {
              grid.innerHTML = '';
              empty.classList.remove('hidden');
              return;
            }

            const pag = data.data;
            this.agents = pag?.data || [];
            this.pagination = {
              current_page: pag?.current_page || 1,
              last_page: pag?.last_page || 1,
              from: pag?.from || 0,
              to: pag?.to || 0,
              total: pag?.total || 0,
            };

            if (!this.agents.length) {
              grid.innerHTML = '';
              empty.classList.remove('hidden');
              return;
            }
            empty.classList.add('hidden');

            const esc = (s) => String(s ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'", '&#039;');

            grid.innerHTML = this.agents.map((a) => {
              const name = a.full_name || a.name || `Agente #${a.mls_agent_id}`;
              const office = a.office?.name || a.office_name || '';
              const officeId = a.mls_office_id || a.office?.mls_office_id || null;
              const img = a.photo || null;
              const propsCount = a.properties_count ?? 0;

              return `
                <div class="property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                  <div class="p-6">
                    <div class="flex items-start gap-4">
                      <div class="size-14 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">
                        ${img ? `<img src="${esc(img)}" alt="${esc(name)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null;this.style.display='none';" />` : `
                          <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" /></svg>
                        `}
                      </div>

                      <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                          <h3 class="text-lg font-bold text-slate-900 truncate">${esc(name)}</h3>
                          <span class="text-xs font-semibold text-slate-500">${esc(propsCount)} props</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600 truncate">${esc(a.email || '—')}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                          <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(79,70,229,0.10); color: rgb(79,70,229);">MLS #${esc(a.mls_agent_id)}</span>
                          ${officeId ? `<a href="/agencias/${esc(officeId)}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold hover:underline" style="background-color: rgba(16,185,129,0.12); color: rgb(5,150,105);">Agencia: ${esc(office || ('#' + officeId))}</a>` : (office ? `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(16,185,129,0.12); color: rgb(5,150,105);">Agencia: ${esc(office)}</span>` : '')}
                        </div>

                        <div class="mt-5">
                          <a href="/agentes/${esc(a.mls_agent_id)}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                            Ver agente
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            }).join('');
          } catch (e) {
            console.error(e);
            grid.innerHTML = '';
            empty.classList.remove('hidden');
          }
        }
      }
    }
  </script>
@endpush

