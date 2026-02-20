<?php

namespace App\Services;

use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Models\CmsMenu;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsSiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CmsService
{
    const CACHE_PREFIX = 'cms_';
    const CACHE_TTL = 3600; // 1 hora

    // ─── Páginas ────────────────────────────────────────

    /**
     * Obtener datos completos de una página por su slug.
     * Devuelve un CmsPageData con helpers para acceder a campos.
     */
    public static function getPageData(string $slug, ?string $locale = null): ?CmsPageData
    {
        $cacheKey = self::CACHE_PREFIX . "page_{$slug}";

        $rawData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug) {
            $page = CmsPage::published()->bySlug($slug)->first();
            if (!$page) {
                return null;
            }

            // Obtener todos los field groups para esta página
            $fieldGroups = CmsFieldGroup::active()
                ->forPage($slug)
                ->with(['fieldDefinitions.children'])
                ->orderBy('sort_order')
                ->get();

            // Obtener todos los valores asociados a esta página
            $fieldValues = CmsFieldValue::forPage($page->id)
                ->with(['fieldDefinition', 'mediaAsset', 'children.fieldDefinition', 'children.mediaAsset'])
                ->get();

            return [
                'page' => $page,
                'fieldGroups' => $fieldGroups,
                'fieldValues' => $fieldValues,
            ];
        });

        if (!$rawData) {
            return null;
        }

        return new CmsPageData(
            $rawData['page'],
            $rawData['fieldGroups'],
            $rawData['fieldValues'],
            $locale
        );
    }

    /**
     * Limpiar cache de una página.
     */
    public static function clearPageCache(string $slug): void
    {
        Cache::forget(self::CACHE_PREFIX . "page_{$slug}");
    }

    // ─── Posts ──────────────────────────────────────────

    /**
     * Obtener datos de un post con sus campos.
     */
    public static function getPostData(string $slug, ?string $locale = null): ?CmsPageData
    {
        $cacheKey = self::CACHE_PREFIX . "post_{$slug}";

        $rawData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug) {
            $post = CmsPost::published()
                ->bySlug($slug)
                ->with(['coverMediaAsset', 'author', 'categories', 'tags'])
                ->first();

            if (!$post) {
                return null;
            }

            // Obtener field groups para posts
            $fieldGroups = CmsFieldGroup::active()
                ->forPost($slug)
                ->with(['fieldDefinitions.children'])
                ->orderBy('sort_order')
                ->get();

            // Obtener valores
            $fieldValues = CmsFieldValue::forPost($post->id)
                ->with(['fieldDefinition', 'mediaAsset', 'children.fieldDefinition', 'children.mediaAsset'])
                ->get();

            return [
                'page' => $post, // usamos 'page' para reutilizar CmsPageData
                'fieldGroups' => $fieldGroups,
                'fieldValues' => $fieldValues,
            ];
        });

        if (!$rawData) {
            return null;
        }

        return new CmsPageData(
            $rawData['page'],
            $rawData['fieldGroups'],
            $rawData['fieldValues'],
            $locale
        );
    }

    public static function clearPostCache(string $slug): void
    {
        Cache::forget(self::CACHE_PREFIX . "post_{$slug}");
    }

    // ─── Menús ──────────────────────────────────────────

    /**
     * Obtener un menú por slug con sus items jerárquicos.
     */
    public static function getMenu(string $slug): ?CmsMenu
    {
        $cacheKey = self::CACHE_PREFIX . "menu_{$slug}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug) {
            return CmsMenu::active()
                ->bySlug($slug)
                ->with(['rootItems.children.children']) // hasta 3 niveles
                ->first();
        });
    }

    public static function clearMenuCache(string $slug): void
    {
        Cache::forget(self::CACHE_PREFIX . "menu_{$slug}");
    }

    // ─── Site Settings ──────────────────────────────────

    /**
     * Obtener un setting individual.
     */
    public static function setting(string $key, ?string $locale = null): ?string
    {
        return CmsSiteSetting::get($key, $locale);
    }

    /**
     * Obtener un grupo de settings como array key => value.
     */
    public static function settings(string|array $groups, ?string $locale = null): array
    {
        if (is_string($groups)) {
            return CmsSiteSetting::getGroup($groups, $locale);
        }

        $result = [];
        foreach ($groups as $group) {
            $result = array_merge($result, CmsSiteSetting::getGroup($group, $locale));
        }
        return $result;
    }

    // ─── Cache Global ───────────────────────────────────

    /**
     * Limpiar todo el cache del CMS.
     */
    public static function clearAllCache(): void
    {
        // Limpiar páginas
        $pages = CmsPage::pluck('slug');
        foreach ($pages as $slug) {
            self::clearPageCache($slug);
        }

        // Limpiar posts
        $posts = CmsPost::pluck('slug');
        foreach ($posts as $slug) {
            self::clearPostCache($slug);
        }

        // Limpiar menús
        $menus = CmsMenu::pluck('slug');
        foreach ($menus as $slug) {
            self::clearMenuCache($slug);
        }

        // Limpiar settings
        CmsSiteSetting::clearCache();
    }
}

// ─────────────────────────────────────────────────────────
// Clase auxiliar: CmsPageData
// Proporciona acceso fluido a campos desde Blade
// ─────────────────────────────────────────────────────────

