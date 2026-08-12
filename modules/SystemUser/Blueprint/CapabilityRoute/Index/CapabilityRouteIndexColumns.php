<?php

namespace Modules\SystemUser\Blueprint\CapabilityRoute\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class CapabilityRouteIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Route Name',
                'key' => 'route_name',
                'searchable' => true,
            ],
            [
                'title' => 'Active',
                'key' => 'is_active',
                'formatter' => [
                    'switch',
                ],
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
