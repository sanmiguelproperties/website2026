<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manual_articles')) {
            DB::table('manual_articles')
                ->whereIn('slug', ['administrar-agencias', 'agencias-principal-referencia'])
                ->delete();
        }

        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (Schema::hasTable($permissionsTable)) {
            DB::table($permissionsTable)
                ->where('name', 'menu.agencies.view')
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        //
    }
};
