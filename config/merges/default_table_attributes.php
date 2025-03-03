<?php

return [
    'embeddedForm' => false,
    'createOnModal' => true,
    'class' => 'pa-3',
    'editOnModal' => true,
    'formWidth' => '60%',
    'isRowEditing' => false,
    'rowActionsType' => 'inline',
    'hideDefaultFooter' => false,
    'tableClasses' => 'elevation-2',
    'itemsPerPage' => 10,
    'hideHeaders' => false,
    'hideSearchField' => false,
    'multiSort' => false,
    'mustSort' => false,
    'tableDensity' => 'compact', // compact, comfortable, null
    // 'tableSubtitle' => '',
    'sticky' => true,
    'showSelect' => true,
    'striped' => true,
    'hideBorderRow' => false,
    'roundedRows' => true,
    'controlsPosition' => 'top', // top, bottom

    'toolbarOptions' => [
        'color' => 'transparent', // rgb(255,255,255,1) or utility colors like white, purple
        'border' => false, // false, 'xs', 'sm', 'md', 'lg', 'xl'.
        'rounded' => false, // This can be 0, xs, sm, true, lg, xl, pill, circle, and shaped. string | number | boolean
        'collapse' => false, // false, true,
        'density' => 'default', // prominent, comfortable, compact, default
        'elevation' => 0, // string or number refers to elevation
        'image' => '', // image link for the background of the toolbar
        // 'height' => '90',
    ],
    'filterBtnOptions' => [
        'variant' => 'outlined', // 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'primary', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        // 'prepend-icon' => 'mdi-chevron-down', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        // 'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'mx-2 text-white text-capitialize rounded px-4',
        'size' => 'default', // sizes: x-small, small, default, large, and x-large.
        // 'icon' => 'mdi-filter-outline',
        'prepend-icon' => 'mdi-filter-outline',
        'append-icon' => 'mdi-chevron-down',
        'slim' => false,
        'density' => 'comfortable',
        // 'loading' => true,
    ],
    'addBtnOptions' => [
        'variant' => 'flat', // 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'secondary', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        'prepend-icon' => 'mdi-plus', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'ml-2 text-white rounded text-capitialize text-bold',
        'size' => 'default', // sizes: x-small, small, default, large, and x-large.
        // 'text' => 'CREATE NEW',
        // 'icon' => 'mdi-abacus'
        // 'loading' => true,
        'density' => 'comfortable',
    ],
    'paginationOptions' => [
        'footerComponent' => 'default', // default|vuePagination|infiniteScroll:
        'footerProps' => [
            'items-per-page-options' => [
                ['value' => 10, 'title' => '10'],
                ['value' => 20, 'title' => '20'],
                ['value' => 30, 'title' => '30'],
                ['value' => 40, 'title' => '40'],
                ['value' => 50, 'title' => '50'],
            ],
            'items-per-page' => 20,
            'show-current-page' => true,
        ],
        '_footerProps' => [
            'variant' => '',
            'border' => false,
            'active-color' => 'black',
            'color' => 'purple',
            'density' => 'default',
            'elevation' => 3,
            'rounded' => 'default',
            'show-first-last-page' => false,
            'size' => 'default',
            // 'total-visible' => 0,
        ],
        '_defaultPaginationOptions' => [
            'items-per-page-options' => [
                ['value' => 10, 'title' => '10'],
                ['value' => 20, 'title' => '20'],
                ['value' => 30, 'title' => '30'],
                ['value' => 40, 'title' => '40'],
                ['value' => 50, 'title' => '50'],
            ],
            'items-per-page' => 20,
            'show-current-page' => true,
        ],
    ],
    'customRowComponent' => [
    /**
     * an object with the following properties:
     * 'iteratorComponent' => 'configurable-card-iterator',
     * 'col' => [
     *     'cols' => 12,
     * ]
     *
     * or
     *
     * an array of objects with the following properties:
     * [
     *     'name' => 'configurable-card-iterator',
     *     'allowedRoles' => ['admin'],
     *     'col' => [
     *     'cols' => 12,
     * ]
     * ],
     * [
     *     'name' => 'configurable-card-iterator',
     *     'allowedRoles' => ['manager'],
     *     'col' => [
     *     'cols' => 12,
     * ]
     * ],
     */
    ],
    'cellOptions' => [
        'maxChar' => 3,
    ],
    'headerOptions' => [
        'color' => 'rgba(140,160,167, .2)', // Hex, rgba or default css colors
    ],

    'formAttributes' => [
        'formClass' => 'px-6 pt-6 pb-0',
        'scrollable' => true,
        'hasSubmit' => false,
        'fillHeight' => true,
        'hasDivider' => true,
        'noDefaultFormPadding' => true,
    ],
    'formModalAttributes' => [
        'widthType' => 'lg',
        'fullscreen' => false,
        'transition' => 'dialog-bottom-transition',
    ],
];
