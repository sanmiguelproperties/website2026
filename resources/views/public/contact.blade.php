@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';
    $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
    $pageTitle = $pageData?->entity?->title($locale) ?? ($isEn ? 'Contact' : 'Contacto');

    $phoneDisplay = $settings['contact_phone'] ?? '+52 55 1234 5678';
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?: '+525512345678';

    $whatsRaw = $settings['contact_whatsapp'] ?? '+525512345678';
    $whatsappNumber = preg_replace('/[^0-9]/', '', (string) $whatsRaw) ?: '525512345678';

    $email = $settings['contact_email'] ?? 'info@sanmiguelproperties.com';
    $address = $settings['contact_address'] ?? ($isEn ? 'San Miguel de Allende, Guanajuato, Mexico' : 'San Miguel de Allende, Guanajuato, México');

    $siteName = $settings['site_name'] ?? 'San Miguel Properties';

    $socialLinks = [
        ['key' => 'social_facebook', 'label' => 'Facebook'],
        ['key' => 'social_instagram', 'label' => 'Instagram'],
        ['key' => 'social_twitter', 'label' => 'X'],
        ['key' => 'social_linkedin', 'label' => 'LinkedIn'],
        ['key' => 'social_youtube', 'label' => 'YouTube'],
    ];
    $faqRows = $pageData?->repeater('contact_faq_items') ?? [];

    $contactFormLabels = [
        'namePlaceholder' => $txt('contact_form_placeholder_name', 'Tu nombre completo', 'Your full name'),
        'phonePlaceholder' => $txt('contact_form_placeholder_phone', '+52 55 1234 5678', '+1 555 123 4567'),
        'emailPlaceholder' => $txt('contact_form_placeholder_email', 'tu@correo.com', 'you@email.com'),
        'messagePlaceholder' => $txt('contact_form_placeholder_message', 'Cuentanos mas sobre lo que buscas...', 'Tell us what you are looking for...'),
        'submit' => $txt('contact_form_submit', 'Enviar mensaje', 'Send message'),
        'sending' => $txt('contact_form_sending', 'Enviando...', 'Sending...'),
    ];

    $faqItems = !empty($faqRows)
        ? collect($faqRows)
            ->map(fn ($row) => [
                'q' => $row->field('faq_question'),
                'a' => $row->field('faq_answer'),
            ])
            ->filter(fn ($row) => !empty($row['q']) && !empty($row['a']))
            ->values()
            ->all()
        : ($isEn
            ? [
                ['q' => 'How long does the buying process take?', 'a' => 'It usually takes between 30 and 60 days depending on the transaction complexity and documentation readiness.'],
                ['q' => 'Do you offer financing support?', 'a' => 'Yes. We work with partner institutions and help you evaluate the best credit options for your case.'],
                ['q' => 'Can I schedule a virtual tour?', 'a' => 'Yes. We offer virtual tours and live video calls so you can review properties remotely.'],
            ]
            : [
                ['q' => '¿Cuánto tiempo tarda el proceso de compra?', 'a' => 'Generalmente toma entre 30 y 60 días dependiendo de la complejidad de la operación y la disponibilidad documental.'],
                ['q' => '¿Ofrecen apoyo con financiamiento?', 'a' => 'Sí. Trabajamos con instituciones aliadas y te ayudamos a evaluar las mejores opciones de crédito para tu caso.'],
                ['q' => '¿Puedo agendar una visita virtual?', 'a' => 'Sí. Ofrecemos recorridos virtuales y videollamadas en vivo para revisar propiedades de forma remota.'],
            ]);
@endphp

@section('title', $pageTitle)

