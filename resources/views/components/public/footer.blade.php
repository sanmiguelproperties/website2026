@php
    use App\Services\CmsService;
    use App\Support\SocialLinks;
    use Illuminate\Support\Str;

    $locale = app()->getLocale();
    $footerData = CmsService::getPageData('footer', $locale);
    $txt = static fn (string $key, string $es, string $en) => $footerData?->field($key) ?? ($locale === 'en' ? $en : $es);

    $settings = CmsService::settings(['general', 'contact', 'social', 'company'], $locale);
    $footerCompany = CmsService::getMenu('footer-company');
    $footerServices = CmsService::getMenu('footer-services');

    $companyItems = $footerCompany?->rootItems ?? collect();
    $serviceItems = $footerServices?->rootItems ?? collect();
    $showMlsOffices = CmsService::settingBoolean('public_show_mls_offices', true);
    $showMlsAgents = CmsService::settingBoolean('public_show_mls_agents', true);

    $isHiddenMlsUrl = static function (?string $resolvedUrl) use ($showMlsOffices, $showMlsAgents): bool {
        $path = parse_url((string) $resolvedUrl, PHP_URL_PATH);
        $normalizedPath = '/' . ltrim((string) ($path ?? $resolvedUrl ?? ''), '/');
        $normalizedPath = rtrim(Str::lower($normalizedPath), '/');
        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }

        if (!$showMlsOffices && (str_starts_with($normalizedPath, '/agencias') || str_starts_with($normalizedPath, '/mls-offices'))) {
            return true;
        }

        if (!$showMlsAgents && (str_starts_with($normalizedPath, '/agentes') || str_starts_with($normalizedPath, '/mls-agents'))) {
            return true;
        }

        return false;
    };

    $shouldKeepMenuItem = static function ($item) use ($showMlsOffices, $showMlsAgents, $isHiddenMlsUrl): bool {
        $routeName = (string) ($item->route_name ?? '');

        if (
            !$showMlsOffices
            && in_array($routeName, ['public.mls-offices.index', 'public.mls-offices.show', 'public.mls-offices.legacy-index', 'public.mls-offices.legacy-show'], true)
        ) {
            return false;
        }

        if (
            !$showMlsAgents
            && in_array($routeName, ['public.mls-agents.index', 'public.mls-agents.show', 'public.mls-agents.legacy-index', 'public.mls-agents.legacy-show'], true)
        ) {
            return false;
        }

        return !$isHiddenMlsUrl($item->resolvedUrl());
    };

    $companyItems = $companyItems->filter($shouldKeepMenuItem)->values();
    $serviceItems = $serviceItems->filter($shouldKeepMenuItem)->values();

    $siteNameValue = $settings['site_name'] ?? 'San Miguel Properties';
    $siteTagline = $settings['site_tagline'] ?? $txt('footer_site_tagline', 'Encuentra tu hogar ideal', 'Find your dream home');

    $phoneDisplay = $settings['contact_phone'] ?? '+52 55 1234 5678';
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?: '+525512345678';
    $email = $settings['contact_email'] ?? 'info@sanmiguelproperties.com';
    $whatsapp = preg_replace('/[^0-9]/', '', (string) ($settings['contact_whatsapp'] ?? '525512345678'));
    $address = $settings['contact_address'] ?? 'San Miguel de Allende, Guanajuato, Mexico';
    $officeHours = $settings['office_hours'] ?? $txt('footer_office_hours', 'Lunes a Viernes 9:00 - 18:00', 'Monday to Friday 9:00 AM - 6:00 PM');

    $copyrightText = $settings['copyright_text'] ?? $txt('footer_copyright', 'Todos los derechos reservados.', 'All rights reserved.');

    $socialLinks = SocialLinks::fromSettings($settings);
    $footerPartnersBg = trim((string) ($footerData?->field('footer_partners_bg_color') ?: '#020202'));
    if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $footerPartnersBg)) {
        $footerPartnersBg = '#020202';
    }

    $normalizeExternalUrl = static function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith(Str::lower($url), ['http://', 'https://'])) {
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    };

    $partnerPlaceholderLogos = [
        ['name' => 'AMPI SMA MLS', 'type' => 'ampi-mls'],
        ['name' => 'AMPI', 'type' => 'ampi'],
        ['name' => 'CIB', 'type' => 'cib'],
        ['name' => 'AIAG', 'type' => 'aiag'],
        ['name' => 'CIPS', 'type' => 'cips'],
        ['name' => 'Administracion de Rentas Inmobiliarias', 'type' => 'ari'],
        ['name' => 'Patronato Pro Ninos', 'type' => 'patronato'],
        ['name' => 'REALTOR', 'type' => 'realtor'],
    ];
    $partnerLogoRows = collect($footerData?->repeater('footer_partner_items') ?? [])
        ->map(static function ($row) use ($normalizeExternalUrl): array {
            $name = trim((string) ($row->field('footer_partner_name') ?? ''));
            $imageUrl = $row->image('footer_partner_logo');

            return [
                'name' => $name !== '' ? $name : 'Empresa aliada',
                'image' => $imageUrl,
                'url' => $normalizeExternalUrl($row->field('footer_partner_url')),
                'type' => null,
            ];
        })
        ->filter(static fn (array $partner): bool => filled($partner['image']))
        ->values()
        ->all();
    $partnerLogos = !empty($partnerLogoRows) ? $partnerLogoRows : $partnerPlaceholderLogos;

    $newsletterTitle = $txt('footer_newsletter_title', 'Suscribete a nuestro newsletter', 'Subscribe to our newsletter');
    $newsletterText = $txt('footer_newsletter_text', 'Recibe las ultimas propiedades y oportunidades exclusivas directamente en tu correo.', 'Receive the latest properties and exclusive opportunities directly in your inbox.');
    $newsletterButton = $txt('footer_newsletter_button', 'Suscribirse', 'Subscribe');
    $showNewsletter = $footerData?->field('footer_hide_newsletter') !== '1';

    $labels = [
        'quick_links' => $txt('footer_quick_links', 'Enlaces rapidos', 'Quick Links'),
        'services' => $txt('footer_services', 'Servicios', 'Services'),
        'contact' => $txt('footer_contact', 'Contactanos', 'Contact'),
        'phone' => $txt('footer_phone', 'Telefono', 'Phone'),
        'email' => $txt('footer_email', 'Email', 'Email'),
        'address' => $txt('footer_address', 'Direccion', 'Address'),
        'hours' => $txt('footer_hours', 'Horario', 'Business Hours'),
        'whatsapp' => $txt('footer_whatsapp', 'WhatsApp', 'WhatsApp'),
        'about' => $txt('footer_about', 'Sobre nosotros', 'About us'),
        'privacy' => $txt('footer_privacy', 'Privacidad', 'Privacy'),
        'terms' => $txt('footer_terms', 'Terminos', 'Terms'),
        'properties' => $txt('footer_properties', 'Propiedades', 'Properties'),
    ];
