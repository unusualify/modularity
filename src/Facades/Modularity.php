<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getModules()
 * @method static array getEnabledModules()
 * @method static bool hasModule(string $name)
 * @method static \Nwidart\Modules\Module findOrFail(string $name)
 * @method static \Nwidart\Modules\Module find(string $name)
 * @method static string getModulePath(string $moduleName)
 * @method static string assetPath(string $module)
 * @method static string moduleAsset(string $module, string $asset)
 * @method static void enableModule(string $moduleName)
 * @method static void disableModule(string $moduleName)
 * @method static void deleteModule(string $moduleName)
 *
 * @see \Unusualify\Modularity\Modularity
 */
class Modularity extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'modularity';
    }
}
