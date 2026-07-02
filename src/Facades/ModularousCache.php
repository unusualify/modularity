<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Services\ModularousCacheService;

/**
 * @method static string getDriver()
 * @method static string getPrefix()
 * @method static void invalidateFormItemCache(string $module, string $routeName, $id)
 * @method static void invalidatePresentationItemCache(string $module, string $routeName, $id)
 * @method static bool isEnabled(?string $module = null, ?string $routeName = null, ?string $type = null)
 * @method static bool usesTags()
 * @method static int getTtl(string $type, ?string $module = null, ?string $routeName = null)
 * @method static string generateCacheKey(string $module, string $routeName, string $type, array $params = [])
 * @method static string generateRecordKey(string $module, string $routeName, $id)
 * @method static array getModuleTags(string $module)
 * @method static array getRouteTags(string $module, string $routeName)
 * @method static array getTypeTags(string $module, string $routeName, string $type)
 * @method static string generateRelationTag(string $modelClass, $id)
 * @method static array generateRelationTags(array $relations)
 * @method static mixed remember(string $key, int $ttl, \Closure $callback, ?string $module = null, ?string $routeName = null)
 * @method static mixed rememberWithRelations(string $key, int $ttl, \Closure $callback, ?string $module = null, ?string $routeName = null, array $relations = [], ?string $type = null)
 * @method static mixed rememberForever(string $key, \Closure $callback, ?string $module = null, ?string $routeName = null)
 * @method static mixed get(string $key, $default = null, ?string $module = null, ?string $routeName = null)
 * @method static mixed getWithRelations(string $key, $default = null, ?string $module = null, ?string $routeName = null, array $relations = [], ?string $type = null)
 * @method static bool put(string $key, $value, int $ttl, ?string $module = null, ?string $routeName = null)
 * @method static bool putWithRelations(string $key, $value, int $ttl, ?string $module = null, ?string $routeName = null, array $relations = [], ?string $type = null)
 * @method static bool has(string $key, ?string $module = null, ?string $routeName = null)
 * @method static bool forget(string $key, ?string $module = null, ?string $routeName = null)
 * @method static bool invalidateModule(string $module)
 * @method static bool invalidateModuleRoute(string $module, string $routeName)
 * @method static bool invalidateByRelatedModel(string $modelClass, $id)
 * @method static int invalidateByRelatedModels(array $relations)
 * @method static int invalidateByPattern(string $pattern)
 * @method static void invalidateForModel(\Illuminate\Database\Eloquent\Model $model, array $types = [], array $options = [])
 * @method static void refreshModelCaches(\Illuminate\Database\Eloquent\Model $model, array $types = [], array $options = [])
 * @method static void purgeModelCacheTypes(\Illuminate\Database\Eloquent\Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null)
 * @method static void warmModuleRouteCaches(string $moduleName, string $moduleRouteName, array $types = [])
 * @method static bool shouldAutoInvalidate(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null)
 * @method static bool hasAdminCacheActions(?string $moduleName = null, ?string $moduleRouteName = null)
 * @method static array getRouteCacheConfig(?string $moduleName, ?string $moduleRouteName)
 * @method static array resolveManualCacheTypes(string $moduleName, string $moduleRouteName)
 * @method static array normalizeCacheTypesInput(array|string $typesInput, string $moduleName, string $moduleRouteName)
 * @method static void warmupModuleRouteCacheCounts(string $moduleName, string $routeName)
 * @method static void invalidateAllItemCaches(string $moduleName, string $moduleRouteName, array $types, bool $shouldWarmDependentModules = true)
 * @method static void invalidateCountCaches(string $module, string $routeName)
 * @method static void invalidateIndexCaches(string $module, string $routeName)
 * @method static bool flush()
 * @method static array getStats(?string $module = null)
 *
 * @see ModularousCacheService
 */
class ModularousCache extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'modularous.cache';
    }
}
