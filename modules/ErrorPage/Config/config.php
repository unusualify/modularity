<?php

return [
    'name' => 'ErrorPage',
    'system_prefix' => true,
    'headline' => 'Error Pages',
    'icon' => 'mdi-alert-circle-outline',
    'group' => 'system',
    'routes' => [
        'error_page' => [
            'index' => [
                'options' => \Modules\ErrorPage\Blueprint\ErrorPage\Index\ErrorPageIndexOptions::class,
                'columns' => \Modules\ErrorPage\Blueprint\ErrorPage\Index\ErrorPageIndexColumns::class,
            ],
            'form' => [
                'inputs' => \Modules\ErrorPage\Blueprint\ErrorPage\Form\ErrorPageFormInputs::class,
            ],
            'parent' => true,
            'name' => 'ErrorPage',
            'headline' => 'Error Pages',
            'url' => 'error-pages',
            'route_name' => 'error_page',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'createOnModal' => true,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Name',
            //         'key' => 'name',
            //         'formatter' => [
            //             'edit',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Code',
            //         'key' => 'error_code',
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Status',
            //         'key' => 'published',
            //         'formatter' => [
            //             'switch',
            //         ],
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'formatter' => [
            //             'date',
            //             'long',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'name' => 'name',
            //         'label' => 'Name',
            //         'type' => 'text',
            //         'rules' => 'required|max:255',
            //     ],
            //     [
            //         'name' => 'error_code',
            //         'label' => 'Error Code',
            //         'type' => 'select',
            //         'rules' => 'required|in:403,404,500',
            //         'itemValue' => 'value',
            //         'itemTitle' => 'title',
            //         'items' => [
            //             ['value' => '404', 'title' => '404 — Not Found'],
            //             ['value' => '403', 'title' => '403 — Forbidden'],
            //             ['value' => '500', 'title' => '500 — Server Error'],
            //         ],
            //     ],
            // ],
        ],
    ],
];
