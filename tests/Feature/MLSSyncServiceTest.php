<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\MLSOffice;
use App\Models\Property;
use App\Services\MLSSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MLSSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_restores_and_updates_existing_soft_deleted_mls_property(): void
    {
        Agency::create([
            'id' => 1,
            'name' => 'Primary Agency',
            'is_primary' => true,
        ]);

        Agency::create([
            'id' => 2,
            'name' => 'Synced Agency',
            'is_primary' => false,
        ]);

        MLSOffice::create([
            'mls_office_id' => 13,
            'name' => 'MLS Office #13',
            'paid' => false,
        ]);

        $existing = Property::create([
            'agency_id' => 1,
            'source' => Property::SOURCE_MLS,
            'easybroker_public_id' => null,
            'mls_id' => 4,
            'mls_public_id' => '7634',
            'published' => true,
            'title' => 'Old title',
            'raw_payload' => ['id' => 4, 'mls_id' => 7634],
        ]);
        $existing->delete();

        $service = new TestableMLSSyncService;

        $synced = $service->syncPropertyForTest([
            'id' => 4,
            'mls_id' => 7634,
            'name' => 'Invierte En Una Propiedad De Lujo',
            'price' => 440000,
            'currency' => 'MXN',
            'office_id' => 13,
            'status' => 'For Sale',
            'category' => 'Residential',
            'created_at' => '2025-04-15T06:43:26.000000Z',
            'updated_at' => '2026-02-25T05:35:07.000000Z',
        ]);

        $this->assertTrue($synced);
        $this->assertSame(1, Property::withTrashed()->where('mls_id', 4)->count());

        $property = Property::where('mls_id', 4)->firstOrFail();
        $this->assertFalse($property->trashed());
        $this->assertSame(2, $property->agency_id);
        $this->assertSame('7634', $property->mls_public_id);
        $this->assertSame('Invierte En Una Propiedad De Lujo', $property->title);
    }

    public function test_it_clears_sync_checkpoint(): void
    {
        Cache::put('mls_sync_checkpoint', ['offset' => 20], 86400);

        (new TestableMLSSyncService)->clearCheckpoint();

        $this->assertFalse(Cache::has('mls_sync_checkpoint'));
    }
}

class TestableMLSSyncService extends MLSSyncService
{
    public function syncPropertyForTest(array $propertyData): bool
    {
        return $this->syncProperty($propertyData);
    }

    public function fetchPropertyDetail(string $mlsId): ?array
    {
        return null;
    }
}
