<?php

namespace OoBook\CRM\Base\Entities;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }


}
