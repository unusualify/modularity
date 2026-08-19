<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Contracts\ModuleRoute;

use Unusualify\Modularous\ModuleRoute;

/**
 * Invokable ModuleRoute Blueprint provider (any index/form UI field).
 */
interface ModuleRouteBlueprintProvider
{
    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array;
}
