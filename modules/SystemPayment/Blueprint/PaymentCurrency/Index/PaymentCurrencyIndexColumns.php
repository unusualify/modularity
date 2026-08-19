<?php

namespace Modules\SystemPayment\Blueprint\PaymentCurrency\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCurrencyIndexColumns implements ModuleRouteHeadersProvider
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
                'title' => 'Services',
                'key' => 'paymentServices',
                'itemTitle' => 'name',
            ],
            [
                'title' => 'Credit Card Service',
                'key' => 'paymentService',
                'itemTitle' => 'name',
            ],
            [
                'title' => __('VAT Rate') . ' (' . __('for personals') . ')',
                'key' => 'personal_vat_rate_name_with_rate',
            ],
            [
                'title' => __('VAT Rate') . ' (' . __('for corporates') . ')',
                'key' => 'corporate_vat_rate_name_with_rate',
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
