<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Utilities;

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
        return getModularousDefaultUrls();
    }

    /**
     * @param string $moduleName
     * @param string $routePrefix
     * @return array
     */
    protected function getIndexUrls()
    {
        return Collection::make([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            'forceDelete',
            'restore',
            'duplicate',
            'reorder',
            'bulkForceDelete',
            'bulkRestore',
            'bulkDelete',
        ])->mapWithKeys(function ($action) {

            $parameters = [];

            if ($this->isNested) {
                $parameters[$this->nestedParentName] = $this->nestedParentId;

            }
            $optionIsActive = $this->getIndexOption($action);

            $prefix = $this->routePrefix;

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
