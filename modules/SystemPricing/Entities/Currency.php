<?php

namespace Modules\SystemPricing\Entities;

use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class Currency extends \Oobook\Priceable\Models\Currency
{
    use ModelHelpers;

    protected $fillable = [
        'name',
        'symbol',
        'iso_4217',
        'iso_4217_number',
    ];
}
