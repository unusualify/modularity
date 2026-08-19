<?php

namespace Modules\SystemUser\Blueprint\Company\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CompanyIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => true,
            'editOnModal' => true,
            'isRowEditing' => true,
            'rowActionsType' => 'inline',
        ];
    }
}
