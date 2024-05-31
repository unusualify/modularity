<?php

return [
    'default_fields' => [
        "name:string",
        // "description:text:nullable",
    ],
    'fillables' => [
        'published:boolean:default(false)'
    ],
    'translated_attributes' => [
        'active:boolean'
    ],
    'default_inputs' => [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
        ]
    ],
    'default_pre_headers' => [
        [
            'title' => 'Name',
            'key' => 'name',
            'formatter' => ['edit'],
            'searchable' => true
        ],
    ],
    'default_post_headers' => [
        [
            'title' => 'Created Time',
            'key' => 'created_at',
            'formatter' => ['date', 'long'],
            'searchable' => true
        ],
        // [
        //     'title' => 'Update Time',
        //     'key' => 'updated_at',
        //     'formatter' => ['date', 'long'],
        //     'searchable' => true
        // ],
        [
            'title' => 'Actions',
            'key' => 'actions',
            'sortable' => false
        ]
    ],
];

