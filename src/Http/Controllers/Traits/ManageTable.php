<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Arr;
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
        // 'embeddedForm' => false,
        // 'createOnModal' => true,
        // 'editOnModal' => true,
        // 'formWidth' => '60%',
        // 'isRowEditing' => false,
        // 'rowActionsType' => 'inline',
        // 'hideDefaultFooter' => false,
        // 'striped' => true,
        // 'hideBorderRow' => true,
        // 'roundedRows' => true,
    ];

    /**
     * @var array
     */
    // public $tableAttributes = [];

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

        $this->defaultTableAttributes = (array) Config::get(modularityBaseKey() . '.default_table_attributes');

        $this->tableAttributes = array_merge_recursive_preserve($this->getTableAttributes(), $this->tableAttributes ?? []);
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
                    array_merge_recursive_preserve(
                        $this->defaultTableAttributes,
                        object_to_array($this->getConfigFieldsByRoute('table_attributes') ?? $this->getConfigFieldsByRoute('table_options') ?? (object) []))
                )->toArray();
            } catch (\Throwable $th) {
                return $this->defaultTableAttributes;
            }
        }

        return $this->defaultTableAttributes;
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

    protected function hydrateTableAttributes()
    {
        $attributes = $this->tableAttributes;

        if (isset($attributes['customRow'])) {
            // Handle associative array case
            if (Arr::isAssoc($attributes['customRow'])) {
                if (isset($attributes['customRow']['name'])) {
                    $attributes['customRow'] = $this->hydrateCustomRow($attributes['customRow']);
                }
            }
            // Handle sequential array case with role-based filtering
            else {
                $firstMatch = [];
                foreach ($attributes['customRow'] as $component) {
                    // Skip if component doesn't pass role check
                    if (isset($component['allowedRoles']) &&
                        (! $this->user ||
                        (! $this->user->hasRole($component['allowedRoles'])))
                    ) {
                        continue;
                    }

                    // Convert first matching component to standard format and break
                    if (isset($component['name'])) {
                        $firstMatch = $this->hydrateCustomRow($component);

                        break;
                    }
                }

                $attributes['customRow'] = $firstMatch;
            }
        }

        return $attributes;
    }

    protected function getTableActions()
    {
        $tableActions = [];

        // if $this->repository has hasPayment
        if (classHasTrait($this->repository->getModel(), 'Unusualify\Modularity\Entities\Traits\HasPayment')) {
            $tableActions[] = [
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
                        'price_id' => 'payment_price.id', // lodash get method
                    ],
                    'schema_formatter' => [
                        'payment_service.price_object' => 'payment_price',

                    ],
                ],
                'conditions' => [
                    ['state.code', 'in', ['pending-payment']],
                    ['payable_price.price_including_vat', '>', 0],
                ],
                //  admin.system.system_payment.payment routeName
                //  admin.crm.template/system/system-payments/pay/{price}
            ];
            // dd($actions);
        }

        // duplicate action
        if ($this->getIndexOption('duplicate')) {
            $tableActions[] = [
                'name' => 'duplicate',
                // 'icon' => '$edit',
                'color' => 'primary darken-2',
            ];
        }

        // edit action
        if ($this->getIndexOption('edit')) {
            $tableActions[] = [
                'name' => 'edit',
                // 'can' => $this->permissionPrefix(Permission::EDIT->value),
                // 'color' => 'green darken-2',
                'color' => 'primary darken-2',
            ];
        }

        // delete action
        if ($this->getIndexOption('delete')) {
            $tableActions[] = [
                'name' => 'delete',
                'can' => $this->permissionPrefix(Permission::DELETE->value),
                'variant' => 'outlined',
                // 'color' => 'red darken-2',
                'color' => 'error',
            ];
        }

        // restore action
        if ($this->getIndexOption('restore')) {
            $tableActions[] = [
                'name' => 'restore',
                // 'icon' => '$',
                'can' => 'restore',
                // 'color' => 'red darken-2',
                'color' => 'green',
            ];
        }

        // force delete action
        if ($this->getIndexOption('forceDelete')) {
            $tableActions[] = [
                'name' => 'forceDelete',
                'icon' => '$delete',
                'can' => 'forceDelete',
                // 'color' => 'red darken-2',
                'color' => 'red',
            ];
        }

        // show action
        if ($this->getIndexOption('show')) {
            $tableActions[] = [
                'name' => 'Show',
                'icon' => 'mdi-eye',
                'color' => 'info',
                'show' => true,
                'title' => 'Show Item',
                'widthType' => '',
                'except' => [
                    'actions',
                    'last_activities',
                    'activities',
                    'activities_show',
                    'lastActivities_show',
                ],
                'fullscreen' => true,
            ];
        }

        // activity action
        if ($this->getIndexOption('activity')) {
            $tableActions[] = [
                'name' => 'Last Operations',
                'icon' => 'mdi-book-open-variant',
                'color' => 'grey-darken-2',
                'show' => 'last_activities',
                'conditions' => [
                    ['last_activities', '>', 0],
                ],
                'title' => 'Last Operations',
                'only' => [
                    'created_at' => 'Time',
                    'event' => 'Event',
                    'causer_id' => 'Causer ID',
                    'causer_type' => 'Causer Type',
                    'causer.name' => 'User Name',
                    // 'causer' => 'Causer',
                    'properties.attributes' => 'New Data',
                    'properties.old' => 'Previous Data',
                ],
                // 'except' => [
                //     'batch_uuid',
                // ]
            ];
        }

        // navigation actions
        $tableActions = array_merge(
            $tableActions,
            Modularity::find($this->moduleName)->getNavigationActions($this->routeName)
        );

        // dropdown actions
        if (count($tableActions) > 3) {
            $this->tableAttributes['rowActionsType'] = 'dropdown';
        }

        return $tableActions;
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
        return array_merge_recursive_preserve(modularityConfig('default_header'), $this->hydrateHeader($header));
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

    protected function hydrateCustomRow($customRow)
    {
        return array_merge_recursive_preserve(['col' => ['cols' => 12]], array_diff_key($customRow, ['allowedRoles' => '']));
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
