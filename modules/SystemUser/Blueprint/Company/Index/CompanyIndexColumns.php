<?php

namespace Modules\SystemUser\Blueprint\Company\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class CompanyIndexColumns implements ModuleRouteHeadersProvider
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
                'sortable' => true,
                'searchable' => true,
                'formatterName' => 'edit',
                'formatter' => [
                    'shorten',
                    20,
                ],
                'width' => 150,
            ],
            [
                'title' => 'Country',
                'key' => 'country_name',
                'align' => 'start',
                'groupable' => true,
                // 'sortable' => true,
                // 'searchable' => true,
            ],
            [
                'title' => 'Type',
                'key' => 'company_type',
                'align' => 'start',
                'groupable' => true,
            ],
            [
                'title' => 'Valid',
                'key' => 'is_valid_formatted',
                'align' => 'start',
                'groupable' => true,
                'formatter' => [
                    'dynamic',
                ],
            ],
            [
                'title' => 'Users',
                'key' => 'users',
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'align' => 'center',
                'sortable' => false,
                'width' => '15%',
        
                'class' => 'actions-extra',
            ],
        ];
    }
}
