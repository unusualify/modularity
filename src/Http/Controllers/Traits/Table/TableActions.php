<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

trait TableActions
{
    /**
     * @var array
     */
    protected $tableActions = [];

    /**
     * @return void
     */
    protected function __afterConstructTableActions($app, $request)
    {
        $this->setTableActions();
    }

    /**
     * @return void
     */
    protected function setTableActions()
    {
        $this->defaultTableActions = (array) Config::get(modularityBaseKey() . '.default_table_actions', []);

        $tableActions = [];

        if ((bool) $this->config) {
            try {
                $tableActions = Collection::make(
                    array_merge_recursive_preserve($this->defaultTableActions, object_to_array($this->getConfigFieldsByRoute('table_actions', [])))
                )->toArray();
            } catch (\Throwable $th) {

            }
        }

        $this->tableActions = array_merge_recursive_preserve($tableActions, $this->tableActions ?? []);
    }

    /**
     * @return array
     */
    public function getTableActions(): array
    {
        $defaultTableAction = (array) Config::get(modularityBaseKey() . '.default_table_action', []);

        return Collection::make($this->tableActions)->reduce(function ($acc, $action, $key) use ($defaultTableAction) {
            $noSuperAdmin = $action['noSuperAdmin'] ?? false;
            $allowedRoles = $action['allowedRoles'] ?? null;

            if (!(!$noSuperAdmin && $this->isSuperAdmin()) && $allowedRoles) {

                if ($this->doesNotHaveAuthorization($allowedRoles)) {
                    return $acc;
                }
            }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $route = Route::getRoutes()->getByName($routeName);

                if(count($route->parameterNames()) > 0){
                    throw new \Exception('Action route must not have parameters: ' . $action['endpoint']);
                }

                $action['endpoint'] = route($routeName);
            }

            if (isset($action['href']) && ($routeName = Route::hasAdmin($action['href']))) {
                $route = Route::getRoutes()->getByName($routeName);

                if(count($route->parameterNames()) > 0){
                    throw new \Exception('Action route must not have parameters: ' . $action['href']);
                }

                $action['href'] = route($routeName);
            }

            $acc[] = array_merge_recursive_preserve($defaultTableAction, $action);

            return $acc;
        }, []);

    }
}
