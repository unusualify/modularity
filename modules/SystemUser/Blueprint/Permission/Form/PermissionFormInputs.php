<?php

namespace Modules\SystemUser\Blueprint\Permission\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PermissionFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'text',
                'title' => 'Name',
                'name' => 'name',
                'label' => 'Permission Name',
                'placeholder' => '',
                'default' => '',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'rules' => 'sometimes|required|min:3',
                // 'prepend-icon' => 'mdi-card-text-outline',
            ],
            [
                'type' => 'text',
                'title' => 'Guard Name',
                'name' => 'guard_name',
                'label' => 'Guard Name',
                'placeholder' => 'web',
                'default' => 'modularous',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                // 'prepend-icon' => 'mdi-account-child',
                'readonly',
                'disabled',
                'flat',
                'hide-spin-buttons',
                // 'full-width',
            ],
            // [
            //     'title' => 'Permission',
            //     'name' => 'permissions',
            //     // 'type' => 'radio',
            //     'type' => 'select',
            //     'default' => 0,
            //     'items' => [
            //         [
            //             'text' => 'Edit Role',
            //             'value' => 0,
            //             'disabled' => false,
            //         ],
            //         [
            //             'text' => 'Create Role',
            //             'value' => 1
            //         ],
            //         [
            //             'text' => 'Delete Role',
            //             'value' => 2
            //         ],
            //     ],
            //     'cols' => 12,
            //     'md' => 12,
            //     'sm' => 12,
            //     'props' => [
            //         'color' => 'success',
            //         'mandatory',
            //         'row',
            //         'outlined',
            //         'dense',
            //         'menu-props' => [
            //             'closeOnClick' => true,
            //             'closeOnContentClick' => false,
            //             'disableKeys' => true,
            //             'openOnClick' => false,
            //             'maxHeight' => 304
            //         ]
            //     ],
            // ],
        
            // [
            //     'title' => 'Activity of Permission',
            //     'name' => 'is_active',
            //     'type' => 'checkbox',
            //     // 'type' => 'switch',
            //     'default' => true,
            //     'cols' => 6,
            //     'md' => 9,
            //     'sm' => 12,
            //     'props' => [
            //         'color' => 'success',
            //         // 'readonly',
            //         'dense',
            //         // 'disabled',
            //         // 'error',
            //         'flat',
            //         'full-width',
            //         'hide-spin-buttons',
            //         // 'false-value' => false,
        
            //         // 'false-value' => true,
            //         // 'true-value' => false,
            //         // 'appendIcon' => 'mdi-dropbox',
            //         // 'prependIcon' => 'mdi-radioactive',
            //         // 'offIcon' => 'mdi-inactive',
            //         // 'onIcon' => 'mdi-radioactive',
            //     ],
            // ],
            // [
            //     'title' => 'Status',
            //     'name' => 'status',
            //     // 'type' => 'radio',
            //     'type' => 'radio',
            //     'options' => [
            //         [
            //             'label' => 'WAITING',
            //             'value' => 0
            //         ],
            //         [
            //             'label' => 'FAILURE',
            //             'value' => 1
            //         ],
            //         [
            //             'label' => 'COMPLETED',
            //             'value' => 2
            //         ],
            //     ],
            //     'default' => 0,
            //     'cols' => 12,
            //     'md' => 12,
            //     'sm' => 12,
            //     'props' => [
            //         'activeClass' => '',
            //         'color' => 'success',
        
            //         'mandatory',
            //         'row',
        
            //         'props' => [
            //             'color' => 'error',
            //             'on-icon' => '$radioOn',
            //             'off-icon' => '$radioOff'
            //         ]
        
            //         // 'appendIcon' => 'mdi-dropbox',
            //         // 'prependIcon' => 'mdi-radioactive',
            //         // 'offIcon' => 'mdi-inactive',
            //         // 'onIcon' => 'mdi-radioactive',
            //     ],
            // ],
            // [
            //     'title' => 'Report',
            //     'name' => 'report',
            //     'type' => 'file',
            //     // 'accept' => "image/*,.doc,.docx,.pdf",
            //     'cols' => 12,
            //     'md' => 12,
            //     'sm' => 12,
            //     'props' => [
            //         'small-chips',
            //         'prependIcon' => '',
            //         'prependInnerIcon' => 'mdi-camera'
        
            //     ]
            // ],
            // [
            //     'title' => 'Day Interval',
            //     'name' => 'day_interval',
            //     'type' => 'range',
            //     'default' => [0,100],
            //     'cols' => 12,
            //     'md' => 12,
            //     'sm' => 12,
            //     'props' => [
            //         'max' => 100,
            //         'min' => 0,
            //         'tick-size' => 1,
            //         // 'background-color' => 'success',
            //         'hint' => '',
        
            //         // 'vertical',
            //     ]
            // ],
            // [
            //     'title' => 'Color',
            //     'name' => 'color',
            //     'type' => 'color',
            //     'default' => '#32010121',
            //     'cols' => 12,
            //     'sm' => 12,
            //     'md' => 12,
        
            //     'props' => [
            //         'placeholder' => '#FFDD11FF',
            //         // 'dotSize' => 'rgba',
            //         'prepend-icon' => 'mdi-palette',
            //         'props' => [
            //             'dotSize' => 25,
            //             'maxHeight' => 200,
            //         ]
        
            //     ]
            // ],
            // [
            //     'title' => 'Start Date',
            //     'name' => 'start_date',
            //     'type' => 'date',
            //     'default' => '',
            //     'cols' => 12,
            //     'sm' => 12,
            //     'md' => 12,
            //     'props' => [
            //         'color' => "red lighten-1",
            //         'prepend-icon' => 'mdi-calendar',
            //         // 'prepend-inner-icon' => 'mdi-calendar',
            //         'dense',
            //         'outlined'
            //     ],
            //     'picker_props' => [
            //         'color' => 'success',
            //         'header-color' => 'info',
            //         'min' => "2016-06-15",
            //         'max' => "2018-03-20",
            //         // 'type' => "month",
            //         // 'range',
        
            //         // 'show-adjacent-months',
            //     ]
            // ],
            // [
            //     'title' => 'Start Time',
            //     'name' => 'start_time',
            //     'type' => 'time',
            //     'default' => '',
            //     'cols' => 12,
            //     'sm' => 12,
            //     'md' => 12,
            //     'props' => [
            //         'color' => "red lighten-1",
            //         'prepend-icon' => 'mdi-calendar',
            //         // 'prepend-inner-icon' => 'mdi-calendar',
            //         'dense',
            //         'outlined'
            //     ],
            //     'picker_props' => [
            //         'color' => 'success',
            //         'header-color' => 'info',
            //         // 'type' => "month",
            //         // 'range',
        
            //         // 'show-adjacent-months',
            //     ]
            // ],
        ];
    }
}
