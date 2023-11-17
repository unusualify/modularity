<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;

trait ManageScopes {


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

    /**
     * Default orders for the index view.
     *
     * @var array
     */
    protected $defaultOrders = [
        'created_at' => 'desc',
    ];

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
            }

            unset($requestFilters['status']);
        }

        foreach ($this->filters as $key => $field) {
            if (array_key_exists($key, $requestFilters)) {
                $value = $requestFilters[$key];
                if ($value == 0 || ! empty($value)) {
                    // add some syntaxic sugar to scope the same filter on multiple columns

                    $fieldSplitted = explode('|', $field);

                    if( $key == 'search' && $field != 'search'){
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
        foreach ($this->fixedFilters as $key => $value) {
            $scope[$key] = $value;
        }
        // dd(
        //     $requestFilters,
        //     $this->defaultFilters,
        //     $this->filters,
        //     $this->concreteFilters,

        //     $prepend,
        //     $scope
        // );

        return $prepend + $scope;
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        if ($this->request->has('search')) {
            return ['search' => $this->request->get('search')];
        }

        return json_decode($this->request->get('filter'), true) ?? [];
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

            foreach( $this->request->get('sortBy') as $str ){
                $sort = json_decode($str);

                if ( $sort->key == 'name') {
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
        $reorder = $this->getIndexOption('reorder');
        $defaultOrders = ($reorder ? [] : ($this->defaultOrders ?? []));

        return $orders + $defaultOrders;
    }
}
