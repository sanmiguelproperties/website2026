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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Seleccionar Rol</label>
            <select id="role-select" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg">
              <option value="">Selecciona un rol...</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Permisos Disponibles</label>
            <div id="permissions-checkboxes" class="w-full max-h-64 overflow-auto bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg p-3 space-y-2">
              <!-- Checkboxes will be loaded here -->
            </div>
          </div>
        </div>
        <div class="mt-4 flex gap-2">
          <button id="btn-assign-permissions" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Asignar Permisos</button>
          <button id="btn-sync-permissions" class="px-4 py-2 bg-[var(--c-accent)] text-white rounded-lg hover:opacity-95 transition">Sincronizar Permisos</button>
        </div>
        <div id="role-permissions" class="mt-6">
          <!-- Current permissions will be shown here -->
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
    const url = `${API_BASE}/roles?q=${encodeURIComponent(search)}`;

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
    const url = `${API_BASE}/permissions?q=${encodeURIComponent(search)}`;

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
      const response = await fetch(`${API_BASE}/roles`, {
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
      document.getElementById('role-permissions').innerHTML = '';
      document.getElementById('permissions-checkboxes').innerHTML = '';
      return;
    }

    try {
      // Load all permissions
      const permissionsResponse = await fetch(`${API_BASE}/permissions`, {
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
        renderPermissionsCheckboxes(permissionsData.data, rolePermissionsData.data);
        renderRolePermissions(rolePermissionsData.data);
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

  function renderPermissionsCheckboxes(allPermissions, assignedPermissions) {
    const container = document.getElementById('permissions-checkboxes');
    container.innerHTML = '';

    if (allPermissions.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-4">No hay permisos disponibles</p>';
      return;
    }

    const assignedIds = assignedPermissions.map(p => p.id);

    allPermissions.forEach(permission => {
      const checkboxDiv = document.createElement('div');
      checkboxDiv.className = 'flex items-center space-x-2';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.id = `permission-${permission.id}`;
      checkbox.value = permission.id;
      checkbox.className = 'w-4 h-4 text-[var(--c-primary)] bg-[var(--c-elev)] border-[var(--c-border)] rounded focus:ring-[var(--c-primary)] focus:ring-2';
      checkbox.checked = assignedIds.includes(permission.id);

      const label = document.createElement('label');
      label.htmlFor = `permission-${permission.id}`;
      label.className = 'text-sm text-[var(--c-text)] cursor-pointer';
      label.textContent = permission.name;

      checkboxDiv.appendChild(checkbox);
      checkboxDiv.appendChild(label);
      container.appendChild(checkboxDiv);
    });
  }

  function renderRolePermissions(permissions) {
    const container = document.getElementById('role-permissions');
    container.innerHTML = '<h3 class="text-md font-medium text-[var(--c-text)] mb-2">Permisos Asignados</h3>';

    if (permissions.length === 0) {
      container.innerHTML += '<p class="text-[var(--c-muted)]">No hay permisos asignados</p>';
      return;
    }

    const list = document.createElement('div');
    list.className = 'space-y-2';
    permissions.forEach(permission => {
      const item = document.createElement('div');
      item.className = 'flex items-center justify-between p-2 bg-[var(--c-elev)] rounded-lg';
      item.innerHTML = `
        <span class="text-sm text-[var(--c-text)]">${permission.name}</span>
        <button class="detach-permission-btn px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition" data-id="${permission.id}">Quitar</button>
      `;
      list.appendChild(item);
    });
    container.appendChild(list);

    // Add event listeners for detach
    container.querySelectorAll('.detach-permission-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const permissionId = e.target.dataset.id;
        const roleId = document.getElementById('role-select').value;
        detachPermission(roleId, permissionId);
      });
    });
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
    const checkboxes = document.querySelectorAll('#permissions-checkboxes input[type="checkbox"]:checked');
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
    const checkboxes = document.querySelectorAll('#permissions-checkboxes input[type="checkbox"]:checked');
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

  async function detachPermission(roleId, permissionId) {
    try {
      const response = await fetch(`${API_BASE}/roles/${roleId}/permissions/detach`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions: [permissionId] })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadRolePermissions();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al quitar permiso', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo quitar el permiso');
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