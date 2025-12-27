<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
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

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors());
        }

        $user->syncRoles($request->roles);

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

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors());
        }

        $user->removeRole($request->roles);

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
}