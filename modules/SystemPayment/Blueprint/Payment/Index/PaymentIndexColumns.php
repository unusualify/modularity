<?php

namespace Modules\SystemPayment\Blueprint\Payment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Owner Id',
                'key' => 'price.priceable',
                'itemTitle' => 'id',
                'allowedRoles' => ['superadmin'],
                'formatter' => [
                    'edit',
                ],
                // 'itemTitle' => 'content->headline',
            ],
            [
                'title' => 'Owner Type',
                'key' => 'price',
                'itemTitle' => 'priceable_type',
                'allowedRoles' => ['superadmin'],
                'groupable' => true,
                'groupOrder' => 'asc',
                // 'itemTitle' => 'content->headline',
            ],
            [
                'title' => 'Related',
                'key' => 'paymentable',
                'itemTitle' => 'id',
                'allowedRoles' => ['superadmin'],
            ],
            [
                'title' => 'Company',
                'key' => 'creator.company',
                'itemTitle' => 'name',
                'minWidth' => 150,
                'searchable' => true,
                'searchKey' => 'creator.company.name',

                'groupable' => true,
                'groupOrder' => 'asc',
            ],
            [
                'title' => 'Service',
                'key' => 'paymentService',
                // 'itemTitle' => 'title',
                'formatter' => [
                    'chip',
                    [
                        'variant' => 'outlined',
                        'color' => 'primary',
                    ],
                ],
                'groupable' => true,
            ],
            [
                'title' => 'Total Price',
                'key' => 'amount_formatted',
            ],
            [
                'title' => 'Status',
                'key' => 'status_vuetify_chip',
                'formatter' => [
                    'dynamic',
                ],
                'groupable' => true,
                'groupOrder' => 'asc',
            ],
            [
                'title' => 'User Email',
                'key' => 'creator',
                'itemTitle' => 'email',
                'searchable' => true,
                'searchKey' => 'creator.email',
                'groupable' => true,
                'groupOrder' => 'asc',
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
                    'long',
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
