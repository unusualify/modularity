<?php

namespace Modules\SystemUser\Blueprint\Role\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class RoleIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => false,
            'editOnModal' => true,
            'isRowEditing' => true,
            'rowActionsType' => 'inline',
        ];
    }
}
