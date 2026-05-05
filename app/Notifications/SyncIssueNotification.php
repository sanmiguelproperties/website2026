<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyncIssueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $integration,
        private readonly string $message,
        private readonly array $summary = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Error de sincronizacion {$this->integration}")
            ->line($this->message)
            ->line('Revisa el log de sincronizacion en el panel.')
            ->action('Abrir panel', url('/admin'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sync_issue',
            'integration' => $this->integration,
            'message' => $this->message,
            'summary' => $this->summary,
        ];
    }
}
