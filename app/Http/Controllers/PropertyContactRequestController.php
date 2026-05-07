<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\PublicLeadCaptureService;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyContactRequestController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = ContactRequest::query()
            ->fromPublicForms()
            ->with(['property', 'agency', 'owner', 'convertedClient'])
            ->latest();

        $this->scopeVisibleLeads($baseQuery, $request->user());

        $statsQuery = clone $baseQuery;
        $query = clone $baseQuery;

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'assignment_status' => trim((string) $request->query('assignment_status', '')),
            'lead_type' => trim((string) $request->query('lead_type', '')),
            'contact_type' => trim((string) $request->query('contact_type', '')),
            'source' => trim((string) $request->query('source', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('property_public_id', 'like', "%{$search}%")
                    ->orWhere('property_address', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('lead_type', 'like', "%{$search}%")
                    ->orWhere('contact_type', 'like', "%{$search}%")
                    ->orWhere('source_url', 'like', "%{$search}%")
                    ->orWhere('referrer_url', 'like', "%{$search}%")
                    ->orWhere('raw_payload', 'like', "%{$search}%")
                    ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('easybroker_public_id', 'like', "%{$search}%")
                            ->orWhere('mls_public_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['assignment_status'] !== '') {
            $query->where('assignment_status', $filters['assignment_status']);
        }

        if ($filters['lead_type'] !== '') {
            $query->where('lead_type', $filters['lead_type']);
        }

        if ($filters['contact_type'] !== '') {
            $query->where('contact_type', $filters['contact_type']);
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

        $leads = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', now()->toDateString())->count(),
            'assigned' => (clone $statsQuery)->where('assignment_status', 'assigned')->count(),
            'pending_assignment' => (clone $statsQuery)->where('assignment_status', 'pending_assignment')->count(),
            'buyers' => (clone $statsQuery)->where('contact_type', ContactRequest::CONTACT_TYPE_BUYER)->count(),
            'sellers' => (clone $statsQuery)->where('contact_type', ContactRequest::CONTACT_TYPE_SELLER)->count(),
            'buyer_sellers' => (clone $statsQuery)->where('contact_type', ContactRequest::CONTACT_TYPE_BUYER_SELLER)->count(),
        ];

        $assignableUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email']);

        return view('contact-requests.property-index', [
            'leads' => $leads,
            'filters' => $filters,
            'stats' => $stats,
            'assignableUsers' => $assignableUsers,
            'canManageLeads' => Rbac::canAny($request->user(), ['leads.edit', 'leads.edit.own']),
            'canConvertLeads' => Rbac::canAny($request->user(), 'clients.create'),
            'leadTypeOptions' => ContactRequest::leadTypeLabels(),
            'contactTypeOptions' => ContactRequest::contactTypeLabels(),
            'sourceOptions' => ContactRequest::sourceLabels(),
            'propertyContextOptions' => ContactRequest::propertyContextLabels(),
            'statusOptions' => [
                'new' => 'Nuevo',
                'pending_assignment' => 'Pendiente',
                'contacted' => 'Contactado',
                'qualified' => 'Calificado',
                'converted' => 'Convertido',
                'closed' => 'Cerrado',
            ],
            'assignmentOptions' => [
                'pending_assignment' => 'Pendiente de asignacion',
                'assigned' => 'Asignado',
            ],
        ]);
    }

    public function update(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        $this->abortUnlessPropertyLead($contactRequest);

        if (!$this->canEditLead($request->user(), $contactRequest)) {
            abort(403, 'No tienes permisos para editar esta solicitud.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'contact_type' => ['required', 'string', Rule::in(array_keys(ContactRequest::contactTypeLabels()))],
            'status' => ['required', 'string', 'max:50', 'in:new,pending_assignment,contacted,qualified,converted,closed'],
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
            'message' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_lead_id', $contactRequest->id);
        }

        $data = $validator->validated();
        $ownerId = $data['owner_id'] ?? null;

        $contactRequest->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'contact_type' => $data['contact_type'],
            'status' => $data['status'],
            'message' => $data['message'] ?: $contactRequest->message,
            'owner_id' => $ownerId,
            'assignment_status' => $ownerId ? 'assigned' : 'pending_assignment',
            'assigned_at' => $ownerId
                ? ($contactRequest->assigned_at ?: now())
                : null,
        ]);

        if ($contactRequest->convertedClient) {
            $contactRequest->convertedClient->update([
                'owner_id' => $ownerId,
            ]);
        }

        return back()->with('status', 'Solicitud actualizada correctamente.');
    }

    public function convertToClient(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        $this->abortUnlessPropertyLead($contactRequest);

        if (!Rbac::canAny($request->user(), 'clients.create') || !$this->canViewLead($request->user(), $contactRequest)) {
            abort(403, 'No tienes permisos para convertir esta solicitud en cliente.');
        }

        $missing = $this->missingClientFields($contactRequest);
        if ($missing !== []) {
            return back()->with('error', 'No se puede convertir en cliente. Faltan datos: ' . implode(', ', $missing) . '.');
        }

        if ($contactRequest->converted_client_id) {
            return back()->with('status', 'Esta solicitud ya fue convertida en cliente.');
        }

        DB::transaction(function () use ($contactRequest) {
            $client = Client::create([
                'contact_request_id' => $contactRequest->id,
                'property_id' => $contactRequest->property_id,
                'owner_id' => $contactRequest->owner_id,
                'name' => (string) $contactRequest->name,
                'email' => (string) $contactRequest->email,
                'phone' => (string) $contactRequest->phone,
                'source' => $contactRequest->source ?: Client::SOURCE_PROPERTY_FORM,
                'contact_type' => $contactRequest->contact_type ?: Client::CONTACT_TYPE_BUYER,
                'status' => 'active',
                'notes' => 'Cliente convertido desde una solicitud publica.',
                'raw_payload' => [
                    'contact_request_id' => $contactRequest->id,
                    'property_public_id' => $contactRequest->property_public_id,
                    'property_name' => $contactRequest->property_form_name,
                    'lead_type' => $contactRequest->lead_type,
                    'contact_type' => $contactRequest->contact_type,
                    'source' => $contactRequest->source,
                    'lead_raw_payload' => $contactRequest->raw_payload,
                ],
            ]);

            $contactRequest->update([
                'converted_client_id' => $client->id,
                'converted_at' => now(),
                'status' => 'converted',
            ]);
        });

        return back()->with('status', 'Solicitud convertida en cliente correctamente.');
    }

    public function store(Request $request, PublicLeadCaptureService $leadCapture): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'property_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'privacy' => ['accepted'],
            'lead_type' => ['nullable', 'string', Rule::in(array_keys(ContactRequest::leadTypeLabels()))],
            'contact_type' => ['nullable', 'string', Rule::in(array_keys(ContactRequest::contactTypeLabels()))],
            'interest' => ['nullable', 'string', 'max:100'],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'referrer_url' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:10'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $lead = $leadCapture->capture(array_merge($validator->validated(), [
            'source' => ContactRequest::SOURCE_PROPERTY_DETAIL_FORM,
            'property_context' => ContactRequest::PROPERTY_CONTEXT_EXISTING_LISTING,
        ]), $request);

        return $this->apiCreated('Solicitud registrada', 'PROPERTY_CONTACT_REQUEST_CREATED', [
            'id' => $lead->id,
        ]);
    }

    private function abortUnlessPropertyLead(ContactRequest $contactRequest): void
    {
        abort_unless($contactRequest->isPublicFormLead(), 404);
    }

    private function canViewLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return true;
        }

        return Rbac::canAny($user, 'leads.view.own')
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function canEditLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.edit')) {
            return true;
        }

        return Rbac::canAny($user, 'leads.edit.own')
            && $this->isLeadOwnedBy($lead, $user);
    }

    private function isLeadOwnedBy(ContactRequest $lead, $user): bool
    {
        if (!$user) {
            return false;
        }

        return $lead->owner_id !== null
            && (int) $lead->owner_id === (int) $user->getAuthIdentifier();
    }

    private function missingClientFields(ContactRequest $lead): array
    {
        $fields = [];

        if (blank($lead->name)) {
            $fields[] = 'nombre';
        }

        if (blank($lead->email)) {
            $fields[] = 'email';
        }

        if (blank($lead->phone)) {
            $fields[] = 'telefono';
        }

        if ($lead->property_context === ContactRequest::PROPERTY_CONTEXT_EXISTING_LISTING && blank($lead->property_id)) {
            $fields[] = 'propiedad';
        }

        if (blank($lead->owner_id)) {
            $fields[] = 'usuario asignado';
        }

        return $fields;
    }

    private function scopeVisibleLeads($query, $user): void
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return;
        }

        if (Rbac::canAny($user, 'leads.view.own')) {
            $query->where(function ($leadQuery) use ($user) {
                $leadQuery->where('owner_id', $user->getAuthIdentifier());
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }
}
