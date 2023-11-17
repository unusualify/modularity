<?php

namespace Unusualify\Modularity\Entities;

class Company extends Model
{
    protected $table = 'unusual_companies';

    protected $fillable = [
        'id',
        'public',
        'name',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'phone',
        'vat_number',
        'tax_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

}
