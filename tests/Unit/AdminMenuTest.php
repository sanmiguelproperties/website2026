<?php

namespace Tests\Unit;

use App\Support\AdminMenu;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\TestCase;

class AdminMenuTest extends TestCase
{
    public function test_it_hides_items_and_groups_without_required_permissions(): void
    {
        $user = new AdminMenuUser(['dashboard.view']);

        $this->assertTrue(AdminMenu::canAccessItem($user, 'dashboard'));
        $this->assertFalse(AdminMenu::canAccessItem($user, 'users'));
        $this->assertTrue(AdminMenu::groupVisible($user, 1));
        $this->assertFalse(AdminMenu::groupVisible($user, 2));
        $this->assertFalse(AdminMenu::groupVisible($user, 6));
    }

    public function test_crm_group_contains_clients_leads_and_calendar_items(): void
    {
        $user = new AdminMenuUser(['clients.view']);
        $leadsUser = new AdminMenuUser(['leads.view']);
        $calendarUser = new AdminMenuUser(['calendar.view']);

        $this->assertTrue(AdminMenu::canAccessItem($user, 'clients'));
        $this->assertTrue(AdminMenu::groupVisible($user, 6));
        $this->assertTrue(AdminMenu::canAccessItem($leadsUser, 'property-contact-requests'));
        $this->assertTrue(AdminMenu::groupVisible($leadsUser, 6));
        $this->assertTrue(AdminMenu::canAccessItem($calendarUser, 'calendar'));
        $this->assertTrue(AdminMenu::groupVisible($calendarUser, 6));
        $this->assertFalse(AdminMenu::groupVisible($user, 1));
    }

    public function test_first_accessible_route_follows_menu_order(): void
    {
        $user = new AdminMenuUser(['integrations.view']);

        $this->assertSame('easybroker', AdminMenu::firstAccessibleRoute($user));
    }

    public function test_corporate_email_configuration_requires_super_admin(): void
    {
        $user = new AdminMenuUser(['integrations.config.edit']);

        $this->assertFalse(AdminMenu::canAccessItem($user, 'corporate-email.configuration'));
        $this->assertFalse(AdminMenu::groupVisible($user, 2));
        $this->assertFalse(AdminMenu::groupVisible($user, 5));
    }

    public function test_corporate_email_inbox_uses_dashboard_permission(): void
    {
        $user = new AdminMenuUser(['dashboard.view']);

        $this->assertTrue(AdminMenu::canAccessItem($user, 'corporate-email.inbox'));
        $this->assertTrue(AdminMenu::canAccessItem($user, 'corporate-email.outbox'));
        $this->assertTrue(AdminMenu::canAccessItem($user, 'corporate-email.compose'));
        $this->assertTrue(AdminMenu::groupVisible($user, 5));
        $this->assertFalse(AdminMenu::canAccessItem($user, 'corporate-email.configuration'));
    }

    public function test_super_admin_can_access_every_menu_item(): void
    {
        $user = new AdminMenuUser([], true);

        $this->assertTrue(AdminMenu::canAccessItem($user, 'rbac'));
        $this->assertTrue(AdminMenu::canAccessItem($user, 'corporate-email.configuration'));
        $this->assertTrue(AdminMenu::groupVisible($user, 4));
        $this->assertSame('dashboard', AdminMenu::firstAccessibleRoute($user));
    }
}

class AdminMenuUser implements Authenticatable
{
    public function __construct(
        private readonly array $permissions,
        private readonly bool $superAdmin = false,
    ) {}

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(string $role): bool
    {
        return $this->superAdmin && $role === 'super-admin';
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 1;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
