<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Enums\Permission;

trait ManageTable {

    /**
     * @var array
     */
    protected $indexTableColumns;


    /**
     * @var array
     */
    protected $defaultTableAttributes = [
        // 'embeddedForm' => true,
        // 'createOnModal' => true,
        // 'editOnModal' => true,
        // 'formWidth' => '60%',
        // 'isRowEditing' => false,
        // 'rowActionsType' => 'inline',
        // 'hideDefaultFooter' => false,
    ];

    /**
     * @var array
     */
    protected $tableAttributes = [];

    protected function __afterConstructManageTable($app, $request) {

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
                'search' => collect( $this->indexTableColumns ?? [] )->filter(function ($item) {
                    return isset($item['searchable']) ? $item['searchable'] : false;
                })->map(function($item){
                    return $item['key'];
                })->implode('|')
            ];
        }

        $this->defaultTableAttributes = (array) Config::get(unusualBaseKey() . '.default_table_attributes');

        $this->tableAttributes = $this->getTableAttributes();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    protected function getTableMainFilters($scopes = [])
    {
        $statusFilters = [];

        $scope = ($this->isNested ? [
            $this->getParentModuleForeignKey() => $this->parentId,
        ] : []) + $scopes;

        $statusFilters[] = [
            // 'name' => unusualTrans("{$this->baseKey}::lang.listing.filter.all-items"),
            'name' => ___("listing.filter.all-items"),
            'slug' => 'all',
            'number' => $this->repository->getCountByStatusSlug('all', $scope),
        ];

        // if ($this->routeHasTrait('revisions') && $this->getIndexOption('create')) {
        //     $statusFilters[] = [
        //         'name' => unusualTrans("$this->baseKey::lang.listing.filter.mine"),
        //         'slug' => 'mine',
        //         'number' => $this->repository->getCountByStatusSlug('mine', $scope),
        //     ];
        // }

        $fillables = $this->repository->getFillable();

        if(in_array('published', $fillables)){
            $statusFilters[] = [
                'name' => ___("listing.filter.published"),
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

        // $statusFilters[] = [
        //     'name' => ___("listing.filter.trash"),
        //     'slug' => 'trash',
        //     'number' => $this->repository->getCountByStatusSlug('trash', $scope),
        // ];

        // dd(
        //     $this->repository,
        //     $this->repository->isSoftDeletable(),
        //     $this->getIndexOption('restore'),
        //     $this->getIndexOption('forceDelete')
        //     // get_class_methods($this->repository->getModel())
        // );
        if ($this->getIndexOption('restore') && $this->repository->isSoftDeletable()) {
            $statusFilters[] = [
                'name' => ___("listing.filter.trash"),
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash', $scope),
            ];
        }

        return $statusFilters;
    }

    public function getIndexTableColumns()
    {
        if(!!$this->indexTableColumns)
            return $this->indexTableColumns;
        else if(!$this->config)
            return [];
        else{
            return $this->indexTableColumns = Collection::make(
                $this->getConfigFieldsByRoute('headers')
            )->map(function($item){
                // dd( (array) $item + unusualConfig('default_header'), $item);
                return $this->getHeader((array) $item);
            })
            ->toArray();
        }

    }

    /**
     * getTableOptions
     *
     * @return void
     */
    public function getTableAttributes()
    {
        if(!!$this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve($this->defaultTableAttributes, (array)$this->getConfigFieldsByRoute('table_options'))
                )->toArray();
            } catch (\Throwable $th) {
                return [];
            }
        }

        return [];
    }

    protected function getTableActions()
    {
        $actions = [];
        // dd(
        //     $this->getIndexOption('duplicate'),
        //     $this->getIndexOption('edit'),
        //     $this->getIndexOption('delete'),
        //     $this->getIndexOption('forceDelete'),
        //     $this->getIndexOption('restore'),
        // );
        if($this->getIndexOption('duplicate') ){
            $actions[] = [
                'name' => 'duplicate',
                // 'icon' => '$edit',
                'color' => 'primary darken-2',
            ];
        }

        if($this->getIndexOption('edit') ){
            $actions[] = [
                'name' => 'edit',
                // 'can' => $this->permissionPrefix(Permission::EDIT->value),
                // 'color' => 'green darken-2',
                'color' => 'primary darken-2',
            ];
        }

        if($this->getIndexOption('delete')){
            $actions[] = [
                'name' => 'delete',
                'can' => $this->permissionPrefix(Permission::DELETE->value),
                // 'color' => 'red darken-2',
                'color' => 'primary',
            ];
        }

        if($this->getIndexOption('restore')){
            $actions[] = [
                'name' => 'restore',
                // 'icon' => '$',
                'can' => 'restore',
                // 'color' => 'red darken-2',
                'color' => 'green',
            ];
        }

        if($this->getIndexOption('forceDelete')){
            $actions[] = [
                'name' => 'forceDelete',
                'icon' => '$delete',
                'can' => 'forceDelete',
                // 'color' => 'red darken-2',
                'color' => 'red',
            ];
        }


        return $actions;
    }

    /**
     * getTableOption
     *
     * @param  mixed $option
     * @return void
     */
    public function getTableOption($option)
    {
        return $this->tableOptions[$option] ?? false;
    }

    /**
     * getVuetifyDatatableOptions
     *
     * @return void
     */
    public function getVuetifyDatatableOptions()
    {
        return [
            'page'          => request()->has('page') ? intval(request()->query('page')) : 1,
            'itemsPerPage'  => request()->has('itemsPerPage') ? intval(request()->query('itemsPerPage')) : ($this->perPage ?? 10),
            'sortBy'        => request()->has('sortBy') ? [request()->get('sortBy')] : [],
            'groupBy'       => [],
            'search'        => ''
            // 'multiSort'     => true,
            // 'mustSort'      => false,
            // 'groupDesc'     => [],
            // 'sortDesc'      => request()->has('sortDesc') ? [request()->get('sortDesc')] : [],
        ];
    }

    protected function getHeader($header)
    {
        return array_merge_recursive_preserve( unusualConfig('default_header'), $this->hydrateHeader($header) );
    }

    protected function hydrateHeader($header)
    {
        if($this->isRelationField($header['key']))
            $header['key'] .= '_relation';

        // add edit functionality to table title cell
        if($this->titleColumnKey == $header['key'] && !isset($header['formatter']))
            $header['formatter'] = [
                'edit'
            ];

        // switch column
        if(isset($header['formatter']) && count($header['formatter']) && $header['formatter'][0] == 'switch'){
            $header['width'] = '20px';
            // $header['align'] = 'center';
        }

        if($header['key'] == 'actions'){
            $header['width'] ??= '100px';
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
        }

        return $header;
    }

}
