<?php
namespace OoBook\CRM\Base\Traits;

use Illuminate\Support\Collection;

trait ManagesScopes {

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
