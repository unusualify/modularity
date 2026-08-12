<?php

namespace Modules\SystemPayment\Blueprint\Payment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\ModuleRoute;

final class PaymentIndexAdvancedFilters implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'columns' => [
                [
                    'type' => 'select',
                    'slug' => 'status',
                    'componentOptions' => [
                        'multiple' => true,
                        'clearable' => true,
                        'variant' => 'outlined',
                        'label' => 'Status',
                        'itemTitle' => 'name',
                        'itemValue' => 'value',
                        'items' => array_map(function ($status) {
                            return [
                                'name' => $status->label(),
                                'value' => $status->value,
                            ];
                        }, PaymentStatus::cases()),
                    ],
                ],
            ],
            'relations' => [
                [
                    'type' => 'select',
                    'slug' => 'paymentService',
                    'componentOptions' => [
                        'multiple' => true,
                        'clearable' => true,
                        'variant' => 'outlined',
                        'label' => 'Payment Service',
                    ],
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                ],
                [
                    'type' => 'select',
                    'slug' => 'currency',
                    'componentOptions' => [
                        'multiple' => true,
                        'clearable' => true,
                        'variant' => 'outlined',
                        'label' => 'Currency',
                        'itemTitle' => 'iso_4217',
                        'itemValue' => 'id',
                        'connector' => 'SystemPricing|Currency^repository->list?column=iso_4217&scopes=[enabled]',
                    ],
                ],
            ],
        ];
    }
}
