<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\{
    HasPresenter,
    ModelHelpers,
    HasScopes,
    HasRelation,
    HasScanModule,
    IsTranslatable
};


class Model extends LaravelModel implements TaggableInterface
{
    use ModelHelpers,
        HasPresenter,
        HasRelation,
        // HasScanModule,
        HasScopes,
        IsTranslatable,
        SoftDeletes,
        TaggableTrait;

    public $timestamps = true;

    protected function isTranslationModel(): bool
    {
        return Str::endsWith(get_class($this), 'Translation');
    }

    public function setPublishStartDateAttribute($value): void
    {
        $this->attributes['publish_start_date'] = $value ?? Carbon::now();
    }

    public function getFillable(): array
    {
        // If the fillable attribute is filled, just use it
        $fillable = $this->fillable;

        // If fillable is empty
        // and it's a translation model
        // and the baseModel was defined
        // Use the list of translatable attributes on our base model
        if (
            blank($fillable) &&
            $this->isTranslationModel() &&
            property_exists($this, 'baseModuleModel')
        ) {
            $fillable = (new $this->baseModuleModel)->getTranslatedAttributes();

            if (!collect($fillable)->contains('locale')) {
                $fillable[] = 'locale';
            }

            if (!collect($fillable)->contains('active')) {
                $fillable[] = 'active';
            }
        }

        return $fillable;
    }

    // public function getTranslatedAttributes()
    // {
    //     return $this->translatedAttributes ?? [];
    // }

    protected static function bootTaggableTrait()
    {
        static::$tagsModel = Tag::class;
    }

    /**
     * {@inheritdoc}
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            static::$tagsModel,
            'taggable',
            unusualConfig('tables.tagged', 'tagged'),
            'taggable_id',
            'tag_id'
        );
    }

}
