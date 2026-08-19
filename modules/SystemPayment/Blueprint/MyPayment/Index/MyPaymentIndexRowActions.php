<?php

namespace Modules\SystemPayment\Blueprint\MyPayment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyPaymentIndexRowActions implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'show' => [
                'name' => 'edit',
                'merge' => true,
                'icon' => 'mdi-eye',
                'label' => __('Show'),
            ],
        ];
    }
}
