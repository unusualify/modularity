<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class UnusualRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        // return 'unusual';
        return \Unusualify\Modularity\Support\UnusualRoutes::class;
    }
}
