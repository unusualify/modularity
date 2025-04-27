<?php

return [
    'user' => [
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
        'phone' => [
            'type' => '_phone',
            'col' => [
                'sm' => 6,
            ],
        ],
        'country' => [
            'type' => '_name',
            'name' => 'country',
            'label' => 'Country',
            'col' => [
                'sm' => 6,
            ],
            // 'rules' => 'min:3',
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
            'rules' => 'sometimes|required|current_password',
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
            'rules' => 'sometimes|min:6|confirmed',
        ],
    ],
    'company' => [
        'name' => [
            'type' => '_name',
            'label' => 'Company',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
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
        'city' => [
            'type' => 'text',
            'name' => 'city',
            'label' => 'City',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'state' => [
            'type' => 'text',
            'name' => 'state',
            'label' => 'State/Province',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'country' => [
            'type' => 'text',
            'name' => 'country',
            'label' => 'Country',
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
        'vat_number' => [
            'type' => 'text',
            'name' => 'vat_number',
            'label' => 'VAT Number',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
        ],
        'tax_id' => [
            'type' => 'text',
            'name' => 'tax_id',
            'label' => 'Tax ID',
            'default' => '',
            'col' => ['sm' => 6],
            'rules' => 'sometimes|min:3',
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
            'allow-image-preview' => true,
            'label-idle' => 'Drop files here...',
            'rules' => 'sometimes|required:array',
        ],
    ],
    'login_shortcut' => [
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
                'cols' => 6,
                'lg' => 6,
            ],
            'rules' => 'min:3',
        ],
        'surname' => [
            'type' => 'text',
            'name' => 'surname',
            'label' => 'Surname',
            'default' => '',
            'col' => [
                'cols' => 6,
                'lg' => 6,
            ],
            'rules' => 'min:2'
        ],
        'company' => [
            'type' => 'text',
            'name' => 'company',
            'label' => 'Company',
            'default' => '',
            'col' => [
                'cols' => 6,
                'lg' => 6,
            ],
            'rules' => 'min:2'
        ],
        'email' => [
            'type' => 'text',
            'name' => 'email',
            'label' => 'E-mail',
            'default' => '',
            'col' => [
                'cols' => 6,
                'lg' => 6,
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
                'cols' => 6,
                'lg' => 6,
            ],
            'rules' => [
                ['required', 'classic', null, null, 'Password is required'],
                ['min', 8, 'Password must be at least 8 characters'],
            ],

        ],
        're_password' => [
            'type' => 'password',
            'name' => 're-password',
            'label' => 'Repeat Password',
            'default' => '',
            'appendInnerIcon' => '$non-visibility',
            'slotHandlers' => [
                'appendInner' => 'password',
            ],
            'col' => [
                'cols' => 6,
                'lg' => 6,
            ],
            'rules' => [
                ['required', 'classic',null, null, 'Confirm Password'],
            ],
        ],
        'tos' => [
            'type' => 'checkbox',
            'name' => 'tos',
            'label' => 'I accept the terms of service',
            'default' => '',
            'col' => [
                'cols' => 12,
                'lg' => 12,
            ],
            // 'rules' => 'required',
        ],
    ]
];
