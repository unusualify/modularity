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

    public function getTable()
    {
        return modularityConfig('tables.states', 'um_states');
    }
}
