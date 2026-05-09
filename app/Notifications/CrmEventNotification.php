<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CrmEventNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'crm_event',
            'event' => 'crm_event',
            'title' => 'Actividad del CRM',
            'message' => 'Hay una nueva actividad en el sistema.',
            'action_url' => route('dashboard'),
            'action_label' => 'Abrir dashboard',
        ], $this->payload);
    }
}
