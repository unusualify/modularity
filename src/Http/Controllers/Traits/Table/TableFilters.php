<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\Traits\Allowable;

trait TableFilters
{
    use Allowable;

    protected function getCountsList($scopes = [])
    {
        $statusFilters = [];

        $scope = $this->nestedParentScopes() + $scopes;
        $statusFilters[] = [
            // 'name' => modularousTrans("{$this->baseKey}::lang.listing.filter.all-items"),
            'name' => ___('listing.filter.all-items'),
            'slug' => 'all',
            'methods' => 'getCountByStatusSlug',
            'force' => true,
            'params' => ['all', $scope],
        ];

        // if ($this->routeHasTrait('revisions') && $this->getIndexOption('create')) {
        //     $statusFilters[] = [
        //         'name' => modularousTrans("$this->baseKey::lang.listing.filter.mine"),
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
            // Raw config path yields objects; ModuleRoute presentation yields arrays.
            $filter = is_array($filter) ? (object) $filter : $filter;

            $statusFilters[] = [
                'name' => $filter->name,
                'slug' => $filter->slug,
                'methods' => 'getCountFor',
                'params' => [$filter->scope ?? $filter->slug],
                ...(isset($filter->allowedRoles) ? ['allowedRoles' => $filter->allowedRoles] : []),
                ...(isset($filter->responsive) ? ['responsive' => $filter->responsive] : []),
                ...(isset($filter->skip_count) ? ['skipCount' => true] : []),
            ];
        }

        return $statusFilters;
    }

    public function handleFilterCount($filter)
    {
        if (! isset($filter['methods'])) {
            throw new \Exception('Number or methods is required for the filter: ' . $filter['slug']);
        }

        if (! isset($filter['params'])) {
            throw new \Exception('Params is required for the filter: ' . $filter['slug']);
        }

        if (is_string($filter['methods'])) {
            // Use cached version of the method if available and caching is enabled
            $method = $filter['methods'];
            $count = $this->repository->{$method}(...$filter['params']);
        } else {
            throw new \Exception('Methods must be a string for the filter: ' . $filter['slug']);
        }

        return $count;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    protected function getTableMainFilters($scopes = [])
    {
        $countsList = $this->getCountsList($scopes);

        $statusFilters = Collection::make($countsList)->reduce(function ($carry, $filter) {
            if (isset($filter['allowedRoles'])) {
                $isAllowed = $this->isAllowedItem(
                    item: ['allowedRoles' => $filter['allowedRoles']],
                    searchKey: 'allowedRoles',
                    disallowIfUnauthenticated: true
                );

                if (! $isAllowed) {
                    return $carry;
                }
            }

            if (! isset($filter['number'])) {
                if ($filter['skipCount'] ?? false) {
                    $filter['number'] = null;
                } else {
                    $count = $this->handleFilterCount($filter);

                    if ($count < 1 && ! ($filter['force'] ?? false)) {
                        return $carry;
                    }

                    $filter['number'] = $count;
                }
            }

            if (isset($filter['responsive'])) {
                $filter = $this->applyResponsiveClasses(
                    item: $filter,
                    searchKey: 'responsive',
                    display: 'flex',
                    classNotation: 'class'
                );
            }

            $carry[] = Arr::except($filter, ['methods', 'params', 'force', 'skipCount']);

            return $carry;
        }, []);

        return $statusFilters;
    }

    public function getMainCountsList()
    {
        $scopes = $this->filterScope($this->nestedParentScopes());

        return $this->getCountsList($scopes);
    }

    /**
     * Get the advanced filters for the table
     *
     * Uses {@see getConfigFieldsByRoute} so nested `index.advanced_filters`, Blueprint
     * class drivers, and legacy flat `filters` all resolve. Query `filters.fixed` stays
     * on PanelController Raw preload and is stripped here when present on the flat key.
     *
     * @return array
     */
    protected function getTableAdvancedFilters()
    {
        $advancedFilters = [];

        $filterConfig = $this->getConfigFieldsByRoute('filters', []);
        if (! is_array($filterConfig)) {
            $filterConfig = object_to_array($filterConfig) ?: [];
        }

        // PanelController owns fixed query scopes via Raw `filters.fixed` — not a category.
        unset($filterConfig['fixed']);

        // Process each filter category
        foreach ($filterConfig as $category => $filters) {
            $filters = is_array($filters) ? $filters : (array) $filters;

            // Apply category-specific configuration
            if (method_exists(__TRAIT__, $method = $category . 'FilterConfiguration')) {
                $filters = array_map([$this, $method], $filters);
            }

            $advancedFilters[$category] = $filters;
        }

        return $advancedFilters;
    }

    /**
     * Configure column filters
     *
     * @param array $filter
     * @return array
     */
    protected function columnsFilterConfiguration($filter)
    {
        // Ensure the column exists in the model
        if (! $this->repository->hasColumn($filter['slug'])) {
            throw new \Exception("Column '{$filter['slug']}' does not exist in the model.");
        }

        // Apply type-specific configuration
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $this->calibrateFilter($filter);
    }

    /**
     * Configure relation filters
     *
     * @param array $filter
     * @return array
     */
    protected function relationsFilterConfiguration($filter)
    {
        // Ensure the relation exists in the model
        $model = $this->repository->getModel();
        $studlyRelationshipName = $this->getStudlyName($filter['slug']);

        if (! method_exists($model, $studlyRelationshipName)) {
            throw new \Exception("Relation '{$filter['slug']}' does not exist in the model.");
        }

        // Apply type-specific configuration
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $this->calibrateFilter($filter);
    }

    protected function detailsFilterConfiguration($filter)
    {
        if (isset($filter->type) && method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter->type))) {
            $filter = $this->$methodName($filter);
        }

        // Mark this as a detail filter
        $filter['_filterType'] = 'detail';

        return $this->calibrateFilter($filter);
    }

    protected function calibrateFilter($filter)
    {
        if (isset($filter['componentOptions']) && isset($filter['componentOptions']['connector'])) {
            $connector = new Connector($filter['componentOptions']['connector']);
            $connector->run(item: $filter['componentOptions'], setKey: 'items');
        }

        if (isset($filter['componentOptions']) && isset($filter['componentOptions']['items']) && is_callable($filter['componentOptions']['items'])) {
            $filter['componentOptions']['items'] = $filter['componentOptions']['items']();
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

        if (isset($filter['repository']) && class_exists($filter['repository'])) {

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
        }

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
