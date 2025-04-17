<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Enums\Permission;

trait TableFilters
{

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
            'number' => $this->repository->getCountByStatusSlug('all', $scope),
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
                'number' => $this->repository->getCountByStatusSlug('published', $scope),
            ];
        }

        if ($this->getIndexOption('publish')) {
            // $statusFilters[] = [
            //     'name' => ___("listing.filter.published"),
            //     'slug' => 'published',
            //     'number' => $this->repository->getCountByStatusSlug('published', $scope),
            // ];
            // $statusFilters[] = [
            //     'name' => ___("listing.filter.draft"),
            //     'slug' => 'draft',
            //     'number' => $this->repository->getCountByStatusSlug('draft', $scope),
            // ];
        }

        if ($this->getIndexOption('restore') && $this->repository->isSoftDeletable()) {
            $statusFilters[] = [
                'name' => ___('listing.filter.trash'),
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash', $scope),
            ];
        }

        if (classHasTrait($this->repository->getModel(), 'Unusualify\Modularity\Entities\Traits\HasStateable')) {
            $statusFilters = array_merge(
                $statusFilters,
                $this->repository->getStateableFilterList(),
            );
        }

        if (classHasTrait($this->repository->getModel(), 'Unusualify\Modularity\Entities\Traits\HasAuthorizable')) {
            $statusFilters[] = [
                'name' => ___('listing.filter.authorized'),
                'slug' => 'authorized',
                'number' => $this->repository->getCountFor('hasAnyAuthorization'),
            ];

            $statusFilters[] = [
                'name' => ___('listing.filter.unauthorized'),
                'slug' => 'unauthorized',
                'number' => $this->repository->getCountFor('unauthorized'),
            ];

            $statusFilters[] = [
                'name' => ___('listing.filter.your-authorizations'),
                'slug' => 'your-authorizations',
                'number' => $this->repository->getCountFor('authorizedToYou'),
            ];
        }

        $statusFilters = array_values(array_filter($statusFilters, function ($filter) {
            return $filter['number'] > 0 || in_array($filter['slug'], ['trash', 'all']);
        }));

        return $statusFilters;
    }

    /**
     * Get the advanced filters for the table
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
