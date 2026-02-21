@extends('layouts.public')

@section('title', 'Detalle de propiedad')

@section('content')
  <div class="relative overflow-hidden pt-24">
    {{-- Background decor --}}
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-from, rgba(209,160,84,.35));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-primary-to, rgba(118,141,89,.35));"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.06)_1px,transparent_0)] [background-size:28px_28px]"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Breadcrumbs --}}
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition">Inicio</a>
        <span class="text-slate-400">/</span>
        <a href="{{ route('public.properties.index') }}" class="text-slate-600 hover:text-slate-900 transition">Propiedades</a>
        <span class="text-slate-400">/</span>
        <span id="breadcrumbTitle" class="text-slate-900 font-medium truncate">Detalle</span>
      </nav>

      {{-- Header --}}
      <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span id="badgeOperation" class="hidden inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
              <span class="inline-block size-1.5 rounded-full bg-white/90"></span>
              <span id="badgeOperationText">Disponible</span>
            </span>
            <span id="badgeType" class="hidden inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.85)); color: var(--fe-properties-type_badge_text, #1C1C1C);">
              <span id="badgeTypeText">Propiedad</span>
            </span>
          </div>

          <h1 id="propertyTitle" class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
            <span class="inline-block align-middle">Cargando…</span>
          </h1>

          <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
            <div class="inline-flex items-center gap-2 text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span id="propertyLocation" class="truncate">—</span>
            </div>

            <div class="hidden sm:block text-slate-300">•</div>

            <div class="inline-flex items-center gap-2 text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span id="propertyUpdated" class="truncate">Actualizado: —</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button id="btnShare" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white/90 backdrop-blur hover:bg-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8a3 3 0 10-6 0v5H7l5 5 5-5h-2V8z" />
            </svg>
            Compartir
          </button>
          <a href="{{ route('public.properties.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver
          </a>
        </div>
      </div>

      {{-- Main layout --}}
      <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 pb-16">
        {{-- Content --}}
        <div class="lg:col-span-8 space-y-6">
          {{-- Gallery --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="relative">
              <div class="swiper property-gallery" aria-label="Galería de imágenes">
                <div class="swiper-wrapper" id="galleryWrapper">
                  {{-- Skeleton slide --}}
                  <div class="swiper-slide">
                    <div class="w-full" style="aspect-ratio: 16 / 10;">
                      <div class="h-full w-full skeleton"></div>
                    </div>
                  </div>
                </div>
                <div class="swiper-pagination !bottom-4"></div>
                <div class="swiper-button-prev !left-4 lg:!left-6"></div>
                <div class="swiper-button-next !right-4 lg:!right-6"></div>
              </div>

              <div class="absolute top-4 left-4 z-10 flex items-center gap-2">
                <span id="galleryCount" class="hidden rounded-full px-3 py-1 text-xs font-semibold text-white/95 backdrop-blur" style="background: rgba(15,23,42,.55);">0 fotos</span>
              </div>
            </div>

            {{-- Thumbs --}}
            <div class="border-t border-slate-100">
              <div class="swiper property-thumbs px-4 py-4">
                <div class="swiper-wrapper" id="thumbsWrapper">
                  {{-- Skeleton thumbs --}}
                  @for ($i = 0; $i < 6; $i++)
                    <div class="swiper-slide" style="width: 96px;">
                      <div class="rounded-2xl overflow-hidden border border-slate-100 bg-slate-50" style="aspect-ratio: 4 / 3;">
                        <div class="h-full w-full skeleton"></div>
                      </div>
                    </div>
                  @endfor
                </div>
              </div>
            </div>
          </section>

          {{-- Highlights --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
            <h2 class="text-lg font-bold text-slate-900">Características principales</h2>

            <div id="highlightsGrid" class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3">
              {{-- Skeleton cards --}}
              @for ($i = 0; $i < 6; $i++)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <div class="h-4 w-24 skeleton rounded"></div>
                  <div class="mt-2 h-6 w-16 skeleton rounded"></div>
                </div>
              @endfor
            </div>
          </section>

          {{-- Description + Meta --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
            <div class="flex items-center justify-between gap-3">
              <h2 class="text-lg font-bold text-slate-900">Descripción</h2>
              <span id="propertyIdChip" class="hidden text-xs font-semibold text-slate-600 rounded-full px-3 py-1 bg-slate-100">#—</span>
            </div>

            <div id="description" class="mt-4 text-slate-700 leading-relaxed whitespace-pre-line">
              <div class="space-y-3">
                <div class="h-4 w-11/12 skeleton rounded"></div>
                <div class="h-4 w-10/12 skeleton rounded"></div>
                <div class="h-4 w-9/12 skeleton rounded"></div>
              </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
              <h3 class="text-sm font-semibold text-slate-900">Features</h3>
              <div id="featuresWrap" class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">Cargando…</span>
              </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
              <h3 class="text-sm font-semibold text-slate-900">Tags</h3>
              <div id="tagsWrap" class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">Cargando…</span>
              </div>
            </div>
          </section>

          {{-- Location / Map --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Ubicación</h2>
                <p id="addressLine" class="mt-2 text-slate-600">—</p>
              </div>
              <a id="mapsLink" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ver mapa
              </a>
            </div>

            <div class="mt-5 rounded-2xl overflow-hidden border border-slate-100 bg-slate-50" style="aspect-ratio: 16 / 7;">
              <iframe
                id="mapFrame"
                title="Mapa"
                class="w-full h-full"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="about:blank"></iframe>
            </div>
          </section>
        </div>

        {{-- Aside --}}
        <aside class="lg:col-span-4">
          <div class="lg:sticky lg:top-28 space-y-6">
            {{-- Price / Actions --}}
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-600">Precio</p>
                  <p id="priceMain" class="mt-2 text-3xl font-extrabold text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">—</p>
                  <p id="priceHint" class="mt-1 text-xs text-slate-500">* Puede variar según operación</p>
                </div>

                <button id="btnFavorite" type="button" class="inline-flex items-center justify-center rounded-2xl w-12 h-12 border border-slate-200 bg-white hover:bg-slate-50 transition" aria-label="Favorito">
                  <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>

              <div id="operationsList" class="mt-5 space-y-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <div class="h-4 w-28 skeleton rounded"></div>
                  <div class="mt-2 h-6 w-40 skeleton rounded"></div>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-1 gap-3">
                <a id="btnWhatsApp" href="#" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.01]" style="background: linear-gradient(to right, #22c55e, #16a34a);">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                  </svg>
                  WhatsApp
                </a>
                <a id="btnCall" href="tel:+525512345678" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition">
                  <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  Llamar
                </a>
              </div>
            </section>

            {{-- Agency / Agent --}}
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6">
              <h3 class="text-sm font-semibold text-slate-900">Asesor / Agencia</h3>

              <div class="mt-4 flex items-center gap-4">
                <div class="size-14 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 grid place-items-center" id="agentAvatar">
                  <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p id="agentName" class="font-bold text-slate-900 truncate">San Miguel Properties</p>
                  <p id="agencyName" class="text-sm text-slate-600 truncate">—</p>
                </div>
              </div>

              {{-- MLS Agents (when property comes from MLS relationships) --}}
              <div id="mlsAgentsWrap" class="hidden mt-5 space-y-3"></div>

              <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600">Nota</p>
                <p class="mt-1 text-sm text-slate-700">Agenda una visita y recibe información completa (disponibilidad, gastos y documentos).</p>
              </div>
            </section>

            {{-- Error box --}}
            <section id="errorBox" class="hidden rounded-3xl border border-rose-200 bg-rose-50 p-6">
              <h3 class="text-sm font-semibold text-rose-900">No se pudo cargar la propiedad</h3>
              <p id="errorText" class="mt-2 text-sm text-rose-800">—</p>
              <div class="mt-4 flex flex-wrap gap-2">
                <button id="btnRetry" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                  Reintentar
                </button>
                <a href="{{ route('public.properties.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-rose-200 bg-white hover:bg-rose-50 transition">
                  Volver al listado
                </a>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // ======================================================
    // PUBLIC PROPERTY DETAIL (responsive, Swiper gallery)
    // Route provides: $propertyId
    // ======================================================
    window.__PROPERTY_ID__ = @json($propertyId ?? null);

    function safeText(v, fallback = '—') {
      const s = (v ?? '').toString().trim();
      return s ? s : fallback;
    }

    function formatIsoToEs(iso) {
      if (!iso) return '—';
      try {
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return String(iso);
        return d.toLocaleString('es-CO', { year: 'numeric', month: 'short', day: '2-digit' });
      } catch (_e) {
        return String(iso);
      }
    }

    function escapeHtml(s) {
      return String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function resolveMediaUrl(asset) {
      if (!asset) return null;
      if (typeof asset === 'string') return asset;
      // Priorizar serving_url (URL local si fue descargada), luego url, luego fallbacks
      const url = asset.serving_url || asset.url || asset.public_url || asset.path || null;
      if (url) return url;
      // Fallback a source_url del pivot si existe
      if (asset.pivot && asset.pivot.source_url) return asset.pivot.source_url;
      return asset.source_url || null;
    }

    function buildImageList(property) {
      const imgs = [];
      const coverUrl = resolveMediaUrl(property.cover_media_asset);
      if (coverUrl) imgs.push({ url: coverUrl, alt: property.title || 'Propiedad' });

      const gallery = Array.isArray(property.media_assets) ? property.media_assets : [];
      gallery.forEach((m) => {
        const url = resolveMediaUrl(m);
        if (!url) return;
        if (imgs.some(i => i.url === url)) return;
        imgs.push({ url, alt: property.title || 'Propiedad' });
      });

      if (!imgs.length) {
        imgs.push({
          url: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
          alt: 'Propiedad'
        });
      }

      return imgs;
    }

    function buildMapsUrl({ lat, lng, q }) {
      if (lat && lng) return `https://www.google.com/maps?q=${encodeURIComponent(lat + ',' + lng)}`;
      return `https://www.google.com/maps?q=${encodeURIComponent(q || '')}`;
    }

    function buildOsmEmbed({ lat, lng, q }) {
      // OSM embed works best with coords; fallback to blank if none.
      if (!(lat && lng)) return 'about:blank';
      // Convertir a número si vienen como string
      const latNum = parseFloat(lat);
      const lngNum = parseFloat(lng);
      if (Number.isNaN(latNum) || Number.isNaN(lngNum)) return 'about:blank';
      // A small bbox around the point
      const d = 0.005;
      const left = (lngNum - d).toFixed(6);
      const right = (lngNum + d).toFixed(6);
      const top = (latNum + d).toFixed(6);
      const bottom = (latNum - d).toFixed(6);
      return `https://www.openstreetmap.org/export/embed.html?bbox=${left}%2C${bottom}%2C${right}%2C${top}&layer=mapnik&marker=${encodeURIComponent(latNum + ',' + lngNum)}`;
    }

    function operationLabel(op) {
      const t = (op?.operation_type || '').toString().toLowerCase();
      if (t === 'sale' || t === 'venta') return 'En venta';
      if (t === 'rent' || t === 'rental' || t === 'arriendo' || t === 'renta') return 'En renta';
      return safeText(op?.operation_type, 'Disponible');
    }

    function operationBadgeColor(op) {
      const t = (op?.operation_type || '').toString().toLowerCase();
      if (t === 'sale' || t === 'venta') return 'linear-gradient(to right, #768D59, #768D59)';
      if (t === 'rent' || t === 'rental' || t === 'arriendo' || t === 'renta') return 'linear-gradient(to right, #D1A054, #D1A054)';
      return 'linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59))';
    }

    function getPrimaryPrice(property) {
      const ops = Array.isArray(property.operations) ? property.operations : [];
      const first = ops[0] || null;
      if (!first) return 'Consultar precio';
      return first.formatted_amount || first.amount || 'Consultar precio';
    }

    function setError(message) {
      document.getElementById('errorBox').classList.remove('hidden');
      document.getElementById('errorText').textContent = safeText(message, 'Error inesperado');
    }

    let gallerySwiper = null;
    let thumbsSwiper = null;

    function initSwipers() {
      // Destroy if already exists (retries)
      if (gallerySwiper) {
        try { gallerySwiper.destroy(true, true); } catch (_e) {}
        gallerySwiper = null;
      }
      if (thumbsSwiper) {
        try { thumbsSwiper.destroy(true, true); } catch (_e) {}
        thumbsSwiper = null;
      }

      thumbsSwiper = new Swiper('.property-thumbs', {
        slidesPerView: 'auto',
        spaceBetween: 10,
        freeMode: true,
        watchSlidesProgress: true,
      });

      gallerySwiper = new Swiper('.property-gallery', {
        loop: true,
        speed: 650,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        thumbs: { swiper: thumbsSwiper },
      });
    }

    function renderProperty(property) {
      document.getElementById('errorBox').classList.add('hidden');

      const title = safeText(property.title, 'Propiedad');
      document.title = `${title} | San Miguel Properties`;
      document.getElementById('propertyTitle').textContent = title;
      document.getElementById('breadcrumbTitle').textContent = title;
      document.getElementById('propertyIdChip').textContent = `#${property.id}`;
      document.getElementById('propertyIdChip').classList.remove('hidden');

      const city = property.location?.city;
      const cityArea = property.location?.city_area;
      const region = property.location?.region;
      const loc = [city, cityArea, region].filter(Boolean).join(', ');
      document.getElementById('propertyLocation').textContent = safeText(loc, 'Ubicación disponible');
      document.getElementById('propertyUpdated').textContent = `Actualizado: ${formatIsoToEs(property.updated_at || property.easybroker_updated_at)}`;

      // Type badge
      if (property.property_type_name) {
        document.getElementById('badgeTypeText').textContent = property.property_type_name;
        document.getElementById('badgeType').classList.remove('hidden');
      }

      // Operation badge
      const ops = Array.isArray(property.operations) ? property.operations : [];
      if (ops.length) {
        document.getElementById('badgeOperationText').textContent = operationLabel(ops[0]);
        document.getElementById('badgeOperation').style.background = operationBadgeColor(ops[0]);
        document.getElementById('badgeOperation').classList.remove('hidden');
      }

      // Price
      const price = getPrimaryPrice(property);
      document.getElementById('priceMain').textContent = String(price);

      // Operations list
      const operationsList = document.getElementById('operationsList');
      if (!ops.length) {
        operationsList.innerHTML = `
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold text-slate-600">Operación</p>
            <p class="mt-1 text-base font-semibold text-slate-900">Consultar disponibilidad</p>
          </div>
        `;
      } else {
        operationsList.innerHTML = ops.map((op) => {
          const label = operationLabel(op);
          const amount = op.formatted_amount || op.amount || 'Consultar';
          const unit = op.unit ? ` / ${escapeHtml(op.unit)}` : '';
          const bg = operationBadgeColor(op);
          return `
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <div class="flex items-center justify-between gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white" style="background:${bg}">${escapeHtml(label)}</span>
                <span class="text-xs font-semibold text-slate-500">${escapeHtml(op.currency?.code || op.currency_code || '')}</span>
              </div>
              <p class="mt-2 text-xl font-extrabold text-slate-900">${escapeHtml(String(amount))}${unit}</p>
            </div>
          `;
        }).join('');
      }

      // Highlights
      const highlights = [
        { label: 'Recámaras', value: property.bedrooms },
        { label: 'Baños', value: property.bathrooms },
        { label: 'Parqueaderos', value: property.parking_spaces },
        { label: 'Construcción', value: property.construction_size ? `${property.construction_size} m²` : null },
        { label: 'Lote', value: property.lot_size ? `${property.lot_size} m²` : null },
        { label: 'Pisos', value: property.floors ?? property.floor },
        { label: 'Edad', value: property.age },
      ].filter(x => x.value !== null && x.value !== undefined && String(x.value).trim() !== '');

      const highlightsGrid = document.getElementById('highlightsGrid');
      if (!highlights.length) {
        highlightsGrid.innerHTML = `
          <div class="col-span-2 sm:col-span-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-slate-700">
            No hay información adicional registrada para esta propiedad.
          </div>
        `;
      } else {
        highlightsGrid.innerHTML = highlights.map(h => `
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold text-slate-600">${escapeHtml(h.label)}</p>
            <p class="mt-1 text-lg font-extrabold text-slate-900">${escapeHtml(String(h.value))}</p>
          </div>
        `).join('');
      }

      // Description
      document.getElementById('description').textContent = safeText(property.description, 'Sin descripción.');

      // Features / tags
      const featuresWrap = document.getElementById('featuresWrap');
      const features = Array.isArray(property.features) ? property.features : [];
      featuresWrap.innerHTML = features.length
        ? features.map(f => `
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9); color: var(--fe-properties-tag_inactive_text, #475569);">
              ${escapeHtml(f.name || f.slug || ('Feature #' + f.id))}
            </span>
          `).join('')
        : '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">Sin features</span>';

      const tagsWrap = document.getElementById('tagsWrap');
      const tags = Array.isArray(property.tags) ? property.tags : [];
      tagsWrap.innerHTML = tags.length
        ? tags.map(t => `
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9); color: var(--fe-properties-tag_inactive_text, #475569);">
              ${escapeHtml(t.name || t.slug || ('Tag #' + t.id))}
            </span>
          `).join('')
        : '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">Sin tags</span>';

      // Location + map
      const street = property.location?.street;
      const postal = property.location?.postal_code;
      const addr = [street, cityArea, city, region, postal].filter(Boolean).join(', ');
      document.getElementById('addressLine').textContent = safeText(addr, 'Ubicación general disponible.');

      const lat = property.location?.latitude;
      const lng = property.location?.longitude;
      const q = addr || loc || title;
      const mapsUrl = buildMapsUrl({ lat, lng, q });
      document.getElementById('mapsLink').href = mapsUrl;
      document.getElementById('mapFrame').src = buildOsmEmbed({ lat, lng, q });

      // Agent / agency
      const agent = property.agent_user;
      const agency = property.agency;
      if (agent?.name) document.getElementById('agentName').textContent = agent.name;
      if (agency?.name) document.getElementById('agencyName').textContent = agency.name;

      const profileUrl = resolveMediaUrl(agent?.profile_image);
      if (profileUrl) {
        document.getElementById('agentAvatar').innerHTML = `<img src="${escapeHtml(profileUrl)}" alt="${escapeHtml(agent?.name || 'Asesor')}" class="w-full h-full object-cover" />`;
      }

      // MLS Agents (list)
      const mlsWrap = document.getElementById('mlsAgentsWrap');
      const mlsAgents = Array.isArray(property.mls_agents) ? property.mls_agents : [];

      if (mlsWrap) {
        if (!mlsAgents.length) {
          mlsWrap.classList.add('hidden');
          mlsWrap.innerHTML = '';
        } else {
          // Prefer primary agents first
          const sorted = [...mlsAgents].sort((a, b) => {
            const ap = a?.pivot?.is_primary ? 1 : 0;
            const bp = b?.pivot?.is_primary ? 1 : 0;
            return bp - ap;
          });

          const rows = sorted.map((a) => {
            const name = safeText(a?.full_name || a?.name, 'Agente');
            const office = safeText(a?.office_name, '—');
            const photo = a?.photo || a?.photo_url || null;

            // Prefer mobile, fallback to phone
            const phone = (a?.mobile || a?.phone || '').toString().trim();
            const email = (a?.email || '').toString().trim();

            const isPrimary = !!a?.pivot?.is_primary;
            const badge = isPrimary
              ? `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">Principal</span>`
              : '';

            const avatarHtml = photo
              ? `<img src="${escapeHtml(photo)}" alt="${escapeHtml(name)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.style.display='none';" />`
              : `
                  <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                  </svg>
                `;

            const contactBits = [
              phone ? `<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900" href="tel:${escapeHtml(phone)}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        ${escapeHtml(phone)}
                      </a>` : '',
              email ? `<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900" href="mailto:${escapeHtml(email)}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        ${escapeHtml(email)}
                      </a>` : '',
            ].filter(Boolean).join('');

            return `
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-start gap-4">
                  <div class="size-12 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0">${avatarHtml}</div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                      <p class="font-bold text-slate-900 truncate">${escapeHtml(name)}</p>
                      ${badge}
                    </div>
                    <p class="mt-0.5 text-xs text-slate-600 truncate">${escapeHtml(office)}</p>
                    ${contactBits ? `<div class="mt-3 flex flex-col gap-2">${contactBits}</div>` : '<p class="mt-3 text-sm text-slate-600">Contacto no disponible</p>'}
                  </div>
                </div>
              </div>
            `;
          }).join('');

          mlsWrap.innerHTML = rows;
          mlsWrap.classList.remove('hidden');
        }
      }

      // WhatsApp link (placeholder phone)
      const waText = `Hola, me interesa la propiedad #${property.id}: ${title}. ¿Me puedes dar más información?`;
      document.getElementById('btnWhatsApp').href = `https://wa.me/525512345678?text=${encodeURIComponent(waText)}`;

      // Gallery
      const imgs = buildImageList(property);
      document.getElementById('galleryCount').textContent = `${imgs.length} ${imgs.length === 1 ? 'foto' : 'fotos'}`;
      document.getElementById('galleryCount').classList.remove('hidden');

      const galleryWrapper = document.getElementById('galleryWrapper');
      const thumbsWrapper = document.getElementById('thumbsWrapper');

      galleryWrapper.innerHTML = imgs.map((img) => `
        <div class="swiper-slide">
          <div class="w-full bg-slate-100" style="aspect-ratio: 16 / 10;">
            <img src="${escapeHtml(img.url)}" alt="${escapeHtml(img.alt)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';" />
          </div>
        </div>
      `).join('');

      thumbsWrapper.innerHTML = imgs.map((img) => `
        <div class="swiper-slide" style="width: 96px;">
          <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100" style="aspect-ratio: 4 / 3;">
            <img src="${escapeHtml(img.url)}" alt="${escapeHtml(img.alt)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';" />
          </div>
        </div>
      `).join('');

      initSwipers();
    }

    async function loadProperty() {
      const id = window.__PROPERTY_ID__;
      if (!id) {
        setError('No se recibió el ID de la propiedad.');
        return;
      }

      try {
        const res = await fetch(`/api/public/properties/${id}`, {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!res.ok || !data?.success) {
          setError(data?.message || `Error HTTP ${res.status}`);
          return;
        }

        renderProperty(data.data);
      } catch (e) {
        console.error(e);
        setError('Error de red al cargar la propiedad.');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('btnRetry')?.addEventListener('click', loadProperty);

      // Share
      document.getElementById('btnShare')?.addEventListener('click', async () => {
        try {
          const url = window.location.href;
          if (navigator.share) {
            await navigator.share({ title: document.title, url });
            return;
          }
          await navigator.clipboard.writeText(url);
          window.dispatchEvent(new CustomEvent('api:response', {
            detail: { success: true, message: 'Enlace copiado al portapapeles', code: 'COPIED' }
          }));
        } catch (_e) {
          // fallback silent
        }
      });

      // Favorite (client-only toggle)
      document.getElementById('btnFavorite')?.addEventListener('click', (e) => {
        e.currentTarget.classList.toggle('ring-4');
        e.currentTarget.classList.toggle('ring-emerald-200');
      });

      loadProperty();
    });
  </script>
@endpush

