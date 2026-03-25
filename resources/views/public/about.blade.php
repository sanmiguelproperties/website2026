@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';
    $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
    $pageTitle = $pageData?->entity?->title($locale) ?? ($isEn ? 'About Us' : 'Nosotros');

    $aboutValues = $pageData?->repeater('about_values_items') ?? [];
    $aboutTimeline = $pageData?->repeater('about_timeline_items') ?? [];
    $aboutTeam = $pageData?->repeater('about_team_members') ?? [];

    $contactPhone = $settings['contact_phone'] ?? '+52 55 1234 5678';
    $contactPhoneHref = preg_replace('/[^0-9+]/', '', $contactPhone) ?: '+525512345678';

    $fallbackValues = [
        [
            'title' => $isEn ? 'Transparency' : 'Transparencia',
            'description' => $isEn
                ? 'Clear information, defined costs and honest support from day one.'
                : 'Información clara, costos definidos y acompañamiento honesto desde el primer día.',
        ],
        [
            'title' => $isEn ? 'Speed with control' : 'Velocidad con control',
            'description' => $isEn
                ? 'Agile processes without improvisation: we validate what matters most.'
                : 'Procesos ágiles sin improvisación: validamos lo que realmente importa.',
        ],
        [
            'title' => $isEn ? 'Innovation' : 'Innovación',
            'description' => $isEn
                ? 'Data, automation and digital marketing to make better decisions.'
                : 'Datos, automatización y marketing digital para tomar mejores decisiones.',
        ],
    ];

    $fallbackTimeline = [
        [
            'year' => '2009',
            'title' => $isEn ? 'Local beginnings' : 'Nacemos con enfoque local',
            'description' => $isEn
                ? 'We started accompanying families and small investors.'
                : 'Iniciamos acompañando familias y pequeños inversionistas.',
        ],
        [
            'year' => '2016',
            'title' => $isEn ? 'Standardized process' : 'Estandarizamos procesos',
            'description' => $isEn
                ? 'We implemented checklists and document validation.'
                : 'Implementamos checklists y validación documental.',
        ],
        [
            'year' => '2021',
            'title' => $isEn ? 'Digital boost' : 'Impulso digital',
            'description' => $isEn
                ? 'CRM, analytics and marketing to improve experience.'
                : 'CRM, analítica y marketing para mejorar la experiencia.',
        ],
    ];
@endphp

@section('title', $pageTitle)

