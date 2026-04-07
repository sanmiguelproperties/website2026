<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgencyController extends Controller
{
    /**
     * GET /api/agencies
     */
    public function index(Request $request): JsonResponse
    {
        $query = Agency::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('is_primary')) {
            $query->where('is_primary', filter_var($request->input('is_primary'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'updated_at');
        $validOrders = ['id', 'name', 'is_primary', 'created_at', 'updated_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'updated_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de agencias', 'AGENCIES_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/agencies
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'account_owner' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
            'raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['raw_payload'] = $data['raw_payload'] ?? null;
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        // Evitar sobreescribir si ya existe.
        if (Agency::whereKey($data['id'])->exists()) {
            return $this->apiError('La agencia ya existe', 'AGENCY_ALREADY_EXISTS', ['id' => ['Ya existe una agencia con este id']], null, 409);
        }

        $agency = null;
        DB::transaction(function () use (&$agency, $data): void {
            if ($data['is_primary']) {
                $this->clearPrimaryExcept();
            }

            $agency = Agency::create($data);
        });

        return $this->apiCreated('Agencia creada exitosamente', 'AGENCY_CREATED', $agency);
    }

    /**
     * GET /api/agencies/{agency}
     */
    public function show(Request $request, Agency $agency): JsonResponse
    {
        return $this->apiSuccess('Agencia obtenida', 'AGENCY_SHOWN', $agency);
    }

    /**
     * PATCH /api/agencies/{agency}
     */
    public function update(Request $request, Agency $agency): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'account_owner' => 'sometimes|nullable|string|max:255',
            'logo_url' => 'sometimes|nullable|string',
            'phone' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'is_primary' => 'sometimes|boolean',
            'raw_payload' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        DB::transaction(function () use ($agency, $data): void {
            if (array_key_exists('is_primary', $data) && (bool) $data['is_primary'] === true) {
                $this->clearPrimaryExcept((int) $agency->id);
            }

            $agency->update($data);
        });

        return $this->apiSuccess('Agencia actualizada', 'AGENCY_UPDATED', $agency->fresh());
    }

    /**
     * DELETE /api/agencies/{agency}
     */
    public function destroy(Request $request, Agency $agency): JsonResponse
    {
        $agency->delete();

        return $this->apiSuccess('Agencia eliminada', 'AGENCY_DELETED', null);
    }

    /**
     * PATCH /api/agencies/{agency}/primary
     */
    public function setPrimary(Request $request, Agency $agency): JsonResponse
    {
        DB::transaction(function () use ($agency): void {
            $this->clearPrimaryExcept((int) $agency->id);

            if (!$agency->is_primary) {
                $agency->update(['is_primary' => true]);
            }
        });

        return $this->apiSuccess('Agencia principal actualizada', 'AGENCY_PRIMARY_UPDATED', $agency->fresh());
    }

    protected function clearPrimaryExcept(?int $agencyId = null): void
    {
        $query = Agency::query()->where('is_primary', true);

        if ($agencyId !== null) {
            $query->whereKeyNot($agencyId);
        }

        $query->update(['is_primary' => false]);
    }
}
