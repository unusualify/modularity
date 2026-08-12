<?php

namespace Modules\Cms\Blueprint\LayoutBuilder\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class LayoutBuilderIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Name',
                'key' => 'name',
                'formatter' => [
                    'edit',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Slug',
                'key' => 'slug',
                'formatter' => [
                    'edit',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Blade Source',
                'key' => 'blade_source',
                'searchable' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
