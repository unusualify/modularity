<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;

trait ManageTable
{
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
                    return $this->dehydrateHeaderSuffix($item) ? $item['key'] : $item['key'];
                })->implode('|'),
            ];
        }

        $this->defaultTableAttributes = (array) Config::get(unusualBaseKey() . '.default_table_attributes');

        $this->tableAttributes = array_merge_recursive_preserve($this->getTableAttributes(), $this->tableAttributes);
    }

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
            // 'name' => unusualTrans("{$this->baseKey}::lang.listing.filter.all-items"),
            'name' => ___('listing.filter.all-items'),
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
                'name' => ___('listing.filter.trash'),
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash', $scope),
            ];
        }

        return $statusFilters;
    }

    public function getIndexTableColumns()
    {
        if ((bool) $this->indexTableColumns) {
            return $this->indexTableColumns;
        } elseif (! $this->config) {
            return [];
        } else {
            $headers = Collection::make($this->getConfigFieldsByRoute('headers'))
                ->map(fn ($item) => (object) [...(array) $item, 'visible' => true]);

            $visibleColumns = explode(',', $this->request->get('columns') ?? $headers->pluck('key')->implode(','));

            return $this->indexTableColumns = $headers->reduce(function ($carry, $item) use ($visibleColumns) {
                $header = $this->getHeader((array) $item);
                if (isset($item->key)) {
                    if ($item->key !== 'actions' && ! in_array($item->key, $visibleColumns)) {
                        $header['visible'] = false;
                    }
                    $carry[] = $header;
                }

                return $carry;
            }, []);
        }

    }

    /**
     * getTableOptions
     *
     * @return void
     */
    public function getTableAttributes()
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve($this->defaultTableAttributes, object_to_array($this->getConfigFieldsByRoute('table_options')))
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
        if ($this->getIndexOption('duplicate')) {
            $actions[] = [
                'name' => 'duplicate',
                // 'icon' => '$edit',
                'color' => 'primary darken-2',
            ];
        }

        if ($this->getIndexOption('edit')) {
            $actions[] = [
                'name' => 'edit',
                // 'can' => $this->permissionPrefix(Permission::EDIT->value),
                // 'color' => 'green darken-2',
                'color' => 'primary darken-2',
            ];
        }

        if ($this->getIndexOption('delete')) {
            $actions[] = [
                'name' => 'delete',
                'can' => $this->permissionPrefix(Permission::DELETE->value),
                'variant' => 'outlined',
                // 'color' => 'red darken-2',
                'color' => 'error',
            ];
        }

        if ($this->getIndexOption('restore')) {
            $actions[] = [
                'name' => 'restore',
                // 'icon' => '$',
                'can' => 'restore',
                // 'color' => 'red darken-2',
                'color' => 'green',
            ];
        }

        if ($this->getIndexOption('forceDelete')) {
            $actions[] = [
                'name' => 'forceDelete',
                'icon' => '$delete',
                'can' => 'forceDelete',
                // 'color' => 'red darken-2',
                'color' => 'red',
            ];
        }
        // if $this->repository has hasPayment
        if (classHasTrait($this->repository->getModel(), 'Unusualify\Modularity\Entities\Traits\HasPayment')) {
            $actions[] = [
                'name' => 'pay',
                'icon' => 'mdi-contactless-payment',
                'forceLabel' => true,
                // 'can' => 'pay',
                // 'color' => 'red darken-2',
                'color' => 'primary',
                'form' => [
                    'attributes' => [
                        'title' => [
                            'text' => 'PAYMENT AND INVOICES',
                            'tag' => 'div',
                            'type' => 'p',
                            'weight' => 'medium',
                            'align' => 'center',
                            'justify' => 'left',
                            'margin' => 'a-5',
                            'color' => 'default',
                            'classes' => 'justify-content-between',
                        ],
                        // 'systembar' => true,
                        'schema' => $this->createFormSchema($this->repository->getPaymentFormSchema()),
                        'actionUrl' => route('admin.system.system_payment.payment'),
                        'async' => false,
                    ],
                    'model_formatter' => [
                        'price_id' => 'price.id', //lodash get method
                    ],
                    'schema_formatter' => [
                        'payment_service.price' => '_price', //lodash set method
                        'payment_service.currency' => 'price.currency.id',
                    ],
                ],
                //  admin.system.system_payment.payment routeName
                //  admin.crm.template/system/system-payments/pay/{price}
            ];
            // dd($actions);
        }

        $actions = array_merge(
            $actions,
            Modularity::find($this->moduleName)->getNavigationActions($this->routeName)
        );

        if (count($actions) > 3) {
            $this->tableAttributes['rowActionsType'] = 'dropdown';
        }

        // dd($actions);
        return $actions;
    }

    /**
     * getTableOption
     *
     * @param mixed $option
     * @return void
     */
    public function getTableOption($option)
    {
        return $this->tableOptions[$option] ?? false;
    }

    /**
     * method that checks whether the attribute configured on table_options
     * and returns its value or false if not.
     *
     *
     * @param mixed $attribute
     * @return bool|mixed returns referenced value or false if it's not defined at module config->table_options
     */
    public function getTableAttribute($attribute)
    {
        return $this->tableAttributes[$attribute] ?? null;
    }

    /**
     * getVuetifyDatatableOptions
     *
     * @return void
     */
    public function getVuetifyDatatableOptions()
    {
        return [
            'page' => request()->has('page') ? intval(request()->query('page')) : 1,
            'itemsPerPage' => request()->has('itemsPerPage') ? intval(request()->query('itemsPerPage')) : ($this->getTableAttribute('itemsPerPage') ?? $this->perPage ?? 10),
            'sortBy' => request()->has('sortBy') ? [request()->get('sortBy')] : [],
            'groupBy' => [],
            'search' => '',
            // 'multiSort'     => true,
            // 'mustSort'      => false,
            // 'groupDesc'     => [],
            // 'sortDesc'      => request()->has('sortDesc') ? [request()->get('sortDesc')] : [],
        ];
    }

    protected function getHeader($header)
    {
        return array_merge_recursive_preserve(unusualConfig('default_header'), $this->hydrateHeader($header));
    }

    protected function hydrateHeader($header)
    {
        $this->hydrateHeaderSuffix($header);
        // add edit functionality to table title cell
        if ($this->titleColumnKey == $header['key'] && ! isset($header['formatter'])) {
            $header['formatter'] = [
                'edit',
            ];
        }

        // switch column
        if (isset($header['formatter']) && count($header['formatter']) && $header['formatter'][0] == 'switch') {
            $header['width'] = '20px';
            // $header['align'] = 'center';
        }

        if (isset($header['sortable']) && $header['sortable']) {
            if (preg_match('/(.*)(_relation)/', $header['key'], $matches)) {
                $header['sortable'] = false;
            }

        }

        if ($header['key'] == 'actions') {
            $header['width'] ??= '100px';
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
        }

        return $header;
    }

    protected function setTableAttributes($tableOptions = null)
    {
        if ($tableOptions) {
            $this->tableAttributes = array_merge_recursive_preserve(
                $this->defaultTableAttributes,
                $tableOptions,
            );

        }

        return $this;

    }

    protected function getTableBulkActions(): array
    {
        $actions = [];

        if ($this->getIndexOption('delete')) {
            $actions[] = [
                'name' => 'bulkDelete',
                'can' => $this->permissionPrefix(Permission::DELETE->value),
                'icon' => '$delete',
                // 'color' => 'red darken-2',
                'color' => 'primary',
            ];
        }

        if ($this->getIndexOption('forceDelete')) {
            $actions[] = [
                'name' => 'bulkForceDelete',
                'icon' => '$delete',
                'can' => 'forceDelete',
                // 'color' => 'red darken-2',
                'color' => 'red',
            ];
        }

        if ($this->getIndexOption('restore')) {
            $actions[] = [
                'name' => 'bulkRestore',
                'icon' => '$restore',
                'can' => 'restore',
                // 'color' => 'red darken-2',
                'color' => 'green',
            ];
        }

        return $actions;
    }

    protected function hydrateHeaderSuffix(&$header)
    {
        if ($this->isRelationField($header['key'])) {
            $header['key'] .= '_relation';
        }

        if (method_exists($this->repository->getModel(), 'isTimestampColumn') && $this->repository->isTimestampColumn($header['key'])) {
            $header['key'] .= '_timestamp';
        }

        // add uuid suffix for formatting on view
        if ($header['key'] == 'id' && $this->repository->hasModelTrait('Unusualify\Modularity\Entities\Traits\HasUuid')) {
            $header['key'] .= '_uuid';
            $header['formatter'] ??= ['edit'];
        }

    }

    protected function dehydrateHeaderSuffix(&$header)
    {
        $header['key'] = preg_replace('/_relation|_timestamp|_uuid/', '', $header['key']);
    }

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

    protected function relationsFilterConfiguration($filter)
    {
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $filter;
    }

    protected function detailFilterConfiguration($filter)
    {
        if (method_exists(__TRAIT__, $methodName = 'getTableAdvancedFilters' . $this->getStudlyName($filter['type']))) {
            $filter = $this->$methodName($filter);
        }

        return $filter;
    }

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
                $items = $items->map(function (Model $item) use ($class) {
                    $item->setAttribute('type', $class);

                    return $item;
                });
            }
        }

        $filter['componentOptions']['items'] = $items->toArray();

        return $filter;
    }

    protected function getTableAdvancedFiltersDatePicker($filter)
    {

        $filter['componentOptions']['title'] ??= $this->getHeadline($filter['slug']);
        $filter['componentOptions']['multiple'] ??= 'range';

        return $filter;
    }

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

    public function translateHeaders($headers)
    {
        foreach ($headers as $key => $value) {

            if (! isset($headers[$key]['title'])) {
                continue;
            }

            $title = $headers[$key]['title'];
            $tableHeader = 'table-headers.' . $title;
            $translation = __($tableHeader);

            if (! is_array($translation) && $translation !== $tableHeader) {
                $headers[$key]['title'] = $translation;
            }
        }

        return $headers;
    }
}
