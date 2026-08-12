<?php

namespace Modules\SystemUser\Blueprint\Capability\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CapabilityFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Capability Key',
                'placeholder' => 'promotion.execute',
                'rules' => 'required|min:3',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
            ],
            [
                'type' => 'text',
                'name' => 'title',
                'label' => 'Title',
                'placeholder' => 'Promotion Execute',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
            ],
            [
                'type' => 'select',
                'name' => 'roles',
                'multiple' => true,
                'itemValue' => 'id',
                'itemTitle' => 'title',
                'label' => 'Allowed Roles',
                'connector' => 'SystemUser:Role|repository:list:column=title',
                'col' => [
                    'cols' => 12,
                ],
            ],
            [
                'type' => 'select-scroll',
                'componentType' => 'v-autocomplete',
                'name' => 'routes',
                'label' => 'Bound Routes',
                'multiple' => true,
                'chips' => true,
                'itemValue' => 'id',
                'itemTitle' => 'route_name',
                'itemsPerPage' => 100,
                'page' => 1,
                'endpoint' => 'admin.system.system_user.capability_route.index',
                'searchKeys' => ['route_name'],
                'col' => [
                    'cols' => 12,
                ],
            ],
            [
                'type' => 'switch',
                'name' => 'strict_route_binding',
                'label' => 'Strict Route Binding',
                'default' => false,
                'hint' => 'When enabled, step-up runs only for bound route names.',
                'col' => [
                    'cols' => 12,
                    'sm' => 6,
                    'md' => 4,
                    'lg' => 3,
                ],
            ],
            [
                'type' => 'switch',
                'name' => 'requires_step_up',
                'label' => 'Require Step-Up',
                'default' => false,
                'col' => [
                    'cols' => 12,
                    'sm' => 6,
                    'md' => 4,
                    'lg' => 3,
                ],
            ],
        ];
    }
}
