@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';
    $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
    $rich = static fn (?string $html, ?string $fallback = null): string => \App\Support\RichTextSanitizer::sanitize($html, $fallback);

    $pageTitle = $pageData?->entity?->title($locale) ?? ($isEn ? 'Sell With Us' : 'Vende con nosotros');
    $heroImage = $pageData?->image('seller_hero_image')
        ?: 'https://images.unsplash.com/photo-1518105779142-d975f22f1b0a?auto=format&fit=crop&w=1800&q=85';

    $guideAsset = $pageData?->media('seller_guide_file');
    $guideUrl = $guideAsset?->serving_url
        ?: $guideAsset?->url
        ?: trim((string) ($pageData?->field('seller_guide_url') ?? ''));

    $testimonialRows = $pageData?->repeater('seller_testimonials') ?? [];
    $testimonials = collect($testimonialRows)
        ->map(fn ($row) => [
            'quote' => trim((string) ($row->field('testimonial_quote') ?? '')),
            'name' => trim((string) ($row->field('testimonial_name') ?? '')),
            'context' => trim((string) ($row->field('testimonial_context') ?? '')),
        ])
        ->filter(fn ($row) => $row['quote'] !== '')
        ->values()
        ->all();

    $leadLabels = [
        'sending' => $isEn ? 'Sending...' : 'Enviando...',
        'submit' => $txt('seller_form_button', 'Enviar solicitud', 'Send request'),
        'success' => $txt('seller_form_success', 'Solicitud enviada correctamente. Nos pondremos en contacto contigo pronto.', 'Request sent successfully. We will contact you shortly.'),
    ];
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
    .sell-with-us-page {
        background-color: var(--fe-sell_content-page_bg, var(--fe-sell_page-page_bg, #ffffff));
        color: var(--fe-sell_content-body_text, var(--fe-sell_page-body_text, var(--fe-ui-body_text, #0f172a)));
    }

    .sell-with-us-page .sell-hero-section {
        background-color: var(--fe-sell_hero-section_bg, var(--fe-sell_page-hero_section_bg, #0f172a));
    }

    .sell-with-us-page .sell-hero-overlay-base {
        background-color: var(--fe-sell_hero-overlay_bg, var(--fe-sell_page-hero_overlay_bg, rgba(15,23,42,0.70)));
    }

    .sell-with-us-page .sell-hero-overlay-gradient {
        background: linear-gradient(
            110deg,
            var(--fe-sell_hero-overlay_from, var(--fe-sell_page-hero_overlay_from, rgba(15,23,42,0.88))) 0%,
            var(--fe-sell_hero-overlay_mid, var(--fe-sell_page-hero_overlay_mid, rgba(15,23,42,0.70))) 45%,
            var(--fe-sell_hero-overlay_to, var(--fe-sell_page-hero_overlay_to, rgba(118,141,89,0.42))) 100%
        );
    }

    .sell-with-us-page .sell-hero-title {
        color: var(--fe-sell_hero-title, var(--fe-sell_page-hero_title, #ffffff)) !important;
    }

    .sell-with-us-page .sell-hero-text {
        color: var(--fe-sell_hero-text, var(--fe-sell_page-hero_text, rgba(255,255,255,0.82)));
    }

    .sell-with-us-page .sell-btn-primary,
    .sell-with-us-page .sell-btn-secondary {
        border: 1px solid transparent;
        transition: transform 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
    }

    .sell-with-us-page .sell-btn-primary {
        background: none !important;
        background-color: var(--fe-buttons-primary_bg, #D1A054) !important;
        border-color: var(--fe-buttons-primary_border, var(--fe-buttons-primary_bg, #D1A054)) !important;
        color: var(--fe-buttons-primary_text, #ffffff) !important;
    }

    .sell-with-us-page .sell-btn-primary:hover {
        background-color: var(--fe-buttons-primary_hover_bg, var(--fe-buttons-primary_bg, #D1A054)) !important;
    }

    .sell-with-us-page .sell-btn-secondary {
        background: none !important;
        background-color: var(--fe-buttons-secondary_bg, #768D59) !important;
        border-color: var(--fe-buttons-secondary_border, var(--fe-buttons-secondary_bg, #768D59)) !important;
        color: var(--fe-buttons-secondary_text, #ffffff) !important;
    }

    .sell-with-us-page .sell-btn-secondary:hover {
        background-color: var(--fe-buttons-secondary_hover_bg, var(--fe-buttons-secondary_bg, #768D59)) !important;
    }

    .sell-with-us-page .sell-form-card {
        background-color: var(--fe-sell_form-card_bg, var(--fe-sell_page-form_bg, #ffffff));
        border-color: var(--fe-sell_form-card_border, var(--fe-sell_page-form_border, rgba(255,255,255,0.18)));
    }

    .sell-with-us-page .sell-eyebrow {
        color: var(--fe-sell_content-eyebrow, var(--fe-sell_page-eyebrow, #9a7035));
    }

    .sell-with-us-page .sell-form-card .sell-eyebrow {
        color: var(--fe-sell_form-eyebrow, var(--fe-sell_page-eyebrow, #9a7035));
    }

    .sell-with-us-page .sell-heading {
        color: var(--fe-sell_content-title, var(--fe-sell_page-title, #0f172a)) !important;
    }

    .sell-with-us-page .sell-form-card .sell-heading {
        color: var(--fe-sell_form-title, var(--fe-sell_page-title, #0f172a)) !important;
    }

    .sell-with-us-page .sell-body {
        color: var(--fe-sell_content-body_text, var(--fe-sell_page-body_text, #334155));
    }

    .sell-with-us-page .sell-muted {
        color: var(--fe-sell_content-muted_text, var(--fe-sell_page-muted_text, #475569));
    }

    .sell-with-us-page .sell-form-card .sell-muted {
        color: var(--fe-sell_form-muted_text, var(--fe-sell_page-muted_text, #475569));
    }

    .sell-with-us-page .sell-alert-success {
        background-color: var(--fe-sell_form-alert_success_bg, var(--fe-sell_page-alert_success_bg, #ecfdf5));
        border-color: var(--fe-sell_form-alert_success_border, var(--fe-sell_page-alert_success_border, #bbf7d0));
        color: var(--fe-sell_form-alert_success_text, var(--fe-sell_page-alert_success_text, #166534));
    }

    .sell-with-us-page .sell-alert-error {
        background-color: var(--fe-sell_form-alert_error_bg, var(--fe-sell_page-alert_error_bg, #fef2f2));
        border-color: var(--fe-sell_form-alert_error_border, var(--fe-sell_page-alert_error_border, #fecaca));
        color: var(--fe-sell_form-alert_error_text, var(--fe-sell_page-alert_error_text, #991b1b));
    }

    .sell-with-us-page .sell-label {
        color: var(--fe-sell_form-label, var(--fe-sell_form-text, var(--fe-sell_page-body_text, #334155)));
    }

    .sell-with-us-page .sell-input {
        background-color: var(--fe-sell_form-input_bg, var(--fe-sell_page-input_bg, #ffffff));
        border-color: var(--fe-sell_form-input_border, var(--fe-sell_page-input_border, #cbd5e1));
        color: var(--fe-sell_form-input_text, var(--fe-sell_page-input_text, #0f172a));
    }

    .sell-with-us-page .sell-input:focus {
        border-color: var(--fe-sell_form-input_focus, var(--fe-sell_page-input_focus, var(--fe-buttons-primary_bg, #D1A054)));
        --tw-ring-color: var(--fe-sell_form-input_focus_ring, var(--fe-sell_page-input_focus_ring, rgba(209,160,84,0.20)));
    }

    .sell-with-us-page .sell-checkbox {
        accent-color: var(--fe-sell_form-checkbox_accent, var(--fe-sell_page-checkbox_accent, var(--fe-buttons-secondary_bg, #768D59)));
        border-color: var(--fe-sell_form-input_border, var(--fe-sell_page-input_border, #cbd5e1));
    }

    .sell-with-us-page .sell-intro-section {
        background: linear-gradient(180deg, var(--fe-sell_content-intro_bg_from, var(--fe-sell_page-intro_bg_from, #ffffff)), var(--fe-sell_content-intro_bg_to, var(--fe-sell_page-intro_bg_to, #f8fafc)));
    }

    .sell-with-us-page .sell-article {
        border-color: var(--fe-sell_content-intro_border, var(--fe-sell_page-intro_border, var(--fe-buttons-primary_bg, #D1A054)));
    }

    .sell-with-us-page .sell-guide-section {
        background-color: var(--fe-sell_guide-section_bg, var(--fe-sell_page-guide_bg, #0f172a));
    }

    .sell-with-us-page .sell-guide-eyebrow {
        color: var(--fe-sell_guide-eyebrow, var(--fe-sell_page-guide_eyebrow, var(--fe-buttons-primary_bg, #D1A054)));
    }

    .sell-with-us-page .sell-guide-title {
        color: var(--fe-sell_guide-title, var(--fe-sell_page-guide_title, #ffffff)) !important;
    }

    .sell-with-us-page .sell-guide-text {
        color: var(--fe-sell_guide-text, var(--fe-sell_page-guide_text, rgba(255,255,255,0.75)));
    }

    .sell-with-us-page .sell-btn-disabled {
        border-color: var(--fe-sell_guide-pending_border, var(--fe-sell_page-guide_pending_border, rgba(255,255,255,0.20)));
        color: var(--fe-sell_guide-pending_text, var(--fe-sell_page-guide_pending_text, rgba(255,255,255,0.70))) !important;
    }

    .sell-with-us-page .sell-testimonials-section {
        background-color: var(--fe-sell_testimonials-section_bg, var(--fe-sell_page-testimonials_bg, #f8fafc));
    }

    .sell-with-us-page .sell-testimonial-card {
        background-color: var(--fe-sell_testimonials-card_bg, var(--fe-sell_page-testimonial_card_bg, #ffffff));
        border-color: var(--fe-sell_testimonials-card_border, var(--fe-sell_page-testimonial_card_border, #e2e8f0));
    }

    .sell-with-us-page .sell-testimonial-divider {
        border-color: var(--fe-sell_testimonials-divider, var(--fe-sell_page-testimonial_divider, #f1f5f9));
    }
</style>
@endpush

@section('content')
<div class="sell-with-us-page pt-24">
    <section class="sell-hero-section relative min-h-[calc(100vh-6rem)] overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ $heroImage }}" alt="{{ $pageTitle }}" class="h-full w-full object-cover" />
            <div class="sell-hero-overlay-base absolute inset-0"></div>
            <div class="sell-hero-overlay-gradient absolute inset-0"></div>
        </div>

        <div class="relative mx-auto grid min-h-[calc(100vh-6rem)] max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8">
            <div class="max-w-3xl">
                <h1 class="sell-hero-title text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                    {{ $txt('seller_hero_title', 'Vende tu propiedad con estrategia local', 'Sell your home with local strategy') }}
                </h1>
                <div class="sell-hero-text mt-6 max-w-2xl text-lg leading-relaxed rich-content">
                    {!! $rich($pageData?->field('seller_hero_subtitle'), $isEn
                        ? 'Selling your home in San Miguel de Allende is a major decision, and choosing the right representation matters.'
                        : 'Vender tu propiedad en San Miguel de Allende es una decision importante, y contar con la representacion adecuada hace toda la diferencia.') !!}
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#sellerLeadForm" class="sell-btn-primary inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-bold shadow-lg hover:-translate-y-0.5">
                        {{ $txt('seller_hero_form_cta', 'Solicitar asesoria', 'Request a consultation') }}
                    </a>
                    @if($guideUrl !== '')
                        <a href="{{ $guideUrl }}" download class="sell-btn-secondary inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-bold shadow-lg hover:-translate-y-0.5">
                            {{ $txt('seller_guide_button', 'Descargar manual', 'Download guide') }}
                        </a>
                    @endif
                </div>
            </div>

            <div id="sellerLeadForm" class="sell-form-card rounded-xl border p-6 shadow-2xl sm:p-8">
                <div>
                    <p class="sell-eyebrow text-sm font-semibold uppercase tracking-wide">
                        {{ $txt('seller_form_badge', 'Captura de datos', 'Lead capture') }}
                    </p>
                    <h2 class="sell-heading mt-2 text-2xl font-black">
                        {{ $txt('seller_form_title', 'Cuéntanos sobre tu propiedad', 'Tell us about your property') }}
                    </h2>
                    <p class="sell-muted mt-2 text-sm leading-relaxed">
                        {{ $txt('seller_form_subtitle', 'Completa el formulario y un broker de San Miguel Properties te contactara.', 'Complete the form and a San Miguel Properties broker will contact you.') }}
                    </p>
                </div>

                <form id="sellerForm" class="mt-6 space-y-4" x-data="sellerLeadForm()">
                    <div x-show="success" x-cloak class="sell-alert-success rounded-lg border px-4 py-3 text-sm font-medium" x-text="successMessage"></div>
                    <div x-show="error" x-cloak class="sell-alert-error rounded-lg border px-4 py-3 text-sm font-medium" x-text="errorMessage"></div>

                    <div>
                        <label for="seller-full-name" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'Full name' : 'Nombre completo' }} *</label>
                        <input id="seller-full-name" type="text" x-model="form.full_name" required class="sell-input w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2" autocomplete="name">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="seller-email" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'Email' : 'Correo' }} *</label>
                            <input id="seller-email" type="email" x-model="form.email" required class="sell-input w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2" autocomplete="email">
                        </div>
                        <div>
                            <label for="seller-phone" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'Phone' : 'Telefono' }} *</label>
                            <input id="seller-phone" type="tel" x-model="form.phone" required class="sell-input w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2" autocomplete="tel">
                        </div>
                    </div>

                    <div>
                        <label for="seller-property-address" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'Property address or neighborhood' : 'Direccion o colonia de la propiedad' }}</label>
                        <input id="seller-property-address" type="text" x-model="form.property_address" class="sell-input w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2">
                    </div>

                    <div>
                        <label for="seller-timeframe" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'When would you like to sell?' : 'Cuando quieres vender?' }}</label>
                        <select id="seller-timeframe" x-model="form.timeframe" class="sell-input w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2">
                            <option value="">{{ $isEn ? 'Select an option' : 'Selecciona una opcion' }}</option>
                            <option value="now">{{ $isEn ? 'As soon as possible' : 'Lo antes posible' }}</option>
                            <option value="1-3 months">{{ $isEn ? 'In 1 to 3 months' : 'En 1 a 3 meses' }}</option>
                            <option value="3-6 months">{{ $isEn ? 'In 3 to 6 months' : 'En 3 a 6 meses' }}</option>
                            <option value="exploring">{{ $isEn ? 'I am exploring options' : 'Estoy explorando opciones' }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="seller-message" class="sell-label mb-1 block text-sm font-semibold">{{ $isEn ? 'Message' : 'Mensaje' }}</label>
                        <textarea id="seller-message" x-model="form.message" rows="4" class="sell-input w-full resize-none rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2"></textarea>
                    </div>

                    <label class="sell-muted flex items-start gap-3 text-sm leading-relaxed">
                        <input type="checkbox" x-model="form.privacy" required class="sell-checkbox mt-1 h-4 w-4 rounded border">
                        <span>{{ $txt('seller_form_privacy', 'Acepto que San Miguel Properties me contacte sobre mi solicitud.', 'I agree that San Miguel Properties may contact me about my request.') }}</span>
                    </label>

                    <button type="submit" :disabled="loading" class="sell-btn-primary w-full rounded-lg px-6 py-3 text-sm font-bold shadow-lg hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0">
                        <span x-text="loading ? labels.sending : labels.submit"></span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="sell-intro-section py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[.85fr_1.15fr] lg:items-start">
                <div class="sticky top-28 hidden lg:block">
                    <p class="sell-eyebrow text-sm font-semibold uppercase tracking-wide">San Miguel Properties</p>
                    <h2 class="sell-heading mt-3 text-3xl font-black">
                        {{ $txt('seller_intro_title', 'Representacion local para vender bien', 'Local representation to sell well') }}
                    </h2>
                </div>

                <div class="space-y-8">
                    @foreach([
                        ['seller_intro_body', $isEn ? 'At San Miguel Properties, our brokers bring more than 30 years of combined local real estate experience to every listing. As a family-run local agency, we understand the nuances of this market because we live and work in it every day.' : 'En San Miguel Properties, nuestros brokers cuentan con mas de 30 años de experiencia local combinada en bienes raices. Como agencia local y familiar, entendemos los matices de este mercado porque vivimos y trabajamos en el todos los dias.'],
                        ['seller_local_experience_body', $isEn ? 'Our team has helped guide not only buyers and sellers, but also other real estate professionals through training and market education. That experience gives our clients a clear advantage when it comes to pricing, positioning and negotiation.' : 'Nuestro equipo ha guiado no solo a compradores y vendedores, sino tambien a otros profesionales inmobiliarios a traves de capacitacion y formacion en el mercado local. Esa experiencia brinda una ventaja clara al definir precio, posicionar la propiedad y negociar.'],
                        ['seller_process_body', $isEn ? 'From preparing your property for market to pricing, promotion, showings, negotiation and closing coordination, we manage each step with professionalism and care.' : 'Desde la preparacion de la propiedad para salir al mercado, hasta la estrategia de precio, promocion, visitas, negociacion y coordinacion del cierre, acompañamos cada etapa con profesionalismo y cuidado.'],
                        ['seller_final_body', $isEn ? 'When you sell with San Miguel Properties, you work with a local team that knows the market, values its reputation and treats every listing with the attention it deserves.' : 'Al vender con San Miguel Properties, trabajas con un equipo local que conoce el mercado, cuida su reputacion y trata cada propiedad con la atencion que merece.'],
                    ] as $block)
                        <article class="sell-article border-l-4 pl-6">
                            <div class="sell-body rich-content text-lg leading-relaxed">
                                {!! $rich($pageData?->field($block[0]), $block[1]) !!}
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="sell-guide-section py-14">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8">
            <div>
                <p class="sell-guide-eyebrow text-sm font-semibold uppercase tracking-wide">
                    {{ $txt('seller_guide_eyebrow', 'Guia para vendedores', 'Seller guide') }}
                </p>
                <h2 class="sell-guide-title mt-3 text-3xl font-black">
                    {{ $txt('seller_guide_title', 'Descarga el manual para vendedores', 'Download the seller guide') }}
                </h2>
                <p class="sell-guide-text mt-3 max-w-3xl text-base leading-relaxed">
                    {{ $txt('seller_guide_text', 'Consulta el material preparado por el equipo para entender los pasos clave antes de vender tu propiedad.', 'Review the material prepared by our team to understand the key steps before selling your property.') }}
                </p>
            </div>

            @if($guideUrl !== '')
                <a href="{{ $guideUrl }}" download class="sell-btn-primary inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-bold shadow-lg hover:-translate-y-0.5">
                    {{ $txt('seller_guide_button', 'Descargar manual', 'Download guide') }}
                </a>
            @else
                <button type="button" disabled class="sell-btn-disabled inline-flex cursor-not-allowed items-center justify-center rounded-lg border px-6 py-3 text-sm font-bold">
                    {{ $txt('seller_guide_pending_button', 'Manual pendiente', 'Guide pending') }}
                </button>
            @endif
        </div>
    </section>

    @if(!empty($testimonials))
        <section class="sell-testimonials-section py-16 lg:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h2 class="sell-heading text-3xl font-black">
                        {{ $txt('seller_testimonials_title', 'Testimoniales', 'Testimonials') }}
                    </h2>
                    <p class="sell-muted mt-3">
                        {{ $txt('seller_testimonials_intro', 'Opiniones agregadas manualmente desde el CMS.', 'Testimonials added manually from the CMS.') }}
                    </p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($testimonials as $testimonial)
                        <article class="sell-testimonial-card rounded-lg border p-6 shadow-sm">
                            <p class="sell-body text-base leading-relaxed">"{{ $testimonial['quote'] }}"</p>
                            @if($testimonial['name'] !== '' || $testimonial['context'] !== '')
                                <div class="sell-testimonial-divider mt-5 border-t pt-4">
                                    @if($testimonial['name'] !== '')
                                        <p class="sell-heading font-bold">{{ $testimonial['name'] }}</p>
                                    @endif
                                    @if($testimonial['context'] !== '')
                                        <p class="sell-muted text-sm">{{ $testimonial['context'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
const SELLER_LEAD_LABELS = {{ \Illuminate\Support\Js::from($leadLabels) }};

function sellerLeadForm() {
    return {
        form: {
            full_name: '',
            email: '',
            phone: '',
            property_address: '',
            timeframe: '',
            message: '',
            privacy: false
        },
        loading: false,
        success: false,
        error: false,
        successMessage: '',
        errorMessage: '',
        labels: SELLER_LEAD_LABELS,

        async submitForm() {
            if (!this.form.full_name || !this.form.email || !this.form.phone) {
                this.showError(window.publicT('contact.requiredFields', 'Por favor completa todos los campos requeridos.'));
                return;
            }

            if (!this.form.privacy) {
                this.showError(window.publicT('contact.acceptPrivacy', 'Debes aceptar la politica de privacidad.'));
                return;
            }

            this.loading = true;
            this.success = false;
            this.error = false;

            try {
                const response = await fetch('{{ route('public.sell-with-us.leads.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ...this.form,
                        contact_type: 'seller',
                        ...((window.publicLeadTrackingPayload && window.publicLeadTrackingPayload()) || {}),
                        locale: window.__PUBLIC_LOCALE__ || 'es'
                    })
                });

                const data = await response.json().catch(() => null);

                if (response.ok && data?.success) {
                    this.success = true;
                    this.successMessage = this.labels.success;
                    this.form = {
                        full_name: '',
                        email: '',
                        phone: '',
                        property_address: '',
                        timeframe: '',
                        message: '',
                        privacy: false
                    };
                    return;
                }

                this.showError(data?.message || window.publicT('contact.submitError', 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.'));
            } catch (error) {
                console.error(error);
                this.showError(window.publicT('contact.connectionError', 'Error de conexion. Por favor verifica tu internet e intenta de nuevo.'));
            } finally {
                this.loading = false;
            }
        },

        showError(message) {
            this.error = true;
            this.success = false;
            this.errorMessage = message;
        },

        init() {
            this.$el.addEventListener('submit', (event) => {
                event.preventDefault();
                this.submitForm();
            });
        }
    };
}
</script>
@endpush
