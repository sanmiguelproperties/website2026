<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('cms_pages')
            || !Schema::hasTable('cms_field_groups')
            || !Schema::hasTable('cms_field_definitions')
            || !Schema::hasTable('cms_field_values')
        ) {
            return;
        }

        $pageId = $this->ensureContactPage();

        $heroGroupId = $this->ensureGroup('contact-hero', 'Hero contacto', 1);
        $infoGroupId = $this->ensureGroup('contact-info', 'Informacion de contacto', 2);
        $formGroupId = $this->ensureGroup('contact-form', 'Formulario de contacto', 3);
        $faqGroupId = $this->ensureGroup('contact-faq', 'Preguntas frecuentes', 4);

        $this->ensureFieldValues($heroGroupId, $pageId, [
            ['contact_hero_title', 'text', 'Titulo', 'Title', 'Estamos aquí para ayudarte', 'We are here to help', 1],
            ['contact_hero_subtitle', 'textarea', 'Subtitulo', 'Subtitle', 'Envíanos un mensaje y te responderemos en menos de 24 horas.', 'Send us a message and we will get back to you in less than 24 hours.', 2],
        ]);

        $this->ensureFieldValues($infoGroupId, $pageId, [
            ['contact_info_title', 'text', 'Titulo de informacion', 'Information title', 'Información de contacto', 'Contact information', 1],
            ['contact_info_subtitle', 'textarea', 'Texto de informacion', 'Information text', 'Elige el canal que prefieras. Te responderemos lo antes posible.', 'Choose your preferred channel. We will respond as soon as possible.', 2],
            ['contact_label_phone', 'text', 'Etiqueta telefono', 'Phone label', 'Teléfono', 'Phone', 3],
            ['contact_label_whatsapp', 'text', 'Etiqueta WhatsApp', 'WhatsApp label', 'WhatsApp', 'WhatsApp', 4],
            ['contact_label_chat', 'text', 'Texto WhatsApp', 'WhatsApp text', 'Chatea con nosotros', 'Chat with us', 5],
            ['contact_label_email', 'text', 'Etiqueta email', 'Email label', 'Email', 'Email', 6],
            ['contact_label_addresses', 'text', 'Titulo direcciones', 'Addresses title', 'Direcciones', 'Addresses', 7],
            ['contact_label_corporate_office', 'text', 'Etiqueta oficina corporativa', 'Corporate office label', 'Oficina corporativa', 'Corporate office', 8],
            ['contact_label_center_office', 'text', 'Etiqueta oficina centro', 'Downtown office label', 'Oficina centro', 'Downtown office', 9],
            ['contact_label_follow', 'text', 'Titulo redes sociales', 'Social title', 'Síguenos', 'Follow us', 10],
        ]);

        $this->ensureFieldValues($formGroupId, $pageId, [
            ['contact_form_title', 'text', 'Titulo formulario', 'Form title', 'Envíanos un mensaje', 'Send us a message', 1],
            ['contact_form_subtitle', 'textarea', 'Texto formulario', 'Form text', 'Completa el formulario y nos pondremos en contacto contigo pronto.', 'Complete the form and we will contact you shortly.', 2],
            ['contact_form_label_name', 'text', 'Etiqueta nombre', 'Name label', 'Nombre completo', 'Full name', 3],
            ['contact_form_placeholder_name', 'text', 'Placeholder nombre', 'Name placeholder', 'Tu nombre completo', 'Your full name', 4],
            ['contact_form_placeholder_phone', 'text', 'Placeholder telefono', 'Phone placeholder', '+52 55 1234 5678', '+1 555 123 4567', 5],
            ['contact_form_label_email', 'text', 'Etiqueta email', 'Email label', 'Correo electrónico', 'Email', 6],
            ['contact_form_placeholder_email', 'text', 'Placeholder email', 'Email placeholder', 'tu@correo.com', 'you@email.com', 7],
            ['contact_form_label_interest', 'text', 'Etiqueta interes', 'Interest label', 'Estoy interesado en', 'I am interested in', 8],
            ['contact_form_interest_placeholder', 'text', 'Opcion inicial interes', 'Interest placeholder', 'Selecciona una opción', 'Select an option', 9],
            ['contact_form_interest_buy', 'text', 'Opcion comprar', 'Buy option', 'Comprar una propiedad', 'Buy a property', 10],
            ['contact_form_interest_rent', 'text', 'Opcion rentar', 'Rent option', 'Rentar una propiedad', 'Rent a property', 11],
            ['contact_form_interest_sell', 'text', 'Opcion vender', 'Sell option', 'Vender mi propiedad', 'Sell my property', 12],
            ['contact_form_interest_buy_sell', 'text', 'Opcion comprar y vender', 'Buy and sell option', 'Comprar y vender', 'Buy and sell', 13],
            ['contact_form_interest_invest', 'text', 'Opcion inversion', 'Investment option', 'Inversión inmobiliaria', 'Real estate investment', 14],
            ['contact_form_interest_other', 'text', 'Opcion otro', 'Other option', 'Otro', 'Other', 15],
            ['contact_form_label_message', 'text', 'Etiqueta mensaje', 'Message label', 'Mensaje', 'Message', 16],
            ['contact_form_placeholder_message', 'textarea', 'Placeholder mensaje', 'Message placeholder', 'Cuéntanos más sobre lo que buscas...', 'Tell us what you are looking for...', 17],
            ['contact_form_privacy', 'textarea', 'Texto privacidad', 'Privacy text', 'Acepto la política de privacidad y autorizo el tratamiento de mis datos personales.', 'I accept the privacy policy and authorize personal data processing.', 18],
            ['contact_form_submit', 'text', 'Boton enviar', 'Submit button', 'Enviar mensaje', 'Send message', 19],
            ['contact_form_sending', 'text', 'Texto enviando', 'Sending text', 'Enviando...', 'Sending...', 20],
            ['i18n_contact_requiredFields', 'text', 'Error campos requeridos', 'Required fields error', 'Por favor completa todos los campos requeridos.', 'Please complete all required fields.', 21],
            ['i18n_contact_acceptPrivacy', 'text', 'Error privacidad', 'Privacy error', 'Debes aceptar la política de privacidad.', 'You must accept the privacy policy.', 22],
            ['i18n_contact_submitSuccess', 'textarea', 'Mensaje envio correcto', 'Success message', '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.', 'Message sent successfully. We will contact you soon.', 23],
            ['i18n_contact_submitError', 'textarea', 'Mensaje error envio', 'Submit error message', 'Hubo un error al enviar el mensaje. Por favor intenta de nuevo.', 'There was an error sending your message. Please try again.', 24],
            ['i18n_contact_connectionError', 'textarea', 'Mensaje error conexion', 'Connection error message', 'Error de conexión. Por favor verifica tu conexión a internet e intenta de nuevo.', 'Connection error. Please check your internet and try again.', 25],
        ]);

        $this->ensureFieldValues($faqGroupId, $pageId, [
            ['contact_faq_title', 'text', 'Titulo FAQs', 'FAQ title', 'Preguntas frecuentes', 'Frequently asked questions', 1],
            ['contact_faq_whatsapp_cta', 'text', 'Boton WhatsApp FAQs', 'FAQ WhatsApp button', 'Chatea con nosotros por WhatsApp', 'Chat with us on WhatsApp', 2],
        ]);

        $faqFieldId = $this->ensureField($faqGroupId, null, [
            'field_key' => 'contact_faq_items',
            'type' => 'repeater',
            'label_es' => 'Preguntas',
            'label_en' => 'Questions',
            'instructions_es' => 'Agrega, edita u ordena las preguntas frecuentes que aparecen al final de la pagina de contacto.',
            'instructions_en' => 'Add, edit, or order the FAQ items displayed at the end of the contact page.',
            'is_translatable' => false,
            'sort_order' => 3,
        ]);

        $questionFieldId = $this->ensureField($faqGroupId, $faqFieldId, [
            'field_key' => 'faq_question',
            'type' => 'text',
            'label_es' => 'Pregunta',
            'label_en' => 'Question',
            'is_translatable' => true,
            'sort_order' => 1,
        ]);

        $answerFieldId = $this->ensureField($faqGroupId, $faqFieldId, [
            'field_key' => 'faq_answer',
            'type' => 'textarea',
            'label_es' => 'Respuesta',
            'label_en' => 'Answer',
            'is_translatable' => true,
            'sort_order' => 2,
        ]);

        $this->ensureFaqRows($pageId, $faqFieldId, $questionFieldId, $answerFieldId);

        Cache::forget('cms_page_contact');
    }

    public function down(): void
    {
        Cache::forget('cms_page_contact');
    }

    private function ensureContactPage(): int
    {
        $pageId = DB::table('cms_pages')->where('slug', 'contact')->value('id');
        $now = now();

        if ($pageId) {
            DB::table('cms_pages')
                ->where('id', $pageId)
                ->update([
                    'template' => 'contact',
                    'status' => 'published',
                    'is_active' => true,
                    'updated_at' => $now,
                ]);

            return (int) $pageId;
        }

        return (int) DB::table('cms_pages')->insertGetId([
            'slug' => 'contact',
            'title_es' => 'Contacto',
            'title_en' => 'Contact',
            'meta_title_es' => 'Contacto - San Miguel Properties',
            'meta_title_en' => 'Contact - San Miguel Properties',
            'meta_description_es' => 'Contáctanos para encontrar tu propiedad ideal. Estamos aquí para ayudarte.',
            'meta_description_en' => 'Contact us to find your ideal property. We are here to help you.',
            'template' => 'contact',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function ensureGroup(string $slug, string $name, int $sortOrder): int
    {
        $groupId = DB::table('cms_field_groups')->where('slug', $slug)->value('id');
        $now = now();

        $data = [
            'name' => $name,
            'description' => null,
            'location_type' => 'page',
            'location_identifier' => 'contact',
            'sort_order' => $sortOrder,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($groupId) {
            DB::table('cms_field_groups')->where('id', $groupId)->update($data);
            return (int) $groupId;
        }

        $data['slug'] = $slug;
        $data['created_at'] = $now;

        return (int) DB::table('cms_field_groups')->insertGetId($data);
    }

    /**
     * @param array<int, array{0:string,1:string,2:string,3:string,4:string,5:string,6:int}> $fields
     */
    private function ensureFieldValues(int $groupId, int $pageId, array $fields): void
    {
        foreach ($fields as [$key, $type, $labelEs, $labelEn, $valueEs, $valueEn, $sortOrder]) {
            $fieldId = $this->ensureField($groupId, null, [
                'field_key' => $key,
                'type' => $type,
                'label_es' => $labelEs,
                'label_en' => $labelEn,
                'is_translatable' => true,
                'sort_order' => $sortOrder,
            ]);

            $this->ensureValue($fieldId, $pageId, $valueEs, $valueEn);
        }
    }

    /**
     * @param array<string, mixed> $field
     */
    private function ensureField(int $groupId, ?int $parentId, array $field): int
    {
        $now = now();
        $fieldKey = (string) $field['field_key'];

        $fieldId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $fieldKey)
            ->value('id');

        if (!$fieldId && $parentId === null) {
            $fieldId = DB::table('cms_field_definitions as fields')
                ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
                ->where('fields.field_key', $fieldKey)
                ->whereNull('fields.parent_id')
                ->where('groups.location_type', 'page')
                ->where('groups.location_identifier', 'contact')
                ->orderBy('fields.id')
                ->value('fields.id');
        }

        $data = [
            'field_group_id' => $groupId,
            'parent_id' => $parentId,
            'type' => $field['type'],
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'] ?? null,
            'instructions_es' => $field['instructions_es'] ?? null,
            'instructions_en' => $field['instructions_en'] ?? null,
            'is_required' => false,
            'is_translatable' => $field['is_translatable'] ?? true,
            'sort_order' => $field['sort_order'] ?? 0,
            'updated_at' => $now,
        ];

        if ($fieldId) {
            DB::table('cms_field_definitions')->where('id', $fieldId)->update($data);
            return (int) $fieldId;
        }

        $data['field_key'] = $fieldKey;
        $data['created_at'] = $now;

        return (int) DB::table('cms_field_definitions')->insertGetId($data);
    }

    private function ensureValue(int $fieldId, int $pageId, ?string $valueEs, ?string $valueEn): void
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureFaqRows(int $pageId, int $faqFieldId, int $questionFieldId, int $answerFieldId): void
    {
        $hasRows = DB::table('cms_field_values')
            ->where('field_definition_id', $faqFieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $pageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($hasRows) {
            return;
        }

        $rows = [
            [
                'q_es' => '¿Cuánto tiempo tarda el proceso de compra?',
                'q_en' => 'How long does the buying process take?',
                'a_es' => 'Generalmente toma entre 30 y 60 días dependiendo de la complejidad de la operación y la disponibilidad documental.',
                'a_en' => 'It usually takes between 30 and 60 days depending on the transaction complexity and documentation readiness.',
            ],
            [
                'q_es' => '¿Ofrecen apoyo con financiamiento?',
                'q_en' => 'Do you offer financing support?',
                'a_es' => 'Sí. Trabajamos con instituciones aliadas y te ayudamos a evaluar las mejores opciones de crédito para tu caso.',
                'a_en' => 'Yes. We work with partner institutions and help you evaluate the best credit options for your case.',
            ],
            [
                'q_es' => '¿Puedo agendar una visita virtual?',
                'q_en' => 'Can I schedule a virtual tour?',
                'a_es' => 'Sí. Ofrecemos recorridos virtuales y videollamadas en vivo para revisar propiedades de forma remota.',
                'a_en' => 'Yes. We offer virtual tours and live video calls so you can review properties remotely.',
            ],
        ];

        foreach ($rows as $index => $row) {
            $parentId = DB::table('cms_field_values')->insertGetId([
                'field_definition_id' => $faqFieldId,
                'entity_type' => 'page',
                'entity_id' => $pageId,
                'value_es' => null,
                'value_en' => null,
                'media_asset_id' => null,
                'parent_value_id' => null,
                'row_index' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cms_field_values')->insert([
                [
                    'field_definition_id' => $questionFieldId,
                    'entity_type' => 'page',
                    'entity_id' => $pageId,
                    'value_es' => $row['q_es'],
                    'value_en' => $row['q_en'],
                    'media_asset_id' => null,
                    'parent_value_id' => $parentId,
                    'row_index' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'field_definition_id' => $answerFieldId,
                    'entity_type' => 'page',
                    'entity_id' => $pageId,
                    'value_es' => $row['a_es'],
                    'value_en' => $row['a_en'],
                    'media_asset_id' => null,
                    'parent_value_id' => $parentId,
                    'row_index' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
};
