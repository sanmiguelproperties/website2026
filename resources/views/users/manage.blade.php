@extends('layouts.app')

@section('title', 'Administrar Usuarios')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Usuarios</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona los usuarios del sistema</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-user" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nuevo Usuario
      </button>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-[var(--c-text)]">Usuarios del Sistema</h2>
      <div class="flex items-center gap-2">
        <input type="text" id="search-users" placeholder="Buscar usuarios..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
        <button id="btn-refresh-users" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Users List -->
    <div id="users-list" class="space-y-3">
      <!-- Users will be loaded here -->
    </div>

    <!-- Pagination -->
    <div id="users-pagination" class="flex justify-between items-center mt-6">
      <!-- Pagination will be loaded here -->
    </div>
  </div>
</div>

<!-- Create/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="user-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Usuario</h3>
      </div>
      <form id="user-form" class="p-6 space-y-4">
        <input type="hidden" id="user-id" name="id">

        <!-- Profile Image -->
        <div>
          <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Imagen de Perfil</label>
          <x-media-input
            name="profile_image_id"
            mode="single"
            :max="1"
            placeholder="Seleccionar imagen de perfil"
            button="Seleccionar Imagen"
            preview="true"
            value="{{ old('profile_image_id', $user->profile_image_id ?? '') }}"
          />
        </div>

        <!-- Name -->
        <div>
          <label for="user-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre</label>
          <input type="text" id="user-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>

        <!-- Email -->
        <div>
          <label for="user-email" class="block text-sm font-medium text-[var(--c-text)] mb-1">Correo Electrónico</label>
          <input type="email" id="user-email" name="email" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>

        <label class="flex items-center gap-3 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] px-3 py-2">
          <input type="checkbox" id="user-is-active" name="is_active" class="rounded border-[var(--c-border)]" checked>
          <span class="text-sm text-[var(--c-text)]">Usuario activo</span>
        </label>

        <!-- Roles -->
        <div>
          <label class="block text-sm font-medium text-[var(--c-text)] mb-2">Roles</label>
          <div id="user-roles-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-lg border border-[var(--c-border)] bg-[var(--c-elev)] p-3">
            <p class="text-sm text-[var(--c-muted)]">Cargando roles...</p>
          </div>
        </div>

        <!-- Public MLS agent profile -->
        <div id="user-mls-agent-section" class="hidden rounded-xl border border-[var(--c-border)] bg-[var(--c-elev)] p-4">
          <label for="user-mls-agent-profile-id" class="block text-sm font-medium text-[var(--c-text)] mb-1">Perfil público de agente</label>
          <select id="user-mls-agent-profile-id" class="w-full px-3 py-2 bg-[var(--c-surface)] border border-[var(--c-border)] rounded-lg">
            <option value="">Sin perfil público relacionado</option>
          </select>
          <p id="user-mls-agent-help" class="mt-2 text-xs text-[var(--c-muted)]">Puedes vincular un perfil MLS existente o crear uno nuevo en la agencia MLS principal.</p>
        </div>

        <!-- Password -->
        <div>
          <label for="user-password" class="block text-sm font-medium text-[var(--c-text)] mb-1">Contraseña</label>
          <input type="password" id="user-password" name="password" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
          <p class="text-xs text-[var(--c-muted)] mt-1">Mínimo 8 caracteres</p>
        </div>

        <!-- Password Confirmation (only for create) -->
        <div id="password-confirm-container" style="display: none;">
          <label for="user-password-confirm" class="block text-sm font-medium text-[var(--c-text)] mb-1">Confirmar Contraseña</label>
          <input type="password" id="user-password-confirm" name="password_confirmation" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent">
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-user" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api';
  const RBAC_API_BASE = '/api/rbac';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;
  let availableRoles = [];
  let usersById = new Map();
  let currentPage = 1;

  // Verificar token antes de cargar datos
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido. Por favor, inicia sesión nuevamente.');
    return;
  }

  // Load initial data
  loadRoles();
  loadUsers();

  // Event listeners
  document.getElementById('btn-create-user').addEventListener('click', () => openUserModal());
  document.getElementById('btn-refresh-users').addEventListener('click', () => loadUsers(currentPage));
  document.getElementById('search-users').addEventListener('input', debounce(() => loadUsers(), 300));

  // Form submissions
  document.getElementById('user-form').addEventListener('submit', saveUser);

  // Modal close buttons
  document.getElementById('btn-cancel-user').addEventListener('click', () => closeUserModal());
  document.getElementById('user-mls-agent-profile-id').addEventListener('change', updateMlsAgentHelp);

  // Functions
  function authHeaders(json = false) {
    const headers = {
      'Authorization': `Bearer ${API_TOKEN}`,
      'X-CSRF-TOKEN': CSRF_TOKEN,
      'Accept': 'application/json'
    };

    if (json) {
      headers['Content-Type'] = 'application/json';
    }

    return headers;
  }

  async function readJson(response) {
    const text = await response.text();

    if (!text) {
      return null;
    }

    try {
      return JSON.parse(text);
    } catch (error) {
      return {
        success: false,
        message: 'Respuesta invalida del servidor',
        code: 'INVALID_JSON_RESPONSE',
        raw: text
      };
    }
  }

  async function loadRoles() {
    try {
      const response = await fetch(`${RBAC_API_BASE}/roles?per_page=100&guard=web&sort=name&order=asc`, {
        headers: authHeaders()
      });
      const data = await readJson(response);

      if (response.ok && data?.success) {
        availableRoles = data.data || [];
        const currentUserId = document.getElementById('user-id')?.value;
        renderRoleCheckboxes(roleNamesFromUser(currentUserId ? usersById.get(String(currentUserId)) : null));
      } else {
        showApiError('Error al cargar roles', data);
      }
    } catch (error) {
      showError('Error de conexion', 'No se pudieron cargar los roles.');
    }
  }

  async function loadUsers(page = 1) {
    const search = document.getElementById('search-users').value;
    const url = `${API_BASE}/users?page=${page}&per_page=15&search=${encodeURIComponent(search)}`;

    try {
      const response = await fetch(url, {
        headers: authHeaders()
      });

      const data = await readJson(response);

      if (response.ok && data?.success) {
        currentPage = data.data.current_page || page;
        renderUsers(data.data);
        renderPagination(data.data);
      } else {
        showApiError('Error al cargar usuarios', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar los usuarios. Verifica tu conexión a internet.');
    }
  }

  function renderUsers(usersData) {
    const container = document.getElementById('users-list');
    container.innerHTML = '';
    usersById = new Map();

    if (usersData.data.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron usuarios</p>';
      return;
    }

    usersData.data.forEach(user => {
      usersById.set(String(user.id), user);

      const userEl = document.createElement('div');
      userEl.className = 'flex flex-col gap-4 p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)] sm:flex-row sm:items-center sm:justify-between';

      // Profile image
      let profileImageHtml = '';
      const imageUrl = user.profile_image?.serving_url || user.profile_image?.url;
      if (imageUrl) {
        profileImageHtml = `<img src="${escapeAttr(imageUrl)}" alt="${escapeAttr(user.profile_image?.alt || user.name || 'Usuario')}" class="w-10 h-10 rounded-full object-cover">`;
      } else {
        const initial = (user.name || '?').charAt(0).toUpperCase();
        profileImageHtml = `<div class="w-10 h-10 rounded-full bg-[var(--c-primary)] flex items-center justify-center text-white font-bold text-lg">${escapeHtml(initial)}</div>`;
      }

      userEl.innerHTML = `
        <div class="flex items-start gap-4 min-w-0">
          ${profileImageHtml}
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="font-medium text-[var(--c-text)]">${escapeHtml(user.name || 'Usuario')}</h3>
              <span class="text-xs px-2 py-0.5 rounded-full ${user.is_active === false ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300'}">${user.is_active === false ? 'Inactivo' : 'Activo'}</span>
            </div>
            <p class="text-sm text-[var(--c-muted)] break-all">${escapeHtml(user.email || '')}</p>
            <div class="mt-2 flex flex-wrap gap-1.5">${renderRoleBadges(user)}${renderMlsAgentBadge(user)}</div>
          </div>
        </div>
        <div class="flex gap-2 shrink-0">
          <button class="edit-user-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${escapeAttr(user.id)}">Editar</button>
          <button class="delete-user-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${escapeAttr(user.id)}">Eliminar</button>
        </div>
      `;
      container.appendChild(userEl);
    });

    // Add event listeners
    container.querySelectorAll('.edit-user-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        openUserModal(usersById.get(String(id)));
      });
    });

    container.querySelectorAll('.delete-user-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        deleteUser(id);
      });
    });
  }

  function renderPagination(usersData) {
    const container = document.getElementById('users-pagination');
    container.innerHTML = '';

    if (usersData.last_page <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Anterior';
    prevBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    prevBtn.disabled = !usersData.prev_page_url;
    prevBtn.addEventListener('click', () => loadUsers(usersData.current_page - 1));

    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Siguiente';
    nextBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    nextBtn.disabled = !usersData.next_page_url;
    nextBtn.addEventListener('click', () => loadUsers(usersData.current_page + 1));

    const pageInfo = document.createElement('div');
    pageInfo.textContent = `Página ${usersData.current_page} de ${usersData.last_page}`;
    pageInfo.className = 'text-sm text-[var(--c-muted)]';

    container.appendChild(prevBtn);
    container.appendChild(pageInfo);
    container.appendChild(nextBtn);
  }

  async function openUserModal(user = null) {
    const modal = document.getElementById('user-modal');
    const title = document.getElementById('user-modal-title');
    const idField = document.getElementById('user-id');
    const nameField = document.getElementById('user-name');
    const emailField = document.getElementById('user-email');
    const passwordField = document.getElementById('user-password');
    const isActiveField = document.getElementById('user-is-active');
    const passwordConfirmContainer = document.getElementById('password-confirm-container');
    const passwordConfirmField = document.getElementById('user-password-confirm');
    const id = user?.id || null;

    if (id) {
      title.textContent = 'Editar Usuario';
      idField.value = id;
      nameField.value = user.name || '';
      emailField.value = user.email || '';
      passwordField.value = '';
      passwordField.required = false;
      passwordField.placeholder = 'Dejar vacío para mantener la contraseña actual';
      isActiveField.checked = user.is_active !== false;
      passwordConfirmContainer.style.display = 'none';
      passwordConfirmField.required = false;
      passwordConfirmField.value = '';
    } else {
      title.textContent = 'Crear Usuario';
      idField.value = '';
      nameField.value = '';
      emailField.value = '';
      passwordField.value = '';
      passwordField.required = true;
      passwordField.placeholder = '';
      isActiveField.checked = true;
      passwordConfirmContainer.style.display = 'block';
      passwordConfirmField.required = true;
      passwordConfirmField.value = '';
    }

    renderRoleCheckboxes(roleNamesFromUser(user));
    updateMlsAgentProfileVisibility();
    await loadMlsAgentOptions(user);

    // Set profile image value
    const mediaInput = modal.querySelector('input[name="profile_image_id"]');
    if (mediaInput) {
      mediaInput.value = user?.profile_image_id || '';
      // Trigger preview update if needed
      setTimeout(() => {
        mediaInput.dispatchEvent(new Event('change', { bubbles: true }));
      }, 100);
    }

    modal.classList.remove('hidden');
  }

  function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
  }

  async function saveUser(e) {
    e.preventDefault();

    const id = document.getElementById('user-id').value;
    const name = document.getElementById('user-name').value;
    const email = document.getElementById('user-email').value;
    const password = document.getElementById('user-password').value;
    const profileImageId = document.querySelector('input[name="profile_image_id"]').value;
    const isActive = document.getElementById('user-is-active').checked;
    const roleIds = selectedRoleIds();

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/users/${id}` : `${API_BASE}/users`;

    const formData = {
      name,
      email,
      profile_image_id: profileImageId || null,
      is_active: isActive
    };

    if (password) {
      formData.password = password;
    }

    try {
      const response = await fetch(url, {
        method: method,
        headers: authHeaders(true),
        body: JSON.stringify(formData)
      });

      const data = await readJson(response);

      if (response.ok && data?.success) {
        const savedUserId = data.data?.id || id;
        const rolesData = await saveUserRoles(savedUserId, roleIds);
        if (!rolesData) {
          return;
        }

        const profileData = await saveUserMlsAgent(savedUserId);
        if (!profileData) {
          return;
        }

        closeUserModal();
        loadUsers(currentPage);
        window.dispatchEvent(new CustomEvent('api:response', { detail: profileData }));
      } else {
        showApiError('Error al guardar usuario', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo guardar el usuario. Verifica tu conexión a internet.');
    }
  }

  async function saveUserRoles(userId, roleIds) {
    if (!userId) {
      showError('Error al guardar roles', 'No se pudo identificar el usuario.');
      return null;
    }

    const response = await fetch(`${API_BASE}/users/${userId}/roles/assign`, {
      method: 'POST',
      headers: authHeaders(true),
      body: JSON.stringify({ roles: roleIds })
    });

    const data = await readJson(response);

    if (response.ok && data?.success) {
      return data;
    }

    showApiError('Error al guardar roles', data);
    return null;
  }

  async function loadMlsAgentOptions(user = null) {
    const select = document.getElementById('user-mls-agent-profile-id');
    const params = new URLSearchParams();
    if (user?.id) {
      params.set('user_id', user.id);
    }

    select.disabled = true;
    select.innerHTML = '<option value="">Cargando perfiles...</option>';

    try {
      const response = await fetch(`${API_BASE}/users/mls-agent-options?${params.toString()}`, {
        headers: authHeaders()
      });
      const data = await readJson(response);

      if (!response.ok || !data?.success) {
        showApiError('Error al cargar perfiles MLS', data);
        return;
      }

      select.innerHTML = '';
      appendSelectOption(select, '', 'Sin perfil público relacionado');
      appendSelectOption(select, '__create__', 'Crear perfil local nuevo en la agencia principal');

      (data.data || []).forEach(profile => {
        const profileName = profile.full_name || profile.name || `Agente #${profile.id}`;
        const source = profile.is_manual ? 'local' : `MLS #${profile.mls_agent_id}`;
        const office = profile.office?.name || profile.office_name || 'sin agencia';
        appendSelectOption(select, profile.id, `${profileName} (${source}, ${office})`);
      });

      select.value = user?.mls_agent?.id ? String(user.mls_agent.id) : '';
      select.disabled = false;
      updateMlsAgentHelp();
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar los perfiles MLS disponibles.');
    }
  }

  async function saveUserMlsAgent(userId) {
    const selected = selectedRoleNames().some(isAgentRoleName)
      ? document.getElementById('user-mls-agent-profile-id').value
      : '';
    const isCreate = selected === '__create__';
    const response = await fetch(`${API_BASE}/users/${userId}/mls-agent`, {
      method: isCreate ? 'POST' : 'PUT',
      headers: authHeaders(true),
      body: isCreate ? null : JSON.stringify({
        mls_agent_profile_id: selected ? Number(selected) : null
      })
    });
    const data = await readJson(response);

    if (response.ok && data?.success) {
      return data;
    }

    showApiError('Error al guardar perfil MLS', data);
    return null;
  }

  async function deleteUser(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este usuario?')) return;

    try {
      const response = await fetch(`${API_BASE}/users/${id}`, {
        method: 'DELETE',
        headers: authHeaders()
      });

      const data = await readJson(response);

      if (response.ok && data?.success) {
        loadUsers(currentPage);
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al eliminar usuario', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo eliminar el usuario. Verifica tu conexión a internet.');
    }
  }

  function renderRoleCheckboxes(selectedRoleNames = []) {
    const container = document.getElementById('user-roles-list');
    const selected = new Set(selectedRoleNames);
    container.innerHTML = '';

    if (availableRoles.length === 0) {
      container.innerHTML = '<p class="text-sm text-[var(--c-muted)]">No hay roles disponibles</p>';
      return;
    }

    availableRoles.forEach(role => {
      const label = document.createElement('label');
      label.className = 'flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-[var(--c-text)] hover:bg-[var(--c-surface)]';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.value = role.id;
      checkbox.checked = selected.has(role.name);
      checkbox.className = 'rounded border-[var(--c-border)] text-[var(--c-primary)]';
      checkbox.addEventListener('change', updateMlsAgentProfileVisibility);

      const text = document.createElement('span');
      text.textContent = role.name;

      label.appendChild(checkbox);
      label.appendChild(text);
      container.appendChild(label);
    });

    updateMlsAgentProfileVisibility();
  }

  function selectedRoleIds() {
    return Array.from(document.querySelectorAll('#user-roles-list input[type="checkbox"]:checked'))
      .map(checkbox => Number(checkbox.value))
      .filter(Number.isFinite);
  }

  function selectedRoleNames() {
    const selectedIds = new Set(selectedRoleIds());
    return availableRoles
      .filter(role => selectedIds.has(Number(role.id)))
      .map(role => role.name);
  }

  function isAgentRoleName(name) {
    return ['agente', 'agent'].includes(String(name || '').trim().toLowerCase());
  }

  function updateMlsAgentProfileVisibility() {
    const section = document.getElementById('user-mls-agent-section');
    section.classList.toggle('hidden', !selectedRoleNames().some(isAgentRoleName));
  }

  function updateMlsAgentHelp() {
    const selected = document.getElementById('user-mls-agent-profile-id').value;
    const help = document.getElementById('user-mls-agent-help');

    help.textContent = selected === '__create__'
      ? 'Al guardar se creará un perfil local con el nombre, correo e imagen del usuario dentro de la agencia MLS principal.'
      : 'Puedes vincular un perfil MLS existente o crear uno nuevo en la agencia MLS principal.';
  }

  function appendSelectOption(select, value, label) {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    select.appendChild(option);
  }

  function roleNamesFromUser(user) {
    const roles = Array.isArray(user?.roles) ? user.roles : [];
    const webRoles = roles.filter(role => role.guard_name === 'web');
    const source = webRoles.length > 0 ? webRoles : roles;

    return Array.from(new Set(
      source
        .map(role => role.name)
        .filter(Boolean)
    )).sort((a, b) => a.localeCompare(b));
  }

  function renderRoleBadges(user) {
    const names = roleNamesFromUser(user);

    if (names.length === 0) {
      return '<span class="text-xs px-2 py-0.5 rounded-full bg-[var(--c-surface)] text-[var(--c-muted)] border border-[var(--c-border)]">Sin roles</span>';
    }

    return names.map(name => (
      `<span class="text-xs px-2 py-0.5 rounded-full bg-[var(--c-surface)] text-[var(--c-text)] border border-[var(--c-border)]">${escapeHtml(name)}</span>`
    )).join('');
  }

  function renderMlsAgentBadge(user) {
    if (!roleNamesFromUser(user).some(isAgentRoleName)) {
      return '';
    }

    return user.mls_agent
      ? '<span class="text-xs px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300">Perfil público vinculado</span>'
      : '<span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300">Sin perfil público</span>';
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[char]));
  }

  function escapeAttr(value) {
    return escapeHtml(value);
  }

  function showError(title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: 'CLIENT_ERROR',
        errors: { general: [message] }
      }
    }));
  }

  function showApiError(title, apiResponse) {
    console.error('API Error:', apiResponse);
    const response = apiResponse || {};

    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: response.message || 'Error desconocido',
        code: response.code || 'UNKNOWN_ERROR',
        errors: response.errors || null,
        status: response.status || null,
        raw: response
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
