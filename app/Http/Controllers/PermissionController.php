<?php

namespace App\Http\Controllers;

use App\Services\RbacMirror;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

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
            'guard' => ['sometimes', 'in:web,api'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'in:name'],
            'order' => ['sometimes', 'in:asc,desc'],
            'q' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validacion fallida', 422, $validator->errors()->toArray());
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

        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];

        return $this->jsonSuccess($paginator->items(), '', 200, ['pagination' => $pagination]);
    }

    public function store(Request $request)
    {
        $payload = [
            'name' => trim((string) $request->input('name', '')),
            'guard_name' => $request->input('guard_name', 'web'),
        ];

        $validator = Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['sometimes', 'in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validacion fallida', 422, $validator->errors()->toArray());
        }

        $guard = $payload['guard_name'] ?? 'web';

        $duplicate = Permission::where('name', $payload['name'])->where('guard_name', $guard)->exists();
        if ($duplicate) {
            return $this->jsonError('El permiso ya existe para el guard especificado', 409);
        }

        $permission = DB::transaction(function () use ($payload, $guard) {
            $permission = Permission::create([
                'name' => $payload['name'],
                'guard_name' => $guard,
            ]);

            $this->rbacMirror->mirrorPermissionCreated($permission);

            return $permission->fresh();
        });

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

        if ($request->has('guard_name') && $request->input('guard_name') !== $permission->guard_name) {
            return $this->jsonError('No se permite cambiar guard_name en una actualizacion. Usa el guard correcto sin mover el registro.', 422);
        }

        $payload = [
            'name' => $request->has('name') ? trim((string) $request->input('name')) : $permission->name,
            'guard_name' => $permission->guard_name,
        ];

        $validator = Validator::make($payload, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where(fn ($q) => $q->where('guard_name', $payload['guard_name']))
                    ->ignore($id),
            ],
            'guard_name' => ['sometimes', 'in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validacion fallida', 422, $validator->errors()->toArray());
        }

        $exists = Permission::where('id', '!=', $id)
            ->where('guard_name', $payload['guard_name'])
            ->where('name', $payload['name'])
            ->exists();

        if ($exists) {
            return $this->jsonError('Ya existe un permiso con ese nombre en el guard especificado', 409);
        }

        $permission = DB::transaction(function () use ($permission, $payload, $oldName, $oldGuard) {
            $permission->name = $payload['name'];
            $permission->guard_name = $payload['guard_name'];
            $permission->save();

            $this->rbacMirror->mirrorPermissionUpdated($permission, $oldName, $oldGuard);

            return $permission->fresh();
        });

        return $this->jsonSuccess($permission, 'Permiso actualizado');
    }

    public function destroy(Request $request, int $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->jsonError('Permiso no encontrado', 404);
        }

        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelHasPermissions = $tableNames['model_has_permissions'] ?? 'model_has_permissions';
        $roleHasPermissions = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        $assignedToUsers = DB::table($modelHasPermissions)
            ->where($pivotPermission, $permission->id)
            ->exists();

        if ($assignedToUsers) {
            return $this->jsonError('No se puede eliminar un permiso que esta asignado a usuarios', 422, [
                'assignments' => ['El permiso esta asignado directamente a uno o mas usuarios. Revoca el permiso antes de eliminar.'],
            ]);
        }

        $assignedToRoles = DB::table($roleHasPermissions)
            ->where($pivotPermission, $permission->id)
            ->exists();

        if ($assignedToRoles) {
            return $this->jsonError('No se puede eliminar un permiso que esta asignado a roles', 422, [
                'assignments' => ['El permiso esta asignado a uno o mas roles. Remueve el permiso de esos roles antes de eliminar.'],
            ]);
        }

        DB::transaction(function () use ($permission) {
            $permission->delete();
            $this->rbacMirror->mirrorPermissionDeleted($permission);
        });

        return $this->jsonSuccess(null, 'Permiso eliminado');
    }
}
