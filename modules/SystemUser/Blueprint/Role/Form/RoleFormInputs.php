<?php

namespace Modules\SystemUser\Blueprint\Role\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class RoleFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'text',
                'name' => 'title',
                'label' => 'Title',
                // 'tooltip' => 'Enter a usual name',
                'col' => [
                    'cols' => 10,
                    'sm' => 10,
                    'md' => 6,
                    'lg' => 6,
                    'xl' => 6,
                ],
                'rules' => 'sometimes|required|min:3',
                // 'prepend-icon' => 'mdi-card-text-outline',
            ],
            [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Name',
                'hint' => '',
                'placeholder' => '',
                'default' => '',
                // 'tooltip' => 'Enter a usual name',
                'col' => [
                    'cols' => 10,
                    'sm' => 10,
                    'md' => 6,
                    'lg' => 6,
                    'xl' => 6,
                ],
                'editable' => false,
                'rules' => 'sometimes|required|min:3',
                // 'prepend-icon' => 'mdi-card-text-outline',
            ],

            [
                'type' => 'text',
                'name' => 'guard_name',
                'label' => 'Guard Name',
                'placeholder' => 'modularous',
                '_tooltip' => 'Enter the guard name',
                'default' => 'modularous',
                'col' => [
                    'cols' => 10,
                    'sm' => 10,
                    'md' => 6,
                    'lg' => 6,
                    'xl' => 6,
                ],
                // 'prepend-icon' => 'mdi-account-child',
                'readonly',
                'disabled',
                'flat',
                // 'full-width',
                'hide-spin-buttons',

            ],
            [
                'type' => 'checklist',
                'isTreeview' => true,
                // Large permission catalogs: mount group headers only until expanded.
                'closeAllGroups' => true,
                'name' => 'permissions',
                'label' => 'Permissions of the role',
                'col' => [
                    'cols' => 12,
                    'sm' => 12,
                    'md' => 12,
                    'lg' => 12,
                    'xl' => 12,
                ],
                'connector' => 'SystemUser:Permission|repository',
                'allowedRoles' => ['superadmin', 'admin'],
                // 'route' => 'permission',
                // 'model' => Spatie\Permission\Models\Permission::class,
            ],
        ];
    }
}
