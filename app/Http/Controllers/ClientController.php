<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\Rbac;
use Illuminate\Http\Request;
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
}
