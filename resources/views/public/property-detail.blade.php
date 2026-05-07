@extends('layouts.public')

@php
  $isEn = ($locale ?? app()->getLocale()) === 'en';
  $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
  $pageTitle = $pageData?->entity?->title($locale ?? app()->getLocale()) ?? ($isEn ? 'Property Detail' : 'Detalle de propiedad');
@endphp

@section('title', $pageTitle)

@section('content')
  <div class="property-detail-page relative overflow-hidden pt-24">
    {{-- Background decor --}}
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-property_detail-decor_glow_from, var(--fe-primary-from, rgba(209,160,84,.35)));"></div>
      <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40" style="background-color: var(--fe-property_detail-decor_glow_to, var(--fe-primary-to, rgba(118,141,89,.35)));"></div>
      <div class="absolute inset-0 pd-decor-grid"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Breadcrumbs --}}
      <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition pd-breadcrumb-link">{{ $txt('i18n_breadcrumb_home', 'Inicio', 'Home') }}</a>
        <span class="text-slate-400 pd-breadcrumb-separator">/</span>
        <a href="{{ route('public.properties.index') }}" class="text-slate-600 hover:text-slate-900 transition pd-breadcrumb-link">{{ $txt('i18n_breadcrumb_properties', 'Propiedades', 'Properties') }}</a>
        <span class="text-slate-400 pd-breadcrumb-separator">/</span>
        <span id="breadcrumbTitle" class="text-slate-900 font-medium truncate pd-breadcrumb-current">{{ $txt('i18n_breadcrumb_detail', 'Detalle', 'Detail') }}</span>
      </nav>

      {{-- Header --}}
      <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span id="badgeOperation" class="hidden inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: linear-gradient(to right, var(--fe-property_detail-contact_button_from, var(--fe-primary-from, #D1A054)), var(--fe-property_detail-contact_button_to, var(--fe-primary-to, #768D59))); color: var(--fe-property_detail-contact_button_text, #ffffff);">
              <span class="inline-block size-1.5 rounded-full bg-white/90"></span>
              <span id="badgeOperationText">{{ $txt('i18n_label_available', 'Disponible', 'Available') }}</span>
            </span>
            <span id="badgeType" class="hidden inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background-color: var(--fe-property_detail-type_badge_bg, var(--fe-properties-type_badge_bg, rgba(255,255,255,0.85))); color: var(--fe-property_detail-type_badge_text, var(--fe-properties-type_badge_text, #1C1C1C));">
              <span id="badgeTypeText">{{ $txt('i18n_label_property', 'Propiedad', 'Property') }}</span>
            </span>
          </div>

          <h1 id="propertyTitle" class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 pd-title">
            <span class="inline-block align-middle">{{ $txt('i18n_label_loading', 'Cargando...','Loading...') }}</span>
          </h1>

          <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
            <div class="inline-flex items-center gap-2 text-slate-600 pd-meta-row">
              <img src="{{ asset('iconos-base/ubicacion.svg') }}" alt="" aria-hidden="true" class="w-5 h-5 object-contain opacity-75">
              <span id="propertyLocation" class="truncate">—</span>
            </div>

            <div class="hidden sm:block text-slate-300 pd-meta-divider">•</div>

            <div class="inline-flex items-center gap-2 text-slate-600 pd-meta-row">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span id="propertyUpdated" class="truncate">{{ $txt('i18n_label_updated', 'Actualizado', 'Updated') }}: —</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button id="btnShare" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 bg-white/90 backdrop-blur hover:bg-white transition pd-btn-share">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8a3 3 0 10-6 0v5H7l5 5 5-5h-2V8z" />
            </svg>
            {{ $txt('cta_share', 'Compartir', 'Share') }}
          </button>
          <a href="{{ route('public.properties.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02] pd-btn-gradient">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ $txt('cta_back', 'Volver', 'Back') }}
          </a>
        </div>
      </div>

      {{-- Main layout --}}
      <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 pb-16">
        {{-- Content --}}
        <div class="lg:col-span-8 space-y-6">
          {{-- Gallery --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft overflow-hidden pd-panel pd-gallery-panel">
            <div class="relative property-gallery-shell pd-gallery-shell">
              <div class="swiper property-gallery h-full" aria-label="{{ $txt('gallery_aria_label', 'Galeria de imagenes', 'Image gallery') }}">
                <div class="swiper-wrapper h-full" id="galleryWrapper">
                  {{-- Skeleton slide --}}
                  <div class="swiper-slide h-full">
                    <div class="h-full w-full skeleton"></div>
                  </div>
                </div>
                <div class="swiper-pagination !bottom-4"></div>
                <div class="swiper-button-prev !left-4 lg:!left-6"></div>
                <div class="swiper-button-next !right-4 lg:!right-6"></div>
              </div>

              <div class="absolute top-4 left-4 z-10 flex items-center gap-2">
                <span id="galleryCount" class="hidden rounded-full px-3 py-1 text-xs font-semibold text-white/95 backdrop-blur pd-gallery-count">0 {{ $txt('i18n_label_photos', 'fotos', 'photos') }}</span>
              </div>
            </div>

            {{-- Thumbs --}}
            <div class="border-t border-slate-100 pd-gallery-strip">
              <div class="swiper property-thumbs px-4 py-4">
                <div class="swiper-wrapper" id="thumbsWrapper">
                  {{-- Skeleton thumbs --}}
                  @for ($i = 0; $i < 6; $i++)
                    <div class="swiper-slide" style="width: 96px;">
                      <div class="rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 pd-gallery-thumb" style="aspect-ratio: 4 / 3;">
                        <div class="h-full w-full skeleton"></div>
                      </div>
                    </div>
                  @endfor
                </div>
              </div>
            </div>
          </section>

          {{-- Highlights --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel">
            <h2 class="text-lg font-bold text-slate-900 pd-section-title">{{ $txt('i18n_section_features', 'Caracteristicas principales', 'Key Features') }}</h2>

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
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel">
            <div class="flex items-center justify-between gap-3">
              <h2 class="text-lg font-bold text-slate-900 pd-section-title">{{ $txt('i18n_section_description', 'Descripcion', 'Description') }}</h2>
              <span id="propertyIdChip" class="hidden text-xs font-semibold text-slate-600 rounded-full px-3 py-1 bg-slate-100 pd-chip">#—</span>
            </div>

            <div id="description" class="mt-4 text-slate-700 leading-relaxed rich-content pd-body-text">
              <div class="space-y-3">
                <div class="h-4 w-11/12 skeleton rounded"></div>
                <div class="h-4 w-10/12 skeleton rounded"></div>
                <div class="h-4 w-9/12 skeleton rounded"></div>
              </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
              <h3 class="text-sm font-semibold text-slate-900 pd-subsection-title">{{ $txt('i18n_section_featureList', 'Caracteristicas', 'Features') }}</h3>
              <div id="featuresWrap" class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">{{ $txt('i18n_label_loading', 'Cargando...','Loading...') }}</span>
              </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
              <h3 class="text-sm font-semibold text-slate-900 pd-subsection-title">{{ $txt('i18n_section_tags', 'Etiquetas', 'Tags') }}</h3>
              <div id="tagsWrap" class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600">{{ $txt('i18n_label_loading', 'Cargando...','Loading...') }}</span>
              </div>
            </div>
          </section>

          {{-- Location / Map --}}
          <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-slate-900 pd-section-title">{{ $txt('i18n_section_location', 'Ubicacion', 'Location') }}</h2>
                <p id="addressLine" class="mt-2 text-slate-600 pd-meta-text">—</p>
              </div>
              <a id="mapsLink" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02] pd-btn-gradient">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $txt('i18n_cta_viewMap', 'Ver mapa', 'View Map') }}
              </a>
            </div>

            <div class="mt-5 rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 pd-map-frame-wrap" style="aspect-ratio: 16 / 7;">
              <iframe
                id="mapFrame"
                title="{{ $txt('i18n_label_map', 'Mapa', 'Map') }}"
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
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-600 pd-meta-text">{{ $txt('i18n_label_price', 'Precio', 'Price') }}</p>
                  <p id="priceMain" class="mt-2 text-3xl font-extrabold text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-property_detail-price_from, var(--fe-primary-from, #D1A054)), var(--fe-property_detail-price_to, var(--fe-primary-to, #768D59)));">—</p>
                  <p id="priceHint" class="mt-1 text-xs text-slate-500 pd-subtle-text">{{ $txt('price_hint', '* Puede variar segun operacion', '* May vary by operation type') }}</p>
                </div>

                <button id="btnFavorite" type="button" data-favorite-btn data-property-id="{{ (int) ($propertyId ?? 0) }}" class="inline-flex items-center justify-center rounded-2xl w-12 h-12 border border-slate-200 bg-white hover:bg-slate-50 transition text-slate-600 pd-favorite-btn" aria-label="{{ $txt('i18n_label_favorite', 'Favorito', 'Favorite') }}">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <a id="btnWhatsApp" href="#" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.01] pd-btn-whatsapp">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                  </svg>
                  {{ $txt('i18n_label_whatsapp', 'WhatsApp', 'WhatsApp') }}
                </a>
                <a id="btnCall" href="tel:+525512345678" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 transition pd-btn-call">
                  <svg class="w-5 h-5 text-slate-700 pd-call-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  {{ $txt('i18n_label_call', 'Llamar', 'Call') }}
                </a>
              </div>
            </section>

            {{-- Contact Interest Form --}}
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel pd-interest-form-panel">
              <div class="flex items-start gap-3">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl pd-interest-form-icon">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h3 class="text-base font-bold text-slate-900 pd-interest-form-title">{{ $txt('i18n_contact_form_title', 'Me interesa esta propiedad', 'I am interested in this property') }}</h3>
                  <p class="mt-1 text-sm text-slate-600 pd-interest-form-subtitle">{{ $txt('i18n_contact_form_subtitle', 'Dejanos tus datos y un asesor podra contactarte.', 'Leave your details and an advisor can contact you.') }}</p>
                </div>
              </div>

              <form id="propertyInterestForm" class="mt-5 space-y-4" action="#" method="post" novalidate>
                <input id="interestPropertyId" type="hidden" name="property_id" value="{{ (int) ($propertyId ?? 0) }}">
                <input id="interestPropertyName" type="hidden" name="property_name" value="">

                <div>
                  <label for="interestFullName" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 pd-interest-label">{{ $txt('i18n_contact_full_name', 'Nombre completo', 'Full name') }}</label>
                  <div class="mt-2 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pd-interest-input-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8.966 8.966 0 0112 15c2.21 0 4.232.8 5.793 2.129M15 9a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </span>
                    <input id="interestFullName" name="full_name" type="text" autocomplete="name" required class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-transparent focus:ring-2 pd-interest-input" placeholder="{{ $txt('i18n_contact_full_name_placeholder', 'Tu nombre', 'Your name') }}">
                  </div>
                </div>

                <div>
                  <label for="interestEmail" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 pd-interest-label">{{ $txt('i18n_contact_email', 'Email', 'Email') }}</label>
                  <div class="mt-2 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pd-interest-input-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </span>
                    <input id="interestEmail" name="email" type="email" autocomplete="email" inputmode="email" required class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-transparent focus:ring-2 pd-interest-input" placeholder="{{ $txt('i18n_contact_email_placeholder', 'correo@ejemplo.com', 'email@example.com') }}">
                  </div>
                </div>

                <div>
                  <label for="interestPhone" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 pd-interest-label">{{ $txt('i18n_contact_phone', 'Telefono', 'Phone') }}</label>
                  <div class="mt-2 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pd-interest-input-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </span>
                    <input id="interestPhone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-transparent focus:ring-2 pd-interest-input" placeholder="{{ $txt('i18n_contact_phone_placeholder', 'Tu telefono', 'Your phone') }}">
                  </div>
                </div>

                <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-600 pd-interest-privacy">
                  <input id="interestPrivacy" name="privacy" type="checkbox" required class="mt-1 h-4 w-4 rounded border-slate-300" style="accent-color: var(--fe-primary-to, #768D59);">
                  <span>{{ $txt('i18n_contact_privacy_short', 'Acepto que San Miguel Properties me contacte sobre esta solicitud.', 'I agree that San Miguel Properties may contact me about this request.') }}</span>
                </label>

                <p id="propertyInterestFeedback" class="hidden rounded-2xl border px-4 py-3 text-sm pd-interest-feedback"></p>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-70 pd-interest-submit">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" />
                  </svg>
                  {{ $txt('i18n_contact_send', 'Enviar solicitud', 'Send request') }}
                </button>
              </form>
            </section>

            {{-- Agency / Agent --}}
            <section class="rounded-3xl border border-slate-200 bg-white shadow-soft p-6 pd-panel pd-agent-panel">
              <h3 class="text-sm font-semibold text-slate-900 pd-subsection-title">{{ $txt('i18n_label_advisorAgency', 'Agencia de contacto', 'Contact Agency') }}</h3>

              <div class="mt-4 flex items-center gap-4">
                <div class="size-14 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 grid place-items-center pd-agent-avatar" id="agentAvatar">
                  <svg class="w-7 h-7 text-slate-400 pd-icon-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p id="agentName" class="font-bold text-slate-900 truncate pd-agent-name">{{ $txt('i18n_label_companyName', 'San Miguel Properties', 'San Miguel Properties') }}</p>
                  <p id="agencyName" class="text-sm text-slate-600 truncate pd-agent-subtitle">—</p>
                </div>
              </div>

              {{-- MLS Agents (when property comes from MLS relationships) --}}
              <div id="mlsAgentsWrap" class="hidden mt-5 space-y-3"></div>

              <div id="sourceAgencyNotice" class="hidden mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 pd-source-notice">
                <p class="text-xs font-semibold text-amber-800 pd-source-label">{{ $txt('property_source_agency_reference_label', 'Referencia', 'Reference') }}</p>
                <p id="sourceAgencyNoticeText" class="mt-1 text-sm text-amber-900 pd-source-text">—</p>
              </div>

              <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4 pd-note">
                <p class="text-xs font-semibold text-slate-600 pd-note-label">{{ $txt('i18n_label_note', 'Nota', 'Note') }}</p>
                <p class="mt-1 text-sm text-slate-700 pd-note-text">{{ $txt('advisor_note', 'Agenda una visita y recibe informacion completa (disponibilidad, gastos y documentos).', 'Schedule a tour and receive complete information (availability, expenses and documentation).') }}</p>
              </div>
            </section>

            {{-- Error box --}}
            <section id="errorBox" class="hidden rounded-3xl border border-rose-200 bg-rose-50 p-6 pd-error-box">
              <h3 class="text-sm font-semibold text-rose-900 pd-error-title">{{ $txt('i18n_error_title', 'No se pudo cargar la propiedad', 'Could not load property') }}</h3>
              <p id="errorText" class="mt-2 text-sm text-rose-800 pd-error-text">—</p>
              <div class="mt-4 flex flex-wrap gap-2">
                <button id="btnRetry" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white pd-btn-gradient">
                  {{ $txt('i18n_cta_retry', 'Reintentar', 'Retry') }}
                </button>
                <a href="{{ route('public.properties.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold border border-rose-200 bg-white hover:bg-rose-50 transition pd-error-secondary-btn">
                  {{ $txt('i18n_cta_backToList', 'Volver al listado', 'Back to listing') }}
                </a>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .property-detail-page .pd-decor-grid {
      background-image: radial-gradient(
        circle at 1px 1px,
        var(--fe-property_detail-decor_pattern_dot, rgba(15,23,42,0.06)) 1px,
        transparent 0
      );
      background-size: 28px 28px;
    }

    .property-detail-page .pd-breadcrumb-link {
      color: var(--fe-property_detail-breadcrumb_link, #475569);
    }

    .property-detail-page .pd-breadcrumb-link:hover {
      color: var(--fe-property_detail-breadcrumb_link_hover, #0f172a);
    }

    .property-detail-page .pd-breadcrumb-separator {
      color: var(--fe-property_detail-breadcrumb_separator, #94a3b8);
    }

    .property-detail-page .pd-breadcrumb-current {
      color: var(--fe-property_detail-breadcrumb_current, #0f172a);
    }

    .property-detail-page .pd-title {
      color: var(--fe-property_detail-title, #0f172a);
    }

    .property-detail-page .pd-meta-row {
      color: var(--fe-property_detail-meta_text, #475569);
    }

    .property-detail-page .pd-meta-row svg {
      color: var(--fe-property_detail-meta_icon, var(--fe-property_detail-meta_text, #475569));
    }

    .property-detail-page .pd-meta-divider {
      color: var(--fe-property_detail-meta_divider, #cbd5e1);
    }

    .property-detail-page .pd-panel {
      background-color: var(--fe-property_detail-panel_bg, #ffffff);
      border-color: var(--fe-property_detail-panel_border, #e2e8f0);
      box-shadow: var(--fe-property_detail-panel_shadow, 0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10));
    }

    .property-detail-page .pd-gallery-panel {
      background-color: var(--fe-gallery-panel_bg, var(--fe-property_detail-panel_bg, #ffffff));
      border-color: var(--fe-gallery-panel_border, var(--fe-property_detail-panel_border, #e2e8f0));
    }

    .property-detail-page .pd-agent-panel {
      background-color: var(--fe-agent_card-panel_bg, var(--fe-property_detail-panel_bg, #ffffff));
      border-color: var(--fe-agent_card-panel_border, var(--fe-property_detail-panel_border, #e2e8f0));
    }

    .property-detail-page .pd-section-title {
      color: var(--fe-property_detail-section_title, #0f172a);
    }

    .property-detail-page .pd-subsection-title {
      color: var(--fe-property_detail-subsection_title, #0f172a);
    }

    .property-detail-page .pd-body-text {
      color: var(--fe-property_detail-body_text, #334155);
    }

    .property-detail-page .pd-meta-text {
      color: var(--fe-property_detail-muted_text, #475569);
    }

    .property-detail-page .pd-subtle-text {
      color: var(--fe-property_detail-subtle_text, #64748b);
    }

    .property-detail-page .pd-chip {
      background-color: var(--fe-property_detail-chip_bg, #f1f5f9);
      color: var(--fe-property_detail-chip_text, #475569);
    }

    .property-detail-page .pd-map-frame-wrap {
      background-color: var(--fe-property_detail-map_bg, #f8fafc);
      border-color: var(--fe-property_detail-map_border, #f1f5f9);
    }

    .property-detail-page .pd-btn-gradient {
      background-color: var(--fe-buttons-primary_bg, #D1A054);
      color: var(--fe-buttons-primary_text, #ffffff);
      border-color: var(--fe-buttons-primary_border, var(--fe-buttons-primary_bg, #D1A054));
    }

    .property-detail-page .pd-btn-gradient:hover {
      background-color: var(--fe-buttons-primary_hover_bg, var(--fe-buttons-primary_bg, #D1A054));
    }

    .property-detail-page .pd-btn-share {
      background-color: var(--fe-property_detail-share_button_bg, rgba(255,255,255,0.9));
      border-color: var(--fe-property_detail-share_button_border, #e2e8f0);
      color: var(--fe-property_detail-share_button_text, #0f172a);
    }

    .property-detail-page .pd-btn-share:hover {
      background-color: var(--fe-property_detail-share_button_hover_bg, #ffffff);
    }

    .property-detail-page .pd-btn-whatsapp {
      background-color: var(--fe-buttons-success_bg, #22c55e);
      color: var(--fe-buttons-success_text, #ffffff);
    }

    .property-detail-page .pd-btn-whatsapp:hover {
      background-color: var(--fe-buttons-success_hover_bg, var(--fe-buttons-success_bg, #22c55e));
    }

    .property-detail-page .pd-btn-call {
      background-color: var(--fe-property_detail-call_button_bg, #ffffff);
      border-color: var(--fe-property_detail-call_button_border, #e2e8f0);
      color: var(--fe-property_detail-call_button_text, #334155);
    }

    .property-detail-page .pd-btn-call:hover {
      background-color: var(--fe-property_detail-call_button_hover_bg, #f8fafc);
    }

    .property-detail-page .pd-call-icon {
      color: var(--fe-property_detail-call_button_icon, #334155);
    }

    .property-detail-page .pd-favorite-btn {
      background-color: var(--fe-property_detail-favorite_button_bg, #ffffff);
      border-color: var(--fe-property_detail-favorite_button_border, #e2e8f0);
      color: var(--fe-property_detail-favorite_button_text, #475569);
    }

    .property-detail-page .pd-favorite-btn:hover {
      background-color: var(--fe-property_detail-favorite_button_hover_bg, #f8fafc);
    }

    .property-detail-page .pd-interest-form-panel {
      background-color: var(--fe-property_detail-interest_form_bg, var(--fe-property_detail-panel_bg, #ffffff));
      border-color: var(--fe-property_detail-interest_form_border, var(--fe-property_detail-panel_border, #e2e8f0));
    }

    .property-detail-page .pd-interest-form-icon {
      background: none;
      background-color: var(--fe-buttons-primary_bg, #D1A054);
      border: 1px solid var(--fe-buttons-primary_border, var(--fe-buttons-primary_bg, #D1A054));
      color: var(--fe-buttons-primary_text, #ffffff) !important;
    }

    .property-detail-page .pd-interest-form-title {
      color: var(--fe-property_detail-interest_form_title, var(--fe-property_detail-subsection_title, #0f172a));
    }

    .property-detail-page .pd-interest-form-subtitle {
      color: var(--fe-property_detail-interest_form_subtitle, var(--fe-property_detail-muted_text, #475569));
    }

    .property-detail-page .pd-interest-label {
      color: var(--fe-property_detail-interest_form_label, #475569);
    }

    .property-detail-page .pd-interest-input {
      background-color: var(--fe-property_detail-interest_form_input_bg, #ffffff);
      border-color: var(--fe-property_detail-interest_form_input_border, #e2e8f0);
      color: var(--fe-property_detail-interest_form_input_text, #0f172a);
    }

    .property-detail-page .pd-interest-input:focus {
      --tw-ring-color: var(--fe-property_detail-interest_form_focus_ring, var(--fe-buttons-primary_bg, #D1A054));
    }

    .property-detail-page .pd-interest-input-icon {
      color: var(--fe-property_detail-interest_form_input_icon, var(--fe-buttons-primary_bg, #D1A054));
    }

    .property-detail-page .pd-interest-submit {
      background: none;
      background-color: var(--fe-buttons-primary_bg, #D1A054);
      border: 1px solid var(--fe-buttons-primary_border, var(--fe-buttons-primary_bg, #D1A054));
      color: var(--fe-buttons-primary_text, #ffffff) !important;
    }

    .property-detail-page .pd-interest-submit:hover {
      background-color: var(--fe-buttons-primary_hover_bg, var(--fe-buttons-primary_bg, #D1A054));
      filter: none;
    }

    .property-detail-page .pd-interest-feedback[data-state="success"] {
      background-color: var(--fe-property_detail-interest_form_success_bg, #ecfdf5);
      border-color: var(--fe-property_detail-interest_form_success_border, #bbf7d0);
      color: var(--fe-property_detail-interest_form_success_text, #166534);
    }

    .property-detail-page .pd-interest-feedback[data-state="error"] {
      background-color: var(--fe-property_detail-interest_form_error_bg, #fff1f2);
      border-color: var(--fe-property_detail-interest_form_error_border, #fecdd3);
      color: var(--fe-property_detail-interest_form_error_text, #9f1239);
    }

    .property-detail-page .pd-source-notice {
      background-color: var(--fe-property_detail-source_notice_bg, #fef3c7);
      border-color: var(--fe-property_detail-source_notice_border, #fcd34d);
    }

    .property-detail-page .pd-source-label {
      color: var(--fe-property_detail-source_notice_label, #92400e);
    }

    .property-detail-page .pd-source-text {
      color: var(--fe-property_detail-source_notice_text, #78350f);
    }

    .property-detail-page .pd-note {
      background-color: var(--fe-property_detail-note_bg, #f8fafc);
      border-color: var(--fe-property_detail-note_border, #f1f5f9);
    }

    .property-detail-page .pd-note-label {
      color: var(--fe-property_detail-note_label, #475569);
    }

    .property-detail-page .pd-note-text {
      color: var(--fe-property_detail-note_text, #334155);
    }

    .property-detail-page .pd-error-box {
      background-color: var(--fe-property_detail-error_bg, #fef2f2);
      border-color: var(--fe-property_detail-error_border, #fecaca);
    }

    .property-detail-page .pd-error-title {
      color: var(--fe-property_detail-error_title, #881337);
    }

    .property-detail-page .pd-error-text {
      color: var(--fe-property_detail-error_text, #9f1239);
    }

    .property-detail-page .pd-error-secondary-btn {
      background-color: var(--fe-property_detail-error_secondary_bg, #ffffff);
      border-color: var(--fe-property_detail-error_secondary_border, #fecaca);
      color: var(--fe-property_detail-error_secondary_text, #be123c);
    }

    .property-detail-page .pd-error-secondary-btn:hover {
      background-color: var(--fe-property_detail-error_secondary_hover_bg, #fff1f2);
    }

    .property-detail-page .pd-gallery-shell {
      aspect-ratio: 16 / 10;
      max-height: min(72vh, 760px);
      background: var(--fe-gallery-shell_bg, #f1f5f9);
      overflow: hidden;
    }

    .property-detail-page .pd-gallery-count {
      background: var(--fe-gallery-count_badge_bg, rgba(15,23,42,.55));
      color: var(--fe-gallery-count_badge_text, rgba(255,255,255,0.95));
    }

    .property-detail-page .pd-gallery-strip {
      border-color: var(--fe-gallery-thumbs_strip_border, #f1f5f9);
    }

    .property-detail-page .pd-gallery-thumb {
      border-color: var(--fe-gallery-thumbnail_border, #e2e8f0);
      background-color: var(--fe-gallery-thumbnail_bg, #f8fafc);
    }

    .property-detail-page .property-thumbs .swiper-slide-thumb-active .pd-gallery-thumb {
      border-color: var(--fe-gallery-thumbnail_border_active, #D1A054);
    }

    .property-detail-page .pd-agent-avatar {
      background-color: var(--fe-agent_card-avatar_bg, #f8fafc);
      border-color: var(--fe-agent_card-avatar_border, #e2e8f0);
    }

    .property-detail-page .pd-icon-muted {
      color: var(--fe-agent_card-avatar_icon, #94a3b8);
    }

    .property-detail-page .pd-agent-name {
      color: var(--fe-agent_card-name_color, #0f172a);
    }

    .property-detail-page .pd-agent-subtitle {
      color: var(--fe-agent_card-subtitle_color, #475569);
    }

    .property-detail-page .pd-mls-card {
      background-color: var(--fe-agent_card-mls_card_bg, #f8fafc);
      border-color: var(--fe-agent_card-mls_card_border, #f1f5f9);
    }

    .property-detail-page .pd-mls-name {
      color: var(--fe-agent_card-mls_name, #0f172a);
    }

    .property-detail-page .pd-mls-office {
      color: var(--fe-agent_card-mls_office, #475569);
    }

    .property-detail-page .pd-contact-link {
      color: var(--fe-agent_card-contact_link, #334155);
    }

    .property-detail-page .pd-contact-link:hover {
      color: var(--fe-agent_card-contact_link_hover, #0f172a);
    }

    .property-detail-page .pd-contact-unavailable {
      color: var(--fe-agent_card-contact_unavailable, #475569);
    }

    .property-detail-page .pd-feature-tag {
      background-color: var(--fe-property_detail-feature_tag_bg, var(--fe-properties-tag_inactive_bg, #f1f5f9));
      color: var(--fe-property_detail-feature_tag_text, var(--fe-properties-tag_inactive_text, #475569));
    }

    .property-detail-page .pd-empty-state {
      background-color: var(--fe-property_detail-empty_state_bg, #f8fafc);
      border-color: var(--fe-property_detail-empty_state_border, #f1f5f9);
      color: var(--fe-property_detail-empty_state_text, #334155);
    }

    .property-gallery,
    .property-gallery .swiper-wrapper,
    .property-gallery .swiper-slide {
      height: 100%;
    }

    .property-thumbs .swiper-wrapper {
      height: auto;
    }

    /* Utility fallback overrides scoped to property detail */
    .property-detail-page .text-slate-900 { color: var(--fe-property_detail-title, #0f172a); }
    .property-detail-page .text-slate-700 { color: var(--fe-property_detail-body_text, #334155); }
    .property-detail-page .text-slate-600 { color: var(--fe-property_detail-muted_text, #475569); }
    .property-detail-page .text-slate-500 { color: var(--fe-property_detail-subtle_text, #64748b); }
    .property-detail-page .text-slate-400 { color: var(--fe-agent_card-avatar_icon, #94a3b8); }
    .property-detail-page .text-slate-300 { color: var(--fe-property_detail-meta_divider, #cbd5e1); }

    .property-detail-page .bg-slate-100 { background-color: var(--fe-property_detail-chip_bg, #f1f5f9); }
    .property-detail-page .bg-slate-50 { background-color: var(--fe-property_detail-card_bg, #f8fafc); }
    .property-detail-page .bg-white { background-color: var(--fe-property_detail-panel_bg, #ffffff); }
    .property-detail-page .bg-white\/90 { background-color: var(--fe-property_detail-share_button_bg, rgba(255,255,255,0.9)); }

    .property-detail-page .border-slate-200 { border-color: var(--fe-property_detail-panel_border, #e2e8f0); }
    .property-detail-page .border-slate-100 { border-color: var(--fe-property_detail-card_border, #f1f5f9); }
    .property-detail-page .hover\:bg-slate-50:hover { background-color: var(--fe-property_detail-card_bg, #f8fafc); }
    .property-detail-page .hover\:text-slate-900:hover { color: var(--fe-property_detail-breadcrumb_link_hover, #0f172a); }

    .property-detail-page .bg-amber-50 { background-color: var(--fe-property_detail-source_notice_bg, #fef3c7); }
    .property-detail-page .border-amber-200 { border-color: var(--fe-property_detail-source_notice_border, #fcd34d); }
    .property-detail-page .text-amber-800 { color: var(--fe-property_detail-source_notice_label, #92400e); }
    .property-detail-page .text-amber-900 { color: var(--fe-property_detail-source_notice_text, #78350f); }

    .property-detail-page .bg-rose-50 { background-color: var(--fe-property_detail-error_bg, #fef2f2); }
    .property-detail-page .border-rose-200 { border-color: var(--fe-property_detail-error_border, #fecaca); }
    .property-detail-page .text-rose-900 { color: var(--fe-property_detail-error_title, #881337); }
    .property-detail-page .text-rose-800 { color: var(--fe-property_detail-error_text, #9f1239); }
    .property-detail-page .hover\:bg-rose-50:hover { background-color: var(--fe-property_detail-error_secondary_hover_bg, #fff1f2); }

    /* Keep component-specific hovers above utility-level fallbacks */
    .property-detail-page .pd-btn-call.hover\:bg-slate-50:hover {
      background-color: var(--fe-property_detail-call_button_hover_bg, #f8fafc);
    }

    .property-detail-page .pd-favorite-btn.hover\:bg-slate-50:hover {
      background-color: var(--fe-property_detail-favorite_button_hover_bg, #f8fafc);
    }

    @media (min-width: 1024px) {
      .property-thumbs .swiper-wrapper {
        height: 80px;
      }
    }
  </style>
@endpush

@push('scripts')
  <script>
    // ======================================================
    // PUBLIC PROPERTY DETAIL (responsive, Swiper gallery)
    // Route provides: $propertyId
    // ======================================================
    window.__PROPERTY_ID__ = @json($propertyId ?? null);

    const tPublic = (key, fallback = '') => (window.publicT ? window.publicT(key, fallback) : fallback);
    const isEnLocale = (window.__PUBLIC_LOCALE__ || 'es') === 'en';
    const publicContact = window.__PUBLIC_CONTACT__ || {};
    const propertyIconUrls = {
      location: @json(asset('iconos-base/ubicacion.svg')),
      bedrooms: @json(asset('iconos-base/recamaras.svg')),
      bathrooms: @json(asset('iconos-base/banos.svg')),
      halfBathrooms: @json(asset('iconos-base/medio-bano.svg')),
      parking: @json(asset('iconos-base/estacionamiento.svg')),
      construction: @json(asset('iconos-base/construccion.svg')),
      lot: @json(asset('iconos-base/area.svg')),
      floors: @json(asset('iconos-base/propiedades.svg')),
      age: @json(asset('iconos-base/construccion.svg')),
      furnished: @json(asset('iconos-base/amueblado.svg')),
      unfurnished: @json(asset('iconos-base/no-amueblado.svg')),
      pool: @json(asset('iconos-base/alberca.svg')),
      yard: @json(asset('iconos-base/jardin.svg')),
      terrace: @json(asset('iconos-base/terraza.svg')),
      roofGarden: @json(asset('iconos-base/roof-garden.svg')),
      pets: @json(asset('iconos-base/mascotas.svg')),
      property: @json(asset('iconos-base/propiedades.svg')),
    };

    const contactPhoneDisplay = (publicContact.phone || '+52 55 1234 5678').toString();
    const contactPhone = contactPhoneDisplay.replace(/[^\d+]/g, '') || '+525512345678';
    const contactWhatsapp = (publicContact.whatsapp || '525512345678').toString().replace(/\D/g, '') || '525512345678';

    function normalizeIconText(value) {
      return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
    }

    function propertyIcon(name, className = 'w-6 h-6') {
      const src = propertyIconUrls[name];
      return src ? `<img src="${src}" alt="" aria-hidden="true" class="${className} inline-block object-contain opacity-75">` : '';
    }

    function iconForFeatureLabel(label) {
      const text = normalizeIconText(label);
      if (text.includes('no amuebl') || text.includes('sin amuebl') || text.includes('sin muebl') || text.includes('unfurnished')) return 'unfurnished';
      if (text.includes('amuebl') || text.includes('amobl') || text.includes('furnished')) return 'furnished';
      if (text.includes('alberca') || text.includes('piscina') || text.includes('pool')) return 'pool';
      if (text.includes('jardin') || text.includes('yard') || text.includes('garden')) return 'yard';
      if (text.includes('roof') || text.includes('azotea')) return 'roofGarden';
      if (text.includes('terraza') || text.includes('terrace') || text.includes('patio') || text.includes('balcon') || text.includes('balcony')) return 'terrace';
      if (text.includes('mascota') || text.includes('pet')) return 'pets';
      if (text.includes('garage') || text.includes('cochera') || text.includes('estacionamiento') || text.includes('parking')) return 'parking';
      if (text.includes('bano') || text.includes('bath')) return 'bathrooms';
      if (text.includes('recamara') || text.includes('habitacion') || text.includes('dormitorio') || text.includes('bedroom')) return 'bedrooms';
      if (text.includes('terreno') || text.includes('lote') || text.includes('lot')) return 'lot';
      if (text.includes('construccion') || text.includes('construction')) return 'construction';
      if (text.includes('casita') || text.includes('casa') || text.includes('house')) return 'property';
      return null;
    }

    function formatFurnishedValue(value) {
      const raw = String(value || '').trim();
      if (!raw) return null;
      const text = normalizeIconText(raw);
      if (['0', 'false', 'no', 'none'].includes(text) || text.includes('sin amuebl') || text.includes('unfurnished')) {
        return tPublic('property.unfurnished', isEnLocale ? 'Unfurnished' : 'Sin amueblar');
      }
      if (text.includes('semi')) {
        return tPublic('property.semiFurnished', isEnLocale ? 'Semi-furnished' : 'Semi amueblado');
      }
      return tPublic('property.furnishedValue', isEnLocale ? 'Furnished' : 'Amueblado');
    }

    function furnishedIcon(value) {
      const text = normalizeIconText(value);
      return (['0', 'false', 'no', 'none'].includes(text) || text.includes('sin amuebl') || text.includes('unfurnished'))
        ? 'unfurnished'
        : 'furnished';
    }

    function safeText(v, fallback = '—') {
      const s = (v ?? '').toString().trim();
      return s ? s : fallback;
    }

    function formatIso(iso) {
      if (!iso) return '—';
      try {
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return String(iso);
        return d.toLocaleDateString(isEnLocale ? 'en-US' : 'es-CO', {
          year: 'numeric',
          month: 'short',
          day: '2-digit',
        });
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
      const url = asset.serving_url || asset.url || asset.public_url || asset.path || null;
      if (url) return url;
      if (asset.pivot && asset.pivot.source_url) return asset.pivot.source_url;
      return asset.source_url || null;
    }

    function buildImageList(property) {
      const imgs = [];
      const defaultAlt = tPublic('common.properties', isEnLocale ? 'Property' : 'Propiedad');

      const coverUrl = resolveMediaUrl(property.cover_media_asset);
      if (coverUrl) imgs.push({ url: coverUrl, alt: property.title || defaultAlt });

      const gallery = Array.isArray(property.media_assets) ? property.media_assets : [];
      gallery.forEach((m) => {
        const url = resolveMediaUrl(m);
        if (!url) return;
        if (imgs.some((i) => i.url === url)) return;
        imgs.push({ url, alt: property.title || defaultAlt });
      });

      if (!imgs.length) {
        imgs.push({
          url: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
          alt: defaultAlt,
        });
      }

      return imgs;
    }

    function buildMapsUrl({ lat, lng, q }) {
      if (lat && lng) return `https://www.google.com/maps?q=${encodeURIComponent(lat + ',' + lng)}`;
      return `https://www.google.com/maps?q=${encodeURIComponent(q || '')}`;
    }

    function buildOsmEmbed({ lat, lng }) {
      if (!(lat && lng)) return 'about:blank';

      const latNum = parseFloat(lat);
      const lngNum = parseFloat(lng);
      if (Number.isNaN(latNum) || Number.isNaN(lngNum)) return 'about:blank';

      const d = 0.005;
      const left = (lngNum - d).toFixed(6);
      const right = (lngNum + d).toFixed(6);
      const top = (latNum + d).toFixed(6);
      const bottom = (latNum - d).toFixed(6);

      return `https://www.openstreetmap.org/export/embed.html?bbox=${left}%2C${bottom}%2C${right}%2C${top}&layer=mapnik&marker=${encodeURIComponent(latNum + ',' + lngNum)}`;
    }

    function operationLabel(op) {
      const t = (op?.operation_type || '').toString().toLowerCase();
      if (['sale', 'venta'].includes(t)) return tPublic('common.sale', isEnLocale ? 'For sale' : 'En venta');
      if (['rent', 'rental', 'arriendo', 'renta'].includes(t)) return tPublic('common.rent', isEnLocale ? 'For rent' : 'En renta');
      return safeText(op?.operation_type, tPublic('property.available', isEnLocale ? 'Available' : 'Disponible'));
    }

    function operationBadgeColor(op) {
      const t = (op?.operation_type || '').toString().toLowerCase();
      if (['sale', 'venta'].includes(t)) {
        return 'linear-gradient(to right, var(--fe-property_detail-badge_sale_from, var(--fe-property_detail-badge_sale, #768D59)), var(--fe-property_detail-badge_sale_to, var(--fe-property_detail-badge_sale, #768D59)))';
      }
      if (['rent', 'rental', 'arriendo', 'renta'].includes(t)) {
        return 'linear-gradient(to right, var(--fe-property_detail-badge_rent_from, var(--fe-property_detail-badge_rent, #D1A054)), var(--fe-property_detail-badge_rent_to, var(--fe-property_detail-badge_rent, #D1A054)))';
      }
      return 'linear-gradient(to right, var(--fe-property_detail-contact_button_from, var(--fe-primary-from, #D1A054)), var(--fe-property_detail-contact_button_to, var(--fe-primary-to, #768D59)))';
    }

    function getPrimaryPrice(property) {
      const ops = Array.isArray(property.operations) ? property.operations : [];
      const first = ops[0] || null;
      if (!first) return tPublic('common.consultPrice', isEnLocale ? 'Ask for price' : 'Consultar precio');

      const fallbackAmount = (typeof window.formatDisplayPrice === 'function')
        ? window.formatDisplayPrice(first.amount, first.currency?.code || first.currency_code)
        : '';

      return first.formatted_amount || fallbackAmount || tPublic('common.consultPrice', isEnLocale ? 'Ask for price' : 'Consultar precio');
    }

    function setError(message) {
      document.getElementById('errorBox').classList.remove('hidden');
      document.getElementById('errorText').textContent = safeText(
        message,
        tPublic('property.unknownError', isEnLocale ? 'Unexpected error' : 'Error inesperado')
      );
    }

    let gallerySwiper = null;
    let thumbsSwiper = null;

    function initSwipers() {
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
        autoHeight: false,
        speed: 650,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        thumbs: { swiper: thumbsSwiper },
      });
    }

    function renderProperty(property) {
      document.getElementById('errorBox').classList.add('hidden');

      const title = safeText(property.title, tPublic('common.properties', isEnLocale ? 'Property' : 'Propiedad'));
      const siteName = tPublic('common.siteName', 'San Miguel Properties');
      document.title = `${title} | ${siteName}`;
      document.getElementById('propertyTitle').textContent = title;
      document.getElementById('breadcrumbTitle').textContent = title;
      document.getElementById('interestPropertyName').value = title;
      document.getElementById('interestPropertyId').value = String(property.id || window.__PROPERTY_ID__ || '');
      document.getElementById('propertyIdChip').textContent = `#${property.id}`;
      document.getElementById('propertyIdChip').classList.remove('hidden');

      const city = property.location?.city;
      const cityArea = property.location?.city_area;
      const region = property.location?.region;
      const loc = [city, cityArea, region].filter(Boolean).join(', ');

      document.getElementById('propertyLocation').textContent = safeText(
        loc,
        tPublic('common.locationAvailable', isEnLocale ? 'Location available' : 'Ubicacion disponible')
      );

      const updatedLabel = tPublic('common.updated', isEnLocale ? 'Updated' : 'Actualizado');
      document.getElementById('propertyUpdated').textContent = `${updatedLabel}: ${formatIso(property.updated_at || property.easybroker_updated_at)}`;

      if (property.property_type_name) {
        document.getElementById('badgeTypeText').textContent = property.property_type_name;
        document.getElementById('badgeType').classList.remove('hidden');
      }

      const ops = Array.isArray(property.operations) ? property.operations : [];
      if (ops.length) {
        document.getElementById('badgeOperationText').textContent = operationLabel(ops[0]);
        document.getElementById('badgeOperation').style.background = operationBadgeColor(ops[0]);
        document.getElementById('badgeOperation').classList.remove('hidden');
      }

      const price = getPrimaryPrice(property);
      document.getElementById('priceMain').textContent = String(price);

      const operationsList = document.getElementById('operationsList');
      if (!ops.length) {
        operationsList.innerHTML = `
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 pd-empty-state">
            <p class="text-xs font-semibold text-slate-600">${tPublic('common.operation', isEnLocale ? 'Operation' : 'Operacion')}</p>
            <p class="mt-1 text-base font-semibold text-slate-900">${tPublic('property.operationAsk', isEnLocale ? 'Check availability' : 'Consultar disponibilidad')}</p>
          </div>
        `;
      } else {
        operationsList.innerHTML = ops.map((op) => {
          const label = operationLabel(op);
          const fallbackAmount = (typeof window.formatDisplayPrice === 'function')
            ? window.formatDisplayPrice(op.amount, op.currency?.code || op.currency_code)
            : '';
          const amount = op.formatted_amount || fallbackAmount || tPublic('common.consultPrice', isEnLocale ? 'Ask for price' : 'Consultar');
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

      const yesLabel = tPublic('common.yes', isEnLocale ? 'Yes' : 'Si');
      const furnishedValue = formatFurnishedValue(property.furnished);
      const highlights = [
        { icon: 'bedrooms', label: tPublic('property.bedrooms', isEnLocale ? 'Bedrooms' : 'Recamaras'), value: Number(property.bedrooms) > 0 ? property.bedrooms : null },
        { icon: 'bathrooms', label: tPublic('property.bathrooms', isEnLocale ? 'Bathrooms' : 'Banos'), value: Number(property.bathrooms) > 0 ? property.bathrooms : null },
        { icon: 'halfBathrooms', label: tPublic('property.halfBathrooms', isEnLocale ? 'Half bathrooms' : 'Medios banos'), value: Number(property.half_bathrooms) > 0 ? property.half_bathrooms : null },
        { icon: 'parking', label: tPublic('property.parking', isEnLocale ? 'Parking' : 'Estacionamientos'), value: Number(property.parking_spaces) > 0 ? property.parking_spaces : null },
        { icon: 'construction', label: tPublic('property.construction', isEnLocale ? 'Construction' : 'Construccion'), value: property.construction_size ? `${property.construction_size} m²` : null },
        { icon: 'lot', label: tPublic('property.lot', isEnLocale ? 'Lot' : 'Lote'), value: property.lot_size ? `${property.lot_size} m²` : null },
        { icon: 'floors', label: tPublic('property.floors', isEnLocale ? 'Floors' : 'Pisos'), value: property.floors ?? property.floor },
        { icon: 'age', label: tPublic('property.age', isEnLocale ? 'Age' : 'Edad'), value: property.age },
        { icon: furnishedValue ? furnishedIcon(property.furnished) : 'furnished', label: tPublic('property.furnished', isEnLocale ? 'Furnished' : 'Amueblado'), value: furnishedValue },
        { icon: 'yard', label: tPublic('property.yard', isEnLocale ? 'Garden' : 'Jardin'), value: property.with_yard ? yesLabel : null },
        { icon: 'pool', label: tPublic('property.pool', isEnLocale ? 'Pool' : 'Alberca'), value: property.pool ? yesLabel : null },
        { icon: 'property', label: tPublic('property.casita', isEnLocale ? 'Casita' : 'Casita'), value: property.casita ? yesLabel : null },
      ].filter((x) => x.value !== null && x.value !== undefined && String(x.value).trim() !== '');

      const highlightsGrid = document.getElementById('highlightsGrid');
      if (!highlights.length) {
        highlightsGrid.innerHTML = `
          <div class="col-span-2 sm:col-span-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-slate-700 pd-empty-state">
            ${tPublic('property.noExtraInfo', isEnLocale ? 'No additional information available for this property.' : 'No hay informacion adicional registrada para esta propiedad.')}
          </div>
        `;
      } else {
        highlightsGrid.innerHTML = highlights.map((h) => `
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="flex items-start gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-white">
                ${propertyIcon(h.icon)}
              </span>
              <span class="min-w-0">
                <span class="block text-xs font-semibold text-slate-600">${escapeHtml(h.label)}</span>
                <span class="mt-1 block text-lg font-extrabold text-slate-900">${escapeHtml(String(h.value))}</span>
              </span>
            </div>
          </div>
        `).join('');
      }

      const descriptionFallback = tPublic('property.noDescription', isEnLocale ? 'No description available.' : 'Sin descripcion.');
      const descriptionEl = document.getElementById('description');
      if (descriptionEl) {
        if (typeof window.publicRenderRichText === 'function') {
          window.publicRenderRichText(descriptionEl, property.description, descriptionFallback);
        } else {
          descriptionEl.textContent = safeText(property.description, descriptionFallback);
        }
      }

      const featuresWrap = document.getElementById('featuresWrap');
      const features = Array.isArray(property.features) ? property.features : [];
      const featureFallbackPrefix = tPublic('property.featureFallbackPrefix', isEnLocale ? 'Feature #' : 'Caracteristica #');
      featuresWrap.innerHTML = features.length
        ? features.map((f) => {
          const label = f.name || f.slug || (featureFallbackPrefix + f.id);
          const iconName = iconForFeatureLabel(label);
          return `
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold pd-feature-tag" style="background-color: var(--fe-property_detail-feature_tag_bg, var(--fe-properties-tag_inactive_bg, #f1f5f9)); color: var(--fe-property_detail-feature_tag_text, var(--fe-properties-tag_inactive_text, #475569));">
              ${iconName ? propertyIcon(iconName, 'w-3.5 h-3.5') : ''}
              <span>${escapeHtml(label)}</span>
            </span>
          `;
        }).join('')
        : `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 pd-feature-tag">${tPublic('property.noFeatures', isEnLocale ? 'No features' : 'Sin caracteristicas')}</span>`;

      const tagsWrap = document.getElementById('tagsWrap');
      const tags = Array.isArray(property.tags) ? property.tags : [];
      const tagFallbackPrefix = tPublic('property.tagFallbackPrefix', isEnLocale ? 'Tag #' : 'Etiqueta #');
      tagsWrap.innerHTML = tags.length
        ? tags.map((t) => {
          const label = t.name || t.slug || (tagFallbackPrefix + t.id);
          const iconName = iconForFeatureLabel(label);
          return `
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold pd-feature-tag" style="background-color: var(--fe-property_detail-feature_tag_bg, var(--fe-properties-tag_inactive_bg, #f1f5f9)); color: var(--fe-property_detail-feature_tag_text, var(--fe-properties-tag_inactive_text, #475569));">
              ${iconName ? propertyIcon(iconName, 'w-3.5 h-3.5') : ''}
              <span>${escapeHtml(label)}</span>
            </span>
          `;
        }).join('')
        : `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 pd-feature-tag">${tPublic('property.noTags', isEnLocale ? 'No tags' : 'Sin etiquetas')}</span>`;

      const street = property.location?.street;
      const postal = property.location?.postal_code;
      const addr = [street, cityArea, city, region, postal].filter(Boolean).join(', ');
      document.getElementById('addressLine').textContent = safeText(
        addr,
        tPublic('common.locationAvailable', isEnLocale ? 'Location available' : 'Ubicacion disponible')
      );

      const lat = property.location?.latitude;
      const lng = property.location?.longitude;
      const q = addr || loc || title;
      const mapsUrl = buildMapsUrl({ lat, lng, q });
      document.getElementById('mapsLink').href = mapsUrl;
      document.getElementById('mapFrame').src = buildOsmEmbed({ lat, lng });

      const agent = property.agent_user;
      const agency = property.agency;
      const contactAgency = property.contact_agency || null;
      const hideExternalAgents = !!property.hide_external_agents;
      const belongsToExternalAgency = !!property.belongs_to_external_agency;

      const agentNameEl = document.getElementById('agentName');
      const agencyNameEl = document.getElementById('agencyName');
      const agentAvatarEl = document.getElementById('agentAvatar');

      if (agentAvatarEl) {
        agentAvatarEl.innerHTML = `
          <svg class="w-7 h-7 text-slate-400 pd-icon-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
          </svg>
        `;
      }

      if (contactAgency?.name) {
        agentNameEl.textContent = contactAgency.name;
        agencyNameEl.textContent = tPublic('property.mainAgencyContact', isEnLocale ? 'Main agency contact' : 'Contacto de agencia principal');
      } else {
        if (hideExternalAgents) {
          agentNameEl.textContent = tPublic('common.siteName', 'San Miguel Properties');
          agencyNameEl.textContent = tPublic('property.mainAgencyContact', isEnLocale ? 'Main agency contact' : 'Contacto de agencia principal');
        } else {
          if (agent?.name) agentNameEl.textContent = agent.name;
          if (agency?.name) agencyNameEl.textContent = agency.name;
        }
      }

      const profileUrl = resolveMediaUrl(contactAgency?.image || (hideExternalAgents ? null : agent?.profile_image));
      if (profileUrl && agentAvatarEl) {
        const avatarAlt = contactAgency?.name || agent?.name || tPublic('property.advisor', isEnLocale ? 'Advisor' : 'Asesor');
        agentAvatarEl.innerHTML = `<img src="${escapeHtml(profileUrl)}" alt="${escapeHtml(avatarAlt)}" class="w-full h-full object-cover" />`;
      }

      const sourceAgencyNoticeWrap = document.getElementById('sourceAgencyNotice');
      const sourceAgencyNoticeText = document.getElementById('sourceAgencyNoticeText');
      if (sourceAgencyNoticeWrap && sourceAgencyNoticeText) {
        if (belongsToExternalAgency) {
          const fallbackNotice = tPublic(
            'property.sourceAgencyNotice',
            isEnLocale
              ? 'This property is shared through MLS. Contact is handled by our main agency.'
              : 'Esta propiedad se comparte por MLS. El contacto se atiende con nuestra agencia principal.'
          );

          sourceAgencyNoticeText.textContent = String(property.source_agency_notice || fallbackNotice);
          sourceAgencyNoticeWrap.classList.remove('hidden');
        } else {
          sourceAgencyNoticeText.textContent = '';
          sourceAgencyNoticeWrap.classList.add('hidden');
        }
      }

      const mlsWrap = document.getElementById('mlsAgentsWrap');
      const mlsAgents = hideExternalAgents ? [] : (Array.isArray(property.mls_agents) ? property.mls_agents : []);

      if (mlsWrap) {
        if (!mlsAgents.length) {
          mlsWrap.classList.add('hidden');
          mlsWrap.innerHTML = '';
        } else {
          const sorted = [...mlsAgents].sort((a, b) => {
            const ap = a?.pivot?.is_primary ? 1 : 0;
            const bp = b?.pivot?.is_primary ? 1 : 0;
            return bp - ap;
          });

          const rows = sorted.map((a) => {
            const name = safeText(a?.full_name || a?.name, tPublic('property.agent', isEnLocale ? 'Agent' : 'Agente'));
            const office = safeText(a?.office_name, '—');
            const photo = a?.photo || a?.photo_url || null;
            const phone = (a?.mobile || a?.phone || '').toString().trim();
            const email = (a?.email || '').toString().trim();

            const isPrimary = !!a?.pivot?.is_primary;
            const badge = isPrimary
              ? `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold text-white" style="background: linear-gradient(to right, var(--fe-agent_card-primary_badge_from, var(--fe-primary-from, #D1A054)), var(--fe-agent_card-primary_badge_to, var(--fe-primary-to, #768D59))); color: var(--fe-agent_card-primary_badge_text, #ffffff);">${tPublic('property.primary', isEnLocale ? 'Primary' : 'Principal')}</span>`
              : '';

            const avatarHtml = photo
              ? `<img src="${escapeHtml(photo)}" alt="${escapeHtml(name)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.style.display='none';" />`
              : `
                  <svg class="w-6 h-6 text-slate-400 pd-icon-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                  </svg>
                `;

            const contactBits = [
              phone ? `<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 pd-contact-link" href="tel:${escapeHtml(phone)}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        ${escapeHtml(phone)}
                      </a>` : '',
              email ? `<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 pd-contact-link" href="mailto:${escapeHtml(email)}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        ${escapeHtml(email)}
                      </a>` : '',
            ].filter(Boolean).join('');

            return `
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 pd-mls-card">
                <div class="flex items-start gap-4">
                  <div class="size-12 rounded-2xl overflow-hidden border border-slate-200 bg-white grid place-items-center shrink-0 pd-agent-avatar">${avatarHtml}</div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                      <p class="font-bold text-slate-900 truncate pd-mls-name">${escapeHtml(name)}</p>
                      ${badge}
                    </div>
                    <p class="mt-0.5 text-xs text-slate-600 truncate pd-mls-office">${escapeHtml(office)}</p>
                    ${contactBits ? `<div class="mt-3 flex flex-col gap-2">${contactBits}</div>` : `<p class="mt-3 text-sm text-slate-600 pd-contact-unavailable">${tPublic('property.contactUnavailable', isEnLocale ? 'Contact not available' : 'Contacto no disponible')}</p>`}
                  </div>
                </div>
              </div>
            `;
          }).join('');

          mlsWrap.innerHTML = rows;
          mlsWrap.classList.remove('hidden');
        }
      }

      const waTemplate = tPublic(
        'property.whatsappMessage',
        isEnLocale
          ? 'Hi, I am interested in property #{id}: {title}. Can you share more information?'
          : 'Hola, me interesa la propiedad #{id}: {title}. ¿Me puedes dar más información?'
      );
      const waText = waTemplate
        .replaceAll('{id}', String(property.id || ''))
        .replaceAll('{title}', title);

      const favoriteButton = document.getElementById('btnFavorite');
      if (favoriteButton) {
        favoriteButton.dataset.propertyId = String(property.id || window.__PROPERTY_ID__ || '');
        window.publicFavorites?.syncButton(favoriteButton);
      }

      document.getElementById('btnWhatsApp').href = `https://wa.me/${contactWhatsapp}?text=${encodeURIComponent(waText)}`;
      document.getElementById('btnCall').href = `tel:${contactPhone}`;

      const imgs = buildImageList(property);
      const photoLabel = imgs.length === 1
        ? tPublic('property.photoSingular', isEnLocale ? 'photo' : 'foto')
        : tPublic('property.photoPlural', isEnLocale ? 'photos' : 'fotos');
      document.getElementById('galleryCount').textContent = `${imgs.length} ${photoLabel}`;
      document.getElementById('galleryCount').classList.remove('hidden');

      const galleryWrapper = document.getElementById('galleryWrapper');
      const thumbsWrapper = document.getElementById('thumbsWrapper');

      galleryWrapper.innerHTML = imgs.map((img) => `
        <div class="swiper-slide h-full">
          <img src="${escapeHtml(img.url)}" alt="${escapeHtml(img.alt)}" class="block w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';" />
        </div>
      `).join('');

      thumbsWrapper.innerHTML = imgs.map((img) => `
        <div class="swiper-slide" style="width: 96px;">
          <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 pd-gallery-thumb" style="aspect-ratio: 4 / 3;">
            <img src="${escapeHtml(img.url)}" alt="${escapeHtml(img.alt)}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';" />
          </div>
        </div>
      `).join('');

      initSwipers();
    }

    function setInterestFeedback(type, message) {
      const feedback = document.getElementById('propertyInterestFeedback');
      if (!feedback) return;

      feedback.textContent = message;
      feedback.dataset.state = type;
      feedback.classList.remove('hidden');
    }

    async function submitPropertyInterestForm(event) {
      event.preventDefault();

      const form = event.currentTarget;
      const fields = form.elements;
      const submitButton = form.querySelector('.pd-interest-submit');
      const payload = {
        property_id: fields.namedItem('property_id')?.value || '',
        property_name: fields.namedItem('property_name')?.value || '',
        full_name: fields.namedItem('full_name')?.value?.trim() || '',
        email: fields.namedItem('email')?.value?.trim() || '',
        phone: fields.namedItem('phone')?.value?.trim() || '',
        privacy: Boolean(fields.namedItem('privacy')?.checked),
        source: 'property_detail_form',
        property_context: 'existing_listing',
        contact_type: 'buyer',
        ...((window.publicLeadTrackingPayload && window.publicLeadTrackingPayload()) || {}),
      };

      if (!payload.property_id || !payload.property_name || !payload.full_name || !payload.email || !payload.phone || !payload.privacy) {
        setInterestFeedback('error', tPublic('contact.requiredFields', isEnLocale ? 'Please complete all required fields.' : 'Por favor completa todos los campos requeridos.'));
        return;
      }

      try {
        if (submitButton) submitButton.disabled = true;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/public/property-contact-requests', {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data?.success) {
          setInterestFeedback('error', data?.message || tPublic('contact.submitError', isEnLocale ? 'There was an error sending your message. Please try again.' : 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.'));
          return;
        }

        fields.namedItem('full_name').value = '';
        fields.namedItem('email').value = '';
        fields.namedItem('phone').value = '';
        fields.namedItem('privacy').checked = false;
        setInterestFeedback('success', tPublic('contact.submitSuccess', isEnLocale ? 'Message sent successfully. We will contact you soon.' : 'Mensaje enviado con exito. Nos pondremos en contacto contigo pronto.'));
      } catch (_error) {
        setInterestFeedback('error', tPublic('contact.connectionError', isEnLocale ? 'Connection error. Please check your internet and try again.' : 'Error de conexion. Por favor verifica tu internet e intenta de nuevo.'));
      } finally {
        if (submitButton) submitButton.disabled = false;
      }
    }

    async function loadProperty() {
      const id = window.__PROPERTY_ID__;
      if (!id) {
        setError(tPublic('property.missingId', isEnLocale ? 'Property ID was not provided.' : 'No se recibio el ID de la propiedad.'));
        return;
      }

      try {
        const res = await fetch(`/api/public/properties/${id}`, {
          headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (!res.ok || !data?.success) {
          const httpFallback = isEnLocale ? `HTTP error ${res.status}` : `Error HTTP ${res.status}`;
          setError(data?.message || tPublic('property.httpError', httpFallback));
          return;
        }

        renderProperty(data.data);
      } catch (e) {
        console.error(e);
        setError(tPublic('property.networkError', isEnLocale ? 'Network error while loading the property.' : 'Error de red al cargar la propiedad.'));
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('btnRetry')?.addEventListener('click', loadProperty);
      document.getElementById('propertyInterestForm')?.addEventListener('submit', submitPropertyInterestForm);

      document.getElementById('btnShare')?.addEventListener('click', async () => {
        try {
          const url = window.location.href;
          if (navigator.share) {
            await navigator.share({ title: document.title, url });
            return;
          }
          await navigator.clipboard.writeText(url);
          window.dispatchEvent(new CustomEvent('api:response', {
            detail: {
              success: true,
              message: tPublic('property.copiedLink', isEnLocale ? 'Link copied to clipboard' : 'Enlace copiado al portapapeles'),
              code: 'COPIED',
            },
          }));
        } catch (_e) {
          // silent fallback
        }
      });

      loadProperty();
    });
  </script>
@endpush


