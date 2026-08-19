<?php

namespace Modules\SystemUser\Blueprint\Company\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class CompanyIndexWith implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>|list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'country',
        ];
    }
}
