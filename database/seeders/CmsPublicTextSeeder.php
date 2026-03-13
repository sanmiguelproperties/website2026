<?php

namespace Database\Seeders;

use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPublicTextSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  -> Sync public view text keys to CMS...');

        $viewMap = [
            'home' => resource_path('views/home.blade.php'),
            'about' => resource_path('views/public/about.blade.php'),
            'contact' => resource_path('views/public/contact.blade.php'),
            'properties' => resource_path('views/public/properties-index.blade.php'),
            'property-detail' => resource_path('views/public/property-detail.blade.php'),
            'mls-offices' => resource_path('views/public/mls-offices-index.blade.php'),
            'mls-office-detail' => resource_path('views/public/mls-office-detail.blade.php'),
            'mls-agents' => resource_path('views/public/mls-agents-index.blade.php'),
            'mls-agent-detail' => resource_path('views/public/mls-agent-detail.blade.php'),
        ];

        $sharedViewPaths = [
            resource_path('views/layouts/public.blade.php'),
            resource_path('views/components/public/header.blade.php'),
            resource_path('views/components/public/footer.blade.php'),
        ];

        foreach ($viewMap as $slug => $viewPath) {
            if (!is_file($viewPath)) {
                continue;
            }

            $page = $this->ensurePage($slug);

            $fieldsByKey = [];
            $paths = array_values(array_unique(array_merge([$viewPath], $sharedViewPaths)));

            foreach ($paths as $path) {
                if (!is_file($path)) {
                    continue;
                }

                foreach ($this->extractViewFields($path) as $field) {
                    $fieldsByKey[$field['field_key']] = $field;
                }
            }

            $fields = array_values($fieldsByKey);
            if (empty($fields)) {
                continue;
            }

            $existingKeys = CmsFieldGroup::query()
                ->forPage($slug)
                ->with('allFieldDefinitions:id,field_group_id,field_key')
                ->get()
                ->flatMap(fn (CmsFieldGroup $group) => $group->allFieldDefinitions->pluck('field_key'))
                ->unique()
                ->values()
                ->all();

            $existingLookup = array_fill_keys($existingKeys, true);
            $missingFields = array_values(array_filter($fields, static fn (array $field) => !isset($existingLookup[$field['field_key']])));

            if (empty($missingFields)) {
                continue;
            }

            $group = CmsFieldGroup::query()->updateOrCreate(
                ['slug' => $slug . '-texts-auto'],
                [
                    'name' => 'Textos auto ' . $slug,
                    'description' => 'Llaves de texto extraidas automaticamente de la vista publica.',
                    'location_type' => 'page',
                    'location_identifier' => $slug,
                    'sort_order' => 95,
                    'is_active' => true,
                ]
            );

            foreach ($missingFields as $index => $field) {
                $fieldDef = CmsFieldDefinition::query()->updateOrCreate(
                    [
                        'field_group_id' => $group->id,
                        'field_key' => $field['field_key'],
                    ],
                    [
                        'type' => $field['type'],
                        'label_es' => $field['label_es'],
                        'label_en' => $field['label_en'],
                        'is_required' => false,
                        'is_translatable' => true,
                        'sort_order' => $index,
                    ]
                );

                CmsFieldValue::query()->updateOrCreate(
                    [
                        'field_definition_id' => $fieldDef->id,
                        'entity_type' => 'page',
                        'entity_id' => $page->id,
                        'parent_value_id' => null,
                    ],
                    [
                        'value_es' => $field['value_es'],
                        'value_en' => $field['value_en'],
                    ]
                );
            }
        }
    }

    /**
     * Extracts keys from Blade and JS helpers:
     * - $txt('key', 'es', 'en')
     * - tPublic('domain.key', isEnLocale ? 'en' : 'es')
     * - tPublic('domain.key', 'fallback')
     *
     * @return array<int, array{field_key:string,type:string,label_es:string,label_en:string,value_es:string,value_en:string}>
     */
    private function extractViewFields(string $viewPath): array
    {
        $content = file_get_contents($viewPath);
        if (!is_string($content) || $content === '') {
            return [];
        }

        $result = [];

        // Blade helper: $txt('field_key', 'es', 'en')
        $txtRegex = "/\\\$txt\\('([^']+)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\)/";
        preg_match_all($txtRegex, $content, $txtMatches, PREG_SET_ORDER);
        foreach ($txtMatches as $match) {
            $this->storeField(
                $result,
                trim((string) ($match[1] ?? '')),
                stripcslashes((string) ($match[2] ?? '')),
                stripcslashes((string) ($match[3] ?? ''))
            );
        }

        // JS helper: tPublic('a.b', isEnLocale ? 'EN' : 'ES')
        $tpTernaryRegex = "/tPublic\\('([a-zA-Z0-9._-]+)'\\s*,\\s*isEnLocale\\s*\\?\\s*'((?:\\\\'|[^'])*)'\\s*:\\s*'((?:\\\\'|[^'])*)'\\s*\\)/";
        preg_match_all($tpTernaryRegex, $content, $tpTernaryMatches, PREG_SET_ORDER);
        foreach ($tpTernaryMatches as $match) {
            $dotKey = trim((string) ($match[1] ?? ''));
            if ($dotKey === '') {
                continue;
            }

            $fieldKey = 'i18n_' . str_replace('.', '_', $dotKey);
            $valueEn = stripcslashes((string) ($match[2] ?? ''));
            $valueEs = stripcslashes((string) ($match[3] ?? ''));

            $this->storeField($result, $fieldKey, $valueEs, $valueEn);
        }

        // JS helper: tPublic('a.b', 'fallback')
        $tpSimpleRegex = "/tPublic\\('([a-zA-Z0-9._-]+)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\s*\\)/";
        preg_match_all($tpSimpleRegex, $content, $tpSimpleMatches, PREG_SET_ORDER);
        foreach ($tpSimpleMatches as $match) {
            $dotKey = trim((string) ($match[1] ?? ''));
            if ($dotKey === '') {
                continue;
            }

            $fieldKey = 'i18n_' . str_replace('.', '_', $dotKey);
            $fallback = stripcslashes((string) ($match[2] ?? ''));

            $this->storeField($result, $fieldKey, $fallback, $fallback);
        }

        return array_values($result);
    }

    /**
     * @param array<string, array{field_key:string,type:string,label_es:string,label_en:string,value_es:string,value_en:string}> $result
     */
    private function storeField(array &$result, string $fieldKey, string $valueEs, string $valueEn): void
    {
        if ($fieldKey === '' || isset($result[$fieldKey])) {
            return;
        }

        $human = ucwords(str_replace('_', ' ', $fieldKey));

        $result[$fieldKey] = [
            'field_key' => $fieldKey,
            'type' => (mb_strlen($valueEs) > 120 || mb_strlen($valueEn) > 120) ? 'textarea' : 'text',
            'label_es' => 'Texto: ' . $human,
            'label_en' => 'Text: ' . $human,
            'value_es' => $valueEs,
            'value_en' => $valueEn,
        ];
    }

    private function ensurePage(string $slug): CmsPage
    {
        $defaults = [
            'home' => ['title_es' => 'Inicio', 'title_en' => 'Home', 'template' => 'home', 'sort_order' => 1],
            'about' => ['title_es' => 'Nosotros', 'title_en' => 'About Us', 'template' => 'public.about', 'sort_order' => 2],
            'contact' => ['title_es' => 'Contacto', 'title_en' => 'Contact', 'template' => 'public.contact', 'sort_order' => 3],
            'properties' => ['title_es' => 'Propiedades', 'title_en' => 'Properties', 'template' => 'public.properties-index', 'sort_order' => 4],
            'property-detail' => ['title_es' => 'Detalle de Propiedad', 'title_en' => 'Property Detail', 'template' => 'public.property-detail', 'sort_order' => 5],
            'mls-offices' => ['title_es' => 'Agencias', 'title_en' => 'Agencies', 'template' => 'public.mls-offices-index', 'sort_order' => 6],
            'mls-office-detail' => ['title_es' => 'Detalle de Agencia', 'title_en' => 'Agency Detail', 'template' => 'public.mls-office-detail', 'sort_order' => 7],
            'mls-agents' => ['title_es' => 'Agentes', 'title_en' => 'Agents', 'template' => 'public.mls-agents-index', 'sort_order' => 8],
            'mls-agent-detail' => ['title_es' => 'Detalle de Agente', 'title_en' => 'Agent Detail', 'template' => 'public.mls-agent-detail', 'sort_order' => 9],
        ];

        $fallback = [
            'title_es' => ucfirst(str_replace('-', ' ', $slug)),
            'title_en' => ucfirst(str_replace('-', ' ', $slug)),
            'template' => 'public.' . $slug,
            'sort_order' => 99,
        ];

        $page = $defaults[$slug] ?? $fallback;

        return CmsPage::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'title_es' => $page['title_es'],
                'title_en' => $page['title_en'],
                'meta_title_es' => $page['title_es'] . ' - San Miguel Properties',
                'meta_title_en' => $page['title_en'] . ' - San Miguel Properties',
                'meta_description_es' => 'Contenido administrable de la pagina ' . $page['title_es'] . '.',
                'meta_description_en' => 'Manageable content for the ' . $page['title_en'] . ' page.',
                'template' => $page['template'],
                'status' => 'published',
                'is_active' => true,
                'sort_order' => $page['sort_order'],
            ]
        );
    }

}
