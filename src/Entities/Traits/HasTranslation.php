<?php

namespace Unusualify\Modularity\Entities\Traits;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Facades\TwillCapsules;

trait HasTranslation
{
    use Translatable;

    public static function bootHasTranslation(): void
    {
        if(method_exists(self::class, 'isSoftDeletable') && self::isSoftDeletable()){
            static::forceDeleting(function (Model $model) {
                /* @var Translatable $model */
                return $model->deleteTranslations();
            });
        }
    }
    /**
     * Returns the fully qualified translation class name for this model.
     *
     * @return string|null
     */
    public function getTranslationModelNameDefault()
    {
        $model = modularityConfig('namespace') . "\Entities\Translations\\" . class_basename($this) . 'Translation';

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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder|null
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $orderField
     * @param string $orderType
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $orderRawString
     * @param string $groupByField
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
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
     * @return Illuminate\Support\Collection
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
     * @return Illuminate\Support\Collection
     */
    public function translatedAttribute($key)
    {
        return $this->translations->mapWithKeys(function ($translation) use ($key) {
            return [$translation->locale => $this->translate($translation->locale)->$key];
        });
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
