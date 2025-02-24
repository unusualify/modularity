<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait TranslationsTrait
{
    protected $nullableFields = [];

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateTranslationsTrait($fields)
    {
        return $this->prepareFieldsBeforeSaveTranslationsTrait(null, $fields);
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveTranslationsTrait($object, $fields)
    {
        if ($this->model->isTranslatable()) {
            $attributes = Collection::make($this->model->translatedAttributes);
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

                    $fields[$locale] = [
                        'active' => $activeField,
                    ] + $attributes->mapWithKeys(function ($attribute) use (&$fields, $locale, $localesCount, $index, $translationsFields) {
                        $attributeValue = $fields[$attribute] ?? $translationsFields[$attribute] ?? null;

                        // if we are at the last locale,
                        // let's unset this field as it is now managed by this trait
                        if ($index + 1 === $localesCount) {
                            unset($fields[$attribute]);
                        }

                        return [
                            // $attribute => ($attributeValue[$locale] ?? null),
                            $attribute => ($attributeValue[$locale] ?? $attributeValue ?? null),
                        ];
                    })->toArray();
                }
            }

            // Always clean up the languages field
            unset($fields['translationLanguages']);
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsTranslationsTrait($object, $fields)
    {
        unset($fields['translations']);

        if ($object->translations != null && $object->translatedAttributes != null) {
            foreach ($object->translations as $translation) {
                foreach ($object->translatedAttributes as $attribute) {
                    unset($fields[$attribute]);

                    if (array_key_exists($attribute, $this->fieldsGroups) && is_array($translation->{$attribute})) {
                        foreach ($this->fieldsGroups[$attribute] as $field_name) {
                            if (isset($translation->{$attribute}[$field_name])) {
                                if ($this->fieldsGroupsFormFieldNamesAutoPrefix) {
                                    $fields['translations'][$attribute . $this->fieldsGroupsFormFieldNameSeparator . $field_name][$translation->locale] = $translation->{$attribute}[$field_name];
                                } else {
                                    $fields['translations'][$field_name][$translation->locale] = $translation->{$attribute}[$field_name];
                                }
                            }
                        }
                        unset($fields['translations'][$attribute]);
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
            $attributes = $this->model->translatedAttributes;

            $query->whereHas('translations', function ($q) use ($scopes, $attributes) {
                foreach ($attributes as $attribute) {
                    if (isset($scopes[$attribute]) && is_string($scopes[$attribute])) {
                        if (! (isset($scopes['searches']) && in_array($attribute, $scopes['searches']))) {
                            $q->where($attribute, $this->getLikeOperator(), '%' . $scopes[$attribute] . '%');
                        }
                    }
                }

                if (isset($scopes['searches'])) {
                    $q->where(function ($query) use (&$scopes) {
                        foreach ($scopes['searches'] as $field) {
                            $query->orWhere($field, $this->getLikeOperator(), '%' . $scopes[$field] . '%');
                        }
                    });
                }
            });

            // if(get_class_short_name($this) == 'FaqRepository')
            //     dd($scopes, $attributes, $query->toSql(), $query->get());

            foreach ($attributes as $attribute) {
                if (isset($scopes[$attribute])) {
                    unset($scopes[$attribute]);
                }
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $orders
     * @return void
     */
    public function orderTranslationsTrait($query, &$orders)
    {
        if ($this->model->isTranslatable()) {
            $attributes = $this->model->translatedAttributes;
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
