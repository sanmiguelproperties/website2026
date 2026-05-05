<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Models\Property;
use App\Notifications\LeadRoutedNotification;
use App\Support\Rbac;
use App\Support\RbacNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContactRequestController extends Controller
{
    /**
     * GET /api/contact-requests
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactRequest::query()->with(['agency', 'property', 'owner']);

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $this->scopeInternalLeads($query, $request->user('api'));

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('property_public_id', 'like', "%{$search}%")
                    ->orWhere('remote_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', (int) $request->input('agency_id'));
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', (int) $request->input('property_id'));
        }

        if ($request->filled('assignment_status')) {
            $query->where('assignment_status', (string) $request->input('assignment_status'));
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'created_at');
        $validOrders = ['id', 'created_at', 'updated_at', 'happened_at', 'status'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'created_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de leads', 'CONTACT_REQUESTS_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/contact-requests
     */
    public function store(Request $request): JsonResponse
    {
        if (!Rbac::canAny($request->user('api'), 'leads.create')) {
            return $this->apiForbidden('No tienes permisos para crear leads', 'LEADS_CREATE_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'agency_id' => 'nullable|exists:agencies,id',
            'property_id' => 'nullable|exists:properties,id',
            'owner_id' => 'nullable|exists:users,id',
            'property_public_id' => 'required|string|max:50',
            'remote_id' => 'required|string|max:100|unique:contact_requests,remote_id',
            'source' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string',
            'happened_at' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'assignment_status' => 'nullable|string|max:50',
            'sent_to_easybroker_at' => 'nullable|date',
            'raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (!empty($data['owner_id']) && !Rbac::canAny($request->user('api'), 'leads.assign')) {
            return $this->apiForbidden('No tienes permisos para asignar leads', 'LEAD_ASSIGN_FORBIDDEN');
        }

        $this->applyLeadAssignmentDefaults($data);

        $lead = ContactRequest::create($data);
        $lead->load(['agency', 'property', 'owner']);
        $this->notifyLeadRouting($lead);

        return $this->apiCreated('Lead creado', 'CONTACT_REQUEST_CREATED', $lead);
    }

    /**
     * GET /api/contact-requests/{contactRequest}
     */
    public function show(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!$this->canViewInternalLead($request->user('api'), $contactRequest)) {
            return $this->apiForbidden('No tienes permisos para ver este lead', 'LEAD_VIEW_FORBIDDEN');
        }

        $contactRequest->load(['agency', 'property', 'owner']);
        return $this->apiSuccess('Lead obtenido', 'CONTACT_REQUEST_SHOWN', $contactRequest);
    }

    /**
     * PATCH /api/contact-requests/{contactRequest}
     */
    public function update(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!$this->canEditInternalLead($request->user('api'), $contactRequest)) {
            return $this->apiForbidden('No tienes permisos para editar este lead', 'LEAD_EDIT_FORBIDDEN');
        }

        if ($request->exists('status') && !Rbac::canAny($request->user('api'), 'leads.status.update')) {
            return $this->apiForbidden('No tienes permisos para cambiar estatus de leads', 'LEAD_STATUS_FORBIDDEN');
        }

        if (($request->exists('property_id') || $request->exists('owner_id')) && !Rbac::canAny($request->user('api'), 'leads.assign')) {
            return $this->apiForbidden('No tienes permisos para reasignar leads', 'LEAD_ASSIGN_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'agency_id' => 'sometimes|nullable|exists:agencies,id',
            'property_id' => 'sometimes|nullable|exists:properties,id',
            'owner_id' => 'sometimes|nullable|exists:users,id',
            'property_public_id' => 'sometimes|required|string|max:50',
            'remote_id' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('contact_requests', 'remote_id')->ignore($contactRequest->id)],
            'source' => 'sometimes|nullable|string|max:100',
            'name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'message' => 'sometimes|required|string',
            'happened_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|string|max:50',
            'assignment_status' => 'sometimes|nullable|string|max:50',
            'sent_to_easybroker_at' => 'sometimes|nullable|date',
            'raw_payload' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('owner_id', $data)) {
            $data['assigned_at'] = $data['owner_id'] ? now() : null;
            $data['assignment_status'] = $data['owner_id'] ? 'assigned' : 'pending_assignment';
        }

        $contactRequest->update($data);
        $contactRequest->load(['agency', 'property', 'owner']);

        return $this->apiSuccess('Lead actualizado', 'CONTACT_REQUEST_UPDATED', $contactRequest);
    }

    /**
     * DELETE /api/contact-requests/{contactRequest}
     */
    public function destroy(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!$this->canDeleteInternalLead($request->user('api'), $contactRequest)) {
            return $this->apiForbidden('No tienes permisos para eliminar leads', 'LEAD_DELETE_FORBIDDEN');
        }

        if ($request->boolean('force')) {
            if (!Rbac::canAny($request->user('api'), 'records.delete.critical')) {
                return $this->apiForbidden('Solo Administrador puede eliminar permanentemente', 'FORCE_DELETE_FORBIDDEN');
            }

            $contactRequest->forceDelete();
            return $this->apiSuccess('Lead eliminado permanentemente', 'CONTACT_REQUEST_FORCE_DELETED', null);
        }

        $contactRequest->delete();
        return $this->apiSuccess('Lead enviado a papelera', 'CONTACT_REQUEST_TRASHED', null);
    }

    public function restore(Request $request, int $contactRequestId): JsonResponse
    {
        $lead = ContactRequest::withTrashed()->find($contactRequestId);

        if (!$lead) {
            return $this->apiNotFound('Lead no encontrado', 'CONTACT_REQUEST_NOT_FOUND');
        }

        if (!$this->canViewInternalLead($request->user('api'), $lead) || !Rbac::canAny($request->user('api'), 'leads.restore')) {
            return $this->apiForbidden('No tienes permisos para restaurar este lead', 'LEAD_RESTORE_FORBIDDEN');
        }

        $lead->restore();

        return $this->apiSuccess('Lead restaurado', 'CONTACT_REQUEST_RESTORED', $lead->fresh(['agency', 'property', 'owner']));
    }

    private function scopeInternalLeads($query, $user): void
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return;
        }

        if (Rbac::canAny($user, 'leads.view.own')) {
            $query->where(function ($leadQuery) use ($user) {
                $leadQuery->where('owner_id', $user->getAuthIdentifier())
                    ->orWhereHas('property', function ($propertyQuery) use ($user) {
                        Rbac::scopeOwned($propertyQuery, $user);
                    });
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canViewInternalLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return true;
        }

        return Rbac::canAny($user, 'leads.view.own')
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function canEditInternalLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.edit')) {
            return true;
        }

        return Rbac::canAny($user, 'leads.edit.own')
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function canDeleteInternalLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.delete')) {
            return true;
        }

        return Rbac::canAny($user, 'leads.delete.own')
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function isLeadOwnedBy(ContactRequest $lead, $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($lead->owner_id !== null && (int) $lead->owner_id === (int) $user->getAuthIdentifier()) {
            return true;
        }

        $lead->loadMissing('property');

        return $lead->property !== null
            && $lead->property->agent_user_id !== null
            && (int) $lead->property->agent_user_id === (int) $user->getAuthIdentifier();
    }

    private function applyLeadAssignmentDefaults(array &$data): void
    {
        $property = $this->resolveLeadProperty($data);

        if ($property) {
            $data['property_id'] = $property->id;
            $data['agency_id'] = $data['agency_id'] ?? $property->agency_id;
        }

        if (
            $property
            && $this->isAgencyProperty($property)
            && $property->agent_user_id
        ) {
            $data['owner_id'] = $data['owner_id'] ?? $property->agent_user_id;
            $data['assignment_status'] = 'assigned';
            $data['assigned_at'] = now();
            return;
        }

        if (!empty($data['owner_id'])) {
            $data['assignment_status'] = 'assigned';
            $data['assigned_at'] = now();
            return;
        }

        $data['owner_id'] = $data['owner_id'] ?? null;
        $data['assignment_status'] = 'pending_assignment';
        $data['status'] = $data['status'] ?? 'pending_assignment';
        $data['assigned_at'] = null;
    }

    private function resolveLeadProperty(array $data): ?Property
    {
        if (!empty($data['property_id'])) {
            return Property::query()
                ->with(['agency', 'mlsOffice'])
                ->find((int) $data['property_id']);
        }

        if (empty($data['property_public_id'])) {
            return null;
        }

        $publicId = (string) $data['property_public_id'];

        return Property::query()
            ->with(['agency', 'mlsOffice'])
            ->where('easybroker_public_id', $publicId)
            ->orWhere('mls_public_id', $publicId)
            ->first();
    }

    private function isAgencyProperty(Property $property): bool
    {
        $property->loadMissing(['agency', 'mlsOffice']);

        if ($property->agent_user_id) {
            return true;
        }

        if ($property->agency && $property->agency->is_primary) {
            return true;
        }

        return $property->mlsOffice && $property->mlsOffice->is_primary;
    }

    private function notifyLeadRouting(ContactRequest $lead): void
    {
        if ($lead->assignment_status === 'assigned') {
            RbacNotifications::notifyUsers(
                $lead->owner ? [$lead->owner] : [],
                new LeadRoutedNotification($lead, 'assigned')
            );

            RbacNotifications::notifyRoles(
                ['manager'],
                new LeadRoutedNotification($lead, 'assigned')
            );

            return;
        }

        RbacNotifications::notifyRoles(
            ['super-admin', 'manager'],
            new LeadRoutedNotification($lead, 'pending_assignment')
        );
    }
}

