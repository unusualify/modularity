<?php

return [
    'embeddedForm' => false,
    'createOnModal' => true,
    'editOnModal' => true,
    'formWidth' => '60%',
    'isRowEditing' => false,
    'rowActionsType' => 'inline',
    'hideDefaultFooter' => false,
    'tableClasses' => 'elevation-2 zebra-stripes free-form',
    'itemsPerPage' => 20,
    'hideHeaders' => false,
    'hideSearchField' => false,
    'multiSort' => false,
    'mustSort' => false,
    'tableDensity' => 'compact', // compact, comfortable, null
    // 'tableSubtitle' => '',
    'sticky' => true,
    'showSelect' => true,

    'toolbarOptions' => [
        'color' => 'transparent', // rgb(255,255,255,1) or utility colors like white, purple
        'border' => false, // false, 'xs', 'sm', 'md', 'lg', 'xl'.
        'rounded' => false, // This can be 0, xs, sm, true, lg, xl, pill, circle, and shaped. string | number | boolean
        'collapse' => false, // false, true,
        'density' => 'compact', // prominent, comfortable, compact, default
        'elevation' => 0, // string or number refers to elevation
        'image' => '', // image link for the background of the toolbar
    ],
    'filterBtnOptions' => [
        'variant' => 'flat', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'purple', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        // 'prepend-icon' => 'mdi-chevron-down', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        // 'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'mx-2 text-white text-capitialize rounded px-8',
        'size' => 'default', //sizes: x-small, small, default, large, and x-large.
        // 'icon' => 'mdi-filter-outline',
        'prepend-icon' => 'mdi-chevron-down',
        'slim' => false,
        // 'loading' => true,
    ],
    'addBtnOptions' => [
        'variant' => 'flat', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'orange', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        'prepend-icon' => 'mdi-plus', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'ml-2 text-white text-capitialize text-bold',
        'size' => 'default', //sizes: x-small, small, default, large, and x-large.
        // 'text' => 'CREATE NEW',
        // 'icon' => 'mdi-abacus'
        // 'loading' => true,
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
        // 'iteratorComponent' => 'card-iterator',
        // 'col' => [
        //     'cols' => 6,
        //     'sm' => 6,
        //     'md' => 6,
        //     'lg' => 6,
        //     'xl' => 6,
        // ],
    ],
];
