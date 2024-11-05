<?php

return [
    'locale' => 'en',
    'fallback_locale' => 'en',
    'default_input' => [
        'type' => 'text',
        'hint' => '',
        'placeholder' => '',
        'errorMessages' => [],
        'col' => [
            'cols' => 12,
            'class' => 'pb-2 pt-2',
        ],
        'offset' => [
            'offset' => 0,
            'offset-sm' => 0,
            'offset-md' => 0,
            'offset-lg' => 0,
            'offset-xl' => 0,
        ],
        'order' => [
            'order' => 0,
            'order-sm' => 0,
            'order-md' => 0,
            'order-lg' => 0,
            'order-xl' => 0,
        ],
        'prependIcon' => '',
        'prependInnerIcon' => '',
        'appendIcon' => '',
        'appendInnerIcon' => '',
        'variant' => 'outlined',
        'density' => 'comfortable', // default | comfortable | compact
    ],
    'default_header' => [
        'align' => 'start',
        'sortable' => false,
        'filterable' => false,
        'groupable' => false,
        'divider' => false,
        'class' => 'text-primary', // || []
        'cellClass' => '', // || []
        'width' => 30, // || int

        'noPadding' => true,
        // vuetify datatable header fields end

        // vuetify dataiterable fields related
        'featured' => false,

        // custom fields for ue-datatable start
        'searchable' => false, //true,
        'isRowEditable' => false,
        'isColumnEditable' => false,
        'formatter' => [],
    ],
    'default_table_attributes' => [
        'embeddedForm' => false,
        'createOnModal' => true,
        'editOnModal' => true,
        'formWidth' => '60%',
        'isRowEditing' => false,
        'rowActionsType' => 'inline',
        'hideDefaultFooter' => false,
    ],
    'form_drafts' => [],
    'ui_settings' => [
        'dashboard' => [
            'blocks' => [
                [
                    'component' => 'board-information-plus',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                        's' => 12,
                        'class' => 'pr-6 pb-6',
                    ],
                    'attributes' => [
                        'container' => [
                            'color' => '#F8F8FF',
                            'elevation' => 10,
                            'class' => 'px-5 py-5',
                        ],
                        'cardAttribute' => [
                            'variant' => 'outlined',
                            'borderRadius' => '14px',
                            'border' => 'md',
                            'titleClass' => 'pt-3 pb-3 text-subtitle-2',
                            'titleColor' => 'grey',
                            'infoClass' => 'text-h4 pt-0 pb-5',
                            'infoColor' => '#000000',
                        ],
                    ],
                    'cards' => [
                        [
                            'title' => 'Users',
                            'repository' => 'Modules\\SystemUser\\Repositories\\UserRepository',
                            'method' => 'count',
                            'flex' => 6,
                        ],
                        [
                            'title' => 'Companies',
                            'repository' => 'Modules\\SystemUser\\Repositories\\CompanyRepository',
                            'method' => 'count',
                            'flex' => 6,
                        ],
                        [
                            'title' => 'User Roles',
                            'repository' => 'Modules\\SystemUser\\Repositories\\RoleRepository',
                            'method' => 'count',
                            'flex' => 6,
                        ],
                        [
                            'title' => 'User Permissions',
                            'repository' => 'Modules\\SystemUser\\Repositories\\PermissionRepository',
                            'method' => 'where:company_id:1|count',
                            'flex' => 6,
                        ],
                    ],
                ],
                [
                    'component' => 'new-table',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                        's' => 12,
                        'class' => 'pl-6 pb-6',
                    ],
                    'controller' => 'Modules\\SystemUser\\Http\\Controllers\\UserController',
                    'attributes' => [
                        'customTitle' => 'System Users',
                        'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                        'tableType' => 'dashboard',

                        'hideHeaders' => true,
                        'fullWidthWrapper' => true,
                        'hideSearchField' => true,
                        'fillHeight' => true,
                        'style' => '',
                        'columns' => [
                            [
                                'title' => 'Name',
                                'key' => 'name',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                    'edit',
                                ],
                            ],
                            [
                                'title' => 'Published',
                                'key' => 'published',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                    'status',
                                    [
                                        'Not Published',
                                        'Published',
                                    ],
                                    [
                                        'blue',
                                        'red',
                                    ],
                                ],
                            ],
                        ],
                        'tableOptions' => [
                            'page' => 1,
                            'sortBy' => [],
                            'multiSort' => false,
                            'mustSort' => false,
                            'groupBy' => [],
                            'itemsPerPage' => 10,
                            'tableType' => 'dashboard', // !!!!!!
                        ],
                        'slots' => [
                            'bottom' => [
                                'elements' => [
                                    [
                                        'tag' => 'div',
                                        'attributes' => [
                                            'class' => 'text-right pa-8',
                                        ],
                                        'elements' => [
                                            [
                                                'tag' => 'v-btn-tertiary',
                                                'elements' => 'MANAGE USERS',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'component' => 'new-table',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 12,
                        'xl' => 12,
                        'lg' => 12,
                        's' => 12,
                        'class' => 'pl-6 pb-6',
                    ],
                    'controller' => 'Modules\SystemUser\\Http\\Controllers\\UserController',
                    'attributes' => [
                        'customTitle' => 'System Users',
                        'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                        'tableType' => 'dashboard',
                        'hideHeaders' => false,
                        'rowActionsType' => 'dropdown',
                        'rowActionsIcon' => 'mdi-dots-vertical',
                        'fullWidthWrapper' => true,
                        'hideSearchField' => true,
                        'fillHeight' => true,
                        'style' => '',
                        'columns' => [
                            [
                                'title' => 'Name',
                                'key' => 'name',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                    'edit',
                                ],
                            ],
                            [
                                'title' => 'Company',
                                'key' => 'company_relation',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                ],
                            ],
                            [
                                'title' => 'Published',
                                'key' => 'published',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                    'status',
                                    [
                                        'Not Published',
                                        'Published',
                                    ],
                                    [
                                        'blue',
                                        'red',
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Actions',
                                'key' => 'actions',
                                'sortable' => false,
                            ],
                        ],
                        'tableOptions' => [
                            'page' => 1,
                            'sortBy' => [],
                            'multiSort' => false,
                            'mustSort' => false,
                            'groupBy' => [],
                            'itemsPerPage' => 10,
                            'tableType' => 'dashboard',
                            // !!!!!!
                        ],
                        'slots' => [
                            'bottom' => [
                                'elements' => [
                                    [
                                        'tag' => 'div',
                                        'attributes' => [
                                            'class' => 'text-right pa-8',
                                        ],
                                        'elements' => [
                                            [
                                                'tag' => 'v-btn-tertiary',
                                                'elements' => 'MANAGE USERS',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

            ],
        ],
    ],
    'payment' => [
        'currency_services' => [
            'USD' => 'garanti-pos',
            'EUR' => 'garanti-pos',
            'TRY' => 'garanti-pos',
        ],
        'default_currency' => 'EUR',
        'locale_currencies' => [
            'tr' => 'TRY',
            'en' => 'EUR',
        ],
    ],
    'default_payment_service' => [
        'USD' => 'garanti-pos',
        'EUR' => 'garanti-pos',
        'TRY' => 'garanti-pos',
    ],
];
