<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user('api')->notifications()->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Notificaciones', 'NOTIFICATIONS_LIST', $query->paginate($perPage));
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user('api')->notifications()->whereKey($notificationId)->first();

        if (!$notification) {
            return $this->apiNotFound('Notificacion no encontrada', 'NOTIFICATION_NOT_FOUND');
        }

        $notification->markAsRead();

        return $this->apiSuccess('Notificacion marcada como leida', 'NOTIFICATION_READ', $notification);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user('api')->unreadNotifications->markAsRead();

        return $this->apiSuccess('Notificaciones marcadas como leidas', 'NOTIFICATIONS_READ_ALL', null);
    }
}
