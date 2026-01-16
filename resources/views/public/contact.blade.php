@extends('layouts.public')

@section('title', 'Contacto - San Miguel Properties')

@section('content')
{{-- ============================================== --}}
{{-- HERO SECTION - CONTACTO --}}
{{-- ============================================== --}}
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
    {{-- Background con gradiente --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--fe-contact-hero_from, #312e81) 0%, var(--fe-contact-hero_via, #4f46e5) 50%, var(--fe-contact-hero_to, #059669) 100%);"></div>
        {{-- Patrón decorativo --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.1)_1px,transparent_0)] [background-size:40px_40px]"></div>
        {{-- Elementos decorativos flotantes --}}
        <div class="absolute top-20 left-10 w-72 h-72 bg-white/5 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl animate-float" style="animation-delay: -3s;"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-white/90 text-sm font-medium mb-6 animate-fade-in" style="background: rgba(255,255,255,0.1);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Estamos aquí para ayudarte
            </div>

            {{-- Título principal --}}
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 animate-slide-up">
                Ponte en
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-contact-title_from, #34d399), var(--fe-contact-title_to, #22d3ee));">
                    contacto
                </span>
            </h1>

            {{-- Subtítulo --}}
            <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto animate-slide-up" style="animation-delay: 0.1s;">
                ¿Tienes preguntas sobre nuestras propiedades? ¿Necesitas asesoría personalizada? 
                Nuestro equipo de expertos está listo para atenderte.
            </p>
        </div>
    </div>

    {{-- Wave decorativo --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg class="w-full h-16 sm:h-24" viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 100V50C240 0 480 0 720 50C960 100 1200 100 1440 50V100H0Z" fill="white"/>
        </svg>
    </div>
</section>

{{-- ============================================== --}}
{{-- INFORMACIÓN DE CONTACTO + FORMULARIO --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">
            {{-- Columna de información de contacto --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Título de sección --}}
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold mb-4" style="color: var(--fe-contact-section_title, #0f172a);">
                        Información de contacto
                    </h2>
                    <p class="text-lg" style="color: var(--fe-contact-section_text, #475569);">
                        Elige el canal que prefieras para comunicarte con nosotros. Respondemos en menos de 24 horas.
                    </p>
                </div>

                {{-- Tarjetas de contacto --}}
                <div class="space-y-4">
                    {{-- Teléfono --}}
                    <a href="tel:+525512345678" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg hover:border-transparent" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-contact-phone_icon_from, #10b981), var(--fe-contact-phone_icon_to, #059669));">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #0f172a);">Teléfono</h3>
                            <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #4f46e5);">+52 55 1234 5678</p>
                            <p class="text-sm mt-1" style="color: var(--fe-contact-card_desc, #64748b);">Lun - Vie: 9:00 AM - 7:00 PM</p>
                        </div>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="https://wa.me/525512345678" target="_blank" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg hover:border-transparent" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-contact-whatsapp_icon_from, #22c55e), var(--fe-contact-whatsapp_icon_to, #16a34a));">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #0f172a);">WhatsApp</h3>
                            <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #22c55e);">Chatea con nosotros</p>
                            <p class="text-sm mt-1" style="color: var(--fe-contact-card_desc, #64748b);">Respuesta inmediata</p>
                        </div>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:info@sanmiguelproperties.com" class="group flex items-start gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-lg hover:border-transparent" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0 group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(to bottom right, var(--fe-contact-email_icon_from, #6366f1), var(--fe-contact-email_icon_to, #4f46e5));">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #0f172a);">Email</h3>
                            <p class="text-lg font-medium" style="color: var(--fe-contact-card_value, #6366f1);">info@sanmiguelproperties.com</p>
                            <p class="text-sm mt-1" style="color: var(--fe-contact-card_desc, #64748b);">Respuesta en 24 horas</p>
                        </div>
                    </a>

                    {{-- Ubicación --}}
                    <div class="flex items-start gap-4 p-5 rounded-2xl border" style="background: linear-gradient(to bottom right, var(--fe-contact-card_bg_from, #f8fafc), var(--fe-contact-card_bg_to, #ffffff)); border-color: var(--fe-contact-card_border, #e2e8f0);">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(to bottom right, var(--fe-contact-location_icon_from, #f59e0b), var(--fe-contact-location_icon_to, #d97706));">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1" style="color: var(--fe-contact-card_title, #0f172a);">Oficina Principal</h3>
                            <p class="font-medium" style="color: var(--fe-contact-card_value, #0f172a);">Av. Principal #123</p>
                            <p class="text-sm" style="color: var(--fe-contact-card_desc, #64748b);">Col. Centro, Ciudad de México, CP 06000</p>
                        </div>
                    </div>
                </div>

                {{-- Redes sociales --}}
                <div>
                    <h3 class="font-semibold mb-4" style="color: var(--fe-contact-social_title, #0f172a);">Síguenos en redes sociales</h3>
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" style="background-color: var(--fe-contact-social_bg, #f1f5f9); color: var(--fe-contact-social_icon, #475569);" aria-label="Facebook">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" style="background-color: var(--fe-contact-social_bg, #f1f5f9); color: var(--fe-contact-social_icon, #475569);" aria-label="Instagram">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" style="background-color: var(--fe-contact-social_bg, #f1f5f9); color: var(--fe-contact-social_icon, #475569);" aria-label="Twitter">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" style="background-color: var(--fe-contact-social_bg, #f1f5f9); color: var(--fe-contact-social_icon, #475569);" aria-label="LinkedIn">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Columna del formulario --}}
            <div class="lg:col-span-3">
                <div class="rounded-3xl p-8 lg:p-10 border shadow-xl" style="background: linear-gradient(to bottom right, var(--fe-contact-form_bg_from, #ffffff), var(--fe-contact-form_bg_to, #f8fafc)); border-color: var(--fe-contact-form_border, #e2e8f0);">
                    {{-- Encabezado del formulario --}}
                    <div class="mb-8">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2" style="color: var(--fe-contact-form_title, #0f172a);">
                            Envíanos un mensaje
                        </h2>
                        <p style="color: var(--fe-contact-form_subtitle, #475569);">
                            Completa el formulario y nos pondremos en contacto contigo lo antes posible.
                        </p>
                    </div>

                    {{-- Formulario --}}
                    <form id="contactForm" class="space-y-6" x-data="contactForm()">
                        {{-- Alerta de éxito --}}
                        <div x-show="success" x-cloak
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="p-4 rounded-xl flex items-center gap-3" style="background-color: var(--fe-contact-alert_success_bg, #d1fae5); color: var(--fe-contact-alert_success_text, #065f46);">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.</span>
                        </div>

                        {{-- Alerta de error --}}
                        <div x-show="error" x-cloak
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="p-4 rounded-xl flex items-center gap-3" style="background-color: var(--fe-contact-alert_error_bg, #fee2e2); color: var(--fe-contact-alert_error_text, #991b1b);">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium" x-text="errorMessage"></span>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">
                                    Nombre completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" x-model="form.name" required
                                       class="w-full px-4 py-3.5 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2"
                                       style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #0f172a); --tw-ring-color: var(--fe-contact-input_focus, rgba(99,102,241,0.3));"
                                       placeholder="Tu nombre completo">
                            </div>

                            {{-- Teléfono --}}
                            <div>
                                <label for="phone" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">
                                    Teléfono <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone" x-model="form.phone" required
                                       class="w-full px-4 py-3.5 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2"
                                       style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #0f172a); --tw-ring-color: var(--fe-contact-input_focus, rgba(99,102,241,0.3));"
                                       placeholder="+52 55 1234 5678">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">
                                Correo electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" x-model="form.email" required
                                   class="w-full px-4 py-3.5 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2"
                                   style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #0f172a); --tw-ring-color: var(--fe-contact-input_focus, rgba(99,102,241,0.3));"
                                   placeholder="tu@correo.com">
                        </div>

                        {{-- Interés --}}
                        <div>
                            <label for="interest" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">
                                ¿En qué estás interesado?
                            </label>
                            <select id="interest" name="interest" x-model="form.interest"
                                    class="w-full px-4 py-3.5 rounded-xl transition-all duration-200 appearance-none cursor-pointer focus:outline-none focus:ring-2"
                                    style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #0f172a); --tw-ring-color: var(--fe-contact-input_focus, rgba(99,102,241,0.3));">
                                <option value="">Selecciona una opción</option>
                                <option value="comprar">Comprar una propiedad</option>
                                <option value="rentar">Rentar una propiedad</option>
                                <option value="vender">Vender mi propiedad</option>
                                <option value="inversion">Invertir en bienes raíces</option>
                                <option value="asesoria">Asesoría inmobiliaria</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        {{-- Mensaje --}}
                        <div>
                            <label for="message" class="block text-sm font-semibold mb-2" style="color: var(--fe-contact-label, #334155);">
                                Mensaje <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message" name="message" x-model="form.message" rows="5" required
                                      class="w-full px-4 py-3.5 rounded-xl transition-all duration-200 resize-none focus:outline-none focus:ring-2"
                                      style="background-color: var(--fe-contact-input_bg, #f8fafc); border: 1px solid var(--fe-contact-input_border, #e2e8f0); color: var(--fe-contact-input_text, #0f172a); --tw-ring-color: var(--fe-contact-input_focus, rgba(99,102,241,0.3));"
                                      placeholder="Cuéntanos más sobre lo que buscas o necesitas..."></textarea>
                        </div>

                        {{-- Checkbox de privacidad --}}
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="privacy" name="privacy" x-model="form.privacy" required
                                   class="mt-1 h-5 w-5 rounded border-slate-300 focus:ring-2"
                                   style="color: var(--fe-primary-from, #4f46e5);">
                            <label for="privacy" class="text-sm" style="color: var(--fe-contact-privacy_text, #475569);">
                                Acepto la <a href="#" style="color: var(--fe-contact-link, #4f46e5);" class="font-medium hover:underline">política de privacidad</a> y autorizo el tratamiento de mis datos personales.
                            </label>
                        </div>

                        {{-- Botón de envío --}}
                        <button type="submit"
                                :disabled="loading"
                                class="w-full px-8 py-4 text-white font-semibold rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center gap-3"
                                style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                            <template x-if="!loading">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </template>
                            <template x-if="loading">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="loading ? 'Enviando...' : 'Enviar mensaje'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- MAPA DE UBICACIÓN --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, var(--fe-contact-map_section_from, #f8fafc), var(--fe-contact-map_section_to, #ffffff));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Encabezado --}}
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: var(--fe-contact-map_badge_bg, #fef3c7); color: var(--fe-contact-map_badge_text, #d97706);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Nuestra ubicación
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-contact-map_title, #0f172a);">
                Visítanos en nuestra oficina
            </h2>
            <p class="text-lg" style="color: var(--fe-contact-map_subtitle, #475569);">
                Estamos ubicados en el corazón de la ciudad. ¡Te esperamos!
            </p>
        </div>

        {{-- Mapa --}}
        <div class="rounded-3xl overflow-hidden shadow-2xl border" style="border-color: var(--fe-contact-map_border, #e2e8f0);">
            <div class="relative h-[400px] lg:h-[500px]">
                {{-- Placeholder del mapa - En producción usar Google Maps o similar --}}
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3762.661913904578!2d-99.16869032394531!3d19.427023481862!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1ff35f5bd1563%3A0x6c366f0e2de02ff7!2sZocalo%2C%20Centro%20Hist%C3%B3rico%20de%20la%20Cdad.%20de%20M%C3%A9xico%2C%20Centro%2C%20Cuauht%C3%A9moc%2C%2006000%20Ciudad%20de%20M%C3%A9xico%2C%20CDMX!5e0!3m2!1ses!2smx!4v1705369200000!5m2!1ses!2smx"
                    class="w-full h-full"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                
                {{-- Overlay con información --}}
                <div class="absolute bottom-6 left-6 right-6 sm:right-auto sm:max-w-sm">
                    <div class="rounded-2xl p-6 shadow-xl backdrop-blur-sm" style="background: rgba(255,255,255,0.95);">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(to bottom right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1" style="color: var(--fe-contact-map_card_title, #0f172a);">San Miguel Properties</h3>
                                <p class="text-sm mb-3" style="color: var(--fe-contact-map_card_text, #475569);">
                                    Av. Principal #123, Col. Centro<br>
                                    Ciudad de México, CP 06000
                                </p>
                                <a href="https://maps.google.com" target="_blank" 
                                   class="inline-flex items-center gap-2 text-sm font-semibold transition-colors"
                                   style="color: var(--fe-contact-link, #4f46e5);">
                                    Cómo llegar
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- FAQ - PREGUNTAS FRECUENTES --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Encabezado --}}
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: var(--fe-contact-faq_badge_bg, #e0e7ff); color: var(--fe-contact-faq_badge_text, #4f46e5);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Preguntas frecuentes
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-contact-faq_title, #0f172a);">
                ¿Tienes dudas?
            </h2>
            <p class="text-lg" style="color: var(--fe-contact-faq_subtitle, #475569);">
                Aquí encontrarás respuestas a las preguntas más comunes.
            </p>
        </div>

        {{-- Acordeón de FAQs --}}
        <div class="space-y-4" x-data="{ openFaq: null }">
            {{-- FAQ 1 --}}
            <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                <button @click="openFaq = openFaq === 1 ? null : 1"
                        class="w-full flex items-center justify-between p-6 text-left transition-colors"
                        :style="openFaq === 1 ? 'background: linear-gradient(to right, rgba(79,70,229,0.05), rgba(16,185,129,0.05))' : 'background-color: var(--fe-contact-faq_bg, #ffffff)'">
                    <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #0f172a);">
                        ¿Cuánto tiempo tarda el proceso de compra?
                    </span>
                    <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openFaq === 1" x-collapse x-cloak>
                    <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">
                        El proceso de compra generalmente toma entre 30 y 60 días, dependiendo de la complejidad de la transacción y la disponibilidad de documentos. Nuestro equipo te acompaña en cada paso para hacer el proceso lo más ágil posible.
                    </div>
                </div>
            </div>

            {{-- FAQ 2 --}}
            <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                <button @click="openFaq = openFaq === 2 ? null : 2"
                        class="w-full flex items-center justify-between p-6 text-left transition-colors"
                        :style="openFaq === 2 ? 'background: linear-gradient(to right, rgba(79,70,229,0.05), rgba(16,185,129,0.05))' : 'background-color: var(--fe-contact-faq_bg, #ffffff)'">
                    <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #0f172a);">
                        ¿Ofrecen financiamiento?
                    </span>
                    <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openFaq === 2" x-collapse x-cloak>
                    <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">
                        Sí, trabajamos con las principales instituciones financieras del país para ofrecerte las mejores opciones de crédito hipotecario. Nuestros asesores te ayudarán a encontrar el plan que mejor se adapte a tu situación financiera.
                    </div>
                </div>
            </div>

            {{-- FAQ 3 --}}
            <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                <button @click="openFaq = openFaq === 3 ? null : 3"
                        class="w-full flex items-center justify-between p-6 text-left transition-colors"
                        :style="openFaq === 3 ? 'background: linear-gradient(to right, rgba(79,70,229,0.05), rgba(16,185,129,0.05))' : 'background-color: var(--fe-contact-faq_bg, #ffffff)'">
                    <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #0f172a);">
                        ¿Puedo agendar una visita virtual?
                    </span>
                    <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openFaq === 3" x-collapse x-cloak>
                    <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">
                        ¡Por supuesto! Ofrecemos tours virtuales 360° para que puedas conocer las propiedades desde la comodidad de tu hogar. También podemos realizar videollamadas en vivo para mostrarte los espacios en tiempo real.
                    </div>
                </div>
            </div>

            {{-- FAQ 4 --}}
            <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                <button @click="openFaq = openFaq === 4 ? null : 4"
                        class="w-full flex items-center justify-between p-6 text-left transition-colors"
                        :style="openFaq === 4 ? 'background: linear-gradient(to right, rgba(79,70,229,0.05), rgba(16,185,129,0.05))' : 'background-color: var(--fe-contact-faq_bg, #ffffff)'">
                    <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #0f172a);">
                        ¿Qué documentos necesito para rentar?
                    </span>
                    <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openFaq === 4" x-collapse x-cloak>
                    <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">
                        Para rentar necesitas: identificación oficial vigente, comprobante de ingresos (últimos 3 recibos de nómina o estados de cuenta), comprobante de domicilio y referencias personales. En algunos casos, podemos ofrecer opciones sin aval.
                    </div>
                </div>
            </div>

            {{-- FAQ 5 --}}
            <div class="rounded-2xl border overflow-hidden" style="border-color: var(--fe-contact-faq_border, #e2e8f0);">
                <button @click="openFaq = openFaq === 5 ? null : 5"
                        class="w-full flex items-center justify-between p-6 text-left transition-colors"
                        :style="openFaq === 5 ? 'background: linear-gradient(to right, rgba(79,70,229,0.05), rgba(16,185,129,0.05))' : 'background-color: var(--fe-contact-faq_bg, #ffffff)'">
                    <span class="font-semibold text-lg pr-4" style="color: var(--fe-contact-faq_question, #0f172a);">
                        ¿Cobran comisión por sus servicios?
                    </span>
                    <svg class="w-6 h-6 flex-shrink-0 transition-transform duration-300" :class="{ 'rotate-180': openFaq === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-contact-faq_icon, #4f46e5);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openFaq === 5" x-collapse x-cloak>
                    <div class="px-6 pb-6" style="color: var(--fe-contact-faq_answer, #475569);">
                        Nuestros servicios de asesoría para compradores son completamente gratuitos. La comisión es cubierta por el vendedor de la propiedad. Para servicios de venta o renta de tu propiedad, te explicamos las condiciones de manera transparente desde el inicio.
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA adicional --}}
        <div class="text-center mt-12">
            <p class="mb-4" style="color: var(--fe-contact-faq_cta_text, #475569);">
                ¿No encontraste lo que buscabas?
            </p>
            <a href="https://wa.me/525512345678" target="_blank"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg hover:scale-105"
               style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Chatea con nosotros
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
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

        async submitForm() {
            // Validación básica
            if (!this.form.name || !this.form.phone || !this.form.email || !this.form.message) {
                this.error = true;
                this.errorMessage = 'Por favor completa todos los campos requeridos.';
                return;
            }

            if (!this.form.privacy) {
                this.error = true;
                this.errorMessage = 'Debes aceptar la política de privacidad.';
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
                        source: 'website_contact_form'
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.success = true;
                    // Limpiar formulario
                    this.form = {
                        name: '',
                        phone: '',
                        email: '',
                        interest: '',
                        message: '',
                        privacy: false
                    };
                    // Scroll al mensaje de éxito
                    document.getElementById('contactForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    this.error = true;
                    this.errorMessage = data.message || 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.';
                }
            } catch (err) {
                console.error('Error:', err);
                this.error = true;
                this.errorMessage = 'Error de conexión. Por favor verifica tu conexión a internet e intenta de nuevo.';
            } finally {
                this.loading = false;
            }
        },

        init() {
            // Agregar listener al formulario
            this.$el.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
    };
}
</script>
@endpush
