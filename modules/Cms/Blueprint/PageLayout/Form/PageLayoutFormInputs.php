<?php

namespace Modules\Cms\Blueprint\PageLayout\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PageLayoutFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'module-route-model',
                'name' => 'target_model_class',
                'label' => 'Module / route (model)',
                'rules' => 'required|string|max:512',
                'onlyPageLayoutModels' => true,
                'hint' => 'One shell per routed model class. URLs stay in Public route registry; use CmsPageLayoutResolver / LayoutBladeResolver on the front.',
            ],
            ['name' => 'admin_label', 'label' => 'Admin label', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            [
                'type' => 'select',
                'name' => 'layout_builder_id',
                'label' => 'Layout builder (shell)',
                'rules' => 'nullable|integer|exists:layout_builders,id',
                'connector' => 'Cms:LayoutBuilder|repository',
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
                'hint' => 'Provide this when Blade source is filesystem (example: cms.page-layouts.shell).',
                'rules' => 'nullable|string|max:512',
            ],
            [
                'type' => 'layout-blades',
                'name' => 'blade_segments',
                'label' => 'Page layout shell segments',
                'hint' => 'Defines the head/body/footer shell that the page renders when Blade source is database-backed.',
                'layoutBladesSubtitle' => 'Use head/body/footer fragments when Blade source is db; leave empty for filesystem views.',
                'rules' => 'nullable|array',
            ],
            ['name' => 'enabled', 'label' => 'Enabled', 'type' => 'switch', 'trueValue' => true, 'falseValue' => false],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
        ];
    }
}
