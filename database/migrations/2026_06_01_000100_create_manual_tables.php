<?php

use App\Support\ManualContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $guards = ['web', 'api'];

    private array $permissions = [
        'menu.manual.view',
        'menu.manual-articles.view',
        'manual.view',
        'manual.manage',
    ];

    public function up(): void
    {
        Schema::create('manual_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();
            $table->string('required_permission', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('manual_articles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_section_id')->constrained('manual_sections')->cascadeOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('title', 180);
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('required_permission', 180)->nullable();
            $table->string('related_route_name', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['manual_section_id', 'is_active', 'sort_order']);
        });

        Schema::create('manual_article_tutorial_video', function (Blueprint $table): void {
            $table->foreignId('manual_article_id')->constrained('manual_articles')->cascadeOnDelete();
            $table->foreignId('tutorial_video_id')->constrained('tutorial_videos')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['manual_article_id', 'tutorial_video_id'], 'manual_article_video_primary');
        });

        $this->syncPermissions();
        ManualContent::seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_article_tutorial_video');
        Schema::dropIfExists('manual_articles');
        Schema::dropIfExists('manual_sections');

        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->guards as $guard) {
            $permissions = Permission::where('guard_name', $guard)
                ->whereIn('name', $this->permissions)
                ->get();

            foreach (Role::where('guard_name', $guard)->get() as $role) {
                $role->permissions()->detach($permissions->pluck('id')->all());
            }

            Permission::where('guard_name', $guard)
                ->whereIn('name', $this->permissions)
                ->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function syncPermissions(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->guards as $guard) {
            foreach ($this->permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }

            $this->attachRolePermissions($guard, 'manager', $this->permissions);
            $this->attachRolePermissions($guard, 'assistant', [
                'menu.manual.view',
                'manual.view',
            ]);
            $this->attachRolePermissions($guard, 'agente', [
                'menu.manual.view',
                'manual.view',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function attachRolePermissions(string $guard, string $roleName, array $permissions): void
    {
        $role = Role::where('name', $roleName)
            ->where('guard_name', $guard)
            ->first();

        if (! $role) {
            return;
        }

        $permissionIds = Permission::where('guard_name', $guard)
            ->whereIn('name', $permissions)
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);
    }
};
