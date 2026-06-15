<?php

namespace Unusualify\Modularous\Entities;

use Carbon\Carbon;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Notification\Events\ModelCreated;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Contracts\ModuleableInterface;
use Unusualify\Modularous\Entities\Traits\Core\HasCaching;
use Unusualify\Modularous\Entities\Traits\Core\LocaleTags;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularous\Entities\Traits\IsTranslatable;
use Unusualify\Modularous\Traits\Traitify;

abstract class Model extends LaravelModel implements CacheableInterface, ModuleableInterface, TaggableInterface
{
    use IsTranslatable,
        ModelHelpers,
        SoftDeletes,
        TaggableTrait,
        LocaleTags,
        Notifiable,
        HasCaching,
        Traitify;

    public $timestamps = true;

    // protected $dispatchesEvents = [
    //     'created' => ModelCreated::class,
    // ];

    protected function isTranslationModel(): bool
    {
        return Str::endsWith(get_class($this), 'Translation') || property_exists($this, 'baseModuleModel');
    }

    // public function setPublishStartDateAttribute($value): void
    // {
    //     $this->attributes['publish_start_date'] = $value ?? Carbon::now();
    // }

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

            if (! collect($fillable)->contains('locale')) {
                $fillable[] = 'locale';
            }

            if (! collect($fillable)->contains('active')) {
                $fillable[] = 'active';
            }
        }

        if (in_array('Unusualify\Modularous\Entities\Traits\HasAuthorizable', class_uses_recursive(static::class))) {
            $fillable = array_merge($fillable, static::$hasAuthorizableFillable ?? []);
        }

        // if (in_array('Unusualify\Modularous\Entities\Traits\HasStateable', class_uses_recursive(static::class))) {
        //     $fillable = array_merge($fillable, static::$hasStateableFillable ?? []);
        // }

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
            modularousConfig('tables.tagged', 'tagged'),
            'taggable_id',
            'tag_id'
        );
    }

    /**
     * Override newInstance to propagate dependentWarmingEnabled to new instances.
     *
     * Laravel's Builder::create() calls $this->model->newInstance($attributes)
     * to build a fresh model before saving. By overriding this, the flag set via
     * preventDependentWarming() on the parent naturally flows to the child,
     * ensuring the observer sees the correct value on the actual persisted instance.
     *
     * @param array $attributes
     * @param bool $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $instance = parent::newInstance($attributes, $exists);

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $instance = $this->$method($instance, $attributes, $exists);
        }

        return $instance;
    }
}
