@extends('layouts.app')

@section('title', 'Administrar Colores del Frontend')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Colores del Frontend</h1>
      <p class="text-[var(--c-muted)] mt-1">Personaliza los colores de las páginas públicas por vista</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-preview" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-elev)] text-[var(--c-text)] border border-[var(--c-border)] rounded-xl hover:bg-[var(--c-surface)] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Ver página de inicio
      </button>
      <button id="btn-create-config" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nueva Configuración
      </button>
    </div>
  </div>

  <!-- View Selector Tabs -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] mb-6">
    <div class="border-b border-[var(--c-border)] overflow-x-auto">
      <div class="flex min-w-max p-2 gap-2" id="view-tabs">
        <!-- View tabs will be generated dynamically -->
      </div>
    </div>
    <div class="p-4">
      <div id="view-info" class="flex items-center justify-between">
        <div>
          <h3 id="view-title" class="text-lg font-semibold text-[var(--c-text)]">Cargando...</h3>
          <p id="view-description" class="text-sm text-[var(--c-muted)]"></p>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm text-[var(--c-muted)]">Grupos de colores:</span>
          <span id="view-groups-count" class="px-2 py-1 bg-[var(--c-elev)] rounded-lg text-sm font-medium">0</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Config Selector and Info -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <label for="config-selector" class="text-sm font-medium text-[var(--c-text)]">Configuración:</label>
        <select id="config-selector" class="px-4 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-[var(--c-text)] min-w-[200px]">
          <option value="">Cargando...</option>
        </select>
        <span id="active-badge" class="hidden px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">
          ✓ Activa
        </span>
      </div>
      <div class="flex gap-2">
        <button id="btn-activate" class="px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50" disabled>
          Activar
        </button>
        <button id="btn-reset-defaults" class="px-3 py-2 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
          Restablecer defaults
        </button>
        <button id="btn-duplicate" class="px-3 py-2 text-sm bg-[var(--c-elev)] text-[var(--c-text)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
          Duplicar
        </button>
        <button id="btn-delete" class="px-3 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition disabled:opacity-50" disabled>
          Eliminar
        </button>
      </div>
    </div>
  </div>

  <!-- Color Editor -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <!-- Color Groups Tabs Navigation -->
    <div class="border-b border-[var(--c-border)] overflow-x-auto">
      <div class="flex min-w-max" id="color-tabs">
        <!-- Tabs will be generated dynamically -->
      </div>
    </div>

    <!-- Tab Content -->
    <div id="color-groups-container" class="p-6">
      <!-- Color groups will be loaded here -->
      <div class="text-center py-12 text-[var(--c-muted)]">
        <svg class="w-12 h-12 mx-auto mb-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Cargando colores...
      </div>
    </div>

    <!-- Save Button -->
    <div class="p-6 border-t border-[var(--c-border)] bg-[var(--c-elev)]/50">
      <div class="flex items-center justify-between">
        <p class="text-sm text-[var(--c-muted)]">
          Los cambios se aplicarán a la vista seleccionada
        </p>
        <div class="flex gap-3">
          <button id="btn-cancel-changes" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">
            Cancelar cambios
          </button>
          <button id="btn-save-colors" class="inline-flex items-center gap-2 px-6 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar Cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create/Rename Config Modal -->
