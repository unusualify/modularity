<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getHost()
 * @method static string getSubdomain()
 * @method static string getDomain()
 * @method static string getTld()
 * @method static bool isSubdomain()
 * @method static bool isDomain()
 * @method static bool isTld()
 * @method static array getRoutes()
 * @method static void addRoute(string $host, string $action)
 * @method static void removeRoute(string $host)
 *
 * @see \Unusualify\Modularity\Suppo\HostRouting
 */
class HostRouting extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'unusualify.hosting';
    }
}
