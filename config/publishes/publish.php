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
        'searchable' => false, // true,
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
        'striped' => true,
        'hideBorderRow' => false,
        'roundedRows' => true,
    ],
    'form_drafts' => [],
    'ui_settings' => [
        'dashboard' => [
            'blocks' => [
                [
                    'tag' => 'v-col',
                    'component' => 'ue-board-information-plus',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                        's' => 12,
                        'class' => 'pr-theme-semi pb-theme-semi',
                    ],
                    'attributes' => [
                        'container' => [
                            'color' => '#F8F8FF',
                            'elevation' => 10,
                            'class' => '',
                        ],
                        'cardAttribute' => [
                            'variant' => 'outlined',
                            'borderRadius' => '14px',
                            'border' => 'sm',
                            'borderColor' => 'rgb(var(--v-theme-primary))',
                            'titleClass' => 'text-subtitle-2',
                            'titleColor' => 'grey',
                            'infoClass' => 'text-h4 pa-0',
                            'infoColor' => 'text-primary',
                            'class' => 'px-4 py-6',
                            'infoLineHeight' => '1',
                            'infoFontWeight' => '700',
                        ],
                    ],
                    'cards' => [
                        [
                            'title' => 'Distributed Press Release',
                            'connector' => 'PressRelease:PressRelease|repository:getCountFor:method=distributedCount',
                            // 'repository' => 'Modules\\SystemUser\\Repositories\\UserRepository',
                            // 'method' => 'count',
                            'iconBackground' => '#DEF5FA',
                            'iconColor' => 'primary',
                            'iconSize' => '24',
                            'icon' => 'mdi-file-document-outline',
                            'flex' => 6,
                            'infoColor' => 'rgb(var(--v-theme-primary))',
                        ],
                        [
                            'title' => 'Distributed Countries',
                            'connector' => 'PressRelease:PressRelease|repository:getCountFor:method=distributedCountries',
                            'iconBackground' => '#FCF1ED',
                            'iconColor' => 'secondary',
                            'iconSize' => '24',
                            'icon' => 'mdi-emoticon-happy-outline',
                            'flex' => 6,
                            'infoColor' => 'rgb(var(--v-theme-secondary))',
                        ],
                        [
                            'title' => 'User Roles',
                            'connector' => 'User:User|repository:getCountForAll',
                            'iconBackground' => '#FCF1ED',
                            'iconColor' => 'secondary',
                            'iconSize' => '24',
                            'icon' => 'mdi-earth',
                            'flex' => 6,
                            'infoColor' => 'rgb(var(--v-theme-secondary))',
                        ],
                        [
                            'title' => 'User Permissions',
                            'connector' => 'User:User|repository:getCountForAll',
                            'iconBackground' => '#DEF5FA',
                            'iconColor' => 'primary',
                            'iconSize' => '24',
                            'icon' => 'mdi-share-variant-outline',
                            'flex' => 6,
                            'infoColor' => 'rgb(var(--v-theme-primary))',
                        ],
                    ],
                ],
                [
                    'tag' => 'v-col',
                    'component' => 'ue-table',
                    'connector' => 'SystemUser:User|repository:listAll:with=roles,permissions',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                        's' => 12,
                        'class' => '',
                    ],
                    // 'controller' => 'Modules\\SystemUser\\Http\\Controllers\\UserController',
                    'attributes' => [
                        'name' => 'System User',
                        'customTitle' => 'System Users',
                        'subtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                        // 'tableType' => 'dashboard',
                        'elevation' => 2,
                        'tableClasses' => 'elevation-2',
                        'createOnModal' => false,
                        'editOnModal' => false,
                        'rowActionsType' => 'dropdown',
                        'hideDefaultFooter' => true,
                        'hideBorderRow' => true,
                        'sticky' => true,
                        'striped' => true,
                        'roundedRows' => true,
                        'showSelect' => false,
                        'toolbarOptions' => [
                            'color' => 'transparent', // rgb(255,255,255,1) or utility colors like white, purple
                            'border' => false, // false, 'xs', 'sm', 'md', 'lg', 'xl'.
                            'rounded' => false, // This can be 0, xs, sm, true, lg, xl, pill, circle, and shaped. string | number | boolean
                            'collapse' => false, // false, true,
                            'density' => 'default', // prominent, comfortable, compact, default
                            'elevation' => 0, // string or number refers to elevation
                            'image' => '', // image link for the background of the toolbar
                        ],

                        'hideHeaders' => false,
                        'fullWidthWrapper' => true,
                        'hideSearchField' => true,
                        'fillHeight' => false,
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
                                'visible' => true,
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
                                'visible' => true,
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
                    'tag' => 'v-col',
                    'component' => 'ue-table',
                    'connector' => 'PressRelease:PressRelease|repository:listAll:scopes=pressReleaseRegion',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 12,
                        'xl' => 12,
                        'lg' => 12,
                        's' => 12,
                        'class' => '',
                    ],
                    'attributes' => [
                        'name' => 'Press Release',
                        'customTitle' => 'MANAGE PRESS RELEASE',
                        // 'tableType' => 'dashboard',
                        'elevation' => 2,
                        'tableClasses' => 'elevation-2',
                        'createOnModal' => false,
                        'editOnModal' => false,
                        'rowActionsType' => 'dropdown',
                        'hideDefaultFooter' => true,
                        'hideBorderRow' => true,
                        'sticky' => true,
                        'striped' => true,
                        'roundedRows' => true,
                        'showSelect' => false,
                        'toolbarOptions' => [
                            'color' => 'transparent', // rgb(255,255,255,1) or utility colors like white, purple
                            'border' => false, // false, 'xs', 'sm', 'md', 'lg', 'xl'.
                            'rounded' => false, // This can be 0, xs, sm, true, lg, xl, pill, circle, and shaped. string | number | boolean
                            'collapse' => false, // false, true,
                            'density' => 'default', // prominent, comfortable, compact, default
                            'elevation' => 0, // string or number refers to elevation
                            'image' => '', // image link for the background of the toolbar
                        ],
                        'headerOptions' => [
                            'color' => 'rgba(140,160,167, .2)', // Hex, rgba or default css colors
                        ],

                        'hideHeaders' => false,
                        'fullWidthWrapper' => true,
                        'hideSearchField' => true,
                        'fillHeight' => false,
                        'style' => '',
                        'columns' => [
                            [
                                'title' => 'Date',
                                'key' => 'created_at',
                                'align' => 'start',
                                'formatter' => [
                                    'date',
                                    'short',
                                ],
                                'sortable' => true,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'visible' => true,
                            ],
                            [
                                'title' => 'PR Headline',
                                'key' => 'content.headline',
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
                                'visible' => true,
                            ],
                            [
                                'title' => 'Region',
                                'key' => 'region_names',
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
                                'visible' => true,
                            ],
                            [
                                'title' => 'Status',
                                'key' => 'state_formatted',
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
                                'visible' => true,
                                'formatter' => [
                                    'dynamic',
                                ],
                            ],
                            [
                                'type' => 'stateable',
                                'label' => 'Status',
                                'createable' => false,
                                'isEvent' => true,
                                'allowedRoles' => ['superadmin', 'admin', 'manager'],
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
                                                'elements' => 'MANAGE PRESS RELEASES',
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
];
