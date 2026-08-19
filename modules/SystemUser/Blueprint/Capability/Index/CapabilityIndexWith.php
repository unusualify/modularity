<?php

namespace Modules\SystemUser\Blueprint\Capability\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class CapabilityIndexWith implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>|list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'roles',
            'routes',
        ];
    }
}
