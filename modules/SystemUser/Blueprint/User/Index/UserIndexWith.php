<?php

namespace Modules\SystemUser\Blueprint\User\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class UserIndexWith implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>|list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'company',
            'roles',
        ];
    }
}
