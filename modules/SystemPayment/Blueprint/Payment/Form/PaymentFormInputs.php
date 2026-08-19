<?php

namespace Modules\SystemPayment\Blueprint\Payment\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\ModuleRoute;

final class PaymentFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'number-input',
                'name' => 'amount',
                'label' => __('Payment Amount'),
                'controlVariant' => 'stacked',
                'col' => ['cols' => 12, 'lg' => 6],
                'allowedRoles' => ['superadmin'],
            ],
            [
                'type' => 'combobox',
                'name' => 'currency_id',
                'label' => __('Currency'),
                'itemTitle' => 'name',
                'itemValue' => 'id',
                'connector' => 'SystemPricing:Currency|repository:list:column=name',
                'default' => 1,
                'rules' => 'required',
                'col' => ['cols' => 12, 'lg' => 6],
                'allowedRoles' => ['superadmin'],
            ],
            [
                'type' => 'text',
                'name' => 'email',
                'label' => __('Payer Email'),
                'col' => ['cols' => 12, 'lg' => 6],
                'allowedRoles' => ['superadmin'],
            ],
            [
                'type' => 'preview',
                'name' => 'description',
                'label' => __('Description'),
                'col' => ['cols' => 12, 'lg' => 12],
                'configuration' => [
                    'elements' => [
                        [
                            'tag' => 'ue-title',
                            'attributes' => [
                                'classes' => 'mb-2',
                                'padding' => 'a-0',
                                'type' => 'body-2',
                            ],
                            'elements' => 'Description',
                        ],
                        [
                            'tag' => 'p',
                            'elements' => '${description??N/A}$',
                        ],
                    ],
                ],
                'conditions' => [
                    ['description', '!=', ''],
                    ['description', '!=', null],
                ],
            ],
            [
                'type' => 'preview',
                'name' => 'bank_receipts',
                'noSubmit' => true,
                'default' => null,
                'col' => ['cols' => 12, 'class' => 'mb-4'],
                'configuration' => [
                    'elements' => [
                        [
                            'tag' => 'ue-title',
                            'attributes' => [
                                'classes' => 'mb-2',
                                'padding' => 'a-0',
                                'type' => 'body-2',
                            ],
                            'elements' => 'Bank Receipts',
                        ],
                        [
                            'tag' => 'ue-filepond-preview',
                            'attributes' => [
                                'source' => '${bank_receipts??N/A}$',
                                'show-inline-file-name' => true,
                                'max-file-name-length' => 30,
                                'image-size' => 24,
                            ],
                        ],
                    ],
                ],
                'conditions' => [
                    ['bank_receipts', '>', 0],
                ],
                'creatable' => 'hidden',
            ],
            [
                'type' => 'select',
                'name' => 'payment_service_id',
                'label' => __('Payment Service'),
                'col' => ['cols' => 12, 'lg' => 6],
                'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                'rules' => 'sometimes|required',
                'editable' => false,
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('Status'),
                'col' => ['cols' => 12, 'lg' => 6],
                'itemTitle' => 'name',
                'itemValue' => 'value',
                'items' => PaymentStatus::cases(),
                'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
                'rules' => 'required',
            ],
            [
                'type' => 'filepond',
                'name' => 'invoice',
                'label' => 'Invoice',
                'max' => 3,
                'conditions' => [
                    ['status', '=', PaymentStatus::COMPLETED, PaymentStatus::REFUNDED, PaymentStatus::CANCELLED],
                ],
                'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
                'acceptedExtensions' => ['pdf'],
            ],
        
        ];
    }
}
