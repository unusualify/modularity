<?php

return [
    'language' => [
        "type" => "select",
        "name" => "language",
        "label" => "Language",
        "default" => 'tr',
        'itemTitle' => 'label',
        'itemValue' => 'value',
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
        "default" => 'Europe/Istanbul',
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
];
