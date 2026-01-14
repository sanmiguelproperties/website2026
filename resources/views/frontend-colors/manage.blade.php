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
    'header': 'Header',
    'footer': 'Footer',
    'ui': 'UI General',
    'pagination': 'Paginación',
    'hero': 'Hero/Slider',
    'stats': 'Estadísticas',
    'features': 'Características',
    'cta_sale': 'CTA Venta',
    'cta_rent': 'CTA Renta',
    'process': 'Proceso',
    'testimonials': 'Testimonios',
    'about': 'Nosotros',
    'contact': 'Contacto',
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
    'search_border': 'Búsqueda - borde',
    'search_focus_border': 'Búsqueda - borde focus',
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

    container.innerHTML = `
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-[var(--c-text)]">${groupLabels[group] || group}</h3>
        <p class="text-sm text-[var(--c-muted)]">Edita los colores de esta sección</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        ${Object.entries(colors).map(([key, value]) => createColorField(group, key, value)).join('')}
      </div>
    `;

    // Attach color picker events
    attachColorPickerEvents();
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
    if (!currentConfigId) return;

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
      } else {
        showError('Error', data.message || 'No se pudieron guardar los colores');
      }
    } catch (error) {
      console.error('Error saving colors:', error);
      showError('Error', 'Error al guardar los colores');
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
