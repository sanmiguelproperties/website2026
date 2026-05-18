@extends('layouts.public')

@php
    $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
    $isEn = $locale === 'en';

    $txt = fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($isEn ? $en : $es);
    $pageTitle = $pageData?->entity?->title($locale) ?? ($isEn ? 'About Us' : 'Nosotros');

    $whoSubtitle = $pageData?->field('about_who_text')
        ?? $pageData?->field('about_hero_subtitle')
        ?? ($isEn
            ? 'A modern real estate team focused on strategy, trust and measurable results.'
            : 'Un equipo inmobiliario moderno, enfocado en estrategia, confianza y resultados medibles.');

    $historyTitle = $pageData?->field('about_history_title') ?? ($isEn ? 'History' : 'Historia');
    $historyText = $pageData?->field('about_history_text') ?? ($isEn
        ? 'We evolved from a local operation to a structured real estate partner with high standards.'
        : 'Evolucionamos de una operación local a un aliado inmobiliario estructurado y de alto estándar.');

    $missionTitle = $pageData?->field('about_mission_title') ?? ($isEn ? 'Mission' : 'Misión');
    $missionText = $pageData?->field('about_mission_text') ?? ($isEn
        ? 'Deliver transparent advisory and clear execution in every transaction.'
        : 'Entregar asesoría transparente y una ejecución clara en cada operación.');

    $visionTitle = $pageData?->field('about_vision_title') ?? ($isEn ? 'Vision' : 'Visión');
    $visionText = $pageData?->field('about_vision_text') ?? ($isEn
        ? 'Be the most trusted agency in our market by combining people, process and technology.'
        : 'Ser la agencia más confiable de nuestro mercado combinando personas, procesos y tecnología.');

    $brokersRows = $pageData?->repeater('about_brokers_members') ?? [];
    $brokers = !empty($brokersRows)
        ? collect($brokersRows)
            ->map(fn ($row) => [
                'name' => trim((string) ($row->field('broker_name') ?? '')),
                'role' => trim((string) ($row->field('broker_role') ?? '')),
                'bio' => trim((string) ($row->field('broker_bio') ?? '')),
                'image' => $row->image('broker_image'),
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all()
        : [];

    if (empty($brokers)) {
        $brokers = [
            [
                'name' => 'Erwit',
                'role' => $isEn ? 'Lead Broker' : 'Broker Líder',
                'bio' => $isEn
                    ? 'Specialized in premium listings and strategic negotiations.'
                    : 'Especializado en propiedades premium y negociaciones estratégicas.',
                'image' => null,
            ],
            [
                'name' => 'Jenny',
                'role' => $isEn ? 'Senior Broker' : 'Broker Senior',
                'bio' => $isEn
                    ? 'Focused on client experience and efficient closing workflows.'
                    : 'Enfocada en experiencia del cliente y cierres eficientes.',
                'image' => null,
            ],
        ];
    }

    $teamRows = $pageData?->repeater('about_core_team_members') ?? [];
    $teamMembers = !empty($teamRows)
        ? collect($teamRows)
            ->map(fn ($row) => [
                'name' => trim((string) ($row->field('core_member_name') ?? '')),
                'role' => trim((string) ($row->field('core_member_role') ?? '')),
                'bio' => trim((string) ($row->field('core_member_bio') ?? '')),
                'image' => $row->image('core_member_image'),
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all()
        : [];

    if (empty($teamMembers)) {
        $teamMembers = [
            [
                'name' => 'Sophia',
                'role' => $isEn ? 'Operations' : 'Operaciones',
                'bio' => $isEn
                    ? 'Coordinates internal workflows to keep every operation on track.'
                    : 'Coordina los flujos internos para mantener cada operación en ritmo.',
                'image' => null,
            ],
            [
                'name' => 'Jorge',
                'role' => $isEn ? 'Marketing' : 'Marketing',
                'bio' => $isEn
                    ? 'Drives positioning, content and digital acquisition.'
                    : 'Impulsa posicionamiento, contenido y adquisición digital.',
                'image' => null,
            ],
            [
                'name' => 'Greta',
                'role' => $isEn ? 'Customer Success' : 'Customer Success',
                'bio' => $isEn
                    ? 'Leads post-sale service and long-term client relationships.'
                    : 'Lidera la atención postventa y la relación de largo plazo con clientes.',
                'image' => null,
            ],
        ];
    }

    $brokersTitle = $txt('about_brokers_title', 'Nuestros Brokers', 'Our Brokers');
    $brokersSubtitle = $txt(
        'about_brokers_subtitle',
        'Liderazgo comercial con criterio, experiencia y ejecución precisa.',
        'Commercial leadership with judgment, experience and precise execution.'
    );

    $coreTeamTitle = $txt('about_core_team_title', 'Nuestro equipo', 'Our Team');
    $coreTeamSubtitle = $txt(
        'about_core_team_subtitle',
        'El equipo interno que sostiene la operación de punta a punta.',
        'The internal team that sustains operations end-to-end.'
    );

    $agentsTitle = $txt('about_agents_title', 'Nuestros agentes', 'Our Agents');
    $agentsSubtitle = $txt(
        'about_agents_subtitle',
        'Mostramos únicamente agentes activos de la agencia principal.',
        'We only show active agents from the main agency.'
    );

    $primaryOfficeAgents = collect($primaryOfficeAgents ?? []);
    $rich = static fn (?string $html, ?string $fallback = null): string => \App\Support\RichTextSanitizer::sanitize($html, $fallback);
@endphp

@section('title', $pageTitle)

@section('content')
<div class="pt-24">
    <section class="relative overflow-hidden py-16 lg:py-24" style="background-color: var(--fe-about_hero-section_bg, var(--fe-about_page-hero_bg_from, #0f172a));">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-end">
                <div class="lg:col-span-8">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight" style="color: var(--fe-about_hero-title, var(--fe-about_page-hero_title, #ffffff));">
                        {{ $txt('about_who_heading', 'Real estate con visión moderna', 'Real estate with modern vision') }}
                    </h1>
                    <div class="mt-6 text-lg sm:text-xl max-w-3xl rich-content" style="color: var(--fe-about_hero-subtitle, var(--fe-about_page-hero_subtitle, rgba(255,255,255,0.82)));">
                        {!! $rich($whoSubtitle) !!}
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border p-6 backdrop-blur" style="border-color: var(--fe-about_hero-focus_card_border, var(--fe-about_page-hero_secondary_cta_border, rgba(255,255,255,0.18))); background-color: var(--fe-about_hero-focus_card_bg, var(--fe-about_page-hero_secondary_cta_bg, rgba(15,23,42,0.45)));">
                        <div class="text-xs uppercase tracking-wide font-semibold" style="color: var(--fe-about_hero-focus_label, var(--fe-about_page-hero_badge_text, rgba(255,255,255,0.72)));">
                            {{ $isEn ? 'Focus' : 'Enfoque' }}
                        </div>
                        <div class="mt-3 text-2xl font-extrabold" style="color: var(--fe-about_hero-focus_title, var(--fe-about_page-hero_title, #ffffff));">
                            {{ $txt('about_focus_label', 'Estrategia + Ejecución', 'Strategy + Execution') }}
                        </div>
                        <p class="mt-3 text-sm" style="color: var(--fe-about_hero-focus_text, var(--fe-about_page-hero_subtitle, rgba(255,255,255,0.78)));">
                            {{ $txt('about_focus_text', 'Decisiones con datos, operación clara y acompañamiento cercano.', 'Data-driven decisions, clear execution and close support.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14 lg:py-20" style="background-color: var(--fe-about_identity-section_bg, var(--fe-about_page-values_section_bg_from, #f8fafc));">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3 lg:items-stretch">
                <article id="historia" class="h-full rounded-3xl border p-8 lg:p-9 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-xl scroll-mt-28" style="border-color: var(--fe-about_identity-card_border, var(--fe-about_page-value_card_border, #e2e8f0)); background-color: var(--fe-about_identity-card_bg, var(--fe-about_page-value_card_bg_from, #ffffff));">
                    <div class="mb-7 h-1 w-14 rounded-full" style="background-color: var(--fe-about_identity-card_line, var(--fe-about_page-value_icon_1, #D1A054));"></div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight" style="color: var(--fe-about_identity-history_title, var(--fe-about_page-section_title, #0f172a));">{{ $historyTitle }}</h2>
                    <div class="mt-5 text-base sm:text-lg leading-relaxed rich-content" style="color: var(--fe-about_identity-history_text, var(--fe-about_page-body_text, #475569));">{!! $rich($historyText) !!}</div>
                </article>

                <article id="mision" class="h-full rounded-3xl border p-8 lg:p-9 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-xl scroll-mt-28" style="border-color: var(--fe-about_identity-card_border, var(--fe-about_page-value_card_border, #e2e8f0)); background-color: var(--fe-about_identity-card_bg, var(--fe-about_page-value_card_bg_from, #ffffff));">
                    <div class="mb-7 h-1 w-14 rounded-full" style="background-color: var(--fe-about_identity-card_line, var(--fe-about_page-value_icon_1, #D1A054));"></div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight" style="color: var(--fe-about_identity-mission_title, var(--fe-about_page-section_title, #0f172a));">{{ $missionTitle }}</h2>
                    <div class="mt-5 text-base sm:text-lg leading-relaxed rich-content" style="color: var(--fe-about_identity-mission_text, var(--fe-about_page-body_text, #334155));">{!! $rich($missionText) !!}</div>
                </article>

                <article id="vision" class="h-full rounded-3xl border p-8 lg:p-9 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-xl scroll-mt-28" style="border-color: var(--fe-about_identity-card_border, var(--fe-about_page-value_card_border, #e2e8f0)); background-color: var(--fe-about_identity-card_bg, var(--fe-about_page-value_card_bg_from, #ffffff));">
                    <div class="mb-7 h-1 w-14 rounded-full" style="background-color: var(--fe-about_identity-card_line, var(--fe-about_page-value_icon_1, #D1A054));"></div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight" style="color: var(--fe-about_identity-vision_title, var(--fe-about_page-section_title, #0f172a));">{{ $visionTitle }}</h2>
                    <div class="mt-5 text-base sm:text-lg leading-relaxed rich-content" style="color: var(--fe-about_identity-vision_text, var(--fe-about_page-body_text, #334155));">{!! $rich($visionText) !!}</div>
                </article>
            </div>
        </div>
    </section>

    <section id="brokers" class="py-16 lg:py-20" style="background-color: var(--fe-about_brokers-section_bg, var(--fe-about_page-summary_section_bg, #ffffff));">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-10">
                <h2 class="text-3xl sm:text-4xl font-black" style="color: var(--fe-about_brokers-title, var(--fe-about_page-section_title, #0f172a));">{{ $brokersTitle }}</h2>
                <p class="mt-3 text-lg" style="color: var(--fe-about_brokers-subtitle, var(--fe-about_page-body_text, #475569));">{{ $brokersSubtitle }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
                @foreach($brokers as $index => $broker)
                    @php
                        $brokerImage = $broker['image']
                            ?: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=900&q=80';
                    @endphp
                    <article class="group rounded-3xl overflow-hidden border shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="border-color: var(--fe-about_brokers-card_border, var(--fe-about_page-team_card_border, #e2e8f0)); background-color: var(--fe-about_brokers-card_bg, var(--fe-about_page-team_card_bg, #ffffff));">
                        @if(!empty($broker['image']))
                            <div class="h-72 sm:h-80 lg:h-96 overflow-hidden">
                                <img src="{{ $brokerImage }}" alt="{{ $broker['name'] }}" class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="text-2xl font-extrabold" style="color: var(--fe-about_brokers-name, var(--fe-about_page-team_name, #0f172a));">{{ $broker['name'] }}</h3>
                            @if(!empty($broker['role']))
                                <p class="mt-1 text-sm font-semibold uppercase tracking-wide" style="color: var(--fe-about_brokers-role, var(--fe-about_page-team_role, #9a7035));">{{ $broker['role'] }}</p>
                            @endif
                            @if(!empty($broker['bio']))
                                <div class="mt-4 text-sm leading-relaxed rich-content" style="color: var(--fe-about_brokers-bio, var(--fe-about_page-body_text, #475569));">{!! $rich($broker['bio']) !!}</div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="equipo" class="py-16 lg:py-20" style="background-color: var(--fe-about_team-section_bg, var(--fe-about_page-team_section_bg_from, #f8fafc));">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-10">
                <h2 class="text-3xl sm:text-4xl font-black" style="color: var(--fe-about_team-title, var(--fe-about_page-section_title, #0f172a));">{{ $coreTeamTitle }}</h2>
                <p class="mt-3 text-lg" style="color: var(--fe-about_team-subtitle, var(--fe-about_page-body_text, #475569));">{{ $coreTeamSubtitle }}</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($teamMembers as $member)
                    @php
                        $memberImage = $member['image']
                            ?: 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=900&q=80';
                    @endphp
                    <article class="group rounded-3xl overflow-hidden border shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="border-color: var(--fe-about_team-card_border, var(--fe-about_page-team_card_border, #e2e8f0)); background-color: var(--fe-about_team-card_bg, var(--fe-about_page-team_card_bg, #ffffff));">
                        @if(!empty($member['image']))
                            <div class="h-72 sm:h-80 lg:h-96 overflow-hidden">
                                <img src="{{ $memberImage }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="text-2xl font-extrabold" style="color: var(--fe-about_team-name, var(--fe-about_page-team_name, #0f172a));">{{ $member['name'] }}</h3>
                            @if(!empty($member['role']))
                                <p class="mt-1 text-sm font-semibold uppercase tracking-wide" style="color: var(--fe-about_team-role, var(--fe-about_page-team_role, #0f766e));">{{ $member['role'] }}</p>
                            @endif
                            @if(!empty($member['bio']))
                                <div class="mt-4 text-sm leading-relaxed rich-content" style="color: var(--fe-about_team-bio, var(--fe-about_page-body_text, #475569));">{!! $rich($member['bio']) !!}</div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="agentes" class="py-16 lg:py-20" style="background-color: var(--fe-about_agents-section_bg, #0f172a);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-10">
                <h2 class="text-3xl sm:text-4xl font-black" style="color: var(--fe-about_agents-title, #ffffff);">{{ $agentsTitle }}</h2>
                <p class="mt-3 text-lg" style="color: var(--fe-about_agents-subtitle, rgba(255,255,255,0.78));">{{ $agentsSubtitle }}</p>
            </div>

            @if($primaryOfficeAgents->isEmpty())
                <div class="rounded-3xl border p-8 text-center" style="border-color: var(--fe-about_agents-empty_card_border, rgba(255,255,255,0.2)); background-color: var(--fe-about_agents-empty_card_bg, rgba(15,23,42,0.4));">
                    <h3 class="text-xl font-bold" style="color: var(--fe-about_agents-empty_title, #ffffff);">{{ $isEn ? 'No agents available right now' : 'No hay agentes disponibles en este momento' }}</h3>
                    <p class="mt-2" style="color: var(--fe-about_agents-empty_text, rgba(255,255,255,0.75));">
                        {{ $isEn
                            ? 'When agents are active in the main agency, they will appear here automatically.'
                            : 'Cuando existan agentes activos en la agencia principal, aparecerán aquí automáticamente.' }}
                    </p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($primaryOfficeAgents as $agent)
                        @php
                            $agentName = $agent->full_name ?: ($agent->name ?? (($isEn ? 'Agent' : 'Agente') . ' #' . $agent->mls_agent_id));
                            $agentImage = $agent->photo ?: 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?auto=format&fit=crop&w=900&q=80';
                        @endphp

                        <article class="rounded-3xl overflow-hidden border" style="border-color: var(--fe-about_agents-card_border, rgba(255,255,255,0.2)); background-color: var(--fe-about_agents-card_bg, rgba(15,23,42,0.45)); backdrop-filter: blur(8px);">
                            <div class="h-72 sm:h-80 lg:h-96 overflow-hidden">
                                <img src="{{ $agentImage }}" alt="{{ $agentName }}" class="w-full h-full object-cover object-top" loading="lazy" />
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-extrabold" style="color: var(--fe-about_agents-name, #ffffff);">{{ $agentName }}</h3>

                                @if(!empty($agent->email))
                                    <p class="mt-2 text-sm" style="color: var(--fe-about_agents-email, rgba(255,255,255,0.74));">{{ $agent->email }}</p>
                                @endif

                                <div class="mt-5 flex gap-2">
                                    <a href="{{ route('public.mls-agents.show', ['mlsAgentId' => (int) $agent->mls_agent_id]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold" style="background-color: var(--fe-about_agents-button_bg, var(--fe-primary-from, #D1A054)); color: var(--fe-about_agents-button_text, #ffffff);">
                                        {{ $isEn ? 'View profile' : 'Ver perfil' }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
