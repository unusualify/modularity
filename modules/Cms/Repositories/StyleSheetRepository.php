<?php

namespace Modules\Cms\Repositories;

use Illuminate\Support\Str;
use Modules\Cms\Entities\StyleSheet;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;
use Unusualify\Modularous\Models\Model;
use Unusualify\Modularous\Repositories\Repository;

class StyleSheetRepository extends Repository
{
    public function __construct(
        StyleSheet $model,
        private readonly StylesheetCompilerService $stylesheetCompiler,
    ) {
        $this->model = $model;
    }

    public function prepareFieldsBeforeCreate($fields)
    {
        $fields = parent::prepareFieldsBeforeCreate($fields);
        $fields['slug'] = $this->normalizeSlug($fields['slug'] ?? null, (string) ($fields['name'] ?? ''), null);

        return $fields;
    }

    /**
     * @param Model $object
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        $fields = parent::prepareFieldsBeforeSave($object, $fields);

        if (! ($object instanceof StyleSheet)) {
            return $fields;
        }

        $name = (string) ($fields['name'] ?? $object->name ?? '');

        if (array_key_exists('slug', $fields)) {
            $incoming = $fields['slug'];
            $fields['slug'] = $this->normalizeSlug(
                is_string($incoming) && $incoming !== '' ? $incoming : null,
                $name,
                $object->getKey(),
            );
        }

        return $fields;
    }

    /**
     * @param Model $object
     */
    public function afterSave($object, $fields)
    {
        parent::afterSave($object, $fields);

        if ($object instanceof StyleSheet) {
            $this->stylesheetCompiler->persistCompiled($object);
        }
    }

    public function recompile(StyleSheet $sheet): void
    {
        $this->stylesheetCompiler->persistCompiled($sheet);
    }

    private function normalizeSlug(?string $slug, string $name, mixed $ignoreId): string
    {
        $slug = $slug !== null && $slug !== '' ? Str::slug($slug) : Str::slug($name);
        if ($slug === '') {
            $slug = 'stylesheet';
        }

        $base = $slug;
        $i = 0;
        while (
            StyleSheet::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
