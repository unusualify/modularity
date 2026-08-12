<?php

namespace Modules\Cms\Blueprint\Redirect\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class RedirectIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
            ['title' => 'From', 'key' => 'from_path', 'searchable' => true],
            ['title' => 'To', 'key' => 'to_path', 'searchable' => true],
            ['title' => 'Status', 'key' => 'status_code'],
            ['title' => 'Active', 'key' => 'is_active', 'formatter' => [
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
