<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerRoutes()
 * @method static void addRoute(string $host, string $action)
 * @method static void removeRoute(string $host)
 * @method static array getRegisteredRoutes()
 * @method static void clearRegisteredRoutes()
 *
 * @see \Unusualify\Modularity\Suppo\HostRouting
 */
class HostRoutingRegistrar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'unusualify.hostRouting';
    }
}
