<?php

namespace Modules\SystemPayment\Blueprint\MyPayment\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyPaymentFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'preview',
                'name' => 'files',
                'noSubmit' => true,
                'default' => null,
                'col' => ['cols' => 12, 'class' => 'mb-4'],
                'configuration' => [
                    'tag' => 'v-card',
                    'attributes' => [
                        'link' => true,
                        'class' => 'mx-auto py-4 mb-4 h-100',
                        'variant' => 'elevated',
                        'title' => 'Invoices',
                    ],
                    'elements' => [
                        'tag' => 'v-card-text',
                        'elements' => [
                            'tag' => 'ue-filepond-preview',
                            'attributes' => [
                                'source' => '$invoices',
                                'show-inline-file-name' => true,
                                'max-file-name-length' => 20,
                                'image-size' => 24,
                            ],
                        ],
                    ],
                ],
                // 'conditions' => [
                //     ['invoice', '>', 0],
                // ],
                'creatable' => 'hidden',
            ],
        ];
    }
}
