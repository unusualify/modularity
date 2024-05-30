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
            'class' => 'pb-2 pt-2'
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
        'embeddedForm' => true,
        'createOnModal' => true,
        'editOnModal' => true,
        'formWidth' => '60%',
        'isRowEditing' => false,
        'rowActionsType' => 'inline',
        'hideDefaultFooter' => false,
    ],
    'form_drafts' => []
];