@section('content')
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--fe-contact-hero_from, #1C1C1C) 0%, var(--fe-contact-hero_via, #D1A054) 50%, var(--fe-contact-hero_to, #768D59) 100%);"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.1)_1px,transparent_0)] [background-size:40px_40px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-white/90 text-sm font-medium mb-6" style="background: rgba(255,255,255,0.1);">
                {{ $pageData?->field('contact_hero_badge') ?? ($isEn ? 'Contact Us' : 'Contáctanos') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
                {{ $pageData?->field('contact_hero_title') ?? ($isEn ? 'We are here to help' : 'Estamos aquí para ayudarte') }}
            </h1>

            <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto">
                {{ $pageData?->field('contact_hero_subtitle') ?? ($isEn ? 'Send us a message and we will get back to you in less than 24 hours.' : 'Envíanos un mensaje y te responderemos en menos de 24 horas.') }}
            </p>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-5 gap-12 lg:gap-16">
        <div class="lg:col-span-2 space-y-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold mb-4" style="color: var(--fe-contact-section_title, #1C1C1C);">
                    {{ $txt('contact_info_title', 'Informacion de contacto', 'Contact information') }}
                </h2>
                <p class="text-lg" style="color: var(--fe-contact-section_text, #475569);">
                    {{ $txt('contact_info_subtitle', 'Elige el canal que prefieras. Te responderemos lo antes posible.', 'Choose your preferred channel. We will respond as soon as possible.') }}
                </p>
            </div>

            <div class="space-y-4">
                <a href="tel:{{ $phoneHref }}" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(to bottom right, var(--fe-contact-phone_icon_from, #768D59), var(--fe-contact-phone_icon_to, #768D59));">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #1C1C1C);">{{ $txt('contact_label_phone', 'Telefono', 'Phone') }}</h3>
                        <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #D1A054);">{{ $phoneDisplay }}</p>
                    </div>
                </a>

                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(to bottom right, var(--fe-contact-whatsapp_icon_from, #22c55e), var(--fe-contact-whatsapp_icon_to, #16a34a));">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #1C1C1C);">{{ $txt('contact_label_whatsapp', 'WhatsApp', 'WhatsApp') }}</h3>
                        <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #22c55e);">{{ $txt('contact_label_chat', 'Chatea con nosotros', 'Chat with us') }}</p>
                    </div>
                </a>

                <a href="mailto:{{ $email }}" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(to bottom right, var(--fe-contact-email_icon_from, #D1A054), var(--fe-contact-email_icon_to, #D1A054));">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #1C1C1C);">{{ $txt('contact_label_email', 'Email', 'Email') }}</h3>
                        <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #D1A054);">{{ $email }}</p>
                    </div>
                </a>
            </div>

            <div>
                <h3 class="font-semibold mb-2" style="color: var(--fe-contact-card_title, #1C1C1C);">{{ $txt('contact_label_address', 'Direccion', 'Address') }}</h3>
                <p style="color: var(--fe-contact-section_text, #475569);">{{ $address }}</p>
            </div>

            <div>
                <h3 class="font-semibold mb-3" style="color: var(--fe-contact-social_title, #1C1C1C);">{{ $txt('contact_label_follow', 'Siguenos', 'Follow us') }}</h3>
                <div class="flex items-center gap-3">
                    @foreach($socialLinks as $social)
                        @php $socialUrl = $settings[$social['key']] ?? null; @endphp
                        @continue(empty($socialUrl))
                        <a href="{{ $socialUrl }}" target="_blank" rel="noopener" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" style="background-color: var(--fe-contact-social_bg, #f1f5f9); color: var(--fe-contact-social_icon, #475569);" aria-label="{{ $social['label'] }}">
                            <span class="text-xs font-semibold">{{ strtoupper(substr($social['label'], 0, 1)) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="rounded-3xl p-8 lg:p-10 border shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-contact-form_bg_from, #ffffff), var(--fe-contact-form_bg_to, #f8fafc)); border-color: var(--fe-contact-form_border, #e2e8f0);">
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold mb-2" style="color: var(--fe-contact-form_title, #1C1C1C);">
                        {{ $txt('contact_form_title', 'Envianos un mensaje', 'Send us a message') }}
                    </h2>
                    <p style="color: var(--fe-contact-form_subtitle, #475569);">
                        {{ $txt('contact_form_subtitle', 'Completa el formulario y nos pondremos en contacto contigo pronto.', 'Complete the form and we will contact you shortly.') }}
                    </p>
                </div>

                <form id="contactForm" class="space-y-6" x-data="contactForm()">
                    <div x-show="success" x-cloak class="p-4 rounded-xl" style="background-color: var(--fe-contact-alert_success_bg, #d1fae5); color: var(--fe-contact-alert_success_text, #065f46);">
                        <span class="font-medium" x-text="successMessage"></span>
                    </div>

                    <div x-show="error" x-cloak class="p-4 rounded-xl" style="background-color: var(--fe-contact-alert_error_bg, #fee2e2); color: var(--fe-contact-alert_error_text, #991b1b);">
                        <span class="font-medium" x-text="errorMessage"></span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('contact_form_label_name', 'Nombre completo', 'Full name') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" x-model="form.name" required class="w-full px-4 py-3.5 rounded-xl focus:outline-none" style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);" :placeholder="labels.namePlaceholder">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('contact_label_phone', 'Telefono', 'Phone') }} <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" x-model="form.phone" required class="w-full px-4 py-3.5 rounded-xl focus:outline-none" style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);" :placeholder="labels.phonePlaceholder">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('contact_form_label_email', 'Correo electronico', 'Email') }} <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" x-model="form.email" required class="w-full px-4 py-3.5 rounded-xl focus:outline-none" style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);" :placeholder="labels.emailPlaceholder">
                    </div>

                    <div>
                        <label for="interest" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('contact_form_label_interest', 'Estoy interesado en', 'I am interested in') }}</label>
                        <select id="interest" name="interest" x-model="form.interest" class="w-full px-4 py-3.5 rounded-xl focus:outline-none" style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);">
                            <option value="">{{ $txt('contact_form_interest_placeholder', 'Selecciona una opcion', 'Select an option') }}</option>
                            <option value="buy">{{ $txt('contact_form_interest_buy', 'Comprar una propiedad', 'Buy a property') }}</option>
                            <option value="rent">{{ $txt('contact_form_interest_rent', 'Rentar una propiedad', 'Rent a property') }}</option>
                            <option value="sell">{{ $txt('contact_form_interest_sell', 'Vender mi propiedad', 'Sell my property') }}</option>
                            <option value="investment">{{ $txt('contact_form_interest_invest', 'Inversion inmobiliaria', 'Real estate investment') }}</option>
                            <option value="other">{{ $txt('contact_form_interest_other', 'Otro', 'Other') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">{{ $txt('contact_form_label_message', 'Mensaje', 'Message') }} <span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" x-model="form.message" rows="5" required class="w-full px-4 py-3.5 rounded-xl resize-none focus:outline-none" style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #1C1C1C);" :placeholder="labels.messagePlaceholder"></textarea>
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="privacy" name="privacy" x-model="form.privacy" required class="mt-1 h-5 w-5 rounded border-slate-300">
                        <label for="privacy" class="text-sm" style="color: var(--fe-contact-privacy_text, #475569);">
                            {{ $txt('contact_form_privacy', 'Acepto la politica de privacidad y autorizo el tratamiento de mis datos personales.', 'I accept the privacy policy and authorize personal data processing.') }}
                        </label>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full px-8 py-4 text-white font-semibold rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:scale-100" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                        <span x-text="loading ? labels.sending : labels.submit"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, var(--fe-contact-faq_bg, #f8fafc), #ffffff);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-contact-faq_title, #1C1C1C);">
                {{ $txt('contact_faq_title', 'Preguntas frecuentes', 'Frequently asked questions') }}
            </h2>
        </div>

        <div class="space-y-4" x-data="{ openFaq: null }">
            @foreach($faqItems as $index => $faq)
                <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                    <button @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between p-6 text-left" :style="openFaq === {{ $index }} ? 'background: linear-gradient(to right, rgba(209,160,84,0.05), rgba(118,141,89,0.05))' : 'background-color: #ffffff'">
                        <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #1C1C1C);">{{ $faq['q'] }}</span>
                        <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #D1A054);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="openFaq === {{ $index }}" x-collapse x-cloak>
                        <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">{{ $faq['a'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                {{ $txt('contact_faq_whatsapp_cta', 'Chatea con nosotros por WhatsApp', 'Chat with us on WhatsApp') }}
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const CONTACT_FORM_LABELS = @json($contactFormLabels);

function contactForm() {
    return {
        form: {
            name: '',
            phone: '',
            email: '',
            interest: '',
            message: '',
            privacy: false
        },
        loading: false,
        success: false,
        error: false,
        errorMessage: '',
        successMessage: '',
        labels: CONTACT_FORM_LABELS,

        async submitForm() {
            if (!this.form.name || !this.form.phone || !this.form.email || !this.form.message) {
                this.error = true;
                this.errorMessage = window.publicT('contact.requiredFields', 'Por favor completa todos los campos requeridos.');
                return;
            }

            if (!this.form.privacy) {
                this.error = true;
                this.errorMessage = window.publicT('contact.acceptPrivacy', 'Debes aceptar la política de privacidad.');
                return;
            }

            this.loading = true;
            this.error = false;
            this.success = false;

            try {
                const response = await fetch('/api/contact-requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: this.form.name,
                        phone: this.form.phone,
                        email: this.form.email,
                        message: this.form.interest ? `[${this.form.interest}] ${this.form.message}` : this.form.message,
                        source: 'website_contact_form',
                        locale: window.__PUBLIC_LOCALE__ || 'es',
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.success = true;
                    this.successMessage = window.publicT('contact.submitSuccess', '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
                    this.form = {
                        name: '',
                        phone: '',
                        email: '',
                        interest: '',
                        message: '',
                        privacy: false
                    };
                    document.getElementById('contactForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    this.error = true;
                    this.errorMessage = data.message || window.publicT('contact.submitError', 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.');
                }
            } catch (err) {
                console.error('Error:', err);
                this.error = true;
                this.errorMessage = window.publicT('contact.connectionError', 'Error de conexión. Por favor verifica tu conexión a internet e intenta de nuevo.');
            } finally {
                this.loading = false;
            }
        },

        init() {
            this.$el.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
    };
}
</script>
@endpush











