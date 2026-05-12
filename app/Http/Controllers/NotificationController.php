<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $query = $user->notifications()->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(50, $perPage));

        /** @var LengthAwarePaginator $notifications */
        $notifications = $query->paginate($perPage);
        $notifications->getCollection()->transform(fn (DatabaseNotification $notification): array => $this->formatNotification($notification));

        return $this->apiSuccess('Notificaciones', 'NOTIFICATIONS_LIST', $notifications, 200, [
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $canViewAll = Rbac::canAny($user, 'notifications.view.all');

        $query = $this->scopedNotificationsQuery($user, $canViewAll)
            ->with('notifiable')
            ->latest();

        $status = (string) $request->input('status', '');
        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($request->filled('event')) {
            $event = trim((string) $request->input('event'));
            $events = $event === 'lead_assigned'
                ? ['lead_assigned', 'lead_assigned_admin']
                : [$event];

            $query->where(function ($eventQuery) use ($events) {
                foreach ($events as $eventName) {
                    $eventQuery->orWhere('data', 'like', '%"event":"' . $eventName . '"%');
                }
            });
        }

        if ($canViewAll && $request->filled('recipient_id')) {
            $query->where('notifiable_type', User::class)
                ->where('notifiable_id', (int) $request->input('recipient_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $matchingUserIds = User::query()
                ->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->pluck('id');

            $query->where(function ($searchQuery) use ($like, $matchingUserIds) {
                $searchQuery->where('type', 'like', $like)
                    ->orWhere('data', 'like', $like);

                if ($matchingUserIds->isNotEmpty()) {
                    $searchQuery->orWhere(function ($recipientQuery) use ($matchingUserIds) {
                        $recipientQuery->where('notifiable_type', User::class)
                            ->whereIn('notifiable_id', $matchingUserIds);
                    });
                }
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        /** @var LengthAwarePaginator $notifications */
        $notifications = $query->paginate($perPage);
        $notifications->getCollection()->transform(fn (DatabaseNotification $notification): array => $this->formatNotification($notification, true));

        return $this->apiSuccess('Notificaciones del sistema', 'ADMIN_NOTIFICATIONS_LIST', $notifications, 200, [
            'scope' => $canViewAll ? 'global' : 'own',
            'stats' => $this->adminStats($user, $canViewAll),
        ]);
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $user = $request->user('api');
        $notification = $user->notifications()->whereKey($notificationId)->first();

        if (!$notification) {
            return $this->apiNotFound('Notificacion no encontrada', 'NOTIFICATION_NOT_FOUND');
        }

        $notification->markAsRead();

        return $this->apiSuccess('Notificacion marcada como leida', 'NOTIFICATION_READ', $this->formatNotification($notification->fresh()), 200, [
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user('api')->unreadNotifications()->update(['read_at' => now()]);

        return $this->apiSuccess('Notificaciones marcadas como leidas', 'NOTIFICATIONS_READ_ALL', null, 200, [
            'unread_count' => 0,
        ]);
    }

    private function formatNotification(DatabaseNotification $notification, bool $includeRecipient = false): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        $formatted = [
            'id' => $notification->id,
            'type' => $data['event'] ?? $data['type'] ?? class_basename($notification->type),
            'title' => $data['title'] ?? $this->fallbackTitle($data),
            'message' => $data['message'] ?? 'Hay una nueva actividad en el sistema.',
            'action_url' => $data['action_url'] ?? $this->fallbackActionUrl($data),
            'action_label' => $data['action_label'] ?? 'Abrir',
            'read_at' => $notification->read_at?->toDateTimeString(),
            'created_at' => $notification->created_at?->toDateTimeString(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
            'data' => $data,
        ];

        if ($includeRecipient) {
            $formatted['recipient'] = $this->formatRecipient($notification);
        }

        return $formatted;
    }

    private function adminStats(User $user, bool $canViewAll): array
    {
        $baseQuery = $this->scopedNotificationsQuery($user, $canViewAll);

        return [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->whereNull('read_at')->count(),
            'read' => (clone $baseQuery)->whereNotNull('read_at')->count(),
            'today' => (clone $baseQuery)->where('created_at', '>=', now()->startOfDay())->count(),
        ];
    }

    private function scopedNotificationsQuery(User $user, bool $canViewAll)
    {
        $query = DatabaseNotification::query();

        if (!$canViewAll) {
            $query->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->getAuthIdentifier());
        }

        return $query;
    }

    private function formatRecipient(DatabaseNotification $notification): ?array
    {
        $recipient = $notification->notifiable;

        if (!$recipient instanceof User) {
            return null;
        }

        return [
            'id' => $recipient->id,
            'name' => $recipient->name,
            'email' => $recipient->email,
        ];
    }

    private function fallbackTitle(array $data): string
    {
        return match ($data['type'] ?? null) {
            'lead_routed' => ($data['routing'] ?? null) === 'assigned' ? 'Nuevo lead asignado' : 'Lead pendiente de asignar',
            'sync_issue' => 'Incidencia de sincronizacion',
            default => 'Notificacion',
        };
    }

    private function fallbackActionUrl(array $data): string
    {
        if (!empty($data['client_id'])) {
            return route('clients.show', ['client' => $data['client_id']]);
        }

        if (!empty($data['lead_id'])) {
            return route('property-contact-requests', ['search' => $data['lead_id']]);
        }

        if (!empty($data['visit_id'])) {
            return route('calendar', ['visit' => $data['visit_id']]);
        }

        return route('dashboard');
    }
}
