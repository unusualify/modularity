<?php

namespace Modules\SystemUtility\Blueprint\Country\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class CountryIndexColumns implements ModuleRouteHeadersProvider
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
                    'edit',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Status',
                'key' => 'published',
                'formatter' => [
                    'switch',
                ],
            ],
            [
                'title' => 'Code',
                'key' => 'code',
                'searchable' => true,
            ],
            [
                'title' => 'Phone Code',
                'key' => 'phone_code',
                'searchable' => true,
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'long',
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
