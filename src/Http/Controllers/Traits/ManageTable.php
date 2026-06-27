<?php

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;

trait ManageTable
{
    use Table\TableAttributes,
        Table\TableColumns,
        Table\TableFilters,
        Table\TableRows,
        Table\TableBulkActions,
        Table\TableActions,
        Table\TableItem,
        Table\TableDraggable;

    /**
     * @param Application $app
     * @param Request $request
     * @return void
     */
    protected function __afterConstructManageTable($app, $request)
    {
        $this->getTableDraggableOptions();
    }

    public function preloadManageTable()
    {
        /*
         * Available columns of the index view
         */
        $this->indexTableColumns = $this->getIndexTableColumns();

        /*
         * Default filters for the index view
         * By default, the search field will run a like query on the title field
         */
        $this->setupDefaultFilters();

        $this->defaultTableAttributes = (array) Config::get(modularousBaseKey() . '.default_table_attributes');

        $this->tableAttributes = array_merge_recursive_preserve($this->getTableAttributes(), $this->tableAttributes ?? []);
    }

    public function setupDefaultFilters()
    {
        if (! isset($this->defaultFilters) || empty($this->defaultFilters)) {
            $this->defaultFilters = [
                'search' => collect($this->indexTableColumns ?? [])->filter(function ($item) {
                    return isset($item['searchable']) ? $item['searchable'] : false;
                })->map(function ($item) {
                    $this->dehydrateHeaderSuffix($item);
                    $searchKey = $item['searchKey'] ?? $item['sourceKey'] ?? $item['key'];

                    return $searchKey;
                })->implode('|'),
            ];
        }
    }

    /**
     * Get the default table options
     *
     * @return array
     */
    public function getDefaultTableOptions()
    {
        return [
            'itemsPerPage' => $this->resolveIndexItemsPerPage(),
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
            'itemsPerPage' => request()->has('itemsPerPage')
                ? intval(request()->query('itemsPerPage'))
                : $this->resolveIndexItemsPerPage(),
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
     * Resolve itemsPerPage for index JSON + default table options.
     */
    protected function resolveIndexItemsPerPage(): int
    {
        if ($this->shouldUseUnpaginatedDraggableIndex()) {
            return -1;
        }

        return (int) ($this->getTableAttribute('itemsPerPage') ?? $this->perPage ?? 10);
    }
}
