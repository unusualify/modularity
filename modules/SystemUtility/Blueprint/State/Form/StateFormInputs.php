<?php

namespace Modules\SystemUtility\Blueprint\State\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class StateFormInputs implements ModuleRouteInputsProvider
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
                'rules' => 'sometimes|required',

            ],
            [
                'type' => 'text',
                'name' => 'code',
                'label' => 'Code',
                'rules' => 'sometimes|required',
            ],
            [
                'type' => 'text',
                'name' => 'color',
                'label' => 'Color',
                'rules' => 'sometimes|required',
            ],
            [
                'type' => 'text',
                'name' => 'icon',
                'label' => 'Icon',
                'rules' => 'sometimes|required',
            ],
        ];
    }
}
