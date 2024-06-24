<?php

return [
    'embeddedForm' => true,
    'createOnModal' => true,
    'editOnModal' => true,
    'formWidth' => '60%',
    'isRowEditing' => false,
    'rowActionsType' => 'inline',
    'hideDefaultFooter' => false,
    'tableClasses' => 'elevation-2 zebra-stripes free-form',
    // 'noFooter' => true,

    'hideHeaders' => false,
    'hideSearchField' => false,
    'multiSort' => false,
    'mustSort' => false,
    'tableDensity' => 'comfortable', // compact, comfortable, null
    'tableSubtitle' => '',


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
        'variant' => 'elevated', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'purple', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        // 'prepend-icon' => 'mdi-chevron-down', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        // 'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'mx-2 text-white text-capitialize rounded px-8 h-75',
        'size' => 'small', //sizes: x-small, small, default, large, and x-large.
        // 'icon' => 'mdi-filter-outline',
        'prepend-icon' => 'mdi-chevron-down',
        'slim' => false,
        // 'loading' => true,
    ],
    'addBtnOptions' => [
        'variant' => 'elevated', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        'color' => 'orange', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        'prepend-icon' => 'mdi-plus', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'ml-2 text-white text-capitialize text-bold',
        'size' => 'default', //sizes: x-small, small, default, large, and x-large.
        'text' => 'CREATE NEW',
        // 'icon' => 'mdi-abacus'
        // 'loading' => true,
    ],
    'paginationOptions' => [
        'footerComponent' => 'vuePagination', // default|vuePagination|null:
        'footerProps' => [
            'itemsPerPageOptions' => [
                ['value' => 1, 'title' => '1'],
                ['value' => 10, 'title' => '10'],
                ['value' => 20, 'title' => '20'],
                ['value' => 30, 'title' => '30'],
                ['value' => 40, 'title' => '40'],
                ['value' => 50, 'title' => '50'],
            ],
            'itemsPerPageText' => 'Items per page:',
            'itemsPerPage' => 10,
            // 'first-icon' => '',
            // 'lastIcon' => '',
            // 'nextIcon' => '',
            // 'prevIcon' => '',
            'showCurrentPage' => true,
        ],
        'vuePagination' => [ //v-pagination
            'activeColor' => 'black',
            'border' => false, // string|number|boolean xs, sm, md, lg, xl. -- false in default
            'color' => 'tertiary', // utility colors or rgba(x,x,x,a),
            'density' => 'default', // default | comfortable | compact
            'elevation' => 3,// string | number or undefined in default
            // 'ellipsis'=> '---', // string '...' in default
            // 'firstIcon' => '',
            // 'lastIcon' => '',
            // 'nextIcon' => '',
            // 'prevIcon' => '',
            'rounded' => 'default', // string|number or boolean 0.xs.sm.true,lg,xl,pill, circle, and shaped
            'showFirstLastPage' => false, // boolean,
            'size' => 'default', // string | number  Sets the height and width of the component. Default unit is px. Can also use the following predefined sizes: x-small, small, default, large, and x-large.
            'variant' => 'flat', //'flat' | 'elevated' | 'tonal' | 'outlined' | 'text' | 'plain' -- 'text' in default
            'totalVisible' => 0 // 'auto' | number  - if 0 is given numbers totally not be shown
        ]
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

