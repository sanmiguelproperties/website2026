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
            ->line('Tipo: ' . ($this->lead->contact_type_label ?: $this->lead->lead_type_label))
            ->line('Origen: ' . $this->lead->source_label)
            ->line('Propiedad/contexto: ' . ($this->lead->property_form_name ?: $this->lead->property_context_label))
            ->action('Abrir panel', url('/admin'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_routed',
            'event' => $this->routing === 'assigned' ? 'lead_assigned' : 'lead_pending_assignment',
            'title' => $this->routing === 'assigned' ? 'Nuevo lead asignado' : 'Lead pendiente de asignar',
            'routing' => $this->routing,
            'message' => $this->message(),
            'action_url' => route('property-contact-requests', ['search' => $this->lead->id]),
            'action_label' => 'Ver lead',
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'lead_email' => $this->lead->email,
            'contact_type' => $this->lead->contact_type,
            'lead_type' => $this->lead->lead_type,
            'source' => $this->lead->source,
            'property_id' => $this->lead->property_id,
            'property_public_id' => $this->lead->property_public_id,
            'owner_id' => $this->lead->owner_id,
            'assignment_status' => $this->lead->assignment_status,
        ];
    }

    private function message(): string
    {
        if ($this->routing === 'assigned') {
            return 'Entro un lead publico y fue asignado al usuario responsable.';
        }

        return 'Entro un lead publico y requiere asignacion manual.';
    }
}
