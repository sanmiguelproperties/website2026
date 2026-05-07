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

@section('content')
<div class="pt-24 bg-white">
    <section class="relative min-h-[calc(100vh-6rem)] overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ $heroImage }}" alt="{{ $pageTitle }}" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-slate-950/70"></div>
            <div class="absolute inset-0" style="background: linear-gradient(110deg, rgba(15,23,42,.88) 0%, rgba(15,23,42,.70) 45%, rgba(118,141,89,.42) 100%);"></div>
        </div>

        <div class="relative mx-auto grid min-h-[calc(100vh-6rem)] max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8">
            <div class="max-w-3xl">
                <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white/90 backdrop-blur">
                    {{ $txt('seller_hero_badge', 'Vendedores', 'Sellers') }}
                </span>
                <h1 class="mt-6 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                    {{ $txt('seller_hero_title', 'Vende tu propiedad con estrategia local', 'Sell your home with local strategy') }}
                </h1>
                <div class="mt-6 max-w-2xl text-lg leading-relaxed text-white/82 rich-content">
                    {!! $rich($pageData?->field('seller_hero_subtitle'), $isEn
                        ? 'Selling your home in San Miguel de Allende is a major decision, and choosing the right representation matters.'
                        : 'Vender tu propiedad en San Miguel de Allende es una decision importante, y contar con la representacion adecuada hace toda la diferencia.') !!}
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#sellerLeadForm" class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, #D1A054, #768D59);">
                        {{ $txt('seller_hero_form_cta', 'Solicitar asesoria', 'Request a consultation') }}
                    </a>
                    @if($guideUrl !== '')
                        <a href="{{ $guideUrl }}" download class="inline-flex items-center justify-center rounded-lg border border-white/35 bg-white/10 px-6 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/18">
                            {{ $txt('seller_guide_button', 'Descargar manual', 'Download guide') }}
                        </a>
                    @endif
                </div>
            </div>

            <div id="sellerLeadForm" class="rounded-xl border border-white/18 bg-white p-6 shadow-2xl sm:p-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide" style="color: #9a7035;">
                        {{ $txt('seller_form_badge', 'Captura de datos', 'Lead capture') }}
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">
                        {{ $txt('seller_form_title', 'Cuéntanos sobre tu propiedad', 'Tell us about your property') }}
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        {{ $txt('seller_form_subtitle', 'Completa el formulario y un broker de San Miguel Properties te contactara.', 'Complete the form and a San Miguel Properties broker will contact you.') }}
                    </p>
                </div>

                <form id="sellerForm" class="mt-6 space-y-4" x-data="sellerLeadForm()">
                    <div x-show="success" x-cloak class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" x-text="successMessage"></div>
                    <div x-show="error" x-cloak class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" x-text="errorMessage"></div>

                    <div>
                        <label for="seller-full-name" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'Full name' : 'Nombre completo' }} *</label>
                        <input id="seller-full-name" type="text" x-model="form.full_name" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20" autocomplete="name">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="seller-email" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'Email' : 'Correo' }} *</label>
                            <input id="seller-email" type="email" x-model="form.email" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20" autocomplete="email">
                        </div>
                        <div>
                            <label for="seller-phone" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'Phone' : 'Telefono' }} *</label>
                            <input id="seller-phone" type="tel" x-model="form.phone" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20" autocomplete="tel">
                        </div>
                    </div>

                    <div>
                        <label for="seller-property-address" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'Property address or neighborhood' : 'Direccion o colonia de la propiedad' }}</label>
                        <input id="seller-property-address" type="text" x-model="form.property_address" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20">
                    </div>

                    <div>
                        <label for="seller-timeframe" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'When would you like to sell?' : 'Cuando quieres vender?' }}</label>
                        <select id="seller-timeframe" x-model="form.timeframe" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20">
                            <option value="">{{ $isEn ? 'Select an option' : 'Selecciona una opcion' }}</option>
                            <option value="now">{{ $isEn ? 'As soon as possible' : 'Lo antes posible' }}</option>
                            <option value="1-3 months">{{ $isEn ? 'In 1 to 3 months' : 'En 1 a 3 meses' }}</option>
                            <option value="3-6 months">{{ $isEn ? 'In 3 to 6 months' : 'En 3 a 6 meses' }}</option>
                            <option value="exploring">{{ $isEn ? 'I am exploring options' : 'Estoy explorando opciones' }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="seller-message" class="mb-1 block text-sm font-semibold text-slate-700">{{ $isEn ? 'Message' : 'Mensaje' }}</label>
                        <textarea id="seller-message" x-model="form.message" rows="4" class="w-full resize-none rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 outline-none transition focus:border-[#D1A054] focus:ring-2 focus:ring-[#D1A054]/20"></textarea>
                    </div>

                    <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-600">
                        <input type="checkbox" x-model="form.privacy" required class="mt-1 h-4 w-4 rounded border-slate-300" style="accent-color: #768D59;">
                        <span>{{ $txt('seller_form_privacy', 'Acepto que San Miguel Properties me contacte sobre mi solicitud.', 'I agree that San Miguel Properties may contact me about my request.') }}</span>
                    </label>

                    <button type="submit" :disabled="loading" class="w-full rounded-lg px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0" style="background: linear-gradient(135deg, #D1A054, #768D59);">
                        <span x-text="loading ? labels.sending : labels.submit"></span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[.85fr_1.15fr] lg:items-start">
                <div class="sticky top-28 hidden lg:block">
                    <p class="text-sm font-semibold uppercase tracking-wide" style="color: #9a7035;">San Miguel Properties</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-950">
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
                        <article class="border-l-4 pl-6" style="border-color: #D1A054;">
                            <div class="rich-content text-lg leading-relaxed text-slate-700">
                                {!! $rich($pageData?->field($block[0]), $block[1]) !!}
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-14" style="background-color: #0f172a;">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide" style="color: #D1A054;">
                    {{ $txt('seller_guide_eyebrow', 'Guia para vendedores', 'Seller guide') }}
                </p>
                <h2 class="mt-3 text-3xl font-black text-white">
                    {{ $txt('seller_guide_title', 'Descarga el manual para vendedores', 'Download the seller guide') }}
                </h2>
                <p class="mt-3 max-w-3xl text-base leading-relaxed text-white/75">
                    {{ $txt('seller_guide_text', 'Consulta el material preparado por el equipo para entender los pasos clave antes de vender tu propiedad.', 'Review the material prepared by our team to understand the key steps before selling your property.') }}
                </p>
            </div>

            @if($guideUrl !== '')
                <a href="{{ $guideUrl }}" download class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, #D1A054, #768D59);">
                    {{ $txt('seller_guide_button', 'Descargar manual', 'Download guide') }}
                </a>
            @else
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-white/20 px-6 py-3 text-sm font-bold text-white/70">
                    {{ $txt('seller_guide_pending_button', 'Manual pendiente', 'Guide pending') }}
                </button>
            @endif
        </div>
    </section>

    @if(!empty($testimonials))
        <section class="py-16 lg:py-20" style="background-color: #f8fafc;">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h2 class="text-3xl font-black text-slate-950">
                        {{ $txt('seller_testimonials_title', 'Testimoniales', 'Testimonials') }}
                    </h2>
                    <p class="mt-3 text-slate-600">
                        {{ $txt('seller_testimonials_intro', 'Opiniones agregadas manualmente desde el CMS.', 'Testimonials added manually from the CMS.') }}
                    </p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($testimonials as $testimonial)
                        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-base leading-relaxed text-slate-700">"{{ $testimonial['quote'] }}"</p>
                            @if($testimonial['name'] !== '' || $testimonial['context'] !== '')
                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    @if($testimonial['name'] !== '')
                                        <p class="font-bold text-slate-950">{{ $testimonial['name'] }}</p>
                                    @endif
                                    @if($testimonial['context'] !== '')
                                        <p class="text-sm text-slate-500">{{ $testimonial['context'] }}</p>
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
