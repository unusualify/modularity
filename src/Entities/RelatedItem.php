<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;

class RelatedItem extends BaseModel
{
    protected $guarded = [];

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    public function related()
    {
        return $this->morphTo('related');
    }

    public function subject()
    {
        return $this->morphTo('subject');
    }

    public function getTable()
    {
        return modularityConfig('related_table', 'twill_related');
    }
}
