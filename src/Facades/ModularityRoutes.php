<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class ModularityRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Unusualify\Modularity\Support\ModularityRoutes::class;
    }
}
