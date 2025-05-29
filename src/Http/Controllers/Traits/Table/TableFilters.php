<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Traits\Allowable;

trait TableFilters
{
    use Allowable;

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    protected function getTableMainFilters($scopes = [])
    {
        $statusFilters = [];

        $scope = $this->nestedParentScopes() + $scopes;

        $statusFilters[] = [
            // 'name' => modularityTrans("{$this->baseKey}::lang.listing.filter.all-items"),
            'name' => ___('listing.filter.all-items'),
            'slug' => 'all',
            'methods' => 'getCountByStatusSlug',
            'params' => ['all', $scope],
        ];

        // if ($this->routeHasTrait('revisions') && $this->getIndexOption('create')) {
        //     $statusFilters[] = [
        //         'name' => modularityTrans("$this->baseKey::lang.listing.filter.mine"),
        //         'slug' => 'mine',
        //         'number' => $this->repository->getCountByStatusSlug('mine', $scope),
        //     ];
        // }

        $fillables = $this->repository->getFillable();

        if (in_array('published', $fillables) && $this->repository->hasColumn('published')) {
            $statusFilters[] = [
                'name' => ___('listing.filter.published'),
                'slug' => 'published',
                'methods' => 'getCountByStatusSlug',
                'params' => ['published', $scope],
            ];
            // $statusFilters[] = [
            //     'name' => ___('listing.filter.draft'),
            //     'slug' => 'draft',
            //      'method' => 'getCountByStatusSlug',
            //      'params' => ['draft', $scope],
            //      'number' => $this->repository->getCountByStatusSlug('draft', $scope),
            // ];
        }

        if ($this->getIndexOption('publish')) {

        }

        // SoftDeletable Filters
        if ($this->getIndexOption('restore') && $this->repository->isSoftDeletable()) {
            $statusFilters[] = [
                'name' => ___('listing.filter.trash'),
                'slug' => 'trash',
                'force' => true,
                'methods' => 'getCountByStatusSlug',
                'params' => ['trash', $scope],
            ];
        }

        // repository table filters
        $statusFilters = array_merge(
            $statusFilters,
            $this->repository->getTableFilters($scope),
        );

        $customMainFilters = $this->getConfigFieldsByRoute('table_filters', []);

        foreach ($customMainFilters as $filter) {
            $statusFilters[] = [
                'name' => $filter->name,
                'slug' => $filter->slug,
                'methods' => 'getCountFor',
                'params' => [$filter->scope ?? $filter->slug],
                ...(isset($filter->allowedRoles) ? ['allowedRoles' => $filter->allowedRoles] : []),
            ];
        }

        $statusFilters = Collection::make($statusFilters)->reduce(function ($carry, $filter) {
            if(isset($filter['allowedRoles'])){
                $isAllowed = $this->isAllowedItem(
                    item: ['allowedRoles' => $filter['allowedRoles']],
                    searchKey: 'allowedRoles',
                    disallowIfUnauthenticated: true
                );

                if(!$isAllowed){
                    return $carry;
                }
            }

            if(!isset($filter['number'])){
                if(!isset($filter['methods'])){
                    throw new \Exception('Number or methods is required for the filter: ' . $filter['slug']);
                }

                if(!isset($filter['params'])){
                    throw new \Exception('Params is required for the filter: ' . $filter['slug']);
                }

                if(is_string($filter['methods'])){
                    $count = $this->repository->{$filter['methods']}(...$filter['params']);
                }else{
                    throw new \Exception('Methods must be a string for the filter: ' . $filter['slug']);
                }

                if($count < 1 && !($filter['force'] ?? false)){
                    return $carry;
                }

                $filter['number'] = $count;
            }

            $carry[] = Arr::except($filter, ['methods', 'params', 'force']);

            return $carry;
        }, []);

        return $statusFilters;
    }

    /**
     * Get the advanced filters for the table
     *
     * @return array
     */
    protected function getTableAdvancedFilters()
    {

        $filters = Collection::make($this->getConfigFieldsByRoute('filters'))->filter(function ($f, $key) {
            return in_array($key, ['relations']);
        });

        return $filters->mapWithKeys(function ($filter, $key) {
            if (method_exists(__TRAIT__, $key . 'FilterConfiguration')) {
                return [$key => array_map([$this, $key . 'FilterConfiguration'], object_to_array($filter))];
            }

            return [$key => $filter];
        })->toArray();
    }

    /**
     * Get the relations filter configuration for the table
     *
     * @param array $filter
     * @return array
     */
    protected function relationsFilterConfiguration($filter)
    {
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $filter;
    }

    /**
     * Get the detail filter configuration for the table
     *
     * @param array $filter
     * @return array
     */
    protected function detailFilterConfiguration($filter)
    {
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $filter;
    }

    /**
     * Get the select filter configuration for the table
     *
     * @param array $filter
     * @return array
     */
    protected function getTableAdvancedFiltersSelect($filter)
    {

        $repository = App::make($filter['repository']);
        $items = $repository->list()->map(function ($value, $key) {
            return $value;
        });

        $filter['componentOptions']['item-value'] ??= 'id';
        $filter['componentOptions']['item-title'] ??= 'name';

        $model = $this->repository->getModel();

        $method = $filter['slug'];
        if (method_exists($model, $method)) {
            $returnType = (new \ReflectionMethod($model, $method))->getReturnType();
            if ($returnType == 'Illuminate\Database\Eloquent\Relations\MorphTo') {
                $filter['componentOptions']['return-object'] = 'true';

                $class = get_class($repository->getModel());
                $items = $items->map(function ($item) use ($class) {
                    // $item->setAttribute('type', $class);
                    $item['type'] = $class;

                    return $item;
                });
            }
        }

        $filter['componentOptions']['items'] = $items->toArray();

        return $filter;
    }

    /**
     * Get the date picker filter configuration for the table
     *
     * @param array $filter
     * @return array
     */
    protected function getTableAdvancedFiltersDatePicker($filter)
    {

        $filter['componentOptions']['title'] ??= $this->getHeadline($filter['slug']);
        $filter['componentOptions']['multiple'] ??= 'range';

        return $filter;
    }
}
