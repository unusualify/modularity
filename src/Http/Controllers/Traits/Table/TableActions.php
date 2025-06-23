<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Services\Connector;
use Unusualify\Modularity\Traits\Allowable;

trait TableActions
{
    use Allowable;

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

    public function getTableActions(): array
    {
        $defaultTableAction = (array) Config::get(modularityBaseKey() . '.default_table_action', []);

        return Collection::make($this->tableActions)->reduce(function ($acc, $action, $key) use ($defaultTableAction) {
            $noSuperAdmin = $action['noSuperAdmin'] ?? false;
            // $allowedRoles = $action['allowedRoles'] ?? null;
            $action['is'] = true;

            if (isset($action['connector'])) {
                $connector = new Connector($action['connector']);

                $connector->run($action, 'is');
            }

            if (! $action['is']) {
                return $acc;
            }

            $isAllowed = $this->isAllowedItem(
                $action,
                searchKey: 'allowedRoles',
                orClosure: fn ($item) => ! $noSuperAdmin && $this->user->isSuperAdmin(),
            );

            if (! $isAllowed) {
                return $acc;
            }

            // if (!(!$noSuperAdmin && $this->isSuperAdmin()) && $allowedRoles) {

            //     if ($this->doesNotHaveAuthorization($allowedRoles)) {
            //         return $acc;
            //     }
            // }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $route = Route::getRoutes()->getByName($routeName);

                if (count($route->parameterNames()) > 0) {
                    throw new \Exception('Action route must not have parameters: ' . $action['endpoint']);
                }

                $action['endpoint'] = route($routeName);
            }

            if (isset($action['href'])) {
                $action['href'] = resolve_route($action['href']);
            }

            $acc[] = array_merge_recursive_preserve($defaultTableAction, $action);

            return $acc;
        }, []);

    }
}
