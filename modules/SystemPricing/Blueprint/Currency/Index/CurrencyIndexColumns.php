<?php

namespace Modules\SystemPricing\Blueprint\Currency\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class CurrencyIndexColumns implements ModuleRouteHeadersProvider
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
                'title' => 'Symbol',
                'key' => 'symbol',
                'searchable' => true,
                'formatter' => [
                    'chip',
                    [
                        'color' => 'success',
                    ],
                ],
            ],
            [
                'title' => 'ISO 4217',
                'key' => 'iso_4217',
                'searchable' => true,
                'formatter' => [
                    'chip',
                    [
                        'color' => 'primary',
                    ],
                ],
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
