<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Unusualify\Modularity\Database\Factories\CompanyFactory;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class Company extends Model
{
    use HasFactory,
        HasSpreadable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory() : Factory
    {
        return CompanyFactory::new();
    }

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

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getTable()
    {
        return modularityConfig('tables.companies', parent::getTable());
    }

}
