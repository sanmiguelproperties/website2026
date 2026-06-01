<?php

namespace App\Models;

use App\Support\RoleName;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    public function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = RoleName::normalize($value);
    }

    protected static function findByParam(array $params = []): ?RoleContract
    {
        $query = static::query();

        if (app(PermissionRegistrar::class)->teams) {
            $teamsKey = app(PermissionRegistrar::class)->teamsKey;

            $query->where(fn ($query) => $query->whereNull($teamsKey)
                ->orWhere($teamsKey, $params[$teamsKey] ?? getPermissionsTeamId())
            );
            unset($params[$teamsKey]);
        }

        if (array_key_exists('name', $params)) {
            $query->whereRaw('LOWER(TRIM(name)) = ?', [RoleName::normalize($params['name'])]);
            unset($params['name']);
        }

        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }
}
