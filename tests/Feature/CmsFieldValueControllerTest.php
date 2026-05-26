<?php

namespace Tests\Feature;

use App\Http\Controllers\CmsFieldValueController;
use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Models\CmsPage;
use App\Services\CmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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

    public function test_update_preserves_spanish_value_when_only_english_content_changes(): void
    {
        $page = CmsPage::create([
            'slug' => 'properties-test',
            'title_es' => 'Propiedades',
            'title_en' => 'Properties',
            'template' => 'public.properties-index',
            'status' => 'published',
            'is_active' => true,
        ]);

        $group = CmsFieldGroup::create([
            'name' => 'Hero',
            'slug' => 'properties-test-hero',
            'location_type' => 'page',
            'location_identifier' => 'properties-test',
            'is_active' => true,
        ]);

        $field = CmsFieldDefinition::create([
            'field_group_id' => $group->id,
            'field_key' => 'page_title_prefix',
            'type' => 'text',
            'label_es' => 'Titulo',
            'label_en' => 'Title',
            'is_translatable' => true,
            'sort_order' => 1,
        ]);

        CmsFieldValue::create([
            'field_definition_id' => $field->id,
            'entity_type' => 'page',
            'entity_id' => $page->id,
            'value_es' => 'Explora nuestras',
            'value_en' => 'Explore our',
        ]);

        $this->assertSame('Explore our', CmsService::getPageData('properties-test', 'en')?->field('page_title_prefix'));

        $request = Request::create('/api/cms/field-values/page/' . $page->id, 'PUT', [
            'fields' => [
                'page_title_prefix' => [
                    'value_en' => 'Updated English heading',
                ],
            ],
        ]);

        $response = app(CmsFieldValueController::class)->update($request, 'page', $page->id);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);

        $value = CmsFieldValue::query()
            ->where('field_definition_id', $field->id)
            ->where('entity_type', 'page')
            ->where('entity_id', $page->id)
            ->firstOrFail();

        $this->assertSame('Explora nuestras', $value->value_es);
        $this->assertSame('Updated English heading', $value->value_en);
        $this->assertSame('Updated English heading', CmsService::getPageData('properties-test', 'en')?->field('page_title_prefix'));
    }

    public function test_page_data_ignores_values_from_inactive_or_legacy_field_groups(): void
    {
        $page = CmsPage::create([
            'slug' => 'properties-test',
            'title_es' => 'Propiedades',
            'title_en' => 'Properties',
            'template' => 'public.properties-index',
            'status' => 'published',
            'is_active' => true,
        ]);

        $activeGroup = CmsFieldGroup::create([
            'name' => 'Textos de propiedades',
            'slug' => 'properties-test-active',
            'location_type' => 'page',
            'location_identifier' => 'properties-test',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $legacyGroup = CmsFieldGroup::create([
            'name' => 'Textos legacy propiedades',
            'slug' => 'properties-test-legacy',
            'location_type' => 'page',
            'location_identifier' => 'properties-legacy',
            'is_active' => false,
            'sort_order' => 999,
        ]);

        $activeField = CmsFieldDefinition::create([
            'field_group_id' => $activeGroup->id,
            'field_key' => 'page_subtitle',
            'type' => 'textarea',
            'label_es' => 'Subtitulo',
            'label_en' => 'Subtitle',
            'is_translatable' => true,
            'sort_order' => 1,
        ]);

        $legacyField = CmsFieldDefinition::create([
            'field_group_id' => $legacyGroup->id,
            'field_key' => 'page_subtitle',
            'type' => 'textarea',
            'label_es' => 'Subtitulo viejo',
            'label_en' => 'Old subtitle',
            'is_translatable' => true,
            'sort_order' => 1,
        ]);

        CmsFieldValue::create([
            'field_definition_id' => $activeField->id,
            'entity_type' => 'page',
            'entity_id' => $page->id,
            'value_es' => 'Subtitulo activo',
            'value_en' => 'Active subtitle',
        ]);

        CmsFieldValue::create([
            'field_definition_id' => $legacyField->id,
            'entity_type' => 'page',
            'entity_id' => $page->id,
            'value_es' => 'Subtitulo legacy',
            'value_en' => 'Legacy subtitle',
        ]);

        $this->assertSame('Active subtitle', CmsService::getPageData('properties-test', 'en')?->field('page_subtitle'));
    }
}
