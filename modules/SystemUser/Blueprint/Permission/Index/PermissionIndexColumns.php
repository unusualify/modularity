<?php

namespace Modules\SystemUser\Blueprint\Permission\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PermissionIndexColumns implements ModuleRouteHeadersProvider
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
                'align' => 'start',
                'sortable' => false,
                'searchable' => true,
                'formatter' => [
                    0 => 'edit',
                ],
            ],
            [
                'title' => 'Guard Name',
                'key' => 'guard_name',
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
