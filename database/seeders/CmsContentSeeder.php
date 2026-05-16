<?php

namespace Database\Seeders;

use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsSiteSetting;
use Illuminate\Database\Seeder;

/**
 * Seeder maestro del CMS.
 * Crea las p�f¡ginas, field groups, definiciones, valores,
 * men�fºs, items y settings con el contenido actual del sitio.
 *
 * Ejecutar: php artisan db:seed --class=CmsContentSeeder
 */
class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Sembrando contenido CMS...');

        $this->seedPages();
        $this->seedHomeFieldGroups();
        $this->seedAboutFieldGroups();
        $this->seedContactFieldGroups();
        $this->seedPublicFrontendFieldGroups();
        $this->seedMenus();
        $this->seedSiteSettings();

        $this->command->info('Contenido CMS sembrado correctamente.');
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // P�fGINAS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedPages(): void
    {
        $this->command->info('  Creando paginas...');

        CmsPage::updateOrCreate(['slug' => 'home'], [
            'title_es' => 'Inicio',
            'title_en' => 'Home',
            'meta_title_es' => 'San Miguel Properties - Encuentra tu hogar ideal',
            'meta_title_en' => 'San Miguel Properties - Find your dream home',
            'meta_description_es' => 'Casas, departamentos y terrenos en las mejores ubicaciones. Tu pr�f³xima inversi�f³n inmobiliaria est�f¡ a un clic de distancia.',
            'meta_description_en' => 'Houses, apartments and land in the best locations. Your next real estate investment is just a click away.',
            'template' => 'home',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        CmsPage::updateOrCreate(['slug' => 'about'], [
            'title_es' => 'Nosotros',
            'title_en' => 'About Us',
            'meta_title_es' => 'Nosotros - San Miguel Properties',
            'meta_title_en' => 'About Us - San Miguel Properties',
            'meta_description_es' => 'Conoce al equipo de San Miguel Properties. +15 a�f±os de experiencia en el mercado inmobiliario.',
            'meta_description_en' => 'Meet the San Miguel Properties team. 15+ years of experience in the real estate market.',
            'template' => 'about',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        CmsPage::updateOrCreate(['slug' => 'contact'], [
            'title_es' => 'Contacto',
            'title_en' => 'Contact',
            'meta_title_es' => 'Contacto - San Miguel Properties',
            'meta_title_en' => 'Contact - San Miguel Properties',
            'meta_description_es' => 'Cont�f¡ctanos para encontrar tu propiedad ideal. Estamos aqu�f­ para ayudarte.',
            'meta_description_en' => 'Contact us to find your ideal property. We are here to help you.',
            'template' => 'contact',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        CmsPage::updateOrCreate(['slug' => 'properties'], [
            'title_es' => 'Propiedades',
            'title_en' => 'Properties',
            'meta_title_es' => 'Propiedades - San Miguel Properties',
            'meta_title_en' => 'Properties - San Miguel Properties',
            'meta_description_es' => 'Explora propiedades en venta y renta en San Miguel de Allende.',
            'meta_description_en' => 'Browse properties for sale and rent in San Miguel de Allende.',
            'template' => 'public.properties-index',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        CmsPage::updateOrCreate(['slug' => 'property-detail'], [
            'title_es' => 'Detalle de Propiedad',
            'title_en' => 'Property Detail',
            'meta_title_es' => 'Detalle de Propiedad - San Miguel Properties',
            'meta_title_en' => 'Property Detail - San Miguel Properties',
            'meta_description_es' => 'Consulta fotos, ubicaciÃ³n, caracterÃ­sticas y contacto de cada propiedad.',
            'meta_description_en' => 'Review photos, location, features and contact details for each property.',
            'template' => 'public.property-detail',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        CmsPage::updateOrCreate(['slug' => 'mls-offices'], [
            'title_es' => 'Agencias',
            'title_en' => 'Agencies',
            'meta_title_es' => 'Agencias - San Miguel Properties',
            'meta_title_en' => 'Agencies - San Miguel Properties',
            'meta_description_es' => 'Conoce las agencias MLS y su inventario de propiedades.',
            'meta_description_en' => 'Discover MLS agencies and their property inventory.',
            'template' => 'public.mls-offices-index',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 6,
        ]);

        CmsPage::updateOrCreate(['slug' => 'mls-office-detail'], [
            'title_es' => 'Detalle de Agencia',
            'title_en' => 'Agency Detail',
            'meta_title_es' => 'Detalle de Agencia - San Miguel Properties',
            'meta_title_en' => 'Agency Detail - San Miguel Properties',
            'meta_description_es' => 'Ficha completa de la agencia con sus agentes y propiedades.',
            'meta_description_en' => 'Complete agency profile with its agents and properties.',
            'template' => 'public.mls-office-detail',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 7,
        ]);

        CmsPage::updateOrCreate(['slug' => 'mls-agents'], [
            'title_es' => 'Agentes',
            'title_en' => 'Agents',
            'meta_title_es' => 'Agentes - San Miguel Properties',
            'meta_title_en' => 'Agents - San Miguel Properties',
            'meta_description_es' => 'Explora agentes MLS y sus propiedades activas.',
            'meta_description_en' => 'Browse MLS agents and their active properties.',
            'template' => 'public.mls-agents-index',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 8,
        ]);

        CmsPage::updateOrCreate(['slug' => 'mls-agent-detail'], [
            'title_es' => 'Detalle de Agente',
            'title_en' => 'Agent Detail',
            'meta_title_es' => 'Detalle de Agente - San Miguel Properties',
            'meta_title_en' => 'Agent Detail - San Miguel Properties',
            'meta_description_es' => 'Perfil del agente, contacto y propiedades vinculadas.',
            'meta_description_en' => 'Agent profile, contact data and linked properties.',
            'template' => 'public.mls-agent-detail',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 9,
        ]);
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // HOME - FIELD GROUPS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedHomeFieldGroups(): void
    {
        $this->command->info('  Creando campos del Home...');
        $page = CmsPage::where('slug', 'home')->first();

        // â�?��,�â�?��,� Hero Section â�?��,�â�?��,�
        $heroGroup = $this->createFieldGroup('home-hero', 'Hero Section', 'page', 'home', 1);
        $this->createFieldsAndValues($heroGroup, $page, 'page', [
            ['field_key' => 'hero_badge_text', 'type' => 'text', 'label_es' => 'Texto del badge', 'value_es' => '+500 propiedades disponibles', 'value_en' => '500+ properties available'],
            ['field_key' => 'hero_title_line1', 'type' => 'text', 'label_es' => 'T�f­tulo l�f­nea 1', 'value_es' => 'Encuentra tu', 'value_en' => 'Find your'],
            ['field_key' => 'hero_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => 'hogar ideal', 'value_en' => 'dream home'],
            ['field_key' => 'hero_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Casas, departamentos y terrenos en las mejores ubicaciones. Tu pr�f³xima inversi�f³n inmobiliaria est�f¡ a un clic de distancia.', 'value_en' => 'Houses, apartments and land in the best locations. Your next real estate investment is just a click away.'],
            ['field_key' => 'hero_search_placeholder', 'type' => 'text', 'label_es' => 'Placeholder buscador', 'value_es' => 'Buscar por ubicaci�f³n, tipo o caracter�f­sticas...', 'value_en' => 'Search by location, type or features...'],
            ['field_key' => 'hero_search_button', 'type' => 'text', 'label_es' => 'Texto bot�f³n buscar', 'value_es' => 'Buscar', 'value_en' => 'Search'],
            ['field_key' => 'hero_scroll_text', 'type' => 'text', 'label_es' => 'Texto scroll', 'value_es' => 'Descubre m�f¡s', 'value_en' => 'Discover more'],
        ]);

        // â�?��,�â�?��,� Stats Bar â�?��,�â�?��,�
        $statsGroup = $this->createFieldGroup('home-stats', 'Stats Bar', 'page', 'home', 2);
        $statsRepeater = $this->createRepeaterAndRows($statsGroup, $page, 'page', 'stats_items', 'Estad�f­sticas', [
            ['stat_number' => ['es' => '500+', 'en' => '500+'], 'stat_label' => ['es' => 'Propiedades', 'en' => 'Properties']],
            ['stat_number' => ['es' => '15+', 'en' => '15+'], 'stat_label' => ['es' => 'A�f±os de experiencia', 'en' => 'Years of experience']],
            ['stat_number' => ['es' => '1000+', 'en' => '1000+'], 'stat_label' => ['es' => 'Clientes felices', 'en' => 'Happy clients']],
            ['stat_number' => ['es' => '50+', 'en' => '50+'], 'stat_label' => ['es' => 'Zonas cubiertas', 'en' => 'Covered areas']],
        ], [
            ['field_key' => 'stat_number', 'type' => 'text', 'label_es' => 'N�fºmero'],
            ['field_key' => 'stat_label', 'type' => 'text', 'label_es' => 'Etiqueta'],
        ]);

        // â�?��,�â�?��,� Servicios â�?��,�â�?��,�
        $this->createFieldsAndValues($statsGroup, $page, 'page', [
            ['field_key' => 'stats_happy_clients_number', 'type' => 'text', 'label_es' => 'Clientes felices', 'label_en' => 'Happy clients', 'value_es' => '1000+', 'value_en' => '1000+'],
        ]);

        $servicesGroup = $this->createFieldGroup('home-services', 'Servicios', 'page', 'home', 3);
        $this->createFieldsAndValues($servicesGroup, $page, 'page', [
            ['field_key' => 'services_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Nuestros Servicios', 'value_en' => 'Our Services'],
            ['field_key' => 'services_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => '�,¿Por qu�f© elegirnos?', 'value_en' => 'Why choose us?'],
            ['field_key' => 'services_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Ofrecemos una experiencia inmobiliaria completa con tecnolog�f­a de vanguardia y un equipo de expertos dedicados a ti.', 'value_en' => 'We offer a complete real estate experience with cutting-edge technology and a team of experts dedicated to you.'],
            ['field_key' => 'services_feature1_icon', 'type' => 'image', 'label_es' => 'Icono - Busqueda inteligente'],
            ['field_key' => 'services_feature1_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - Busqueda inteligente', 'value_es' => '#D1A054', 'is_translatable' => false],
            ['field_key' => 'services_feature2_icon', 'type' => 'image', 'label_es' => 'Icono - Transacciones seguras'],
            ['field_key' => 'services_feature2_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - Transacciones seguras', 'value_es' => '#768D59', 'is_translatable' => false],
            ['field_key' => 'services_feature3_icon', 'type' => 'image', 'label_es' => 'Icono - Tours virtuales'],
            ['field_key' => 'services_feature3_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - Tours virtuales', 'value_es' => '#A52A2A', 'is_translatable' => false],
            ['field_key' => 'services_feature4_icon', 'type' => 'image', 'label_es' => 'Icono - Asesores expertos'],
            ['field_key' => 'services_feature4_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - Asesores expertos', 'value_es' => '#5B5B5B', 'is_translatable' => false],
            ['field_key' => 'services_feature5_icon', 'type' => 'image', 'label_es' => 'Icono - Financiamiento flexible'],
            ['field_key' => 'services_feature5_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - Financiamiento flexible', 'value_es' => '#A52A2A', 'is_translatable' => false],
            ['field_key' => 'services_feature6_icon', 'type' => 'image', 'label_es' => 'Icono - App movil'],
            ['field_key' => 'services_feature6_icon_bg_color', 'type' => 'color', 'label_es' => 'Color fondo icono - App movil', 'value_es' => '#768D59', 'is_translatable' => false],
        ]);
        $this->createRepeaterAndRows($servicesGroup, $page, 'page', 'services_items', 'Servicios', [
            ['service_title' => ['es' => 'B�fºsqueda Inteligente', 'en' => 'Smart Search'], 'service_description' => ['es' => 'Filtros avanzados y b�fºsqueda por mapa para encontrar exactamente lo que necesitas en segundos.', 'en' => 'Advanced filters and map search to find exactly what you need in seconds.']],
            ['service_title' => ['es' => 'Transacciones Seguras', 'en' => 'Secure Transactions'], 'service_description' => ['es' => 'Proceso de compra transparente con asesor�f­a legal incluida y documentaci�f³n verificada.', 'en' => 'Transparent purchase process with legal advice included and verified documentation.']],
            ['service_title' => ['es' => 'Tours Virtuales 360Ã‚Â°', 'en' => 'Virtual Tours 360Ã‚Â°'], 'service_description' => ['es' => 'Recorre las propiedades desde la comodidad de tu hogar con nuestros tours virtuales inmersivos.', 'en' => 'Tour properties from the comfort of your home with our immersive virtual tours.']],
            ['service_title' => ['es' => 'Asesores Expertos', 'en' => 'Expert Advisors'], 'service_description' => ['es' => 'Un equipo de profesionales certificados te acompa�f±a en cada paso del proceso.', 'en' => 'A team of certified professionals accompanies you every step of the way.']],
            ['service_title' => ['es' => 'Financiamiento Flexible', 'en' => 'Flexible Financing'], 'service_description' => ['es' => 'Opciones de cr�f©dito con las mejores tasas del mercado y planes a tu medida.', 'en' => 'Credit options with the best market rates and custom plans.']],
            ['service_title' => ['es' => 'App M�f³vil', 'en' => 'Mobile App'], 'service_description' => ['es' => 'Gestiona tus favoritos, agenda visitas y recibe alertas desde cualquier lugar.', 'en' => 'Manage your favorites, schedule visits and receive alerts from anywhere.']],
        ], [
            ['field_key' => 'service_title', 'type' => 'text', 'label_es' => 'T�f­tulo del servicio'],
            ['field_key' => 'service_description', 'type' => 'textarea', 'label_es' => 'Descripci�f³n del servicio'],
        ]);

        // â�?��,�â�?��,� CTA Venta â�?��,�â�?��,�
        $ctaSaleGroup = $this->createFieldGroup('home-cta-sale', 'CTA Propiedades en Venta', 'page', 'home', 4);
        $this->createFieldsAndValues($ctaSaleGroup, $page, 'page', [
            ['field_key' => 'cta_sale_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Propiedades en Venta', 'value_en' => 'Properties for Sale'],
            ['field_key' => 'cta_sale_title_line1', 'type' => 'text', 'label_es' => 'T�f­tulo l�f­nea 1', 'value_es' => 'Tu pr�f³xima', 'value_en' => 'Your next'],
            ['field_key' => 'cta_sale_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => 'inversi�f³n', 'value_en' => 'investment'],
            ['field_key' => 'cta_sale_title_line2', 'type' => 'text', 'label_es' => 'T�f­tulo l�f­nea 2', 'value_es' => 'te espera', 'value_en' => 'awaits you'],
            ['field_key' => 'cta_sale_description', 'type' => 'textarea', 'label_es' => 'Descripci�f³n', 'value_es' => 'Descubre nuestra selecci�f³n exclusiva de propiedades en venta. Desde acogedores departamentos hasta lujosas residencias, encontrar�f¡s opciones para todos los presupuestos.', 'value_en' => 'Discover our exclusive selection of properties for sale. From cozy apartments to luxurious residences, you\'ll find options for all budgets.'],
            ['field_key' => 'cta_sale_button_text', 'type' => 'text', 'label_es' => 'Texto bot�f³n', 'value_es' => 'Ver propiedades en venta', 'value_en' => 'View properties for sale'],
            ['field_key' => 'cta_sale_button_url', 'type' => 'url', 'label_es' => 'URL bot�f³n', 'value_es' => '/propiedades?operation_type=sale', 'is_translatable' => false],
        ]);

        // â�?��,�â�?��,� CTA Renta â�?��,�â�?��,�
        $ctaRentGroup = $this->createFieldGroup('home-cta-rent', 'CTA Propiedades en Renta', 'page', 'home', 5);
        $this->createFieldsAndValues($ctaRentGroup, $page, 'page', [
            ['field_key' => 'cta_rent_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Propiedades en Renta', 'value_en' => 'Properties for Rent'],
            ['field_key' => 'cta_rent_title_line1', 'type' => 'text', 'label_es' => 'T�f­tulo l�f­nea 1', 'value_es' => 'Renta sin', 'value_en' => 'Rent without'],
            ['field_key' => 'cta_rent_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => 'complicaciones', 'value_en' => 'complications'],
            ['field_key' => 'cta_rent_description', 'type' => 'textarea', 'label_es' => 'Descripci�f³n', 'value_es' => 'Encuentra el espacio perfecto para tu pr�f³xima aventura. Contratos flexibles, propiedades verificadas y mudanza express disponible.', 'value_en' => 'Find the perfect space for your next adventure. Flexible contracts, verified properties and express moving available.'],
            ['field_key' => 'cta_rent_button_text', 'type' => 'text', 'label_es' => 'Texto bot�f³n', 'value_es' => 'Ver propiedades en renta', 'value_en' => 'View properties for rent'],
            ['field_key' => 'cta_rent_button_url', 'type' => 'url', 'label_es' => 'URL bot�f³n', 'value_es' => '/propiedades?operation_type=rental', 'is_translatable' => false],
        ]);

        // â�?��,�â�?��,� Proceso â�?��,�â�?��,�
        $processGroup = $this->createFieldGroup('home-process', 'Proceso de Compra', 'page', 'home', 6);
        $this->createFieldsAndValues($processGroup, $page, 'page', [
            ['field_key' => 'process_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Proceso Simplificado', 'value_en' => 'Simplified Process'],
            ['field_key' => 'process_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Tu nuevo hogar en', 'value_en' => 'Your new home in'],
            ['field_key' => 'process_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => '4 simples pasos', 'value_en' => '4 simple steps'],
            ['field_key' => 'process_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Hemos simplificado el proceso inmobiliario para que puedas enfocarte en lo que realmente importa.', 'value_en' => 'We\'ve simplified the real estate process so you can focus on what really matters.'],
        ]);
        $this->createRepeaterAndRows($processGroup, $page, 'page', 'process_steps', 'Pasos del proceso', [
            ['step_title' => ['es' => 'Explora', 'en' => 'Explore'], 'step_description' => ['es' => 'Navega por nuestro cat�f¡logo y usa los filtros para encontrar propiedades que te interesen.', 'en' => 'Browse our catalog and use filters to find properties that interest you.']],
            ['step_title' => ['es' => 'Agenda', 'en' => 'Schedule'], 'step_description' => ['es' => 'Programa una visita presencial o virtual con uno de nuestros asesores expertos.', 'en' => 'Schedule an in-person or virtual visit with one of our expert advisors.']],
            ['step_title' => ['es' => 'Negocia', 'en' => 'Negotiate'], 'step_description' => ['es' => 'Te ayudamos a negociar el mejor precio y condiciones para tu compra o renta.', 'en' => 'We help you negotiate the best price and conditions for your purchase or rental.']],
            ['step_title' => ['es' => '�,¡Listo!', 'en' => 'Done!'], 'step_description' => ['es' => 'Firma, recibe las llaves y disfruta de tu nuevo hogar. �,¡As�f­ de f�f¡cil!', 'en' => 'Sign, receive the keys and enjoy your new home. That easy!']],
        ], [
            ['field_key' => 'step_title', 'type' => 'text', 'label_es' => 'T�f­tulo del paso'],
            ['field_key' => 'step_description', 'type' => 'textarea', 'label_es' => 'Descripci�f³n del paso'],
        ]);

        // â�?��,�â�?��,� Testimonios â�?��,�â�?��,�
        $testimonialsGroup = $this->createFieldGroup('home-testimonials', 'Testimonios', 'page', 'home', 7);
        $this->createFieldsAndValues($testimonialsGroup, $page, 'page', [
            ['field_key' => 'testimonials_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Lo que dicen nuestros clientes', 'value_en' => 'What our clients say'],
            ['field_key' => 'testimonials_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Historias de �f©xito', 'value_en' => 'Success stories'],
            ['field_key' => 'testimonials_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Cientos de familias han encontrado su hogar ideal con nosotros.', 'value_en' => 'Hundreds of families have found their ideal home with us.'],
        ]);
        $this->createRepeaterAndRows($testimonialsGroup, $page, 'page', 'testimonials_items', 'Testimonios', [
            [
                'testimonial_text' => ['es' => 'El proceso fue incre�f­blemente sencillo. En menos de un mes encontr�f© la casa perfecta para mi familia. El equipo de San Miguel fue excepcional.', 'en' => 'The process was incredibly simple. In less than a month I found the perfect house for my family. The San Miguel team was exceptional.'],
                'testimonial_name' => ['es' => 'Mar�f­a Garc�f­a', 'en' => 'Mar�f­a Garc�f­a'],
                'testimonial_role' => ['es' => 'Compradora - Polanco', 'en' => 'Buyer - Polanco'],
                'testimonial_rating' => ['es' => '5', 'en' => '5'],
            ],
            [
                'testimonial_text' => ['es' => 'Como inversionista, valoro la transparencia. San Miguel me brind�f³ toda la informaci�f³n que necesitaba para tomar la mejor decisi�f³n.', 'en' => 'As an investor, I value transparency. San Miguel provided all the information I needed to make the best decision.'],
                'testimonial_name' => ['es' => 'Carlos Rodr�f­guez', 'en' => 'Carlos Rodr�f­guez'],
                'testimonial_role' => ['es' => 'Inversionista - Santa Fe', 'en' => 'Investor - Santa Fe'],
                'testimonial_rating' => ['es' => '5', 'en' => '5'],
            ],
            [
                'testimonial_text' => ['es' => 'Rentar mi departamento fue s�fºper f�f¡cil. Sin aval, contrato flexible y el equipo siempre disponible para resolver mis dudas.', 'en' => 'Renting my apartment was super easy. No guarantor, flexible contract and the team always available to answer my questions.'],
                'testimonial_name' => ['es' => 'Ana L�f³pez', 'en' => 'Ana L�f³pez'],
                'testimonial_role' => ['es' => 'Arrendataria - Condesa', 'en' => 'Renter - Condesa'],
                'testimonial_rating' => ['es' => '5', 'en' => '5'],
            ],
        ], [
            ['field_key' => 'testimonial_text', 'type' => 'textarea', 'label_es' => 'Texto del testimonio'],
            ['field_key' => 'testimonial_name', 'type' => 'text', 'label_es' => 'Nombre', 'is_translatable' => false],
            ['field_key' => 'testimonial_role', 'type' => 'text', 'label_es' => 'Rol / ubicaci�f³n'],
            ['field_key' => 'testimonial_rating', 'type' => 'number', 'label_es' => 'Calificaci�f³n (1-5)'],
        ]);

        // â�?��,�â�?��,� About (secci�f³n del home) â�?��,�â�?��,�
        $aboutGroup = $this->createFieldGroup('home-about', 'Sobre Nosotros (Home)', 'page', 'home', 8);
        $this->createFieldsAndValues($aboutGroup, $page, 'page', [
            ['field_key' => 'home_about_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Sobre Nosotros', 'value_en' => 'About Us'],
            ['field_key' => 'home_about_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'M�f¡s que una inmobiliaria, somos tu', 'value_en' => 'More than a real estate agency, we are your'],
            ['field_key' => 'home_about_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => 'aliado', 'value_en' => 'ally'],
            ['field_key' => 'home_about_text', 'type' => 'textarea', 'label_es' => 'Descripci�f³n', 'value_es' => 'Desde 2009, San Miguel Properties ha sido el puente entre familias y sus hogares so�f±ados. Con un enfoque centrado en el cliente y tecnolog�f­a de vanguardia, hemos transformado la experiencia inmobiliaria en M�f©xico.', 'value_en' => 'Since 2009, San Miguel Properties has been the bridge between families and their dream homes. With a client-focused approach and cutting-edge technology, we have transformed the real estate experience in Mexico.'],
            ['field_key' => 'home_about_satisfaction', 'type' => 'text', 'label_es' => '% Satisfacci�f³n', 'value_es' => '98%', 'value_en' => '98%'],
        ]);

        // â�?��,�â�?��,� Contact (secci�f³n del home) â�?��,�â�?��,�
        $contactGroup = $this->createFieldGroup('home-contact', 'Contacto (Home)', 'page', 'home', 9);
        $this->createFieldsAndValues($contactGroup, $page, 'page', [
            ['field_key' => 'home_contact_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Cont�f¡ctanos', 'value_en' => 'Contact Us'],
            ['field_key' => 'home_contact_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => '�,¿Listo para encontrar tu', 'value_en' => 'Ready to find your'],
            ['field_key' => 'home_contact_title_highlight', 'type' => 'text', 'label_es' => 'T�f­tulo destacado', 'value_es' => 'hogar ideal', 'value_en' => 'dream home'],
            ['field_key' => 'home_contact_text', 'type' => 'textarea', 'label_es' => 'Descripci�f³n', 'value_es' => 'D�f©janos tus datos y uno de nuestros asesores se pondr�f¡ en contacto contigo en menos de 24 horas.', 'value_en' => 'Leave us your details and one of our advisors will contact you within 24 hours.'],
        ]);
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // ABOUT - FIELD GROUPS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedAboutFieldGroups(): void
    {
        $this->command->info('  Creando campos de Nosotros...');
        $page = CmsPage::where('slug', 'about')->first();

        // Hero, enfoque y equipos usados por resources/views/public/about.blade.php
        $teamGroup = $this->createFieldGroup('about-team', 'Equipo, Brokers y Agentes', 'page', 'about', 2);
        $this->createFieldsAndValues($teamGroup, $page, 'page', [
            ['field_key' => 'about_who_heading', 'type' => 'text', 'label_es' => 'Heading principal', 'value_es' => 'Real estate con vision moderna', 'value_en' => 'Real estate with modern vision'],
            ['field_key' => 'about_who_text', 'type' => 'textarea', 'label_es' => 'Texto del hero', 'value_es' => 'Un equipo inmobiliario moderno, enfocado en estrategia, confianza y resultados medibles.', 'value_en' => 'A modern real estate team focused on strategy, trust and measurable results.'],
            ['field_key' => 'about_focus_label', 'type' => 'text', 'label_es' => 'Etiqueta de enfoque', 'value_es' => 'Estrategia + Ejecucion', 'value_en' => 'Strategy + Execution'],
            ['field_key' => 'about_focus_text', 'type' => 'textarea', 'label_es' => 'Texto de enfoque', 'value_es' => 'Decisiones con datos, operacion clara y acompanamiento cercano.', 'value_en' => 'Data-driven decisions, clear execution and close support.'],
            ['field_key' => 'about_brokers_title', 'type' => 'text', 'label_es' => 'Titulo Brokers', 'value_es' => 'Nuestros Brokers', 'value_en' => 'Our Brokers'],
            ['field_key' => 'about_brokers_subtitle', 'type' => 'textarea', 'label_es' => 'Subtitulo Brokers', 'value_es' => 'Liderazgo comercial con criterio, experiencia y ejecucion precisa.', 'value_en' => 'Commercial leadership with judgment, experience and precise execution.'],
            ['field_key' => 'about_core_team_title', 'type' => 'text', 'label_es' => 'Titulo Equipo', 'value_es' => 'Nuestro equipo', 'value_en' => 'Our Team'],
            ['field_key' => 'about_core_team_subtitle', 'type' => 'textarea', 'label_es' => 'Subtitulo Equipo', 'value_es' => 'El equipo interno que sostiene la operacion de punta a punta.', 'value_en' => 'The internal team that sustains operations end-to-end.'],
            ['field_key' => 'about_agents_title', 'type' => 'text', 'label_es' => 'Titulo Agentes', 'value_es' => 'Nuestros agentes', 'value_en' => 'Our Agents'],
            ['field_key' => 'about_agents_subtitle', 'type' => 'textarea', 'label_es' => 'Subtitulo Agentes', 'value_es' => 'Mostramos unicamente agentes activos de la agencia principal.', 'value_en' => 'We only show active agents from the main agency.'],
        ]);

        $this->createRepeaterAndRows($teamGroup, $page, 'page', 'about_brokers_members', 'Brokers', [
            ['broker_name' => ['es' => 'Erwit', 'en' => 'Erwit'], 'broker_role' => ['es' => 'Broker Lider', 'en' => 'Lead Broker'], 'broker_bio' => ['es' => 'Especializado en propiedades premium y negociaciones estrategicas.', 'en' => 'Specialized in premium listings and strategic negotiations.']],
            ['broker_name' => ['es' => 'Jenny', 'en' => 'Jenny'], 'broker_role' => ['es' => 'Broker Senior', 'en' => 'Senior Broker'], 'broker_bio' => ['es' => 'Enfocada en experiencia del cliente y cierres eficientes.', 'en' => 'Focused on client experience and efficient closing workflows.']],
        ], [
            ['field_key' => 'broker_name', 'type' => 'text', 'label_es' => 'Nombre', 'label_en' => 'Name', 'is_translatable' => false],
            ['field_key' => 'broker_role', 'type' => 'text', 'label_es' => 'Cargo', 'label_en' => 'Role'],
            ['field_key' => 'broker_bio', 'type' => 'textarea', 'label_es' => 'Bio', 'label_en' => 'Bio'],
            ['field_key' => 'broker_image', 'type' => 'image', 'label_es' => 'Foto', 'label_en' => 'Photo'],
        ]);

        $this->createRepeaterAndRows($teamGroup, $page, 'page', 'about_core_team_members', 'Equipo base', [
            ['core_member_name' => ['es' => 'Sophia', 'en' => 'Sophia'], 'core_member_role' => ['es' => 'Operaciones', 'en' => 'Operations'], 'core_member_bio' => ['es' => 'Coordina los flujos internos para mantener cada operacion en ritmo.', 'en' => 'Coordinates internal workflows to keep every operation on track.']],
            ['core_member_name' => ['es' => 'Jorge', 'en' => 'Jorge'], 'core_member_role' => ['es' => 'Marketing', 'en' => 'Marketing'], 'core_member_bio' => ['es' => 'Impulsa posicionamiento, contenido y adquisicion digital.', 'en' => 'Drives positioning, content and digital acquisition.']],
            ['core_member_name' => ['es' => 'Greta', 'en' => 'Greta'], 'core_member_role' => ['es' => 'Customer Success', 'en' => 'Customer Success'], 'core_member_bio' => ['es' => 'Lidera la atencion postventa y la relacion de largo plazo con clientes.', 'en' => 'Leads post-sale service and long-term client relationships.']],
        ], [
            ['field_key' => 'core_member_name', 'type' => 'text', 'label_es' => 'Nombre', 'label_en' => 'Name', 'is_translatable' => false],
            ['field_key' => 'core_member_role', 'type' => 'text', 'label_es' => 'Cargo', 'label_en' => 'Role'],
            ['field_key' => 'core_member_bio', 'type' => 'textarea', 'label_es' => 'Bio', 'label_en' => 'Bio'],
            ['field_key' => 'core_member_image', 'type' => 'image', 'label_es' => 'Foto', 'label_en' => 'Photo'],
        ]);

        // Historia, Mision y Vision
        $identityGroup = $this->createFieldGroup('about-identity', 'Historia, Misión y Visión', 'page', 'about', 3);
        $this->createFieldsAndValues($identityGroup, $page, 'page', [
            ['field_key' => 'about_history_title', 'type' => 'text', 'label_es' => 'Título Historia', 'value_es' => 'Historia', 'value_en' => 'History'],
            ['field_key' => 'about_history_text', 'type' => 'textarea', 'label_es' => 'Texto Historia', 'value_es' => 'Desde nuestros inicios hemos evolucionado con procesos claros y enfoque total en el cliente.', 'value_en' => 'Since our beginnings, we have evolved with clear processes and a client-first mindset.'],
            ['field_key' => 'about_mission_title', 'type' => 'text', 'label_es' => 'Título Misión', 'value_es' => 'Misión', 'value_en' => 'Mission'],
            ['field_key' => 'about_mission_text', 'type' => 'textarea', 'label_es' => 'Texto Misión', 'value_es' => 'Guiar a cada cliente con asesoría transparente y resultados medibles en cada operación.', 'value_en' => 'Guide each client with transparent advice and measurable results in every transaction.'],
            ['field_key' => 'about_vision_title', 'type' => 'text', 'label_es' => 'Título Visión', 'value_es' => 'Visión', 'value_en' => 'Vision'],
            ['field_key' => 'about_vision_text', 'type' => 'textarea', 'label_es' => 'Texto Visión', 'value_es' => 'Ser el aliado inmobiliario más confiable de la región, combinando personas y tecnología.', 'value_en' => 'Be the most trusted real estate partner in the region, powered by people and technology.'],
        ]);
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // CONTACT - FIELD GROUPS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedContactFieldGroups(): void
    {
        $this->command->info('  Creando campos de Contacto...');
        $page = CmsPage::where('slug', 'contact')->first();

        $heroGroup = $this->createFieldGroup('contact-hero', 'Hero Contacto', 'page', 'contact', 1);
        $this->createFieldsAndValues($heroGroup, $page, 'page', [
            ['field_key' => 'contact_hero_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Cont�f¡ctanos', 'value_en' => 'Contact Us'],
            ['field_key' => 'contact_hero_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Estamos aqu�f­ para ayudarte', 'value_en' => 'We are here to help'],
            ['field_key' => 'contact_hero_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Env�f­anos un mensaje y te responderemos en menos de 24 horas.', 'value_en' => 'Send us a message and we\'ll respond within 24 hours.'],
        ]);
    }

    protected function seedPublicFrontendFieldGroups(): void
    {
        $this->command->info('  Creando campos de vistas publicas...');

        $commonLabels = [
            ['field_key' => 'i18n_common_details', 'type' => 'text', 'label_es' => 'Texto: Ver detalles', 'value_es' => 'Ver detalles', 'value_en' => 'View details'],
            ['field_key' => 'i18n_common_properties', 'type' => 'text', 'label_es' => 'Texto: Propiedad', 'value_es' => 'Propiedad', 'value_en' => 'Property'],
            ['field_key' => 'i18n_common_available', 'type' => 'text', 'label_es' => 'Texto: Disponible', 'value_es' => 'Propiedad disponible', 'value_en' => 'Available property'],
            ['field_key' => 'i18n_common_sale', 'type' => 'text', 'label_es' => 'Texto: Venta', 'value_es' => 'En venta', 'value_en' => 'For sale'],
            ['field_key' => 'i18n_common_rent', 'type' => 'text', 'label_es' => 'Texto: Renta', 'value_es' => 'En renta', 'value_en' => 'For rent'],
            ['field_key' => 'i18n_common_locationAvailable', 'type' => 'text', 'label_es' => 'Texto: Ubicaci�f³n', 'value_es' => 'Ubicaci�f³n disponible', 'value_en' => 'Location available'],
            ['field_key' => 'i18n_common_consultPrice', 'type' => 'text', 'label_es' => 'Texto: Consultar precio', 'value_es' => 'Consultar precio', 'value_en' => 'Ask for price'],
            ['field_key' => 'i18n_common_operation', 'type' => 'text', 'label_es' => 'Texto: Operaci�f³n', 'value_es' => 'Operaci�f³n', 'value_en' => 'Operation'],
            ['field_key' => 'i18n_common_updated', 'type' => 'text', 'label_es' => 'Texto: Actualizado', 'value_es' => 'Actualizado', 'value_en' => 'Updated'],
        ];

        $pagesConfig = [
            'properties' => [
                ['field_key' => 'page_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Cat�f¡logo', 'value_en' => 'Catalog'],
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Explora nuestras propiedades', 'value_en' => 'Explore our properties'],
                ['field_key' => 'page_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Filtra por tipo y encuentra la propiedad ideal.', 'value_en' => 'Filter by type and find the right property for you.'],
                ['field_key' => 'search_label', 'type' => 'text', 'label_es' => 'Label b�fºsqueda', 'value_es' => 'Buscar', 'value_en' => 'Search'],
                ['field_key' => 'search_placeholder', 'type' => 'text', 'label_es' => 'Placeholder b�fºsqueda', 'value_es' => 'Buscar por ciudad, zona, tipo...', 'value_en' => 'Search by city, area, type...'],
            ],
            'property-detail' => [
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo base', 'value_es' => 'Detalle de propiedad', 'value_en' => 'Property detail'],
                ['field_key' => 'cta_back', 'type' => 'text', 'label_es' => 'Bot�f³n volver', 'value_es' => 'Volver', 'value_en' => 'Back'],
                ['field_key' => 'cta_share', 'type' => 'text', 'label_es' => 'Bot�f³n compartir', 'value_es' => 'Compartir', 'value_en' => 'Share'],
            ],
            'mls-offices' => [
                ['field_key' => 'page_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Agencias MLS', 'value_en' => 'MLS Agencies'],
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Explora nuestras agencias', 'value_en' => 'Explore our agencies'],
                ['field_key' => 'page_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Encuentra una agencia y revisa sus agentes y propiedades.', 'value_en' => 'Find an agency and review its agents and properties.'],
            ],
            'mls-office-detail' => [
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo base', 'value_es' => 'Detalle de agencia', 'value_en' => 'Agency detail'],
                ['field_key' => 'agents_title', 'type' => 'text', 'label_es' => 'T�f­tulo agentes', 'value_es' => 'Agentes', 'value_en' => 'Agents'],
                ['field_key' => 'properties_title', 'type' => 'text', 'label_es' => 'T�f­tulo propiedades', 'value_es' => 'Propiedades de la agencia', 'value_en' => 'Agency properties'],
            ],
            'mls-agents' => [
                ['field_key' => 'page_badge', 'type' => 'text', 'label_es' => 'Badge', 'value_es' => 'Agentes MLS', 'value_en' => 'MLS Agents'],
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo', 'value_es' => 'Conoce a nuestros agentes', 'value_en' => 'Meet our agents'],
                ['field_key' => 'page_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo', 'value_es' => 'Busca agentes, filtra por agencia y revisa sus propiedades.', 'value_en' => 'Search agents, filter by agency and review their properties.'],
            ],
            'mls-agent-detail' => [
                ['field_key' => 'page_title', 'type' => 'text', 'label_es' => 'T�f­tulo base', 'value_es' => 'Detalle de agente', 'value_en' => 'Agent detail'],
                ['field_key' => 'properties_title', 'type' => 'text', 'label_es' => 'T�f­tulo propiedades', 'value_es' => 'Propiedades del agente', 'value_en' => 'Agent properties'],
                ['field_key' => 'properties_subtitle', 'type' => 'textarea', 'label_es' => 'Subt�f­tulo propiedades', 'value_es' => 'Busca y navega propiedades vinculadas a este agente.', 'value_en' => 'Search and browse properties linked to this agent.'],
            ],
        ];

        foreach ($pagesConfig as $slug => $fields) {
            $page = CmsPage::where('slug', $slug)->first();
            if (!$page) {
                continue;
            }

            $group = $this->createFieldGroup("{$slug}-texts", "Textos {$slug}", 'page', $slug, 50);
            $this->createFieldsAndValues($group, $page, 'page', array_merge($fields, $commonLabels));
        }
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // MEN�fšS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedMenus(): void
    {
        $this->command->info('  Creando menus...');

        // â�?��,�â�?��,� Header Principal â�?��,�â�?��,�
        $header = CmsMenu::updateOrCreate(['slug' => 'main-header'], [
            'name' => 'Men�fº Principal',
            'location' => 'header',
            'description' => 'Navegaci�f³n principal del sitio',
            'is_active' => true,
        ]);
        $headerItems = [
            ['label_es' => 'Inicio', 'label_en' => 'Home', 'route_name' => 'home', 'sort_order' => 1],
            ['label_es' => 'Propiedades', 'label_en' => 'Properties', 'route_name' => 'public.properties.index', 'sort_order' => 2],
            ['label_es' => 'Agencias', 'label_en' => 'Agencies', 'route_name' => 'public.mls-offices.index', 'sort_order' => 3],
            ['label_es' => 'Vendedores', 'label_en' => 'Sellers', 'route_name' => 'public.sell-with-us', 'sort_order' => 4],
            ['label_es' => 'Nosotros', 'label_en' => 'About Us', 'route_name' => 'about', 'sort_order' => 5],
            ['label_es' => 'Contacto', 'label_en' => 'Contact', 'route_name' => 'public.contact', 'sort_order' => 6],
        ];
        foreach ($headerItems as $item) {
            if ($item['route_name'] === 'public.sell-with-us') {
                $sellerItem = CmsMenuItem::query()
                    ->where('menu_id', $header->id)
                    ->where(function ($query): void {
                        $query->whereIn('label_es', ['Agentes', 'Vendedores'])
                            ->orWhere('route_name', 'public.mls-agents.index');
                    })
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if ($sellerItem) {
                    $sellerItem->update(array_merge($item, ['menu_id' => $header->id, 'is_active' => true]));
                    continue;
                }
            }

            CmsMenuItem::updateOrCreate(
                ['menu_id' => $header->id, 'label_es' => $item['label_es']],
                array_merge($item, ['menu_id' => $header->id, 'is_active' => true])
            );
        }

        // â�?��,�â�?��,� Footer Empresa â�?��,�â�?��,�
        $footerCompany = CmsMenu::updateOrCreate(['slug' => 'footer-company'], [
            'name' => 'Footer - Empresa',
            'location' => 'footer_col_1',
            'description' => 'Enlaces de empresa en el footer',
            'is_active' => true,
        ]);
        $footerCompanyItems = [
            ['label_es' => 'Sobre nosotros', 'label_en' => 'About us', 'route_name' => 'about', 'sort_order' => 1],
            ['label_es' => 'Nuestro equipo', 'label_en' => 'Our team', 'url' => '/nosotros#equipo', 'sort_order' => 2],
            ['label_es' => 'Contacto', 'label_en' => 'Contact', 'route_name' => 'public.contact', 'sort_order' => 3],
        ];
        foreach ($footerCompanyItems as $item) {
            CmsMenuItem::updateOrCreate(
                ['menu_id' => $footerCompany->id, 'label_es' => $item['label_es']],
                array_merge($item, ['menu_id' => $footerCompany->id, 'is_active' => true])
            );
        }

        // â�?��,�â�?��,� Footer Servicios â�?��,�â�?��,�
        $footerServices = CmsMenu::updateOrCreate(['slug' => 'footer-services'], [
            'name' => 'Footer - Servicios',
            'location' => 'footer_col_2',
            'description' => 'Enlaces de servicios en el footer',
            'is_active' => true,
        ]);
        $footerServicesItems = [
            ['label_es' => 'Comprar', 'label_en' => 'Buy', 'url' => '/propiedades?operation_type=sale', 'sort_order' => 1],
            ['label_es' => 'Rentar', 'label_en' => 'Rent', 'url' => '/propiedades?operation_type=rental', 'sort_order' => 2],
            ['label_es' => 'Agencias', 'label_en' => 'Agencies', 'route_name' => 'public.mls-offices.index', 'sort_order' => 3],
            ['label_es' => 'Agentes', 'label_en' => 'Agents', 'route_name' => 'public.mls-agents.index', 'sort_order' => 4],
        ];
        foreach ($footerServicesItems as $item) {
            CmsMenuItem::updateOrCreate(
                ['menu_id' => $footerServices->id, 'label_es' => $item['label_es']],
                array_merge($item, ['menu_id' => $footerServices->id, 'is_active' => true])
            );
        }
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // SITE SETTINGS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function seedSiteSettings(): void
    {
        $this->command->info('  Creando site settings...');

        $settings = [
            // â�?��,�â�?��,� Contacto â�?��,�â�?��,�
            ['setting_key' => 'contact_phone', 'setting_group' => 'contact', 'label_es' => 'Tel�f©fono principal', 'label_en' => 'Main phone', 'type' => 'phone', 'value_es' => '+52 55 1234 5678', 'sort_order' => 1],
            ['setting_key' => 'contact_phone_secondary', 'setting_group' => 'contact', 'label_es' => 'Tel�f©fono secundario', 'label_en' => 'Secondary phone', 'type' => 'phone', 'value_es' => '', 'sort_order' => 2],
            ['setting_key' => 'contact_email', 'setting_group' => 'contact', 'label_es' => 'Email principal', 'label_en' => 'Main email', 'type' => 'email', 'value_es' => 'info@sanmiguelproperties.com', 'sort_order' => 3],
            ['setting_key' => 'contact_whatsapp', 'setting_group' => 'contact', 'label_es' => 'WhatsApp', 'label_en' => 'WhatsApp', 'type' => 'phone', 'value_es' => '+525512345678', 'sort_order' => 4],
            ['setting_key' => 'contact_address', 'setting_group' => 'contact', 'label_es' => 'Direcci�f³n', 'label_en' => 'Address', 'type' => 'textarea', 'value_es' => 'San Miguel de Allende, Guanajuato, M�f©xico', 'value_en' => 'San Miguel de Allende, Guanajuato, Mexico', 'sort_order' => 5],

            // â�?��,�â�?��,� Redes Sociales â�?��,�â�?��,�
            ['setting_key' => 'social_facebook', 'setting_group' => 'social', 'label_es' => 'Facebook', 'type' => 'url', 'value_es' => 'https://facebook.com/sanmiguelproperties', 'sort_order' => 1],
            ['setting_key' => 'social_instagram', 'setting_group' => 'social', 'label_es' => 'Instagram', 'type' => 'url', 'value_es' => 'https://instagram.com/sanmiguelproperties', 'sort_order' => 2],
            ['setting_key' => 'social_twitter', 'setting_group' => 'social', 'label_es' => 'Twitter/X', 'type' => 'url', 'value_es' => '', 'sort_order' => 3],
            ['setting_key' => 'social_linkedin', 'setting_group' => 'social', 'label_es' => 'LinkedIn', 'type' => 'url', 'value_es' => '', 'sort_order' => 4],
            ['setting_key' => 'social_youtube', 'setting_group' => 'social', 'label_es' => 'YouTube', 'type' => 'url', 'value_es' => '', 'sort_order' => 5],

            // â�?��,�â�?��,� General â�?��,�â�?��,�
            ['setting_key' => 'site_name', 'setting_group' => 'general', 'label_es' => 'Nombre del sitio', 'label_en' => 'Site name', 'type' => 'text', 'value_es' => 'San Miguel Properties', 'value_en' => 'San Miguel Properties', 'sort_order' => 1],
            ['setting_key' => 'site_tagline', 'setting_group' => 'general', 'label_es' => 'Tagline', 'type' => 'text', 'value_es' => 'Encuentra tu hogar ideal', 'value_en' => 'Find your dream home', 'sort_order' => 2],
            ['setting_key' => 'copyright_text', 'setting_group' => 'general', 'label_es' => 'Texto copyright', 'type' => 'text', 'value_es' => '2024 San Miguel Properties. Todos los derechos reservados.', 'value_en' => '2024 San Miguel Properties. All rights reserved.', 'sort_order' => 3],
            ['setting_key' => 'public_show_mls_offices', 'setting_group' => 'general', 'label_es' => 'Mostrar agencias MLS en el sitio', 'label_en' => 'Show MLS agencies on site', 'type' => 'boolean', 'value_es' => '1', 'value_en' => '1', 'sort_order' => 40],
            ['setting_key' => 'public_show_mls_agents', 'setting_group' => 'general', 'label_es' => 'Mostrar agentes MLS en el sitio', 'label_en' => 'Show MLS agents on site', 'type' => 'boolean', 'value_es' => '1', 'value_en' => '1', 'sort_order' => 41],
            ['setting_key' => 'public_mls_only_primary_office', 'setting_group' => 'general', 'label_es' => 'Restringir agentes publicos a la agencia principal', 'label_en' => 'Restrict public agents to the primary office', 'type' => 'boolean', 'value_es' => '0', 'value_en' => '0', 'sort_order' => 42],

            // â�?��,�â�?��,� SEO â�?��,�â�?��,�
            ['setting_key' => 'default_meta_title', 'setting_group' => 'seo', 'label_es' => 'Meta t�f­tulo por defecto', 'type' => 'text', 'value_es' => 'San Miguel Properties - Bienes Ra�f­ces', 'value_en' => 'San Miguel Properties - Real Estate', 'sort_order' => 1],
            ['setting_key' => 'default_meta_description', 'setting_group' => 'seo', 'label_es' => 'Meta descripci�f³n por defecto', 'type' => 'textarea', 'value_es' => 'Encuentra tu hogar ideal en San Miguel de Allende. Propiedades en venta y renta.', 'value_en' => 'Find your dream home in San Miguel de Allende. Properties for sale and rent.', 'sort_order' => 2],
            ['setting_key' => 'google_analytics_id', 'setting_group' => 'seo', 'label_es' => 'Google Analytics ID', 'type' => 'text', 'value_es' => '', 'sort_order' => 3],

            // â�?��,�â�?��,� Empresa â�?��,�â�?��,�
            ['setting_key' => 'company_name', 'setting_group' => 'company', 'label_es' => 'Nombre legal', 'type' => 'text', 'value_es' => 'San Miguel Properties S.A. de C.V.', 'sort_order' => 1],
            ['setting_key' => 'office_hours', 'setting_group' => 'company', 'label_es' => 'Horario de oficina', 'label_en' => 'Office hours', 'type' => 'text', 'value_es' => 'Lunes a Viernes 9:00 - 18:00', 'value_en' => 'Monday to Friday 9:00 AM - 6:00 PM', 'sort_order' => 2],
        ];

        foreach ($settings as $setting) {
            CmsSiteSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }

    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�
    // HELPERS
    // â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�â�?��,�

    protected function createFieldGroup(string $slug, string $name, string $locationType, ?string $locationIdentifier, int $sortOrder): CmsFieldGroup
    {
        return CmsFieldGroup::updateOrCreate(['slug' => $slug], [
            'name' => $name,
            'slug' => $slug,
            'location_type' => $locationType,
            'location_identifier' => $locationIdentifier,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }

    /**
     * Crea campos simples (no repeater) con sus valores.
     */
    protected function createFieldsAndValues(CmsFieldGroup $group, $entity, string $entityType, array $fields): void
    {
        foreach ($fields as $index => $fieldData) {
            $isTranslatable = $fieldData['is_translatable'] ?? !in_array($fieldData['type'], CmsFieldDefinition::NON_TRANSLATABLE_TYPES);

            $fieldDef = CmsFieldDefinition::updateOrCreate(
                ['field_group_id' => $group->id, 'field_key' => $fieldData['field_key']],
                [
                    'type' => $fieldData['type'],
                    'label_es' => $fieldData['label_es'],
                    'label_en' => $fieldData['label_en'] ?? null,
                    'is_required' => $fieldData['is_required'] ?? false,
                    'is_translatable' => $isTranslatable,
                    'sort_order' => $index,
                ]
            );

            if (isset($fieldData['value_es'])) {
                CmsFieldValue::updateOrCreate(
                    [
                        'field_definition_id' => $fieldDef->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entity->id,
                        'parent_value_id' => null,
                    ],
                    [
                        'value_es' => $fieldData['value_es'],
                        'value_en' => $fieldData['value_en'] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * Crea un campo repeater con sub-campos y filas de valores.
     */
    protected function createRepeaterAndRows(
        CmsFieldGroup $group,
        $entity,
        string $entityType,
        string $repeaterKey,
        string $repeaterLabel,
        array $rows,
        array $subFieldDefs
    ): CmsFieldDefinition {
        // Crear el campo repeater padre
        $repeaterDef = CmsFieldDefinition::updateOrCreate(
            ['field_group_id' => $group->id, 'field_key' => $repeaterKey],
            [
                'type' => 'repeater',
                'label_es' => $repeaterLabel,
                'is_translatable' => false,
                'sort_order' => 99, // Al final del grupo
            ]
        );

        // Crear sub-campos del repeater
        $subFields = [];
        foreach ($subFieldDefs as $index => $subDef) {
            $isTranslatable = $subDef['is_translatable'] ?? !in_array($subDef['type'], CmsFieldDefinition::NON_TRANSLATABLE_TYPES);
            $subFields[$subDef['field_key']] = CmsFieldDefinition::updateOrCreate(
                ['field_group_id' => $group->id, 'field_key' => $subDef['field_key']],
                [
                    'parent_id' => $repeaterDef->id,
                    'type' => $subDef['type'],
                    'label_es' => $subDef['label_es'],
                    'label_en' => $subDef['label_en'] ?? null,
                    'is_translatable' => $isTranslatable,
                    'sort_order' => $index,
                ]
            );
        }

        // Limpiar valores existentes del repeater para esta entidad
        $existingParents = CmsFieldValue::where('field_definition_id', $repeaterDef->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entity->id)
            ->whereNull('parent_value_id')
            ->get();
        foreach ($existingParents as $parent) {
            $parent->children()->delete();
            $parent->delete();
        }

        // Crear filas de valores
        foreach ($rows as $rowIndex => $rowData) {
            // Valor padre del repeater (representa la fila)
            $parentValue = CmsFieldValue::create([
                'field_definition_id' => $repeaterDef->id,
                'entity_type' => $entityType,
                'entity_id' => $entity->id,
                'row_index' => $rowIndex,
            ]);

            // Valores de sub-campos
            foreach ($rowData as $subKey => $subValue) {
                if (isset($subFields[$subKey])) {
                    CmsFieldValue::create([
                        'field_definition_id' => $subFields[$subKey]->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entity->id,
                        'value_es' => $subValue['es'] ?? null,
                        'value_en' => $subValue['en'] ?? null,
                        'parent_value_id' => $parentValue->id,
                        'row_index' => 0,
                    ]);
                }
            }
        }

        return $repeaterDef;
    }
}
