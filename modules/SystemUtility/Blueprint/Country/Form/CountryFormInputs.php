<?php

namespace Modules\SystemUtility\Blueprint\Country\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CountryFormInputs implements ModuleRouteInputsProvider
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
            ],
            [
                'type' => 'text',
                'name' => 'code',
                'label' => 'Code',
                'rules' => 'sometimes|required',
            ],
            [
                'type' => 'text',
                'name' => 'phone_code',
                'label' => 'Phone Code',
                'rules' => 'sometimes|required',
            ],
        ];
    }
}