@section('content')
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--fe-about_page-hero_bg_from, #1C1C1C) 0%, rgba(209,160,84,0.95) 45%, var(--fe-about_page-hero_bg_to, #768D59) 100%);"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.10)_1px,transparent_0)] [background-size:40px_40px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-sm text-white/90 text-sm font-medium mb-6" style="background: rgba(255,255,255,0.12);">
                {{ $pageData?->field('about_hero_badge') ?? ($isEn ? 'Who we are' : 'Quiénes somos') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
                {{ $pageData?->field('about_hero_title') ?? ($isEn ? 'We build trust,' : 'Construimos confianza,') }}
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, rgba(52,211,153,1), rgba(34,211,238,1));">
                    {{ $pageData?->field('about_hero_title_highlight') ?? ($isEn ? 'we close opportunities' : 'cerramos oportunidades') }}
                </span>
            </h1>

            <p class="text-lg sm:text-xl text-white/80">
                {{ $pageData?->field('about_hero_subtitle') ?? ($isEn ? 'A real estate team that combines experience, data and human support.' : 'Un equipo inmobiliario que combina experiencia, datos y acompañamiento humano.') }}
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('public.properties.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                    {{ $txt('about_hero_cta_primary', 'Ver propiedades', 'View Properties') }}
                </a>
                <a href="{{ route('public.contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold text-white/95 border border-white/20 backdrop-blur-sm transition-all duration-300 hover:bg-white/20" style="background: rgba(255,255,255,0.10);">
                    {{ $txt('about_hero_cta_secondary', 'Hablar con un asesor', 'Talk to an advisor') }}
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-16">
        <div class="lg:col-span-7">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6" style="background-color: rgba(209,160,84,0.08); color: var(--fe-primary-from, #D1A054);">
                {{ $pageData?->field('about_summary_badge') ?? ($isEn ? 'Our promise' : 'Nuestra promesa') }}
            </div>

            <h2 class="text-3xl sm:text-4xl font-bold mb-6" style="color: var(--fe-about_page-section_title, #1e293b);">
                {{ $pageData?->field('about_summary_title') ?? ($isEn ? 'Modern real estate experience,' : 'Experiencia inmobiliaria moderna,') }}
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                    {{ $pageData?->field('about_summary_title_highlight') ?? ($isEn ? 'frictionless' : 'sin fricciones') }}
                </span>
            </h2>

            <div class="space-y-4 text-lg" style="color: #475569;">
                <p>{{ $pageData?->field('about_summary_text1') ?? ($isEn ? 'We combine technology with personalized advice.' : 'Combinamos tecnología con asesoría personalizada.') }}</p>
                <p>{{ $pageData?->field('about_summary_text2') ?? ($isEn ? 'Our approach is clear process, communication and measurable results.' : 'Nuestro enfoque es claridad en el proceso, comunicación y resultados medibles.') }}</p>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-3xl overflow-hidden border shadow-soft" style="border-color: rgba(226,232,240,1);">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=80" alt="{{ $txt('about_summary_team_image_alt', 'Equipo', 'Team') }}" class="w-full h-[420px] object-cover" />
            </div>
            <div class="mt-6 rounded-2xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(248,250,252,1), rgba(255,255,255,1));">
                <div class="text-sm" style="color: #64748b;">{{ $txt('about_direct_line_label', 'Línea directa', 'Direct line') }}</div>
                <a href="tel:{{ $contactPhoneHref }}" class="font-semibold hover:underline" style="color: var(--fe-about_page-section_title, #1e293b);">{{ $contactPhone }}</a>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, rgba(248,250,252,1), rgba(255,255,255,1));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: rgba(118,141,89,0.10); color: rgba(5,150,105,1);">
                {{ $pageData?->field('about_values_badge') ?? ($isEn ? 'Our culture' : 'Nuestra cultura') }}
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                {{ $pageData?->field('about_values_title') ?? ($isEn ? 'Values felt in every operation' : 'Valores que se sienten en cada operación') }}
            </h2>
            <p class="text-lg" style="color: #475569;">
                {{ $pageData?->field('about_values_subtitle') ?? ($isEn ? 'Closing is not enough, we do it right.' : 'No solo cerramos, lo hacemos bien.') }}
            </p>
        </div>

        @php
            $valueRows = !empty($aboutValues)
                ? collect($aboutValues)->map(fn($row) => [
                    'title' => $row->field('value_title'),
                    'description' => $row->field('value_description'),
                ])->filter(fn($row) => !empty($row['title']) || !empty($row['description']))->values()->all()
                : $fallbackValues;
        @endphp

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach($valueRows as $value)
                <div class="rounded-3xl p-8 border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                    <h3 class="text-xl font-bold mb-2" style="color: var(--fe-about_page-section_title, #1e293b);">{{ $value['title'] }}</h3>
                    <p style="color: #475569;">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-16">
        <div class="lg:col-span-5">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                {{ $pageData?->field('about_timeline_title') ?? ($isEn ? 'Our history' : 'Nuestra historia') }}
            </h2>
            <p class="text-lg" style="color: #475569;">
                {{ $pageData?->field('about_timeline_subtitle') ?? ($isEn ? 'We evolve with the market.' : 'Hemos evolucionado con el mercado.') }}
            </p>
        </div>

        @php
            $timelineRows = !empty($aboutTimeline)
                ? collect($aboutTimeline)->map(fn($row) => [
                    'year' => $row->field('timeline_year'),
                    'title' => $row->field('timeline_title'),
                    'description' => $row->field('timeline_description'),
                ])->filter(fn($row) => !empty($row['title']))->values()->all()
                : $fallbackTimeline;
        @endphp

        <div class="lg:col-span-7 relative pl-6">
            <div class="absolute left-2 top-0 bottom-0 w-px" style="background-color: var(--fe-about_page-timeline_line, #e2e8f0);"></div>
            @foreach($timelineRows as $item)
                <div class="relative pb-8">
                    <div class="absolute -left-[2px] top-2 w-4 h-4 rounded-full" style="background-color: var(--fe-about_page-timeline_dot_active, #D1A054);"></div>
                    <div class="ml-6 rounded-3xl border p-6" style="border-color: rgba(226,232,240,1); background: linear-gradient(to bottom right, rgba(255,255,255,1), rgba(248,250,252,1));">
                        <div class="text-sm font-semibold" style="color: var(--fe-primary-from, #D1A054);">{{ $item['year'] }}</div>
                        <h3 class="mt-2 text-xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">{{ $item['title'] }}</h3>
                        <p class="mt-2" style="color: #475569;">{{ $item['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 lg:py-24" style="background: linear-gradient(to bottom, rgba(248,250,252,1), rgba(255,255,255,1));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-4" style="background-color: rgba(147,51,234,0.10); color: rgba(147,51,234,1);">
                {{ $pageData?->field('about_team_badge') ?? ($isEn ? 'The team' : 'El equipo') }}
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4" style="color: var(--fe-about_page-section_title, #1e293b);">
                {{ $pageData?->field('about_team_title') ?? ($isEn ? 'Real people, real results' : 'Personas reales, resultados reales') }}
            </h2>
            <p class="text-lg" style="color: #475569;">
                {{ $pageData?->field('about_team_subtitle') ?? ($isEn ? 'A team focused on your objective.' : 'Un equipo enfocado en tu objetivo.') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($aboutTeam as $person)
                @php
                    $memberName = $person->field('member_name') ?? ($isEn ? 'Advisor' : 'Asesor');
                    $memberRole = $person->field('member_role') ?? ($isEn ? 'Real Estate Advisor' : 'Asesor Inmobiliario');
                    $memberImage = $person->image('member_image') ?: 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=900&q=80';
                @endphp
                <div class="group rounded-3xl overflow-hidden border transition-all duration-300 hover:shadow-xl" style="border-color: var(--fe-about_page-team_card_border, #e2e8f0); background: #fff;">
                    <div class="relative h-60 overflow-hidden">
                        <img src="{{ $memberImage }}" alt="{{ $memberName }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    <div class="p-6">
                        <div class="text-lg font-bold" style="color: var(--fe-about_page-team_name, #1e293b);">{{ $memberName }}</div>
                        <div class="text-sm" style="color: var(--fe-about_page-team_role, #64748b);">{{ $memberRole }}</div>
                        <div class="mt-5">
                            <a href="{{ route('public.contact') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:shadow-lg" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                                {{ $txt('about_team_member_cta', 'Contactar', 'Contact') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border p-10 lg:p-14" style="border-color: rgba(226,232,240,1); background: linear-gradient(135deg, rgba(209,160,84,0.08), rgba(118,141,89,0.10));">
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8">
                    <h2 class="text-3xl sm:text-4xl font-bold" style="color: var(--fe-about_page-section_title, #1e293b);">
                        {{ $pageData?->field('about_cta_title') ?? ($isEn ? 'Shall we talk about your next property?' : '¿Hablamos de tu próxima propiedad?') }}
                    </h2>
                    <p class="mt-3 text-lg" style="color: #475569;">
                        {{ $pageData?->field('about_cta_subtitle') ?? ($isEn ? 'Tell us what you are looking for and we will share real options.' : 'Cuéntanos qué buscas y te compartimos opciones reales.') }}
                    </p>
                </div>
                <div class="lg:col-span-4 flex flex-col gap-3 justify-end">
                    <a href="{{ route('public.contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-white font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105" style="background: linear-gradient(to right, var(--fe-primary-from, #D1A054), var(--fe-primary-to, #768D59));">
                        {{ $txt('about_cta_button_primary', 'Ir a contacto', 'Go to contact') }}
                    </a>
                    <a href="{{ route('public.properties.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold border transition-colors" style="border-color: rgba(226,232,240,1); color: rgba(30,41,59,1); background: rgba(255,255,255,0.7);">
                        {{ $txt('about_cta_button_secondary', 'Explorar catálogo', 'Explore catalog') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection




