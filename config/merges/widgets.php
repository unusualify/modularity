<?php

return [
    'table' => [
        'tag' => 'ue-table',
        'widgetSlots' => [],
        'component' => 'ue-table',
        'attributes' => [

        ],
    ],
    'board-information-plus' => [
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
                'color' => '',
                'elevation' => 2,
                'class' => 'h-100',
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
                'class' => 'px-4 py-6 h-100',
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
];
