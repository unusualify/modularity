<?php

namespace Modules\SystemPricing\Entities;

use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class PriceType extends \Oobook\Priceable\Models\PriceType
{
    use ModelHelpers;

    public $fillable = [
        'name',
        'slug'
    ];
}
