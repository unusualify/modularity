<?php

namespace Modules\Cms\Blueprint\Sitemap\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class SitemapIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['title' => 'ID', 'key' => 'id', 'searchable' => true],
            ['title' => 'Slug', 'key' => 'slug', 'searchable' => true],
            ['title' => 'Created At', 'key' => 'created_at', 'searchable' => true],
            ['title' => 'Updated At', 'key' => 'updated_at', 'searchable' => true],
            ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
        ];
    }
}
