<?php

return [
    'embeddedForm' => true,
    'createOnModal' => true,
    'editOnModal' => true,
    'formWidth' => '60%',
    'isRowEditing' => false,
    'rowActionsType' => 'inline',
    'hideDefaultFooter' => false,
    'tableClasses' => 'elevation-2',
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
        'color' => 'green', // rgb(255,255,255,1) or utility colors like white, purple
        // 'elevation' => null, // string or number refers to elevation
        'prepend-icon' => 'mdi-plus', // material design icon name,
        'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
        'ripple' => true, // boolean
        'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
        'class' => 'mx-2 text-white text-capitialize',
        'size' => 'default' //sizes: x-small, small, default, large, and x-large.
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
        'class' => 'mx-2 text-white text-capitialize',
        'size' => 'default', //sizes: x-small, small, default, large, and x-large.
        'text' => 'Add New',
        // 'icon' => 'mdi-abacus'
        // 'loading' => true,
    ],


];

