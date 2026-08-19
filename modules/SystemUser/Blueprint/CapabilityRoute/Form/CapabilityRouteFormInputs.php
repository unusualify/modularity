<?php

namespace Modules\SystemUser\Blueprint\CapabilityRoute\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CapabilityRouteFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'select-scroll',
                'componentType' => 'v-autocomplete',
                'name' => 'route_name',
                'label' => 'Route Name',
                'itemValue' => 'name',
                'itemTitle' => 'name_with_uri',
                'placeholder' => 'admin.system.cms.promotion.execute',
                'itemsPerPage' => 100,
                'page' => 1,
                'endpoint' => 'admin.system.system_user.capabilities.discover_routes',
                'searchKeys' => ['name', 'uri'],
                'col' => [
                    'cols' => 12,
                ],
                'rules' => 'required|min:2',
            ],
            [
                'type' => 'switch',
                'name' => 'is_active',
                'label' => 'Active',
                'default' => true,
                'col' => [
                    'cols' => 12,
                    'sm' => 6,
                    'md' => 4,
                ],
            ],
        ];
    }
}
