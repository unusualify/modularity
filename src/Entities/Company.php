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

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getTable()
    {
        return unusualConfig('tables.companies', parent::getTable());
    }

}
