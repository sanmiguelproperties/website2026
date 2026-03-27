<?php

namespace App\Http\Controllers;

use App\Models\MLSOffice;
use App\Models\Property;
use App\Services\EasyBrokerMlsExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EasyBrokerMlsExportController extends Controller
{
    public function __construct(
        protected EasyBrokerMlsExportService $exportService
    ) {
    }

    /**
     * GET /api/easybroker/mls-export/offices
     * Lista agencias/oficinas MLS con conteo de propiedades MLS.
     */
    public function offices(): JsonResponse
    {
        $offices = MLSOffice::query()
            ->select([
                'mls_office_id',
                'name',
                'city',
                'state_province',
                'is_managed_by_us',
                'updated_at',
            ])
            ->whereHas('properties', function ($query) {
                $query->where('source', Property::SOURCE_MLS);
            })
            ->withCount([
                'properties as mls_properties_count' => function ($query) {
                    $query->where('source', Property::SOURCE_MLS);
                },
            ])
            ->orderBy('name')
            ->get();

        return $this->apiSuccess(
            'Listado de agencias MLS para exportación',
            'EASYBROKER_MLS_EXPORT_OFFICES',
            $offices
        );
    }

    /**
     * GET /api/easybroker/mls-export/property-types
     * Obtiene tipos de propiedad válidos desde EasyBroker.
     */
    public function propertyTypes(): JsonResponse
    {
        $this->exportService->reloadConfiguration();

        if (!$this->exportService->isConfigured()) {
            return $this->apiError(
                'EasyBroker no está configurado. Configura la API Key antes de consultar tipos de propiedad.',
                'EASYBROKER_NOT_CONFIGURED',
                null,
                null,
                400
            );
        }

        $types = $this->exportService->fetchPropertyTypes();

        return $this->apiSuccess(
            'Tipos de propiedad de EasyBroker',
            'EASYBROKER_PROPERTY_TYPES',
            [
                'types' => $types,
                'total' => count($types),
            ]
        );
    }

    /**
     * GET /api/easybroker/mls-export/properties
     * Lista propiedades MLS locales para previsualizar/exportar.
     */
    public function properties(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'sometimes|nullable|string|max:255',
            'mls_office_id' => 'sometimes|nullable|integer',
            'synced' => 'sometimes|nullable|in:all,synced,unsynced',
            'per_page' => 'sometimes|nullable|integer|min:1|max:100',
            'page' => 'sometimes|nullable|integer|min:1',
            'target_status' => 'sometimes|nullable|in:published,not_published',
            'fallback_property_type' => 'sometimes|nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $query = Property::query()
            ->fromMLS()
            ->with([
                'mlsOffice:mls_office_id,name,city,state_province,is_managed_by_us',
                'location:property_id,region,city,city_area,street,latitude,longitude',
                'operations:id,property_id,operation_type,amount,currency_code',
                'mediaAssets:id,url,storage_path,name,alt',
            ])
            ->withCount(['operations', 'features', 'tags']);

        if (!empty($data['search'])) {
            $search = trim((string) $data['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('mls_public_id', 'like', "%{$search}%")
                    ->orWhere('mls_id', 'like', "%{$search}%")
                    ->orWhere('easybroker_public_id', 'like', "%{$search}%")
                    ->orWhere('property_type_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if (!empty($data['mls_office_id'])) {
            $query->where('mls_office_id', (int) $data['mls_office_id']);
        }

        $syncedFilter = $data['synced'] ?? 'all';
        if ($syncedFilter === 'synced') {
            $query->whereNotNull('easybroker_public_id')->where('easybroker_public_id', '!=', '');
        } elseif ($syncedFilter === 'unsynced') {
            $query->where(function ($q) {
                $q->whereNull('easybroker_public_id')->orWhere('easybroker_public_id', '=');
            });
        }

        $perPage = (int) ($data['per_page'] ?? 15);
        $targetStatus = $data['target_status'] ?? 'not_published';
        $fallbackPropertyType = $data['fallback_property_type'] ?? null;

        $paginated = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $paginated->getCollection()->transform(function (Property $property) use ($fallbackPropertyType, $targetStatus) {
            $draft = $this->exportService->buildDraftPayload(
                $property,
                $fallbackPropertyType,
                null,
                $targetStatus
            );

            $primaryOperation = $property->operations->first();
            $amount = $primaryOperation?->amount;
            $currency = $primaryOperation?->currency_code;

            return [
                'id' => $property->id,
                'mls_public_id' => $property->mls_public_id,
                'mls_id' => $property->mls_id,
                'easybroker_public_id' => $property->easybroker_public_id,
                'title' => $property->title,
                'status' => $property->status,
                'category' => $property->category,
                'property_type_name' => $property->property_type_name,
                'published' => (bool) $property->published,
                'allow_integration' => (bool) $property->allow_integration,
                'last_synced_at' => $property->last_synced_at?->toIso8601String(),
                'updated_at' => $property->updated_at?->toIso8601String(),
                'mls_office' => $property->mlsOffice ? [
                    'mls_office_id' => $property->mlsOffice->mls_office_id,
                    'name' => $property->mlsOffice->name,
                    'city' => $property->mlsOffice->city,
                    'state_province' => $property->mlsOffice->state_province,
                    'is_managed_by_us' => (bool) $property->mlsOffice->is_managed_by_us,
                ] : null,
                'location' => $property->location ? [
                    'street' => $property->location->street,
                    'city_area' => $property->location->city_area,
                    'city' => $property->location->city,
                    'region' => $property->location->region,
                ] : null,
                'primary_operation' => $primaryOperation ? [
                    'type' => $primaryOperation->operation_type,
                    'amount' => $amount,
                    'currency_code' => $currency,
                ] : null,
                'counts' => [
                    'operations' => (int) $property->operations_count,
                    'features' => (int) $property->features_count,
                    'tags' => (int) $property->tags_count,
                ],
                'export_preview' => [
                    'ready' => empty($draft['missing_required']),
                    'missing_required' => $draft['missing_required'],
                    'resolved' => $draft['resolved'],
                ],
            ];
        });

        return $this->apiSuccess(
            'Listado de propiedades MLS para exportación',
            'EASYBROKER_MLS_EXPORT_PROPERTIES',
            $paginated
        );
    }

    /**
     * POST /api/easybroker/mls-export/send
     * Envía propiedades MLS seleccionadas a EasyBroker.
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1|max:200',
            'property_ids.*' => 'integer',
            'target_status' => 'sometimes|nullable|in:published,not_published',
            'fallback_property_type' => 'sometimes|nullable|string|max:120',
            'dry_run' => 'sometimes|boolean',
            'create_if_missing_on_404' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $this->exportService->reloadConfiguration();
        if (!$this->exportService->isConfigured()) {
            return $this->apiError(
                'EasyBroker no está configurado. Configura la API Key antes de exportar propiedades.',
                'EASYBROKER_NOT_CONFIGURED',
                null,
                null,
                400
            );
        }

        $propertyIds = array_values(array_unique(array_map('intval', $data['property_ids'])));
        $targetStatus = $data['target_status'] ?? 'not_published';
        $fallbackPropertyType = $data['fallback_property_type'] ?? null;
        $dryRun = (bool) ($data['dry_run'] ?? false);
        $createIfMissingOn404 = (bool) ($data['create_if_missing_on_404'] ?? true);

        $properties = Property::query()
            ->fromMLS()
            ->with([
                'location',
                'operations.currency',
                'tags:id,name',
                'mlsOffice:mls_office_id,name,city,state_province,is_managed_by_us',
                'mediaAssets:id,url,storage_path,name,alt',
            ])
            ->whereIn('id', $propertyIds)
            ->get()
            ->keyBy('id');

        $allowedPropertyTypes = $this->exportService->fetchPropertyTypes();

        $results = [];
        $stats = [
            'requested' => count($propertyIds),
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];

        foreach ($propertyIds as $propertyId) {
            $property = $properties->get($propertyId);

            if (!$property) {
                $stats['errors']++;
                $results[] = [
                    'success' => false,
                    'action' => 'error',
                    'property_id' => $propertyId,
                    'message' => 'La propiedad no existe o no pertenece al origen MLS.',
                ];
                continue;
            }

            $stats['processed']++;

            if ($dryRun) {
                $draft = $this->exportService->buildDraftPayload(
                    $property,
                    $fallbackPropertyType,
                    $allowedPropertyTypes,
                    $targetStatus
                );

                $ready = empty($draft['missing_required']);
                if ($ready) {
                    $stats['skipped']++;
                } else {
                    $stats['errors']++;
                }

                $results[] = [
                    'success' => $ready,
                    'action' => 'dry_run',
                    'property_id' => $property->id,
                    'easybroker_public_id' => $property->easybroker_public_id,
                    'message' => $ready
                        ? 'Payload válido para envío.'
                        : 'Faltan campos obligatorios para enviar.',
                    'missing_required' => $draft['missing_required'],
                    'resolved' => $draft['resolved'],
                    'request_payload' => $draft['payload'],
                ];

                continue;
            }

            $result = $this->exportService->pushProperty($property, [
                'target_status' => $targetStatus,
                'fallback_property_type' => $fallbackPropertyType,
                'allowed_property_types' => $allowedPropertyTypes,
                'create_if_missing_on_404' => $createIfMissingOn404,
            ]);

            if ($result['success']) {
                if (($result['action'] ?? null) === 'created') {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }
            } else {
                if (($result['action'] ?? null) === 'skipped') {
                    $stats['skipped']++;
                } else {
                    $stats['errors']++;
                }
            }

            $results[] = $result;
        }

        return $this->apiSuccess(
            $dryRun
                ? 'Prevalidación completada'
                : 'Exportación MLS ? EasyBroker completada',
            $dryRun
                ? 'EASYBROKER_MLS_EXPORT_DRY_RUN'
                : 'EASYBROKER_MLS_EXPORT_COMPLETED',
            [
                'dry_run' => $dryRun,
                'stats' => $stats,
                'allowed_property_types_total' => count($allowedPropertyTypes),
                'results' => $results,
            ]
        );
    }
}

