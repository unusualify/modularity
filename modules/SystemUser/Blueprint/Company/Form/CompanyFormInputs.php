<?php

namespace Modules\SystemUser\Blueprint\Company\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CompanyFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'radio-group',
                'name' => 'is_personal',
                'color' => 'primary',
                'col' => ['cols' => 12],
                'hideDetails' => false,
                'spreadable' => true,
                // 'ext' => [
                //     [
                //         'set',
                //         'name',
                //         'disabled',
                //         'disable_value.*.value',
                //     ],
                //     [
                //         'set',
                //         'tax_id',
                //         'disabled',
                //         'disable_value.*.value',
                //     ],
                //     [
                //         'set',
                //         'phone',
                //         'disabled',
                //         'disable_value.*.value',
                //     ],
                //     [
                //         'set',
                //         'email',
                //         'disabled',
                //         'disable_value.*.value',
                //     ],
                // ],
                'disable_value' => [
                    [
                        'id' => 0,
                        'value' => 0,
                    ],
                    [
                        'id' => 1,
                        'value' => 1,
                    ],
                ],
                'items' => [
                    [
                        'name' => 'Company',
                        'id' => 0,
                    ],
                    [
                        'name' => 'Personal',
                        'id' => 1,
                    ],
                ],
            ],
            [
                'type' => 'text',
                'title' => 'Name',
                'name' => 'name',
                'label' => ' Name',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'max:99',
                // 'rules' => 'sometimes|required|min:3',
            ],
            [
                'type' => 'text',
                'title' => 'Tax ID',
                'name' => 'tax_id',
                'label' => 'Tax ID',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'min:6|max:30',
                // 'rules' => 'sometimes|required|min:5',
            ],
            [
                'type' => 'text',
                'title' => 'Address',
                'name' => 'address',
                'label' => 'Address',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                ],
                'rules' => 'sometimes|required|min:5',
            ],
            [
                'type' => 'select',
                'title' => 'Country',
                'name' => 'country_id',
                'label' => 'Country',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'connector' => 'SystemUtility:Country|repository:list',
                'rules' => 'sometimes|required',
            ],
            [
                'type' => 'text',
                'title' => 'State',
                'name' => 'state',
                'label' => 'State/Province',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'sometimes|required|min:3|max:50',
            ],
            [
                'type' => 'text',
                'title' => 'City',
                'name' => 'city',
                'label' => 'City',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'sometimes|required|min:3|max:50',
            ],
            [
                'type' => 'text',
                'title' => 'Zip Code',
                'name' => 'zip_code',
                'label' => 'Zip Code',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'sometimes|required|min:5|max:30',
            ],
            [
                'type' => 'input-phone',
                'title' => 'Phone',
                'name' => 'phone',
                'label' => 'Phone',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
            ],
            [
                'type' => 'text',
                'name' => 'email',
                'label' => 'Work E-mail',
                'default' => '',
                'col' => ['sm' => 6],
                'spreadable' => true,
            ],
            // [
            //     'type' => 'text',
            //     'title' => 'Vat Number',
            //     'name' => 'vat_number',
            //     'label' => 'Vat Number',
            //     'placeholder' => '',
            //     'default' => '',
            //     'col' => [
            //         'cols' => 12,
            //         'sm' => 8,
            //         'md' => 6,
            //     ],
            //     'rules' => 'sometimes|required|min:5',
            // ],

        ];
    }
}
