<?php

namespace Unusualify\Modularity\Entities;

class Profile extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'surname',
        'phone',
        'country',
        'language',
        'timezone'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }


}
