<?php

namespace Modules\Cms\Blueprint\PageLayout\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PageLayoutIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => true,
            'editOnModal' => true,
        ];
    }
}
