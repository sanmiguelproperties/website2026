<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('agency_team_members');

        if (Schema::hasTable('manual_articles')) {
            DB::table('manual_articles')
                ->whereIn('slug', ['administrar-equipo', 'equipo-publico-referencia'])
                ->delete();
        }

        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (Schema::hasTable($permissionsTable)) {
            DB::table($permissionsTable)
                ->where('name', 'menu.team-members.view')
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::create('agency_team_members', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 180);
            $table->string('position_es', 180);
            $table->string('position_en', 180)->nullable();
            $table->string('department_es', 120)->nullable();
            $table->string('department_en', 120)->nullable();
            $table->text('bio_es')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('specialties_es')->nullable();
            $table->text('specialties_en')->nullable();
            $table->string('email', 180)->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('whatsapp', 60)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->foreignId('photo_media_asset_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index(['is_active']);
            $table->index(['is_featured']);
            $table->index(['sort_order']);
            $table->index(['department_es']);
            $table->index(['department_en']);
        });
    }
};
