<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect\Contracts;

use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectReport;

/**
 * Read-only inspect source for heal/remedy (avoids mocking final Inspector in tests).
 */
interface ModuleRouteInspectSource
{
    public function inspect(?string $moduleName = null, ?string $routeName = null): ModuleRouteInspectReport;
}
