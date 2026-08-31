<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Model;

trait TranslationsTrait
{
    protected $nullableFields = [];

    public function setColumnsTranslationsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (isset($curr['translated']) && $curr['translated']) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateTranslationsTrait($fields)
    {
        return $this->prepareFieldsBeforeSaveTranslationsTrait(null, $fields);
    }

    /**
     * @param Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveTranslationsTrait($object, $fields)
    {
        if ($this->model->isTranslatable()) {
            $attributes = Collection::make($this->model->getTranslatedAttributes());
            $translationsFields = $fields['translations'] ?? [];

            // Check if any translated fields are present
            $hasTranslationFields = false;
            foreach ($attributes as $attribute) {
                if (isset($fields[$attribute]) || isset($translationsFields[$attribute])) {
                    $hasTranslationFields = true;

                    break;
                }
            }

            // Only process translations if we have translation fields
            if ($hasTranslationFields) {
                $locales = getLocales();
                $localesCount = count($locales);
                $submittedLanguages = Collection::make($fields['translationLanguages'] ?? []);

                $atLeastOneLanguageIsPublished = $submittedLanguages->contains(function ($language) {
                    return $language['published'];
                });

                foreach ($locales as $index => $locale) {
                    $submittedLanguage = Arr::first($submittedLanguages->filter(function ($lang) use ($locale) {
                        return $lang['value'] == $locale;
                    }));

                    $shouldPublishFirstLanguage = ($index === 0 && ! $atLeastOneLanguageIsPublished);

                    $activeField = $shouldPublishFirstLanguage || (isset($submittedLanguage) ? $submittedLanguage['published'] : false);

                    $fields[$locale] = $attributes->mapWithKeys(function ($attribute) use (&$fields, $locale, $localesCount, $index, $translationsFields) {
                        $attributeValue = $fields[$attribute] ?? $translationsFields[$attribute] ?? null;

                        // if we are at the last locale,
                        // let's unset this field as it is now managed by this trait
                        if ($index + 1 === $localesCount) {
                            unset($fields[$attribute]);
                        }

                        $perLocale = isset($attributeValue[$locale]) ? $attributeValue[$locale] : null;
                        $perLocale = $this->normalizeSlugPayloadForTranslationColumn($attribute, $perLocale);

                        if ($attribute === 'active' && is_null($perLocale)) {
                            $perLocale = false;
                        }

                        return [
                            $attribute => $perLocale,
                        ];
                    })->toArray() + [
                        'active' => $activeField,
                    ];
                }
            }

            // Always clean up the languages field
            unset($fields['translationLanguages']);
        }

        return $fields;
    }

    /**
     * Slug input (manageActive) submits `{ slug: string, active: bool }` per locale; translation rows store only the slug string.
     */
    protected function normalizeSlugPayloadForTranslationColumn(string $attribute, mixed $value): mixed
    {
        if (! is_array($value) || ! array_key_exists('slug', $value)) {
            return $value;
        }

        if (! method_exists($this->model, 'getSlugAttributes')) {
            return $value;
        }

        if (! in_array($attribute, $this->model->getSlugAttributes(), true)) {
            return $value;
        }

        $slug = $value['slug'];

        return $slug === null || $slug === '' ? '' : (string) $slug;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsTranslationsTrait($object, $fields)
    {
        $translatedAttributes = $object->getTranslatedAttributes();

        unset($fields['translations']);

        if ($object->translations != null && $translatedAttributes != null) {
            foreach ($object->translations as $translation) {
                foreach ($translatedAttributes as $attribute) {
                    unset($fields[$attribute]);

                    if (array_key_exists($attribute, $this->fieldsGroups) && is_array($translation->{$attribute})) {
                        // foreach ($this->fieldsGroups[$attribute] as $field_name) {
                        //     if (isset($translation->{$attribute}[$field_name])) {
                        //         if ($this->fieldsGroupsFormFieldNamesAutoPrefix) {
                        //             $fields['translations'][$attribute . $this->fieldsGroupsFormFieldNameSeparator . $field_name][$translation->locale] = $translation->{$attribute}[$field_name];
                        //         } else {
                        //             $fields['translations'][$field_name][$translation->locale] = $translation->{$attribute}[$field_name];
                        //         }
                        //     }
                        // }
                        // unset($fields['translations'][$attribute]);
                    } else {
                        $fields['translations'][$attribute][$translation->locale] = $translation->{$attribute};
                    }
                }
            }
        }

        return $fields;
    }

    protected function filterTranslationsTrait($query, &$scopes)
    {
        if ($this->model->isTranslatable()) {
            $attributes = $this->model->getTranslatedAttributes();

            $translatableValues = [];
            if (isset($scopes['searches'])) {
                foreach ($scopes['searches'] as $field) {
                    if (isset($scopes[$field]) && in_array($field, $attributes)) {
                        $translatableValues[$field] = $scopes[$field];
                    }
                }
            }

            if (count($translatableValues) > 0) {
                $query->orWhereHas('translations', function ($q) use ($translatableValues) {
                    $q->where(function ($q) use ($translatableValues) {
                        foreach ($translatableValues as $field => $value) {
                            $q->orWhere($field, $this->getLikeOperator(), '%' . $value . '%');
                        }
                    });
                });
            }

            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute, $scopes)) {
                    unset($scopes[$attribute]);
                }
            }
        }
    }

    /**
     * @param Builder $query
     * @param array $orders
     * @return void
     */
    public function orderTranslationsTrait($query, &$orders)
    {
        if ($this->model->isTranslatable()) {
            $attributes = $this->model->getTranslatedAttributes();
            $table = $this->model->getTable();
            $tableTranslation = $this->model->translations()->getRelated()->getTable();
            $foreignKeyMethod = method_exists($this->model->translations(), 'getQualifiedForeignKeyName') ? 'getQualifiedForeignKeyName' : 'getForeignKey';
            $foreignKey = $this->model->translations()->$foreignKeyMethod();

            $isOrdered = false;

            foreach ($attributes as $attribute) {
                if (isset($orders[$attribute])) {
                    $query->orderBy($tableTranslation . '.' . $attribute, $orders[$attribute]);
                    $isOrdered = true;
                    unset($orders[$attribute]);
                }
            }

            if ($isOrdered) {
                $query
                    ->join($tableTranslation, $foreignKey, '=', $table . '.id')
                    ->where($tableTranslation . '.locale', '=', $orders['locale'] ?? app()->getLocale())
                    ->select($table . '.*');

                unset($orders['locale']);
            }
        }
    }

    /**
     * After save, re-enable timestamps and touch the parent model
     * only when a translation row really changed (ignoring auto-timestamp
     * columns that the translation table may carry).
     *
     * @param \Illuminate\Database\Eloquent\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveTranslationsTrait($object, $fields)
    {
        if (! $this->model->isTranslatable()) {
            return;
        }

        if ($object->relationLoaded('translations')) {
            $timestampKeys = ['updated_at', 'created_at', 'deleted_at'];

            foreach ($object->translations as $translation) {
                $changedKeys = array_keys($translation->getChanges());
                $meaningfulChanges = array_diff($changedKeys, $timestampKeys);

                if (! empty($meaningfulChanges)) {
                    $this->letEloquentModelBeTouched(true);

                    break;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getPublishedScopesTranslationsTrait()
    {
        return ['withActiveTranslations'];
    }
}
