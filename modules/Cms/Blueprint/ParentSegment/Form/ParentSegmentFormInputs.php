<?php

namespace Modules\Cms\Blueprint\ParentSegment\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class ParentSegmentFormInputs implements ModuleRouteInputsProvider
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
                'onlyParentSegmentModels' => true,
            ],
            ['name' => 'locale', 'label' => 'Locale (empty = all locales)', 'type' => 'text', 'rules' => 'nullable|string|max:12'],
            ['name' => 'normalized_prefix', 'label' => 'URL path prefix (empty = homepage / locale root)', 'type' => 'text', 'rules' => 'nullable|string|max:2048'],
            ['name' => 'admin_label', 'label' => 'Admin label', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            ['name' => 'enabled', 'label' => 'Enabled', 'type' => 'switch', 'trueValue' => true, 'falseValue' => false],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
        ];
    }
}
