<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\ClientVisit;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = Client::query()
            ->with(['property', 'owner', 'contactRequest.owner'])
            ->latest();

        $this->scopeVisibleClients($baseQuery, $request->user());

        $statsQuery = clone $baseQuery;
        $query = clone $baseQuery;

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'source' => trim((string) $request->query('source', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('easybroker_public_id', 'like', "%{$search}%")
                            ->orWhere('mls_public_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contactRequest.owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['source'] !== '') {
            $query->where('source', $filters['source']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $clients = $query
            ->withCount(['comments', 'visits'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', Client::STATUS_ACTIVE)->count(),
            'from_property_forms' => (clone $statsQuery)->where('source', Client::SOURCE_PROPERTY_FORM)->count(),
            'this_month' => (clone $statsQuery)->whereDate('created_at', '>=', now()->startOfMonth()->toDateString())->count(),
        ];

        return view('clients.manage', [
            'clients' => $clients,
            'filters' => $filters,
            'stats' => $stats,
            'statusOptions' => [
                Client::STATUS_ACTIVE => 'Activo',
                'inactive' => 'Inactivo',
                'archived' => 'Archivado',
            ],
            'sourceOptions' => [
                Client::SOURCE_PROPERTY_FORM => 'Formulario de propiedad',
                'manual' => 'Manual',
            ],
        ]);
    }

    public function show(Request $request, Client $client): View
    {
        if (!$this->canViewClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para ver este cliente.');
        }

        $client->load(['property', 'owner', 'contactRequest.owner']);

        $comments = $client->comments()
            ->with('user')
            ->latest()
            ->get();

        $visits = $client->visits()
            ->with(['property', 'assignedUser', 'creator'])
            ->orderByDesc('scheduled_at')
            ->get();

        $assignableUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email']);

        return view('clients.show', [
            'client' => $client,
            'comments' => $comments,
            'visits' => $visits,
            'assignableUsers' => $assignableUsers,
            'canEditClient' => $this->canEditClient($request->user(), $client),
            'statusOptions' => $this->statusOptions(),
            'sourceOptions' => $this->sourceOptions(),
            'visitStatusOptions' => $this->visitStatusOptions(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para editar este cliente.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statusOptions()))],
            'owner_id' => [
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
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_client', true);
        }

        $data = $validator->validated();

        $client->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'owner_id' => $data['owner_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Cliente actualizado correctamente.');
    }

    public function storeComment(Request $request, Client $client): RedirectResponse
    {
        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para agregar comentarios a este cliente.');
        }

        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('comment_form', true);
        }

        $client->comments()->create([
            'user_id' => $request->user()?->getAuthIdentifier(),
            'body' => $validator->validated()['body'],
        ]);

        return back()->with('status', 'Comentario agregado correctamente.');
    }

    public function updateComment(Request $request, Client $client, ClientComment $comment): RedirectResponse
    {
        $this->abortUnlessCommentBelongsToClient($comment, $client);

        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para editar comentarios de este cliente.');
        }

        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_comment_id', $comment->id);
        }

        $comment->update($validator->validated());

        return back()->with('status', 'Comentario actualizado correctamente.');
    }

    public function destroyComment(Request $request, Client $client, ClientComment $comment): RedirectResponse
    {
        $this->abortUnlessCommentBelongsToClient($comment, $client);

        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para eliminar comentarios de este cliente.');
        }

        $comment->delete();

        return back()->with('status', 'Comentario eliminado correctamente.');
    }

    public function storeVisit(Request $request, Client $client): RedirectResponse
    {
        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para registrar visitas en este cliente.');
        }

        $validator = $this->visitValidator($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('visit_form', true);
        }

        $data = $this->normalizeVisitData($validator->validated(), $request->user()?->getAuthIdentifier());

        $client->visits()->create($data);

        return back()->with('status', 'Visita registrada correctamente.');
    }

    public function updateVisit(Request $request, Client $client, ClientVisit $visit): RedirectResponse
    {
        $this->abortUnlessVisitBelongsToClient($visit, $client);

        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para editar visitas de este cliente.');
        }

        $validator = $this->visitValidator($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_visit_id', $visit->id);
        }

        $previousScheduledAt = $visit->scheduled_at?->copy();
        $data = $this->normalizeVisitData($validator->validated(), $visit->created_by, $visit->completed_at);
        $newScheduledAt = $data['scheduled_at']->copy();

        DB::transaction(function () use ($visit, $request, $previousScheduledAt, $newScheduledAt, $data): void {
            $visit->update($data);

            if (!$previousScheduledAt || !$previousScheduledAt->equalTo($newScheduledAt)) {
                $this->recordRescheduleComment($visit, $request->user(), $previousScheduledAt, $newScheduledAt);
            }
        });

        return back()->with('status', 'Visita actualizada correctamente.');
    }

    public function destroyVisit(Request $request, Client $client, ClientVisit $visit): RedirectResponse
    {
        $this->abortUnlessVisitBelongsToClient($visit, $client);

        if (!$this->canEditClient($request->user(), $client)) {
            abort(403, 'No tienes permisos para eliminar visitas de este cliente.');
        }

        $visit->delete();

        return back()->with('status', 'Visita eliminada correctamente.');
    }

    private function scopeVisibleClients($query, $user): void
    {
        if (Rbac::canAny($user, 'clients.view.all')) {
            return;
        }

        if (Rbac::canAny($user, 'clients.view.own')) {
            $query->where(function ($clientQuery) use ($user) {
                $clientQuery
                    ->where('owner_id', $user->getAuthIdentifier())
                    ->orWhereHas('contactRequest', function ($leadQuery) use ($user) {
                        $leadQuery->where('owner_id', $user->getAuthIdentifier());
                    });
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canViewClient($user, Client $client): bool
    {
        if (Rbac::canAny($user, 'clients.view.all')) {
            return true;
        }

        return Rbac::canAny($user, 'clients.view.own')
            && $this->isClientOwnedBy($client, $user);
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

    private function abortUnlessCommentBelongsToClient(ClientComment $comment, Client $client): void
    {
        abort_unless((int) $comment->client_id === (int) $client->id, 404);
    }

    private function abortUnlessVisitBelongsToClient(ClientVisit $visit, Client $client): void
    {
        abort_unless((int) $visit->client_id === (int) $client->id, 404);
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

    private function statusOptions(): array
    {
        return [
            Client::STATUS_ACTIVE => 'Activo',
            'inactive' => 'Inactivo',
            'archived' => 'Archivado',
        ];
    }

    private function sourceOptions(): array
    {
        return [
            Client::SOURCE_PROPERTY_FORM => 'Formulario de propiedad',
            'manual' => 'Manual',
        ];
    }

    private function visitStatusOptions(): array
    {
        return [
            ClientVisit::STATUS_SCHEDULED => 'Pautada',
            ClientVisit::STATUS_COMPLETED => 'Realizada',
            ClientVisit::STATUS_CANCELLED => 'Cancelada',
        ];
    }
}
