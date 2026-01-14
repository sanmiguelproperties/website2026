{{-- Footer Público - San Miguel Properties --}}
{{-- Usa variables CSS dinámicas del frontend color system --}}
<footer class="relative text-white overflow-hidden" style="background-color: var(--fe-footer-background, #0f172a);">
    {{-- Decorative Background Elements - Usa variables CSS dinámicas --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_from, rgba(79, 70, 229, 0.2));"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl" style="background-color: var(--fe-footer-accent_to, rgba(16, 185, 129, 0.2));"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.03)_1px,transparent_0)] [background-size:32px_32px]"></div>
    </div>

    <div class="relative">
        {{-- Newsletter Section - Usa variables CSS dinámicas --}}
        <div class="border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6" style="background: linear-gradient(to right, var(--fe-footer-newsletter_badge_from, rgba(79,70,229,0.2)), var(--fe-footer-newsletter_badge_to, rgba(16,185,129,0.2))); color: var(--fe-footer-newsletter_badge_text, #34d399);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Mantente actualizado
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold mb-4">
                        Suscríbete a nuestro 
                        <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--fe-footer-newsletter_title_from, #818cf8), var(--fe-footer-newsletter_title_to, #34d399));">newsletter</span>
                    </h3>
                    <p class="text-slate-400 mb-8 max-w-xl mx-auto">
                        Recibe las últimas propiedades, ofertas exclusivas y consejos inmobiliarios directamente en tu correo.
                    </p>
                    <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                        <div class="relative flex-1">
                            <input type="email" 
                                   placeholder="tu@correo.com" 
                                   class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-500">
                                <svg class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <button type="submit" class="px-6 py-3.5 rounded-xl font-semibold text-white hover:shadow-lg hover:shadow-indigo-500/25 transition-all duration-300 hover:scale-105" style="background: linear-gradient(to right, var(--fe-footer-newsletter_button_from, #4f46e5), var(--fe-footer-newsletter_button_to, #10b981));">
                            Suscribirse
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Main Footer Content --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                {{-- Brand Column - Usa variables CSS dinámicas --}}
                <div class="lg:col-span-1">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 mb-6 group">
                        <div class="grid h-12 w-12 place-items-center rounded-xl text-white shadow-lg transition-transform duration-300 group-hover:scale-105" style="background: linear-gradient(to bottom right, var(--fe-footer-accent_from, #4f46e5), var(--fe-footer-accent_to, #10b981));">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18" />
                                <path d="M6 21V7a2 2 0 0 1 2-2h3" />
                                <path d="M11 21V11a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v10" />
                                <path d="M9 9h2" />
                                <path d="M9 13h2" />
                                <path d="M9 17h2" />
                                <path d="M15 13h2" />
                                <path d="M15 17h2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold tracking-tight">San Miguel</p>
                            <p class="text-sm text-slate-400">Properties</p>
                        </div>
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Tu socio de confianza en el mercado inmobiliario. Más de 15 años ayudando a familias a encontrar el hogar de sus sueños.
                    </p>
                    {{-- Social Links - Usa variables CSS dinámicas --}}
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 transition-all duration-300 footer-social-facebook" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 transition-all duration-300 footer-social-instagram" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 transition-all duration-300 footer-social-twitter" aria-label="Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 transition-all duration-300 footer-social-whatsapp" aria-label="WhatsApp">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 transition-all duration-300 footer-social-linkedin" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Quick Links - Usa variables CSS dinámicas --}}
                <div>
                    <h4 class="text-white font-semibold text-lg mb-6 flex items-center gap-2">
                        <span class="w-8 h-0.5 rounded-full" style="background: linear-gradient(to right, var(--fe-footer-accent_from, #4f46e5), var(--fe-footer-accent_to, #10b981));"></span>
                        Enlaces Rápidos
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="#propiedades" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_1, #6366f1);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Todas las Propiedades
                            </a>
                        </li>
                        <li>
                            <a href="#venta" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_1, #6366f1);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Propiedades en Venta
                            </a>
                        </li>
                        <li>
                            <a href="#renta" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_1, #6366f1);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Propiedades en Renta
                            </a>
                        </li>
                        <li>
                            <a href="#nosotros" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_1, #6366f1);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Sobre Nosotros
                            </a>
                        </li>
                        <li>
                            <a href="#contacto" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_1, #6366f1);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Contacto
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Property Types - Usa variables CSS dinámicas --}}
                <div>
                    <h4 class="text-white font-semibold text-lg mb-6 flex items-center gap-2">
                        <span class="w-8 h-0.5 rounded-full" style="background: linear-gradient(to right, var(--fe-footer-accent_from, #4f46e5), var(--fe-footer-accent_to, #10b981));"></span>
                        Tipos de Propiedad
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_2, #10b981);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Casas
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_2, #10b981);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Departamentos
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_2, #10b981);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Terrenos
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_2, #10b981);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Locales Comerciales
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--fe-footer-link_arrow_2, #10b981);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Oficinas
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Contact Info - Usa variables CSS dinámicas --}}
                <div>
                    <h4 class="text-white font-semibold text-lg mb-6 flex items-center gap-2">
                        <span class="w-8 h-0.5 rounded-full" style="background: linear-gradient(to right, var(--fe-footer-accent_from, #4f46e5), var(--fe-footer-accent_to, #10b981));"></span>
                        Contáctanos
                    </h4>
                    <ul class="space-y-4">
                        <li>
                            <a href="tel:+525512345678" class="flex items-start gap-3 text-slate-400 hover:text-white transition-colors duration-200 group">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center group-hover:text-white transition-all duration-300 flex-shrink-0 footer-contact-phone" style="color: var(--fe-footer-contact_phone_icon, #10b981);">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-white">Teléfono</p>
                                    <p class="text-sm">+52 55 1234 5678</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@sanmiguelproperties.com" class="flex items-start gap-3 text-slate-400 hover:text-white transition-colors duration-200 group">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center group-hover:text-white transition-all duration-300 flex-shrink-0 footer-contact-email" style="color: var(--fe-footer-contact_email_icon, #6366f1);">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-white">Email</p>
                                    <p class="text-sm">info@sanmiguelproperties.com</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-start gap-3 text-slate-400">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center flex-shrink-0" style="color: var(--fe-footer-contact_location_icon, #6366f1);">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-white">Dirección</p>
                                    <p class="text-sm">Av. Principal #123, Col. Centro<br>Ciudad de México, CP 06000</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-start gap-3 text-slate-400">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center flex-shrink-0" style="color: var(--fe-footer-contact_hours_icon, #10b981);">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-white">Horario</p>
                                    <p class="text-sm">Lun - Vie: 9:00 AM - 7:00 PM<br>Sáb: 10:00 AM - 2:00 PM</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-slate-500 text-sm text-center md:text-left">
                        © {{ date('Y') }} San Miguel Properties. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-slate-500 hover:text-white text-sm transition-colors duration-200">
                            Términos y Condiciones
                        </a>
                        <a href="#" class="text-slate-500 hover:text-white text-sm transition-colors duration-200">
                            Política de Privacidad
                        </a>
                        <a href="#" class="text-slate-500 hover:text-white text-sm transition-colors duration-200">
                            Aviso Legal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

{{-- CSS para efectos hover en redes sociales usando variables CSS dinámicas --}}
<style>
    .footer-social-facebook:hover {
        background-color: var(--fe-footer-social_facebook_hover, #4f46e5);
        color: white;
    }
    .footer-social-instagram:hover {
        background: linear-gradient(to bottom right, var(--fe-footer-social_instagram_from, #9333ea), var(--fe-footer-social_instagram_to, #ec4899));
        color: white;
    }
    .footer-social-twitter:hover {
        background-color: var(--fe-footer-social_twitter_hover, #0ea5e9);
        color: white;
    }
    .footer-social-whatsapp:hover {
        background-color: var(--fe-footer-social_whatsapp_hover, #22c55e);
        color: white;
    }
    .footer-social-linkedin:hover {
        background-color: var(--fe-footer-social_linkedin_hover, #2563eb);
        color: white;
    }
    .footer-contact-phone:hover {
        background-color: var(--fe-footer-contact_phone_icon, #10b981);
    }
    .footer-contact-email:hover {
        background-color: var(--fe-footer-contact_email_icon, #6366f1);
    }
</style>
