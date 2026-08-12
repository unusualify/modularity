<?php

namespace Modules\Cms\Blueprint\PageLayout\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PageLayoutIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['title' => 'Model', 'key' => 'target_model_class', 'searchable' => true],
            ['title' => 'Label', 'key' => 'admin_label', 'searchable' => true],
            ['title' => 'Layout', 'key' => 'layoutBuilder', 'itemTitle' => 'name', 'searchable' => false],
            [
                'title' => 'Blade Source',
                'key' => 'blade_source',
                'searchable' => true,
            ],
            ['title' => 'Enabled', 'key' => 'enabled', 'formatter' => [
                0 => 'switch',
                1 => [
                    'trueValue' => true,
                    'falseValue' => false,
                ],
            ]],
            ['title' => 'Sort', 'key' => 'sort_order'],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'numeric-full',
                ],
            ],
            ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
        ];
    }
}
