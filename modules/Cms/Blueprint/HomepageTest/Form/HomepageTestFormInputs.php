<?php

namespace Modules\Cms\Blueprint\HomepageTest\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class HomepageTestFormInputs implements ModuleRouteInputsProvider
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
                'label' => 'Name',
                'translated' => true,
            ],
        ];
    }
}
