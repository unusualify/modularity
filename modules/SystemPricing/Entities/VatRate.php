<?php

namespace Modules\SystemPricing\Entities;

use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class VatRate extends \Oobook\Priceable\Models\VatRate
{
    use ModelHelpers;

    public $fillable = [
        'name',
        'slug',
        'rate',
    ];
}
