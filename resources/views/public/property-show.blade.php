@extends('layouts.public')

@section('title', 'Detalle de Propiedad (Prueba) - San Miguel Properties')

@push('styles')
    <style>
        /* Ajustes finos para galería con thumbs */
        .property-gallery .swiper-slide {
            height: auto;
        }
        .property-gallery-thumbs .swiper-slide {
            opacity: .55;
            transition: opacity .2s ease;
        }
        .property-gallery-thumbs .swiper-slide-thumb-active {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    {{-- Spacer for fixed header --}}
    <div class="h-20"></div>

    <section class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm mb-6" style="color: var(--fe-properties-card_location, #64748b);">
                <a href="{{ url('/') }}" class="hover:underline">Inicio</a>
                <span class="opacity-60">/</span>
                <a href="{{ url('/#propiedades') }}" class="hover:underline">Propiedades</a>
                <span class="opacity-60">/</span>
                <span class="font-medium text-slate-900" id="propertyBreadcrumb">Detalle</span>
            </nav>

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-10">
                {{-- GALERÍA + INFO PRINCIPAL --}}
                <div class="lg:col-span-8">
                    {{-- Gallery Card --}}
                    <div class="rounded-3xl overflow-hidden border shadow-soft" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                        <div class="relative">
                            {{-- Badges --}}
                            <div class="absolute top-4 left-4 z-20 flex flex-wrap gap-2">
                                <span id="propertyTypeBadge" class="hidden px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm" style="background-color: var(--fe-properties-type_badge_bg, rgba(255,255,255,0.9)); color: var(--fe-properties-type_badge_text, #0f172a);"></span>
                                <span id="propertyOperationBadge" class="hidden px-3 py-1 rounded-full text-xs font-semibold text-white"></span>
                            </div>

                            {{-- Gallery Main --}}
                            <div class="swiper property-gallery w-full">
                                <div class="swiper-wrapper" id="propertyGalleryWrapper">
                                    {{-- Placeholder slides (se reemplazan por JS) --}}
                                    @for ($i = 0; $i < 3; $i++)
                                        <div class="swiper-slide">
                                            <div class="relative aspect-[16/10] sm:aspect-[16/9]">
                                                <div class="absolute inset-0 skeleton"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <div class="swiper-pagination !bottom-4"></div>
                                <div class="swiper-button-prev !left-4"></div>
                                <div class="swiper-button-next !right-4"></div>
                            </div>

                            {{-- Thumbs --}}
                            <div class="p-4 pt-3" style="background-color: var(--fe-properties-filter_bg, #ffffff);">
                                <div class="swiper property-gallery-thumbs">
                                    <div class="swiper-wrapper" id="propertyGalleryThumbsWrapper">
                                        @for ($i = 0; $i < 6; $i++)
                                            <div class="swiper-slide !w-24 sm:!w-28">
                                                <div class="relative aspect-[4/3] rounded-xl overflow-hidden">
                                                    <div class="absolute inset-0 skeleton"></div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Título + ubicación + precio --}}
                    <div class="mt-8">
                        <h1 id="propertyTitle" class="text-3xl sm:text-4xl font-bold tracking-tight mb-3" style="color: var(--fe-properties-title, #0f172a);">
                            Cargando propiedad…
                        </h1>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-2 text-sm" style="color: var(--fe-properties-card_location, #64748b);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span id="propertyLocation">Ubicación disponible</span>
                            </div>

                            <div class="text-3xl font-extrabold text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                                <span id="propertyPrice">Consultando…</span>
                            </div>
                        </div>
                    </div>

                    {{-- Características --}}
                    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $items = [
                                ['id' => 'bedrooms', 'label' => 'Recámaras', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                                ['id' => 'bathrooms', 'label' => 'Baños', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['id' => 'parking', 'label' => 'Estacionamientos', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 01-2 2M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
                                ['id' => 'construction_size', 'label' => 'Construcción', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                                ['id' => 'lot_size', 'label' => 'Terreno', 'icon' => 'M3 7h18M3 17h18M7 3v18M17 3v18'],
                                ['id' => 'floors', 'label' => 'Niveles', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
                            ];
                        @endphp
                        @foreach ($items as $it)
                            <div class="rounded-2xl border p-5" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl text-white grid place-items-center shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $it['icon'] }}" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" style="color: var(--fe-properties-card_meta, #475569);">{{ $it['label'] }}</div>
                                        <div class="text-xl font-bold" style="color: var(--fe-properties-title, #0f172a);">
                                            <span id="propertyMetric_{{ $it['id'] }}">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Descripción --}}
                    <div class="mt-10 rounded-3xl border p-7 shadow-sm" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h2 class="text-xl font-bold" style="color: var(--fe-properties-title, #0f172a);">Descripción</h2>
                            <span class="text-xs px-3 py-1 rounded-full" style="background-color: var(--fe-properties-badge_bg, #eef2ff); color: var(--fe-properties-badge_text, #4f46e5);">Vista de prueba</span>
                        </div>
                        <p id="propertyDescription" class="leading-relaxed" style="color: var(--fe-properties-subtitle, #475569);">
                            Cargando descripción…
                        </p>
                    </div>

                    {{-- Features + Tags --}}
                    <div class="mt-10 grid lg:grid-cols-2 gap-6">
                        <div class="rounded-3xl border p-7" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                            <h3 class="text-lg font-bold mb-4" style="color: var(--fe-properties-title, #0f172a);">Características</h3>
                            <div id="propertyFeatures" class="flex flex-wrap gap-2">
                                {{-- chips por JS --}}
                                <span class="px-3 py-1 rounded-full text-sm" style="background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9); color: var(--fe-properties-tag_inactive_text, #475569);">Cargando…</span>
                            </div>
                        </div>
                        <div class="rounded-3xl border p-7" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                            <h3 class="text-lg font-bold mb-4" style="color: var(--fe-properties-title, #0f172a);">Etiquetas</h3>
                            <div id="propertyTags" class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-sm" style="background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9); color: var(--fe-properties-tag_inactive_text, #475569);">Cargando…</span>
                            </div>
                        </div>
                    </div>

                    {{-- Propiedades similares (slider) --}}
                    <div class="mt-12">
                        <div class="flex items-end justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-2xl font-bold" style="color: var(--fe-properties-title, #0f172a);">Propiedades similares</h2>
                                <p class="text-sm" style="color: var(--fe-properties-subtitle, #475569);">Carrusel moderno (demo) alimentado desde la API pública.</p>
                            </div>
                        </div>

                        <div class="rounded-3xl border p-4 sm:p-6" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                            <div class="swiper related-properties-slider">
                                <div class="swiper-wrapper" id="relatedPropertiesWrapper">
                                    @for ($i = 0; $i < 6; $i++)
                                        <div class="swiper-slide !w-[280px] sm:!w-[320px]">
                                            <div class="rounded-2xl overflow-hidden border shadow-sm" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                                                <div class="skeleton h-44 w-full"></div>
                                                <div class="p-5 space-y-3">
                                                    <div class="skeleton h-4 w-3/4 rounded"></div>
                                                    <div class="skeleton h-6 w-full rounded"></div>
                                                    <div class="skeleton h-4 w-1/2 rounded"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <div class="swiper-pagination !static !mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR CTA / CONTACTO --}}
                <aside class="lg:col-span-4">
                    <div class="sticky top-28 space-y-6">
                        {{-- Resumen / Acciones --}}
                        <div class="rounded-3xl border p-7 shadow-soft" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                            <h2 class="text-lg font-bold mb-2" style="color: var(--fe-properties-title, #0f172a);">Agenda una visita</h2>
                            <p class="text-sm mb-6" style="color: var(--fe-properties-subtitle, #475569);">
                                Esta sección es de prueba. Aquí irá el formulario real de contacto / lead.
                            </p>

                            <div class="space-y-3">
                                <button class="w-full px-6 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                                    Solicitar información
                                </button>
                                <button class="w-full px-6 py-3 rounded-xl font-semibold border transition-colors hover:bg-slate-50" style="border-color: var(--fe-properties-filter_border, #e2e8f0); color: var(--fe-properties-title, #0f172a);">
                                    Agendar visita
                                </button>
                            </div>

                            <div class="mt-6 pt-6 border-t" style="border-color: var(--fe-properties-filter_divider, #f1f5f9);">
                                <div class="flex items-center justify-between text-sm" style="color: var(--fe-properties-card_meta, #475569);">
                                    <span>ID de prueba</span>
                                    <span class="font-semibold" id="propertyIdLabel">#{{ $propertyId }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Agente (si viene en la API) --}}
                        <div class="rounded-3xl border p-7" style="background-color: var(--fe-properties-filter_bg, #ffffff); border-color: var(--fe-properties-filter_border, #e2e8f0);">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 flex items-center justify-center" id="agentAvatar">
                                    <span class="text-slate-500 font-bold">A</span>
                                </div>
                                <div>
                                    <div class="text-sm" style="color: var(--fe-properties-card_meta, #475569);">Asesor</div>
                                    <div class="text-lg font-bold" style="color: var(--fe-properties-title, #0f172a);" id="agentName">Por asignar</div>
                                </div>
                            </div>
                            <div class="mt-5 space-y-2 text-sm" style="color: var(--fe-properties-subtitle, #475569);">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">Tel:</span>
                                    <span id="agentPhone">—</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">Email:</span>
                                    <span id="agentEmail">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ubicación (resumen) --}}
                        <div class="rounded-3xl border p-7" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                            <h3 class="text-lg font-bold mb-3" style="color: var(--fe-properties-title, #0f172a);">Ubicación</h3>
                            <div class="space-y-2 text-sm" style="color: var(--fe-properties-subtitle, #475569);">
                                <div><span class="font-medium">Región:</span> <span id="locRegion">—</span></div>
                                <div><span class="font-medium">Ciudad:</span> <span id="locCity">—</span></div>
                                <div><span class="font-medium">Zona:</span> <span id="locArea">—</span></div>
                                <div><span class="font-medium">Dirección:</span> <span id="locStreet">—</span></div>
                                <div><span class="font-medium">CP:</span> <span id="locPostal">—</span></div>
                            </div>

                            <div class="mt-5 rounded-2xl overflow-hidden border" style="border-color: var(--fe-properties-filter_border, #e2e8f0);">
                                <div class="relative h-44">
                                    <div class="absolute inset-0 bg-slate-100"></div>
                                    <div class="absolute inset-0 grid place-items-center text-slate-500 text-sm">
                                        Mapa (placeholder)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // =====================================================
        // DETALLE DE PROPIEDAD (VISTA DE PRUEBA)
        // =====================================================
        window.__propertyDetail = {
            propertyId: Number(@json($propertyId)),
            gallerySwiper: null,
            thumbsSwiper: null,
            relatedSwiper: null,
        };

        document.addEventListener('DOMContentLoaded', async function () {
            await loadPropertyDetail(window.__propertyDetail.propertyId);
            await loadRelatedProperties(window.__propertyDetail.propertyId);
        });

        function safeText(value, fallback = '—') {
            if (value === null || value === undefined) return fallback;
            const str = String(value).trim();
            return str.length ? str : fallback;
        }

        function setText(id, value, fallback = '—') {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = safeText(value, fallback);
        }

        function setHtml(id, html) {
            const el = document.getElementById(id);
            if (!el) return;
            el.innerHTML = html;
        }

        function buildChip(text) {
            const label = safeText(text, '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            if (!label) return '';
            return `<span class="px-3 py-1 rounded-full text-sm" style="background-color: var(--fe-properties-tag_inactive_bg, #f1f5f9); color: var(--fe-properties-tag_inactive_text, #475569);">${label}</span>`;
        }

        function pickMainOperation(operations = []) {
            if (!Array.isArray(operations) || operations.length === 0) return null;
            // Preferimos venta, luego renta, luego el primero.
            const sale = operations.find(o => o?.operation_type === 'sale');
            const rent = operations.find(o => o?.operation_type === 'rent');
            return sale || rent || operations[0];
        }

        function normalizeImages(property) {
            const fallback = [
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80',
                'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=2000&q=80',
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=2000&q=80',
            ];

            const cover = property?.cover_media_asset?.url ? [property.cover_media_asset.url] : [];
            const media = Array.isArray(property?.media_assets)
                ? property.media_assets
                    .map(m => m?.url)
                    .filter(Boolean)
                : [];

            const all = [...cover, ...media].filter(Boolean);
            const unique = Array.from(new Set(all));
            return unique.length ? unique : fallback;
        }

        function initGallery(images) {
            // Destroy previous
            if (window.__propertyDetail.gallerySwiper) {
                window.__propertyDetail.gallerySwiper.destroy(true, true);
                window.__propertyDetail.gallerySwiper = null;
            }
            if (window.__propertyDetail.thumbsSwiper) {
                window.__propertyDetail.thumbsSwiper.destroy(true, true);
                window.__propertyDetail.thumbsSwiper = null;
            }

            const mainWrapper = document.getElementById('propertyGalleryWrapper');
            const thumbsWrapper = document.getElementById('propertyGalleryThumbsWrapper');

            mainWrapper.innerHTML = images.map((url, idx) => {
                const alt = `Imagen ${idx + 1}`;
                return `
                    <div class="swiper-slide">
                        <div class="relative aspect-[16/10] sm:aspect-[16/9] overflow-hidden">
                            <img src="${url}" alt="${alt}" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
                            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,.45), transparent 55%);"></div>
                        </div>
                    </div>
                `;
            }).join('');

            thumbsWrapper.innerHTML = images.map((url, idx) => {
                const alt = `Thumb ${idx + 1}`;
                return `
                    <div class="swiper-slide !w-24 sm:!w-28">
                        <button type="button" class="block w-full">
                            <div class="relative aspect-[4/3] rounded-xl overflow-hidden border" style="border-color: var(--fe-properties-filter_border, #e2e8f0);">
                                <img src="${url}" alt="${alt}" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
                            </div>
                        </button>
                    </div>
                `;
            }).join('');

            window.__propertyDetail.thumbsSwiper = new Swiper('.property-gallery-thumbs', {
                spaceBetween: 10,
                slidesPerView: 'auto',
                freeMode: true,
                watchSlidesProgress: true,
            });

            window.__propertyDetail.gallerySwiper = new Swiper('.property-gallery', {
                loop: images.length > 1,
                speed: 700,
                grabCursor: true,
                pagination: {
                    el: '.property-gallery .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.property-gallery .swiper-button-next',
                    prevEl: '.property-gallery .swiper-button-prev',
                },
                thumbs: {
                    swiper: window.__propertyDetail.thumbsSwiper,
                },
                keyboard: { enabled: true },
            });
        }

        async function loadPropertyDetail(propertyId) {
            const url = `/api/public/properties/${propertyId}`;

            let property = null;

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json?.success && json?.data) {
                    property = json.data;
                }
            } catch (e) {
                // Silencioso: usamos fallback
            }

            // Fallback demo si no hay datos reales
            if (!property) {
                property = {
                    id: propertyId,
                    title: `Propiedad de prueba #${propertyId}`,
                    property_type_name: 'Departamento',
                    description: 'Esta es una vista de prueba para el detalle de la propiedad. Aquí se mostrarán todos los campos relevantes (precio, ubicación, operación, galerías, características, amenities, etc.).',
                    operations: [{ operation_type: 'sale', formatted_amount: '$ 1,250,000', currency_code: 'MXN' }],
                    location: { region: 'Región Demo', city: 'Ciudad Demo', city_area: 'Zona Demo', street: 'Calle Demo 123', postal_code: '00000' },
                    bedrooms: 3,
                    bathrooms: 2,
                    parking_spaces: 2,
                    construction_size: 120,
                    lot_size: 180,
                    floors: 2,
                    features: [{ name: 'Alberca' }, { name: 'Gimnasio' }, { name: 'Seguridad 24/7' }],
                    tags: [{ name: 'Vista panorámica' }, { name: 'Pet friendly' }],
                    cover_media_asset: { url: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80' },
                    media_assets: [
                        { url: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=2000&q=80' },
                        { url: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=2000&q=80' },
                    ],
                    agent_user: { name: 'Asesor Demo', agent_phone: '+52 55 0000 0000', agent_public_email: 'asesor@demo.com', profile_image: null },
                };
            }

            // Breadcrumb
            setText('propertyBreadcrumb', property?.title || `Propiedad #${propertyId}`, 'Detalle');

            // Título
            setText('propertyTitle', property?.title, `Propiedad #${propertyId}`);

            // Ubicación
            const loc = property?.location || {};
            const locLine = [loc.city, loc.city_area, loc.region].filter(Boolean).join(', ');
            setText('propertyLocation', locLine || 'Ubicación disponible');

            // Precio + badges
            const op = pickMainOperation(property?.operations);
            const price = op?.formatted_amount || (typeof op?.amount === 'number' ? formatCurrency(op.amount, op?.currency?.code || op?.currency_code || 'MXN') : null);
            setText('propertyPrice', price || 'Consultar precio');

            const typeBadge = document.getElementById('propertyTypeBadge');
            if (typeBadge && property?.property_type_name) {
                typeBadge.textContent = property.property_type_name;
                typeBadge.classList.remove('hidden');
            }

            const opBadge = document.getElementById('propertyOperationBadge');
            if (opBadge && op?.operation_type) {
                opBadge.classList.remove('hidden');
                const isSale = op.operation_type === 'sale';
                opBadge.textContent = isSale ? 'En Venta' : (op.operation_type === 'rent' ? 'En Renta' : op.operation_type);
                opBadge.style.backgroundColor = isSale
                    ? 'var(--fe-properties-sale_badge, #10b981)'
                    : 'var(--fe-properties-rent_badge, #f59e0b)';
            }

            // Métricas (alineadas a los campos reales del modelo)
            setText('propertyMetric_bedrooms', property?.bedrooms, '—');
            setText('propertyMetric_bathrooms', property?.bathrooms, '—');
            setText('propertyMetric_parking', property?.parking_spaces, '—');
            setText('propertyMetric_construction_size', property?.construction_size ? `${property.construction_size} m²` : null, '—');
            setText('propertyMetric_lot_size', property?.lot_size ? `${property.lot_size} m²` : null, '—');
            setText('propertyMetric_floors', property?.floors, '—');

            // Descripción
            setText('propertyDescription', property?.description, 'Sin descripción por ahora.');

            // Features + Tags
            const features = Array.isArray(property?.features) ? property.features : [];
            const tags = Array.isArray(property?.tags) ? property.tags : [];

            setHtml('propertyFeatures', features.length
                ? features.map(f => buildChip(f?.name)).join('')
                : buildChip('Sin características registradas'));

            setHtml('propertyTags', tags.length
                ? tags.map(t => buildChip(t?.name)).join('')
                : buildChip('Sin etiquetas'));

            // Ubicación (sidebar)
            setText('locRegion', loc?.region);
            setText('locCity', loc?.city);
            setText('locArea', loc?.city_area);
            setText('locStreet', loc?.street);
            setText('locPostal', loc?.postal_code);

            // Agente
            const agent = property?.agent_user || null;
            setText('agentName', agent?.name, 'Por asignar');
            setText('agentPhone', agent?.agent_phone, '—');
            setText('agentEmail', agent?.agent_public_email || agent?.email, '—');

            const avatar = document.getElementById('agentAvatar');
            const avatarUrl = agent?.profile_image?.url;
            if (avatar && avatarUrl) {
                avatar.innerHTML = `<img src="${avatarUrl}" alt="${safeText(agent?.name, 'Agente')}" class="w-full h-full object-cover" />`;
            }

            // Galería
            const images = normalizeImages(property);
            initGallery(images);
        }

        async function loadRelatedProperties(currentId) {
            let items = [];
            try {
                const res = await fetch('/api/public/properties?per_page=12&sort=desc&order=updated_at', { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json?.success && json?.data?.data) {
                    items = json.data.data;
                }
            } catch (e) {
                items = [];
            }

            // Filtrar el currentId si existe
            items = items.filter(p => Number(p?.id) !== Number(currentId)).slice(0, 10);

            const wrapper = document.getElementById('relatedPropertiesWrapper');
            if (!wrapper) return;

            if (!items.length) {
                wrapper.innerHTML = `
                    <div class="swiper-slide !w-full">
                        <div class="rounded-2xl p-10 text-center" style="background-color: var(--fe-properties-filter_bg, #ffffff);">
                            <div class="text-sm" style="color: var(--fe-properties-subtitle, #475569);">No hay propiedades para mostrar (demo).</div>
                        </div>
                    </div>
                `;
            } else {
                wrapper.innerHTML = items.map(p => {
                    const img = p?.cover_media_asset?.url || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=80';
                    const title = safeText(p?.title, 'Propiedad');
                    const loc = [p?.location?.city, p?.location?.city_area].filter(Boolean).join(', ') || 'Ubicación disponible';
                    const op = pickMainOperation(p?.operations);
                    const price = op?.formatted_amount || 'Consultar precio';
                    const href = `/propiedades/${p?.id}`;

                    return `
                        <div class="swiper-slide !w-[280px] sm:!w-[320px]">
                            <a href="${href}" class="block property-card rounded-2xl overflow-hidden border shadow-sm group" style="background-color: var(--fe-properties-card_bg, #ffffff); border-color: var(--fe-properties-card_border, #f1f5f9);">
                                <div class="relative h-44 overflow-hidden">
                                    <img src="${img}" alt="${title}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>
                                </div>
                                <div class="p-5">
                                    <div class="text-xs mb-2" style="color: var(--fe-properties-card_location, #64748b);">${loc}</div>
                                    <div class="font-bold line-clamp-2" style="color: var(--fe-properties-card_title, #0f172a);">${title}</div>
                                    <div class="mt-3 text-xl font-extrabold text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">${price}</div>
                                </div>
                            </a>
                        </div>
                    `;
                }).join('');
            }

            if (window.__propertyDetail.relatedSwiper) {
                window.__propertyDetail.relatedSwiper.destroy(true, true);
                window.__propertyDetail.relatedSwiper = null;
            }

            window.__propertyDetail.relatedSwiper = new Swiper('.related-properties-slider', {
                slidesPerView: 'auto',
                spaceBetween: 16,
                grabCursor: true,
                pagination: {
                    el: '.related-properties-slider .swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: { spaceBetween: 18 },
                    1024: { spaceBetween: 22 },
                }
            });
        }
    </script>
@endpush

