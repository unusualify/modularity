<?php

namespace Modules\Cms\Blueprint\StyleSheet\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class StyleSheetIndexColumns implements ModuleRouteHeadersProvider
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
                'searchable' => true,
            ],
            [
                'title' => 'Driver',
                'key' => 'driver',
                'searchable' => true,
            ],
            [
                'title' => 'Framework source',
                'key' => 'framework_source',
                'searchable' => true,
            ],
            [
                'title' => 'Compiled',
                'key' => 'compiled_at',
                'formatter' => [
                    'date',
                    'long',
                ],
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'long',
                ],
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
