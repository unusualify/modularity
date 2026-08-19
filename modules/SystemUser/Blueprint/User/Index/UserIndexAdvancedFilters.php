<?php

namespace Modules\SystemUser\Blueprint\User\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class UserIndexAdvancedFilters implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'relations' => [
                [
                    'type' => 'select',
                    'slug' => 'company',
                    'componentOptions' => [
                        'multiple' => true,
                        'clearable' => true,
                        'variant' => 'solo',
                        'label' => 'Company',
                    ],
                    'repository' => 'Modules\\SystemUser\\Repositories\\CompanyRepository',
                ],
            ],
        ];
    }
}