<div id="config-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-20 w-full max-w-md">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="config-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Nueva Configuración</h3>
      </div>
      <form id="config-form" class="p-6">
        <div class="mb-4">
          <label for="config-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre</label>
          <input type="text" id="config-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required placeholder="Ej: Tema Navideño">
        </div>
        <div class="mb-4">
          <label for="config-description" class="block text-sm font-medium text-[var(--c-text)] mb-1">Descripción</label>
          <textarea id="config-description" name="description" rows="3" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" placeholder="Descripción opcional"></textarea>
        </div>
        <div class="mb-6">
          <label class="block text-sm font-medium text-[var(--c-text)] mb-1">Vista</label>
          <p id="config-view-name" class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-[var(--c-muted)]"></p>
          <input type="hidden" id="config-view-slug" name="view_slug">
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" id="btn-cancel-modal" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Duplicate Modal -->
<div id="duplicate-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-20 w-full max-w-md">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 class="text-lg font-semibold text-[var(--c-text)]">Duplicar Configuración</h3>
      </div>
      <form id="duplicate-form" class="p-6">
        <div class="mb-6">
          <label for="duplicate-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre para la copia</label>
          <input type="text" id="duplicate-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required placeholder="Ej: Copia de Default">
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" id="btn-cancel-duplicate" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Duplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api/frontend-colors';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;

  // Verificar token
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido.');
    return;
  }

  // State
  let availableViews = {};
  let currentViewSlug = 'global';
  let configs = [];
  let currentConfigId = null;
  let currentConfig = null;
  let colorGroups = {};
  let modifiedColors = {};
  let activeColorTab = null;

  // Color group labels
  const groupLabels = {
    'primary': 'Primarios',
    'buttons': 'Botones Globales',
    'header': 'Header',
    'footer': 'Footer',
    'ui': 'UI General',
    'pagination': 'Paginación',
    'hero': 'Hero/Slider',
    'stats': 'Estadísticas',
    'services': 'Servicios',
    'features': 'Características',
    'cta_sale': 'CTA Venta',
    'cta_rent': 'CTA Renta',
    'process': 'Proceso',
    'testimonials': 'Testimonios',
    'about': 'Nosotros',
    'contact': 'Contacto',
    'properties': 'Propiedades',
    'property_cards': 'Propiedades',
    'filters': 'Filtros',
    'property_detail': 'Detalle Propiedad',
    'gallery': 'Galería',
    'agent_card': 'Tarjeta Agente',
    'contact_page': 'Página Contacto',
    'about_page': 'Página Nosotros'
  };

  // Color key labels (human readable)
  const colorKeyLabels = {
    'from': 'Color inicial',
    'to': 'Color final',
    'via': 'Color intermedio',
    'primary_bg': 'Botón primario - fondo',
    'primary_text': 'Botón primario - texto',
    'primary_hover_bg': 'Botón primario - hover',
    'primary_border': 'Botón primario - borde',
    'secondary_bg': 'Botón secundario - fondo',
    'secondary_text': 'Botón secundario - texto',
    'secondary_hover_bg': 'Botón secundario - hover',
    'secondary_border': 'Botón secundario - borde',
    'success_bg': 'Botón éxito - fondo',
    'success_text': 'Botón éxito - texto',
    'success_hover_bg': 'Botón éxito - hover',
    'title_gradient_from': 'Título - inicio',
    'title_gradient_via': 'Título - medio',
    'title_gradient_to': 'Título - fin',
    'overlay_from': 'Overlay - inicio',
    'overlay_via': 'Overlay - medio',
    'overlay_to': 'Overlay - fin',
    'badge_bg': 'Badge - fondo',
    'badge_dot': 'Badge - punto',
    'badge_text': 'Badge - texto',
    'search_bar_bg': 'Búsqueda - fondo',
    'search_bg': 'Búsqueda - fondo general',
    'search_border': 'Búsqueda - borde',
    'search_focus_border': 'Búsqueda - borde focus',
    'search_focus': 'Búsqueda - focus',
    'quick_filter_bg': 'Filtros rápidos - fondo',
    'quick_filter_border': 'Filtros rápidos - borde',
    'properties_from': 'Propiedades - inicio',
    'properties_to': 'Propiedades - fin',
    'experience_from': 'Experiencia - inicio',
    'experience_to': 'Experiencia - fin',
    'clients_from': 'Clientes - inicio',
    'clients_to': 'Clientes - fin',
    'zones_from': 'Zonas - inicio',
    'zones_to': 'Zonas - fin',
    'search_from': 'Búsqueda - inicio',
    'search_to': 'Búsqueda - fin',
    'search_hover_border': 'Búsqueda - hover borde',
    'security_from': 'Seguridad - inicio',
    'security_to': 'Seguridad - fin',
    'security_hover_border': 'Seguridad - hover borde',
    'tours_from': 'Tours - inicio',
    'tours_to': 'Tours - fin',
    'tours_hover_border': 'Tours - hover borde',
    'advisors_from': 'Asesores - inicio',
    'advisors_to': 'Asesores - fin',
    'advisors_hover_border': 'Asesores - hover borde',
    'financing_from': 'Financiamiento - inicio',
    'financing_to': 'Financiamiento - fin',
    'financing_hover_border': 'Financiamiento - hover borde',
    'app_from': 'App - inicio',
    'app_to': 'App - fin',
    'app_hover_border': 'App - hover borde',
    'accent_from': 'Acento - inicio',
    'accent_to': 'Acento - fin',
    'button_from': 'Botón - inicio',
    'button_to': 'Botón - fin',
    'button_shadow': 'Botón - sombra',
    'decorative_border': 'Borde decorativo',
    'check_color': 'Color checks',
    'section_bg': 'Fondo sección',
    'bg': 'Fondo base',
    'border': 'Borde',
    'bg_from': 'Fondo - inicio',
    'bg_to': 'Fondo - fin',
    'subtitle': 'Subtítulo',
    'bubble_1': 'Burbuja 1',
    'bubble_2': 'Burbuja 2',
    'step1_from': 'Paso 1 - inicio',
    'step1_to': 'Paso 1 - fin',
    'step2_from': 'Paso 2 - inicio',
    'step2_to': 'Paso 2 - fin',
    'step3_from': 'Paso 3 - inicio',
    'step3_to': 'Paso 3 - fin',
    'step4_from': 'Paso 4 - inicio',
    'step4_to': 'Paso 4 - fin',
    'card_bg': 'Tarjeta - fondo',
    'card_border': 'Tarjeta - borde',
    'star_color': 'Color estrellas',
    'quote_1': 'Cita 1',
    'quote_2': 'Cita 2',
    'quote_3': 'Cita 3',
    'avatar_1_from': 'Avatar 1 - inicio',
    'avatar_1_to': 'Avatar 1 - fin',
    'avatar_2_from': 'Avatar 2 - inicio',
    'avatar_2_to': 'Avatar 2 - fin',
    'avatar_3_from': 'Avatar 3 - inicio',
    'avatar_3_to': 'Avatar 3 - fin',
    'section_bg_from': 'Sección fondo - inicio',
    'section_bg_to': 'Sección fondo - fin',
    'title_highlight_from': 'Título destacado - inicio',
    'title_highlight_to': 'Título destacado - fin',
    'floating_card_gradient_from': 'Card flotante - inicio',
    'floating_card_gradient_to': 'Card flotante - fin',
    'check_1_bg': 'Check 1 - fondo',
    'check_1_text': 'Check 1 - texto',
    'check_2_bg': 'Check 2 - fondo',
    'check_2_text': 'Check 2 - texto',
    'check_3_bg': 'Check 3 - fondo',
    'check_3_text': 'Check 3 - texto',
    'phone_icon_from': 'Icono teléfono - inicio',
    'phone_icon_to': 'Icono teléfono - fin',
    'whatsapp_icon_from': 'Icono WhatsApp - inicio',
    'whatsapp_icon_to': 'Icono WhatsApp - fin',
    'email_icon_from': 'Icono email - inicio',
    'email_icon_to': 'Icono email - fin',
    'info_icon_phone_from': 'Contacto pagina - icono telefono inicio',
    'info_icon_phone_to': 'Contacto pagina - icono telefono fin',
    'info_icon_whatsapp_from': 'Contacto pagina - icono WhatsApp inicio',
    'info_icon_whatsapp_to': 'Contacto pagina - icono WhatsApp fin',
    'info_icon_email_from': 'Contacto pagina - icono email inicio',
    'info_icon_email_to': 'Contacto pagina - icono email fin',
    'card_value': 'Contacto pagina - valor general',
    'card_value_phone': 'Contacto pagina - valor telefono',
    'card_value_whatsapp': 'Contacto pagina - valor WhatsApp',
    'card_value_email': 'Contacto pagina - valor email',
    'social_title': 'Contacto pagina - titulo redes',
    'social_icon': 'Contacto pagina - icono redes',
    'section_text': 'Contacto pagina - texto seccion',
    'form_title': 'Contacto pagina - titulo formulario',
    'form_subtitle': 'Contacto pagina - subtitulo formulario',
    'alert_success_bg': 'Contacto pagina - alerta exito fondo',
    'alert_success_text': 'Contacto pagina - alerta exito texto',
    'alert_error_bg': 'Contacto pagina - alerta error fondo',
    'alert_error_text': 'Contacto pagina - alerta error texto',
    'required_mark': 'Contacto pagina - asterisco requerido',
    'checkbox_border': 'Contacto pagina - checkbox borde',
    'checkbox_accent': 'Contacto pagina - checkbox color',
    'faq_bg_from': 'Contacto pagina - FAQ fondo inicio',
    'faq_bg_to': 'Contacto pagina - FAQ fondo fin',
    'faq_title': 'Contacto pagina - FAQ titulo',
    'faq_border': 'Contacto pagina - FAQ borde',
    'faq_question': 'Contacto pagina - FAQ pregunta',
    'faq_icon': 'Contacto pagina - FAQ icono',
    'faq_answer': 'Contacto pagina - FAQ respuesta',
    'faq_item_bg': 'Contacto pagina - FAQ item fondo',
    'faq_item_open_from': 'Contacto pagina - FAQ abierto inicio',
    'faq_item_open_to': 'Contacto pagina - FAQ abierto fin',
    'form_focus_border': 'Form - borde focus',
    'checkbox_color': 'Checkbox color',
    'background': 'Fondo',
    'newsletter_badge_from': 'Newsletter badge - inicio',
    'newsletter_badge_to': 'Newsletter badge - fin',
    'newsletter_badge_text': 'Newsletter badge - texto',
    'newsletter_title_from': 'Newsletter título - inicio',
    'newsletter_title_to': 'Newsletter título - fin',
    'newsletter_button_from': 'Newsletter botón - inicio',
    'newsletter_button_to': 'Newsletter botón - fin',
    'social_facebook_hover': 'Facebook - hover',
    'social_instagram_from': 'Instagram - inicio',
    'social_instagram_to': 'Instagram - fin',
    'social_twitter_hover': 'Twitter - hover',
    'social_whatsapp_hover': 'WhatsApp - hover',
    'social_linkedin_hover': 'LinkedIn - hover',
    'link_arrow_1': 'Flecha enlaces 1',
    'link_arrow_2': 'Flecha enlaces 2',
    'contact_phone_icon': 'Contacto - teléfono',
    'contact_email_icon': 'Contacto - email',
    'contact_location_icon': 'Contacto - ubicación',
    'contact_hours_icon': 'Contacto - horario',
    'logo_gradient_from': 'Logo - inicio',
    'logo_gradient_to': 'Logo - fin',
    'cta_button_from': 'CTA botón - inicio',
    'cta_button_to': 'CTA botón - fin',
    'nav_hover': 'Navegación - hover',
    'mobile_menu_icon_active': 'Menú móvil - activo',
    'price_from': 'Precio - inicio',
    'price_to': 'Precio - fin',
    'sale_badge': 'Badge venta',
    'rent_badge': 'Badge renta',
    'favorite_hover': 'Favorito - hover',
    'title_hover': 'Título - hover',
    'active_from': 'Activo - inicio',
    'active_to': 'Activo - fin',
    'focus_border': 'Borde focus',
    'focus_ring': 'Ring focus',
    'hover_bg': 'Hover fondo',
    'back_to_top_from': 'Volver arriba - inicio',
    'back_to_top_to': 'Volver arriba - fin',
    'preloader_border_1': 'Preloader borde 1',
    'preloader_border_2': 'Preloader borde 2',
    'scrollbar_from': 'Scrollbar - inicio',
    'scrollbar_to': 'Scrollbar - fin',
    'scrollbar_hover_from': 'Scrollbar hover - inicio',
    'scrollbar_hover_to': 'Scrollbar hover - fin',
    'background_scrolled': 'Header - fondo con scroll',
    'background_top': 'Header - fondo sin scroll',
    'shadow': 'Header - sombra',
    'brand_text_scrolled': 'Marca - texto con scroll',
    'brand_text_top': 'Marca - texto sin scroll',
    'nav_text_scrolled': 'Nav - texto con scroll',
    'nav_text_top': 'Nav - texto sin scroll',
    'nav_text_top_hover': 'Nav - hover sin scroll',
    'nav_hover_bg': 'Nav - fondo hover',
    'cta_ring': 'CTA - color de anillo',
    'dropdown_bg': 'Dropdown - fondo',
    'dropdown_border': 'Dropdown - borde',
    'dropdown_shadow': 'Dropdown - sombra',
    'dropdown_title': 'Dropdown - titulo',
    'dropdown_text': 'Dropdown - texto',
    'dropdown_text_hover': 'Dropdown - texto hover',
    'dropdown_hover_bg': 'Dropdown - fondo hover',
    'dropdown_icon': 'Dropdown - icono',
    'dropdown_icon_hover_bg': 'Dropdown - icono fondo hover',
    'dropdown_icon_hover': 'Dropdown - icono hover',
    'dropdown_tag_bg': 'Dropdown - tag fondo',
    'dropdown_tag_border': 'Dropdown - tag borde',
    'dropdown_tag_text': 'Dropdown - tag texto',
    'dropdown_tag_border_hover': 'Dropdown - tag borde hover',
    'dropdown_tag_text_hover': 'Dropdown - tag texto hover',
    'lang_text_scrolled': 'Idioma - texto con scroll',
    'lang_border_scrolled': 'Idioma - borde con scroll',
    'lang_text_top': 'Idioma - texto sin scroll',
    'lang_border_top': 'Idioma - borde sin scroll',
    'phone_text_scrolled': 'Telefono - texto con scroll',
    'phone_text_top': 'Telefono - texto sin scroll',
    'favorites_bg_scrolled': 'Favoritas - fondo con scroll',
    'favorites_border_scrolled': 'Favoritas - borde con scroll',
    'favorites_text_scrolled': 'Favoritas - texto con scroll',
    'favorites_bg_top': 'Favoritas - fondo sin scroll',
    'favorites_bg_top_hover': 'Favoritas - fondo hover sin scroll',
    'favorites_border_top': 'Favoritas - borde sin scroll',
    'favorites_text_top': 'Favoritas - texto sin scroll',
    'mobile_toggle_text_scrolled': 'Menu movil - texto con scroll',
    'mobile_toggle_text_top': 'Menu movil - texto sin scroll',
    'mobile_toggle_hover_bg': 'Menu movil - fondo hover',
    'mobile_panel_bg': 'Menu movil - fondo panel',
    'mobile_panel_border': 'Menu movil - borde panel',
    'mobile_link_text': 'Menu movil - texto enlace',
    'mobile_link_hover_bg': 'Menu movil - fondo enlace hover',
    'mobile_section_bg': 'Menu movil - fondo seccion',
    'mobile_section_border': 'Menu movil - borde seccion',
    'mobile_section_hover_bg': 'Menu movil - fondo seccion hover',
    'mobile_section_title': 'Menu movil - titulo seccion',
    'mobile_section_title_muted': 'Menu movil - titulo secundario',
    'pattern': 'Patron',
    'divider': 'Divisor',
    'text_primary': 'Texto primario',
    'text_secondary': 'Texto secundario',
    'text_muted': 'Texto tenue',
    'input_bg': 'Input - fondo',
    'input_border': 'Input - borde',
    'input_text': 'Input - texto',
    'input_placeholder': 'Input - placeholder',
    'social_bg': 'Social - fondo',
    'social_text': 'Social - texto',
    'social_hover_bg': 'Social - fondo hover',
    'social_hover_text': 'Social - texto hover',
    'link_text': 'Link - texto',
    'link_hover': 'Link - hover',
    'copyright_text': 'Copyright - texto',
    'body_bg': 'Body - fondo',
    'body_text': 'Body - texto',
    'text_white': 'Blanco',
    'text_white_90': 'Blanco 90%',
    'text_white_80': 'Blanco 80%',
    'text_white_70': 'Blanco 70%',
    'text_white_60': 'Blanco 60%',
    'bg_white_5': 'Blanco fondo 5%',
    'bg_white_10': 'Blanco fondo 10%',
    'bg_white_20': 'Blanco fondo 20%',
    'border_white_10': 'Blanco borde 10%',
    'border_white_20': 'Blanco borde 20%',
    'border_white_30': 'Blanco borde 30%',
    'slate_900': 'Slate 900',
    'slate_800': 'Slate 800',
    'slate_700': 'Slate 700',
    'slate_600': 'Slate 600',
    'slate_500': 'Slate 500',
    'slate_400': 'Slate 400',
    'slate_300': 'Slate 300',
    'slate_200': 'Slate 200',
    'slate_100': 'Slate 100',
    'slate_50': 'Slate 50',
    'slate_50_60': 'Slate 50 (60%)',
    'slate_100_70': 'Slate 100 (70%)',
    'preloader_bg': 'Preloader - fondo',
    'preloader_track': 'Preloader - pista',
    'scrollbar_track': 'Scrollbar - pista',
    'glass_bg': 'Glass - fondo',
    'swiper_bullet': 'Swiper - bullet inactivo',
    'swiper_nav_bg': 'Swiper - fondo controles',
    'swiper_nav_text': 'Swiper - texto controles',
    'card_hover_shadow': 'Tarjeta - sombra hover',
    'skeleton_from': 'Skeleton - inicio',
    'skeleton_mid': 'Skeleton - medio',
    'skeleton_to': 'Skeleton - fin',
    'back_to_top_ring': 'Volver arriba - anillo',
    'favorites_active_text': 'Favorito activo - texto',
    'favorites_active_bg': 'Favorito activo - fondo',
    'favorites_active_border': 'Favorito activo - borde',
    'favorites_active_ring': 'Favorito activo - anillo',
    'placeholder_from': 'Placeholder - inicio',
    'placeholder_to': 'Placeholder - fin',
    'placeholder_text': 'Placeholder - texto',
    'subtitle_text': 'Subtitulo',
    'search_icon': 'Busqueda - icono',
    'search_input_bg': 'Busqueda - input fondo',
    'search_input_border': 'Busqueda - input borde',
    'search_input_text': 'Busqueda - input texto',
    'search_input_placeholder': 'Busqueda - input placeholder',
    'quick_filter_text': 'Filtro rapido - texto',
    'quick_filter_hover_bg': 'Filtro rapido - fondo hover',
    'scroll_text': 'Scroll - texto',
    'scroll_text_hover': 'Scroll - texto hover',
    'title': 'Titulo',
    'text': 'Texto',
    'stat_value': 'Estadistica - valor',
    'stat_label': 'Estadistica - etiqueta',
    'btn_primary_from': 'Boton primario - inicio',
    'btn_primary_to': 'Boton primario - fin',
    'btn_secondary_bg': 'Boton secundario - fondo',
    'btn_secondary_border': 'Boton secundario - borde',
    'btn_secondary_text': 'Boton secundario - texto',
    'btn_secondary_hover_bg': 'Boton secundario - fondo hover',
    'decor': 'Decorativo - fondo',
    'decor_border': 'Decorativo - borde',
    'feature_text': 'Feature - texto',
    'filter_bg': 'Filtro - fondo',
    'filter_border': 'Filtro - borde',
    'filter_icon': 'Filtro - icono',
    'filter_clear': 'Filtro - limpiar',
    'filter_label': 'Filtro - etiqueta',
    'filter_label_muted': 'Filtro - etiqueta secundaria',
    'filter_results_text': 'Filtro - resultados texto',
    'filter_count_bg': 'Filtro - contador fondo',
    'filter_count_text': 'Filtro - contador texto',
    'filter_clear_bg': 'Filtro - limpiar fondo',
    'filter_clear_border': 'Filtro - limpiar borde',
    'filter_clear_text': 'Filtro - limpiar texto',
    'filter_clear_hover_bg': 'Filtro - limpiar hover',
    'filter_divider': 'Filtro - divisor',
    'modal_backdrop': 'Modal - fondo oscurecido',
    'modal_bg': 'Modal - fondo',
    'modal_header_bg_from': 'Modal header - inicio',
    'modal_header_bg_to': 'Modal header - fin',
    'modal_header_border': 'Modal header - borde',
    'modal_title': 'Modal - titulo',
    'modal_subtitle': 'Modal - subtitulo',
    'modal_close_icon': 'Modal - icono cerrar',
    'modal_close_hover_bg': 'Modal - cerrar hover',
    'modal_footer_bg': 'Modal footer - fondo',
    'modal_footer_border': 'Modal footer - borde',
    'modal_clear_bg': 'Modal limpiar - fondo',
    'modal_clear_border': 'Modal limpiar - borde',
    'modal_clear_text': 'Modal limpiar - texto',
    'modal_clear_hover_bg': 'Modal limpiar - hover',
    'tag_type_bg': 'Tag tipo - fondo',
    'tag_type_text': 'Tag tipo - texto',
    'tag_type_remove_hover': 'Tag tipo - quitar hover',
    'tag_operation_bg': 'Tag operacion - fondo',
    'tag_operation_text': 'Tag operacion - texto',
    'tag_operation_remove_hover': 'Tag operacion - quitar hover',
    'tag_bedrooms_bg': 'Tag recamaras - fondo',
    'tag_bedrooms_text': 'Tag recamaras - texto',
    'tag_bedrooms_remove_hover': 'Tag recamaras - quitar hover',
    'tag_bathrooms_bg': 'Tag banos - fondo',
    'tag_bathrooms_text': 'Tag banos - texto',
    'tag_bathrooms_remove_hover': 'Tag banos - quitar hover',
    'tag_price_bg': 'Tag precio - fondo',
    'tag_price_text': 'Tag precio - texto',
    'tag_price_remove_hover': 'Tag precio - quitar hover',
    'tag_city_bg': 'Tag ciudad - fondo',
    'tag_city_text': 'Tag ciudad - texto',
    'tag_city_remove_hover': 'Tag ciudad - quitar hover',
    'mobile_count_bg': 'Movil contador - fondo',
    'mobile_count_text': 'Movil contador - texto',
    'option_inactive_bg': 'Opcion filtro - fondo inactivo',
    'option_inactive_text': 'Opcion filtro - texto inactivo',
    'option_inactive_hover_bg': 'Opcion filtro - hover inactivo',
    'option_type_active_bg': 'Opcion tipo - fondo activo',
    'option_type_active_text': 'Opcion tipo - texto activo',
    'option_type_active_ring': 'Opcion tipo - anillo activo',
    'option_operation_active_bg': 'Opcion operacion - fondo activo',
    'option_operation_active_text': 'Opcion operacion - texto activo',
    'option_operation_active_ring': 'Opcion operacion - anillo activo',
    'option_feature_active_bg': 'Opcion caracteristica - fondo activo',
    'option_feature_active_text': 'Opcion caracteristica - texto activo',
    'option_feature_active_ring': 'Opcion caracteristica - anillo activo',
    'card_title': 'Tarjeta - titulo',
    'card_text': 'Tarjeta - texto',
    'card_location': 'Tarjeta - ubicacion',
    'card_meta': 'Tarjeta - metadatos',
    'card_divider': 'Tarjeta - divisor',
    'tag_active_from': 'Tag activo - inicio',
    'tag_active_to': 'Tag activo - fin',
    'tag_inactive_bg': 'Tag inactivo - fondo',
    'tag_inactive_text': 'Tag inactivo - texto',
    'tag_inactive_hover': 'Tag inactivo - hover',
    'fav_btn_bg': 'Favorito - fondo boton',
    'fav_btn_icon': 'Favorito - icono boton',
    'step_title': 'Proceso - titulo de paso',
    'image_caption_title': 'Imagen - titulo',
    'image_caption_subtitle': 'Imagen - subtitulo',
    'privacy_check_border': 'Privacidad - borde checkbox',
    'privacy_text': 'Privacidad - texto',
    'label': 'Etiqueta',
    'link': 'Enlace',
    'method_bg': 'Metodo - fondo',
    'method_title': 'Metodo - titulo',
    'method_text': 'Metodo - texto',
    'form_bg_from': 'Formulario - fondo inicio',
    'form_bg_to': 'Formulario - fondo fin',
    'form_border': 'Formulario - borde',
    'phone_from': 'Telefono - inicio',
    'phone_to': 'Telefono - fin',
    'whatsapp_from': 'WhatsApp - inicio',
    'whatsapp_to': 'WhatsApp - fin',
    'email_from': 'Email - inicio',
    'email_to': 'Email - fin',
    'skeleton_bg': 'Skeleton - fondo tarjeta',
    'skeleton_border': 'Skeleton - borde tarjeta',
    'empty_icon_bg': 'Empty - icono fondo',
    'empty_icon': 'Empty - icono',
    'empty_title': 'Empty - titulo',
    'empty_text': 'Empty - texto',
    'pagination_border': 'Paginacion - borde',
    'pagination_bg': 'Paginacion - fondo',
    'pagination_text': 'Paginacion - texto',
    'pagination_hover_bg': 'Paginacion - fondo hover',
    'pagination_ellipsis': 'Paginacion - puntos',
    'fav_btn_border': 'Favorito - borde boton',
    'image_overlay': 'Imagen - overlay',
    'avatar1_from': 'Avatar 1 - inicio',
    'avatar1_to': 'Avatar 1 - fin',
    'avatar2_from': 'Avatar 2 - inicio',
    'avatar2_to': 'Avatar 2 - fin',
    'avatar3_from': 'Avatar 3 - inicio',
    'avatar3_to': 'Avatar 3 - fin',
    'badge_rent': 'Badge renta',
    'badge_sale': 'Badge venta',
    'card_bg_from': 'Tarjeta - fondo inicio',
    'card_bg_to': 'Tarjeta - fondo fin',
    'contact_button_from': 'Contacto - boton inicio',
    'contact_button_to': 'Contacto - boton fin',
    'email_icon': 'Email - icono',
    'feature1_from': 'Caracteristica 1 - inicio',
    'feature1_to': 'Caracteristica 1 - fin',
    'feature1_glow': 'Caracteristica 1 - brillo',
    'feature2_from': 'Caracteristica 2 - inicio',
    'feature2_to': 'Caracteristica 2 - fin',
    'feature2_glow': 'Caracteristica 2 - brillo',
    'feature3_from': 'Caracteristica 3 - inicio',
    'feature3_to': 'Caracteristica 3 - fin',
    'feature3_glow': 'Caracteristica 3 - brillo',
    'feature4_from': 'Caracteristica 4 - inicio',
    'feature4_to': 'Caracteristica 4 - fin',
    'feature4_glow': 'Caracteristica 4 - brillo',
    'feature5_from': 'Caracteristica 5 - inicio',
    'feature5_to': 'Caracteristica 5 - fin',
    'feature5_glow': 'Caracteristica 5 - brillo',
    'feature6_from': 'Caracteristica 6 - inicio',
    'feature6_to': 'Caracteristica 6 - fin',
    'feature6_glow': 'Caracteristica 6 - brillo',
    'feature_icon': 'Caracteristica - icono',
    'fullscreen_bg': 'Pantalla completa - fondo',
    'glow1': 'Brillo 1',
    'glow2': 'Brillo 2',
    'hero_bg_from': 'Hero - fondo inicio',
    'hero_bg_via': 'Hero - fondo medio',
    'hero_bg_to': 'Hero - fondo fin',
    'hero_pattern_dot': 'Hero - patron puntos',
    'hero_badge_bg': 'Hero - badge fondo',
    'hero_badge_text': 'Hero - badge texto',
    'hero_title': 'Hero - titulo',
    'hero_highlight_from': 'Hero - destacado inicio',
    'hero_highlight_to': 'Hero - destacado fin',
    'hero_subtitle': 'Hero - subtitulo',
    'hero_secondary_cta_bg': 'Hero - CTA secundario fondo',
    'hero_secondary_cta_text': 'Hero - CTA secundario texto',
    'hero_secondary_cta_border': 'Hero - CTA secundario borde',
    'highlight_from': 'Resaltado - inicio',
    'highlight_to': 'Resaltado - fin',
    'info_icon_email': 'Info - icono email',
    'info_icon_location': 'Info - icono ubicacion',
    'info_icon_phone': 'Info - icono telefono',
    'map_marker': 'Mapa - marcador',
    'name': 'Nombre',
    'name_color': 'Nombre - color',
    'nav_button_bg': 'Navegacion - boton fondo',
    'nav_button_hover': 'Navegacion - boton hover',
    'phone_icon': 'Telefono - icono',
    'quote1': 'Cita 1',
    'quote2': 'Cita 2',
    'quote3': 'Cita 3',
    'role': 'Rol',
    'section_title': 'Seccion - titulo',
    'summary_section_bg': 'Resumen - seccion fondo',
    'summary_badge_bg': 'Resumen - badge fondo',
    'summary_badge_text': 'Resumen - badge texto',
    'summary_media_border': 'Resumen - media borde',
    'summary_media_box_bg_from': 'Resumen - caja fondo inicio',
    'summary_media_box_bg_to': 'Resumen - caja fondo fin',
    'summary_direct_label': 'Resumen - etiqueta linea directa',
    'stars': 'Estrellas',
    'submit_button_from': 'Enviar - boton inicio',
    'submit_button_to': 'Enviar - boton fin',
    'values_section_bg_from': 'Valores - seccion fondo inicio',
    'values_section_bg_to': 'Valores - seccion fondo fin',
    'values_badge_bg': 'Valores - badge fondo',
    'values_badge_text': 'Valores - badge texto',
    'value_card_border': 'Valores - tarjeta borde',
    'value_card_bg_from': 'Valores - tarjeta fondo inicio',
    'value_card_bg_to': 'Valores - tarjeta fondo fin',
    'timeline_section_bg': 'Timeline - seccion fondo',
    'team_card_border': 'Equipo - tarjeta borde',
    'team_card_bg': 'Equipo - tarjeta fondo',
    'team_name': 'Equipo - nombre',
    'team_role': 'Equipo - rol',
    'team_section_bg_from': 'Equipo - seccion fondo inicio',
    'team_section_bg_to': 'Equipo - seccion fondo fin',
    'team_badge_bg': 'Equipo - badge fondo',
    'team_badge_text': 'Equipo - badge texto',
    'thumbnail_border_active': 'Miniatura - borde activo',
    'timeline_dot_active': 'Timeline - punto activo',
    'timeline_line': 'Timeline - linea',
    'timeline_card_border': 'Timeline - tarjeta borde',
    'timeline_card_bg_from': 'Timeline - tarjeta fondo inicio',
    'timeline_card_bg_to': 'Timeline - tarjeta fondo fin',
    'timeline_year': 'Timeline - año',
    'type_badge_bg': 'Tipo - badge fondo',
    'type_badge_text': 'Tipo - badge texto',
    'cta_section_bg': 'CTA - seccion fondo',
    'cta_box_border': 'CTA - caja borde',
    'cta_box_bg_from': 'CTA - caja fondo inicio',
    'cta_box_bg_to': 'CTA - caja fondo fin',
    'cta_secondary_btn_border': 'CTA secundario - borde',
    'cta_secondary_btn_text': 'CTA secundario - texto',
    'cta_secondary_btn_bg': 'CTA secundario - fondo',
    'value1_bg': 'Valor 1 - fondo',
    'value1_icon': 'Valor 1 - icono',
    'value2_bg': 'Valor 2 - fondo',
    'value2_icon': 'Valor 2 - icono',
    'value3_bg': 'Valor 3 - fondo',
    'value3_icon': 'Valor 3 - icono',
    'value_icon_1': 'Valor - icono 1',
    'value_icon_2': 'Valor - icono 2',
    'value_icon_3': 'Valor - icono 3',
    'value_text': 'Valor - texto',
    'value_title': 'Valor - titulo',
    'whatsapp_button_from': 'WhatsApp - boton inicio',
    'whatsapp_button_to': 'WhatsApp - boton fin',
    'whatsapp_button_text': 'WhatsApp - boton texto',
    'badge_sale_from': 'Badge venta - inicio',
    'badge_sale_to': 'Badge venta - fin',
    'badge_rent_from': 'Badge renta - inicio',
    'badge_rent_to': 'Badge renta - fin',
    'subsection_title': 'Subseccion - titulo',
    'meta_text': 'Meta - texto',
    'meta_icon': 'Meta - icono',
    'meta_divider': 'Meta - divisor',
    'breadcrumb_link': 'Breadcrumb - enlace',
    'breadcrumb_link_hover': 'Breadcrumb - enlace hover',
    'breadcrumb_separator': 'Breadcrumb - separador',
    'breadcrumb_current': 'Breadcrumb - actual',
    'panel_bg': 'Panel - fondo',
    'panel_border': 'Panel - borde',
    'panel_shadow': 'Panel - sombra',
    'chip_bg': 'Chip - fondo',
    'chip_text': 'Chip - texto',
    'map_bg': 'Mapa - fondo',
    'map_border': 'Mapa - borde',
    'note_bg': 'Nota - fondo',
    'note_border': 'Nota - borde',
    'note_label': 'Nota - etiqueta',
    'note_text': 'Nota - texto',
    'source_notice_bg': 'Aviso fuente - fondo',
    'source_notice_border': 'Aviso fuente - borde',
    'source_notice_label': 'Aviso fuente - etiqueta',
    'source_notice_text': 'Aviso fuente - texto',
    'error_bg': 'Error - fondo',
    'error_border': 'Error - borde',
    'error_title': 'Error - titulo',
    'error_text': 'Error - texto',
    'error_secondary_bg': 'Error secundario - fondo',
    'error_secondary_border': 'Error secundario - borde',
    'error_secondary_text': 'Error secundario - texto',
    'error_secondary_hover_bg': 'Error secundario - hover',
    'share_button_bg': 'Compartir - fondo',
    'share_button_border': 'Compartir - borde',
    'share_button_text': 'Compartir - texto',
    'share_button_hover_bg': 'Compartir - hover',
    'call_button_bg': 'Llamar - fondo',
    'call_button_border': 'Llamar - borde',
    'call_button_text': 'Llamar - texto',
    'call_button_icon': 'Llamar - icono',
    'call_button_hover_bg': 'Llamar - hover',
    'favorite_button_bg': 'Favorito - fondo',
    'favorite_button_border': 'Favorito - borde',
    'favorite_button_text': 'Favorito - texto',
    'favorite_button_hover_bg': 'Favorito - hover',
    'feature_tag_bg': 'Tag - fondo',
    'feature_tag_text': 'Tag - texto',
    'empty_state_bg': 'Estado vacio - fondo',
    'empty_state_border': 'Estado vacio - borde',
    'empty_state_text': 'Estado vacio - texto',
    'contact_button_text': 'Boton contacto - texto',
    'decor_glow_from': 'Decoracion brillo - inicio',
    'decor_glow_to': 'Decoracion brillo - fin',
    'decor_pattern_dot': 'Decoracion patron - punto',
    'shell_bg': 'Galeria - fondo',
    'count_badge_bg': 'Galeria contador - fondo',
    'count_badge_text': 'Galeria contador - texto',
    'thumbs_strip_border': 'Galeria miniaturas - borde superior',
    'thumbnail_bg': 'Miniatura - fondo',
    'thumbnail_border': 'Miniatura - borde',
    'avatar_bg': 'Avatar - fondo',
    'avatar_border': 'Avatar - borde',
    'avatar_icon': 'Avatar - icono',
    'subtitle_color': 'Subtitulo - color',
    'mls_card_bg': 'MLS tarjeta - fondo',
    'mls_card_border': 'MLS tarjeta - borde',
    'mls_name': 'MLS nombre',
    'mls_office': 'MLS oficina',
    'contact_link': 'Contacto enlace',
    'contact_link_hover': 'Contacto enlace hover',
    'contact_unavailable': 'Contacto no disponible',
    'primary_badge_from': 'Badge principal - inicio',
    'primary_badge_to': 'Badge principal - fin',
    'primary_badge_text': 'Badge principal - texto',
  };

  const BUTTON_PRESETS = {
    gold: {
      label: 'Gold',
      description: 'Dorado de marca + oliva',
      colors: {
        primary_bg: '#D1A054',
        primary_text: '#ffffff',
        primary_hover_bg: '#B8883F',
        primary_border: '#D1A054',
        secondary_bg: '#768D59',
        secondary_text: '#ffffff',
        secondary_hover_bg: '#627748',
        secondary_border: '#768D59',
        success_bg: '#22C55E',
        success_text: '#ffffff',
        success_hover_bg: '#16A34A',
        badge_bg: '#D1A054',
        badge_text: '#ffffff'
      }
    },
    green: {
      label: 'Green',
      description: 'Verde dominante + acento neutro',
      colors: {
        primary_bg: '#768D59',
        primary_text: '#ffffff',
        primary_hover_bg: '#627748',
        primary_border: '#768D59',
        secondary_bg: '#5B5B5B',
        secondary_text: '#ffffff',
        secondary_hover_bg: '#4A4A4A',
        secondary_border: '#5B5B5B',
        success_bg: '#22C55E',
        success_text: '#ffffff',
        success_hover_bg: '#16A34A',
        badge_bg: '#768D59',
        badge_text: '#ffffff'
      }
    },
    terracotta: {
      label: 'Terracotta',
      description: 'Terracota cálido + dorado',
      colors: {
        primary_bg: '#A52A2A',
        primary_text: '#ffffff',
        primary_hover_bg: '#8B2323',
        primary_border: '#A52A2A',
        secondary_bg: '#D1A054',
        secondary_text: '#ffffff',
        secondary_hover_bg: '#B8883F',
        secondary_border: '#D1A054',
        success_bg: '#768D59',
        success_text: '#ffffff',
        success_hover_bg: '#627748',
        badge_bg: '#A52A2A',
        badge_text: '#ffffff'
      }
    }
  };

  // Initialize
  loadViews();

  // Event Listeners
  document.getElementById('config-selector').addEventListener('change', handleConfigChange);
  document.getElementById('btn-create-config').addEventListener('click', () => openConfigModal());
  document.getElementById('btn-activate').addEventListener('click', activateConfig);
  document.getElementById('btn-reset-defaults').addEventListener('click', resetDefaults);
  document.getElementById('btn-duplicate').addEventListener('click', openDuplicateModal);
  document.getElementById('btn-delete').addEventListener('click', deleteConfig);
  document.getElementById('btn-save-colors').addEventListener('click', saveColors);
  document.getElementById('btn-cancel-changes').addEventListener('click', cancelChanges);
  document.getElementById('btn-preview').addEventListener('click', () => window.open('/', '_blank'));
  document.getElementById('config-form').addEventListener('submit', saveConfig);
  document.getElementById('duplicate-form').addEventListener('submit', duplicateConfig);
  document.getElementById('btn-cancel-modal').addEventListener('click', closeConfigModal);
  document.getElementById('btn-cancel-duplicate').addEventListener('click', closeDuplicateModal);

  // Functions
  async function loadViews() {
    try {
      const response = await fetch(`${API_BASE}/views`, {
        headers: {
          'Accept': 'application/json'
        }
      });
      const data = await response.json();

      if (data.success) {
        availableViews = data.data;
        renderViewTabs();
        // Load first view (global)
        switchView('global');
      }
    } catch (error) {
      console.error('Error loading views:', error);
      showError('Error', 'No se pudieron cargar las vistas');
    }
  }

  function renderViewTabs() {
    const tabs = document.getElementById('view-tabs');
    
    tabs.innerHTML = Object.entries(availableViews).map(([slug, info]) => `
      <button 
        onclick="window.colorApp.switchView('${slug}')"
        class="view-tab-btn px-4 py-2 text-sm font-medium rounded-xl transition-colors ${slug === currentViewSlug ? 'bg-[var(--c-primary)] text-[var(--c-primary-ink)]' : 'bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-border)]'}"
        data-view="${slug}">
        ${info.name}
      </button>
    `).join('');
  }

  async function switchView(viewSlug) {
    currentViewSlug = viewSlug;
    
    // Update view tabs
    document.querySelectorAll('.view-tab-btn').forEach(btn => {
      const isActive = btn.dataset.view === viewSlug;
      btn.classList.toggle('bg-[var(--c-primary)]', isActive);
      btn.classList.toggle('text-[var(--c-primary-ink)]', isActive);
      btn.classList.toggle('bg-[var(--c-elev)]', !isActive);
      btn.classList.toggle('text-[var(--c-text)]', !isActive);
    });

    // Update view info
    const viewInfo = availableViews[viewSlug];
    document.getElementById('view-title').textContent = viewInfo.name;
    document.getElementById('view-description').textContent = viewInfo.description;
    document.getElementById('view-groups-count').textContent = viewInfo.groups.length;

    // Load configs for this view
    await loadConfigsForView(viewSlug);
    await loadColorGroupsForView(viewSlug);
  }

  async function loadConfigsForView(viewSlug) {
    try {
      const response = await fetch(`${API_BASE}?view=${viewSlug}`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();

      if (data.success) {
        configs = data.data;
        renderConfigSelector();
      }
    } catch (error) {
      console.error('Error loading configs:', error);
      showError('Error', 'No se pudieron cargar las configuraciones');
    }
  }

  async function loadColorGroupsForView(viewSlug) {
    try {
      const response = await fetch(`${API_BASE}/groups/${viewSlug}`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();

      if (data.success) {
        colorGroups = data.data;
        renderColorTabs();
      }
    } catch (error) {
      console.error('Error loading groups:', error);
    }
  }

  function renderConfigSelector() {
    const selector = document.getElementById('config-selector');
    
    if (configs.length === 0) {
      selector.innerHTML = '<option value="">No hay configuraciones</option>';
      currentConfigId = null;
      currentConfig = null;
      updateButtonStates();
      return;
    }

    selector.innerHTML = configs.map(c => 
      `<option value="${c.id}" ${c.is_active ? 'selected' : ''}>${c.name}${c.is_active ? ' (Activa)' : ''}</option>`
    ).join('');

    // Select first or active config
    const activeConfig = configs.find(c => c.is_active) || configs[0];
    if (activeConfig) {
      currentConfigId = activeConfig.id;
      loadConfig(activeConfig.id);
    }
  }

  function renderColorTabs() {
    const tabs = document.getElementById('color-tabs');
    const groups = Object.keys(colorGroups);
    
    if (groups.length === 0) {
      tabs.innerHTML = '<div class="p-4 text-[var(--c-muted)]">No hay grupos de colores para esta vista</div>';
      return;
    }

    activeColorTab = activeColorTab && groups.includes(activeColorTab) ? activeColorTab : groups[0];
    
    tabs.innerHTML = groups.map(group => `
      <button 
        onclick="window.colorApp.switchColorTab('${group}')"
        class="color-tab-btn px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap ${group === activeColorTab ? 'border-[var(--c-primary)] text-[var(--c-primary)]' : 'border-transparent text-[var(--c-muted)] hover:text-[var(--c-text)]'}"
        data-tab="${group}">
        ${groupLabels[group] || group}
      </button>
    `).join('');
  }

  async function loadConfig(id) {
    try {
      const response = await fetch(`${API_BASE}/${id}`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();

      if (data.success) {
        currentConfig = data.data;
        currentConfigId = id;
        modifiedColors = JSON.parse(JSON.stringify(currentConfig.colors || {}));
        updateButtonStates();
        renderColorGroup(activeColorTab);
      }
    } catch (error) {
      console.error('Error loading config:', error);
    }
  }

  function handleConfigChange(e) {
    loadConfig(e.target.value);
  }

  function updateButtonStates() {
    const isActive = currentConfig?.is_active;
    document.getElementById('btn-activate').disabled = isActive || !currentConfig;
    document.getElementById('btn-delete').disabled = isActive || !currentConfig;
    document.getElementById('active-badge').classList.toggle('hidden', !isActive);
  }

  function switchColorTab(tab) {
    activeColorTab = tab;
    document.querySelectorAll('.color-tab-btn').forEach(btn => {
      const isActive = btn.dataset.tab === tab;
      btn.classList.toggle('border-[var(--c-primary)]', isActive);
      btn.classList.toggle('text-[var(--c-primary)]', isActive);
      btn.classList.toggle('border-transparent', !isActive);
      btn.classList.toggle('text-[var(--c-muted)]', !isActive);
    });
    renderColorGroup(tab);
  }

  function renderColorGroup(group) {
    const container = document.getElementById('color-groups-container');
    
    if (!group || !modifiedColors[group]) {
      container.innerHTML = `
        <div class="text-center py-12 text-[var(--c-muted)]">
          ${currentConfig ? 'No hay colores configurados para este grupo' : 'Selecciona una configuración'}
        </div>
      `;
      return;
    }

    const colors = modifiedColors[group];
    const showButtonPresets = currentViewSlug === 'global' && group === 'buttons';

    container.innerHTML = `
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-[var(--c-text)]">${groupLabels[group] || group}</h3>
        <p class="text-sm text-[var(--c-muted)]">Edita los colores de esta sección</p>
      </div>
      ${showButtonPresets ? createButtonPresetToolbar() : ''}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        ${Object.entries(colors).map(([key, value]) => createColorField(group, key, value)).join('')}
      </div>
    `;

    if (showButtonPresets) {
      attachButtonPresetEvents();
    }

    // Attach color picker events
    attachColorPickerEvents();
  }

  function createButtonPresetToolbar() {
    const presetsMarkup = Object.entries(BUTTON_PRESETS).map(([key, preset]) => `
      <button
        type="button"
        class="btn-color-preset inline-flex items-center gap-3 px-4 py-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] hover:bg-[var(--c-surface)] transition text-left"
        data-preset="${key}"
        title="Aplicar preset ${preset.label}">
        <span class="flex items-center gap-1">
          <span class="w-4 h-4 rounded-md border border-white/40" style="background-color: ${preset.colors.primary_bg};"></span>
          <span class="w-4 h-4 rounded-md border border-white/40" style="background-color: ${preset.colors.secondary_bg};"></span>
          <span class="w-4 h-4 rounded-md border border-white/40" style="background-color: ${preset.colors.success_bg};"></span>
        </span>
        <span>
          <span class="block text-sm font-semibold text-[var(--c-text)]">${preset.label}</span>
          <span class="block text-xs text-[var(--c-muted)]">${preset.description}</span>
        </span>
      </button>
    `).join('');

    return `
      <div class="mb-6 rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)]/40 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
          <h4 class="text-sm font-semibold text-[var(--c-text)]">Presets de Botones</h4>
          <p class="text-xs text-[var(--c-muted)]">Un clic aplica y guarda en la configuracion activa</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          ${presetsMarkup}
        </div>
      </div>
    `;
  }

  function createColorField(group, key, value) {
    const label = colorKeyLabels[key] || key.replace(/_/g, ' ');
    const isRgba = value.startsWith('rgba') || value.startsWith('rgb');
    const fieldId = `color-${group}-${key}`;

    return `
      <div class="bg-[var(--c-elev)] rounded-xl p-4 border border-[var(--c-border)]">
        <label for="${fieldId}" class="block text-xs font-medium text-[var(--c-muted)] mb-2">${label}</label>
        <div class="flex items-center gap-2">
          <input 
            type="color" 
            class="color-picker h-10 w-14 p-1 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] cursor-pointer"
            data-group="${group}"
            data-key="${key}"
            value="${isRgba ? '#888888' : value}"
            ${isRgba ? 'disabled title="Este color usa transparencia (rgba). Edita manualmente."' : ''}
          >
          <input 
            type="text" 
            id="${fieldId}"
            class="color-input flex-1 px-3 py-2 text-sm bg-[var(--c-surface)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent"
            data-group="${group}"
            data-key="${key}"
            value="${value}"
          >
        </div>
        ${isRgba ? '<p class="text-[10px] text-[var(--c-muted)] mt-1">Formato rgba con transparencia</p>' : ''}
      </div>
    `;
  }

  function attachButtonPresetEvents() {
    document.querySelectorAll('.btn-color-preset').forEach(button => {
      button.addEventListener('click', async () => {
        const presetKey = button.dataset.preset;
        await applyButtonPreset(presetKey);
      });
    });
  }

  async function applyButtonPreset(presetKey) {
    const preset = BUTTON_PRESETS[presetKey];
    if (!preset || !currentConfigId || !currentConfig) {
      return;
    }

    if (!modifiedColors.buttons || typeof modifiedColors.buttons !== 'object') {
      modifiedColors.buttons = {};
    }

    modifiedColors.buttons = {
      ...modifiedColors.buttons,
      ...preset.colors
    };

    renderColorGroup('buttons');
    await saveColors();
  }

  function attachColorPickerEvents() {
    document.querySelectorAll('.color-picker').forEach(picker => {
      picker.addEventListener('input', (e) => {
        const group = e.target.dataset.group;
        const key = e.target.dataset.key;
        const input = document.querySelector(`.color-input[data-group="${group}"][data-key="${key}"]`);
        if (input) {
          input.value = e.target.value;
          updateColor(group, key, e.target.value);
        }
      });
    });

    document.querySelectorAll('.color-input').forEach(input => {
      input.addEventListener('blur', (e) => {
        const group = e.target.dataset.group;
        const key = e.target.dataset.key;
        updateColor(group, key, e.target.value);
        
        // Update color picker if it's a valid hex
        if (/^#[0-9a-f]{6}$/i.test(e.target.value)) {
          const picker = document.querySelector(`.color-picker[data-group="${group}"][data-key="${key}"]`);
          if (picker && !picker.disabled) {
            picker.value = e.target.value;
          }
        }
      });
    });
  }

  function updateColor(group, key, value) {
    if (!modifiedColors[group]) {
      modifiedColors[group] = {};
    }
    modifiedColors[group][key] = value;
  }

  async function saveColors() {
    if (!currentConfigId) return false;

    try {
      const response = await fetch(`${API_BASE}/${currentConfigId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ colors: modifiedColors })
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        currentConfig.colors = JSON.parse(JSON.stringify(modifiedColors));
        return true;
      } else {
        showError('Error', data.message || 'No se pudieron guardar los colores');
        return false;
      }
    } catch (error) {
      console.error('Error saving colors:', error);
      showError('Error', 'Error al guardar los colores');
      return false;
    }
  }

  function cancelChanges() {
    if (currentConfig) {
      modifiedColors = JSON.parse(JSON.stringify(currentConfig.colors || {}));
      renderColorGroup(activeColorTab);
    }
  }

  async function activateConfig() {
    if (!currentConfigId) return;

    try {
      const response = await fetch(`${API_BASE}/${currentConfigId}/activate`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        await loadConfigsForView(currentViewSlug);
      } else {
        showError('Error', data.message);
      }
    } catch (error) {
      showError('Error', 'No se pudo activar la configuración');
    }
  }

  async function resetDefaults() {
    if (!currentConfigId) return;
    if (!confirm('¿Estás seguro de que quieres restablecer todos los colores a los valores por defecto?')) return;

    try {
      const response = await fetch(`${API_BASE}/${currentConfigId}/reset-defaults`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        loadConfig(currentConfigId);
      } else {
        showError('Error', data.message);
      }
    } catch (error) {
      showError('Error', 'No se pudieron restablecer los colores');
    }
  }

  async function deleteConfig() {
    if (!currentConfigId) return;
    if (!confirm('¿Estás seguro de que quieres eliminar esta configuración?')) return;

    try {
      const response = await fetch(`${API_BASE}/${currentConfigId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        await loadConfigsForView(currentViewSlug);
      } else {
        showError('Error', data.message);
      }
    } catch (error) {
      showError('Error', 'No se pudo eliminar la configuración');
    }
  }

  function openConfigModal() {
    document.getElementById('config-modal-title').textContent = 'Nueva Configuración';
    document.getElementById('config-name').value = '';
    document.getElementById('config-description').value = '';
    document.getElementById('config-view-slug').value = currentViewSlug;
    document.getElementById('config-view-name').textContent = availableViews[currentViewSlug]?.name || currentViewSlug;
    document.getElementById('config-modal').classList.remove('hidden');
  }

  function closeConfigModal() {
    document.getElementById('config-modal').classList.add('hidden');
  }

  async function saveConfig(e) {
    e.preventDefault();
    const name = document.getElementById('config-name').value;
    const description = document.getElementById('config-description').value;
    const viewSlug = document.getElementById('config-view-slug').value;

    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description, view_slug: viewSlug })
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        closeConfigModal();
        await loadConfigsForView(currentViewSlug);
        loadConfig(data.data.id);
      } else {
        showError('Error', data.message || 'No se pudo crear la configuración');
      }
    } catch (error) {
      showError('Error', 'Error al crear la configuración');
    }
  }

  function openDuplicateModal() {
    if (!currentConfig) return;
    document.getElementById('duplicate-name').value = `Copia de ${currentConfig.name}`;
    document.getElementById('duplicate-modal').classList.remove('hidden');
  }

  function closeDuplicateModal() {
    document.getElementById('duplicate-modal').classList.add('hidden');
  }

  async function duplicateConfig(e) {
    e.preventDefault();
    if (!currentConfigId) return;
    
    const name = document.getElementById('duplicate-name').value;

    try {
      const response = await fetch(`${API_BASE}/${currentConfigId}/duplicate`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name })
      });

      const data = await response.json();

      if (data.success) {
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
        closeDuplicateModal();
        await loadConfigsForView(currentViewSlug);
        loadConfig(data.data.id);
      } else {
        showError('Error', data.message);
      }
    } catch (error) {
      showError('Error', 'Error al duplicar la configuración');
    }
  }

  function showError(title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message
      }
    }));
  }

  // Expose for tabs
  window.colorApp = { switchView, switchColorTab };
});
</script>
@endsection
