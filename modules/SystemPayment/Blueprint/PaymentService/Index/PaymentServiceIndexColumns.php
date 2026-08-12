<?php

namespace Modules\SystemPayment\Blueprint\PaymentService\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentServiceIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Service Name',
                'key' => 'name',
                'formatter' => [
                    'edit',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Service Slug',
                'key' => 'key',
                'searchable' => true,
            ],
            [
                'title' => 'Credit Card Currencies',
                'key' => 'internalPaymentCurrencies',
            ],
            [
                'title' => 'External Supported Currencies',
                'key' => 'paymentCurrencies',
            ],
            [
                'title' => 'Transaction Fee (%)',
                'key' => 'transaction_fee_percentage',
                'formatter' => [
                    'chip',
                ],
            ],
            [
                'title' => 'Status',
                'key' => 'published',
                'formatter' => [
                    'switch',
                ],
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'medium',
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
