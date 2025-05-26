<?php

namespace Modules\SystemUtility\Entities;

use Modules\SystemUser\Entities\Company;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class Country extends Model
{
    use HasTranslation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'published',
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

    public function companies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function getTable(): string
    {
        return modularityConfig('tables.countries', 'um_countries');
    }
}