@endphp

<footer class="smp-public-footer relative text-white overflow-hidden" style="background-color: var(--fe-footer-background, #1C1C1C);">
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_from, rgba(209, 160, 84, 0.2));"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_to, rgba(118, 141, 89, 0.2));"></div>
        <div class="absolute inset-0 [background-size:32px_32px]" style="background-image: radial-gradient(circle at 1px 1px, var(--fe-footer-pattern, rgba(255,255,255,0.03)) 1px, transparent 0);"></div>
    </div>

    <div class="relative">
        <section class="footer-partners w-full border-b border-white/10" aria-label="Empresas que trabajan con nosotros" style="background-color: {{ $footerPartnersBg }};">
            <div class="footer-partners-track flex w-full items-center justify-between gap-8 overflow-x-auto px-6 py-5 sm:px-10 lg:px-14">
                @foreach($partnerLogos as $partner)
                    <div class="footer-partner-logo flex h-16 min-w-[112px] shrink-0 items-center justify-center text-white" title="{{ $partner['name'] }}">
                        @if(!empty($partner['image']))
                            @if(!empty($partner['url']))
                                <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="flex h-full items-center justify-center" aria-label="{{ $partner['name'] }}">
                                    <img src="{{ $partner['image'] }}" alt="{{ $partner['name'] }}" loading="lazy" />
                                </a>
                            @else
                                <img src="{{ $partner['image'] }}" alt="{{ $partner['name'] }}" loading="lazy" />
                            @endif
                        @else
                        @switch($partner['type'] ?? '')
                            @case('ampi-mls')
                                <div class="flex items-center gap-2">
                                    <div class="grid h-9 w-9 place-items-center border-2 border-current">
                                        <span class="block h-5 w-4 border-x-2 border-t-2 border-current"></span>
                                    </div>
                                    <div class="leading-none">
                                        <p class="text-[10px] font-bold">AMPI SMA</p>
                                        <p class="text-2xl font-extrabold">MLS</p>
                                    </div>
                                </div>
                                @break

                            @case('ampi')
                                <p class="text-3xl font-extrabold italic">AMPI</p>
                                @break

                            @case('cib')
                                <div class="relative grid h-14 w-14 place-items-center rotate-45 border-2 border-current">
                                    <span class="-rotate-45 text-2xl font-black">CIB</span>
                                </div>
                                @break

                            @case('aiag')
                                <div class="grid h-14 w-14 place-items-center rounded-full border-2 border-current">
                                    <span class="text-lg font-black">AIAG</span>
                                </div>
                                @break

                            @case('cips')
                                <div class="flex items-end gap-1">
                                    <span class="text-4xl font-light">CIPS</span>
                                    <span class="mb-1 grid grid-cols-3 gap-0.5">
                                        @for($i = 0; $i < 9; $i++)
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        @endfor
                                    </span>
                                </div>
                                @break

                            @case('ari')
                                <div class="grid place-items-center">
                                    <div class="grid h-11 w-11 place-items-center rounded-full border-2 border-current">
                                        <span class="text-3xl leading-none">A</span>
                                    </div>
                                    <p class="mt-1 max-w-[130px] text-center text-[9px] font-semibold uppercase leading-tight">Administracion de Rentas Inmobiliarias</p>
                                </div>
                                @break

                            @case('patronato')
                                <div class="text-center leading-none">
                                    <div class="mx-auto mb-1 h-8 w-8 rounded-full border-2 border-current"></div>
                                    <p class="text-lg font-bold">Patronato</p>
                                    <p class="text-sm font-semibold">Pro Ninos</p>
                                </div>
                                @break

                            @case('realtor')
                                <div class="grid h-20 w-24 place-items-center bg-white/10 p-2">
                                    <div class="text-center leading-none">
                                        <p class="text-5xl font-black">R</p>
                                        <p class="text-xs font-bold">REALTOR</p>
                                    </div>
                                </div>
                                @break
                        @endswitch
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        @if($showNewsletter)
        <div class="footer-divider border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 text-center">
                <h3 class="text-2xl sm:text-3xl font-bold mb-3">{{ $newsletterTitle }}</h3>
                <p class="footer-text-secondary text-slate-400 mb-7 max-w-2xl mx-auto">{{ $newsletterText }}</p>
                <form id="publicNewsletterForm" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" novalidate>
                    <input name="email" type="email" required placeholder="{{ $txt('footer_newsletter_placeholder', 'tu@correo.com', 'you@email.com') }}" class="footer-input w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none" />
                    <button type="submit" class="px-6 py-3.5 rounded-xl font-semibold text-white hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-footer-newsletter_button_from, #D1A054), var(--fe-footer-newsletter_button_to, #768D59));">
                        {{ $newsletterButton }}
                    </button>
                </form>
                <p id="publicNewsletterFeedback" class="hidden mx-auto mt-4 max-w-md rounded-xl border px-4 py-3 text-sm"></p>
            </div>
        </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                <div>
                    <a href="{{ url('/') }}" class="flex items-center gap-3 mb-5 group">
                        @if(!empty($siteLogoDarkUrl))
                            <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteNameValue }}" class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                        @elseif(!empty($siteLogoUrl))
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteNameValue }}" class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                        @else
                            <div class="grid h-12 w-12 place-items-center rounded-xl text-white shadow-lg" style="background: linear-gradient(to bottom right, var(--fe-footer-accent_from, #D1A054), var(--fe-footer-accent_to, #768D59));">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21h18" />
                                    <path d="M6 21V7a2 2 0 0 1 2-2h3" />
                                    <path d="M11 21V11a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v10" />
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="text-lg font-bold tracking-tight">{{ $siteNameValue }}</p>
                            <p class="footer-text-secondary text-sm text-slate-400">{{ $siteTagline }}</p>
                        </div>
                    </a>

                    @if(!empty($socialLinks))
                    <div class="flex flex-wrap items-center gap-3 mt-6">
                        @foreach($socialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="footer-social-link w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-300 hover:text-white transition-all duration-300 hover:-translate-y-0.5" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                                @include('components.public.social-icon', ['network' => $social['network'], 'class' => 'h-5 w-5'])
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div>
                    <h4 class="footer-heading text-white font-semibold text-lg mb-6">{{ $labels['quick_links'] }}</h4>
                    <ul class="space-y-3">
                        @forelse($companyItems as $item)
                            @php
                                $resolved = $item->resolvedUrl() ?? '#';
                                $isExternal = Str::startsWith($resolved, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                                $href = $isExternal ? $resolved : url($resolved);
                            @endphp
                            <li>
                                <a href="{{ $href }}" class="footer-link text-slate-400 hover:text-white transition-colors duration-200">
                                    {{ $item->label($locale) }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('about') }}" class="footer-link text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['about'] }}</a></li>
                            <li><a href="{{ route('public.contact') }}" class="footer-link text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['contact'] }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading text-white font-semibold text-lg mb-6">{{ $labels['services'] }}</h4>
                    <ul class="space-y-3">
                        @forelse($serviceItems as $item)
                            @php
                                $resolved = $item->resolvedUrl() ?? '#';
                                $isExternal = Str::startsWith($resolved, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                                $href = $isExternal ? $resolved : url($resolved);
                            @endphp
                            <li>
                                <a href="{{ $href }}" class="footer-link text-slate-400 hover:text-white transition-colors duration-200">
                                    {{ $item->label($locale) }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('public.properties.index') }}" class="footer-link text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['properties'] }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading text-white font-semibold text-lg mb-6">{{ $labels['contact'] }}</h4>
                    <ul class="footer-text-secondary space-y-4 text-slate-400">
                        <li>
                            <a href="tel:{{ $phoneHref }}" class="footer-link hover:text-white transition-colors duration-200">
                                <span class="footer-contact-label font-medium text-white">{{ $labels['phone'] }}:</span> {{ $phoneDisplay }}
                            </a>
                        </li>
                        <li>
                            <a href="mailto:{{ $email }}" class="footer-link hover:text-white transition-colors duration-200">
                                <span class="footer-contact-label font-medium text-white">{{ $labels['email'] }}:</span> {{ $email }}
                            </a>
                        </li>
                        @if(!empty($whatsapp))
                            <li>
                                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="footer-link hover:text-white transition-colors duration-200">{{ $labels['whatsapp'] }}</a>
                            </li>
                        @endif
                        <li>
                            <span class="footer-contact-label font-medium text-white">{{ $labels['address'] }}:</span>
                            <p class="text-sm mt-1">{{ $address }}</p>
                        </li>
                        <li>
                            <span class="footer-contact-label font-medium text-white">{{ $labels['hours'] }}:</span>
                            <p class="text-sm mt-1">{{ $officeHours }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-divider border-t border-white/10">
            <div class="footer-bottom-text max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-3 text-sm text-slate-500">
                <p>{{ date('Y') }} {{ $siteNameValue }}. {{ $copyrightText }}</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('public.contact') }}" class="footer-link hover:text-white transition-colors duration-200">{{ $labels['privacy'] }}</a>
                    <a href="{{ route('public.contact') }}" class="footer-link hover:text-white transition-colors duration-200">{{ $labels['terms'] }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>

@if($showNewsletter)
    @once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('publicNewsletterForm');
        const feedback = document.getElementById('publicNewsletterFeedback');
        if (!form || !feedback) return;

        function showNewsletterFeedback(type, message) {
            feedback.textContent = message;
            feedback.classList.remove('hidden', 'border-emerald-500/30', 'bg-emerald-500/10', 'text-emerald-100', 'border-rose-500/30', 'bg-rose-500/10', 'text-rose-100');
            if (type === 'success') {
                feedback.classList.add('border-emerald-500/30', 'bg-emerald-500/10', 'text-emerald-100');
            } else {
                feedback.classList.add('border-rose-500/30', 'bg-rose-500/10', 'text-rose-100');
            }
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const email = form.elements.namedItem('email')?.value?.trim() || '';
            if (!email) {
                showNewsletterFeedback('error', window.publicT('contact.requiredFields', 'Por favor completa todos los campos requeridos.'));
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');

            try {
                if (submitButton) submitButton.disabled = true;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/api/public/contact-requests', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    body: JSON.stringify({
                        email,
                        source: 'footer_newsletter',
                        lead_type: 'newsletter',
                        ...((window.publicLeadTrackingPayload && window.publicLeadTrackingPayload()) || {}),
                    }),
                });
                const data = await response.json().catch(() => null);

                if (!response.ok || !data?.success) {
                    showNewsletterFeedback('error', data?.message || window.publicT('contact.submitError', 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.'));
                    return;
                }

                form.reset();
                showNewsletterFeedback('success', window.publicT('contact.submitSuccess', 'Mensaje enviado con exito. Nos pondremos en contacto contigo pronto.'));
            } catch (_error) {
                showNewsletterFeedback('error', window.publicT('contact.connectionError', 'Error de conexion. Por favor verifica tu internet e intenta de nuevo.'));
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    });
    </script>
    @endpush
    @endonce
