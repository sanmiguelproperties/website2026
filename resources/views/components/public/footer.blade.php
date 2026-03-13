@php
    use App\Services\CmsService;
    use Illuminate\Support\Str;

    $locale = app()->getLocale();
    $pageData = $pageData ?? null;
    $txt = static fn (string $key, string $es, string $en) => $pageData?->field($key) ?? ($locale === 'en' ? $en : $es);

    $settings = CmsService::settings(['general', 'contact', 'social', 'company'], $locale);
    $footerCompany = CmsService::getMenu('footer-company');
    $footerServices = CmsService::getMenu('footer-services');

    $companyItems = $footerCompany?->rootItems ?? collect();
    $serviceItems = $footerServices?->rootItems ?? collect();

    $siteNameValue = $settings['site_name'] ?? 'San Miguel Properties';
    $siteTagline = $settings['site_tagline'] ?? $txt('footer_site_tagline', 'Encuentra tu hogar ideal', 'Find your dream home');

    $phoneDisplay = $settings['contact_phone'] ?? '+52 55 1234 5678';
    $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?: '+525512345678';
    $email = $settings['contact_email'] ?? 'info@sanmiguelproperties.com';
    $whatsapp = preg_replace('/[^0-9]/', '', (string) ($settings['contact_whatsapp'] ?? '525512345678'));
    $address = $settings['contact_address'] ?? 'San Miguel de Allende, Guanajuato, Mexico';
    $officeHours = $settings['office_hours'] ?? $txt('footer_office_hours', 'Lunes a Viernes 9:00 - 18:00', 'Monday to Friday 9:00 AM - 6:00 PM');

    $copyrightText = $settings['copyright_text'] ?? $txt('footer_copyright', 'Todos los derechos reservados.', 'All rights reserved.');

    $socialLinks = [
        ['key' => 'social_facebook', 'label' => 'Facebook'],
        ['key' => 'social_instagram', 'label' => 'Instagram'],
        ['key' => 'social_twitter', 'label' => 'X'],
        ['key' => 'social_linkedin', 'label' => 'LinkedIn'],
        ['key' => 'social_youtube', 'label' => 'YouTube'],
    ];

    $newsletterTitle = $txt('footer_newsletter_title', 'Suscribete a nuestro newsletter', 'Subscribe to our newsletter');
    $newsletterText = $txt('footer_newsletter_text', 'Recibe las ultimas propiedades y oportunidades exclusivas directamente en tu correo.', 'Receive the latest properties and exclusive opportunities directly in your inbox.');
    $newsletterButton = $txt('footer_newsletter_button', 'Suscribirse', 'Subscribe');

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

<footer class="relative text-white overflow-hidden" style="background-color: var(--fe-footer-background, #1C1C1C);">
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_from, rgba(209, 160, 84, 0.2));"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_to, rgba(118, 141, 89, 0.2));"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.03)_1px,transparent_0)] [background-size:32px_32px]"></div>
    </div>

    <div class="relative">
        <div class="border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 text-center">
                <h3 class="text-2xl sm:text-3xl font-bold mb-3">{{ $newsletterTitle }}</h3>
                <p class="text-slate-400 mb-7 max-w-2xl mx-auto">{{ $newsletterText }}</p>
                <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input type="email" placeholder="{{ $txt('footer_newsletter_placeholder', 'tu@correo.com', 'you@email.com') }}" class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none" />
                    <button type="submit" class="px-6 py-3.5 rounded-xl font-semibold text-white hover:shadow-lg transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-footer-newsletter_button_from, #D1A054), var(--fe-footer-newsletter_button_to, #768D59));">
                        {{ $newsletterButton }}
                    </button>
                </form>
            </div>
        </div>

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
                            <p class="text-sm text-slate-400">{{ $siteTagline }}</p>
                        </div>
                    </a>

                    <div class="flex items-center gap-3 mt-6">
                        @foreach($socialLinks as $social)
                            @php $socialUrl = $settings[$social['key']] ?? null; @endphp
                            @continue(empty($socialUrl))
                            <a href="{{ $socialUrl }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-300 hover:text-white transition-all duration-300" aria-label="{{ $social['label'] }}">
                                <span class="text-xs font-semibold">{{ strtoupper(substr($social['label'], 0, 1)) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-lg mb-6">{{ $labels['quick_links'] }}</h4>
                    <ul class="space-y-3">
                        @forelse($companyItems as $item)
                            @php
                                $resolved = $item->resolvedUrl() ?? '#';
                                $isExternal = Str::startsWith($resolved, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                                $href = $isExternal ? $resolved : url($resolved);
                            @endphp
                            <li>
                                <a href="{{ $href }}" class="text-slate-400 hover:text-white transition-colors duration-200">
                                    {{ $item->label($locale) }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('about') }}" class="text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['about'] }}</a></li>
                            <li><a href="{{ route('public.contact') }}" class="text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['contact'] }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-lg mb-6">{{ $labels['services'] }}</h4>
                    <ul class="space-y-3">
                        @forelse($serviceItems as $item)
                            @php
                                $resolved = $item->resolvedUrl() ?? '#';
                                $isExternal = Str::startsWith($resolved, ['http://', 'https://', 'mailto:', 'tel:', '#']);
                                $href = $isExternal ? $resolved : url($resolved);
                            @endphp
                            <li>
                                <a href="{{ $href }}" class="text-slate-400 hover:text-white transition-colors duration-200">
                                    {{ $item->label($locale) }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('public.properties.index') }}" class="text-slate-400 hover:text-white transition-colors duration-200">{{ $labels['properties'] }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-lg mb-6">{{ $labels['contact'] }}</h4>
                    <ul class="space-y-4 text-slate-400">
                        <li>
                            <a href="tel:{{ $phoneHref }}" class="hover:text-white transition-colors duration-200">
                                <span class="font-medium text-white">{{ $labels['phone'] }}:</span> {{ $phoneDisplay }}
                            </a>
                        </li>
                        <li>
                            <a href="mailto:{{ $email }}" class="hover:text-white transition-colors duration-200">
                                <span class="font-medium text-white">{{ $labels['email'] }}:</span> {{ $email }}
                            </a>
                        </li>
                        @if(!empty($whatsapp))
                            <li>
                                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-white transition-colors duration-200">{{ $labels['whatsapp'] }}</a>
                            </li>
                        @endif
                        <li>
                            <span class="font-medium text-white">{{ $labels['address'] }}:</span>
                            <p class="text-sm mt-1">{{ $address }}</p>
                        </li>
                        <li>
                            <span class="font-medium text-white">{{ $labels['hours'] }}:</span>
                            <p class="text-sm mt-1">{{ $officeHours }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-3 text-sm text-slate-500">
                <p>{{ date('Y') }} {{ $siteNameValue }}. {{ $copyrightText }}</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('public.contact') }}" class="hover:text-white transition-colors duration-200">{{ $labels['privacy'] }}</a>
                    <a href="{{ route('public.contact') }}" class="hover:text-white transition-colors duration-200">{{ $labels['terms'] }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
