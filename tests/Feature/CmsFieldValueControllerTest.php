<?php

namespace Tests\Feature;

use App\Http\Controllers\CmsFieldValueController;
use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Models\CmsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsFieldValueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_repeater_rows_for_a_field(): void
    {
        $page = CmsPage::create([
            'slug' => 'test-contact',
            'title_es' => 'Contacto de prueba',
            'title_en' => 'Test contact',
            'template' => 'test-contact',
            'status' => 'published',
            'is_active' => true,
        ]);

        $group = CmsFieldGroup::create([
            'name' => 'Preguntas frecuentes',
            'slug' => 'test-contact-faq',
            'location_type' => 'page',
            'location_identifier' => 'test-contact',
            'is_active' => true,
        ]);

        $repeater = CmsFieldDefinition::create([
            'field_group_id' => $group->id,
            'field_key' => 'contact_faq_items',
            'type' => 'repeater',
            'label_es' => 'Preguntas',
            'label_en' => 'Questions',
            'is_translatable' => false,
            'sort_order' => 1,
        ]);

        $question = CmsFieldDefinition::create([
            'field_group_id' => $group->id,
            'parent_id' => $repeater->id,
            'field_key' => 'faq_question',
            'type' => 'text',
            'label_es' => 'Pregunta',
            'label_en' => 'Question',
            'is_translatable' => true,
            'sort_order' => 1,
        ]);

        $answer = CmsFieldDefinition::create([
            'field_group_id' => $group->id,
            'parent_id' => $repeater->id,
            'field_key' => 'faq_answer',
            'type' => 'textarea',
            'label_es' => 'Respuesta',
            'label_en' => 'Answer',
            'is_translatable' => true,
            'sort_order' => 2,
        ]);

        foreach ([0, 1] as $index) {
            $parent = CmsFieldValue::create([
                'field_definition_id' => $repeater->id,
                'entity_type' => 'page',
                'entity_id' => $page->id,
                'row_index' => $index,
            ]);

            CmsFieldValue::create([
                'field_definition_id' => $question->id,
                'entity_type' => 'page',
                'entity_id' => $page->id,
                'parent_value_id' => $parent->id,
                'value_es' => 'Pregunta ' . ($index + 1),
                'value_en' => 'Question ' . ($index + 1),
            ]);

            CmsFieldValue::create([
                'field_definition_id' => $answer->id,
                'entity_type' => 'page',
                'entity_id' => $page->id,
                'parent_value_id' => $parent->id,
                'value_es' => 'Respuesta ' . ($index + 1),
                'value_en' => 'Answer ' . ($index + 1),
            ]);
        }

        $response = app(CmsFieldValueController::class)->index('page', $page->id);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);
        $rows = $payload['data']['test-contact-faq']['contact_faq_items']['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame('Pregunta 1', $rows[0]['faq_question']['value_es']);
        $this->assertSame('Respuesta 2', $rows[1]['faq_answer']['value_es']);
    }
}
