<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Services\RbacMirror;

class PermissionController extends Controller
{
    protected RbacMirror $rbacMirror;

    public function __construct()
    {
        $this->rbacMirror = app(RbacMirror::class);
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'guard' => ['sometimes','in:web,api'],
            'page' => ['sometimes','integer','min:1'],
            'per_page' => ['sometimes','integer','min:1','max:100'],
            'sort' => ['sometimes','in:name'],
            'order' => ['sometimes','in:asc,desc'],
            'q' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $request->query('guard', 'web');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $sort = $request->query('sort', 'name');
        $order = $request->query('order', 'asc');
        $q = $request->query('q');

        $query = Permission::query()->where('guard_name', $guard);

        if ($q) {
            $query->where('name', 'like', '%'.$q.'%');
        }

        $query->orderBy($sort, $order);

        $paginator = $query->paginate($perPage);
        $data = $paginator->items();

        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];

        return $this->jsonSuccess($data, '', 200, ['pagination' => $pagination]);
    }

    public function store(Request $request)
    {
        $payload = [
            'name' => trim((string) $request->input('name', '')),
            'guard_name' => $request->input('guard_name', 'web'),
        ];

        $validator = Validator::make($payload, [
            'name' => ['required','string','max:255'],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        $guard = $payload['guard_name'] ?? 'web';

        $duplicate = Permission::where('name', $payload['name'])->where('guard_name', $guard)->exists();
        if ($duplicate) {
            return $this->jsonError('El permiso ya existe para el guard especificado', 409);
        }

        $permission = Permission::create([
            'name' => $payload['name'],
            'guard_name' => $guard,
        ]);

        // Replicar al guard espejo
        $this->rbacMirror->mirrorPermissionCreated($permission);

        return $this->jsonSuccess($permission, 'Permiso creado', 201);
    }

    public function show(Request $request, int $id)
    {
        $guard = $request->query('guard', 'web');

        $permission = Permission::where('id', $id)->where('guard_name', $guard)->first();

        if (!$permission) {
            return $this->jsonError('Permiso no encontrado', 404);
        }

        return $this->jsonSuccess($permission, 'Permiso encontrado');
    }

    public function update(Request $request, int $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->jsonError('Permiso no encontrado', 404);
        }

        $oldName = $permission->name;
        $oldGuard = $permission->guard_name;

        $payload = [
            'name' => $request->has('name') ? trim((string) $request->input('name')) : $permission->name,
            'guard_name' => $request->input('guard_name', $permission->guard_name),
        ];

        $validator = Validator::make($payload, [
            'name' => [
                'required','string','max:255',
                Rule::unique('permissions', 'name')
                    ->where(fn ($q) => $q->where('guard_name', $payload['guard_name']))
                    ->ignore($id),
            ],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        // Extra chequeo para conflictos de unicidad
        $exists = Permission::where('id', '!=', $id)
            ->where('guard_name', $payload['guard_name'])
            ->where('name', $payload['name'])
            ->exists();

        if ($exists) {
            return $this->jsonError('Ya existe un permiso con ese nombre en el guard especificado', 409);
        }

        $permission->name = $payload['name'];
        $permission->guard_name = $payload['guard_name'];
        $permission->save();

        // Replicar cambio al guard espejo
        $this->rbacMirror->mirrorPermissionUpdated($permission, $oldName, $oldGuard);

        return $this->jsonSuccess($permission, 'Permiso actualizado');
    }

    public function destroy(Request $request, int $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->jsonError('Permiso no encontrado', 404);
        }

        // Bloquear eliminación si el permiso está asignado directamente a algún usuario
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelHasPermissions = $tableNames['model_has_permissions'] ?? 'model_has_permissions';
        $roleHasPermissions = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        $assignedToUsers = DB::table($modelHasPermissions)
            ->where($pivotPermission, $permission->id)
            ->exists();

        if ($assignedToUsers) {
            return $this->jsonError('No se puede eliminar un permiso que está asignado a usuarios', 422, [
                'assignments' => ['El permiso está asignado directamente a uno o más usuarios. Revoca el permiso antes de eliminar.'],
            ]);
        }

        // Opcionalmente, bloquear si está asignado a roles (que podrían estar asociados a usuarios)
        $assignedToRoles = DB::table($roleHasPermissions)
            ->where($pivotPermission, $permission->id)
            ->exists();

        if ($assignedToRoles) {
            return $this->jsonError('No se puede eliminar un permiso que está asignado a roles', 422, [
                'assignments' => ['El permiso está asignado a uno o más roles. Remueve el permiso de esos roles antes de eliminar.'],
            ]);
        }

        $permission->delete();

        // Replicar eliminación al guard espejo
        $this->rbacMirror->mirrorPermissionDeleted($permission);

        return $this->jsonSuccess(null, 'Permiso eliminado');
    }
}