class CmsPageData
{
    public $entity; // CmsPage o CmsPost
    public Collection $fieldGroups;
    public Collection $fieldValues;
    protected ?string $locale;
    protected array $indexedValues = [];

    public function __construct($entity, Collection $fieldGroups, Collection $fieldValues, ?string $locale = null)
    {
        $this->entity = $entity;
        $this->fieldGroups = $fieldGroups;
        $this->fieldValues = $fieldValues;
        $this->locale = $locale;
        $this->buildIndex();
    }

    /**
     * Construye un índice para acceso rápido por field_key.
     */
    protected function buildIndex(): void
    {
        foreach ($this->fieldValues as $value) {
            if ($value->fieldDefinition && is_null($value->parent_value_id)) {
                $key = $value->fieldDefinition->field_key;
                $this->indexedValues[$key] = $value;
            }
        }
    }

    /**
     * Obtener el valor de un campo por su field_key.
     *
     * Uso en Blade:
     *   {{ $pageData->field('hero_title') }}
     *   {{ $pageData->field('hero_title', 'en') }}
     */
    public function field(string $fieldKey, ?string $locale = null): ?string
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        if (!$value) {
            return null;
        }

        return $value->value($locale ?? $this->locale);
    }

    /**
     * Obtener la URL de un campo de tipo image.
     *
     * Uso en Blade:
     *   <img src="{{ $pageData->image('hero_bg') }}" />
     */
    public function image(string $fieldKey): ?string
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        return $value?->mediaUrl();
    }

    /**
     * Obtener el media asset completo de un campo de tipo image/file.
     */
    public function media(string $fieldKey)
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        return $value?->mediaAsset;
    }

    /**
     * Obtener filas de un campo repeater.
     * Devuelve un array de CmsRepeaterRow.
     *
     * Uso en Blade:
     *   @foreach($pageData->repeater('testimonials') as $row)
     *       {{ $row->field('name') }}
     *       {{ $row->field('text') }}
     *       <img src="{{ $row->image('avatar') }}" />
     *   @endforeach
     */
    public function repeater(string $fieldKey, ?string $locale = null): array
    {
        // Buscar TODOS los parent values del repeater (uno por fila)
        $parentValues = $this->fieldValues
            ->filter(function ($v) use ($fieldKey) {
                return $v->fieldDefinition
                    && $v->fieldDefinition->field_key === $fieldKey
                    && $v->fieldDefinition->type === 'repeater'
                    && is_null($v->parent_value_id);
            })
            ->sortBy('row_index');

        if ($parentValues->isEmpty()) {
            return [];
        }

        $rows = [];
        foreach ($parentValues as $parentValue) {
            // Buscar todos los child values que apuntan a este parent
            $children = $this->fieldValues
                ->where('parent_value_id', $parentValue->id);

            $rows[] = new CmsRepeaterRow($children, $parentValue->row_index, $locale ?? $this->locale);
        }

        return $rows;
    }

    /**
     * Obtener un campo como array (para checkbox, JSON values, etc).
     */
    public function fieldArray(string $fieldKey, ?string $locale = null): array
    {
        $raw = $this->field($fieldKey, $locale);
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [$raw];
    }

    /**
     * Obtener un campo como boolean.
     */
    public function fieldBool(string $fieldKey): bool
    {
        return (bool) $this->field($fieldKey);
    }

    /**
     * Verificar si un campo tiene valor.
     */
    public function has(string $fieldKey): bool
    {
        return isset($this->indexedValues[$fieldKey]) && $this->indexedValues[$fieldKey]->value_es !== null;
    }

    /**
     * Obtener todos los valores de un field group específico.
     */
    public function group(string $groupSlug): array
    {
        $group = $this->fieldGroups->firstWhere('slug', $groupSlug);
        if (!$group) {
            return [];
        }

        $result = [];
        foreach ($group->fieldDefinitions as $fieldDef) {
            $result[$fieldDef->field_key] = $this->field($fieldDef->field_key);
        }
        return $result;
    }
}

// ─────────────────────────────────────────────────────────
// Clase auxiliar: CmsRepeaterRow
// Representa una fila dentro de un campo repeater
// ─────────────────────────────────────────────────────────

class CmsRepeaterRow
{
    protected Collection $values;
    protected int $rowIndex;
    protected ?string $locale;
    protected array $indexedValues = [];

    public function __construct(Collection $values, int $rowIndex, ?string $locale = null)
    {
        $this->values = $values;
        $this->rowIndex = $rowIndex;
        $this->locale = $locale;
        $this->buildIndex();
    }

    protected function buildIndex(): void
    {
        foreach ($this->values as $value) {
            if ($value->fieldDefinition) {
                $this->indexedValues[$value->fieldDefinition->field_key] = $value;
            }
        }
    }

    public function field(string $fieldKey, ?string $locale = null): ?string
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        return $value?->value($locale ?? $this->locale);
    }

    public function image(string $fieldKey): ?string
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        return $value?->mediaUrl();
    }

    public function media(string $fieldKey)
    {
        $value = $this->indexedValues[$fieldKey] ?? null;
        return $value?->mediaAsset;
    }

    public function has(string $fieldKey): bool
    {
        return isset($this->indexedValues[$fieldKey]) && $this->indexedValues[$fieldKey]->value_es !== null;
    }

    public function index(): int
    {
        return $this->rowIndex;
    }
}
