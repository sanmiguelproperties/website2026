<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fieldKeys = [
        'about_who_title',
        'about_hero_badge',
        'about_hero_title',
        'about_hero_title_highlight',
        'about_summary_badge',
        'about_summary_title',
        'about_summary_title_highlight',
        'about_summary_text1',
        'about_summary_text2',
        'about_identity_badge',
        'about_identity_title',
        'about_identity_subtitle',
        'about_values_badge',
        'about_values_title',
        'about_values_subtitle',
        'about_values_items',
        'value_title',
        'value_description',
        'about_timeline_title',
        'about_timeline_subtitle',
        'about_timeline_items',
        'timeline_year',
        'timeline_title',
        'timeline_description',
        'about_team_badge',
        'about_team_title',
        'about_team_subtitle',
        'about_team_members',
        'member_name',
        'member_role',
        'member_image',
        'about_cta_title',
        'about_cta_subtitle',
    ];

    private array $groupSlugs = [
        'about-summary',
        'about-values',
        'about-timeline',
        'about-cta',
    ];

    public function up(): void
    {
        $fieldIds = DB::table('cms_field_definitions')
            ->whereIn('field_key', $this->fieldKeys)
            ->whereIn('field_group_id', function ($query) {
                $query->select('id')
                    ->from('cms_field_groups')
                    ->where('location_type', 'page')
                    ->where('location_identifier', 'about');
            })
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            $parentValueIds = DB::table('cms_field_values')
                ->whereIn('field_definition_id', $fieldIds)
                ->pluck('id');

            if ($parentValueIds->isNotEmpty()) {
                DB::table('cms_field_values')
                    ->whereIn('parent_value_id', $parentValueIds)
                    ->delete();
            }

            DB::table('cms_field_values')
                ->whereIn('field_definition_id', $fieldIds)
                ->delete();

            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        DB::table('cms_field_groups')
            ->whereIn('slug', $this->groupSlugs)
            ->where('location_type', 'page')
            ->where('location_identifier', 'about')
            ->delete();
    }

    public function down(): void
    {
        throw new RuntimeException('This migration removes obsolete CMS field values and cannot be safely reversed.');
    }
};
