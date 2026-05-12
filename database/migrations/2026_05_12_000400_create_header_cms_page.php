<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fields = [
        ['header_nav_neighborhoods', 'text', 'Etiqueta submenu colonias', 'Neighborhoods submenu label', 'Colonias', 'Neighborhoods'],
        ['header_nav_favorites', 'text', 'Etiqueta favoritas', 'Favorites label', 'Favoritas', 'Favorites'],
        ['header_nav_offices', 'text', 'Etiqueta agencias', 'Agencies label', 'Agencias', 'Agencies'],
        ['header_nav_properties_terrains', 'text', 'Etiqueta terrenos', 'Land label', 'Terrenos', 'Land'],
        ['header_nav_properties_commercial', 'text', 'Etiqueta comercial', 'Commercial label', 'Commercial', 'Commercial'],
        ['header_nav_properties_all', 'text', 'Etiqueta todas las propiedades', 'All properties label', 'Todas las propiedades', 'All properties'],
        ['header_nav_properties_luxury', 'text', 'Etiqueta lujo', 'Luxury label', 'Lujo', 'Luxury'],
        ['header_cta_dashboard', 'text', 'Boton panel', 'Dashboard button', 'Panel', 'Dashboard'],
        ['header_cta_login', 'text', 'Boton login', 'Login button', 'Acceder', 'Login'],
        ['header_mobile_menu', 'text', 'Accesibilidad menu movil', 'Mobile menu accessibility label', 'Abrir menu', 'Open menu'],
        ['header_switch_to_en', 'text', 'Accesibilidad cambiar a ingles', 'Switch to English accessibility label', 'Cambiar a ingles', 'Switch to English'],
        ['header_switch_to_es', 'text', 'Accesibilidad cambiar a espanol', 'Switch to Spanish accessibility label', 'Cambiar a espanol', 'Switch to Spanish'],
        ['i18n_header_brand_primary', 'text', 'Marca fallback linea 1', 'Fallback brand line 1', 'San Miguel', 'San Miguel'],
        ['i18n_header_brand_secondary', 'text', 'Marca fallback linea 2', 'Fallback brand line 2', 'Properties', 'Properties'],
    ];

    public function up(): void
    {
        $now = now();

        DB::table('cms_pages')->updateOrInsert(
            ['slug' => 'header'],
            [
                'title_es' => 'Header',
                'title_en' => 'Header',
                'meta_title_es' => 'Header - San Miguel Properties',
                'meta_title_en' => 'Header - San Miguel Properties',
                'meta_description_es' => 'Contenido global administrable del header.',
                'meta_description_en' => 'Global manageable header content.',
                'template' => 'components.public.header',
                'status' => 'published',
                'is_active' => true,
                'sort_order' => 94,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $pageId = (int) DB::table('cms_pages')->where('slug', 'header')->value('id');

        DB::table('cms_field_groups')->updateOrInsert(
            ['slug' => 'header-content'],
            [
                'name' => 'Contenido del Header',
                'description' => 'Textos globales del header. Los enlaces se administran desde menus o rutas por defecto.',
                'location_type' => 'page',
                'location_identifier' => 'header',
                'sort_order' => 1,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $groupId = (int) DB::table('cms_field_groups')->where('slug', 'header-content')->value('id');

        foreach ($this->fields as $index => [$key, $type, $labelEs, $labelEn, $valueEs, $valueEn]) {
            DB::table('cms_field_definitions')->updateOrInsert(
                ['field_group_id' => $groupId, 'field_key' => $key],
                [
                    'parent_id' => null,
                    'type' => $type,
                    'label_es' => $labelEs,
                    'label_en' => $labelEn,
                    'is_required' => false,
                    'is_translatable' => true,
                    'sort_order' => $index,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $fieldId = (int) DB::table('cms_field_definitions')
                ->where('field_group_id', $groupId)
                ->where('field_key', $key)
                ->value('id');

            DB::table('cms_field_values')->updateOrInsert(
                [
                    'field_definition_id' => $fieldId,
                    'entity_type' => 'page',
                    'entity_id' => $pageId,
                    'parent_value_id' => null,
                ],
                [
                    'value_es' => $valueEs,
                    'value_en' => $valueEn,
                    'media_asset_id' => null,
                    'row_index' => 0,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        $groupId = DB::table('cms_field_groups')->where('slug', 'header-content')->value('id');

        if ($groupId) {
            $fieldIds = DB::table('cms_field_definitions')
                ->where('field_group_id', $groupId)
                ->pluck('id');

            if ($fieldIds->isNotEmpty()) {
                DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
                DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
            }

            DB::table('cms_field_groups')->where('id', $groupId)->delete();
        }

        DB::table('cms_pages')->where('slug', 'header')->delete();
    }
};
