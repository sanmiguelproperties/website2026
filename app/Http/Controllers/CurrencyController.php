<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    /**
     * GET /api/currencies
     */
    public function index(Request $request): JsonResponse
    {
        $query = Currency::query();

        if ($request->filled('search')) {
            $search = trim((string)$request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'created_at');
        $validOrders = ['created_at', 'updated_at', 'name', 'code', 'exchange_rate'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'created_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int)$request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $data = $query->paginate($perPage);

        // Formatear exchange_rate a 2 decimales
        $data->getCollection()->transform(function ($currency) {
            $currency->exchange_rate = number_format($currency->exchange_rate, 2, '.', '');
            return $currency;
        });

        return $this->apiSuccess('Listado de monedas', 'CURRENCIES_LIST', $data);
    }

    /**
     * POST /api/currencies
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0|max:999999.99',
            'is_base' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Redondear exchange_rate a 2 decimales
        $validated['exchange_rate'] = round($validated['exchange_rate'], 2);

        // Si se marca como base, quitar la marca de base de otras monedas
        if ($validated['is_base'] ?? false) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        $currency = Currency::create($validated);

        // Formatear para respuesta
        $currency->exchange_rate = number_format($currency->exchange_rate, 2, '.', '');

        return $this->apiCreated('Moneda creada exitosamente', 'CURRENCY_CREATED', $currency);
    }

    /**
     * GET /api/currencies/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return $this->apiNotFound('Moneda no encontrada', 'CURRENCY_NOT_FOUND');
        }

        // Formatear exchange_rate a 2 decimales
        $currency->exchange_rate = number_format($currency->exchange_rate, 2, '.', '');

        return $this->apiSuccess('Moneda obtenida', 'CURRENCY_SHOWN', $currency);
    }

    /**
     * PATCH /api/currencies/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return $this->apiNotFound('Moneda no encontrada', 'CURRENCY_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => ['sometimes', 'required', 'string', 'size:3', Rule::unique('currencies')->ignore($id)],
            'symbol' => 'sometimes|required|string|max:10',
            'exchange_rate' => 'sometimes|required|numeric|min:0|max:999999.99',
            'is_base' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Redondear exchange_rate a 2 decimales si se proporciona
        if (isset($validated['exchange_rate'])) {
            $validated['exchange_rate'] = round($validated['exchange_rate'], 2);
        }

        // Si se marca como base, quitar la marca de base de otras monedas
        if (($validated['is_base'] ?? false) && !$currency->is_base) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        $currency->update($validated);

        // Formatear para respuesta
        $currency->exchange_rate = number_format($currency->exchange_rate, 2, '.', '');

        return $this->apiSuccess('Moneda actualizada', 'CURRENCY_UPDATED', $currency);
    }

    /**
     * DELETE /api/currencies/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return $this->apiNotFound('Moneda no encontrada', 'CURRENCY_NOT_FOUND');
        }

        // No permitir eliminar la moneda base
        if ($currency->is_base) {
            return $this->apiForbidden('No se puede eliminar la moneda base', 'BASE_CURRENCY_DELETE_FORBIDDEN');
        }

        $currency->delete();

        return $this->apiSuccess('Moneda eliminada', 'CURRENCY_DELETED', null);
    }
}