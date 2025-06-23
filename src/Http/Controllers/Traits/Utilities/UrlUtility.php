<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

trait UrlUtility
{
    /**
     * @param string $moduleName
     * @param string $routePrefix
     * @return array
     */
    protected function getUrls()
    {
        return getModularityDefaultUrls();
    }

    /**
     * @param string $moduleName
     * @param string $routePrefix
     * @return array
     */
    protected function getIndexUrls()
    {

        // 'indexEndpoint' => route(
        //     ($this->isParentRoute() ? '' : $this->getSnakeCase($this->moduleName) . '.')
        //         . $this->getSnakeCase($this->routeName)
        //         . ".index"
        // ), // basic laravel index url for create|edit|store|update|delete routes

        return Collection::make([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            // 'delete',

            'forceDelete',
            'restore',
            'duplicate',
            'reorder',
            // 'show',

            // 'publish',
            // 'bulkPublish',

            // 'feature',
            // 'bulkFeature',
            'bulkForceDelete',
            'bulkRestore',
            'bulkDelete',
        ])->mapWithKeys(function ($action) {

            // $parameters = $this->submodule ? [$this->submoduleParentId] : [];
            $parameters = [];

            if ($this->isNested) {
                // $parameters[Str::camel($this->moduleName)] = $this->parentId;
                $parameters[$this->nestedParentName] = $this->nestedParentId;

            }
            $optionIsActive = $this->getIndexOption($action);

            // if(!$optionIsActive && !preg_match('/edit|create|forceDelete|restore/', $action)){
            //     dd($action);
            // }

            $prefix = $this->routePrefix;
            // dd(moduleRoute(
            //     $this->getConfigFieldsByRoute('route_name'),
            //     $prefix,
            //     'store',
            //     $parameters),
            //     $this->getConfigFieldsByRoute('route_name'),
            //     $action,
            //     );

            if (! in_array($action, ['index', 'create', 'store'])) {
                $prefix = $this->generateRoutePrefix(noNested: true);
            }

            return [
                // $action . 'Endpoint' => $optionIsActive
                $action => $optionIsActive
                            ? moduleRoute(
                                $this->getConfigFieldsByRoute('route_name'),
                                $prefix,
                                $action,
                                $parameters
                            )
                            : null,
            ];

        })->toArray();
        // + ['languages' => route(Route::hasAdminRoute(''))]

    }
}
