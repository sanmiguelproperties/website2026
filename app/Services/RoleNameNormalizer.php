<?php

namespace App\Services;

use App\Support\RoleName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class RoleNameNormalizer
{
    /**
     * @return array{roles_renamed:int,roles_merged:int}
     */
    public function normalizeExistingRoles(): array
    {
        $tables = config('permission.table_names');
        $columns = config('permission.column_names');
        $rolesTable = $tables['roles'] ?? 'roles';
        $modelHasRolesTable = $tables['model_has_roles'] ?? 'model_has_roles';
        $roleHasPermissionsTable = $tables['role_has_permissions'] ?? 'role_has_permissions';
        $roleKey = $columns['role_pivot_key'] ?? 'role_id';

        if (! Schema::hasTable($rolesTable)) {
            return ['roles_renamed' => 0, 'roles_merged' => 0];
        }

        $stats = DB::transaction(function () use (
            $rolesTable,
            $modelHasRolesTable,
            $roleHasPermissionsTable,
            $roleKey
        ): array {
            $renamed = 0;
            $merged = 0;
            $roles = DB::table($rolesTable)
                ->orderBy('id')
                ->get(['id', 'name', 'guard_name']);

            $groups = $roles->groupBy(fn ($role) => $role->guard_name.'|'.RoleName::normalize($role->name));

            foreach ($groups as $group) {
                $canonicalName = RoleName::normalize($group->first()->name);

                if ($canonicalName === '') {
                    continue;
                }

                $survivor = $group->first(fn ($role) => $role->name === $canonicalName) ?? $group->first();

                foreach ($group as $role) {
                    if ((int) $role->id === (int) $survivor->id) {
                        continue;
                    }

                    $this->movePivotRows($modelHasRolesTable, $roleKey, (int) $role->id, (int) $survivor->id);
                    $this->movePivotRows($roleHasPermissionsTable, $roleKey, (int) $role->id, (int) $survivor->id);
                    DB::table($rolesTable)->where('id', $role->id)->delete();
                    $merged++;
                }

                if ($survivor->name !== $canonicalName) {
                    DB::table($rolesTable)
                        ->where('id', $survivor->id)
                        ->update(['name' => $canonicalName]);
                    $renamed++;
                }
            }

            return ['roles_renamed' => $renamed, 'roles_merged' => $merged];
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $stats;
    }

    private function movePivotRows(string $table, string $roleKey, int $sourceRoleId, int $targetRoleId): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where($roleKey, $sourceRoleId)
            ->get()
            ->each(function ($row) use ($table, $roleKey, $targetRoleId): void {
                $attributes = (array) $row;
                $attributes[$roleKey] = $targetRoleId;

                DB::table($table)->insertOrIgnore($attributes);
            });

        DB::table($table)->where($roleKey, $sourceRoleId)->delete();
    }
}
