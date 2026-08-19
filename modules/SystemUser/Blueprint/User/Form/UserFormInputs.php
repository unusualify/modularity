<?php

namespace Modules\SystemUser\Blueprint\User\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class UserFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'filepond-avatar',
                // 'label' => 'Profile Avatar',
                'name' => 'avatar',
                'allow-image-preview' => true,
                'label-idle' => 'Drop files here...',
                // 'rules' => 'sometimes|required:array',
                'disabled' => true,
                'noSubmit' => true,
                'creatable' => 'hidden',
                'editable' => false,
            ],
            [
                'type' => 'text',
                'label' => 'Email',
                'name' => 'email',
                'rules' => 'sometimes|required',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                // 'prepend-icon' => 'mdi-card-text-outline',
                'dense',
            ],
            [
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
                'rules' => 'sometimes|required',
                'editable' => false,
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                // 'prepend-icon' => 'mdi-card-text-outline',
                'dense',
            ],
            [
                'label' => 'Surname',
                'name' => 'surname',
                'type' => 'text',
                'editable' => false,
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                // 'prepend-icon' => 'mdi-card-text-outline',
                'dense',
            ],
            [
                'name' => 'company_id',
                'label' => 'Company',
                'type' => 'select',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'repository' => 'Modules\\SystemUser\\Repositories\\CompanyRepository',
            ],
            [
                'name' => 'company_name',
                'label' => 'Company Name',
                'placeholder' => 'If you want to create user with a new company, please enter the name here.',
                'hint' => 'If you select a company on the company field, this field will be ignored.',
                'editable' => 'hidden',
                'type' => 'text',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'conditions' => [
                    ['id', 'not exists'],
                ],
            ],
            [
                'type' => 'combobox',
                'label' => 'Roles',
                'name' => 'roles',
                'chips' => true,
                'itemTitle' => 'title',
                'col' => [
                    'cols' => 12,
                    'sm' => 8,
                    'md' => 6,
                ],
                'allowedRoles' => ['superadmin', 'admin'],
                'rules' => 'required',
                // 'editable' => false,
                'connector' => 'SystemUser:Role|repository:list:column=title',
        
                // 'type' => 'select-scroll',
                // 'page' => 1,
                // 'endpoint' => [
                //     'admin.system.system_user.role.index',
                // ],
            ],
        ];
    }
}
