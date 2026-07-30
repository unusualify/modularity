<?php

namespace Modules\SystemUtility\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SystemUser\Entities\Company;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\Publishable;

class Country extends Model
{
    use HasTranslation, Publishable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'phone_code',
    ];

    /**
     * The translated attributes that are assignable for hasTranslation Trait.
     *
     * @var array<int, string>
     */
    public $translatedAttributes = [
        'name',
        'active',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.countries', 'um_countries');
    }
}
