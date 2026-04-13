<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $groupId = $this->upsertAboutTeamGroup($now);
        $aboutPageId = DB::table('cms_pages')->where('slug', 'about')->value('id');

        $simpleFields = [
            [
                'field_key' => 'about_who_title',
                'type' => 'text',
                'label_es' => 'Titulo Quienes somos',
                'label_en' => 'Who are we title',
                'value_es' => '¿Quiénes somos?',
                'value_en' => 'Who are we?',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_who_text',
                'type' => 'textarea',
                'label_es' => 'Texto Quienes somos',
                'label_en' => 'Who are we text',
                'value_es' => 'Un equipo inmobiliario moderno, enfocado en estrategia, confianza y resultados medibles.',
                'value_en' => 'A modern real estate team focused on strategy, trust and measurable results.',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_who_heading',
                'type' => 'text',
                'label_es' => 'Heading principal',
                'label_en' => 'Main heading',
                'value_es' => 'Real estate con visión moderna',
                'value_en' => 'Real estate with modern vision',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_focus_label',
                'type' => 'text',
                'label_es' => 'Etiqueta de enfoque',
                'label_en' => 'Focus label',
                'value_es' => 'Estrategia + Ejecución',
                'value_en' => 'Strategy + Execution',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_focus_text',
                'type' => 'textarea',
                'label_es' => 'Texto de enfoque',
                'label_en' => 'Focus text',
                'value_es' => 'Decisiones con datos, operación clara y acompañamiento cercano.',
                'value_en' => 'Data-driven decisions, clear execution and close support.',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_brokers_title',
                'type' => 'text',
                'label_es' => 'Titulo Brokers',
                'label_en' => 'Brokers title',
                'value_es' => 'Nuestros Brokers',
                'value_en' => 'Our Brokers',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_brokers_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo Brokers',
                'label_en' => 'Brokers subtitle',
                'value_es' => 'Liderazgo comercial con criterio, experiencia y ejecución precisa.',
                'value_en' => 'Commercial leadership with judgment, experience and precise execution.',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_core_team_title',
                'type' => 'text',
                'label_es' => 'Titulo Equipo',
                'label_en' => 'Team title',
                'value_es' => 'Nuestro equipo',
                'value_en' => 'Our Team',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_core_team_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo Equipo',
                'label_en' => 'Team subtitle',
                'value_es' => 'El equipo interno que sostiene la operación de punta a punta.',
                'value_en' => 'The internal team that sustains operations end-to-end.',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_agents_title',
                'type' => 'text',
                'label_es' => 'Titulo Agentes',
                'label_en' => 'Agents title',
                'value_es' => 'Nuestros agentes',
                'value_en' => 'Our Agents',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'about_agents_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo Agentes',
                'label_en' => 'Agents subtitle',
                'value_es' => 'Mostramos únicamente agentes activos de la agencia principal.',
                'value_en' => 'We only show active agents from the main agency.',
                'is_translatable' => true,
            ],
        ];

        foreach ($simpleFields as $index => $field) {
            $fieldId = $this->upsertFieldDefinition($groupId, $field, 100 + $index, $now);

            if ($aboutPageId) {
                $this->insertDefaultValueIfMissing(
                    $fieldId,
                    (int) $aboutPageId,
                    $field['value_es'],
                    $field['value_en'],
                    $now
                );
            }
        }

        $brokersRepeaterId = $this->upsertFieldDefinition(
            $groupId,
            [
                'field_key' => 'about_brokers_members',
                'type' => 'repeater',
                'label_es' => 'Brokers',
                'label_en' => 'Brokers',
                'is_translatable' => false,
            ],
            200,
            $now
        );

        $brokerChildren = [
            [
                'field_key' => 'broker_name',
                'type' => 'text',
                'label_es' => 'Nombre',
                'label_en' => 'Name',
                'is_translatable' => false,
            ],
            [
                'field_key' => 'broker_role',
                'type' => 'text',
                'label_es' => 'Cargo',
                'label_en' => 'Role',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'broker_bio',
                'type' => 'textarea',
                'label_es' => 'Bio',
                'label_en' => 'Bio',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'broker_image',
                'type' => 'image',
                'label_es' => 'Foto',
                'label_en' => 'Photo',
                'is_translatable' => false,
            ],
        ];

        $brokerChildIds = $this->upsertRepeaterChildren($groupId, $brokersRepeaterId, $brokerChildren, $now);

        if ($aboutPageId) {
            $this->insertRepeaterRowsIfMissing(
                $brokersRepeaterId,
                (int) $aboutPageId,
                $brokerChildIds,
                [
                    [
                        'broker_name' => ['es' => 'Erwit', 'en' => 'Erwit'],
                        'broker_role' => ['es' => 'Broker Líder', 'en' => 'Lead Broker'],
                        'broker_bio' => [
                            'es' => 'Especializado en propiedades premium y negociaciones estratégicas.',
                            'en' => 'Specialized in premium listings and strategic negotiations.',
                        ],
                    ],
                    [
                        'broker_name' => ['es' => 'Jenny', 'en' => 'Jenny'],
                        'broker_role' => ['es' => 'Broker Senior', 'en' => 'Senior Broker'],
                        'broker_bio' => [
                            'es' => 'Enfocada en experiencia del cliente y cierres eficientes.',
                            'en' => 'Focused on client experience and efficient closing workflows.',
                        ],
                    ],
                ],
                $now
            );
        }

        $teamRepeaterId = $this->upsertFieldDefinition(
            $groupId,
            [
                'field_key' => 'about_core_team_members',
                'type' => 'repeater',
                'label_es' => 'Equipo base',
                'label_en' => 'Core team',
                'is_translatable' => false,
            ],
            210,
            $now
        );

        $teamChildren = [
            [
                'field_key' => 'core_member_name',
                'type' => 'text',
                'label_es' => 'Nombre',
                'label_en' => 'Name',
                'is_translatable' => false,
            ],
            [
                'field_key' => 'core_member_role',
                'type' => 'text',
                'label_es' => 'Cargo',
                'label_en' => 'Role',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'core_member_bio',
                'type' => 'textarea',
                'label_es' => 'Bio',
                'label_en' => 'Bio',
                'is_translatable' => true,
            ],
            [
                'field_key' => 'core_member_image',
                'type' => 'image',
                'label_es' => 'Foto',
                'label_en' => 'Photo',
                'is_translatable' => false,
            ],
        ];

        $teamChildIds = $this->upsertRepeaterChildren($groupId, $teamRepeaterId, $teamChildren, $now);

        if ($aboutPageId) {
            $this->insertRepeaterRowsIfMissing(
                $teamRepeaterId,
                (int) $aboutPageId,
                $teamChildIds,
                [
                    [
                        'core_member_name' => ['es' => 'Sophia', 'en' => 'Sophia'],
                        'core_member_role' => ['es' => 'Operaciones', 'en' => 'Operations'],
                        'core_member_bio' => [
                            'es' => 'Coordina los flujos internos para mantener cada operación en ritmo.',
                            'en' => 'Coordinates internal workflows to keep every operation on track.',
                        ],
                    ],
                    [
                        'core_member_name' => ['es' => 'Jorge', 'en' => 'Jorge'],
                        'core_member_role' => ['es' => 'Marketing', 'en' => 'Marketing'],
                        'core_member_bio' => [
                            'es' => 'Impulsa posicionamiento, contenido y adquisición digital.',
                            'en' => 'Drives positioning, content and digital acquisition.',
                        ],
                    ],
                    [
                        'core_member_name' => ['es' => 'Greta', 'en' => 'Greta'],
                        'core_member_role' => ['es' => 'Customer Success', 'en' => 'Customer Success'],
                        'core_member_bio' => [
                            'es' => 'Lidera la atención postventa y la relación de largo plazo con clientes.',
                            'en' => 'Leads post-sale service and long-term client relationships.',
                        ],
                    ],
                ],
                $now
            );
        }
    }

    public function down(): void
    {
        $group = DB::table('cms_field_groups')->where('slug', 'about-team')->first();
        if (!$group) {
            return;
        }

        $keys = [
            'about_who_title',
            'about_who_text',
            'about_who_heading',
            'about_focus_label',
            'about_focus_text',
            'about_brokers_title',
            'about_brokers_subtitle',
            'about_core_team_title',
            'about_core_team_subtitle',
            'about_agents_title',
            'about_agents_subtitle',
            'about_brokers_members',
            'broker_name',
            'broker_role',
            'broker_bio',
            'broker_image',
            'about_core_team_members',
            'core_member_name',
            'core_member_role',
            'core_member_bio',
            'core_member_image',
        ];

        $fieldIds = DB::table('cms_field_definitions')
            ->where('field_group_id', $group->id)
            ->whereIn('field_key', $keys)
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
        DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
    }

    private function upsertAboutTeamGroup($now): int
    {
        $group = DB::table('cms_field_groups')->where('slug', 'about-team')->first();

        if ($group) {
            DB::table('cms_field_groups')
                ->where('id', $group->id)
                ->update([
                    'name' => 'Equipo, Brokers y Agentes',
                    'description' => 'Campos de brokers, equipo base y agentes para Nosotros.',
                    'location_type' => 'page',
                    'location_identifier' => 'about',
                    'sort_order' => 6,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);

            return (int) $group->id;
        }

        return (int) DB::table('cms_field_groups')->insertGetId([
            'name' => 'Equipo, Brokers y Agentes',
            'slug' => 'about-team',
            'description' => 'Campos de brokers, equipo base y agentes para Nosotros.',
            'location_type' => 'page',
            'location_identifier' => 'about',
            'sort_order' => 6,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertFieldDefinition(int $groupId, array $field, int $sortOrder, $now): int
    {
        $existing = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $field['field_key'])
            ->first();

        $payload = [
            'parent_id' => $field['parent_id'] ?? null,
            'type' => $field['type'],
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'] ?? null,
            'is_required' => false,
            'is_translatable' => $field['is_translatable'] ?? true,
            'sort_order' => $sortOrder,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('cms_field_definitions')
                ->where('id', $existing->id)
                ->update($payload);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_field_definitions')->insertGetId(array_merge($payload, [
            'field_group_id' => $groupId,
            'field_key' => $field['field_key'],
            'created_at' => $now,
        ]));
    }

    private function insertDefaultValueIfMissing(int $fieldId, int $aboutPageId, string $valueEs, string $valueEn, $now): void
    {
        $exists = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $aboutPageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('cms_field_values')->insert([
            'field_definition_id' => $fieldId,
            'entity_type' => 'page',
            'entity_id' => $aboutPageId,
            'value_es' => $valueEs,
            'value_en' => $valueEn,
            'media_asset_id' => null,
            'parent_value_id' => null,
            'row_index' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertRepeaterChildren(int $groupId, int $repeaterId, array $children, $now): array
    {
        $ids = [];

        foreach ($children as $index => $child) {
            $ids[$child['field_key']] = $this->upsertFieldDefinition(
                $groupId,
                [
                    'field_key' => $child['field_key'],
                    'parent_id' => $repeaterId,
                    'type' => $child['type'],
                    'label_es' => $child['label_es'],
                    'label_en' => $child['label_en'] ?? null,
                    'is_translatable' => $child['is_translatable'] ?? true,
                ],
                300 + $index,
                $now
            );
        }

        return $ids;
    }

    private function insertRepeaterRowsIfMissing(int $repeaterId, int $aboutPageId, array $childIds, array $rows, $now): void
    {
        $hasRows = DB::table('cms_field_values')
            ->where('field_definition_id', $repeaterId)
            ->where('entity_type', 'page')
            ->where('entity_id', $aboutPageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($hasRows) {
            return;
        }

        foreach ($rows as $rowIndex => $row) {
            $parentId = DB::table('cms_field_values')->insertGetId([
                'field_definition_id' => $repeaterId,
                'entity_type' => 'page',
                'entity_id' => $aboutPageId,
                'value_es' => null,
                'value_en' => null,
                'media_asset_id' => null,
                'parent_value_id' => null,
                'row_index' => $rowIndex,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($row as $fieldKey => $value) {
                if (!isset($childIds[$fieldKey])) {
                    continue;
                }

                DB::table('cms_field_values')->insert([
                    'field_definition_id' => $childIds[$fieldKey],
                    'entity_type' => 'page',
                    'entity_id' => $aboutPageId,
                    'value_es' => $value['es'] ?? null,
                    'value_en' => $value['en'] ?? null,
                    'media_asset_id' => null,
                    'parent_value_id' => $parentId,
                    'row_index' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
