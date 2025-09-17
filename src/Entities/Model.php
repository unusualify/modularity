<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
// use Modules\Notification\Events\ModelCreated;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\HasPresenter;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

abstract class Model extends LaravelModel implements TaggableInterface
{
    use HasPresenter,
        IsTranslatable,
        ModelHelpers,
        SoftDeletes,
        TaggableTrait,
        Notifiable;

    public $timestamps = true;

    // protected $dispatchesEvents = [
    //     'created' => ModelCreated::class,
    // ];

    protected function isTranslationModel(): bool
    {
        return Str::endsWith(get_class($this), 'Translation') || property_exists($this, 'baseModuleModel');
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

            if (! collect($fillable)->contains('locale')) {
                $fillable[] = 'locale';
            }

            if (! collect($fillable)->contains('active')) {
                $fillable[] = 'active';
            }
        }

        if (in_array('Unusualify\Modularity\Entities\Traits\HasAuthorizable', class_uses_recursive(static::class))) {
            $fillable = array_merge($fillable, static::$hasAuthorizableFillable ?? []);
        }

        // if (in_array('Unusualify\Modularity\Entities\Traits\HasStateable', class_uses_recursive(static::class))) {
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
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(
            static::$tagsModel,
            'taggable',
            modularityConfig('tables.tagged', 'tagged'),
            'taggable_id',
            'tag_id'
        );
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding_($value, $field = null)
    {
        return $this->where('name', $value)->firstOrFail();
    }

    /**
     * Retrieve the child model for a bound value.
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding_($childType, $value, $field)
    {
        return parent::resolveChildRouteBinding($childType, $value, $field);
    }
}
