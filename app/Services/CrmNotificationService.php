<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\ContactRequest;
use App\Models\User;
use App\Notifications\CrmEventNotification;
use App\Support\RbacNotifications;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CrmNotificationService
{
    public function leadCreated(ContactRequest $lead): void
    {
        $lead->loadMissing(['owner', 'property']);

        $this->notifySuperAdmins(new CrmEventNotification([
            'event' => 'lead_created',
            'title' => 'Nuevo lead recibido',
            'message' => $this->leadMessage($lead, 'Entro un nuevo lead al sistema.'),
            'action_url' => route('property-contact-requests', ['search' => $lead->id]),
            'action_label' => 'Ver lead',
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'owner_id' => $lead->owner_id,
            'property_id' => $lead->property_id,
        ]));
    }

    public function leadAssigned(ContactRequest $lead, ?User $actor = null): void
    {
        $lead->loadMissing(['owner', 'property']);

        if (!$lead->owner) {
            return;
        }

        RbacNotifications::notifyUsers([$lead->owner], new CrmEventNotification([
            'event' => 'lead_assigned',
            'title' => 'Lead asignado',
            'message' => $this->leadMessage($lead, 'Se te asigno un lead para contactar.'),
            'action_url' => route('property-contact-requests', ['search' => $lead->id]),
            'action_label' => 'Ver lead',
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'owner_id' => $lead->owner_id,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]));

        $this->notifySuperAdmins(new CrmEventNotification([
            'event' => 'lead_assigned_admin',
            'title' => 'Lead asignado a usuario',
            'message' => $this->leadMessage($lead, 'El lead fue asignado a ' . ($lead->owner->name ?: 'un usuario') . '.'),
            'action_url' => route('property-contact-requests', ['search' => $lead->id]),
            'action_label' => 'Ver lead',
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'owner_id' => $lead->owner_id,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]));
    }

    public function leadStatusChanged(ContactRequest $lead, ?string $previousStatus, ?string $newStatus, ?User $actor = null): void
    {
        if ($previousStatus === $newStatus) {
            return;
        }

        $lead->loadMissing(['owner', 'property']);
        $message = $this->leadMessage(
            $lead,
            'El estado del lead cambio de ' . $this->leadStatusLabel($previousStatus) . ' a ' . $this->leadStatusLabel($newStatus) . '.'
        );

        $notification = new CrmEventNotification([
            'event' => 'lead_status_changed',
            'title' => 'Estado de lead actualizado',
            'message' => $message,
            'action_url' => route('property-contact-requests', ['search' => $lead->id]),
            'action_label' => 'Ver lead',
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'owner_id' => $lead->owner_id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]);

        $this->notifyUsersAndSuperAdmins($lead->owner ? [$lead->owner] : [], $notification);
    }

    public function leadConverted(ContactRequest $lead, Client $client, ?User $actor = null): void
    {
        $lead->loadMissing(['owner', 'property']);
        $client->loadMissing(['owner', 'property']);

        $notification = new CrmEventNotification([
            'event' => 'lead_converted',
            'title' => 'Lead convertido en cliente',
            'message' => $this->leadMessage($lead, 'El lead paso a ser cliente.'),
            'action_url' => route('clients.show', $client),
            'action_label' => 'Ver cliente',
            'lead_id' => $lead->id,
            'client_id' => $client->id,
            'lead_name' => $lead->name,
            'client_name' => $client->name,
            'owner_id' => $client->owner_id ?: $lead->owner_id,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]);

        $this->notifyUsersAndSuperAdmins($this->clientStakeholders($client, $lead), $notification);
    }

    public function visitScheduled(ClientVisit $visit, ?User $actor = null): void
    {
        $visit->loadMissing(['client.owner', 'client.contactRequest.owner', 'property', 'assignedUser']);

        $notification = new CrmEventNotification([
            'event' => 'visit_scheduled',
            'title' => 'Nueva visita agendada',
            'message' => $this->visitMessage($visit, 'Se agendo una visita con un cliente.'),
            'action_url' => $this->visitUrl($visit),
            'action_label' => 'Ver visita',
            'visit_id' => $visit->id,
            'client_id' => $visit->client_id,
            'client_name' => $visit->client?->name,
            'property_id' => $visit->property_id,
            'assigned_user_id' => $visit->assigned_user_id,
            'scheduled_at' => $visit->scheduled_at?->toDateTimeString(),
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]);

        $this->notifyUsersAndSuperAdmins($this->visitStakeholders($visit), $notification);
    }

    public function visitUpdated(ClientVisit $visit, array $changes, ?User $actor = null): void
    {
        if ($changes === []) {
            return;
        }

        $visit->loadMissing(['client.owner', 'client.contactRequest.owner', 'property', 'assignedUser']);

        $notification = new CrmEventNotification([
            'event' => 'visit_updated',
            'title' => 'Visita actualizada',
            'message' => $this->visitMessage($visit, 'Se actualizo una visita del cliente.'),
            'action_url' => $this->visitUrl($visit),
            'action_label' => 'Ver visita',
            'visit_id' => $visit->id,
            'client_id' => $visit->client_id,
            'client_name' => $visit->client?->name,
            'property_id' => $visit->property_id,
            'assigned_user_id' => $visit->assigned_user_id,
            'scheduled_at' => $visit->scheduled_at?->toDateTimeString(),
            'changes' => $changes,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ]);

        $this->notifyUsersAndSuperAdmins($this->visitStakeholders($visit), $notification);
    }

    private function notifySuperAdmins(CrmEventNotification $notification): void
    {
        RbacNotifications::notifyUsers($this->superAdminUsers(), $notification);
    }

    private function notifyUsersAndSuperAdmins(iterable $users, CrmEventNotification $notification): void
    {
        RbacNotifications::notifyUsers(
            collect($users)->filter()->merge($this->superAdminUsers()),
            $notification
        );
    }

    private function superAdminUsers(): Collection
    {
        $query = User::query()->permission(['leads.view.all', 'clients.view.all', 'calendar.view.all']);

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    private function clientStakeholders(Client $client, ?ContactRequest $lead = null): Collection
    {
        $lead?->loadMissing('owner');
        $client->loadMissing(['owner', 'contactRequest.owner']);

        return collect([
            $client->owner,
            $client->contactRequest?->owner,
            $lead?->owner,
        ])->filter();
    }

    private function visitStakeholders(ClientVisit $visit): Collection
    {
        $visit->loadMissing(['client.owner', 'client.contactRequest.owner', 'assignedUser']);

        return collect([
            $visit->assignedUser,
            $visit->client?->owner,
            $visit->client?->contactRequest?->owner,
        ])->filter();
    }

    private function leadMessage(ContactRequest $lead, string $prefix): string
    {
        $name = $lead->name ?: 'Lead sin nombre';
        $property = $lead->property_form_name ?: $lead->property_context_label;

        return trim($prefix . ' ' . $name . ($property ? ' - ' . $property : ''));
    }

    private function visitMessage(ClientVisit $visit, string $prefix): string
    {
        $client = $visit->client?->name ?: 'Cliente sin nombre';
        $date = $visit->scheduled_at instanceof Carbon
            ? $visit->scheduled_at->format('d/m/Y H:i')
            : 'sin fecha';

        return trim($prefix . ' ' . $client . ' - ' . $date . '.');
    }

    private function visitUrl(ClientVisit $visit): string
    {
        return route('calendar', [
            'month' => $visit->scheduled_at?->format('Y-m'),
            'visit' => $visit->id,
        ]);
    }

    private function leadStatusLabel(?string $status): string
    {
        return match ($status) {
            'new' => 'Nuevo',
            'pending_assignment' => 'Pendiente',
            'contacted' => 'Contactado',
            'qualified' => 'Calificado',
            'converted' => 'Convertido',
            'closed' => 'Cerrado',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : 'Sin estado',
        };
    }
}
