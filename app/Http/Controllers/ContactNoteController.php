<?php

namespace App\Http\Controllers;

use App\Models\ContactNote;
use App\Models\ContactRequest;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactNoteController extends Controller
{
    public function index(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!$this->canViewLead($request->user('api'), $contactRequest)) {
            return $this->apiForbidden('No tienes permisos para ver notas de este contacto', 'CONTACT_NOTES_FORBIDDEN');
        }

        $query = $contactRequest->notes()->with('user')->latest();
        $this->scopeNotes($query, $request->user('api'));

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Notas del contacto', 'CONTACT_NOTES_LIST', $query->paginate($perPage));
    }

    public function store(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        if (!$this->canViewLead($request->user('api'), $contactRequest) || !Rbac::canAny($request->user('api'), 'crm.notes.create')) {
            return $this->apiForbidden('No tienes permisos para crear notas en este contacto', 'CONTACT_NOTE_CREATE_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:10000',
            'note_type' => 'sometimes|string|max:50',
            'metadata' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $note = $contactRequest->notes()->create(array_merge(
            $validator->validated(),
            ['user_id' => $request->user('api')->getAuthIdentifier()]
        ));

        return $this->apiCreated('Nota creada', 'CONTACT_NOTE_CREATED', $note->load('user'));
    }

    public function update(Request $request, ContactNote $contactNote): JsonResponse
    {
        if (!$this->canModifyNote($request->user('api'), $contactNote, 'edit')) {
            return $this->apiForbidden('No tienes permisos para editar esta nota', 'CONTACT_NOTE_EDIT_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'body' => 'sometimes|required|string|max:10000',
            'note_type' => 'sometimes|string|max:50',
            'metadata' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $contactNote->update($validator->validated());

        return $this->apiSuccess('Nota actualizada', 'CONTACT_NOTE_UPDATED', $contactNote->fresh('user'));
    }

    public function destroy(Request $request, ContactNote $contactNote): JsonResponse
    {
        if (!$this->canModifyNote($request->user('api'), $contactNote, 'delete')) {
            return $this->apiForbidden('No tienes permisos para eliminar esta nota', 'CONTACT_NOTE_DELETE_FORBIDDEN');
        }

        $contactNote->delete();

        return $this->apiSuccess('Nota enviada a papelera', 'CONTACT_NOTE_TRASHED', null);
    }

    private function scopeNotes($query, $user): void
    {
        if (Rbac::canAny($user, 'crm.notes.view.all')) {
            return;
        }

        if (Rbac::canAny($user, 'crm.notes.view.own')) {
            $query->where('user_id', $user->getAuthIdentifier());
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canModifyNote($user, ContactNote $note, string $action): bool
    {
        $note->loadMissing('contactRequest.property');

        if (!$this->canViewLead($user, $note->contactRequest)) {
            return false;
        }

        if (Rbac::canAny($user, "crm.notes.{$action}")) {
            return true;
        }

        return Rbac::canAny($user, "crm.notes.{$action}.own")
            && $user !== null
            && $note->user_id !== null
            && (int) $note->user_id === (int) $user->getAuthIdentifier();
    }

    private function canViewLead($user, ContactRequest $lead): bool
    {
        if (Rbac::canAny($user, 'leads.view.all')) {
            return true;
        }

        if (!Rbac::canAny($user, 'leads.view.own')) {
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
}
