<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;

trait ManageTable
{
    use Table\TableAttributes,
        Table\TableColumns,
        Table\TableFilters,
        Table\TableRows,
        Table\TableBulkActions,
        Table\TableActions;

    /**
     * @param Application $app
     * @param Request $request
     * @return void
     */
    protected function __afterConstructManageTable($app, $request)
    {

        $this->getTableDraggableOptions();
        /*
         * Available columns of the index view
         */
        $this->indexTableColumns = $this->getIndexTableColumns();

        /*
         * Default filters for the index view
         * By default, the search field will run a like query on the title field
         */
        if (! isset($this->defaultFilters)) {
            $this->defaultFilters = [
                'search' => collect($this->indexTableColumns ?? [])->filter(function ($item) {
                    return isset($item['searchable']) ? $item['searchable'] : false;
                })->map(function ($item) {
                    $this->dehydrateHeaderSuffix($item);
                    $searchKey = $item['searchKey'] ?? $item['key'];
                    return $searchKey;
                })->implode('|'),
            ];
        }

        $this->defaultTableAttributes = (array) Config::get(modularityBaseKey() . '.default_table_attributes');

        $this->tableAttributes = array_merge_recursive_preserve($this->getTableAttributes(), $this->tableAttributes ?? []);
    }

    /**
     * Get the default table options
     *
     * @return array
     */
    public function getDefaultTableOptions()
    {
        return [
            'itemsPerPage' => $this->getTableAttribute('itemsPerPage') ?? $this->perPage ?? 10,
            'page' => 1,
            'search' => '',
            'sortBy' => [],
            'groupBy' => [],
        ];
    }

    /**
     * getVuetifyDatatableOptions
     *
     * @return void
     */
    public function getVuetifyDatatableOptions()
    {
        return array_merge($this->getDefaultTableOptions(), [
            'page' => request()->has('page') ? intval(request()->query('page')) : 1,
            'itemsPerPage' => request()->has('itemsPerPage') ? intval(request()->query('itemsPerPage')) : ($this->getTableAttribute('itemsPerPage') ?? $this->perPage ?? 10),
            'sortBy' => request()->has('sortBy') ? [request()->get('sortBy')] : [],
            'groupBy' => [],
            'search' => '',
            // 'multiSort'     => true,
            // 'mustSort'      => false,
            // 'groupDesc'     => [],
            // 'sortDesc'      => request()->has('sortDesc') ? [request()->get('sortDesc')] : [],
        ]);
    }

    /**
     * Get the table draggable options
     *
     * @return array
     */
    protected function getTableDraggableOptions()
    {
        if ($this->repository) {
            return [
                'draggable' => classHasTrait($this->repository->getModel(), \Unusualify\Modularity\Entities\Traits\HasPosition::class),
                'orderKey' => 'position',
            ];
        }

        return [
            'draggable' => false,
        ];

    }
}
