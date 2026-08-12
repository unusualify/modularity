<?php

namespace Modules\SystemUser\Blueprint\User\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class UserIndexColumns implements ModuleRouteHeadersProvider
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
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Surname',
                'key' => 'surname',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Email',
                'key' => 'email',
                'align' => 'start',
                'sortable' => false,
                'searchable' => true,
            ],
            [
                'title' => 'Company',
                'key' => 'company',
            ],
            [
                'title' => 'Roles',
                'key' => 'roles',
                'itemTitle' => 'title',
            ],
            [
                'title' => 'Status',
                'key' => 'published',
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
