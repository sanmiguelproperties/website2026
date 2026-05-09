<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\ContactRequest;
use App\Models\Property;
use App\Support\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isGlobalScope = Rbac::isSuperAdmin($user);
        $today = now();
        $startOfDay = $today->copy()->startOfDay();
        $endOfDay = $today->copy()->endOfDay();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $propertiesQuery = $this->visiblePropertiesQuery($user);
        $leadsQuery = $this->visibleLeadsQuery($user);
        $clientsQuery = $this->visibleClientsQuery($user);
        $visitsQuery = $this->visibleVisitsQuery($user);

        $totalLeads = (clone $leadsQuery)->count();
        $convertedLeads = (clone $leadsQuery)->whereNotNull('converted_client_id')->count();

        $stats = [
            'properties_total' => (clone $propertiesQuery)->count(),
            'properties_published' => (clone $propertiesQuery)->where('published', true)->count(),
            'properties_unpublished' => (clone $propertiesQuery)->where('published', false)->count(),
            'properties_for_sale' => (clone $propertiesQuery)
                ->where(function ($query): void {
                    $query->where('for_rent', false)
                        ->orWhereHas('operations', fn ($operationQuery) => $operationQuery->where('operation_type', 'sale'));
                })
                ->count(),
            'properties_for_rent' => (clone $propertiesQuery)
                ->where(function ($query): void {
                    $query->where('for_rent', true)
                        ->orWhereHas('operations', fn ($operationQuery) => $operationQuery->whereIn('operation_type', ['rent', 'rental']));
                })
                ->count(),

            'leads_total' => $totalLeads,
            'leads_today' => (clone $leadsQuery)->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'leads_this_month' => (clone $leadsQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'leads_pending' => (clone $leadsQuery)->where('assignment_status', 'pending_assignment')->count(),
            'leads_assigned' => (clone $leadsQuery)->where('assignment_status', 'assigned')->count(),
            'leads_converted' => $convertedLeads,
            'conversion_rate' => $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0,

            'clients_total' => (clone $clientsQuery)->count(),
            'clients_active' => (clone $clientsQuery)->where('status', Client::STATUS_ACTIVE)->count(),
            'clients_this_month' => (clone $clientsQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),

            'visits_today' => (clone $visitsQuery)->whereBetween('scheduled_at', [$startOfDay, $endOfDay])->count(),
            'visits_this_month' => (clone $visitsQuery)->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])->count(),
            'visits_upcoming' => (clone $visitsQuery)
                ->where('scheduled_at', '>=', $today)
                ->where('status', ClientVisit::STATUS_SCHEDULED)
                ->count(),
            'visits_completed_this_month' => (clone $visitsQuery)
                ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
                ->where('status', ClientVisit::STATUS_COMPLETED)
                ->count(),
        ];

        return view('dashboard', [
            'dashboardUser' => $user,
            'dashboardScope' => [
                'is_global' => $isGlobalScope,
                'label' => $isGlobalScope ? 'Datos generales' : 'Tus datos',
                'description' => $isGlobalScope
                    ? 'Metricas consolidadas de todo el sistema.'
                    : 'Metricas asociadas a tu usuario, tus leads, clientes, visitas y propiedades asignadas.',
            ],
            'dashboardStats' => $stats,
            'recentLeads' => (clone $leadsQuery)
                ->with(['property', 'owner'])
                ->latest()
                ->limit(6)
                ->get(),
            'upcomingVisits' => (clone $visitsQuery)
                ->with(['client', 'property', 'assignedUser'])
                ->where('scheduled_at', '>=', $today)
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get(),
            'recentProperties' => (clone $propertiesQuery)
                ->with(['operations.currency', 'location'])
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get(),
            'leadSources' => $this->leadBreakdown(
                $this->buildDashboardBreakdownRows($leadsQuery, 'source', 'sin_origen'),
                ContactRequest::sourceLabels(),
                'Sin origen',
                $totalLeads
            ),
            'leadTypes' => $this->leadBreakdown(
                $this->buildDashboardBreakdownRows($leadsQuery, 'contact_type', 'sin_tipo'),
                ContactRequest::contactTypeLabels(),
                'Sin tipo',
                $totalLeads
            ),
            'topLeadProperties' => (clone $leadsQuery)
                ->select('property_id', DB::raw('COUNT(*) as leads_count'))
                ->whereNotNull('property_id')
                ->with('property:id,title')
                ->groupBy('property_id')
                ->orderByDesc('leads_count')
                ->limit(5)
                ->get(),
            'recentActivity' => $this->recentActivity($leadsQuery, $clientsQuery, $visitsQuery),
            'dashboardPermissions' => [
                'can_create_property' => Rbac::canAny($user, 'properties.create'),
                'can_view_leads' => Rbac::canAny($user, 'leads.view'),
                'can_view_clients' => Rbac::canAny($user, 'clients.view'),
                'can_view_calendar' => Rbac::canAny($user, 'calendar.view'),
                'can_create_visit' => Rbac::canAny($user, 'calendar.view')
                    && Rbac::canAny($user, 'clients.edit|clients.edit.own'),
            ],
        ]);
    }

    private function visiblePropertiesQuery($user)
    {
        $query = Property::query();

        if (Rbac::isSuperAdmin($user)) {
            return $query;
        }

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('agent_user_id', $user->getAuthIdentifier());
    }

    private function visibleLeadsQuery($user)
    {
        $query = ContactRequest::query()->fromPublicForms();

        if (Rbac::isSuperAdmin($user)) {
            return $query;
        }

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $userId = $user->getAuthIdentifier();

        return $query->where(function ($leadQuery) use ($userId): void {
            $leadQuery
                ->where('owner_id', $userId)
                ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('agent_user_id', $userId));
        });
    }

    private function visibleClientsQuery($user)
    {
        $query = Client::query();

        if (Rbac::isSuperAdmin($user)) {
            return $query;
        }

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $userId = $user->getAuthIdentifier();

        return $query->where(function ($clientQuery) use ($userId): void {
            $clientQuery
                ->where('owner_id', $userId)
                ->orWhereHas('contactRequest', fn ($leadQuery) => $leadQuery->where('owner_id', $userId))
                ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('agent_user_id', $userId));
        });
    }

    private function visibleVisitsQuery($user)
    {
        $query = ClientVisit::query();

        if (Rbac::isSuperAdmin($user)) {
            return $query;
        }

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $userId = $user->getAuthIdentifier();

        return $query->where(function ($visitQuery) use ($userId): void {
            $visitQuery
                ->where('assigned_user_id', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereHas('client', function ($clientQuery) use ($userId): void {
                    $clientQuery
                        ->where('owner_id', $userId)
                        ->orWhereHas('contactRequest', fn ($leadQuery) => $leadQuery->where('owner_id', $userId))
                        ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('agent_user_id', $userId));
                });
        });
    }

    private function buildDashboardBreakdownRows($baseQuery, string $column, string $fallbackKey): Collection
    {
        $normalizedExpression = match ($column) {
            'source' => "COALESCE(NULLIF(source, ''), 'sin_origen')",
            'contact_type' => "COALESCE(NULLIF(contact_type, ''), 'sin_tipo')",
            default => throw new \InvalidArgumentException("Unsupported dashboard breakdown column: {$column}"),
        };

        return DB::query()
            ->fromSub(
                (clone $baseQuery)->selectRaw("{$normalizedExpression} as dashboard_key"),
                'dashboard_breakdown'
            )
            ->selectRaw('dashboard_key, COUNT(*) as total')
            ->groupBy('dashboard_key')
            ->orderByDesc('total')
            ->limit(6)
            ->get();
    }

    private function leadBreakdown(Collection $rows, array $labels, string $fallbackLabel, int $totalLeads): Collection
    {
        return $rows->map(function ($row) use ($labels, $fallbackLabel, $totalLeads): array {
            $key = (string) $row->dashboard_key;
            $total = (int) $row->total;

            return [
                'key' => $key,
                'label' => $labels[$key] ?? $fallbackLabel,
                'total' => $total,
                'percentage' => $totalLeads > 0 ? round(($total / $totalLeads) * 100) : 0,
            ];
        });
    }

    private function recentActivity($leadsQuery, $clientsQuery, $visitsQuery): Collection
    {
        $leadActivity = (clone $leadsQuery)
            ->latest()
            ->limit(4)
            ->get(['id', 'name', 'contact_type', 'created_at'])
            ->map(fn (ContactRequest $lead): array => [
                'label' => 'Nuevo lead',
                'title' => $lead->name ?: 'Lead sin nombre',
                'detail' => $lead->contact_type_label,
                'date' => $lead->created_at,
                'route' => route('property-contact-requests', ['search' => $lead->id]),
                'tone' => 'primary',
            ]);

        $clientActivity = (clone $clientsQuery)
            ->latest()
            ->limit(4)
            ->get(['id', 'name', 'created_at'])
            ->map(fn (Client $client): array => [
                'label' => 'Cliente creado',
                'title' => $client->name ?: 'Cliente sin nombre',
                'detail' => 'CRM',
                'date' => $client->created_at,
                'route' => route('clients.show', $client),
                'tone' => 'accent',
            ]);

        $visitActivity = (clone $visitsQuery)
            ->with('client:id,name')
            ->whereNotNull('scheduled_at')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get(['id', 'client_id', 'reason', 'status', 'scheduled_at', 'updated_at'])
            ->map(fn (ClientVisit $visit): array => [
                'label' => 'Visita actualizada',
                'title' => $visit->client?->name ?: $visit->reason,
                'detail' => $this->visitStatusLabel($visit->status) . ' - ' . $visit->scheduled_at?->format('d/m/Y H:i'),
                'date' => $visit->updated_at,
                'route' => route('calendar', [
                    'month' => $visit->scheduled_at?->format('Y-m'),
                    'visit' => $visit->id,
                ]),
                'tone' => 'muted',
            ]);

        return $leadActivity
            ->merge($clientActivity)
            ->merge($visitActivity)
            ->sortByDesc(fn (array $item): int => $item['date'] instanceof Carbon ? $item['date']->getTimestamp() : 0)
            ->take(6)
            ->values();
    }

    private function visitStatusLabel(?string $status): string
    {
        return match ($status) {
            ClientVisit::STATUS_SCHEDULED => 'Pautada',
            ClientVisit::STATUS_COMPLETED => 'Realizada',
            ClientVisit::STATUS_CANCELLED => 'Cancelada',
            default => 'Sin estado',
        };
    }
}