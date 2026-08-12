<?php

namespace Modules\SystemPayment\Blueprint\PaymentCountry\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCountryIndexColumns implements ModuleRouteHeadersProvider
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
                'title' => 'EUR VAT Rate',
                'key' => 'EURVatRate',
            ],
            [
                'title' => 'USD VAT Rate',
                'key' => 'USDVatRate',
            ],
            [
                'title' => 'TRY VAT Rate',
                'key' => 'TRYVatRate',
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
