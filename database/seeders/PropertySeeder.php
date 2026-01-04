<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Currency;
use App\Models\Feature;
use App\Models\MediaAsset;
use App\Models\Property;
use App\Models\PropertyLocation;
use App\Models\PropertyOperation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Tipos de propiedades disponibles
     */
    protected array $propertyTypes = [
        'Casa',
        'Departamento',
        'Terreno',
        'Local Comercial',
        'Oficina',
        'Bodega',
        'Casa de Campo',
        'Penthouse',
        'Casa de Playa',
        'Edificio',
    ];

    /**
     * Tipos de operación
     */
    protected array $operationTypes = [
        'sale' => 'Venta',
        'rent' => 'Alquiler',
    ];

    /**
     * Features/Amenidades disponibles
     */
    protected array $featuresList = [
        'Piscina',
        'Gimnasio',
        'Estacionamiento',
        'Jardín',
        'Terraza',
        'Ascensor',
        'Seguridad 24/7',
        'Área de BBQ',
        'Sala de Eventos',
        'Zona de Juegos',
        'Cuarto de Servicio',
        'Lavandería',
        'Aire Acondicionado',
        'Calefacción',
        'Vista al Mar',
        'Vista a la Montaña',
        'Acceso a Playa',
        'Cocina Equipada',
        'Walking Closet',
        'Balcón',
        'Depósito',
        'Cisterna',
        'Tanque Elevado',
        'Pozo a Tierra',
        'Gas Natural',
        'Internet de Alta Velocidad',
    ];

    /**
     * Tags disponibles
     */
    protected array $tagsList = [
        'Nuevo',
        'Destacado',
        'Oportunidad',
        'Exclusivo',
        'Inversión',
        'Estreno',
        'Remodelado',
        'Amoblado',
        'Semi-amoblado',
        'Promoción',
        'Urgente',
        'Negociable',
        'Bien Ubicado',
        'Zona Residencial',
        'Zona Comercial',
    ];

    /**
     * Ciudades con sus regiones y coordenadas base
     */
    protected array $locations = [
        [
            'region' => 'Lima',
            'city' => 'Lima',
            'areas' => ['Miraflores', 'San Isidro', 'Surco', 'La Molina', 'San Borja', 'Barranco', 'Jesús María', 'Lince', 'Magdalena', 'Pueblo Libre'],
            'lat_base' => -12.046374,
            'lng_base' => -77.042793,
        ],
        [
            'region' => 'Arequipa',
            'city' => 'Arequipa',
            'areas' => ['Yanahuara', 'Cayma', 'Cerro Colorado', 'José Luis Bustamante y Rivero', 'Sachaca', 'Hunter'],
            'lat_base' => -16.409047,
            'lng_base' => -71.537451,
        ],
        [
            'region' => 'La Libertad',
            'city' => 'Trujillo',
            'areas' => ['La Esperanza', 'El Porvenir', 'Víctor Larco Herrera', 'Huanchaco', 'Moche'],
            'lat_base' => -8.111899,
            'lng_base' => -79.028856,
        ],
        [
            'region' => 'Piura',
            'city' => 'Piura',
            'areas' => ['Castilla', 'Catacaos', 'Veintiséis de Octubre', 'Tambogrande'],
            'lat_base' => -5.194429,
            'lng_base' => -80.632885,
        ],
        [
            'region' => 'Cusco',
            'city' => 'Cusco',
            'areas' => ['San Sebastián', 'San Jerónimo', 'Wanchaq', 'Santiago', 'Saylla'],
            'lat_base' => -13.531950,
            'lng_base' => -71.967461,
        ],
    ];

    /**
     * Calles típicas
     */
    protected array $streets = [
        'Av. Javier Prado',
        'Av. Larco',
        'Av. Arequipa',
        'Calle Las Begonias',
        'Jr. de la Unión',
        'Av. La Marina',
        'Calle Los Pinos',
        'Av. Benavides',
        'Calle Las Orquídeas',
        'Av. El Sol',
        'Jr. Ayacucho',
        'Calle San Martín',
        'Av. Grau',
        'Pasaje Los Rosales',
        'Av. Brasil',
        'Calle Las Camelias',
        'Jr. Cusco',
        'Av. Primavera',
        'Calle Los Laureles',
        'Av. Angamos',
    ];

    /**
     * URLs de imágenes de ejemplo (usando picsum.photos para imágenes aleatorias)
     */
    protected array $sampleImageUrls = [
        'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800',
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
        'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800',
        'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=800',
        'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800',
        'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?w=800',
        'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear la agencia si no existe
        $agency = $this->createAgency();

        // Crear features
        $features = $this->createFeatures();

        // Crear tags
        $tags = $this->createTags();

        // Obtener monedas
        $currencies = Currency::all();
        if ($currencies->isEmpty()) {
            $this->command->warn('No hay monedas en la base de datos. Ejecuta CurrencySeeder primero.');
            return;
        }

        // Crear 20 propiedades
        for ($i = 1; $i <= 20; $i++) {
            $this->createProperty($i, $agency, $features, $tags, $currencies);
        }

        $this->command->info('Se han creado 20 propiedades con todos sus datos relacionados.');
    }

    /**
     * Crear la agencia principal
     */
    protected function createAgency(): Agency
    {
        return Agency::firstOrCreate(
            ['id' => 1],
            [
                'id' => 1,
                'name' => 'San Miguel Properties',
                'account_owner' => 'Administrador Principal',
                'logo_url' => 'https://via.placeholder.com/200x100?text=SMP',
                'phone' => '+51 999 888 777',
                'email' => 'info@sanmiguelproperties.com',
                'raw_payload' => null,
            ]
        );
    }

    /**
     * Crear features/amenidades
     */
    protected function createFeatures(): array
    {
        $features = [];
        foreach ($this->featuresList as $featureName) {
            $features[] = Feature::firstOrCreate(
                ['name' => $featureName, 'locale' => 'es'],
                ['name' => $featureName, 'locale' => 'es']
            );
        }
        return $features;
    }

    /**
     * Crear tags
     */
    protected function createTags(): array
    {
        $tags = [];
        foreach ($this->tagsList as $tagName) {
            $tags[] = Tag::firstOrCreate(
                ['name' => $tagName],
                ['name' => $tagName, 'slug' => Str::slug($tagName)]
            );
        }
        return $tags;
    }

    /**
     * Crear una propiedad completa con todos sus datos relacionados
     */
    protected function createProperty(int $index, Agency $agency, array $features, array $tags, $currencies): Property
    {
        $propertyType = $this->propertyTypes[array_rand($this->propertyTypes)];
        $location = $this->locations[array_rand($this->locations)];
        $cityArea = $location['areas'][array_rand($location['areas'])];

        // Generar datos aleatorios según el tipo de propiedad
        $propertyData = $this->generatePropertyData($propertyType, $index, $cityArea, $location);

        // Crear la propiedad
        $property = Property::create([
            'agency_id' => $agency->id,
            'agent_user_id' => null,
            'easybroker_public_id' => 'EB-' . str_pad($index, 6, '0', STR_PAD_LEFT),
            'easybroker_agent_id' => 'AGENT-' . rand(1000, 9999),
            'published' => rand(0, 10) > 2, // 80% publicadas
            'easybroker_created_at' => now()->subDays(rand(30, 365)),
            'easybroker_updated_at' => now()->subDays(rand(1, 30)),
            'last_synced_at' => now(),
            'title' => $propertyData['title'],
            'description' => $propertyData['description'],
            'url' => 'https://sanmiguelproperties.com/propiedad/' . Str::slug($propertyData['title']) . '-' . $index,
            'ad_type' => null,
            'property_type_name' => $propertyType,
            'bedrooms' => $propertyData['bedrooms'],
            'bathrooms' => $propertyData['bathrooms'],
            'half_bathrooms' => $propertyData['half_bathrooms'],
            'parking_spaces' => $propertyData['parking_spaces'],
            'lot_size' => $propertyData['lot_size'],
            'construction_size' => $propertyData['construction_size'],
            'expenses' => rand(0, 1) ? rand(100, 1000) : null,
            'lot_length' => $propertyData['lot_length'],
            'lot_width' => $propertyData['lot_width'],
            'floors' => $propertyData['floors'],
            'floor' => $propertyData['floor'],
            'age' => $this->getRandomAge(),
            'virtual_tour_url' => rand(0, 1) ? 'https://my.matterport.com/show/?m=' . Str::random(11) : null,
            'cover_media_asset_id' => null,
            'raw_payload' => null,
        ]);

        // Crear ubicación
        $this->createPropertyLocation($property, $location, $cityArea);

        // Crear operaciones (venta y/o alquiler)
        $this->createPropertyOperations($property, $currencies, $propertyType);

        // Crear media assets y asociarlos
        $this->createPropertyMediaAssets($property);

        // Asociar features aleatorios (entre 3 y 10)
        $randomFeatures = collect($features)->random(rand(3, min(10, count($features))));
        $property->features()->attach($randomFeatures->pluck('id'));

        // Asociar tags aleatorios (entre 1 y 4)
        $randomTags = collect($tags)->random(rand(1, min(4, count($tags))));
        $property->tags()->attach($randomTags->pluck('id'));

        return $property;
    }

    /**
     * Generar datos según el tipo de propiedad
     */
    protected function generatePropertyData(string $propertyType, int $index, string $cityArea, array $location): array
    {
        $baseData = [
            'title' => '',
            'description' => '',
            'bedrooms' => null,
            'bathrooms' => null,
            'half_bathrooms' => null,
            'parking_spaces' => null,
            'lot_size' => null,
            'construction_size' => null,
            'lot_length' => null,
            'lot_width' => null,
            'floors' => null,
            'floor' => null,
        ];

        switch ($propertyType) {
            case 'Casa':
            case 'Casa de Campo':
            case 'Casa de Playa':
                $bedrooms = rand(3, 6);
                $baseData = array_merge($baseData, [
                    'title' => $this->generateTitle($propertyType, $bedrooms, $cityArea),
                    'description' => $this->generateDescription($propertyType, $bedrooms, $cityArea, $location),
                    'bedrooms' => $bedrooms,
                    'bathrooms' => rand(2, 4),
                    'half_bathrooms' => rand(0, 2),
                    'parking_spaces' => rand(1, 4),
                    'lot_size' => rand(150, 500),
                    'construction_size' => rand(120, 400),
                    'lot_length' => rand(15, 30),
                    'lot_width' => rand(10, 20),
                    'floors' => rand(1, 3),
                    'floor' => null,
                ]);
                break;

            case 'Departamento':
                $bedrooms = rand(1, 4);
                $baseData = array_merge($baseData, [
                    'title' => $this->generateTitle($propertyType, $bedrooms, $cityArea),
                    'description' => $this->generateDescription($propertyType, $bedrooms, $cityArea, $location),
                    'bedrooms' => $bedrooms,
                    'bathrooms' => rand(1, 3),
                    'half_bathrooms' => rand(0, 1),
                    'parking_spaces' => rand(1, 2),
                    'lot_size' => null,
                    'construction_size' => rand(60, 200),
                    'lot_length' => null,
                    'lot_width' => null,
                    'floors' => null,
                    'floor' => (string)rand(1, 20),
                ]);
                break;

            case 'Penthouse':
                $bedrooms = rand(3, 5);
                $baseData = array_merge($baseData, [
                    'title' => $this->generateTitle($propertyType, $bedrooms, $cityArea),
                    'description' => $this->generateDescription($propertyType, $bedrooms, $cityArea, $location),
                    'bedrooms' => $bedrooms,
                    'bathrooms' => rand(3, 5),
                    'half_bathrooms' => rand(1, 2),
                    'parking_spaces' => rand(2, 4),
                    'lot_size' => null,
                    'construction_size' => rand(200, 500),
                    'lot_length' => null,
                    'lot_width' => null,
                    'floors' => rand(1, 2),
                    'floor' => 'Último',
                ]);
                break;

            case 'Terreno':
                $baseData = array_merge($baseData, [
                    'title' => "Terreno en {$cityArea} - Excelente ubicación",
                    'description' => $this->generateDescription($propertyType, null, $cityArea, $location),
                    'lot_size' => rand(200, 2000),
                    'lot_length' => rand(20, 50),
                    'lot_width' => rand(10, 40),
                ]);
                break;

            case 'Local Comercial':
                $baseData = array_merge($baseData, [
                    'title' => "Local Comercial en {$cityArea} - Zona comercial",
                    'description' => $this->generateDescription($propertyType, null, $cityArea, $location),
                    'bathrooms' => rand(1, 3),
                    'half_bathrooms' => rand(0, 1),
                    'parking_spaces' => rand(0, 5),
                    'construction_size' => rand(50, 500),
                    'floors' => rand(1, 3),
                    'floor' => rand(0, 1) ? (string)rand(1, 5) : null,
                ]);
                break;

            case 'Oficina':
                $baseData = array_merge($baseData, [
                    'title' => "Oficina Premium en {$cityArea}",
                    'description' => $this->generateDescription($propertyType, null, $cityArea, $location),
                    'bathrooms' => rand(1, 2),
                    'half_bathrooms' => rand(0, 1),
                    'parking_spaces' => rand(1, 3),
                    'construction_size' => rand(30, 300),
                    'floor' => (string)rand(1, 15),
                ]);
                break;

            case 'Bodega':
                $baseData = array_merge($baseData, [
                    'title' => "Bodega Industrial en {$cityArea}",
                    'description' => $this->generateDescription($propertyType, null, $cityArea, $location),
                    'bathrooms' => rand(1, 2),
                    'parking_spaces' => rand(2, 10),
                    'lot_size' => rand(500, 5000),
                    'construction_size' => rand(400, 4000),
                ]);
                break;

            case 'Edificio':
                $baseData = array_merge($baseData, [
                    'title' => "Edificio en {$cityArea} - Oportunidad de Inversión",
                    'description' => $this->generateDescription($propertyType, null, $cityArea, $location),
                    'bathrooms' => rand(10, 30),
                    'parking_spaces' => rand(10, 50),
                    'lot_size' => rand(300, 1000),
                    'construction_size' => rand(1000, 5000),
                    'floors' => rand(3, 10),
                ]);
                break;
        }

        return $baseData;
    }

    /**
     * Generar título de propiedad
     */
    protected function generateTitle(string $propertyType, ?int $bedrooms, string $cityArea): string
    {
        $adjectives = ['Hermoso', 'Amplio', 'Moderno', 'Elegante', 'Acogedor', 'Espacioso', 'Luminoso', 'Exclusivo'];
        $adjective = $adjectives[array_rand($adjectives)];

        if ($bedrooms) {
            return "{$adjective} {$propertyType} de {$bedrooms} dormitorios en {$cityArea}";
        }

        return "{$adjective} {$propertyType} en {$cityArea}";
    }

    /**
     * Generar descripción de propiedad
     */
    protected function generateDescription(string $propertyType, ?int $bedrooms, string $cityArea, array $location): string
    {
        $descriptions = [
            "Excelente {$propertyType} ubicado en una de las mejores zonas de {$cityArea}, {$location['city']}. ",
            "Magnífica oportunidad en {$cityArea}, {$location['city']}. {$propertyType} con acabados de primera. ",
            "Se ofrece {$propertyType} en privilegiada ubicación de {$cityArea}, {$location['region']}. ",
        ];

        $description = $descriptions[array_rand($descriptions)];

        if ($bedrooms) {
            $description .= "Cuenta con {$bedrooms} dormitorios amplios y bien iluminados. ";
        }

        $extras = [
            "Acabados de lujo y materiales de primera calidad. ",
            "Zona tranquila y segura, ideal para familias. ",
            "Cerca de centros comerciales, colegios y parques. ",
            "Excelente ventilación e iluminación natural. ",
            "Diseño moderno y funcional. ",
            "Áreas verdes y espacios de recreación. ",
            "Fácil acceso a vías principales. ",
            "Entrega inmediata. ",
        ];

        // Agregar 2-4 extras aleatorios
        $selectedExtras = array_rand($extras, rand(2, 4));
        if (!is_array($selectedExtras)) {
            $selectedExtras = [$selectedExtras];
        }

        foreach ($selectedExtras as $index) {
            $description .= $extras[$index];
        }

        $description .= "\n\n¡No pierda esta oportunidad! Contáctenos para más información y agendar una visita.";

        return $description;
    }

    /**
     * Crear ubicación de la propiedad
     */
    protected function createPropertyLocation(Property $property, array $location, string $cityArea): PropertyLocation
    {
        // Agregar variación aleatoria a las coordenadas base
        $latVariation = (rand(-1000, 1000) / 10000);
        $lngVariation = (rand(-1000, 1000) / 10000);

        $street = $this->streets[array_rand($this->streets)];
        $streetNumber = rand(100, 9999);

        return PropertyLocation::create([
            'property_id' => $property->id,
            'region' => $location['region'],
            'city' => $location['city'],
            'city_area' => $cityArea,
            'street' => "{$street} {$streetNumber}",
            'postal_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
            'show_exact_location' => rand(0, 1) ? true : false,
            'latitude' => $location['lat_base'] + $latVariation,
            'longitude' => $location['lng_base'] + $lngVariation,
            'raw_payload' => null,
        ]);
    }

    /**
     * Crear operaciones de la propiedad (venta/alquiler)
     */
    protected function createPropertyOperations(Property $property, $currencies, string $propertyType): void
    {
        // Determinar qué operaciones tendrá la propiedad
        $hasRent = rand(0, 1);
        $hasSale = rand(0, 1) || !$hasRent; // Al menos una operación

        // Obtener moneda USD o PEN
        $usdCurrency = $currencies->firstWhere('code', 'USD');
        $penCurrency = $currencies->firstWhere('code', 'PEN');

        $currency = rand(0, 1) ? $usdCurrency : $penCurrency;
        if (!$currency) {
            $currency = $currencies->first();
        }

        if ($hasSale) {
            $salePrice = $this->generateSalePrice($propertyType);
            $formattedAmount = $currency->symbol . ' ' . number_format($salePrice, 0, '.', ',');

            PropertyOperation::create([
                'property_id' => $property->id,
                'operation_type' => 'sale',
                'amount' => $salePrice,
                'currency_id' => $currency->id,
                'currency_code' => $currency->code,
                'formatted_amount' => $formattedAmount,
                'unit' => null,
                'raw_payload' => null,
            ]);
        }

        if ($hasRent) {
            $rentPrice = $this->generateRentPrice($propertyType);
            $formattedAmount = $currency->symbol . ' ' . number_format($rentPrice, 0, '.', ',');

            PropertyOperation::create([
                'property_id' => $property->id,
                'operation_type' => 'rent',
                'amount' => $rentPrice,
                'currency_id' => $currency->id,
                'currency_code' => $currency->code,
                'formatted_amount' => $formattedAmount,
                'unit' => 'monthly',
                'raw_payload' => null,
            ]);
        }
    }

    /**
     * Generar precio de venta según tipo de propiedad
     */
    protected function generateSalePrice(string $propertyType): int
    {
        $ranges = [
            'Casa' => [150000, 800000],
            'Casa de Campo' => [200000, 1000000],
            'Casa de Playa' => [250000, 1500000],
            'Departamento' => [80000, 500000],
            'Penthouse' => [300000, 2000000],
            'Terreno' => [50000, 500000],
            'Local Comercial' => [100000, 1000000],
            'Oficina' => [80000, 600000],
            'Bodega' => [200000, 2000000],
            'Edificio' => [1000000, 10000000],
        ];

        $range = $ranges[$propertyType] ?? [100000, 500000];

        // Generar precio redondeado a miles
        return round(rand($range[0], $range[1]) / 1000) * 1000;
    }

    /**
     * Generar precio de alquiler según tipo de propiedad
     */
    protected function generateRentPrice(string $propertyType): int
    {
        $ranges = [
            'Casa' => [800, 5000],
            'Casa de Campo' => [1000, 6000],
            'Casa de Playa' => [1500, 8000],
            'Departamento' => [500, 3000],
            'Penthouse' => [2000, 10000],
            'Terreno' => [300, 2000],
            'Local Comercial' => [1000, 8000],
            'Oficina' => [500, 4000],
            'Bodega' => [1500, 10000],
            'Edificio' => [10000, 50000],
        ];

        $range = $ranges[$propertyType] ?? [500, 3000];

        // Generar precio redondeado a 50
        return round(rand($range[0], $range[1]) / 50) * 50;
    }

    /**
     * Crear media assets para la propiedad
     */
    protected function createPropertyMediaAssets(Property $property): void
    {
        // Crear entre 3 y 8 imágenes por propiedad
        $imageCount = rand(3, 8);

        for ($i = 0; $i < $imageCount; $i++) {
            $imageUrl = $this->sampleImageUrls[array_rand($this->sampleImageUrls)];

            // Crear el media asset
            $mediaAsset = MediaAsset::create([
                'type' => 'image',
                'provider' => 'external',
                'url' => $imageUrl . '&random=' . Str::random(8),
                'storage_path' => null,
                'mime_type' => 'image/jpeg',
                'size_bytes' => rand(100000, 500000),
                'duration_seconds' => null,
                'created_at' => now(),
                'name' => "Imagen {$property->id}-" . ($i + 1),
                'alt' => "Imagen de {$property->title}",
            ]);

            // Asociar con la propiedad
            $property->mediaAssets()->attach($mediaAsset->id, [
                'role' => $i === 0 ? 'cover' : 'image',
                'title' => $i === 0 ? 'Imagen Principal' : "Vista " . ($i + 1),
                'position' => $i,
                'checksum' => md5($imageUrl . $i),
                'source_url' => $imageUrl,
                'raw_payload' => null,
            ]);

            // Actualizar la imagen de portada si es la primera
            if ($i === 0) {
                $property->update(['cover_media_asset_id' => $mediaAsset->id]);
            }
        }
    }

    /**
     * Obtener edad aleatoria de la propiedad
     */
    protected function getRandomAge(): ?string
    {
        $ages = [
            'Estreno',
            'A estrenar',
            '1 año',
            '2 años',
            '3 años',
            '5 años',
            '10 años',
            '15 años',
            '20 años',
            'Más de 20 años',
            null,
        ];

        return $ages[array_rand($ages)];
    }
}
