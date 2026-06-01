@extends('layouts.app')

@section('title', 'Administrar Roles y Permisos')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Roles y Permisos</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona roles y permisos del sistema</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-role" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nuevo Rol
      </button>
      <button id="btn-create-permission" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-accent)] text-white rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nuevo Permiso
      </button>
    </div>
  </div>

  <!-- Tabs -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <div class="border-b border-[var(--c-border)]">
      <nav class="flex">
        <button id="tab-roles" class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-[var(--c-primary)] text-[var(--c-primary)]">Roles</button>
        <button id="tab-permissions" class="tab-btn px-6 py-4 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]">Permisos</button>
        <button id="tab-assignments" class="tab-btn px-6 py-4 text-sm font-medium text-[var(--c-muted)] hover:text-[var(--c-text)]">Asignaciones</button>
      </nav>
    </div>

    <div class="p-6">
      <!-- Roles Tab -->
      <div id="roles-content" class="tab-content">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--c-text)]">Roles del Sistema</h2>
          <div class="flex items-center gap-2">
            <input type="text" id="search-roles" placeholder="Buscar roles..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
            <button id="btn-refresh-roles" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </button>
          </div>
        </div>
        <div id="roles-list" class="space-y-3">
          <!-- Roles will be loaded here -->
        </div>
      </div>

      <!-- Permissions Tab -->
      <div id="permissions-content" class="tab-content hidden">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--c-text)]">Permisos del Sistema</h2>
          <div class="flex items-center gap-2">
            <input type="text" id="search-permissions" placeholder="Buscar permisos..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
            <button id="btn-refresh-permissions" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </button>
          </div>
        </div>
        <div id="permissions-list" class="space-y-3">
          <!-- Permissions will be loaded here -->
        </div>
      </div>

      <!-- Assignments Tab -->
      <div id="assignments-content" class="tab-content hidden">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--c-text)]">Asignar Permisos a Roles</h2>
        </div>
        <div class="space-y-6">
          <div class="max-w-3xl rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
            <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Seleccionar Rol</label>
            <select id="role-select" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg">
              <option value="">Selecciona un rol...</option>
            </select>
            <p class="mt-2 text-xs text-[var(--c-muted)]">Se muestran roles y permisos del guard web. Al guardar, el sistema mantiene sincronizado el guard api por nombre.</p>
          </div>

          <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] overflow-hidden">
            <div class="border-b border-[var(--c-border)] p-4 space-y-4">
              <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                  <label class="block text-sm font-medium text-[var(--c-text)]">Permisos Disponibles</label>
                  <p id="assignment-summary" class="mt-1 text-xs text-[var(--c-muted)]">Selecciona un rol para cargar permisos.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                  <input type="text" id="permission-assignment-search" placeholder="Buscar permiso o vista..." class="w-full sm:w-72 px-3 py-2 bg-[var(--c-surface)] border border-[var(--c-border)] rounded-lg text-sm">
                  <button id="btn-select-all-permissions" type="button" class="px-3 py-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] text-sm hover:bg-[var(--c-elev)] transition">Seleccionar todos</button>
                  <button id="btn-clear-permissions" type="button" class="px-3 py-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] text-sm hover:bg-[var(--c-elev)] transition">Limpiar</button>
                </div>
              </div>
              <div>
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-[var(--c-muted)]">Acceso rápido por área</p>
                <div id="permission-area-buttons" class="flex flex-wrap gap-2">
                  <!-- Area buttons will be loaded here -->
                </div>
              </div>
            </div>
            <div id="permissions-checkboxes" class="w-full max-h-[60vh] overflow-auto bg-[var(--c-surface)] p-4 space-y-4">
              <!-- Checkboxes will be loaded here -->
            </div>
          </div>
        </div>
        <div class="mt-4 flex gap-2">
          <button id="btn-assign-permissions" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Asignar Permisos</button>
          <button id="btn-sync-permissions" class="px-4 py-2 bg-[var(--c-accent)] text-white rounded-lg hover:opacity-95 transition">Sincronizar Permisos</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<!-- Create/Edit Role Modal -->
<div id="role-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-16 w-full max-w-md">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="role-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Rol</h3>
      </div>
      <form id="role-form" class="p-6 space-y-4">
        <input type="hidden" id="role-id" name="id">
        <div>
          <label for="role-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre del Rol</label>
          <input type="text" id="role-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" id="btn-cancel-role" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Create/Edit Permission Modal -->
