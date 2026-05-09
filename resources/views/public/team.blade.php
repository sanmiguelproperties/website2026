@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';

    $title = $isEn ? 'Our Team' : 'Nuestro Equipo';
    $subtitle = $isEn
        ? 'Meet the complete agency team, from leadership to every specialized area.'
        : 'Conoce al equipo completo de la agencia, desde direccion hasta cada area especializada.';
@endphp

@section('title', $title)

@section('content')
<div class="pt-24">
  <section class="relative overflow-hidden py-14 lg:py-20">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-28 right-0 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
      <div class="absolute -bottom-28 left-0 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl">
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
          {{ $title }}
        </h1>

        <p class="mt-4 text-lg text-slate-600">
          {{ $subtitle }}
        </p>
      </div>
    </div>
  </section>

  <section class="pb-16 lg:pb-24" style="background: linear-gradient(to bottom, var(--fe-properties-bg_from, #f8fafc), var(--fe-properties-bg_to, #ffffff));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="publicTeamPage()" x-init="init()">
      <div class="rounded-2xl border p-4 sm:p-6 shadow-sm mb-8" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
          <div class="md:col-span-6">
            <label class="block text-xs font-semibold text-slate-600 mb-2">{{ $isEn ? 'Search team member' : 'Buscar integrante' }}</label>
            <input
              type="search"
              x-model="filters.search"
              @input.debounce.350ms="applyFilters()"
              :placeholder="texts.searchPlaceholder"
              class="w-full px-4 py-3 rounded-xl transition-all focus:outline-none"
              style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);"
            />
          </div>

          <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-slate-600 mb-2">{{ $isEn ? 'Department' : 'Area' }}</label>
            <select
              x-model="filters.department"
              @change="applyFilters()"
              class="w-full px-4 py-3 rounded-xl transition-all appearance-none cursor-pointer focus:outline-none"
              style="background-color: var(--fe-properties-input_bg, #f8fafc); border: 1px solid var(--fe-properties-input_border, #e2e8f0); color: var(--fe-properties-input_text, #1C1C1C);"
            >
              <option value="" x-text="texts.allDepartments"></option>
              <template x-for="department in departments" :key="department.key">
                <option :value="department.key" x-text="departmentLabel(department)"></option>
              </template>
            </select>
          </div>

          <div class="md:col-span-2 flex items-end">
            <button
              @click="clearFilters()"
              x-show="hasFilters()"
              class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition"
            >
              {{ $isEn ? 'Clear' : 'Limpiar' }}
            </button>
          </div>
        </div>

        <div class="mt-4 text-sm text-slate-600">
          <span x-text="texts.showing"></span>
          <span x-text="pagination?.from || 0"></span>
          -
          <span x-text="pagination?.to || 0"></span>
          <span x-text="texts.of"></span>
          <span x-text="pagination?.total || 0"></span>
        </div>
      </div>

      <div id="teamLoading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" x-show="loading">
        <template x-for="idx in [1,2,3,4,5,6]" :key="idx">
          <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden p-5 space-y-4">
            <div class="h-40 skeleton rounded-xl"></div>
            <div class="h-4 skeleton rounded w-2/3"></div>
            <div class="h-4 skeleton rounded w-1/2"></div>
            <div class="h-12 skeleton rounded"></div>
          </div>
        </template>
      </div>

      <div id="teamGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" x-show="!loading && members.length">
        <template x-for="member in members" :key="member.id">
          <article class="property-card rounded-2xl overflow-hidden border shadow-sm" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
            <div class="h-56 overflow-hidden bg-slate-100">
              <img :src="member.photo_url || fallbackAvatar(member.full_name)" :alt="member.full_name" class="w-full h-full object-cover" loading="lazy" />
            </div>

            <div class="p-5">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="text-lg font-bold text-slate-900" x-text="member.full_name"></h3>
                  <p class="text-sm text-slate-600" x-text="member.position"></p>
                </div>
                <span x-show="member.is_featured" class="px-2 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700" x-text="texts.featured"></span>
              </div>

              <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500" x-text="member.department || texts.noDepartment"></p>

              <div class="mt-3 text-sm text-slate-600 rich-content" x-html="memberBioHtml(member)"></div>

              <div class="mt-4 flex flex-wrap gap-2" x-show="Array.isArray(member.specialties) && member.specialties.length">
                <template x-for="(specialty, index) in member.specialties.slice(0, 3)" :key="`${member.id}-specialty-${index}`">
                  <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-700" x-text="specialty"></span>
                </template>
              </div>

              <div class="mt-5 flex flex-wrap items-center gap-2">
                <a x-show="member.email" :href="`mailto:${member.email}`" class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-semibold border border-slate-200 hover:bg-slate-50">
                  {{ $isEn ? 'Email' : 'Correo' }}
                </a>
                <a x-show="member.phone" :href="`tel:${sanitizePhone(member.phone)}`" class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-semibold border border-slate-200 hover:bg-slate-50">
                  {{ $isEn ? 'Call' : 'Llamar' }}
                </a>
                <a x-show="member.linkedin_url" :href="member.linkedin_url" target="_blank" rel="noopener" class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-semibold border border-slate-200 hover:bg-slate-50">
                  LinkedIn
                </a>
              </div>
            </div>
          </article>
        </template>
      </div>

      <div id="teamEmpty" class="text-center py-16" x-show="!loading && !members.length">
        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
          <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21a8 8 0 10-16 0" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">{{ $isEn ? 'No team members found' : 'No se encontraron integrantes' }}</h3>
        <p class="text-slate-600">{{ $isEn ? 'Try adjusting your search or department filter.' : 'Intenta ajustar la busqueda o el filtro por area.' }}</p>
      </div>

      <div class="mt-10 pt-8 border-t border-slate-200 flex items-center justify-between gap-3">
        <button
          @click="goToPage((pagination?.current_page || 1) - 1)"
          :disabled="!(pagination?.current_page > 1)"
          class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          x-text="texts.previous"
        ></button>

        <div class="text-sm text-slate-600">
          <span x-text="texts.page"></span>
          <span x-text="pagination?.current_page || 1"></span>
          <span x-text="texts.of"></span>
          <span x-text="pagination?.last_page || 1"></span>
        </div>

        <button
          @click="goToPage((pagination?.current_page || 1) + 1)"
          :disabled="!(pagination?.current_page < pagination?.last_page)"
          class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          x-text="texts.next"
        ></button>
      </div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
