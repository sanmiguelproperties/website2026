<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $headerKeys = [
        'header_nav_home',
        'header_nav_properties',
        'header_nav_neighborhoods',
        'header_nav_favorites',
        'header_nav_offices',
        'header_nav_about',
        'header_nav_team',
        'header_nav_contact',
        'header_nav_properties_terrains',
        'header_nav_properties_commercial',
        'header_nav_properties_all',
        'header_nav_properties_luxury',
        'header_cta_dashboard',
        'header_cta_login',
        'header_mobile_menu',
        'header_switch_to_en',
        'header_switch_to_es',
        'i18n_header_brand_primary',
        'i18n_header_brand_secondary',
        'i18n_common_siteName',
    ];

    public function up(): void
    {
        if (!DB::table('cms_pages')->where('slug', 'header')->exists()) {
            return;
        }

        $fieldIds = DB::table('cms_field_definitions as fields')
            ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
            ->whereIn('fields.field_key', $this->headerKeys)
            ->where('groups.location_type', 'page')
            ->where(function ($query) {
                $query->where('groups.location_identifier', '<>', 'header')
                    ->orWhereNull('groups.location_identifier');
            })
            ->pluck('fields.id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        $valueIds = DB::table('cms_field_values')
            ->whereIn('field_definition_id', $fieldIds)
            ->pluck('id');

        if ($valueIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('parent_value_id', $valueIds)->delete();
        }

        DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
        DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
    }

    public function down(): void
    {
        throw new RuntimeException('Header texts are now managed globally from the header CMS page and cannot be safely restored to every page.');
    }
};