<div id="permission-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-16 w-full max-w-md">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="permission-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Permiso</h3>
      </div>
      <form id="permission-form" class="p-6 space-y-4">
        <input type="hidden" id="permission-id" name="id">
        <div>
          <label for="permission-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre del Permiso</label>
          <input type="text" id="permission-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" id="btn-cancel-permission" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api/rbac';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;
  let assignmentPermissions = [];
  let assignmentAssignedIds = [];
  let assignmentSearchTerm = '';

  const PERMISSION_GROUPS = [
    {
      key: 'dashboard',
      label: 'Dashboard',
      description: 'Entrada al panel y métricas generales.',
      prefixes: ['dashboard.', 'menu.dashboard.'],
      exact: ['menu.dashboard.view'],
    },
    {
      key: 'real-estate',
      label: 'Inmobiliaria',
      description: 'Dashboard, propiedades y zonas.',
      prefixes: ['properties.', 'menu.properties.', 'menu.zones.', 'catalogs.'],
      exact: ['menu.properties.view', 'menu.zones.view'],
    },
    {
      key: 'crm',
      label: 'CRM',
      description: 'Clientes, leads, notas, visitas y pipeline.',
      prefixes: ['clients.', 'leads.', 'crm.', 'calendar.', 'pipelines.', 'closings.', 'commissions.', 'menu.clients.', 'menu.property-contact-requests.', 'menu.calendar.'],
      exact: ['menu.clients.view', 'menu.property-contact-requests.view', 'menu.calendar.view'],
    },
    {
      key: 'admin',
      label: 'Administración',
      description: 'Usuarios, roles y permisos.',
      prefixes: ['users.', 'rbac.', 'menu.users.', 'menu.rbac.'],
      exact: ['menu.users.view', 'menu.rbac.view'],
    },
    {
      key: 'mls',
      label: 'MLS',
      description: 'Sincronización, agentes, oficinas y exportación.',
      prefixes: ['menu.easybroker.mls-export.', 'menu.mls.', 'menu.mls-agents.', 'menu.mls-offices.'],
      exact: ['menu.easybroker.mls-export.view', 'menu.mls.view', 'menu.mls-agents.view', 'menu.mls-offices.view'],
    },
    {
      key: 'email',
      label: 'Correos',
      description: 'Cuentas, bandejas y envío de correo corporativo.',
      prefixes: ['corporate-email.', 'menu.corporate-email.'],
      exact: ['menu.corporate-email.configuration.view', 'menu.corporate-email.inbox.view', 'menu.corporate-email.outbox.view', 'menu.corporate-email.compose.view'],
    },
    {
      key: 'cms',
      label: 'CMS',
      description: 'Páginas, posts, menús y ajustes del sitio.',
      prefixes: ['cms.', 'menu.cms.'],
      exact: ['menu.cms.pages.view', 'menu.cms.posts.view', 'menu.cms.menus.view', 'menu.cms.settings.view'],
    },
    {
      key: 'settings',
      label: 'Ajustes',
      description: 'Monedas, temas, colores, integraciones y notificaciones.',
      prefixes: ['settings.', 'integrations.', 'notifications.', 'marketing.', 'sensitive.', 'financial.', 'records.', 'menu.currencies.', 'menu.color-themes.', 'menu.frontend-colors.', 'menu.easybroker.', 'menu.notifications.'],
      exact: ['menu.currencies.view', 'menu.color-themes.view', 'menu.frontend-colors.view', 'menu.easybroker.view', 'menu.notifications.view'],
    },
    {
      key: 'documents-reports',
      label: 'Documentos y reportes',
      description: 'Documentos, reportes y exportaciones.',
      prefixes: ['documents.', 'reports.'],
      exact: [],
    },
  ];

  // Tab switching
  const tabs = document.querySelectorAll('.tab-btn');
  const contents = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => {
        t.classList.remove('active', 'border-b-2', 'border-[var(--c-primary)]', 'text-[var(--c-primary)]');
        t.classList.add('text-[var(--c-muted)]');
      });
      contents.forEach(c => c.classList.add('hidden'));

      tab.classList.add('active', 'border-b-2', 'border-[var(--c-primary)]', 'text-[var(--c-primary)]');
      tab.classList.remove('text-[var(--c-muted)]');

      const target = tab.id.replace('tab-', '') + '-content';
      document.getElementById(target).classList.remove('hidden');
    });
  });

  // Verificar token antes de cargar datos
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido. Por favor, inicia sesión nuevamente.');
    return;
  }

  // Load initial data
  loadRoles();
  loadPermissions();
  loadRolesForSelect();

  // Event listeners
  document.getElementById('btn-create-role').addEventListener('click', () => openRoleModal());
  document.getElementById('btn-create-permission').addEventListener('click', () => openPermissionModal());
  document.getElementById('btn-refresh-roles').addEventListener('click', loadRoles);
  document.getElementById('btn-refresh-permissions').addEventListener('click', loadPermissions);
  document.getElementById('role-select').addEventListener('change', loadRolePermissions);
  document.getElementById('btn-assign-permissions').addEventListener('click', assignPermissions);
  document.getElementById('btn-sync-permissions').addEventListener('click', syncPermissions);
  document.getElementById('permission-assignment-search').addEventListener('input', (event) => {
    assignmentSearchTerm = event.target.value.trim().toLowerCase();
    renderPermissionsCheckboxes(assignmentPermissions, assignmentAssignedIds);
  });
  document.getElementById('btn-select-all-permissions').addEventListener('click', selectVisiblePermissions);
  document.getElementById('btn-clear-permissions').addEventListener('click', clearVisiblePermissions);

  // Search functionality
  document.getElementById('search-roles').addEventListener('input', debounce(loadRoles, 300));
  document.getElementById('search-permissions').addEventListener('input', debounce(loadPermissions, 300));

  // Form submissions
  document.getElementById('role-form').addEventListener('submit', saveRole);
  document.getElementById('permission-form').addEventListener('submit', savePermission);

  // Modal close buttons
  document.getElementById('btn-cancel-role').addEventListener('click', () => closeRoleModal());
  document.getElementById('btn-cancel-permission').addEventListener('click', () => closePermissionModal());

  // Functions
  async function loadRoles() {
    const search = document.getElementById('search-roles').value;
    const url = `${API_BASE}/roles?per_page=500&q=${encodeURIComponent(search)}`;

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        renderRoles(data.data);
      } else {
        showApiError('Error al cargar roles', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar los roles');
    }
  }

  async function loadPermissions() {
    const search = document.getElementById('search-permissions').value;
    const url = `${API_BASE}/permissions?per_page=500&q=${encodeURIComponent(search)}`;

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        renderPermissions(data.data);
      } else {
        showApiError('Error al cargar permisos', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar los permisos');
    }
  }

  async function loadRolesForSelect() {
    try {
      const response = await fetch(`${API_BASE}/roles?per_page=500`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        const select = document.getElementById('role-select');
        select.innerHTML = '<option value="">Selecciona un rol...</option>';
        data.data.forEach(role => {
          const option = document.createElement('option');
          option.value = role.id;
          option.textContent = role.name;
          select.appendChild(option);
        });
      } else {
        showApiError('Error al cargar roles', data);
      }
    } catch (error) {
      console.error('Error loading roles for select:', error);
    }
  }

  async function loadRolePermissions() {
    const roleId = document.getElementById('role-select').value;
    if (!roleId) {
      document.getElementById('permissions-checkboxes').innerHTML = '';
      document.getElementById('permission-area-buttons').innerHTML = '';
      updateAssignmentSummary();
      return;
    }

    try {
      // Load all permissions
      const permissionsResponse = await fetch(`${API_BASE}/permissions?per_page=500`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      // Load role permissions
      const rolePermissionsResponse = await fetch(`${API_BASE}/roles/${roleId}/permissions`, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const permissionsData = await permissionsResponse.json();
      const rolePermissionsData = await rolePermissionsResponse.json();

      if (permissionsData.success && rolePermissionsData.success) {
        assignmentPermissions = [...permissionsData.data].sort((a, b) => a.name.localeCompare(b.name));
        assignmentAssignedIds = rolePermissionsData.data.map(p => Number(p.id));
        renderPermissionsCheckboxes(assignmentPermissions, assignmentAssignedIds);
      } else {
        showApiError('Error al cargar permisos del rol', permissionsData.success ? rolePermissionsData : permissionsData);
      }
    } catch (error) {
      console.error('Error loading role permissions:', error);
    }
  }

  function renderRoles(roles) {
    const container = document.getElementById('roles-list');
    container.innerHTML = '';

    if (roles.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron roles</p>';
      return;
    }

    roles.forEach(role => {
      const roleEl = document.createElement('div');
      roleEl.className = 'flex items-center justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';
      roleEl.innerHTML = `
        <div>
          <h3 class="font-medium text-[var(--c-text)]">${role.name}</h3>
          <p class="text-sm text-[var(--c-muted)]">Guard: ${role.guard_name}</p>
        </div>
        <div class="flex gap-2">
          <button class="edit-role-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${role.id}" data-name="${role.name}">Editar</button>
          <button class="delete-role-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${role.id}">Eliminar</button>
        </div>
      `;
      container.appendChild(roleEl);
    });

    // Add event listeners
    container.querySelectorAll('.edit-role-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        const name = e.target.dataset.name;
        openRoleModal(id, name);
      });
    });

    container.querySelectorAll('.delete-role-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        deleteRole(id);
      });
    });
  }

  function renderPermissions(permissions) {
    const container = document.getElementById('permissions-list');
    container.innerHTML = '';

    if (permissions.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron permisos</p>';
      return;
    }

    permissions.forEach(permission => {
      const permEl = document.createElement('div');
      permEl.className = 'flex items-center justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';
      permEl.innerHTML = `
        <div>
          <h3 class="font-medium text-[var(--c-text)]">${permission.name}</h3>
          <p class="text-sm text-[var(--c-muted)]">Guard: ${permission.guard_name}</p>
        </div>
        <div class="flex gap-2">
          <button class="edit-permission-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${permission.id}" data-name="${permission.name}">Editar</button>
          <button class="delete-permission-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${permission.id}">Eliminar</button>
        </div>
      `;
      container.appendChild(permEl);
    });

    // Add event listeners
    container.querySelectorAll('.edit-permission-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        const name = e.target.dataset.name;
        openPermissionModal(id, name);
      });
    });

    container.querySelectorAll('.delete-permission-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        deletePermission(id);
      });
    });
  }

  function renderPermissionsCheckboxes(allPermissions, assignedIds = []) {
    const container = document.getElementById('permissions-checkboxes');
    container.innerHTML = '';

    const normalizedAssignedIds = assignedIds.map(id => Number(id));

    if (allPermissions.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-4">No hay permisos disponibles</p>';
      renderAreaButtons([]);
      updateAssignmentSummary();
      return;
    }

    const groups = buildPermissionGroups(allPermissions);
    renderAreaButtons(groups);

    let renderedCount = 0;
    groups.forEach(group => {
      const visiblePermissions = filterPermissions(group.permissions);
      if (visiblePermissions.length === 0) {
        return;
      }

      renderedCount += visiblePermissions.length;
      const selectedInGroup = visiblePermissions.filter(permission => normalizedAssignedIds.includes(Number(permission.id))).length;

      const section = document.createElement('section');
      section.id = `permission-group-${group.key}`;
      section.className = 'permission-group-section scroll-mt-4 rounded-2xl border border-[var(--c-border)] bg-[var(--c-elev)] overflow-hidden transition-shadow duration-300';
      section.innerHTML = `
        <div class="flex flex-col gap-3 border-b border-[var(--c-border)] p-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h3 class="font-semibold text-[var(--c-text)]">${escapeHtml(group.label)}</h3>
            <p class="mt-1 text-xs text-[var(--c-muted)]">${escapeHtml(group.description)} ${selectedInGroup}/${visiblePermissions.length} seleccionados.</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <button type="button" class="select-group-btn px-3 py-1.5 rounded-lg bg-[var(--c-primary)] text-[var(--c-primary-ink)] text-xs font-medium hover:opacity-95 transition" data-group="${group.key}">Seleccionar área</button>
            <button type="button" class="clear-group-btn px-3 py-1.5 rounded-lg border border-[var(--c-border)] bg-[var(--c-surface)] text-xs font-medium hover:bg-[var(--c-elev)] transition" data-group="${group.key}">Limpiar área</button>
          </div>
        </div>
      `;

      const grid = document.createElement('div');
      grid.className = 'grid grid-cols-1 gap-2 p-4 md:grid-cols-2 xl:grid-cols-3';

      visiblePermissions.forEach(permission => {
        grid.appendChild(createPermissionCheckbox(permission, normalizedAssignedIds.includes(Number(permission.id))));
      });

      section.appendChild(grid);
      container.appendChild(section);
    });

    if (renderedCount === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No hay permisos que coincidan con la búsqueda.</p>';
    }

    container.querySelectorAll('.select-group-btn').forEach(button => {
      button.addEventListener('click', () => setGroupSelection(button.dataset.group, true));
    });

    container.querySelectorAll('.clear-group-btn').forEach(button => {
      button.addEventListener('click', () => setGroupSelection(button.dataset.group, false));
    });

    updateAssignmentSummary();
  }

  function buildPermissionGroups(permissions) {
    const matchedIds = new Set();
    const groups = PERMISSION_GROUPS.map(group => {
      const groupPermissions = permissions.filter(permission => permissionBelongsToGroup(permission.name, group));
      groupPermissions.forEach(permission => matchedIds.add(Number(permission.id)));

      return {
        ...group,
        permissions: groupPermissions,
      };
    }).filter(group => group.permissions.length > 0);

    const otherPermissions = permissions.filter(permission => !matchedIds.has(Number(permission.id)));
    if (otherPermissions.length > 0) {
      groups.push({
        key: 'other',
        label: 'Otros permisos',
        description: 'Permisos personalizados o sin área definida.',
        prefixes: [],
        exact: [],
        permissions: otherPermissions,
      });
    }

    return groups;
  }

  function permissionBelongsToGroup(permissionName, group) {
    return (group.exact || []).includes(permissionName)
      || (group.prefixes || []).some(prefix => permissionName.startsWith(prefix));
  }

  function filterPermissions(permissions) {
    if (!assignmentSearchTerm) {
      return permissions;
    }

    return permissions.filter(permission => {
      const area = getPermissionArea(permission.name);
      return permission.name.toLowerCase().includes(assignmentSearchTerm)
        || area.label.toLowerCase().includes(assignmentSearchTerm)
        || area.description.toLowerCase().includes(assignmentSearchTerm);
    });
  }

  function createPermissionCheckbox(permission, checked) {
    const item = document.createElement('label');
    item.htmlFor = `permission-${permission.id}`;
    item.className = 'flex min-h-[3rem] cursor-pointer items-start gap-3 rounded-xl border border-[var(--c-border)] bg-[var(--c-surface)] p-3 hover:border-[var(--c-primary)] transition';

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = `permission-${permission.id}`;
    checkbox.value = permission.id;
    checkbox.className = 'permission-checkbox mt-1 w-4 h-4 text-[var(--c-primary)] bg-[var(--c-elev)] border-[var(--c-border)] rounded focus:ring-[var(--c-primary)] focus:ring-2';
    checkbox.checked = checked;
    checkbox.addEventListener('change', updateAssignmentSummary);

    const content = document.createElement('span');
    content.className = 'min-w-0 flex-1';

    const name = document.createElement('span');
    name.className = 'block break-all text-sm font-medium text-[var(--c-text)]';
    name.textContent = permission.name;

    const meta = document.createElement('span');
    meta.className = 'mt-1 block text-xs text-[var(--c-muted)]';
    meta.textContent = permissionHint(permission.name);

    content.appendChild(name);
    content.appendChild(meta);
    item.appendChild(checkbox);
    item.appendChild(content);

    return item;
  }

  function renderAreaButtons(groups) {
    const container = document.getElementById('permission-area-buttons');
    container.innerHTML = '';

    groups.forEach(group => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'area-select-btn rounded-full border border-[var(--c-border)] bg-[var(--c-surface)] px-3 py-1.5 text-xs font-medium hover:border-[var(--c-primary)] hover:text-[var(--c-primary)] transition';
      button.dataset.group = group.key;
      button.textContent = `${group.label} (${group.permissions.length})`;
      button.addEventListener('click', () => scrollToPermissionGroup(group.key));
      container.appendChild(button);
    });
  }

  function scrollToPermissionGroup(groupKey) {
    const section = document.getElementById(`permission-group-${groupKey}`);
    if (!section) {
      return;
    }

    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    section.classList.add('ring-2', 'ring-[var(--c-primary)]', 'shadow-lg');

    window.setTimeout(() => {
      section.classList.remove('ring-2', 'ring-[var(--c-primary)]', 'shadow-lg');
    }, 1200);
  }

  function setGroupSelection(groupKey, checked) {
    const group = buildPermissionGroups(assignmentPermissions).find(item => item.key === groupKey);
    if (!group) {
      return;
    }

    const visibleIds = new Set(filterPermissions(group.permissions).map(permission => Number(permission.id)));
    document.querySelectorAll('#permissions-checkboxes input.permission-checkbox').forEach(checkbox => {
      if (visibleIds.has(Number(checkbox.value))) {
        checkbox.checked = checked;
      }
    });

    updateAssignmentSummary();
  }

  function selectVisiblePermissions() {
    document.querySelectorAll('#permissions-checkboxes input.permission-checkbox').forEach(checkbox => {
      checkbox.checked = true;
    });
    updateAssignmentSummary();
  }

  function clearVisiblePermissions() {
    document.querySelectorAll('#permissions-checkboxes input.permission-checkbox').forEach(checkbox => {
      checkbox.checked = false;
    });
    updateAssignmentSummary();
  }

  function updateAssignmentSummary() {
    const summary = document.getElementById('assignment-summary');
    const selected = document.querySelectorAll('#permissions-checkboxes input.permission-checkbox:checked').length;
    const visible = document.querySelectorAll('#permissions-checkboxes input.permission-checkbox').length;
    const total = assignmentPermissions.length;

    if (!document.getElementById('role-select').value) {
      summary.textContent = 'Selecciona un rol para cargar permisos.';
      return;
    }

    summary.textContent = `${selected} permisos seleccionados de ${visible} visibles (${total} permisos disponibles en web).`;
  }

  function getPermissionArea(permissionName) {
    return PERMISSION_GROUPS.find(group => permissionBelongsToGroup(permissionName, group)) || {
      label: 'Otros permisos',
      description: 'Permisos personalizados o sin área definida.',
    };
  }

  function permissionHint(permissionName) {
    if (permissionName.startsWith('menu.')) {
      return 'Visibilidad de menú';
    }

    if (permissionName.endsWith('.view.all') || permissionName.endsWith('.view.global')) {
      return 'Ver registros globales';
    }

    if (permissionName.endsWith('.view.own')) {
      return 'Ver registros propios';
    }

    if (permissionName.endsWith('.edit.own')) {
      return 'Editar registros propios';
    }

    if (permissionName.endsWith('.delete.own')) {
      return 'Eliminar registros propios';
    }

    if (permissionName.includes('.create')) {
      return 'Crear registros';
    }

    if (permissionName.includes('.edit') || permissionName.includes('.manage')) {
      return 'Administrar o editar';
    }

    if (permissionName.includes('.delete')) {
      return 'Eliminar registros';
    }

    if (permissionName.includes('.sync')) {
      return 'Ejecutar sincronización';
    }

    if (permissionName.includes('.export')) {
      return 'Exportar información';
    }

    if (permissionName.includes('.view')) {
      return 'Ver sección o información';
    }

    return getPermissionArea(permissionName).label;
  }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  }

  function openRoleModal(id = null, name = '') {
    const modal = document.getElementById('role-modal');
    const title = document.getElementById('role-modal-title');
    const idField = document.getElementById('role-id');
    const nameField = document.getElementById('role-name');

    if (id) {
      title.textContent = 'Editar Rol';
      idField.value = id;
      nameField.value = name;
    } else {
      title.textContent = 'Crear Rol';
      idField.value = '';
      nameField.value = '';
    }

    modal.classList.remove('hidden');
  }

  function closeRoleModal() {
    document.getElementById('role-modal').classList.add('hidden');
  }

  function openPermissionModal(id = null, name = '') {
    const modal = document.getElementById('permission-modal');
    const title = document.getElementById('permission-modal-title');
    const idField = document.getElementById('permission-id');
    const nameField = document.getElementById('permission-name');

    if (id) {
      title.textContent = 'Editar Permiso';
      idField.value = id;
      nameField.value = name;
    } else {
      title.textContent = 'Crear Permiso';
      idField.value = '';
      nameField.value = '';
    }

    modal.classList.remove('hidden');
  }

  function closePermissionModal() {
    document.getElementById('permission-modal').classList.add('hidden');
  }

  async function saveRole(e) {
    e.preventDefault();

    const id = document.getElementById('role-id').value;
    const name = document.getElementById('role-name').value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/roles/${id}` : `${API_BASE}/roles`;

    try {
      const response = await fetch(url, {
        method: method,
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closeRoleModal();
        loadRoles();
        loadRolesForSelect();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al guardar rol', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo guardar el rol');
    }
  }

  async function savePermission(e) {
    e.preventDefault();

    const id = document.getElementById('permission-id').value;
    const name = document.getElementById('permission-name').value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/permissions/${id}` : `${API_BASE}/permissions`;

    try {
      const response = await fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closePermissionModal();
        loadPermissions();
        if (document.getElementById('role-select').value) {
          loadRolePermissions();
        }
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al guardar permiso', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo guardar el permiso');
    }
  }

  async function deleteRole(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este rol?')) return;

    try {
      const response = await fetch(`${API_BASE}/roles/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadRoles();
        loadRolesForSelect();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al eliminar rol', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo eliminar el rol');
    }
  }

  async function deletePermission(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este permiso?')) return;

    try {
      const response = await fetch(`${API_BASE}/permissions/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadPermissions();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al eliminar permiso', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo eliminar el permiso');
    }
  }

  async function assignPermissions() {
    const roleId = document.getElementById('role-select').value;
    const checkboxes = document.querySelectorAll('#permissions-checkboxes input.permission-checkbox:checked');
    const selectedPermissions = Array.from(checkboxes).map(checkbox => checkbox.value);

    if (!roleId || selectedPermissions.length === 0) {
      showError('Selección requerida', 'Selecciona un rol y al menos un permiso');
      return;
    }

    try {
      const response = await fetch(`${API_BASE}/roles/${roleId}/permissions/attach`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions: selectedPermissions })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadRolePermissions();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al asignar permisos', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron asignar los permisos');
    }
  }

  async function syncPermissions() {
    const roleId = document.getElementById('role-select').value;
    const checkboxes = document.querySelectorAll('#permissions-checkboxes input.permission-checkbox:checked');
    const selectedPermissions = Array.from(checkboxes).map(checkbox => checkbox.value);

    if (!roleId) {
      showError('Selección requerida', 'Selecciona un rol');
      return;
    }

    try {
      const response = await fetch(`${API_BASE}/roles/${roleId}/permissions/sync`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions: selectedPermissions })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadRolePermissions();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al sincronizar permisos', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron sincronizar los permisos');
    }
  }

  function showError(title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: 'ERROR'
      }
    }));
  }

  function showApiError(title, apiResponse) {
    let message = apiResponse.message || 'Error desconocido';
    let code = apiResponse.code || 'UNKNOWN_ERROR';
    let details = '';

    // Mostrar detalles específicos según el código de error
    switch (code) {
      case 'TOKEN_MISSING':
        message = 'Token de acceso requerido';
        details = 'No se encontró un token de acceso en la solicitud.';
        break;
      case 'TOKEN_INVALID':
        message = 'Token inválido o expirado';
        details = 'El token proporcionado no es válido o ha expirado.';
        break;
      case 'USER_NOT_FOUND':
        message = 'Usuario no encontrado';
        details = 'El usuario asociado al token no existe.';
        break;
      case 'AUTH_ERROR':
        message = 'Error de autenticación';
        details = 'Error interno al validar el token.';
        break;
      case 'FORBIDDEN_ERROR':
        message = 'Acceso denegado';
        details = 'No tienes permisos para realizar esta acción.';
        break;
      case 'VALIDATION_ERROR':
        message = 'Datos inválidos';
        if (apiResponse.errors) {
          details = Object.values(apiResponse.errors).flat().join(', ');
        }
        break;
      default:
        if (apiResponse.errors && typeof apiResponse.errors === 'object') {
          details = Object.values(apiResponse.errors).flat().join(', ');
        }
    }

    // Mostrar el error en la consola para debugging
    console.error('API Error:', { title, code, message, details, apiResponse });

    // Disparar evento para mostrar el error al usuario
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: code,
        details: details
      }
    }));
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
});
</script>
@endsection
