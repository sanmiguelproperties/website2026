<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyLookupController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $selectedId = $request->query('selected');

        $query = Property::query()
            ->with('location')
            ->select([
                'id',
                'title',
                'mls_id',
                'mls_public_id',
                'mls_office_id',
                'mls_neighborhood',
                'easybroker_public_id',
            ])
            ->orderByDesc('updated_at')
            ->limit(20);

        if ($search !== '') {
            $query->where(function ($propertyQuery) use ($search) {
                $propertyQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('mls_public_id', 'like', "%{$search}%")
                    ->orWhere('mls_id', 'like', "%{$search}%")
                    ->orWhere('mls_office_id', 'like', "%{$search}%")
                    ->orWhere('mls_neighborhood', 'like', "%{$search}%")
                    ->orWhere('easybroker_public_id', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($locationQuery) use ($search) {
                        $locationQuery
                            ->where('region', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('city_area', 'like', "%{$search}%")
                            ->orWhere('street', 'like', "%{$search}%")
                            ->orWhere('postal_code', 'like', "%{$search}%");
                    });
            });
        } elseif (is_numeric($selectedId)) {
            $query->whereKey((int) $selectedId);
        } else {
            $query->whereRaw('1 = 0');
        }

        $properties = $query
            ->get()
            ->map(fn (Property $property): array => $this->formatProperty($property))
            ->values();

        return response()->json([
            'data' => $properties,
        ]);
    }

    private function formatProperty(Property $property): array
    {
        $locationParts = collect([
            $property->location?->city_area,
            $property->location?->city,
            $property->location?->region,
        ])
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'id' => $property->id,
            'title' => $property->title ?: 'Propiedad #' . $property->id,
            'subtitle' => implode(', ', $locationParts),
            'mls_public_id' => $property->mls_public_id,
            'mls_id' => $property->mls_id,
            'mls_office_id' => $property->mls_office_id,
            'easybroker_public_id' => $property->easybroker_public_id,
            'label' => trim(($property->title ?: 'Propiedad #' . $property->id) . ' #' . $property->id),
        ];
    }
}
