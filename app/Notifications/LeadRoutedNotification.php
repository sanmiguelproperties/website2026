<?php

namespace App\Notifications;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadRoutedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ContactRequest $lead,
        private readonly string $routing
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->routing === 'assigned'
            ? 'Nuevo lead asignado'
            : 'Lead pendiente de asignar';

        return (new MailMessage)
            ->subject($subject)
            ->line($this->message())
            ->line('Lead: ' . ($this->lead->name ?: 'Sin nombre'))
            ->line('Propiedad: ' . ($this->lead->property_public_id ?: 'Sin propiedad'))
            ->action('Abrir panel', url('/admin'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_routed',
            'routing' => $this->routing,
            'message' => $this->message(),
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'lead_email' => $this->lead->email,
            'property_id' => $this->lead->property_id,
            'property_public_id' => $this->lead->property_public_id,
            'owner_id' => $this->lead->owner_id,
            'assignment_status' => $this->lead->assignment_status,
        ];
    }

    private function message(): string
    {
        if ($this->routing === 'assigned') {
            return 'Entro un lead por una propiedad de la agencia y fue asignado al agente propietario.';
        }

        return 'Entro un lead por una propiedad externa y requiere asignacion manual.';
    }
}
