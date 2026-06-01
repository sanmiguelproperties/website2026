<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RbacNotifications
{
    public static function notifyRoles(array $roles, Notification $notification): void
    {
        $query = User::query()->withRoleNames($roles);

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        }

        self::notifyUsers($query->get(), $notification);
    }

    public static function notifyPermissions(array|string $permissions, Notification $notification): void
    {
        $query = User::query()->permission($permissions);

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        }

        self::notifyUsers($query->get(), $notification);
    }

    public static function notifyUsers(iterable $users, Notification $notification): void
    {
        collect($users)
            ->filter()
            ->unique(fn ($user) => $user->getAuthIdentifier())
            ->each(function ($user) use ($notification): void {
                try {
                    $user->notify($notification);
                } catch (\Throwable $e) {
                    Log::warning('RBAC notification delivery failed', [
                        'user_id' => $user->getAuthIdentifier(),
                        'notification' => get_class($notification),
                        'error' => $e->getMessage(),
                    ]);
                }
            });
    }
}
