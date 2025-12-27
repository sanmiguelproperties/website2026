<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\RbacMirror;

class RolePermissionController extends Controller
{
    protected RbacMirror $rbacMirror;

    public function __construct()
    {
        $this->rbacMirror = app(RbacMirror::class);
    }

    // GET /api/rbac/roles/{roleId}/permissions
    public function index(Request $request, int $roleId)
    {
        $validator = Validator::make($request->query(), [
            'guard' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $request->query('guard', 'web');

        $role = Role::where('id', $roleId)->where('guard_name', $guard)->first();
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        $permissions = $role->permissions()->where('guard_name', $guard)->get();

        return $this->jsonSuccess($permissions, 'Permisos obtenidos');
    }

    // POST /api/rbac/roles/{roleId}/permissions/attach
    public function attach(Request $request, int $roleId)
    {
        $payload = [
            'permissions' => $request->input('permissions', []),
            'mode' => $request->input('mode', 'by_id'),
            'guard_name' => $request->input('guard_name', 'web'),
        ];

        $validator = Validator::make($payload, [
            'permissions' => ['required','array','min:1'],
            'mode' => ['required','in:by_id,by_name'],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $payload['guard_name'];

        $role = Role::where('id', $roleId)->where('guard_name', $guard)->first();
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        $resolved = $this->resolvePermissions($payload['permissions'], $payload['mode'], $guard);
        if ($resolved['missing']) {
            return $this->jsonError('Algunos permisos no existen para el guard especificado', 422, [
                'missing' => $resolved['missing'],
            ]);
        }

        // Usar métodos de Spatie para limpiar caché y mantener eventos
        $role->givePermissionTo($resolved['collection']->all());
        // Replicar en guard espejo
        $this->rbacMirror->attachPermissions($role, $resolved['collection']);

        $current = $role->permissions()->where('guard_name', $guard)->get();

        return $this->jsonSuccess($current, 'Permisos asignados');
    }

    // POST /api/rbac/roles/{roleId}/permissions/sync
    public function sync(Request $request, int $roleId)
    {
        $payload = [
            'permissions' => $request->input('permissions', []),
            'mode' => $request->input('mode', 'by_id'),
            'guard_name' => $request->input('guard_name', 'web'),
        ];

        $validator = Validator::make($payload, [
            'permissions' => ['required','array','min:1'],
            'mode' => ['required','in:by_id,by_name'],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $payload['guard_name'];

        $role = Role::where('id', $roleId)->where('guard_name', $guard)->first();
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        $resolved = $this->resolvePermissions($payload['permissions'], $payload['mode'], $guard);
        if ($resolved['missing']) {
            return $this->jsonError('Algunos permisos no existen para el guard especificado', 422, [
                'missing' => $resolved['missing'],
            ]);
        }

        $role->syncPermissions($resolved['collection']->all());
        // Replicar en guard espejo
        $this->rbacMirror->syncPermissions($role, $resolved['collection']);

        $current = $role->permissions()->where('guard_name', $guard)->get();

        return $this->jsonSuccess($current, 'Permisos sincronizados');
    }

    // POST /api/rbac/roles/{roleId}/permissions/detach
    public function detach(Request $request, int $roleId)
    {
        $payload = [
            'permissions' => $request->input('permissions', []),
            'mode' => $request->input('mode', 'by_id'),
            'guard_name' => $request->input('guard_name', 'web'),
        ];

        $validator = Validator::make($payload, [
            'permissions' => ['required','array','min:1'],
            'mode' => ['required','in:by_id,by_name'],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $payload['guard_name'];

        $role = Role::where('id', $roleId)->where('guard_name', $guard)->first();
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        $resolved = $this->resolvePermissions($payload['permissions'], $payload['mode'], $guard);
        if ($resolved['missing']) {
            return $this->jsonError('Algunos permisos no existen para el guard especificado', 422, [
                'missing' => $resolved['missing'],
            ]);
        }

        // Idempotente: revocar un permiso no asignado no debe fallar
        $role->revokePermissionTo($resolved['collection']->all());
        // Replicar en guard espejo
        $this->rbacMirror->detachPermissions($role, $resolved['collection']);

        $current = $role->permissions()->where('guard_name', $guard)->get();

        return $this->jsonSuccess($current, 'Permisos removidos');
    }

    /**
     * Resuelve permisos por ids o nombres en el guard especificado.
     *
     * @param array $items ids o nombres
     * @param string $mode 'by_id'|'by_name'
     * @param string $guard
     * @return array{collection:\Illuminate\Support\Collection, missing:array}
     */
    private function resolvePermissions(array $items, string $mode, string $guard): array
    {
        $items = array_values($items);

        if ($mode === 'by_id') {
            // Normalizar a enteros positivos
            $ids = array_values(array_unique(array_map(fn($v) => (int) $v, $items)));
            $ids = array_filter($ids, fn($v) => $v > 0);

            $found = Permission::whereIn('id', $ids)->where('guard_name', $guard)->get();
            $foundIds = $found->pluck('id')->all();
            $missing = array_values(array_diff($ids, $foundIds));

            return ['collection' => $found, 'missing' => $missing];
        }

        // by_name
        $names = array_values(array_unique(array_map(fn($v) => trim((string) $v), $items)));
        $names = array_filter($names, fn($v) => $v !== '');

        $found = Permission::whereIn('name', $names)->where('guard_name', $guard)->get();
        $foundNames = $found->pluck('name')->all();
        $missing = array_values(array_diff($names, $foundNames));

        return ['collection' => $found, 'missing' => $missing];
    }
}