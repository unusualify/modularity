<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;

class UserOauth extends BaseModel
{
    protected $fillable = [
        'token',
        'provider',
        'avatar',
        'oauth_id',
        'user_id',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = modularityConfig('tables.user_oauths', 'um_user_oauths');

        parent::__construct($attributes);
    }

    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }
}
