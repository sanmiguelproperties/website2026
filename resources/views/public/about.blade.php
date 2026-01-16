@extends('layouts.public')

@section('title', 'Nosotros - San Miguel Properties')

@section('content')
{{-- ============================================== --}}
{{-- HERO - NOSOTROS (PÁGINA) --}}
{{-- ============================================== --}}
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
    {{-- Background con gradiente (usa variables de about_page) --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--fe-about_page-hero_bg_from, #312e81) 0%, rgba(79,70,229,0.95) 45%, var(--fe-about_page-hero_bg_to, #059669) 100%);"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.10)_1px,transparent_0)] [background-size:40px_40px]"></div>
        <div class="absolute top-16 left-10 w-80 h-80 bg-white/5 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-10 right-10 w-[28rem] h-[28rem] bg-emerald-500/10 rounded-full blur-3xl animate-float" style="animation-delay: -3s;"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-white/90 text-sm font-medium mb-6 animate-fade-in" style="background: rgba(255,255,255,0.12);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Quiénes somos
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 animate-slide-up">
                Construimos confianza,
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, rgba(52,211,153,1), rgba(34,211,238,1));">
                    cerramos oportunidades
                </span>
            </h1>

            <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto animate-slide-up" style="animation-delay: 0.1s;">
                Somos un equipo inmobiliario que combina experiencia, datos y acompañamiento humano
                para que comprar, vender o rentar sea un proceso claro, rápido y seguro.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 animate-slide-up" style="animation-delay: 0.2s;">
                <a href="{{ route('public.properties.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-105"
                   style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                    Ver propiedades
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="{{ route('public.contact') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold text-white/95 border border-white/20 backdrop-blur-sm transition-all duration-300 hover:bg-white/20"
                   style="background: rgba(255,255,255,0.10);">
                    Hablar con un asesor
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </a>
            </div>
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
{{-- RESUMEN + MÉTRICAS --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-start">
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6" style="background-color: rgba(79,70,229,0.08); color: var(--fe-primary-from, #4f46e5);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Nuestra promesa
                </div>

                <h2 class="text-3xl sm:text-4xl font-bold mb-6" style="color: var(--fe-about_page-section_title, #1e293b);">
                    Experiencia inmobiliaria moderna,
                    <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                        sin fricciones
                    </span>
                </h2>

                <div class="space-y-4 text-lg" style="color: #475569;">
                    <p>
                        En San Miguel Properties combinamos tecnología con asesoría personalizada. Te ayudamos
                        a comparar opciones, validar documentación, negociar y cerrar con seguridad.
                    </p>
                    <p>
                        Nuestro enfoque es simple: claridad en el proceso, comunicación constante y resultados medibles.
                    </p>
                </div>

                <div class="mt-10 grid sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(248,250,252,1), rgba(255,255,255,1));">
                        <div class="text-3xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">+15</div>
                        <div class="mt-1 text-sm" style="color: #64748b;">Años de experiencia</div>
                    </div>
                    <div class="rounded-2xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(248,250,252,1), rgba(255,255,255,1));">
                        <div class="text-3xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">+1,000</div>
                        <div class="mt-1 text-sm" style="color: #64748b;">Clientes acompañados</div>
                    </div>
                    <div class="rounded-2xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(248,250,252,1), rgba(255,255,255,1));">
                        <div class="text-3xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">98%</div>
                        <div class="mt-1 text-sm" style="color: #64748b;">Satisfacción</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="rounded-3xl overflow-hidden border shadow-soft" style="border-color: rgba(226,232,240,1);">
                    <div class="relative">
                        <img
                            src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=80"
                            alt="Equipo trabajando"
                            class="w-full h-[420px] object-cover"
                        />
                        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(15,23,42,0.75), rgba(15,23,42,0.05));"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-8">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium text-white/90 backdrop-blur-sm" style="background: rgba(255,255,255,0.10);">
                                <span class="inline-flex h-2 w-2 rounded-full" style="background: var(--fe-primary-to, #10b981);"></span>
                                Equipo multidisciplinario
                            </div>
                            <p class="mt-3 text-white text-lg font-semibold">Asesoría, marketing y tecnología</p>
                            <p class="text-white/70">Un solo equipo para todo el proceso</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- VALORES --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, rgba(248,250,252,1), rgba(255,255,255,1));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: rgba(16,185,129,0.10); color: rgba(5,150,105,1);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Nuestra cultura
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                Valores que se sienten en cada operación
            </h2>
            <p class="text-lg" style="color: #475569;">
                Lo importante no es solo cerrar una venta: es hacerlo bien, con transparencia y acompañamiento.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <div class="rounded-3xl p-8 border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-6" style="background: linear-gradient(to bottom right, var(--fe-about_page-value_icon_1, #4f46e5), rgba(129,140,248,1));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: var(--fe-about_page-section_title, #1e293b);">Transparencia</h3>
                <p style="color: #475569;">Información clara, costos definidos y acompañamiento honesto desde el primer día.</p>
            </div>

            <div class="rounded-3xl p-8 border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-6" style="background: linear-gradient(to bottom right, var(--fe-about_page-value_icon_2, #10b981), rgba(34,211,238,1));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: var(--fe-about_page-section_title, #1e293b);">Velocidad con control</h3>
                <p style="color: #475569;">Procesos ágiles sin improvisación: validamos y priorizamos lo que realmente importa.</p>
            </div>

            <div class="rounded-3xl p-8 border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-6" style="background: linear-gradient(to bottom right, var(--fe-about_page-value_icon_3, #f59e0b), rgba(251,146,60,1));">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color: var(--fe-about_page-section_title, #1e293b);">Innovación</h3>
                <p style="color: #475569;">Datos, automatización y marketing digital para tomar mejores decisiones y llegar más lejos.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- TIMELINE / HISTORIA --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16">
            <div class="lg:col-span-5">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                    Nuestra historia
                </h2>
                <p class="text-lg" style="color: #475569;">
                    Hemos evolucionado con el mercado. Hoy trabajamos con procesos y herramientas que elevan la experiencia del cliente.
                </p>

                <div class="mt-8 rounded-3xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(248,250,252,1), rgba(255,255,255,1));">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold" style="color: var(--fe-about_page-section_title, #1e293b);">Metodología</div>
                            <div class="text-sm" style="color: #64748b;">Diagnóstico → Estrategia → Ejecución → Cierre</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="relative pl-6">
                    <div class="absolute left-2 top-0 bottom-0 w-px" style="background-color: var(--fe-about_page-timeline_line, #e2e8f0);"></div>

                    @php
                        $timeline = [
                            ['year' => '2009', 'title' => 'Nacemos con enfoque local', 'desc' => 'Iniciamos acompañando familias y pequeños inversionistas en decisiones clave.'],
                            ['year' => '2016', 'title' => 'Estandarizamos procesos', 'desc' => 'Implementamos checklists, validación documental y mejores prácticas para cerrar con seguridad.'],
                            ['year' => '2021', 'title' => 'Impulso digital', 'desc' => 'Marketing, CRM y medición para acelerar ventas y mejorar la experiencia del cliente.'],
                            ['year' => 'Hoy',  'title' => 'Ecosistema completo', 'desc' => 'Asesoría, tecnología y operación para comprar/vender/rentar con control y claridad.'],
                        ];
                    @endphp

                    @foreach($timeline as $i => $item)
                        <div class="relative pb-10">
                            <div class="absolute -left-[2px] top-1">
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $i === count($timeline) - 1 ? 'var(--fe-about_page-timeline_dot_active, #4f46e5)' : 'var(--fe-about_page-timeline_line, #e2e8f0)' }};"></div>
                            </div>

                            <div class="ml-6 rounded-3xl border p-6 transition-all duration-300 hover:shadow-lg" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="text-sm font-semibold" style="color: var(--fe-primary-from, #4f46e5);">{{ $item['year'] }}</div>
                                    <div class="text-xs font-medium px-3 py-1 rounded-full" style="background: rgba(16,185,129,0.10); color: rgba(5,150,105,1);">
                                        Hito
                                    </div>
                                </div>
                                <h3 class="mt-3 text-xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">{{ $item['title'] }}</h3>
                                <p class="mt-2" style="color: #475569;">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- EQUIPO (cards) --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, rgba(248,250,252,1), rgba(255,255,255,1));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: rgba(147,51,234,0.10); color: rgba(147,51,234,1);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11c1.657 0 3-1.343 3-3S17.657 5 16 5s-3 1.343-3 3 1.343 3 3 3zM8 11c1.657 0 3-1.343 3-3S9.657 5 8 5 5 6.343 5 8s1.343 3 3 3zM8 13c-2.761 0-5 2.239-5 5v1h10v-1c0-2.761-2.239-5-5-5zM16 13c-.29 0-.572.02-.844.06 1.85 1.02 3.094 2.991 3.094 5.23V19h5v-1c0-2.761-2.239-5-5-5z" />
                </svg>
                El equipo
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                Personas reales, resultados reales
            </h2>
            <p class="text-lg" style="color: #475569;">
                Un equipo que entiende tu objetivo y trabaja para lograrlo con criterio, datos y experiencia.
            </p>
        </div>

        @php
            $team = [
                ['name' => 'Laura Martínez', 'role' => 'Dirección Comercial', 'img' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80'],
                ['name' => 'Diego Herrera', 'role' => 'Asesor Inmobiliario', 'img' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=900&q=80'],
                ['name' => 'Sofía Ramírez', 'role' => 'Marketing & Contenido', 'img' => 'https://images.unsplash.com/photo-1524502397800-2eeaad7c3fe5?auto=format&fit=crop&w=900&q=80'],
                ['name' => 'Andrés Silva', 'role' => 'Operaciones & Cierres', 'img' => 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=900&q=80'],
            ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($team as $person)
                <div class="group rounded-3xl overflow-hidden border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: #fff;">
                    <div class="relative h-60 overflow-hidden">
                        <img src="{{ $person['img'] }}" alt="{{ $person['name'] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="background: linear-gradient(to top, rgba(15,23,42,0.70), transparent);"></div>
                    </div>
                    <div class="p-6">
                        <div class="text-lg font-bold" style="color: var(--fe-about_page-team_name, #1e293b);">{{ $person['name'] }}</div>
                        <div class="text-sm" style="color: var(--fe-about_page-team_role, #64748b);">{{ $person['role'] }}</div>

                        <div class="mt-5 flex items-center gap-2">
                            <a href="{{ route('public.contact') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:shadow-lg" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                                Contactar
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                            <a href="tel:+525512345678" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border transition-colors" style="border-color: rgba(226,232,240,1); color: rgba(71,85,105,1);" aria-label="Llamar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- CTA FINAL --}}
{{-- ============================================== --}}
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border p-10 lg:p-14" style="border-color: rgba(226,232,240,1); background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(16,185,129,0.10));">
            <div class="absolute -top-10 -right-10 w-56 h-56 rounded-full blur-3xl" style="background: rgba(79,70,229,0.20);"></div>
            <div class="absolute -bottom-10 -left-10 w-64 h-64 rounded-full blur-3xl" style="background: rgba(16,185,129,0.20);"></div>

            <div class="relative z-10 grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8">
                    <h2 class="text-3xl sm:text-4xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">
                        ¿Hablamos de tu próxima propiedad?
                    </h2>
                    <p class="mt-3 text-lg" style="color: #475569;">
                        Cuéntanos qué buscas y te compartimos opciones reales, con contexto y recomendaciones.
                    </p>
                </div>
                <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 justify-end">
                    <a href="{{ route('public.contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #4f46e5), var(--fe-primary-to, #10b981));">
                        Ir a contacto
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="{{ route('public.properties.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold border transition-colors" style="border-color: rgba(226,232,240,1); color: rgba(30,41,59,1); background: rgba(255,255,255,0.7);">
                        Explorar catálogo
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

