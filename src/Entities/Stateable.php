<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class Stateable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'state_id',
        'stateable_id',
        'stateable_type',
        'color',
        'icon',
        'is_active',
    ];

    public function getTable()
    {
        return modularityConfig('tables.stateables', 'modularity_stateables');
    }
}
