<?php

namespace OoBook\CRM\Base\Entities;

class Company extends Model
{
    protected $fillable = [
        'id',
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
