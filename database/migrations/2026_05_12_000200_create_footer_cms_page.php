<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fields = [
        ['footer_site_tagline', 'text', 'Tagline del sitio', 'Site tagline', 'Encuentra tu hogar ideal', 'Find your dream home'],
        ['footer_office_hours', 'text', 'Horario de oficina', 'Business hours', 'Lunes a Viernes 9:00 - 18:00', 'Monday to Friday 9:00 AM - 6:00 PM'],
        ['footer_copyright', 'text', 'Texto copyright', 'Copyright text', 'Todos los derechos reservados.', 'All rights reserved.'],
        ['footer_newsletter_title', 'text', 'Titulo newsletter', 'Newsletter title', 'Suscribete a nuestro newsletter', 'Subscribe to our newsletter'],
        ['footer_newsletter_text', 'textarea', 'Texto newsletter', 'Newsletter text', 'Recibe las ultimas propiedades y oportunidades exclusivas directamente en tu correo.', 'Receive the latest properties and exclusive opportunities directly in your inbox.'],
        ['footer_newsletter_placeholder', 'text', 'Placeholder email newsletter', 'Newsletter email placeholder', 'tu@correo.com', 'you@email.com'],
        ['footer_newsletter_button', 'text', 'Boton newsletter', 'Newsletter button', 'Suscribirse', 'Subscribe'],
        ['footer_quick_links', 'text', 'Titulo enlaces rapidos', 'Quick links title', 'Enlaces rapidos', 'Quick Links'],
        ['footer_services', 'text', 'Titulo servicios', 'Services title', 'Servicios', 'Services'],
        ['footer_contact', 'text', 'Titulo contacto', 'Contact title', 'Contactanos', 'Contact'],
        ['footer_phone', 'text', 'Etiqueta telefono', 'Phone label', 'Telefono', 'Phone'],
        ['footer_email', 'text', 'Etiqueta email', 'Email label', 'Email', 'Email'],
        ['footer_address', 'text', 'Etiqueta direccion', 'Address label', 'Direccion', 'Address'],
        ['footer_hours', 'text', 'Etiqueta horario', 'Hours label', 'Horario', 'Business Hours'],
        ['footer_whatsapp', 'text', 'Etiqueta WhatsApp', 'WhatsApp label', 'WhatsApp', 'WhatsApp'],
        ['footer_about', 'text', 'Fallback enlace nosotros', 'About fallback link', 'Sobre nosotros', 'About us'],
        ['footer_privacy', 'text', 'Texto privacidad', 'Privacy text', 'Privacidad', 'Privacy'],
        ['footer_terms', 'text', 'Texto terminos', 'Terms text', 'Terminos', 'Terms'],
        ['footer_properties', 'text', 'Fallback propiedades', 'Properties fallback', 'Propiedades', 'Properties'],
    ];

    public function up(): void
    {
        $now = now();

        $pageId = (int) DB::table('cms_pages')->updateOrInsert(
            ['slug' => 'footer'],
            [
                'title_es' => 'Footer',
                'title_en' => 'Footer',
                'meta_title_es' => 'Footer - San Miguel Properties',
                'meta_title_en' => 'Footer - San Miguel Properties',
                'meta_description_es' => 'Contenido global administrable del footer.',
                'meta_description_en' => 'Global manageable footer content.',
                'template' => 'components.public.footer',
                'status' => 'published',
                'is_active' => true,
                'sort_order' => 95,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $pageId = (int) DB::table('cms_pages')->where('slug', 'footer')->value('id');

        $groupId = (int) DB::table('cms_field_groups')->updateOrInsert(
            ['slug' => 'footer-content'],
            [
                'name' => 'Contenido del Footer',
                'description' => 'Textos globales del footer y newsletter. Se aplican a todo el sitio.',
                'location_type' => 'page',
                'location_identifier' => 'footer',
                'sort_order' => 1,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $groupId = (int) DB::table('cms_field_groups')->where('slug', 'footer-content')->value('id');

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
        $groupId = DB::table('cms_field_groups')->where('slug', 'footer-content')->value('id');

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

        DB::table('cms_pages')->where('slug', 'footer')->delete();
    }
};
