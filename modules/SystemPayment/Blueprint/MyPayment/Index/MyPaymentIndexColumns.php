<?php

namespace Modules\SystemPayment\Blueprint\MyPayment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyPaymentIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Amount',
                'key' => 'amount_formatted',
            ],
            [
                'title' => 'Status',
                'key' => 'status',
                'formatter' => [
                    'chip',
                    [
                        'size' => 'small',
                    ],
                ],
            ],
            [
                'title' => 'User Email',
                'key' => 'creator',
                'itemTitle' => 'email',
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'visible' => false,
                'formatter' => [
                    'date',
                    'medium',
                ],
                'searchable' => false,
                'sortable' => true,
            ],
            [
                'title' => 'Last Update',
                'key' => 'updated_at',
                'formatter' => [
                    'date',
                    'numeric',
                ],
                'searchable' => false,
                'sortable' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
