<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Support\Rbac;
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
        $query = ContactRequest::query()->with(['agency', 'property']);

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
            'property_public_id' => 'required|string|max:50',
            'remote_id' => 'required|string|max:100|unique:contact_requests,remote_id',
            'source' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string',
            'happened_at' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'sent_to_easybroker_at' => 'nullable|date',
            'raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $lead = ContactRequest::create($validator->validated());
        $lead->load(['agency', 'property']);

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

        $contactRequest->load(['agency', 'property']);
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

        if ($request->exists('property_id') && !Rbac::canAny($request->user('api'), 'leads.assign')) {
            return $this->apiForbidden('No tienes permisos para reasignar leads', 'LEAD_ASSIGN_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'agency_id' => 'sometimes|nullable|exists:agencies,id',
            'property_id' => 'sometimes|nullable|exists:properties,id',
            'property_public_id' => 'sometimes|required|string|max:50',
            'remote_id' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('contact_requests', 'remote_id')->ignore($contactRequest->id)],
            'source' => 'sometimes|nullable|string|max:100',
            'name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'message' => 'sometimes|required|string',
            'happened_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|string|max:50',
            'sent_to_easybroker_at' => 'sometimes|nullable|date',
            'raw_payload' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $contactRequest->update($validator->validated());
        $contactRequest->load(['agency', 'property']);

        return $this->apiSuccess('Lead actualizado', 'CONTACT_REQUEST_UPDATED', $contactRequest);
    }

    /**
     * DELETE /api/contact-requests/{contactRequest}
     */
    public function destroy(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!Rbac::canAny($request->user('api'), 'leads.delete')) {
            return $this->apiForbidden('No tienes permisos para eliminar leads', 'LEAD_DELETE_FORBIDDEN');
        }

        $contactRequest->delete();
        return $this->apiSuccess('Lead eliminado', 'CONTACT_REQUEST_DELETED', null);
    }

    private function scopeInternalLeads($query, $user): void
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return;
        }

        if (Rbac::canAny($user, ['leads.view.own', 'leads.view.team'])) {
            $query->whereHas('property', function ($propertyQuery) use ($user) {
                Rbac::scopeOwned($propertyQuery, $user);
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

        return Rbac::canAny($user, ['leads.view.own', 'leads.view.team'])
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function canEditInternalLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.edit')) {
            return true;
        }

        return Rbac::canAny($user, ['leads.edit.own', 'leads.edit.assigned'])
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function isLeadOwnedBy(ContactRequest $lead, $user): bool
    {
        if (!$user) {
            return false;
        }

        $lead->loadMissing('property');

        return $lead->property !== null
            && $lead->property->agent_user_id !== null
            && (int) $lead->property->agent_user_id === (int) $user->getAuthIdentifier();
    }
}

