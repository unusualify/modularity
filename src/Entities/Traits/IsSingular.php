<?php

namespace Unusualify\Modularous\Entities\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Scopes\SingularScope;
use Unusualify\Modularous\Facades\Modularous;

trait IsSingular
{
    private static $isSingularSelfAttributes = ['singleton_type', 'content'];

    /**
     * @return list<string>
     */
    private static function singularContentAttributeNames(Model $model): array
    {
        return array_values(array_filter(
            $model->getFillable(),
            fn ($attribute) => ! in_array($attribute, self::$isSingularSelfAttributes, true)
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveExistingSingularContent(Model $model): array
    {
        $existing = $model->getRawOriginal('content');

        if (is_string($existing)) {
            $existing = json_decode($existing, true) ?: [];
        }

        return is_array($existing) ? $existing : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildSingularContentForUpdate(Model $model): array
    {
        $content = self::resolveExistingSingularContent($model);
        $attributes = $model->getAttributes();

        foreach (self::singularContentAttributeNames($model) as $attribute) {
            if (array_key_exists($attribute, $attributes) || $model->isDirty($attribute)) {
                $content[$attribute] = $model->getAttribute($attribute);
            }
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildSingularContent(Model $model): array
    {
        return Collection::make(self::singularContentAttributeNames($model))
            ->mapWithKeys(fn ($attribute) => [$attribute => $model->getAttribute($attribute)])
            ->toArray();
    }

    private static function unsetSingularContentAttributes(Model $model): void
    {
        foreach (self::singularContentAttributeNames($model) as $attribute) {
            $model->offsetUnset($attribute);
        }
    }

    public static function bootIsSingular()
    {
        static::addGlobalScope(new SingularScope);

        self::creating(static function (Model $model) {
            $model->setAttribute('singleton_type', static::class);
            $model->setAttribute('content', self::buildSingularContent($model));
            self::unsetSingularContentAttributes($model);
        });

        self::updating(static function (Model $model) {
            $model->setAttribute('content', self::buildSingularContentForUpdate($model));
            self::unsetSingularContentAttributes($model);
        });

        self::retrieved(static function (Model $model) {
            if ($model->content) {
                $data = $model->content ?? [];
                foreach ($data as $key => $value) {
                    if (in_array($key, $model->getFillable())) {
                        $model->setAttribute($key, $value);
                    }
                }
            }
            $model->offsetUnset('content');
            $model->offsetUnset('singleton_type');
        });
    }

    public function initializeIsSingular()
    {
        $this->mergeFillable(['singleton_type', 'content']);
        $this->casts['content'] = 'array';
    }

    public static function single()
    {
        return static::query()->firstOrCreate();
    }

    public function scopePublished($query)
    {
        return $query->where("{$this->getTable()}.content->published", true);
    }

    public function isPublished()
    {
        return (bool) ($this->published ?? $this->content['published'] ?? true);
    }

    public function scopeVisible($query)
    {
        // zero hour today
        $now = Carbon::now();
        $startOfDay = $now->startOfDay();
        $endOfDay = $now->endOfDay();

        $query->where(function ($query) use ($startOfDay) {
            $query->whereNull("{$this->getTable()}.content->publish_start_date")
                ->orWhere("{$this->getTable()}.content->publish_start_date", '<=', $startOfDay);
        });

        $query->where(function ($query) use ($endOfDay) {
            $query->whereNull("{$this->getTable()}.content->publish_end_date")
                ->orWhere("{$this->getTable()}.content->publish_end_date", '>=', $endOfDay);
        });

        return $query;
    }

    final public function getTable()
    {
        return Modularous::config('tables.singletons', 'modularous_singletons');
    }
}
