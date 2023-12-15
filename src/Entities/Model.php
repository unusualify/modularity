<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\{
    HasPresenter,
    HasHelpers,
    HasScopes,
    HasRelation,
    IsTranslatable
};


abstract class Model extends BaseModel implements TaggableInterface
{
    use HasPresenter, HasHelpers, HasScopes, SoftDeletes, TaggableTrait, IsTranslatable, HasRelation;

    public $timestamps = true;

    protected function isTranslationModel()
    {
        return Str::endsWith(get_class($this), 'Translation');
    }

    public function setPublishStartDateAttribute($value)
    {
        $this->attributes['publish_start_date'] = $value ?? Carbon::now();
    }

    public function getFillable()
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
