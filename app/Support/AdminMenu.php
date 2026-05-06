<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class AdminMenu
{
    private const ITEM_PERMISSIONS = [
        'dashboard' => 'dashboard.view',
        'properties' => 'properties.view',
        'zones' => 'settings.manage',
        'team-members' => 'users.view',
        'agencies' => 'catalogs.manage',
        'clients' => 'clients.view',
        'property-contact-requests' => 'leads.view',
        'calendar' => 'calendar.view',

        'users' => 'super-admin',
        'currencies' => 'settings.manage',
        'color-themes' => 'settings.manage',
        'frontend-colors' => 'settings.manage',
        'rbac' => 'rbac.manage',
        'easybroker' => 'integrations.view',
        'easybroker.mls-export' => 'integrations.sync',
        'mls' => 'integrations.view',
        'mls-agents' => 'catalogs.manage',
        'mls-offices' => 'catalogs.manage',

        'corporate-email.configuration' => 'super-admin',
        'corporate-email.inbox' => 'dashboard.view',
        'corporate-email.outbox' => 'dashboard.view',
        'corporate-email.compose' => 'dashboard.view',

        'cms.pages' => 'cms.view',
        'cms.posts' => 'cms.view',
        'cms.menus' => 'cms.view',
        'cms.settings' => 'cms.view',

        'preferences' => ['settings.view', 'settings.manage'],
        'notifications' => 'dashboard.view',
    ];

    private const GROUP_ITEMS = [
        1 => ['dashboard', 'properties', 'zones', 'team-members', 'agencies'],
        6 => ['clients', 'property-contact-requests', 'calendar'],
        2 => ['users', 'currencies', 'color-themes', 'frontend-colors', 'rbac', 'easybroker', 'easybroker.mls-export', 'mls', 'mls-agents', 'mls-offices'],
        5 => ['corporate-email.configuration', 'corporate-email.inbox', 'corporate-email.outbox', 'corporate-email.compose'],
        4 => ['cms.pages', 'cms.posts', 'cms.menus', 'cms.settings'],
        3 => ['preferences', 'notifications'],
    ];

    private const ROUTE_ITEMS = [
        'dashboard' => 'dashboard',
        'properties' => 'properties',
        'zones' => 'zones',
        'team-members' => 'team-members',
        'agencies' => 'agencies',
        'clients' => 'clients',
        'clients.show' => 'clients',
        'property-contact-requests' => 'property-contact-requests',
        'calendar' => 'calendar',

        'users' => 'users',
        'currencies' => 'currencies',
        'color-themes' => 'color-themes',
        'frontend-colors' => 'frontend-colors',
        'rbac' => 'rbac',
        'easybroker' => 'easybroker',
        'easybroker.mls-export' => 'easybroker.mls-export',
        'mls' => 'mls',
        'mls-agents' => 'mls-agents',
        'mls-offices' => 'mls-offices',

        'corporate-email.configuration' => 'corporate-email.configuration',
        'corporate-email.inbox' => 'corporate-email.inbox',
        'corporate-email.outbox' => 'corporate-email.outbox',
        'corporate-email.compose' => 'corporate-email.compose',

        'cms.pages' => 'cms.pages',
        'cms.posts' => 'cms.posts',
        'cms.menus' => 'cms.menus',
        'cms.settings' => 'cms.settings',
    ];

    public static function canAccessItem(?Authenticatable $user, string $item): bool
    {
        if (! array_key_exists($item, self::ITEM_PERMISSIONS)) {
            return false;
        }

        $permission = self::ITEM_PERMISSIONS[$item];

        if ($permission === 'super-admin') {
            return Rbac::isSuperAdmin($user);
        }

        return Rbac::canAny($user, $permission);
    }

    public static function canAccessRoute(?Authenticatable $user, string $routeName): bool
    {
        $item = self::ROUTE_ITEMS[$routeName] ?? null;

        return $item !== null && self::canAccessItem($user, $item);
    }

    public static function groupVisible(?Authenticatable $user, int $groupId): bool
    {
        foreach (self::GROUP_ITEMS[$groupId] ?? [] as $item) {
            if (self::canAccessItem($user, $item)) {
                return true;
            }
        }

        return false;
    }

    public static function firstAccessibleRoute(?Authenticatable $user): ?string
    {
        foreach (array_keys(self::ROUTE_ITEMS) as $routeName) {
            if (self::canAccessRoute($user, $routeName)) {
                return $routeName;
            }
        }

        return null;
    }
}
