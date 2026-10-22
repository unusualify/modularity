<?php

namespace Unusualify\Modularous\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Contracts\ModuleableInterface;

trait ModularModel
{
    /**
     * Get the module name from a model instance.
     * Extracts from the model's namespace: \Modules\{ModuleName}\Entities\...
     */
    protected function getModuleNameFromModel(Model $model): ?string
    {
        // First, check if the model defines its module explicitly
        if ($model instanceof CacheableInterface && ($moduleName = $model->getCacheModuleName())) {
            return $moduleName;
        }

        if ($model instanceof ModuleableInterface && ($moduleName = $model->getModuleName())) {
            return $moduleName;
        }

        if ($model instanceof \Unusualify\Modularous\Entities\Model) {
            if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', get_class($model), $matches)) {
                return $matches[1];
            }
        }

        // Try to extract from model namespace
        $class = get_class($model);

        if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', $class, $matches)) {
            return $matches[1];
        }

        // Fallback to table name converted to StudlyCase
        return Str::studly(Str::singular($model->getTable()));
    }

    /**
     * Get the route name (submodule) from a model instance.
     * Uses the model class basename as the route identifier.
     */
    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        // First, check if the model defines its route explicitly
        if ($model instanceof CacheableInterface && ($routeName = $model->getCacheModuleRouteName())) {
            return $routeName;
        }

        if ($model instanceof ModuleableInterface && ($routeName = $model->getRouteName())) {
            return $routeName;
        }

        if ($model instanceof \Unusualify\Modularous\Entities\Model) {
            return $model->getCacheModuleRouteName();
        }

        // Use the model class basename as the route identifier
        $basename = class_basename(get_class($model));

        if ($basename) {
            return $basename;
        }

        // Fallback to table name converted to StudlyCase
        return Str::studly(Str::singular($model->getTable()));
    }
}
