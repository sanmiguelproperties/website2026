<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $footerKeys = [
        'footer_site_tagline',
        'footer_office_hours',
        'footer_copyright',
        'footer_newsletter_title',
        'footer_newsletter_text',
        'footer_newsletter_placeholder',
        'footer_newsletter_button',
        'footer_quick_links',
        'footer_services',
        'footer_contact',
        'footer_phone',
        'footer_email',
        'footer_address',
        'footer_hours',
        'footer_whatsapp',
        'footer_about',
        'footer_privacy',
        'footer_terms',
        'footer_properties',
    ];

    public function up(): void
    {
        $footerPageId = DB::table('cms_pages')->where('slug', 'footer')->value('id');
        $footerGroupId = DB::table('cms_field_groups')->where('slug', 'footer-content')->value('id');

        if (!$footerPageId || !$footerGroupId) {
            return;
        }

        $fieldIds = DB::table('cms_field_definitions as fields')
            ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
            ->whereIn('fields.field_key', $this->footerKeys)
            ->where('groups.location_type', 'page')
            ->where(function ($query) {
                $query->where('groups.location_identifier', '<>', 'footer')
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
        throw new RuntimeException('Footer texts are now managed globally from the footer CMS page and cannot be safely restored to every page.');
    }
};
