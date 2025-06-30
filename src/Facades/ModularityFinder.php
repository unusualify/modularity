<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|false getModel(string $table)
 * @method static string|false getRouteModel(string $routeName, bool $asClass = false)
 * @method static string|false getRepository(string $table)
 * @method static string|false getRouteRepository(string $routeName, bool $asClass = false)
 * @method static array getPossibleModels(string $routeName)
 * @method static array getClasses(string $path)
 * @method static \Illuminate\Support\Collection getAllModels()
 *
 * @see \Unusualify\Modularity\Support\Finder
 */
class ModularityFinder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Unusualify\Modularity\Support\Finder::class;
    }
}
