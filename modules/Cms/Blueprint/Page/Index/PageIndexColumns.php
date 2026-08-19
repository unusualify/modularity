<?php

namespace Modules\Cms\Blueprint\Page\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class PageIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['title' => 'Title', 'key' => 'title', 'searchable' => true],
            ['title' => 'Published', 'key' => 'published', 'formatter' => [
                0 => 'switch',
                1 => [
                    'trueValue' => true,
                    'falseValue' => false,
                ],
            ]],
            ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
        ];
    }
}
