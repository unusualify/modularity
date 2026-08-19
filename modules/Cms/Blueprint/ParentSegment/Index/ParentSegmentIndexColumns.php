<?php

namespace Modules\Cms\Blueprint\ParentSegment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class ParentSegmentIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['title' => 'Model', 'key' => 'target_model_class', 'searchable' => true],
            ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
            ['title' => 'Prefix', 'key' => 'normalized_prefix', 'searchable' => true],
            ['title' => 'Label', 'key' => 'admin_label', 'searchable' => true],
            ['title' => 'Enabled', 'key' => 'enabled', 'formatter' => [
                0 => 'switch',
                1 => [
                    'trueValue' => true,
                    'falseValue' => false,
                ],
            ]],
            ['title' => 'Sort', 'key' => 'sort_order'],
            ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
        ];
    }
}
