<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Services\RbacMirror;

class RoleController extends Controller
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

        $query = Role::query()->where('guard_name', $guard);

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

        $duplicate = Role::where('name', $payload['name'])->where('guard_name', $guard)->exists();
        if ($duplicate) {
            return $this->jsonError('El rol ya existe para el guard especificado', 409);
        }

        $role = Role::create([
            'name' => $payload['name'],
            'guard_name' => $guard,
        ]);

        // Replicar al guard espejo
        $this->rbacMirror->mirrorRoleCreated($role);

        return $this->jsonSuccess($role, 'Rol creado', 201);
    }

    public function show(Request $request, int $id)
    {
        $guard = $request->query('guard', 'web');

        $role = Role::where('id', $id)->where('guard_name', $guard)->first();

        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        return $this->jsonSuccess($role, 'Rol encontrado');
    }

    public function update(Request $request, int $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        $oldName = $role->name;
        $oldGuard = $role->guard_name;

        $payload = [
            'name' => $request->has('name') ? trim((string) $request->input('name')) : $role->name,
            'guard_name' => $request->input('guard_name', $role->guard_name),
        ];

        $validator = Validator::make($payload, [
            'name' => [
                'required','string','max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('guard_name', $payload['guard_name']))
                    ->ignore($id),
            ],
            'guard_name' => ['sometimes','in:web,api'],
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validación fallida', 422, $validator->errors()->toArray());
        }

        // Extra chequeo para conflictos de unicidad
        $exists = Role::where('id', '!=', $id)
            ->where('guard_name', $payload['guard_name'])
            ->where('name', $payload['name'])
            ->exists();

        if ($exists) {
            return $this->jsonError('Ya existe un rol con ese nombre en el guard especificado', 409);
        }

        $role->name = $payload['name'];
        $role->guard_name = $payload['guard_name'];
        $role->save();

        // Replicar cambios al guard espejo
        $this->rbacMirror->mirrorRoleUpdated($role, $oldName, $oldGuard);

        return $this->jsonSuccess($role, 'Rol actualizado');
    }

    public function destroy(Request $request, int $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->jsonError('Rol no encontrado', 404);
        }

        // Bloquear eliminación si el rol está asignado a algún usuario (en cualquier modelo del guard)
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelHasRoles = $tableNames['model_has_roles'] ?? 'model_has_roles';

        $assigned = DB::table($modelHasRoles)
            ->where($pivotRole, $role->id)
            ->exists();

        if ($assigned) {
            return $this->jsonError('No se puede eliminar un rol que está asignado a usuarios', 422, [
                'assignments' => ['El rol está asignado a uno o más usuarios. Revoca el rol antes de eliminar.'],
            ]);
        }

        $role->delete();

        // Replicar eliminación al guard espejo
        $this->rbacMirror->mirrorRoleDeleted($role);

        return $this->jsonSuccess(null, 'Rol eliminado');
    }

}