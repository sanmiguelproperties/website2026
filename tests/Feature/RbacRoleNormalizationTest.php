<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\RbacMirror;
use App\Services\RoleNameNormalizer;
use App\Support\Rbac;
use App\Support\RbacNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacRoleNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_model_stores_and_finds_names_in_lowercase(): void
    {
        $role = Role::findOrCreate('Manager', 'web');

        $this->assertSame('manager', $role->name);
        $this->assertSame($role->id, Role::findOrCreate('MANAGER', 'web')->id);
        $this->assertDatabaseCount('roles', 1);
    }

    public function test_normalizer_merges_case_duplicates_without_losing_users_or_permissions(): void
    {
        $firstRoleId = $this->insertLegacyRole('Agente');
        $canonicalRoleId = $this->insertLegacyRole('agente');
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $firstPermission = Permission::findOrCreate('properties.view', 'web');
        $secondPermission = Permission::findOrCreate('clients.view', 'web');

        $this->attachRoleToUser($firstRoleId, $firstUser);
        $this->attachRoleToUser($canonicalRoleId, $secondUser);
        $this->attachPermissionToRole($firstPermission->id, $firstRoleId);
        $this->attachPermissionToRole($secondPermission->id, $canonicalRoleId);

        $stats = app(RoleNameNormalizer::class)->normalizeExistingRoles();

        $this->assertSame(['roles_renamed' => 0, 'roles_merged' => 1], $stats);
        $this->assertDatabaseMissing('roles', ['id' => $firstRoleId]);
        $this->assertDatabaseHas('roles', ['id' => $canonicalRoleId, 'name' => 'agente']);
        $this->assertDatabaseHas('model_has_roles', ['role_id' => $canonicalRoleId, 'model_id' => $firstUser->id]);
        $this->assertDatabaseHas('model_has_roles', ['role_id' => $canonicalRoleId, 'model_id' => $secondUser->id]);
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => $canonicalRoleId, 'permission_id' => $firstPermission->id]);
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => $canonicalRoleId, 'permission_id' => $secondPermission->id]);
    }

    public function test_mixed_case_super_admin_keeps_the_authorization_bypass_during_transition(): void
    {
        $roleId = $this->insertLegacyRole('Super-Admin');
        $user = User::factory()->create();
        $this->attachRoleToUser($roleId, $user);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue(Rbac::isSuperAdmin($user));
        $this->assertTrue(Rbac::canAny($user, 'permission.not.assigned'));
    }

    public function test_syncing_user_roles_normalizes_names_for_both_guards(): void
    {
        $user = User::factory()->create();

        app(RbacMirror::class)->syncUserRolesBothGuardsByNames($user, [' Manager ']);

        $this->assertSame(
            ['api:manager', 'web:manager'],
            $user->roles()
                ->get()
                ->map(fn (Role $role) => $role->guard_name.':'.$role->name)
                ->sort()
                ->values()
                ->all()
        );
    }

    public function test_role_notifications_match_names_without_case_sensitivity(): void
    {
        Notification::fake();
        $roleId = $this->insertLegacyRole('Agente');
        $user = User::factory()->create();
        $this->attachRoleToUser($roleId, $user);

        $this->assertSame([$user->id], User::query()->withRoleNames(['AGENTE'])->pluck('id')->all());

        RbacNotifications::notifyRoles(['AGENTE'], new RoleCaseNotification());

        Notification::assertSentTo($user, RoleCaseNotification::class);
    }

    public function test_repair_command_normalizes_legacy_roles_and_creates_the_missing_guard_mirror(): void
    {
        $this->insertLegacyRole('Assistant');

        $this->assertSame(0, Artisan::call('rbac:repair-mirrors'));

        $this->assertDatabaseHas('roles', ['name' => 'assistant', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'assistant', 'guard_name' => 'api']);
        $this->assertStringContainsString('roles_renamed: 1', Artisan::output());
    }

    private function insertLegacyRole(string $name, string $guard = 'web'): int
    {
        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
            'guard_name' => $guard,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachRoleToUser(int $roleId, User $user): void
    {
        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    private function attachPermissionToRole(int $permissionId, int $roleId): void
    {
        DB::table('role_has_permissions')->insert([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]);
    }
}

class RoleCaseNotification extends BaseNotification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
