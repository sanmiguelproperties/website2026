<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameRole('agent', 'agente');
    }

    public function down(): void
    {
        $this->renameRole('agente', 'agent');
    }

    private function renameRole(string $from, string $to): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        $rolesTable = $tableNames['roles'] ?? 'roles';
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        $sourceRoles = DB::table($rolesTable)->where('name', $from)->get();

        foreach ($sourceRoles as $sourceRole) {
            $targetRole = DB::table($rolesTable)
                ->where('name', $to)
                ->where('guard_name', $sourceRole->guard_name)
                ->first();

            if (!$targetRole) {
                DB::table($rolesTable)
                    ->where('id', $sourceRole->id)
                    ->update([
                        'name' => $to,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            $assignments = DB::table($modelHasRolesTable)
                ->where($pivotRole, $sourceRole->id)
                ->get();

            foreach ($assignments as $assignment) {
                $row = (array) $assignment;
                $row[$pivotRole] = $targetRole->id;

                DB::table($modelHasRolesTable)->insertOrIgnore($row);
            }

            DB::table($modelHasRolesTable)->where($pivotRole, $sourceRole->id)->delete();

            $permissions = DB::table($roleHasPermissionsTable)
                ->where($pivotRole, $sourceRole->id)
                ->pluck($pivotPermission);

            foreach ($permissions as $permissionId) {
                DB::table($roleHasPermissionsTable)->insertOrIgnore([
                    $pivotRole => $targetRole->id,
                    $pivotPermission => $permissionId,
                ]);
            }

            DB::table($roleHasPermissionsTable)->where($pivotRole, $sourceRole->id)->delete();
            DB::table($rolesTable)->where('id', $sourceRole->id)->delete();
        }

        if (app()->bound(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
