<?php

namespace Unusualify\Modularous\Entities\Traits;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Facades\TwillCapsules;

trait HasTranslation
{
    use Translatable;

    private $translationFillingIsActive = true;

    public static function bootHasTranslation(): void
    {
        static::saving(function (Model $model) {
            if ($model->bypassTranslationFilling()) {
                $attributes = $model->getAttributes();
                if (count($attributes) > 0) {
                    foreach ($attributes as $key => $value) {
                        $model->offsetUnset($key);
                    }
                    $attributes = $model->handleTranslationAttributes($attributes);

                    foreach ($attributes as $key => $value) {
                        $model->setAttribute($key, $value);
                    }
                }
            }
        });

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::forceDeleting(function (Model $model) {
                /* @var Translatable $model */
                return $model->deleteTranslations();
            });
        } else {
            static::deleting(function (Model $model) {
                /* @var Translatable $model */
                return $model->deleteTranslations();
            });
        }
    }

    public function initializeHasTranslation()
    {
        if ($this->useTranslatedAttributeTransformation()) {
            $this->mergeFillable($this->getTranslatedAttributes());
        }
    }

    /**
     * Disable translation filling for the model.
     */
    public function disableTranslationFilling()
    {
        $this->translationFillingIsActive = false;
    }

    /**
     * Enable translation filling for the model.
     */
    public function enableTranslationFilling()
    {
        $this->translationFillingIsActive = true;
    }

    protected function useTranslatedAttributeTransformation(): bool
    {
        return isset($this->transformTranslatedAttributes) ? (bool) $this->transformTranslatedAttributes : false;
    }

    protected function bypassTranslationFilling()
    {
        return $this instanceof Pivot && ! $this->exists;
    }

    public function setAttribute($key, $value)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if (! $this->bypassTranslationFilling() && $this->isTranslationAttribute($attribute)) {
            $this->getTranslationOrNew($locale)->$attribute = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function fillingTranslatable(array $attributes): array
    {
        return $attributes;
    }

    protected function handleTranslationAttributes(array $attributes)
    {
        if ($this->useTranslatedAttributeTransformation()) {
            $locales = getLocales();
            $newAttributes = array_diff_key($attributes, array_flip($this->getTranslatedAttributes()));

            foreach ($attributes as $key => $values) {
                if ($this->isTranslationAttribute($key)) {
                    foreach ($locales as $locale) {
                        if (is_array($values) && array_key_exists($locale, $values)) {
                            $newAttributes[$locale] = array_merge(
                                ['active' => true],
                                $attributes[$locale] ?? [],
                                [$key => $values[$locale] ?? '']
                            );

                            // unset($newAttributes[$key]);
                        } elseif (is_array($values) && ! array_key_exists($locale, $values)) {
                            $newAttributes[$locale][$key] = '';
                            // unset($newAttributes[$key]);
                        } else {
                            $newAttributes[$locale][$key] ??= $values;
                        }
                    }
                }
            }

            $attributes = $newAttributes;
        }

        foreach ($attributes as $key => $values) {
            if ($this->isWrapperAttribute($key)) {
                $this->fill($values);

                unset($attributes[$key]);

                continue;
            }

            if (
                $this->getLocalesHelper()->has($key)
                && is_array($values)
            ) {
                $this->getTranslationOrNew($key)->fill($values);

                unset($attributes[$key]);

                continue;
            }

            [$attribute, $locale] = $this->getAttributeAndLocale($key);

            if (
                $this->getLocalesHelper()->has($locale)
                && $this->isTranslationAttribute($attribute)
            ) {

                $this->getTranslationOrNew($locale)->fill([$attribute => $values]);

                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    public function fill(array $attributes)
    {
        $attributes = $this->fillingTranslatable($attributes);

        if ($this->translationFillingIsActive) {
            if (! $this->bypassTranslationFilling()) {
                $attributes = $this->handleTranslationAttributes($attributes);
            }
        }

        return parent::fill($attributes);
    }

    /**
     * Returns the fully qualified translation class name for this model.
     *
     * @return string|null
     */
    public function getTranslationModelNameDefault()
    {
        $model = modularousConfig('namespace') . "\Entities\Translations\\" . class_basename($this) . 'Translation';

        if (@class_exists($model)) {
            return $model;
        }

        $model = class_namespace($this) . "\Translations\\" . class_basename($this) . 'Translation';

        if (@class_exists($model)) {
            return $model;
        }
        dd(
            $model,
            class_namespace($this),

            get_class($this)
        );

        return TwillCapsules::getCapsuleForModel(class_basename($this))->getTranslationModel();
    }

    /**
     * @param Builder $query
     * @param string|null $locale
     * @return Builder|null
     */
    public function scopeWithActiveTranslations($query, $locale = null)
    {
        if (method_exists($query->getModel(), 'translations')) {
            $locale = $locale == null ? app()->getLocale() : $locale;

            $query->whereHas('translations', function ($query) use ($locale) {
                $query->whereActive(true);
                $query->whereLocale($locale);

                if (config('translatable.use_property_fallback', false)) {
                    $query->orWhere('locale', config('translatable.fallback_locale'));
                }
            });

            return $query->with(['translations' => function ($query) use ($locale) {
                $query->whereActive(true);
                $query->whereLocale($locale);

                if (config('translatable.use_property_fallback', false)) {
                    $query->orWhere('locale', config('translatable.fallback_locale'));
                }
            }]);
        }
    }

    /**
     * @param Builder $query
     * @param string $orderField
     * @param string $orderType
     * @param string|null $locale
     * @return Builder
     */
    public function scopeOrderByTranslation($query, $orderField, $orderType = 'ASC', $locale = null)
    {
        $translationTable = $this->getTranslationsTable();
        $localeKey = $this->getLocaleKey();
        $table = $this->getTable();
        $keyName = $this->getKeyName();
        $locale = $locale == null ? app()->getLocale() : $locale;

        return $query
            ->join($translationTable, function (JoinClause $join) use ($translationTable, $localeKey, $table, $keyName) {
                $join
                    ->on($translationTable . '.' . $this->getTranslationRelationKey(), '=', $table . '.' . $keyName)
                    ->where($translationTable . '.' . $localeKey, $this->locale());
            })
            ->where($translationTable . '.' . $this->getLocaleKey(), $locale)
            ->orderBy($translationTable . '.' . $orderField, $orderType)
            ->select($table . '.*')
            ->with('translations');
    }

    /**
     * @param Builder $query
     * @param string $orderRawString
     * @param string $groupByField
     * @param string|null $locale
     * @return Builder
     */
    public function scopeOrderByRawByTranslation($query, $orderRawString, $groupByField, $locale = null)
    {
        $translationTable = $this->getTranslationsTable();
        $table = $this->getTable();
        $locale = $locale == null ? app()->getLocale() : $locale;

        return $query->join("{$translationTable} as t", "t.{$this->getTranslationRelationKey()}", '=', "{$table}.id")
            ->where($this->getLocaleKey(), $locale)
            ->groupBy("{$table}.id")
            ->groupBy("t.{$groupByField}")
            ->select("{$table}.*")
            ->orderByRaw($orderRawString)
            ->with('translations');
    }

    /**
     * Checks if this model has active translations.
     *
     * @param string|null $locale
     * @return bool
     */
    public function hasActiveTranslation($locale = null)
    {
        $locale = $locale ?: $this->locale();

        $translations = $this->memoizedTranslations ?? ($this->memoizedTranslations = $this->translations()->get());

        foreach ($translations as $translation) {
            if ($translation->getAttribute($this->getLocaleKey()) == $locale && $translation->getAttribute('active')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection
     */
    public function getActiveLanguages()
    {
        return Collection::make(getLocales())->map(function ($locale) {
            $translation = $this->translations->firstWhere('locale', $locale);

            return [
                'shortlabel' => mb_strtoupper($locale),
                'label' => getLabelFromLocale($locale),
                'value' => $locale,
                'published' => $translation->active ?? false,
            ];
        })->values();
    }

    /**
     * Returns all translations for a given attribute.
     *
     * @param string $key
     * @return Collection
     */
    public function translatedAttribute($key, $locale = null)
    {
        $translations = $this->translations->mapWithKeys(function ($translation) use ($key) {
            return [$translation->locale => $this->translate($translation->locale)->$key];
        });

        if ($locale) {
            // get first translation if not found
            return $translations[$locale] ?? $translations->first() ?? null;
        }

        return $translations;
    }

    /**
     * Get the translated attributes for the model.
     *
     * @return array
     */
    public function getTranslatedAttributes()
    {
        return $this->translatedAttributes ?? [];
    }

    /**
     * Get the translated attribute value for a specific locale.
     *
     * @param string $key The attribute name
     * @param string|null $locale The locale to get the value for (defaults to current locale)
     * @return mixed The translated attribute value
     */
    public function getTranslatedAttribute($key, $locale = null)
    {
        $locale = $locale ?: $this->locale();

        return $this->translate($locale)->$key;
    }

    // /**
    //  * Scope a query to find models by a translated attribute value for a specific locale.
    //  *
    //  * @param \Illuminate\Database\Eloquent\Builder $query
    //  * @param string $attribute The translated attribute name
    //  * @param mixed $value The value to search for
    //  * @param string|null $locale The locale to search in (defaults to current locale)
    //  * @return \Illuminate\Database\Eloquent\Builder
    //  */
    // public function scopeWhereTranslation($query, $attribute, $value, $locale = null)
    // {
    //     $locale = $locale ?: app()->getLocale();
    //     $translationTable = $this->getTranslationsTable();

    //     return $query->whereHas('translations', function($q) use ($attribute, $value, $locale) {
    //         $q->where('locale', $locale)
    //           ->where($attribute, $value);
    //     });
    // }

    /**
     * Get the translations table name.
     *
     * @return string
     */
    // protected function getTranslationsTable()
    // {
    //     return $this->translations()->getRelated()->getTable();
    // }
}
