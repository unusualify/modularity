<?php

namespace Unusualify\Modularity\Entities;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use Translatable;

    /**
     * Returns the fully qualified translation class name for this model.
     *
     * @return string|null
     */
    public function getTranslationModelNameDefault()
    {
        $model = config('unusual.namespace') . "\Entities\Translations\\" . class_basename($this) . 'Translation';

        if (@class_exists($model)) {
            return $model;
        }
        //TODO: Fix this while creating a package for State
        $model = class_namespace($this) . "\Translations\\" . class_basename($this) . 'Translation';

        if (@class_exists($model)) {
            return $model;
        }
        dd(
            $model,
            class_namespace($this),

            get_class($this)
        );

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'published',
        'code',
        'icon',
        'color',
    ];

    /**
     * The translated attributes that are assignable for hasTranslation Trait.
     *
     * @var array<int, string>
     */
    public $translatedAttributes = [
        'name',
        'active',
    ];
}
