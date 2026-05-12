<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class Rbac
{
    public const SUPER_ADMIN = 'super-admin';

    public static function isSuperAdmin(?Authenticatable $user): bool
    {
        return $user !== null
            && method_exists($user, 'hasRole')
            && $user->hasRole(self::SUPER_ADMIN);
    }

    public static function canViewAll(?Authenticatable $user, string $basePermission): bool
    {
        return self::canAny($user, $basePermission . '.view.all');
    }

    public static function canAny(?Authenticatable $user, array|string|null $permissions): bool
    {
        if (!$user) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        $required = self::normalizePermissions($permissions);

        if ($required === []) {
            return method_exists($user, 'can') && $user->can('dashboard.view');
        }

        return method_exists($user, 'canAny') && $user->canAny($required);
    }

    public static function canAll(?Authenticatable $user, array|string|null $permissions): bool
    {
        if (!$user) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        $required = self::normalizePermissions($permissions);

        foreach ($required as $permission) {
            if (!method_exists($user, 'can') || !$user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    public static function normalizePermissions(array|string|null $permissions): array
    {
        if ($permissions === null) {
            return [];
        }

        $items = is_array($permissions) ? $permissions : [$permissions];
        $normalized = [];

        foreach ($items as $item) {
            foreach (preg_split('/[|,]/', (string) $item) ?: [] as $permission) {
                $permission = trim($permission);
                if ($permission !== '') {
                    $normalized[] = $permission;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    public static function scopeOwned(Builder $query, ?Authenticatable $user, string $column = 'agent_user_id'): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $user->getAuthIdentifier());
    }
}
