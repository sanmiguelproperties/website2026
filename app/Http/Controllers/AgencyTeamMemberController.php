<?php

namespace App\Http\Controllers;

use App\Models\AgencyTeamMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AgencyTeamMemberController extends Controller
{
    /**
     * GET /api/team-members
     */
    public function index(Request $request): JsonResponse
    {
        $query = AgencyTeamMember::query()->with('photoMediaAsset');

        $this->applyFilters($query, $request, false);
        $this->applySorting($query, $request, false);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de integrantes del equipo', 'TEAM_MEMBERS_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/team-members/{teamMember}
     */
    public function show(Request $request, AgencyTeamMember $teamMember): JsonResponse
    {
        return $this->apiSuccess('Integrante obtenido', 'TEAM_MEMBER_SHOWN', $teamMember->load('photoMediaAsset'));
    }

    /**
     * POST /api/team-members
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:180',
            'position_es' => 'required|string|max:180',
            'position_en' => 'nullable|string|max:180',
            'department_es' => 'nullable|string|max:120',
            'department_en' => 'nullable|string|max:120',
            'bio_es' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'specialties_es' => 'nullable|string',
            'specialties_en' => 'nullable|string',
            'email' => 'nullable|email|max:180',
            'phone' => 'nullable|string|max:60',
            'whatsapp' => 'nullable|string|max:60',
            'linkedin_url' => 'nullable|url|max:255',
            'photo_media_asset_id' => 'nullable|integer|exists:media_assets,id',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['is_featured'] = array_key_exists('is_featured', $data) ? (bool) $data['is_featured'] : false;
        $data['sort_order'] = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

        $member = AgencyTeamMember::create($data);

        return $this->apiCreated('Integrante creado', 'TEAM_MEMBER_CREATED', $member->load('photoMediaAsset'));
    }

    /**
     * PUT/PATCH /api/team-members/{teamMember}
     */
    public function update(Request $request, AgencyTeamMember $teamMember): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:180',
            'position_es' => 'sometimes|required|string|max:180',
            'position_en' => 'sometimes|nullable|string|max:180',
            'department_es' => 'sometimes|nullable|string|max:120',
            'department_en' => 'sometimes|nullable|string|max:120',
            'bio_es' => 'sometimes|nullable|string',
            'bio_en' => 'sometimes|nullable|string',
            'specialties_es' => 'sometimes|nullable|string',
            'specialties_en' => 'sometimes|nullable|string',
            'email' => 'sometimes|nullable|email|max:180',
            'phone' => 'sometimes|nullable|string|max:60',
            'whatsapp' => 'sometimes|nullable|string|max:60',
            'linkedin_url' => 'sometimes|nullable|url|max:255',
            'photo_media_asset_id' => 'sometimes|nullable|integer|exists:media_assets,id',
            'sort_order' => 'sometimes|nullable|integer|min:0|max:1000000',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('sort_order', $data) && $data['sort_order'] === null) {
            $data['sort_order'] = 0;
        }

        $teamMember->update($data);

        return $this->apiSuccess('Integrante actualizado', 'TEAM_MEMBER_UPDATED', $teamMember->fresh()->load('photoMediaAsset'));
    }

    /**
     * DELETE /api/team-members/{teamMember}
     */
    public function destroy(Request $request, AgencyTeamMember $teamMember): JsonResponse
    {
        $teamMember->delete();

        return $this->apiSuccess('Integrante eliminado', 'TEAM_MEMBER_DELETED', null);
    }

    /**
     * GET /api/team-members/departments
     */
    public function departments(Request $request): JsonResponse
    {
        return $this->apiSuccess('Departamentos obtenidos', 'TEAM_MEMBER_DEPARTMENTS', $this->buildDepartments(false));
    }

    /**
     * GET /api/public/team-members
     */
    public function indexPublic(Request $request): JsonResponse
    {
        $locale = $this->normalizeLocale((string) $request->input('locale', app()->getLocale()));

        $query = AgencyTeamMember::query()
            ->with('photoMediaAsset')
            ->active();

        $this->applyFilters($query, $request, true);
        $this->applySorting($query, $request, true);

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min(60, $perPage));

        $paginated = $query->paginate($perPage);
        $paginated->setCollection(
            $paginated->getCollection()->map(fn (AgencyTeamMember $member) => $this->publicMemberPayload($member, $locale))
        );

        return $this->apiSuccess('Equipo obtenido', 'TEAM_MEMBERS_PUBLIC_LIST', [
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'from' => $paginated->firstItem(),
            'to' => $paginated->lastItem(),
            'departments' => $this->buildDepartments(true),
        ]);
    }

    /**
     * GET /api/public/team-members/departments
     */
    public function departmentsPublic(Request $request): JsonResponse
    {
        return $this->apiSuccess('Departamentos publicados', 'TEAM_MEMBER_DEPARTMENTS_PUBLIC', $this->buildDepartments(true));
    }

    private function applyFilters(Builder $query, Request $request, bool $public): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('position_es', 'like', "%{$search}%")
                    ->orWhere('position_en', 'like', "%{$search}%")
                    ->orWhere('department_es', 'like', "%{$search}%")
                    ->orWhere('department_en', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!$public && $request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('department')) {
            $department = trim((string) $request->input('department'));
            $departmentLower = Str::lower($department);
            $departmentSlug = Str::slug($department);

            $query->where(function (Builder $builder) use ($department, $departmentLower, $departmentSlug): void {
                $builder
                    ->where('department_es', $department)
                    ->orWhere('department_en', $department)
                    ->orWhereRaw('LOWER(department_es) = ?', [$departmentLower])
                    ->orWhereRaw('LOWER(department_en) = ?', [$departmentLower])
                    ->orWhereRaw("LOWER(REPLACE(REPLACE(department_es, ' ', '-'), '_', '-')) = ?", [$departmentSlug])
                    ->orWhereRaw("LOWER(REPLACE(REPLACE(department_en, ' ', '-'), '_', '-')) = ?", [$departmentSlug]);
            });
        }
    }

    private function applySorting(Builder $query, Request $request, bool $public): void
    {
        $order = (string) $request->input('order', $public ? 'sort_order' : 'sort_order');
        $sort = strtolower((string) $request->input('sort', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedOrders = ['id', 'full_name', 'department_es', 'department_en', 'sort_order', 'is_active', 'is_featured', 'updated_at'];
        if (!in_array($order, $allowedOrders, true)) {
            $order = 'sort_order';
        }

        if ($public) {
            $query->orderByDesc('is_featured');
        }

        $query
            ->orderBy($order, $sort)
            ->orderBy('full_name', 'asc');
    }

    private function normalizeLocale(string $locale): string
    {
        return strtolower(trim($locale)) === 'en' ? 'en' : 'es';
    }

    private function buildDepartments(bool $onlyActive): array
    {
        $query = AgencyTeamMember::query();
        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $rows = $query
            ->select('department_es', 'department_en')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $nameEs = trim((string) ($row->department_es ?? ''));
            $nameEn = trim((string) ($row->department_en ?? ''));

            $seed = $nameEn !== '' ? $nameEn : $nameEs;
            if ($seed === '') {
                continue;
            }

            $key = Str::slug($seed);
            if ($key === '') {
                continue;
            }

            if (!isset($map[$key])) {
                $map[$key] = [
                    'key' => $key,
                    'name_es' => $nameEs !== '' ? $nameEs : $nameEn,
                    'name_en' => $nameEn !== '' ? $nameEn : $nameEs,
                ];
                continue;
            }

            if ($map[$key]['name_es'] === '' && $nameEs !== '') {
                $map[$key]['name_es'] = $nameEs;
            }
            if ($map[$key]['name_en'] === '' && $nameEn !== '') {
                $map[$key]['name_en'] = $nameEn;
            }
        }

        $result = array_values($map);

        usort($result, function (array $a, array $b): int {
            return strcasecmp($a['name_es'] ?: $a['name_en'], $b['name_es'] ?: $b['name_en']);
        });

        return $result;
    }

    private function publicMemberPayload(AgencyTeamMember $member, string $locale): array
    {
        $department = $member->department($locale);

        return [
            'id' => $member->id,
            'full_name' => $member->full_name,
            'position' => $member->position($locale),
            'department' => $department,
            'department_key' => $department ? Str::slug($department) : null,
            'bio' => $member->bio($locale),
            'specialties' => $member->specialties($locale),
            'email' => $member->email,
            'phone' => $member->phone,
            'whatsapp' => $member->whatsapp,
            'linkedin_url' => $member->linkedin_url,
            'photo_url' => $member->photoUrl(),
            'is_featured' => (bool) $member->is_featured,
            'sort_order' => (int) $member->sort_order,
        ];
    }
}
