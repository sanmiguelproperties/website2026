<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

}
