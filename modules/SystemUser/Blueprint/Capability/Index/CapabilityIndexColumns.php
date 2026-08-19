<?php

namespace Modules\SystemUser\Blueprint\Capability\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class CapabilityIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Title',
                'key' => 'title',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Name',
                'key' => 'name',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Roles',
                'key' => 'roles',
                'itemTitle' => 'title',
            ],
            [
                'title' => 'Routes',
                'key' => 'routes',
                'itemTitle' => 'route_name',
            ],
            [
                'title' => 'Strict Route Binding',
                'key' => 'strict_route_binding',
                'formatter' => [
                    'switch',
                ],
            ],
            [
                'title' => 'Step-Up',
                'key' => 'requires_step_up',
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
