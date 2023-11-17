<?php

return [
    'user' => [
        'name' => [
            "type" => "text",
            "name" => "name",
            "label" => "Name",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'rules' => [
                ['min', 3]
            ]
        ],
        'surname' => [
            "type" => "text",
            "name" => "surname",
            "label" => "Surname",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'rules' => [
                ['min', 2]
            ]
        ],
        'job_title' => [
            "type" => "text",
            "name" => "job_title",
            "label" => "Job Title",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'rules' => [
                ['min', 2]
            ]
        ],
        'email' => [
            "type" => "text",
            "name" => "email",
            "label" => "E-mail",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'rules' => [
                ['email']
            ]
        ],
        'phone' => [
            "type" => "custom-input-phone",
            "name" => "phone",
            "label" => "Phone Number",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'clearable' => false
        ],
        'country' => [
            "type" => "text",
            "name" => "country",
            "label" => "Country",
            "default" => "",
            'col' => [
                'sm' => 6,
            ],
            'rules' => [
                ['min', 3]
            ]
        ],
        'language' => [
            "type" => "select",
            "name" => "language",
            "label" => "Preferred Language",
            "default" => 0,
            'col' => [
                'sm' => 6,
            ],
            'itemTitle' => 'label',
            // 'itemValue' => 'value',
            'items_' => [
                [
                    'text' => 'TR',
                    'value' => 1,
                ],
                [
                    'text' => 'EN',
                    'value' => 2
                ]
            ],
            'items' => array_map(function($locale) {
                return [
                    'value' => $locale,
                    'label' => getLabelFromLocale($locale, true)
                ];
            }, unusualConfig('available_user_locales', ['en', 'tr']))
        ],
        'timezone' => [
            "type" => "combobox",
            "name" => "timezone",
            "label" => "Timezone",
            "default" => 0,
            'col' => [
                'sm' => 6,
            ],
            "returnObject" => false,
            'itemTitle' => 'label',
            'itemValue' => 'value',
            'items' => collect((new \Camroncade\Timezone\Timezone())->timezoneList)->map(function($value,$key){
                return [
                    'label' => $key,
                    'value' => $value
                ];
            })->values()->toArray(),
        ],
    ],
    'user_password' => [
        'password' => [
            "type" => "password",
            // "ext" => "password",
            "name" => "password",
            "label" => "Current Password",
            "default" => "",
            'col' => [ 'sm' => 6],
            "appendInnerIcon" => '$non-visibility',
            "slotHandlers" => [
                'appendInner' => 'password',
            ],
        ],
        'gap-1' => [
            'type' => 'v-sheet',
            'name' => 'gap-1',
            'class' => 'd-none d-md-block',
            'col' => [
                'cols' => 0,
                'sm' => 6,
                'class' => 'd-none d-sm-block',
            ]
        ],
        'new-password' => [
            "type" => "password",
            // "ext" => "password",
            "name" => "new_password",
            "label" => "New Password",
            "default" => "",
            'col' => [ 'sm' => 6],
            "appendInnerIcon" => '$non-visibility',
            "slotHandlers" => [
                'appendInner' => 'password',
            ],
            'rules' => [
                ['min', 6]
            ]
        ],
        'confirm-password' => [
            "type" => "password",
            // "ext" => "password",
            "name" => "confirm_password",
            "label" => "Confirm Password",
            "default" => "",
            'col' => [ 'sm' => 6],
            "appendInnerIcon" => '$non-visibility',
            "slotHandlers" => [
                'appendInner' => 'password',
            ],
            'rules' => [
                ['min', 6],
                ['confirmation', 'new_password'],
            ]
        ],
    ],
    'company' => [
        'name' => [
            "type" => "text",
            "name" => "name",
            "label" => "Company",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
        'address' => [
            "type" => "text",
            "name" => "address",
            "label" => "Address",
            "default" => "",
            'col' => [
                'cols' => 12,
                'sm' => 12,
            ],
            'rules' => [
                // ['email']
            ]
        ],
        'city' => [
            "type" => "text",
            "name" => "city",
            "label" => "City",
            "default" => "",
            'col' => [ 'sm' => 6],

            'rules' => [
                ['min', 3]
            ]
        ],
        'state' => [
            "type" => "text",
            "name" => "state",
            "label" => "State/Province",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
        'country' => [
            "type" => "text",
            "name" => "country",
            "label" => "Country",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
        'zip_code' => [
            "type" => "text",
            "name" => "zip_code",
            "label" => "ZIP/Postal Code",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
        'phone' => [
            "type" => "custom-input-phone",
            "name" => "phone",
            "label" => "Phone Number",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                // ['email']
            ],
            'clearable' => false
        ],
        'vat_number' => [
            "type" => "text",
            "name" => "vat_number",
            "label" => "VAT Number",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
        'tax_id' => [
            "type" => "text",
            "name" => "tax_id",
            "label" => "Tax ID",
            "default" => "",
            'col' => [ 'sm' => 6],
            'rules' => [
                ['min', 3]
            ]
        ],
    ]
];
