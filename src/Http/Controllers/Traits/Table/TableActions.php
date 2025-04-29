<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
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

    /**
     * @return array
     */
    public function getTableActions(): array
    {
        $defaultTableAction = (array) Config::get(modularityBaseKey() . '.default_table_action', []);

        return Collection::make($this->tableActions)->reduce(function ($acc, $action, $key) use ($defaultTableAction) {
            $noSuperAdmin = $action['noSuperAdmin'] ?? false;
            // $allowedRoles = $action['allowedRoles'] ?? null;

            $isAllowed = $this->isAllowedItem(
                $action,
                searchKey: 'allowedRoles',
                orClosure: fn($item) => !$noSuperAdmin && $this->user->isSuperAdmin(),
            );

            if(!$isAllowed) {
                return $acc;
            }

            // if (!(!$noSuperAdmin && $this->isSuperAdmin()) && $allowedRoles) {

            //     if ($this->doesNotHaveAuthorization($allowedRoles)) {
            //         return $acc;
            //     }
            // }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $route = Route::getRoutes()->getByName($routeName);

                if(count($route->parameterNames()) > 0){
                    throw new \Exception('Action route must not have parameters: ' . $action['endpoint']);
                }

                $action['endpoint'] = route($routeName);
            }

            if (isset($action['href'])) {
                $routeName = $action['href'];
                $params = [];

                if(is_array($action['href'])){
                    $routeName = $action['href'][0];
                    $params = $action['href'][1] ?? [];
                }

                if(($routeName = Route::hasAdmin($routeName))){
                    $route = Route::getRoutes()->getByName($routeName);

                    if(count($route->parameterNames())){
                        throw new \Exception('Action route must not have parameters: ' . $routeName);
                    }

                    $url = route($routeName);

                    if (count($params) > 0) {
                        // 2) JSON‐encode any value that is an array or object
                        $flat = collect($params)
                            ->mapWithKeys(function($value, $key) {
                                return [
                                    $key => is_array($value) || is_object($value)
                                                ? json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
                                                : $value
                                ];
                            })
                            ->all();

                        // 3) Build the query string (RFC3986 encoding)
                        //    e.g. filter={"foo":"bar"}&page=2
                        $qs = http_build_query($flat, '', '&', PHP_QUERY_RFC3986);

                        // 4) Append “?” + query string to the URL
                        $url .= '?' . $qs;
                    }

                    $action['href'] = $url;
                }

            }

            $acc[] = array_merge_recursive_preserve($defaultTableAction, $action);

            return $acc;
        }, []);

    }
}
