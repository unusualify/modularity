<?php

namespace Modules\SystemPricing\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

class VatRate extends \Oobook\Priceable\Models\VatRate
{
    use ModelHelpers;

    public $fillable = [
        'name',
        'slug',
        'rate',
    ];

    protected $appends = [
        'name_with_rate',
    ];

    protected function nameWithRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->name . ' (' . $this->rate . '%)',
        );
    }
}
