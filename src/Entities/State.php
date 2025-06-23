<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class State extends Model
{
    use HasTranslation;

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
        // TODO: Fix this while creating a package for State
        $model = class_namespace($this) . "\Translations\\" . class_basename($this) . 'Translation';

        if (@class_exists($model)) {
            return $model;
        }

        throw new \Exception('State translation model not found');
    }

    public function getTable()
    {
        return modularityConfig('tables.states', 'um_states');
    }
}
