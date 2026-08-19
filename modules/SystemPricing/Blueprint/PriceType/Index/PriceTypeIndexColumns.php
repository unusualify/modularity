<?php

namespace Modules\SystemPricing\Blueprint\PriceType\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PriceTypeIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Name',
                'key' => 'name',
                'formatter' => [
                    0 => 'edit',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    0 => 'date',
                    1 => 'long',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
