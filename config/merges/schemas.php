<?php

return [
    'default_fields' => [
        'name:string',
        'published:boolean:default(true)',
        // "description:text:nullable",
    ],
    // 'fillables' => [
    //     // 'published:boolean:default(false)',
    // ],
    'translated_attributes' => [
        'active:boolean',
    ],
    'non_migration_fields' => [
        'published',
        'publish_start_date',
        'publish_end_date',
        'public',
        'created_at',
        'updated_at',
        'deleted_at',
    ],
    'non_translatable_fillable' => [
        'published',
    ],
    'default_inputs' => [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
        ],
        [
            'name' => 'published',
            'label' => 'Published',
            'isEvent' => true,
            'type' => 'switch',
        ],
    ],
    'default_pre_headers' => [
        [
            'title' => 'Name',
            'key' => 'name',
            'formatter' => ['edit'],
            'searchable' => true,
        ],
        [
            'title' => 'Status',
            'key' => 'published',
            'formatter' => [
                0 => 'switch',
            ],
        ],
    ],
    'default_post_headers' => [
        [
            'title' => 'Created Time',
            'key' => 'created_at',
            'formatter' => ['date', 'long'],
            'searchable' => true,
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
            'sortable' => false,
        ],
    ],
];