@endif

<style>
    .smp-public-footer .footer-partners {
        background-color: var(--fe-footer-partners_bg, #020202);
        border-color: var(--fe-footer-divider, rgba(255,255,255,0.1)) !important;
    }

    .smp-public-footer .footer-partners-track {
        scrollbar-width: none;
    }

    .smp-public-footer .footer-partners-track::-webkit-scrollbar {
        display: none;
    }

    .smp-public-footer .footer-partner-logo {
        color: var(--fe-footer-partners_logo, #ffffff);
        opacity: 0.9;
    }

    .smp-public-footer .footer-partner-logo img {
        max-height: 64px;
        width: auto;
        filter: brightness(0) invert(1);
    }

    .smp-public-footer .footer-divider {
        border-color: var(--fe-footer-divider, rgba(255,255,255,0.1)) !important;
    }

    .smp-public-footer .footer-text-secondary {
        color: var(--fe-footer-text_secondary, #94a3b8) !important;
    }

    .smp-public-footer .footer-heading,
    .smp-public-footer .footer-contact-label {
        color: var(--fe-footer-text_primary, #ffffff) !important;
    }

    .smp-public-footer .footer-link {
        color: var(--fe-footer-link_text, #94a3b8) !important;
    }

    .smp-public-footer .footer-link:hover {
        color: var(--fe-footer-link_hover, #ffffff) !important;
    }

    .smp-public-footer .footer-input {
        background-color: var(--fe-footer-input_bg, rgba(255,255,255,0.05)) !important;
        border-color: var(--fe-footer-input_border, rgba(255,255,255,0.1)) !important;
        color: var(--fe-footer-input_text, #ffffff) !important;
    }

    .smp-public-footer .footer-input::placeholder {
        color: var(--fe-footer-input_placeholder, #64748b) !important;
    }

    .smp-public-footer .footer-social-link {
        background-color: var(--fe-footer-social_bg, rgba(255,255,255,0.05)) !important;
        color: var(--fe-footer-social_text, #cbd5e1) !important;
    }

    .smp-public-footer .footer-social-link:hover {
        background-color: var(--fe-footer-social_hover_bg, rgba(255,255,255,0.1)) !important;
        color: var(--fe-footer-social_hover_text, #ffffff) !important;
    }

    .smp-public-footer .footer-bottom-text {
        color: var(--fe-footer-copyright_text, #64748b) !important;
    }
</style>
