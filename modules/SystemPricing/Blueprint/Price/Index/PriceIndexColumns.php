<?php

namespace Modules\SystemPricing\Blueprint\Price\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PriceIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Id',
                'key' => 'id',
            ],
            [
                'title' => 'Priceable',
                'key' => 'priceable_type',
            ],
            [
                'title' => 'Priceable Id',
                'key' => 'priceable_id',
            ],
        ];
    }
}
