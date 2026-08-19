<?php

namespace Modules\Cms\Blueprint\LayoutBuilder\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class LayoutBuilderFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'rules' => 'required|string|max:255',
            ],
            [
                'name' => 'slug',
                'label' => 'Layout slug',
                'type' => 'text',
                'rules' => 'required|string|max:191',
            ],
            [
                'type' => 'select',
                'name' => 'blade_source',
                'label' => 'Blade source',
                'rules' => 'required|string|in:db,filesystem',
                'items' => [
                    ['id' => 'db', 'name' => 'Database segments (head/body/footer)'],
                    ['id' => 'filesystem', 'name' => 'Filesystem Blade view'],
                ],
                'default' => 'db',
                'itemValue' => 'id',
                'itemTitle' => 'name',
            ],
            [
                'type' => 'text',
                'name' => 'blade_view_name',
                'label' => 'Filesystem view name',
                'hint' => 'Example: vendor.modularous.cms.layout-builder.master (run php artisan cms:layout-builder:publish-blades).',
                'rules' => 'nullable|string|max:512',
            ],
            [
                'type' => 'select',
                'name' => 'style_sheet_id',
                'label' => 'Primary stylesheet',
                'rules' => 'nullable',
                'connector' => 'Cms:StyleSheet|repository',
            ],
            [
                'type' => 'layout-blades',
                'name' => 'blade_segments',
                'label' => 'Blade segments',
                'rules' => 'nullable|array',
            ],
            [
                'type' => 'json-field',
                'name' => 'definition',
                'label' => 'Definition (JSON)',
                'rules' => 'nullable|array',
                'jsonFieldSubtitle' => 'Optional metadata only (filesystem layouts keep markup in Blade files).',
                'rows' => 10,
                'jsonSnippets' => [
                    [
                        'label' => '{}',
                        'insert' => (object) [],
                    ],
                ],
            ],
            [
                'type' => 'json-field',
                'name' => 'style_sheet_slugs',
                'label' => 'Extra stylesheet slugs (JSON array)',
                'rules' => 'nullable|array',
                'jsonFieldSubtitle' => 'List of style_sheets.slug values appended after the primary Style sheet FK.',
                'rows' => 6,
                'jsonSnippets' => [
                    [
                        'label' => '["example-slug"]',
                        'insert' => [],
                    ],
                ],
            ],
        
        ];
    }
}
