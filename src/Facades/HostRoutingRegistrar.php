<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class HostRoutingRegistrar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'unusualify.hostRouting';
    }
}
