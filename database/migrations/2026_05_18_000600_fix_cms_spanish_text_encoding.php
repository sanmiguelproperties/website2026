<?php

use App\Support\CmsSpanishText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repairCmsTextColumns();
        $this->refreshAutoFieldLabels();
    }

    public function down(): void
    {
        // Data-only cleanup; intentionally not reversible.
    }

    private function repairCmsTextColumns(): void
    {
        foreach ($this->cmsTextColumns() as $table => $columns) {
            $this->repairTableColumns($table, $columns);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function cmsTextColumns(): array
    {
        return [
            'cms_pages' => ['title_es', 'title_en', 'meta_title_es', 'meta_title_en', 'meta_description_es', 'meta_description_en'],
            'cms_field_groups' => ['name', 'description'],
            'cms_field_definitions' => ['label_es', 'label_en', 'instructions_es', 'instructions_en', 'placeholder_es', 'placeholder_en', 'default_value_es', 'default_value_en'],
            'cms_field_values' => ['value_es', 'value_en'],
            'cms_site_settings' => ['label_es', 'label_en', 'value_es', 'value_en'],
            'cms_menus' => ['name', 'description'],
            'cms_menu_items' => ['label_es', 'label_en'],
        ];
    }

    /**
     * @param array<int, string> $columns
     */
    private function repairTableColumns(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columns = array_values(array_filter($columns, static fn (string $column): bool => Schema::hasColumn($table, $column)));
        if ($columns === []) {
            return;
        }

        DB::table($table)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $columns): void {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($columns as $column) {
                        $original = $row->{$column} ?? null;
                        if (!is_string($original)) {
                            continue;
                        }

                        $fixed = CmsSpanishText::repairEncoding($original);
                        if ($fixed !== $original) {
                            $updates[$column] = $fixed;
                        }
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function refreshAutoFieldLabels(): void
    {
        if (
            !Schema::hasTable('cms_field_definitions')
            || !Schema::hasTable('cms_field_groups')
            || !Schema::hasTable('cms_field_values')
        ) {
            return;
        }

        $definitions = DB::table('cms_field_definitions as fields')
            ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
            ->leftJoin('cms_field_values as values', function ($join): void {
                $join->on('values.field_definition_id', '=', 'fields.id')
                    ->whereNull('values.parent_value_id');
            })
            ->where('groups.slug', 'like', '%-texts-auto')
            ->select('fields.id', 'fields.field_key', 'fields.label_es', 'values.value_es')
            ->orderBy('fields.id')
            ->get();

        foreach ($definitions as $definition) {
            $label = CmsSpanishText::makeAdminLabel($definition->field_key, $definition->value_es);

            if ($label !== $definition->label_es) {
                DB::table('cms_field_definitions')
                    ->where('id', $definition->id)
                    ->update(['label_es' => $label]);
            }
        }
    }
};
