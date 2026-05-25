<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\MediaAsset;
use App\Models\Property;
use App\Services\EasyBrokerMlsExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EasyBrokerMlsExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_includes_external_and_local_media_asset_images_without_http_preflight(): void
    {
        Http::fake([
            '*' => Http::response('', 404),
        ]);

        $property = $this->makeExportableProperty();

        $localUrl = 'https://cdn.sanmiguelproperties.com/storage/mls/local-photo.jpg';
        $externalUrl = 'https://ampisanmigueldeallende.com/storage/properties/external-photo.jpeg';

        $localAsset = MediaAsset::create([
            'type' => 'image',
            'provider' => 'local',
            'url' => $localUrl,
            'name' => 'Local photo',
        ]);

        $externalAsset = MediaAsset::create([
            'type' => 'image',
            'provider' => 'mls',
            'url' => $externalUrl,
            'storage_path' => null,
            'name' => 'External MLS photo',
        ]);

        $property->update(['cover_media_asset_id' => $localAsset->id]);
        $property->mediaAssets()->attach($localAsset->id, [
            'role' => 'image',
            'position' => 0,
            'source_url' => $localUrl,
        ]);
        $property->mediaAssets()->attach($externalAsset->id, [
            'role' => 'image',
            'position' => 1,
            'source_url' => $externalUrl,
        ]);

        $draft = $this->buildDraft($property);

        $this->assertSame(2, $draft['resolved']['images_count']);
        $this->assertSame(
            [$localUrl, $externalUrl],
            array_column($draft['payload']['images'], 'url')
        );
        Http::assertNothingSent();
    }

    public function test_it_includes_cover_media_asset_even_when_it_is_not_attached_to_gallery(): void
    {
        $property = $this->makeExportableProperty();
        $externalUrl = 'https://img.example-cdn.com/property-photo?w=1200&q=85';

        $coverAsset = MediaAsset::create([
            'type' => 'image',
            'provider' => 'external',
            'url' => $externalUrl,
            'storage_path' => null,
            'name' => 'External cover photo',
        ]);

        $property->update(['cover_media_asset_id' => $coverAsset->id]);

        $draft = $this->buildDraft($property);

        $this->assertSame(1, $draft['resolved']['images_count']);
        $this->assertSame($externalUrl, $draft['payload']['images'][0]['url']);
    }

    public function test_it_treats_cover_role_media_assets_as_images(): void
    {
        $property = $this->makeExportableProperty();
        $externalUrl = 'https://cdn.example.com/listings/photo-1.jpg';

        $coverAsset = MediaAsset::create([
            'type' => 'image',
            'provider' => 'external',
            'url' => $externalUrl,
            'storage_path' => null,
            'name' => 'Cover role photo',
        ]);

        $property->mediaAssets()->attach($coverAsset->id, [
            'role' => 'cover',
            'position' => 0,
            'source_url' => $externalUrl,
        ]);

        $draft = $this->buildDraft($property);

        $this->assertSame(1, $draft['resolved']['images_count']);
        $this->assertSame($externalUrl, $draft['payload']['images'][0]['url']);
    }

    public function test_it_supplements_linked_images_with_external_urls_from_raw_payload(): void
    {
        $localUrl = 'https://cdn.sanmiguelproperties.com/storage/mls/local-photo.jpg';
        $externalUrl = 'https://ampisanmigueldeallende.com/storage/properties/raw-external-photo.jpg';

        $property = $this->makeExportableProperty([
            'raw_payload' => [
                'photos' => [
                    $localUrl,
                    [
                        'url' => $externalUrl,
                        'title' => 'Exterior',
                    ],
                ],
            ],
        ]);

        $localAsset = MediaAsset::create([
            'type' => 'image',
            'provider' => 'local',
            'url' => $localUrl,
            'name' => 'Local photo',
        ]);

        $property->update(['cover_media_asset_id' => $localAsset->id]);
        $property->mediaAssets()->attach($localAsset->id, [
            'role' => 'image',
            'position' => 0,
            'source_url' => $localUrl,
        ]);

        $draft = $this->buildDraft($property);

        $this->assertSame(2, $draft['resolved']['images_count']);
        $this->assertSame(
            [$localUrl, $externalUrl],
            array_column($draft['payload']['images'], 'url')
        );
        $this->assertSame('Exterior', $draft['payload']['images'][1]['title']);
    }

    public function test_it_extracts_external_image_urls_from_common_raw_payload_fields(): void
    {
        $externalUrl = 'https://mls-cdn.example.com/photo-token-123?width=1600';

        $property = $this->makeExportableProperty([
            'raw_payload' => [
                'photos' => [
                    [
                        'image_url' => $externalUrl,
                        'title' => 'Primary exterior',
                    ],
                ],
            ],
        ]);

        $draft = $this->buildDraft($property);

        $this->assertSame(1, $draft['resolved']['images_count']);
        $this->assertSame($externalUrl, $draft['payload']['images'][0]['url']);
        $this->assertSame('Primary exterior', $draft['payload']['images'][0]['title']);
    }

    private function buildDraft(Property $property): array
    {
        $property->load(['location', 'operations.currency', 'tags', 'mediaAssets']);

        return (new EasyBrokerMlsExportService)->buildDraftPayload(
            $property,
            null,
            ['House'],
            'not_published'
        );
    }

    private function makeExportableProperty(array $attributes = []): Property
    {
        Agency::firstOrCreate(['id' => 1], ['name' => 'Primary Agency']);

        $property = Property::create(array_merge([
            'agency_id' => 1,
            'source' => Property::SOURCE_MLS,
            'easybroker_public_id' => null,
            'mls_id' => random_int(1000, 9999),
            'mls_public_id' => (string) random_int(1000, 9999),
            'title' => 'Casa MLS de prueba',
            'description' => 'Descripcion suficiente para enviar la propiedad a EasyBroker.',
            'property_type_name' => 'House',
            'published' => true,
            'status' => 'For Sale',
            'category' => 'Residential',
            'raw_payload' => [],
        ], $attributes));

        $property->location()->create([
            'street' => 'Calle Principal 123',
            'city_area' => 'Centro',
            'city' => 'San Miguel de Allende',
            'region' => 'Guanajuato',
        ]);

        $property->operations()->create([
            'operation_type' => 'sale',
            'amount' => 350000,
            'currency_code' => 'USD',
        ]);

        return $property;
    }
}