function publicTeamPage() {
  const isEn = (window.__PUBLIC_LOCALE__ || 'es') === 'en';

  return {
    loading: false,
    members: [],
    departments: [],
    pagination: null,
    filters: {
      search: '',
      department: '',
      page: 1,
      per_page: 12,
    },
    texts: {
      allDepartments: isEn ? 'All departments' : 'Todas las areas',
      showing: isEn ? 'Showing' : 'Mostrando',
      of: isEn ? 'of' : 'de',
      page: isEn ? 'Page' : 'Pagina',
      next: isEn ? 'Next' : 'Siguiente',
      previous: isEn ? 'Previous' : 'Anterior',
      featured: isEn ? 'Featured' : 'Destacado',
      noDepartment: isEn ? 'No department' : 'Sin area',
      noBio: isEn ? 'No bio available.' : 'Sin bio disponible.',
      searchPlaceholder: isEn ? 'Name, role, department...' : 'Nombre, cargo, area...',
    },

    init() {
      this.loadFiltersFromUrl();
      this.loadDepartments();
      this.loadMembers();

      window.addEventListener('popstate', () => {
        this.loadFiltersFromUrl();
        this.loadMembers();
      });
    },

    loadFiltersFromUrl() {
      const params = new URLSearchParams(window.location.search);

      this.filters.search = params.get('search') || '';
      this.filters.department = params.get('department') || '';
      this.filters.page = parseInt(params.get('page') || '1', 10) || 1;
      this.filters.per_page = parseInt(params.get('per_page') || '12', 10) || 12;
    },

    updateUrl() {
      const params = new URLSearchParams();

      if (this.filters.search) params.set('search', this.filters.search);
      if (this.filters.department) params.set('department', this.filters.department);
      if (this.filters.page > 1) params.set('page', String(this.filters.page));
      if (this.filters.per_page !== 12) params.set('per_page', String(this.filters.per_page));

      const nextUrl = params.toString()
        ? `${window.location.pathname}?${params.toString()}`
        : window.location.pathname;

      window.history.pushState(null, '', nextUrl);
    },

    hasFilters() {
      return Boolean(this.filters.search || this.filters.department);
    },

    applyFilters() {
      this.filters.page = 1;
      this.updateUrl();
      this.loadMembers();
    },

    clearFilters() {
      this.filters.search = '';
      this.filters.department = '';
      this.filters.page = 1;
      this.updateUrl();
      this.loadMembers();
    },

    goToPage(page) {
      if (!this.pagination) return;
      if (page < 1 || page > this.pagination.last_page) return;

      this.filters.page = page;
      this.updateUrl();
      this.loadMembers();

      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    departmentLabel(department) {
      if (!department) return '';
      if (isEn) {
        return department.name_en || department.name_es || department.key;
      }
      return department.name_es || department.name_en || department.key;
    },

    fallbackAvatar(name) {
      const safeName = encodeURIComponent(name || (isEn ? 'Team Member' : 'Integrante'));
      return `https://ui-avatars.com/api/?name=${safeName}&background=D1A054&color=ffffff&size=600`;
    },

    sanitizePhone(phone) {
      return String(phone || '').replace(/[^0-9+]/g, '');
    },

    escapeHtml(value) {
      return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    },

    memberBioHtml(member) {
      const source = (member?.bio || '').toString().trim();
      if (typeof window.publicSanitizeRichHtml === 'function') {
        return window.publicSanitizeRichHtml(source, this.texts.noBio);
      }
      return this.escapeHtml(source || this.texts.noBio);
    },

    async loadDepartments() {
      try {
        const response = await fetch('/api/public/team-members/departments', {
          headers: { Accept: 'application/json' },
        });

        const payload = await response.json();
        this.departments = response.ok && payload?.success ? (payload.data || []) : [];
      } catch (_error) {
        this.departments = [];
      }
    },

    async loadMembers() {
      this.loading = true;

      try {
        const params = new URLSearchParams();
        if (this.filters.search) params.set('search', this.filters.search);
        if (this.filters.department) params.set('department', this.filters.department);
        params.set('page', String(this.filters.page || 1));
        params.set('per_page', String(this.filters.per_page || 12));

        const response = await fetch(`/api/public/team-members?${params.toString()}`, {
          headers: { Accept: 'application/json' },
        });

        const payload = await response.json();

        if (!response.ok || !payload?.success || !payload?.data) {
          this.members = [];
          this.pagination = {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
          };
          return;
        }

        const data = payload.data;
        this.members = data.data || [];
        this.pagination = {
          current_page: data.current_page || 1,
          last_page: data.last_page || 1,
          from: data.from || 0,
          to: data.to || 0,
          total: data.total || 0,
        };
      } catch (_error) {
        this.members = [];
        this.pagination = {
          current_page: 1,
          last_page: 1,
          from: 0,
          to: 0,
          total: 0,
        };
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
@endpush
