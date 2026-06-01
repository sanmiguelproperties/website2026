<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\RbacMirror;
use App\Support\RoleName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    /**
     * Asignar roles a un usuario
     */
    public function assignRoles(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->apiNotFound();
        }

        $roleNames = RoleName::normalizeMany($request->input('roles', []));
        $validator = Validator::make(['roles' => $roleNames], [
            'roles' => 'required|array',
            'roles.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors());
        }

        if ($missing = $this->missingRoleNames($roleNames)) {
            return $this->apiValidationError(['roles' => ['No existen los roles: '.implode(', ', $missing)]]);
        }

        app(RbacMirror::class)->syncUserRolesBothGuardsByNames($user, $roleNames);

        return $this->apiSuccess('Roles asignados exitosamente', null, $user->load('roles'));
    }

    /**
     * Remover roles de un usuario
     */
    public function revokeRoles(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->apiNotFound();
        }

        $roleNames = RoleName::normalizeMany($request->input('roles', []));
        $validator = Validator::make(['roles' => $roleNames], [
            'roles' => 'required|array',
            'roles.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors());
        }

        if ($missing = $this->missingRoleNames($roleNames)) {
            return $this->apiValidationError(['roles' => ['No existen los roles: '.implode(', ', $missing)]]);
        }

        $roleIds = Role::query()
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $roleNames)
            ->pluck('id');
        $user->roles()->detach($roleIds);

        return $this->apiSuccess('Roles removidos exitosamente', null, $user->load('roles'));
    }

    /**
     * Obtener roles de un usuario
     */
    public function getUserRoles($userId)
    {
        $user = User::with('roles')->find($userId);

        if (!$user) {
            return $this->apiNotFound();
        }

        return $this->apiSuccess('Roles del usuario obtenidos exitosamente', null, $user->roles);
    }

    /**
     * Obtener permisos de un usuario
     */
    public function getUserPermissions($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->apiNotFound();
        }

        return $this->apiSuccess('Permisos del usuario obtenidos exitosamente', null, $user->getAllPermissions());
    }

    /**
     * @return array<int, string>
     */
    private function missingRoleNames(array $roleNames): array
    {
        $existing = Role::query()
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $roleNames)
            ->pluck('name')
            ->map(fn (string $name) => RoleName::normalize($name))
            ->unique()
            ->all();

        return array_values(array_diff($roleNames, $existing));
    }
}
