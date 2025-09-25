<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class CreatorRecord extends Model
{
    protected $fillable = [
        'id',
        'creator_type',
        'creator_id',
        'guard_name',
        'creatable_type',
        'creatable_id',
    ];

    public $timestamps = false;

    /**
     * get the parent creatable model
     */
    public function creatable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularityConfig('tables.creator_records', 'um_creator_records');
    }
}
