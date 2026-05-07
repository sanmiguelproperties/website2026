<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $pageId = $this->upsertPage($now);

        $heroGroupId = $this->upsertGroup(
            'sell-with-us-hero',
            'Vende con nosotros - Hero',
            'Encabezado principal, imagen y llamada a la accion.',
            10,
            $now
        );

        $this->upsertFieldsWithDefaults($heroGroupId, $pageId, [
            [
                'field_key' => 'seller_hero_badge',
                'type' => 'text',
                'label_es' => 'Badge del hero',
                'label_en' => 'Hero badge',
                'value_es' => 'Vendedores',
                'value_en' => 'Sellers',
            ],
            [
                'field_key' => 'seller_hero_title',
                'type' => 'text',
                'label_es' => 'Titulo del hero',
                'label_en' => 'Hero title',
                'value_es' => 'Vende tu propiedad con estrategia local',
                'value_en' => 'Sell your home with local strategy',
            ],
            [
                'field_key' => 'seller_hero_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo del hero',
                'label_en' => 'Hero subtitle',
                'value_es' => 'Vender tu propiedad en San Miguel de Allende es una decision importante, y contar con la representacion adecuada hace toda la diferencia.',
                'value_en' => 'Selling your home in San Miguel de Allende is a major decision, and choosing the right representation matters.',
            ],
            [
                'field_key' => 'seller_hero_form_cta',
                'type' => 'text',
                'label_es' => 'Boton hacia formulario',
                'label_en' => 'Form CTA button',
                'value_es' => 'Solicitar asesoria',
                'value_en' => 'Request a consultation',
            ],
            [
                'field_key' => 'seller_hero_image',
                'type' => 'image',
                'label_es' => 'Imagen del hero',
                'label_en' => 'Hero image',
                'is_translatable' => false,
            ],
        ], $now);

        $contentGroupId = $this->upsertGroup(
            'sell-with-us-content',
            'Vende con nosotros - Contenido',
            'Bloques de texto principales de la pagina.',
            20,
            $now
        );

        $this->upsertFieldsWithDefaults($contentGroupId, $pageId, [
            [
                'field_key' => 'seller_intro_title',
                'type' => 'text',
                'label_es' => 'Titulo de contenido',
                'label_en' => 'Content title',
                'value_es' => 'Representacion local para vender bien',
                'value_en' => 'Local representation to sell well',
            ],
            [
                'field_key' => 'seller_intro_body',
                'type' => 'textarea',
                'label_es' => 'Bloque 1',
                'label_en' => 'Block 1',
                'value_es' => 'En San Miguel Properties, nuestros brokers cuentan con mas de 30 años de experiencia local combinada en bienes raices. Como agencia local y familiar profundamente arraigada en San Miguel, conocemos las colonias, las expectativas de los compradores, los rangos de precio, los aspectos legales y la red de colaboracion entre asesores que influyen directamente en una venta exitosa.',
                'value_en' => 'At San Miguel Properties, our brokers bring more than 30 years of combined local real estate experience to every listing. As a family-run agency deeply rooted in San Miguel, we understand the neighborhoods, buyer expectations, pricing patterns, legal considerations, and co-broker network that shape a successful sale.',
            ],
            [
                'field_key' => 'seller_local_experience_body',
                'type' => 'textarea',
                'label_es' => 'Bloque 2',
                'label_en' => 'Block 2',
                'value_es' => 'Nuestro equipo esta liderado por profesionales con decadas de experiencia en el mercado inmobiliario de San Miguel, incluyendo la formacion y capacitacion de otros asesores dentro de la comunidad. Ese nivel de conocimiento nos permite orientar a nuestros clientes con claridad, honestidad y una perspectiva que solo se obtiene con años de experiencia practica.',
                'value_en' => 'Our team is led by experienced professionals who have worked in the San Miguel market for decades and have also helped train and guide other real estate agents in the community. That depth of knowledge allows us to advise sellers with clarity, honesty, and hands-on insight.',
            ],
            [
                'field_key' => 'seller_process_body',
                'type' => 'textarea',
                'label_es' => 'Bloque 3',
                'label_en' => 'Block 3',
                'value_es' => 'Desde la preparacion de la propiedad para salir al mercado, hasta la estrategia de precio, promocion, visitas, negociacion y coordinacion del cierre, acompañamos cada etapa con profesionalismo y cuidado. Nuestro objetivo es representar tu propiedad de la mejor manera, proteger tus intereses y ayudarte a tomar decisiones informadas de principio a fin.',
                'value_en' => 'From preparing your property for market to pricing, promotion, showings, negotiation, and closing coordination, we manage each step with professionalism and care. Our goal is to represent your property well, protect your interests, and help you make informed decisions from start to finish.',
            ],
            [
                'field_key' => 'seller_final_body',
                'type' => 'textarea',
                'label_es' => 'Bloque final',
                'label_en' => 'Final block',
                'value_es' => 'Al vender con San Miguel Properties, trabajas con un equipo local que conoce el mercado, cuida su reputacion y trata cada propiedad con la atencion que merece.',
                'value_en' => 'When you sell with San Miguel Properties, you are working with a local team that knows the market, values its reputation, and treats every listing with the attention it deserves.',
            ],
        ], $now);

        $guideGroupId = $this->upsertGroup(
            'sell-with-us-guide',
            'Vende con nosotros - Manual descargable',
            'PDF o URL del manual para vendedores.',
            30,
            $now
        );

        $this->upsertFieldsWithDefaults($guideGroupId, $pageId, [
            [
                'field_key' => 'seller_guide_eyebrow',
                'type' => 'text',
                'label_es' => 'Etiqueta del manual',
                'label_en' => 'Guide eyebrow',
                'value_es' => 'Guia para vendedores',
                'value_en' => 'Seller guide',
            ],
            [
                'field_key' => 'seller_guide_title',
                'type' => 'text',
                'label_es' => 'Titulo del manual',
                'label_en' => 'Guide title',
                'value_es' => 'Descarga el manual para vendedores',
                'value_en' => 'Download the seller guide',
            ],
            [
                'field_key' => 'seller_guide_text',
                'type' => 'textarea',
                'label_es' => 'Texto del manual',
                'label_en' => 'Guide text',
                'value_es' => 'Consulta el material preparado por el equipo para entender los pasos clave antes de vender tu propiedad.',
                'value_en' => 'Review the material prepared by our team to understand the key steps before selling your property.',
            ],
            [
                'field_key' => 'seller_guide_button',
                'type' => 'text',
                'label_es' => 'Texto boton descargar',
                'label_en' => 'Download button text',
                'value_es' => 'Descargar manual',
                'value_en' => 'Download guide',
            ],
            [
                'field_key' => 'seller_guide_pending_button',
                'type' => 'text',
                'label_es' => 'Texto si falta el manual',
                'label_en' => 'Missing guide text',
                'value_es' => 'Manual pendiente',
                'value_en' => 'Guide pending',
            ],
            [
                'field_key' => 'seller_guide_file',
                'type' => 'file',
                'label_es' => 'PDF del manual',
                'label_en' => 'Guide PDF',
                'instructions_es' => 'Selecciona el PDF desde la biblioteca de medios.',
                'instructions_en' => 'Select the PDF from the media library.',
                'is_translatable' => false,
            ],
            [
                'field_key' => 'seller_guide_url',
                'type' => 'url',
                'label_es' => 'URL alternativa del manual',
                'label_en' => 'Alternative guide URL',
                'instructions_es' => 'Opcional. Se usa si no hay PDF seleccionado.',
                'instructions_en' => 'Optional. Used if no PDF is selected.',
                'is_translatable' => false,
            ],
        ], $now);

        $testimonialsGroupId = $this->upsertGroup(
            'sell-with-us-testimonials',
            'Vende con nosotros - Testimoniales',
            'Testimoniales agregados manualmente.',
            40,
            $now
        );

        $this->upsertFieldsWithDefaults($testimonialsGroupId, $pageId, [
            [
                'field_key' => 'seller_testimonials_title',
                'type' => 'text',
                'label_es' => 'Titulo testimoniales',
                'label_en' => 'Testimonials title',
                'value_es' => 'Testimoniales',
                'value_en' => 'Testimonials',
            ],
            [
                'field_key' => 'seller_testimonials_intro',
                'type' => 'textarea',
                'label_es' => 'Intro testimoniales',
                'label_en' => 'Testimonials intro',
                'value_es' => 'Opiniones agregadas manualmente desde el CMS.',
                'value_en' => 'Testimonials added manually from the CMS.',
            ],
        ], $now);

        $testimonialsRepeaterId = $this->upsertField($testimonialsGroupId, [
            'field_key' => 'seller_testimonials',
            'type' => 'repeater',
            'label_es' => 'Testimoniales',
            'label_en' => 'Testimonials',
            'is_translatable' => false,
            'sort_order' => 20,
        ], $now);

        $this->upsertField($testimonialsGroupId, [
            'parent_id' => $testimonialsRepeaterId,
            'field_key' => 'testimonial_quote',
            'type' => 'textarea',
            'label_es' => 'Testimonio',
            'label_en' => 'Quote',
            'sort_order' => 1,
        ], $now);
        $this->upsertField($testimonialsGroupId, [
            'parent_id' => $testimonialsRepeaterId,
            'field_key' => 'testimonial_name',
            'type' => 'text',
            'label_es' => 'Nombre',
            'label_en' => 'Name',
            'sort_order' => 2,
        ], $now);
        $this->upsertField($testimonialsGroupId, [
            'parent_id' => $testimonialsRepeaterId,
            'field_key' => 'testimonial_context',
            'type' => 'text',
            'label_es' => 'Contexto',
            'label_en' => 'Context',
            'sort_order' => 3,
        ], $now);

        $formGroupId = $this->upsertGroup(
            'sell-with-us-form',
            'Vende con nosotros - Formulario',
            'Textos del formulario de captura de leads.',
            50,
            $now
        );

        $this->upsertFieldsWithDefaults($formGroupId, $pageId, [
            [
                'field_key' => 'seller_form_badge',
                'type' => 'text',
                'label_es' => 'Badge del formulario',
                'label_en' => 'Form badge',
                'value_es' => 'Captura de datos',
                'value_en' => 'Lead capture',
            ],
            [
                'field_key' => 'seller_form_title',
                'type' => 'text',
                'label_es' => 'Titulo del formulario',
                'label_en' => 'Form title',
                'value_es' => 'Cuéntanos sobre tu propiedad',
                'value_en' => 'Tell us about your property',
            ],
            [
                'field_key' => 'seller_form_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo del formulario',
                'label_en' => 'Form subtitle',
                'value_es' => 'Completa el formulario y un broker de San Miguel Properties te contactara.',
                'value_en' => 'Complete the form and a San Miguel Properties broker will contact you.',
            ],
            [
                'field_key' => 'seller_form_privacy',
                'type' => 'textarea',
                'label_es' => 'Texto privacidad',
                'label_en' => 'Privacy text',
                'value_es' => 'Acepto que San Miguel Properties me contacte sobre mi solicitud.',
                'value_en' => 'I agree that San Miguel Properties may contact me about my request.',
            ],
            [
                'field_key' => 'seller_form_button',
                'type' => 'text',
                'label_es' => 'Boton enviar',
                'label_en' => 'Submit button',
                'value_es' => 'Enviar solicitud',
                'value_en' => 'Send request',
            ],
            [
                'field_key' => 'seller_form_success',
                'type' => 'textarea',
                'label_es' => 'Mensaje de exito',
                'label_en' => 'Success message',
                'value_es' => 'Solicitud enviada correctamente. Nos pondremos en contacto contigo pronto.',
                'value_en' => 'Request sent successfully. We will contact you shortly.',
            ],
        ], $now);

        $this->upsertMainHeaderItem($pageId, $now);
    }

    public function down(): void
    {
        $groupSlugs = [
            'sell-with-us-hero',
            'sell-with-us-content',
            'sell-with-us-guide',
            'sell-with-us-testimonials',
            'sell-with-us-form',
        ];

        $groupIds = DB::table('cms_field_groups')
            ->whereIn('slug', $groupSlugs)
            ->pluck('id');

        if ($groupIds->isNotEmpty()) {
            $fieldIds = DB::table('cms_field_definitions')
                ->whereIn('field_group_id', $groupIds)
                ->pluck('id');

            if ($fieldIds->isNotEmpty()) {
                DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
                DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
            }

            DB::table('cms_field_groups')->whereIn('id', $groupIds)->delete();
        }

        $menu = DB::table('cms_menus')->where('slug', 'main-header')->first();

        if ($menu) {
            DB::table('cms_menu_items')
                ->where('menu_id', $menu->id)
                ->where('label_es', 'Vendedores')
                ->where('route_name', 'public.sell-with-us')
                ->update([
                    'label_es' => 'Agentes',
                    'label_en' => 'Agents',
                    'route_name' => 'public.mls-agents.index',
                    'updated_at' => now(),
                ]);
        }

        DB::table('cms_menu_items')
            ->where('route_name', 'public.sell-with-us')
            ->where('label_es', 'Vende con nosotros')
            ->delete();

        DB::table('cms_pages')
            ->where('slug', 'sell-with-us')
            ->delete();
    }

    private function upsertPage($now): int
    {
        $existing = DB::table('cms_pages')->where('slug', 'sell-with-us')->first();
        $payload = [
            'title_es' => 'Vende con nosotros',
            'title_en' => 'Sell With Us',
            'meta_title_es' => 'Vende con nosotros - San Miguel Properties',
            'meta_title_en' => 'Sell With Us - San Miguel Properties',
            'meta_description_es' => 'Vende tu propiedad en San Miguel de Allende con asesoria local, estrategia de precio y acompañamiento profesional.',
            'meta_description_en' => 'Sell your home in San Miguel de Allende with local advice, pricing strategy and professional guidance.',
            'template' => 'public.sell-with-us',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 10,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('cms_pages')->where('id', $existing->id)->update($payload);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_pages')->insertGetId(array_merge($payload, [
            'slug' => 'sell-with-us',
            'created_at' => $now,
        ]));
    }

    private function upsertGroup(string $slug, string $name, string $description, int $sortOrder, $now): int
    {
        $existing = DB::table('cms_field_groups')->where('slug', $slug)->first();
        $payload = [
            'name' => $name,
            'description' => $description,
            'location_type' => 'page',
            'location_identifier' => 'sell-with-us',
            'sort_order' => $sortOrder,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('cms_field_groups')->where('id', $existing->id)->update($payload);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_field_groups')->insertGetId(array_merge($payload, [
            'slug' => $slug,
            'created_at' => $now,
        ]));
    }

    private function upsertFieldsWithDefaults(int $groupId, int $pageId, array $fields, $now): void
    {
        foreach ($fields as $index => $field) {
            $field['sort_order'] = $field['sort_order'] ?? $index;
            $fieldId = $this->upsertField($groupId, $field, $now);

            if (array_key_exists('value_es', $field) || array_key_exists('value_en', $field)) {
                $this->insertDefaultValueIfMissing(
                    $fieldId,
                    $pageId,
                    $field['value_es'] ?? null,
                    $field['value_en'] ?? null,
                    $now
                );
            }
        }
    }

    private function upsertField(int $groupId, array $field, $now): int
    {
        $existing = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $field['field_key'])
            ->first();

        $nonTranslatableTypes = ['number', 'boolean', 'color', 'date', 'datetime', 'image', 'gallery', 'file', 'email', 'phone'];
        $isTranslatable = $field['is_translatable'] ?? !in_array($field['type'], $nonTranslatableTypes, true);

        $payload = [
            'parent_id' => $field['parent_id'] ?? null,
            'type' => $field['type'],
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'] ?? null,
            'instructions_es' => $field['instructions_es'] ?? null,
            'instructions_en' => $field['instructions_en'] ?? null,
            'is_required' => $field['is_required'] ?? false,
            'is_translatable' => $isTranslatable,
            'sort_order' => $field['sort_order'] ?? 0,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('cms_field_definitions')->where('id', $existing->id)->update($payload);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_field_definitions')->insertGetId(array_merge($payload, [
            'field_group_id' => $groupId,
            'field_key' => $field['field_key'],
            'created_at' => $now,
        ]));
    }

    private function insertDefaultValueIfMissing(int $fieldId, int $pageId, ?string $valueEs, ?string $valueEn, $now): void
    {
        $exists = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $pageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('cms_field_values')->insert([
            'field_definition_id' => $fieldId,
            'entity_type' => 'page',
            'entity_id' => $pageId,
            'value_es' => $valueEs,
            'value_en' => $valueEn,
            'media_asset_id' => null,
            'parent_value_id' => null,
            'row_index' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertMainHeaderItem(int $pageId, $now): void
    {
        $menu = DB::table('cms_menus')->where('slug', 'main-header')->first();

        if (!$menu) {
            $menuId = DB::table('cms_menus')->insertGetId([
                'name' => 'Menu Principal',
                'slug' => 'main-header',
                'location' => 'header',
                'description' => 'Menu principal del sitio',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $menuId = (int) $menu->id;
        }

        $sellerMenuItem = DB::table('cms_menu_items')
            ->where('menu_id', $menuId)
            ->where(function ($query): void {
                $query->whereIn('label_es', ['Agentes', 'Vendedores'])
                    ->orWhere('route_name', 'public.mls-agents.index');
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $sellerPayload = [
            'menu_id' => $menuId,
            'parent_id' => null,
            'label_es' => 'Vendedores',
            'label_en' => 'Sellers',
            'url' => null,
            'route_name' => 'public.sell-with-us',
            'page_id' => null,
            'target' => '_self',
            'sort_order' => 4,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($sellerMenuItem) {
            DB::table('cms_menu_items')->where('id', $sellerMenuItem->id)->update($sellerPayload);
        } else {
            DB::table('cms_menu_items')->insert(array_merge($sellerPayload, [
                'created_at' => $now,
            ]));
        }

    }
};
