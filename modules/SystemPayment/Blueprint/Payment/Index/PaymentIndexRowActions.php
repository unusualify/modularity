<?php

namespace Modules\SystemPayment\Blueprint\Payment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentIndexRowActions implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'cancel' => [
                'name' => 'cancel',
                'icon' => 'mdi-credit-card-remove-outline',
                'color' => 'error',
                'conditions' => [
                    ['is_cancelable', '=', true],
                ],
                'url' => [
                    'payable.cancel',
                    [
                        'payment' => ':id',
                    ],
                ],
                'hasDialog' => true,
                'dialogQuestion' => __('Are you sure you want to cancel this payment?'),
            ],
            'refund' => [
                'name' => 'refund',
                'icon' => 'mdi-credit-card-refund-outline',
                'color' => 'warning',
                'conditions' => [
                    ['is_refundable', '=', true],
                ],
                'url' => [
                    'payable.refund',
                    [
                        'payment' => ':id',
                    ],
                ],
                'hasDialog' => true,
                'dialogQuestion' => __('Are you sure you want to refund this payment?'),
                'allowedRoles' => ['superadmin', 'admin', 'manager'],
            ],
        ];
    }
}
