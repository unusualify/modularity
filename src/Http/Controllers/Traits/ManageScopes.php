<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait ManageScopes
{
    /**
     * @var array
     */
    protected $defaultFilters;

    /**
     * Additional filters for the index view.
     *
     * To automatically have your filter added to the index view use the following convention:
     * suffix the key containing the list of items to show in the filter by 'List' and
     * name it the same as the filter you defined in this array.
     *
     * Example: 'fCategory' => 'category_id' here and 'fCategoryList' in indexData()
     * By default, this will run a where query on the category_id column with the value
     * of fCategory if found in current request parameters. You can intercept this behavior
     * from your repository in the filter() function.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Fixed filters for the index view.
     *
     *
     * @var array
     */
    protected $fixedFilters = [];

    /**
     * Additional links to display in the listing filter.
     *
     * @var array
     */
    protected $filterLinks = [];

    /**
     * Filters that are selected by default in the index view.
     *
     * Example: 'filter_key' => 'default_filter_value'
     *
     * @var array
     */
    protected $filtersDefaultOptions = [];

    // /**
    //  * Default orders for the index view.
    //  *
    //  * @var array
    //  */
    // protected $defaultOrders = [
    //     'created_at' => 'desc',
    // ];
    protected function __afterConstructManageScopes($app, $request)
    {
        $this->defaultTableOrders = (array) Config::get(modularityBaseKey() . '.default_table_orders', ['created_at' => 'desc']);

        // $this->tableOrders = array_merge_recursive_preserve($this->getTableOrders(), $this->tableOrders ?? []);
        $this->tableOrders = $this->getTableOrders();
    }

    protected function getExactScope()
    {
        $scope = [];

        foreach ($this->fixedFilters as $key => $value) {
            $scope[$key] = $value;
        }

        $configScopes = (array) $this->getConfigFieldsByRoute('scopes', []);

        foreach ($configScopes as $key => $value) {
            $scope[$key] = $value;
        }

        return $scope;
    }

    /**
     * @param array $prepend
     * @return array
     */
    protected function filterScope($prepend = [])
    {
        $scope = [];

        $requestFilters = $this->getRequestFilters();

        $this->filters = array_merge($this->defaultFilters, $this->filters);

        if (array_key_exists('status', $requestFilters)) {
            switch ($requestFilters['status']) {
                // General Filters
                case 'published':
                    $scope['published'] = true;

                    break;
                case 'draft':
                    $scope['draft'] = true;

                    break;
                case 'trash':
                    $scope['onlyTrashed'] = true;

                    break;
                case 'mine':
                    $scope['mine'] = true;

                    break;
                    // Authorizable Filters
                case 'authorized':
                    $scope['hasAnyAuthorization'] = true;

                    break;
                case 'unauthorized':
                    $scope['unauthorized'] = true;

                    break;
                case 'your-authorizations':
                    $scope['isAuthorizedToYou'] = true;

                    break;
                    // Assignable Filters
                case 'my-assignments':
                    $scope['isActiveAssignee'] = true;

                    break;
                case 'your-role-assignments':
                    $scope['isActiveAssigneeForYourRole'] = true;

                    break;
                case 'completed-assignments':
                    $scope['completedAssignments'] = true;

                    break;
                case 'pending-assignments':
                    $scope['pendingAssignments'] = true;

                    break;
                case 'your-completed-assignments':
                    $scope['yourCompletedAssignments'] = true;

                    break;
                case 'your-pending-assignments':
                    $scope['yourPendingAssignments'] = true;

                    break;
                case 'team-completed-assignments':
                    $scope['teamCompletedAssignments'] = true;

                    break;
                case 'team-pending-assignments':
                    $scope['teamPendingAssignments'] = true;

                    break;

                default:
                    $customMainFilters = $this->getConfigFieldsByRoute('table_filters', []);

                    $customMainFilter = Collection::make($customMainFilters)->filter(function ($filter) use ($requestFilters) {
                        return isset($filter->slug) && $filter->slug == $requestFilters['status'];
                    })->first();

                    if ($customMainFilter) {
                        $scope[$customMainFilter->scope ?? $customMainFilter->slug] = true;
                    }

                    break;
            }

            if (! Str::startsWith($requestFilters['status'], 'isStateable')) {
                unset($requestFilters['status']);
            }
        }

        if (array_key_exists('status', $requestFilters)) {
            $code = Str::kebab(Str::after($requestFilters['status'], 'isStateable'));
            $scope['isStateable'] = $code;
            unset($requestFilters['status']);
        }

        foreach ($this->filters as $key => $field) {
            if (array_key_exists($key, $requestFilters)) {
                $value = $requestFilters[$key];
                if ($value == 0 || ! empty($value)) {
                    // add some syntaxic sugar to scope the same filter on multiple columns
                    if ($field != '') {
                        $fieldSplitted = explode('|', $field);
                        if ($key == 'search' && $field != 'search') {
                            $fieldSplitted = explode('|', $field);

                            $scope['searches'] = $fieldSplitted;

                            $scope[$key] = $requestFilters[$key]; // search
                        }
                        if (count($fieldSplitted) > 1) {
                            $requestValue = $requestFilters[$key];

                            // $scope[$scopeKey] =
                            Collection::make($fieldSplitted)->each(function ($scopeKey) use (&$scope, $requestValue) {
                                $scope[$scopeKey] = $requestValue;
                            });
                        } else {
                            $scope[$field] = $requestFilters[$key];
                        }
                    }
                }
            }
        }

        $scope = array_merge($this->getExactScope(), $scope);

        if (array_key_exists('relations', $requestFilters)) {

            foreach ($requestFilters['relations'] as $relationship => $value) {
                $scope['addRelation' . $this->getStudlyName($relationship)] = $value;
            }

            // unset($requestFilters['relations']);
        }

        return $prepend + $scope;
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        $searchFilters = [];

        if ($this->request->has('search')) {
            $searchFilters['search'] = $this->request->get('search');
        }

        $filter = $this->request->get('filter');

        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        return array_merge($searchFilters, $filter ?? []);
    }

    /**
     * @return void
     */
    protected function applyFiltersDefaultOptions()
    {
        if (! count($this->filtersDefaultOptions) || $this->request->has('search')) {
            return;
        }

        $filters = $this->getRequestFilters();

        foreach ($this->filtersDefaultOptions as $filterName => $defaultOption) {
            if (! isset($filters[$filterName])) {
                $filters[$filterName] = $defaultOption;
            }
        }

        $this->request->merge(['filter' => json_encode($filters)]);
    }

    /**
     * @return array
     */
    protected function orderScope()
    {
        $orders = [];

        if ($this->request->has('sortBy')) {

            foreach ($this->request->get('sortBy') as $object) {
                $sort = is_array($object) ? (object) $object : json_decode($object);

                if (preg_match('/(.*)(_timestamp)/', $sort->key, $matches)) {
                    $sort->key = $matches[1];
                }

                if (preg_match('/(.*)(_uuid)/', $sort->key, $matches)) {
                    $sort->key = $matches[1];
                }

                if (preg_match('/(.*)(_relation)/', $sort->key, $matches)) {
                    continue;
                    // dd($sort);
                    $sort->key = $matches[1];
                }

                if ($sort->key == 'name') {
                    $sortBy = $this->titleColumnKey;
                } elseif (! empty($sort->key)) {
                    $sortBy = $sort->key;
                }

                if (isset($sortBy)) {
                    $orders[$this->indexColumns[$sortBy]['sortBy'] ?? $sortBy] = $sort->order;
                }
            }
        }

        // don't apply default orders if reorder is enabled
        // $reorder = $this->getIndexOption('reorder');
        // $defaultOrders = ($reorder ? [] : ($this->defaultOrders ?? []));
        $defaultOrders = $this->getTableOrders();

        return $orders + $defaultOrders;
    }

    protected function getTableOrders()
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve(
                        $this->defaultTableOrders,
                        object_to_array($this->getConfigFieldsByRoute('table_orders') ?? $this->getConfigFieldsByRoute('table_orders') ?? (object) []),
                        $this->tableOrders ?? []
                    )
                )->toArray();
            } catch (\Throwable $th) {
                return $this->defaultTableOrders;
            }
        }

        return array_merge_recursive_preserve($this->tableOrders ?? [], $this->defaultTableOrders);
    }
}
