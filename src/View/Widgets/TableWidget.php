<?php

namespace Unusualify\Modularity\View\Widgets;

use Unusualify\Modularity\Module;
use Unusualify\Modularity\Traits\Allowable;
use Unusualify\Modularity\View\ModularityWidget;

class TableWidget extends ModularityWidget
{
    use Allowable;

    public $tag = 'ue-table';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 6,
        'xl' => 6,
    ];

    public $attributes = [
        'class' => 'h-100',

        'createOnModal' => false,
        'editOnModal' => false,

        'fullWidthWrapper' => true,
        'fillHeight' => false,

        'hideHeaders' => false,
        'hideBorderRow' => true,
        'hideDefaultFooter' => false,
        'hideSearchField' => true,

        'rowActionsType' => 'dropdown',
        'roundedRows' => true,

        'showSelect' => false,
        'style' => '',
        'class' => 'elevation-2',

        'tableOptions' => [
            'page' => 1,
            'sortBy' => [],
            'search' => null,
            'groupBy' => [],
            'itemsPerPage' => 5,
            // 'tableType' => 'dashboard',
        ],
        'noFullScreen' => true,

        'tableClasses' => 'elevation-2',
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
        'paginationOptions' => [
            'footerComponent' => 'vuePagination',
        ],
    ];

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);

        $attributes = array_merge_recursive_preserve(
            modularityConfig('default_table_attributes'),
            $attributes
        );

        if (isset($attributes['_routeName']) && isset($attributes['_module']) && $attributes['_module'] instanceof Module) {

            $routeEndpoints = $attributes['_module']->getRoutePanelUrls(
                routeName: $attributes['_routeName'],
                withoutNamePrefix: true,
                modelBindingValue: ':id'
            );

            $attributes['endpoints'] = array_merge(
                getModularityDefaultUrls(),
                $routeEndpoints,
            );
        }

        if (isset($attributes['columns'])) {
            $newColumns = $this->getAllowableItems(
                $attributes['columns'],
                searchKey: 'allowedRoles',
                orClosure: fn ($item, $user) => $user->isSuperAdmin(),
            );

            $newColumns = configure_table_columns($newColumns);

            $attributes['columns'] = hydrate_table_columns_translations($newColumns);
        }

        return $attributes;
    }
}
