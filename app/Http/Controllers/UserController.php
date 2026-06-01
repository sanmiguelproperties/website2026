<?php

namespace App\Http\Controllers;

use App\Models\MLSAgent;
use App\Models\User;
use App\Services\MlsAgentProfileService;
use App\Services\RbacMirror;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected MlsAgentProfileService $mlsAgentProfiles
    ) {}

    /**
     * GET /api/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['profileImage', 'roles:id,name,guard_name', 'mlsAgent.office']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'created_at');
        $validOrders = ['created_at', 'updated_at', 'name', 'email'];
        if (! in_array($order, $validOrders, true)) {
            $order = 'created_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $data = $query->paginate($perPage);

        return $this->apiSuccess('Listado de usuarios', 'USERS_LIST', $data);
    }

    /**
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'users.create')) {
            return $this->apiForbidden('No tienes permisos para crear usuarios', 'USERS_CREATE_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'profile_image_id' => 'nullable|exists:media_assets,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'profile_image_id' => $request->input('profile_image_id'),
            'is_active' => $request->boolean('is_active', true),
        ];

        $user = User::create($userData);

        // Cargar la relación de imagen de perfil
        $user->load(['profileImage', 'roles:id,name,guard_name', 'mlsAgent.office']);

        return $this->apiCreated('Usuario creado exitosamente', 'USER_CREATED', $user);
    }

    /**
     * GET /api/users/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = User::with(['profileImage', 'roles:id,name,guard_name', 'mlsAgent.office'])->find($id);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        return $this->apiSuccess('Usuario obtenido', 'USER_SHOWN', $user);
    }

    /**
     * PATCH /api/users/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'users.edit')) {
            return $this->apiForbidden('No tienes permisos para editar usuarios', 'USERS_EDIT_FORBIDDEN');
        }

        $user = User::find($id);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:8',
            'profile_image_id' => 'nullable|exists:media_assets,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $updateData = $validator->validated();

        if (array_key_exists('is_active', $updateData) && ! Rbac::canAny($request->user('api'), 'users.deactivate')) {
            return $this->apiForbidden('No tienes permisos para activar o desactivar usuarios', 'USERS_DEACTIVATE_FORBIDDEN');
        }

        $currentUser = $request->user('api') ?? auth()->user();
        if (
            array_key_exists('is_active', $updateData)
            && ! $updateData['is_active']
            && $currentUser
            && (int) $currentUser->id === (int) $user->id
        ) {
            return $this->apiForbidden('No puedes desactivar tu propio usuario', 'SELF_DEACTIVATE_FORBIDDEN');
        }

        // Hash password if provided
        if (isset($updateData['password']) && ! empty($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        } elseif (isset($updateData['password']) && empty($updateData['password'])) {
            unset($updateData['password']); // Don't update if empty
        }

        $user->update($updateData);

        // Cargar la relación de imagen de perfil
        $user->load(['profileImage', 'roles:id,name,guard_name', 'mlsAgent.office']);

        return $this->apiSuccess('Usuario actualizado', 'USER_UPDATED', $user);
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'users.delete')) {
            return $this->apiForbidden('No tienes permisos para eliminar usuarios', 'USERS_DELETE_FORBIDDEN');
        }

        $user = User::find($id);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        // No permitir eliminar al usuario actual (si está autenticado)
        $currentUser = $request->user('api') ?? auth()->user();
        if ($currentUser && (int) $currentUser->id === (int) $user->id) {
            return $this->apiForbidden('No puedes eliminar tu propio usuario', 'SELF_DELETE_FORBIDDEN');
        }

        $user->delete();

        return $this->apiSuccess('Usuario eliminado', 'USER_DELETED', null);
    }

    /**
     * GET /api/users/mls-agent-options
     */
    public function mlsAgentOptions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer|exists:users,id',
            'search' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $userId = $data['user_id'] ?? null;
        $query = MLSAgent::query()
            ->with(['office', 'user:id,name,email'])
            ->whereHas('office', fn ($office) => $office->primary())
            ->where(function ($query) use ($userId): void {
                $query->whereNull('user_id');

                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            });

        if (! empty($data['search'])) {
            $search = trim($data['search']);
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mls_agent_id', 'like', "%{$search}%");
            });
        }

        return $this->apiSuccess(
            'Perfiles MLS disponibles',
            'USER_MLS_AGENT_OPTIONS',
            $query->orderBy('name')->orderBy('id')->limit(200)->get()
        );
    }

    /**
     * PUT /api/users/{user}/mls-agent
     */
    public function updateMlsAgent(Request $request, User $user): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'users.edit')) {
            return $this->apiForbidden('No tienes permisos para editar usuarios', 'USERS_EDIT_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'mls_agent_profile_id' => 'nullable|integer|exists:mls_agents,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        try {
            $profileId = $validator->validated()['mls_agent_profile_id'] ?? null;
            $currentProfile = $user->mlsAgent()->first();

            if (! $profileId) {
                if ($currentProfile) {
                    $this->mlsAgentProfiles->linkUser($currentProfile, null);
                }
            } else {
                $profile = MLSAgent::query()->findOrFail($profileId);
                $this->mlsAgentProfiles->linkUser($profile, $user, true);
            }
        } catch (ValidationException $e) {
            return $this->apiValidationError($e->errors());
        }

        return $this->apiSuccess(
            'Perfil MLS relacionado',
            'USER_MLS_AGENT_UPDATED',
            $user->fresh()->load(['profileImage', 'roles:id,name,guard_name', 'mlsAgent.office'])
        );
    }

    /**
     * POST /api/users/{user}/mls-agent
     */
    public function createMlsAgent(Request $request, User $user): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'users.edit')) {
            return $this->apiForbidden('No tienes permisos para editar usuarios', 'USERS_EDIT_FORBIDDEN');
        }

        try {
            $profile = $this->mlsAgentProfiles->createForUser($user);
        } catch (ValidationException $e) {
            return $this->apiValidationError($e->errors());
        }

        return $this->apiCreated(
            'Perfil MLS creado y relacionado',
            'USER_MLS_AGENT_CREATED',
            $profile->load(['photoMediaAsset', 'office', 'user'])
        );
    }

    /**
     * GET /api/users/{id}/roles
     */
    public function getUserRoles($userId): JsonResponse
    {
        $user = User::with('roles')->find($userId);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        $roles = $user->roles
            ->sortBy([['guard_name', 'asc'], ['name', 'asc']])
            ->values();

        return $this->apiSuccess('Roles del usuario obtenidos', 'USER_ROLES', $roles);
    }

    /**
     * GET /api/users/{id}/permissions
     */
    public function getUserPermissions($userId): JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        $permissions = $user->getAllPermissions();

        return $this->apiSuccess('Permisos del usuario obtenidos', 'USER_PERMISSIONS', $permissions);
    }

    /**
     * POST /api/users/{userId}/roles/assign
     */
    public function assignRoles(Request $request, $userId): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'rbac.manage')) {
            return $this->apiForbidden('No tienes permisos para administrar roles', 'RBAC_MANAGE_FORBIDDEN');
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'roles' => 'present|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $roleIds = collect($validator->validated()['roles'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $roleNames = Role::whereIn('id', $roleIds)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $currentUser = $request->user('api') ?? auth()->user();
        if (
            $currentUser
            && (int) $currentUser->id === (int) $user->id
            && Rbac::isSuperAdmin($user)
            && ! in_array(Rbac::SUPER_ADMIN, $roleNames, true)
        ) {
            return $this->apiForbidden('No puedes quitarte tu propio rol super-admin', 'SELF_SUPER_ADMIN_ROLE_FORBIDDEN');
        }

        app(RbacMirror::class)->syncUserRolesBothGuardsByNames($user, $roleNames);
        $this->unlinkMlsProfileWhenUserIsNotAgent($user);

        $user->load(['roles' => fn ($query) => $query->orderBy('guard_name')->orderBy('name')]);

        return $this->apiSuccess('Roles asignados al usuario', 'USER_ROLES_ASSIGNED', $user->roles);
    }

    /**
     * POST /api/users/{userId}/roles/revoke
     */
    public function revokeRoles(Request $request, $userId): JsonResponse
    {
        if (! Rbac::canAny($request->user('api'), 'rbac.manage')) {
            return $this->apiForbidden('No tienes permisos para administrar roles', 'RBAC_MANAGE_FORBIDDEN');
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->apiNotFound('Usuario no encontrado', 'USER_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $roleIds = $request->input('roles');
        $user->roles()->detach($roleIds);
        $this->unlinkMlsProfileWhenUserIsNotAgent($user);

        return $this->apiSuccess('Roles revocados del usuario', 'USER_ROLES_REVOKED', $user->roles);
    }

    private function unlinkMlsProfileWhenUserIsNotAgent(User $user): void
    {
        $isAgent = $user->roles()
            ->whereIn('name', ['agente', 'agent'])
            ->exists();

        if (! $isAgent && ($profile = $user->mlsAgent()->first())) {
            $this->mlsAgentProfiles->linkUser($profile, null);
        }
    }
}
