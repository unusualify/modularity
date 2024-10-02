<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class Authorized extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'authorizedable_type',
        'authorizedable_id',
    ];

    public $timestamps = false;

    /**
     * get the parent authorizedable model
     */
    public function authorizedable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTable()
    {
        return unusualConfig('tables.authorizeds', 'modularity_authorizeds');
    }
}
