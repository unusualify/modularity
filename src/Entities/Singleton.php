<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Facades\Modularity;

class Singleton extends Model
{
    public $fillable = [
        'id',
        'singleton_type',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function getTable()
    {
        return Modularity::config('tables.singletons', 'modularity_singletons');
    }
}
