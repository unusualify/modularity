<?php

return [
    'user' => [
        'avatar' => [
            'type' => 'filepond-avatar',
            // 'label' => 'Profile Avatar',
            'name' => 'avatar',
            'allow-image-preview' => true,
            'label-idle' => 'Drop files here...',
            'acceptedExtensions' => ['jpg', 'jpeg', 'png'],
        ],
        'name' => [
            'type' => '_name',
            'col' => [
                'sm' => 6,
            ],
            'rules' => 'min:3', // Removed |unique_table since it causes update problems on name. Name shouldn't be unique
        ],
        'surname' => [
            'type' => '_name',
            'name' => 'surname',
            'label' => 'Surname',
            'col' => [
                'sm' => 6,
            ],
            // 'rules' => 'min:2',
        ],
        // 'job_title' => [
        //     'type' => '_name',
        //     'name' => 'job_title',
        //     'label' => 'Job Title',
        //     'col' => [
        //         'sm' => 6,
        //     ],
        //     'rules' => 'min:2',
        // ],
        'email' => [
            'type' => '_email',
            'default' => '',
            'col' => [
                'sm' => 6,
            ],
            'rules' => 'required|email|unique_table',
        ],
        'country_id' => [
            'type' => 'select',
            'name' => 'country_id',
            'label' => 'Country',
            'col' => [
                'sm' => 6,
            ],
            'connector' => 'SystemUtility:Country|repository:list',
            'rules' => 'sometimes|required',
            // 'rules' => 'min:3',
        ],
        'phone' => [
            'type' => '_phone',
            'col' => [
                'sm' => 6,
            ],
        ],
        'language' => [
            'type' => '_language',
            'name' => 'language',
            'label' => 'Preferred Language',
            'col' => ['sm' => 6],
        ],
        // 'timezone' => [
        //     'type' => '_timezone',
        //     'name' => 'timezone',
        //     'col' => ['sm' => 6],
        //     'name' => 'time',
        //     'rules' => 'sometimes|required',
        // ],
    ],
    'user_password' => [
        'current_password' => [
            'type' => '_password',
            'name' => 'current_password',
            'label' => 'Current Password',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|current_password',
        ],
        'gap-1' => [
            'type' => 'v-sheet',
            'name' => 'gap-1',
            'class' => 'd-none d-md-block',
            'col' => [
                'cols' => 0,
                'sm' => 6,
                'class' => 'd-none d-sm-block',
            ],
        ],
        'password_confirmation' => [
            'type' => '_password_confirmation',
            'col' => ['sm' => 6],
        ],
        'password' => [
            'type' => '_password',
            'name' => 'password',
            'label' => 'Confirm Password',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:8|confirmed',
        ],
    ],
    'company' => [
        'is_personal' => [
            'type' => 'checkbox',
            'name' => 'is_personal',
            'color' => 'primary',
            'label' => 'I don\'t have a company',
            'col' => ['cols' => 12],
            'hideDetails' => true,
            'default' => false,
            'trueValue' => true,
            'falseValue' => false,
            'ext' => [
                [
                    'set',
                    'name',
                    'disabled',
                    'disable_value.*.value',
                ],
                [
                    'set',
                    'phone',
                    'disabled',
                    'disable_value.*.value',
                ],
                [
                    'set',
                    'email',
                    'disabled',
                    'disable_value.*.value',
                ],
            ],
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
            'spreadable' => true,
        ],
        'name' => [
            'type' => '_name',
            'label' => 'Company Name',
            'col' => ['sm' => 6],
            // 'rules' => 'sometimes|min:3',
        ],
        'tax_id' => [
            'type' => 'text',
            'name' => 'tax_id',
            'label' => 'Tax ID / Personal ID',
            'default' => '',
            'col' => ['sm' => 6],
            // 'rules' => 'sometimes|min:3',
        ],
        'address' => [
            'type' => 'text',
            'name' => 'address',
            'label' => 'Address',
            'col' => [
                'cols' => 12,
                'sm' => 12,
            ],
        ],
        'country_id' => [
            'type' => 'select',
            'name' => 'country_id',
            'label' => 'Country',
            'default' => '',
            'connector' => 'SystemUtility:Country|repository:list',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|required',
        ],
        'state' => [
            'type' => 'text',
            'name' => 'state',
            'label' => 'State/Province',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'city' => [
            'type' => 'text',
            'name' => 'city',
            'label' => 'City',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'zip_code' => [
            'type' => 'text',
            'name' => 'zip_code',
            'label' => 'ZIP/Postal Code',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'phone' => [
            'type' => '_phone',
            'col' => ['sm' => 6],
        ],
        // 'vat_number' => [
        //     'type' => 'text',
        //     'name' => 'vat_number',
        //     'label' => 'VAT Number',
        //     'default' => '',
        //     'col' => ['sm' => 6],
        //     'rules' => 'sometimes|min:3',
        // ],
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'Work E-mail',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|email',
            'spreadable' => true,
        ],

    ],
    'profile_shortcut' => [
        'id' => [
            'type' => 'hidden',
            'name' => 'id',
        ],
        // 'name' => [
        //     'type' => 'text',
        //     'name' => 'name',
        //     'label' => 'Name',
        //     'col' => ['cols' => 12],
        //     'editable' => false,
        // ],
        // 'email' => [
        //     'type' => 'text',
        //     'name' => 'email',
        //     'label' => 'E-mail',
        //     'rules' => 'email',
        //     'col' => ['cols' => 12],
        //     'editable' => false,
        // ],
        'avatar' => [
            'type' => 'filepond',
            // 'label' => 'Profile Avatar',
            'name' => 'avatar',
            'max' => 1,
            'allowImagePreview' => true,
            'label-idle' => 'Drop files here...',
            'rules' => 'sometimes|required:array',
        ],
    ],
    'login_shortcut' => [
        'timezone' => [
            'type' => '_timezone',
        ],
        'email' => [
            'type' => 'email',
            'name' => 'email',
            'label' => 'E-mail',
        ],
        'password' => [
            'type' => 'password',
            'name' => 'password',
            'label' => 'Password',
        ],
    ],
    'login_form' => [
        'timezone' => [
            'type' => '_timezone',
        ],
        'email' => [
            'type' => 'text',
            'name' => 'email',
            // 'label' => ___('authentication.email'),
            'label' => 'E-mail',
            'hint' => 'enter @example.com',
            'default' => '',
            'col' => [
                'lg' => 12,
            ],
            'rules' => [
                ['email'],
            ],
        ],
        'password' => [
            'type' => 'password',
            'name' => 'password',
            'label' => 'Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'lg' => 12,
            ],
        ],
    ],
    'forgot_password_form' => [
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'Email',
            'default' => '',
        ],
    ],
    'reset_password_form' => [
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'Email',
            'default' => '',
            'col' => [
                'cols' => 12,
            ],
            'readonly' => true,
            'rules' => 'required|email',
        ],
        'password' => [
            'type' => 'password',
            'name' => 'password',
            'label' => 'Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'cols' => 12,
            ],
        ],
        'password_confirmation' => [
            'type' => 'password',
            'name' => 'password_confirmation',
            'label' => 'Password Confirmation',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'cols' => 12,
            ],
        ],
        'token' => [
            'type' => 'hidden',
            // "ext" => "hidden",
            'name' => 'token',
        ],
    ],
    'register_form' => [
        'name' => [
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'default' => '',
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            'rules' => 'min:3',
        ],
        'surname' => [
            'type' => 'text',
            'name' => 'surname',
            'label' => 'Surname',
            'default' => '',
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            'rules' => 'min:2',
        ],
        'company' => [
            'type' => 'text',
            'name' => 'company',
            'label' => 'Company',
            'default' => '',
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            // 'rules' => 'min:2',
        ],
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'E-mail',
            'default' => '',
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            'rules' => 'email',
        ],
        'password' => [
            'type' => 'password',
            'name' => 'password',
            'label' => 'Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            'rules' => [
                ['required', 'classic', null, null, 'Password is required'],
                ['min', 8, 'Password must be at least 8 characters'],
            ],

        ],
        'password_confirmation' => [
            'type' => 'password',
            'name' => 'password_confirmation',
            'label' => 'Repeat Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'cols' => 12,
                'sm' => 6,
            ],
            'rules' => [
                ['required', 'classic', null, null, 'Confirm Password'],
            ],
        ],
        'tos' => [
            'type' => 'input-terms-checkbox',
            'name' => 'tos',
            'label' => 'I accept the terms of service',
            'default' => '',
            'col' => [
                'cols' => 12,
                'lg' => 12,
            ],
            // 'rules' => 'required',
        ],
    ],
    'pre_register_form' => [
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'Email',
            'default' => '',
            'rules' => 'required|email',
            'validateOn' => 'lazy input',
        ],
        'tos' => [
            'type' => 'input-terms-checkbox',
            'name' => 'tos',
            'label' => 'authentication.tos',
            'default' => '',
            'noCheckbox' => true,
            'noHandleClick' => true,
            'default' => 1,
            'col' => [
                'cols' => 12,
                'lg' => 12,
            ],
        ],
    ],
    'complete_register_form' => [
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'Email',
            'col' => ['cols' => 12, 'lg' => 12],
            'readonly' => true,
            'clearable' => false,
            'validateOn' => 'lazy input',
        ],
        'name' => [
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'default' => '',
            'col' => ['cols' => 12, 'lg' => 12],
            'rules' => 'min:2',
            'validateOn' => 'lazy input',
        ],
        'surname' => [
            'type' => 'text',
            'name' => 'surname',
            'label' => 'Surname',
            'default' => '',
            'col' => ['cols' => 12, 'lg' => 12],
            'rules' => 'min:2',
            'validateOn' => 'lazy input',
        ],
        'company' => [
            'type' => 'text',
            'name' => 'company',
            'label' => 'Company',
            'default' => '',
            'col' => ['cols' => 12, 'lg' => 12],
            'rules' => 'min:2',
        ],
        'password' => [
            'type' => 'password',
            'name' => 'password',
            'label' => 'Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => ['cols' => 12, 'lg' => 12],
            'rules' => 'min:8',
        ],
        'password_confirmation' => [
            'type' => 'password',
            'name' => 'password_confirmation',
            'label' => 'Confirm Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => ['cols' => 12, 'lg' => 12],
            'rules' => 'min:8',
        ],
        'token' => [
            'type' => 'hidden',
            'name' => 'token',
        ],
    ],
];
