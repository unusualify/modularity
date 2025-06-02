<?php

return [
    '_language' => [
        'type' => 'select',
        'name' => 'language',
        'label' => 'Language',
        'default' => 'tr',
        'itemTitle' => 'label',
        'itemValue' => 'value',
        'items' => array_map(function ($locale) {
            return [
                'value' => $locale,
                'label' => getLabelFromLocale($locale, true),
            ];
        }, modularityConfig('available_user_locales', ['en'])),
        'rules' => 'sometimes:required',
    ],
    '_timezone' => [
        'type' => 'combobox',
        'name' => 'timezone',
        'label' => 'Timezone',
        'default' => 'Europe/Istanbul',
        'returnObject' => false,
        'itemTitle' => 'label',
        'itemValue' => 'value',
        'items' => collect((new \Camroncade\Timezone\Timezone)->timezoneList)->map(function ($value, $key) {
            return [
                'label' => $key,
                'value' => $value,
            ];
        })->values()->toArray(),
    ],
    '_name' => [
        'type' => 'text',
        'name' => 'name',
        'label' => 'Name',
        'default' => '',
    ],
    '_description' => [
        'type' => 'textarea',
        'name' => 'description',
        'label' => 'Description',
    ],
    '_phone' => [
        'type' => 'input-phone',
        'name' => 'phone',
        'label' => 'Phone Number',
        'default' => '',
        'clearable' => false,
    ],
    '_email' => [
        'type' => 'text',
        'name' => 'email',
        'label' => 'E-mail',
        'rules' => 'email',
    ],
    '_password_confirmation' => [
        'type' => 'password',
        'name' => 'password_confirmation',
        'label' => 'New Password',
        'appendInnerIcon' => '$non-visibility',
        'slotHandlers' => [
            'appendInner' => 'password',
        ],
        'rules' => 'sometimes|min:6',
    ],
    '_password' => [
        'type' => 'password',
        'name' => 'password',
        'label' => 'Password',
        'default' => '',
        'appendInnerIcon' => '$non-visibility',
        'slotHandlers' => [
            'appendInner' => 'password',
        ],
    ],
    '_published' => [
        'type' => 'switch',
        'name' => 'published',
        'label' => 'Status',
        'default' => true,
    ],
    '_timezone' => [
        'type' => 'hidden',
        'name' => '_timezone',
        'default' => 'Europe/London',
        'id' => 'timezone_session',
    ],
];
