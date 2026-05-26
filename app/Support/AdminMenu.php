<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class AdminMenu
{
    private const ITEM_PERMISSIONS = [
        'dashboard' => 'menu.dashboard.view',
        'properties' => 'menu.properties.view',
        'zones' => 'menu.zones.view',
        'team-members' => 'menu.team-members.view',
        'agencies' => 'menu.agencies.view',
        'clients' => 'menu.clients.view',
        'property-contact-requests' => 'menu.property-contact-requests.view',
        'calendar' => 'menu.calendar.view',

        'users' => 'menu.users.view',
        'currencies' => 'menu.currencies.view',
        'color-themes' => 'menu.color-themes.view',
        'frontend-colors' => 'menu.frontend-colors.view',
        'rbac' => 'menu.rbac.view',
        'easybroker' => 'menu.easybroker.view',
        'easybroker.mls-export' => 'menu.easybroker.mls-export.view',
        'mls' => 'menu.mls.view',
        'mls-agents' => 'menu.mls-agents.view',
        'mls-offices' => 'menu.mls-offices.view',

        'corporate-email.configuration' => 'menu.corporate-email.configuration.view',
        'corporate-email.inbox' => 'menu.corporate-email.inbox.view',
        'corporate-email.outbox' => 'menu.corporate-email.outbox.view',
        'corporate-email.compose' => 'menu.corporate-email.compose.view',

        'cms.pages' => 'menu.cms.pages.view',
        'cms.posts' => 'menu.cms.posts.view',
        'cms.menus' => 'menu.cms.menus.view',
        'cms.settings' => 'menu.cms.settings.view',
        'tutorials' => 'menu.tutorials.view',
        'tutorial-videos' => 'menu.tutorial-videos.view',

        'notifications' => 'menu.notifications.view',
    ];

    private const GROUP_ITEMS = [
        1 => ['dashboard', 'properties', 'zones', 'team-members', 'agencies'],
        6 => ['clients', 'property-contact-requests', 'calendar'],
        2 => ['users', 'rbac'],
        7 => ['easybroker.mls-export', 'mls', 'mls-agents', 'mls-offices'],
        5 => ['corporate-email.configuration', 'corporate-email.inbox', 'corporate-email.outbox', 'corporate-email.compose'],
        4 => ['cms.pages', 'cms.posts', 'cms.menus', 'cms.settings'],
        8 => ['tutorials', 'tutorial-videos'],
        3 => ['currencies', 'color-themes', 'frontend-colors', 'easybroker', 'notifications'],
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
        'tutorials' => 'tutorials',
        'tutorial-videos' => 'tutorial-videos',

        'notifications' => 'notifications',
    ];

    public static function canAccessItem(?Authenticatable $user, string $item): bool
    {
        if (! array_key_exists($item, self::ITEM_PERMISSIONS)) {
            return false;
        }

        $permission = self::ITEM_PERMISSIONS[$item];

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
