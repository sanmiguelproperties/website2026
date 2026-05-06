<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $guards = ['web', 'api'];

    private array $removedPermissions = [
        'properties.create',
        'properties.edit.own',
        'properties.delete.own',
        'properties.restore',
    ];

    public function up(): void
    {
        $this->syncAgentPropertyMutationPermissions(false);
    }

    public function down(): void
    {
        $this->syncAgentPropertyMutationPermissions(true);
    }

    private function syncAgentPropertyMutationPermissions(bool $attach): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            return;
        }

        foreach ($this->guards as $guard) {
            $role = Role::where('name', 'agent')
                ->where('guard_name', $guard)
                ->first();

            if (!$role) {
                continue;
            }

            $permissionIds = Permission::where('guard_name', $guard)
                ->whereIn('name', $this->removedPermissions)
                ->pluck('id')
                ->all();

            if ($attach) {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            } else {
                $role->permissions()->detach($permissionIds);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
