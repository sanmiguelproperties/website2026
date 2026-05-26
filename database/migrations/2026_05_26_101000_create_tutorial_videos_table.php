<?php

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
        'menu.tutorials.view',
        'menu.tutorial-videos.view',
        'tutorials.view',
        'tutorials.manage',
    ];

    public function up(): void
    {
        Schema::create('tutorial_videos', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('youtube_url');
            $table->string('youtube_video_id', 32);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('youtube_video_id');
        });

        $this->syncPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorial_videos');

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
                'menu.tutorials.view',
                'tutorials.view',
            ]);
            $this->attachRolePermissions($guard, 'agente', [
                'menu.tutorials.view',
                'tutorials.view',
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
