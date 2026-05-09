<?php

namespace App\Http\Controllers;

use App\Models\ClientVisit;
use App\Models\Client;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class VisitCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->monthFromRequest($request);
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $visitsQuery = ClientVisit::query()
            ->with(['client.owner', 'client.contactRequest.owner', 'client.property', 'property', 'assignedUser', 'creator'])
            ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
            ->orderBy('scheduled_at');

        $this->scopeVisibleVisits($visitsQuery, $request->user());

        $visits = $visitsQuery->get();
        $selectedVisit = $visits->firstWhere('id', (int) $request->query('visit'));

        $assignableUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email']);
        $canCreateVisit = $this->canCreateVisits($request->user());
        $visitClientOptions = $canCreateVisit
            ? $this->editableClientsQuery($request->user())
                ->with('contactRequest')
                ->orderBy('name')
                ->limit(200)
                ->get(['id', 'contact_request_id', 'property_id', 'owner_id', 'name', 'email', 'phone'])
            : collect();

        return view('calendar.visits', [
            'month' => $month,
            'previousMonth' => $month->copy()->subMonthNoOverflow(),
            'nextMonth' => $month->copy()->addMonthNoOverflow(),
            'today' => now(),
            'visits' => $visits,
            'eventsByDate' => $visits->groupBy(fn (ClientVisit $visit): string => $visit->scheduled_at->toDateString()),
            'calendarDays' => $this->calendarDays($startOfMonth),
            'selectedVisit' => $selectedVisit,
            'assignableUsers' => $assignableUsers,
            'canCreateVisit' => $canCreateVisit,
            'visitClientOptions' => $visitClientOptions,
            'canEditSelectedVisit' => $selectedVisit ? $this->canEditVisit($request->user(), $selectedVisit) : false,
            'editableVisitIds' => $visits
                ->filter(fn (ClientVisit $visit): bool => $this->canEditVisit($request->user(), $visit))
                ->pluck('id')
                ->all(),
            'visitStatusOptions' => $this->visitStatusOptions(),
            'stats' => [
                'total' => $visits->count(),
                'scheduled' => $visits->where('status', ClientVisit::STATUS_SCHEDULED)->count(),
                'completed' => $visits->where('status', ClientVisit::STATUS_COMPLETED)->count(),
                'cancelled' => $visits->where('status', ClientVisit::STATUS_CANCELLED)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$this->canCreateVisits($request->user())) {
            abort(403, 'No tienes permisos para agendar visitas.');
        }

        $validator = $this->visitValidator($request);
        $validator->addRules([
            'client_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($value === null || $value === '' || !is_numeric($value)) {
                        return;
                    }

                    $client = Client::query()->find((int) $value);
                    if (!$client || !$this->canEditClient($request->user(), $client)) {
                        $fail('Selecciona un cliente valido para tu usuario.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('calendar', ['action' => 'create'])
                ->withErrors($validator)
                ->withInput()
                ->with('calendar_visit_form', true);
        }

        $data = $validator->validated();
        $client = Client::query()->findOrFail((int) $data['client_id']);
        $visitData = $this->normalizeVisitData($data, $request->user()?->getAuthIdentifier());

        $visit = $client->visits()->create($visitData);
        $scheduledAt = $visitData['scheduled_at'];
        $visit->load(['client.owner', 'client.contactRequest.owner', 'property', 'assignedUser']);

        app(\App\Services\CrmNotificationService::class)->visitScheduled($visit, $request->user());

        return redirect()
            ->route('calendar', [
                'month' => $scheduledAt->format('Y-m'),
                'visit' => $visit->id,
            ])
            ->with('status', 'Visita agendada correctamente.');
    }

    public function update(Request $request, ClientVisit $visit): RedirectResponse
    {
        $visit->loadMissing(['client.owner', 'client.contactRequest.owner']);

        if (!$this->canEditVisit($request->user(), $visit)) {
            abort(403, 'No tienes permisos para editar esta visita.');
        }

        $validator = $this->visitValidator($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_visit_id', $visit->id);
        }

        $previousScheduledAt = $visit->scheduled_at?->copy();
        $previousStatus = $visit->status;
        $previousAssignedUserId = $visit->assigned_user_id;
        $data = $this->normalizeVisitData($validator->validated(), $visit->created_by, $visit->completed_at);
        $newScheduledAt = $data['scheduled_at']->copy();

        DB::transaction(function () use ($visit, $request, $previousScheduledAt, $newScheduledAt, $data): void {
            $visit->update($data);

            if (!$previousScheduledAt || !$previousScheduledAt->equalTo($newScheduledAt)) {
                $this->recordRescheduleComment($visit, $request->user(), $previousScheduledAt, $newScheduledAt);
            }
        });

        $notificationChanges = $this->visitNotificationChanges(
            $previousScheduledAt,
            $newScheduledAt,
            $previousStatus,
            $visit->status,
            $previousAssignedUserId,
            $visit->assigned_user_id
        );

        $visit->load(['client.owner', 'client.contactRequest.owner', 'property', 'assignedUser']);
        app(\App\Services\CrmNotificationService::class)->visitUpdated($visit, $notificationChanges, $request->user());

        return redirect()
            ->route('calendar', [
                'month' => $newScheduledAt->format('Y-m'),
                'visit' => $visit->id,
            ])
            ->with('status', 'Visita actualizada correctamente.');
    }

    private function monthFromRequest(Request $request): Carbon
    {
        $month = trim((string) $request->query('month', ''));

        if ($month === '') {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function calendarDays(Carbon $startOfMonth): array
    {
        $cursor = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $end = $startOfMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = [];

        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return $days;
    }

    private function scopeVisibleVisits($query, $user): void
    {
        if (Rbac::canAny($user, 'calendar.view.all')) {
            return;
        }

        if (Rbac::canAny($user, 'calendar.view.own')) {
            $query->where(function ($visitQuery) use ($user) {
                $visitQuery
                    ->where('assigned_user_id', $user->getAuthIdentifier())
                    ->orWhereHas('client', function ($clientQuery) use ($user) {
                        $clientQuery
                            ->where('owner_id', $user->getAuthIdentifier())
                            ->orWhereHas('contactRequest', function ($leadQuery) use ($user) {
                                $leadQuery->where('owner_id', $user->getAuthIdentifier());
                            });
                    });
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function editableClientsQuery($user)
    {
        $query = Client::query()->active();

        if (Rbac::canAny($user, 'clients.edit')) {
            return $query;
        }

        if (Rbac::canAny($user, 'clients.edit.own')) {
            return $query->where(function ($clientQuery) use ($user) {
                $clientQuery
                    ->where('owner_id', $user?->getAuthIdentifier())
                    ->orWhereHas('contactRequest', function ($leadQuery) use ($user) {
                        $leadQuery->where('owner_id', $user?->getAuthIdentifier());
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }

    private function canCreateVisits($user): bool
    {
        return Rbac::canAny($user, 'clients.edit|clients.edit.own');
    }

    private function canEditClient($user, Client $client): bool
    {
        if (Rbac::canAny($user, 'clients.edit')) {
            return true;
        }

        return Rbac::canAny($user, 'clients.edit.own')
            && $this->isClientOwnedBy($client, $user);
    }

    private function isClientOwnedBy(Client $client, $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($client->owner_id !== null && (int) $client->owner_id === (int) $user->getAuthIdentifier()) {
            return true;
        }

        $client->loadMissing('contactRequest');

        return $client->contactRequest !== null
            && $client->contactRequest->owner_id !== null
            && (int) $client->contactRequest->owner_id === (int) $user->getAuthIdentifier();
    }

    private function canEditVisit($user, ClientVisit $visit): bool
    {
        if (Rbac::isSuperAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $visit->loadMissing(['client.contactRequest']);

        if ($visit->client?->owner_id !== null && (int) $visit->client->owner_id === (int) $user->getAuthIdentifier()) {
            return true;
        }

        return $visit->client?->contactRequest !== null
            && $visit->client->contactRequest->owner_id !== null
            && (int) $visit->client->contactRequest->owner_id === (int) $user->getAuthIdentifier();
    }

    private function visitValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'reason' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->visitStatusOptions()))],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'assigned_user_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '' || !is_numeric($value)) {
                        return;
                    }

                    $userExists = User::query()
                        ->where('is_active', true)
                        ->whereKey((int) $value)
                        ->exists();

                    if (!$userExists) {
                        $fail('El usuario asignado no esta activo o no existe.');
                    }
                },
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'outcome' => ['nullable', 'string', 'max:10000'],
        ]);
    }

    private function normalizeVisitData(array $data, $createdBy, $existingCompletedAt = null): array
    {
        $completedAt = $data['status'] === ClientVisit::STATUS_COMPLETED ? ($existingCompletedAt ?: now()) : null;

        return [
            'property_id' => $data['property_id'] ?? null,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'created_by' => $createdBy,
            'scheduled_at' => Carbon::parse($data['scheduled_date'] . ' ' . $data['scheduled_time']),
            'duration_minutes' => (int) $data['duration_minutes'],
            'reason' => $data['reason'],
            'status' => $data['status'],
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
            'outcome' => $data['outcome'] ?? null,
            'completed_at' => $completedAt,
        ];
    }

    private function recordRescheduleComment(ClientVisit $visit, $user, ?Carbon $previousScheduledAt, Carbon $newScheduledAt): void
    {
        $visit->loadMissing('client');

        if (!$visit->client) {
            return;
        }

        $previousDate = $previousScheduledAt?->format('d/m/Y H:i') ?? 'sin fecha previa';
        $newDate = $newScheduledAt->format('d/m/Y H:i');
        $userName = $user?->name ?: 'Sistema';

        $visit->client->comments()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'body' => "Se reprogramo la visita \"{$visit->reason}\". Fecha anterior: {$previousDate}. Nueva fecha: {$newDate}. Usuario: {$userName}.",
        ]);
    }

    private function visitStatusOptions(): array
    {
        return [
            ClientVisit::STATUS_SCHEDULED => 'Pautada',
            ClientVisit::STATUS_COMPLETED => 'Realizada',
            ClientVisit::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    private function visitNotificationChanges(
        ?Carbon $previousScheduledAt,
        Carbon $newScheduledAt,
        ?string $previousStatus,
        ?string $newStatus,
        $previousAssignedUserId,
        $newAssignedUserId
    ): array {
        $changes = [];

        if (!$previousScheduledAt || !$previousScheduledAt->equalTo($newScheduledAt)) {
            $changes['scheduled_at'] = [
                'from' => $previousScheduledAt?->toDateTimeString(),
                'to' => $newScheduledAt->toDateTimeString(),
            ];
        }

        if ($previousStatus !== $newStatus) {
            $changes['status'] = [
                'from' => $previousStatus,
                'to' => $newStatus,
            ];
        }

        if ((int) $previousAssignedUserId !== (int) $newAssignedUserId) {
            $changes['assigned_user_id'] = [
                'from' => $previousAssignedUserId,
                'to' => $newAssignedUserId,
            ];
        }

        return $changes;
    }
}
