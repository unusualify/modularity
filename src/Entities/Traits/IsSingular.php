<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Scopes\SingularScope;
use Unusualify\Modularity\Facades\Modularity;

trait IsSingular
{
    private static $isSingularSelfAttributes = ['singleton_type', 'content'];

    public static function bootIsSingular()
    {
        static::addGlobalScope(new SingularScope);

        self::creating(static function (Model $model) {
            $model->setAttribute('singleton_type', static::class);
            $model->setAttribute('content', Collection::make($model->fillable)
                ->filter(fn ($attribute) => ! in_array($attribute, self::$isSingularSelfAttributes) && in_array($attribute, $model->getFillable()))
                ->mapWithKeys(fn ($attribute) => [$attribute => $model->{$attribute}])
                ->toArray());

            foreach ($model->fillable as $attribute) {
                if (! in_array($attribute, self::$isSingularSelfAttributes)) {
                    $model->offsetUnset($attribute);
                }
            }
        });

        self::updating(static function (Model $model) {
            $model->setAttribute('content', Collection::make($model->fillable)
                ->filter(fn ($attribute) => ! in_array($attribute, self::$isSingularSelfAttributes) && in_array($attribute, $model->getFillable()))
                ->mapWithKeys(fn ($attribute) => [$attribute => $model->{$attribute}])
                ->toArray());

            foreach ($model->fillable as $attribute) {
                if (! in_array($attribute, self::$isSingularSelfAttributes)) {
                    $model->offsetUnset($attribute);
                }
            }
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

    final public function getTable()
    {
        return Modularity::config('tables.singletons', 'modularity_singletons');
    }
}
