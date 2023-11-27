<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Services\View\UWrapper;
use Unusualify\Modularity\Support\Finder;
use stdClass;

trait ManageUtilities {

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

    /**
     * @var array
     */
    protected $indexTableColumns;

    protected $formSchema;


    protected function __afterConstructManageUtilities($app, $request) {
        $this->defaultTableAttributes = (array) Config::get(unusualBaseKey() . '.default_table_attributes');

        $this->tableAttributes = $this->getTableAttributes();
    }

    protected function __beforeConstructManageUtilities($app, $request) {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
    }

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        $data = [
            // 'hiddenFilters' => array_keys(Arr::except($this->filters, array_keys($this->defaultFilters))),
            // 'filterLinks' => $this->filterLinks ?? [],
            'initialResource' => $this->getJSONData(), //
            'tableMainFilters' => $this->getTableMainFilters(),
            'filters' => json_decode($this->request->get('filter'), true) ?? [],
            'requestFilter' => json_decode(request()->get('filter'), true) ?? [],
            'searchText' =>  request()->has('search') ? request()->query('search') : "", // for current text of search parameter

            'headers' => $this->getIndexTableColumns(), // headers to be used in unusual datatable component
            'formSchema'  => $this->formSchema, // input fields to be used in unusual datatable component
            /***
             * TODO variables to be assigned dynamically
             *
             * */
            // 'actions' => $this->getTableActions(),
            'endpoints' => $this->getIndexUrls(),
        ] + $this->getViewLayoutVariables();
        // $baseUrl = $this->getPermalinkBaseUrl();
        // dd($this->tableAttributes, $this->getViewLayoutVariables());

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'translate' => $this->routeHas('translations'),

            'tableAttributes' => array_merge_recursive_preserve(
                [
                    'name' => $this->getHeadline($this->routeName),
                    'titleKey' => $this->titleColumnKey,
                ],
                $this->tableAttributes,
            )
            + ['nestedData' => $this->getNestedData()]
            + ['rowActions' => $this->getTableActions()],

            'listOptions' => $this->getVuetifyDatatableOptions(), // options to be used in unusual table components in datatable store

            // 'routeName' => $this->getHeadline($this->routeName),
            // 'translateTitle' => $this->titleIsTranslatable(),


            // 'skipCreateModal' => $this->getIndexOption('skipCreateModal'),
            // 'reorder' => $this->getIndexOption('reorder'),
            // 'create' => $this->getIndexOption('create'),
            // 'duplicate' => $this->getIndexOption('duplicate'),
            // 'permalink' => $this->getIndexOption('permalink'),
            // 'bulkEdit' => $this->getIndexOption('bulkEdit'),
            // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,
            // 'baseUrl' => $baseUrl,
            // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl),
            // 'additionalTableActions' => $this->additionalTableActions(),
        ];
        // dd($this->getVuetifyDatatableOptions());
        return array_replace_recursive($data + $options, $this->indexData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexData($request)
    {
        return [];
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return array
     */
    public function indexItemData($item)
    {
        return [];
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

        if ($this->getIndexOption('restore')) {
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

        if($this->getIndexOption('edit') ){
            $actions[] = [
                'name' => 'edit',
                // 'color' => 'green darken-2',
                'color' => 'primary darken-2',
            ];
        }
        if($this->getIndexOption('delete')){
            $actions[] = [
                'name' => 'delete',
                // 'color' => 'red darken-2',
                'color' => 'primary',
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

    /**
     * @param string $moduleName
     * @param string $routePrefix
     * @return array
     */
    protected function getIndexUrls()
    {

        // 'indexEndpoint' => route(
        //     ($this->isParentRoute() ? '' : $this->getSnakeCase($this->moduleName) . '.')
        //         . $this->getSnakeCase($this->routeName)
        //         . ".index"
        // ), // basic laravel index url for create|edit|store|update|delete routes

        return Collection::make([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            // 'delete',

            // 'show',
            // 'update',
            // 'destroy'

            // 'publish',
            // 'bulkPublish',
            // 'restore',
            // 'bulkRestore',
            // 'forceDelete',
            // 'bulkForceDelete',
            // 'reorder',
            // 'feature',
            // 'bulkFeature',
            // 'bulkDelete',
        ])->mapWithKeys(function ($action) {

            // $parameters = $this->submodule ? [$this->submoduleParentId] : [];
            $parameters = [];

            if($this->isNested){
                $parameters[Str::camel($this->moduleName)] = $this->parentId;
            }
            $optionIsActive = $this->getIndexOption($action);
            // dd($this->defaultIndexOptions);
            // if($optionIsActive){
            //     $boundEndpoints =
            // }
            // dd(
            //     $action,
            //     $optionIsActive,

            //     moduleRoute(
            //         $this->routeName,
            //         $this->routePrefix,
            //         $action,
            //         $parameters
            //     ),
            //     route(
            //         ($this->isParentRoute() ? '' : $this->getSnakeCase($this->moduleName) . '.')
            //             . $this->getSnakeCase($this->routeName)
            //             . ".index"
            //     )
            // );
            if(!$optionIsActive){
                dd($action);
            }
            return [
                // $action . 'Endpoint' => $optionIsActive
                $action => $optionIsActive
                                            ?   moduleRoute(
                                                    $this->routeName,
                                                    $this->routePrefix,
                                                    $action,
                                                    $parameters
                                                )
                                            :   null
            ];

        })->toArray();
    }

    /**
     * @param int $id
     * @param \Unusualify\Modularity\Models\Model|null $item
     * @return array
     */
    protected function getFormData($id, $item = null, $nested=null)
    {
        $schema = $this->formSchema;
        if (!$item && $id) {
            $item = $this->repository->getById(
                $id,
                $this->formWith,
                $this->formWithCount
            );
        } elseif (! $item && ! $id) {
            $item = $this->repository->newInstance();
        }

        $fullRoutePrefix = 'admin.' . ($this->routePrefix ? $this->routePrefix . '.' : '') . $this->moduleName . '.';
        $previewRouteName = $fullRoutePrefix . 'preview';
        $restoreRouteName = $fullRoutePrefix . 'restoreRevision';
        // dd(
        //     $item
        // );
        // $baseUrl = $item->urlWithoutSlug ?? $this->getPermalinkBaseUrl();
        // $localizedPermalinkBase = $this->getLocalizedPermalinkBase();

        $itemId = $this->getItemIdentifier($item);

        $data = [
            'translate' => $this->routeHas('translations'),
            'formAttributes' => [
                'hasSubmit' => true,
                'stickyButton' => false,
                'modelValue' => array_merge($this->repository->getFormFields($item, $schema), [
                    'package_continent_id' => 1,
                    // 'packageFeatures' => [
                    //     [
                    //         'package_feature_id' => 1,
                    //         'active' => true
                    //     ],
                    //     [
                    //         'package_feature_id' => 2,
                    //         'active' => false
                    //     ]
                    // ]
                ]),
                // 'title' => ___((!!$itemId ? 'edit-item': 'new-item'), ['item' => $this->routeName]),
                'title' => ___((!!$itemId ? 'edit-item': 'new-item'), ['item' => trans_choice('modules.'. snakeCase($this->routeName), 0)]),
                // 'schema'  => $schema, // input fields to be used in unusual datatable component
                // 'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                //     return [ $item['name'] => $item['default'] ?? ''];
                //     $carry[$key] = $item->default ?? '';
                // })->toArray(),
                // 'actionUrl' => $itemId ? $this->getModuleRoute($itemId, 'update') : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->submoduleParentId]),
            ],
            'endpoints' => [
                (!!$itemId ? 'update' : 'store') => $itemId ? $this->getModuleRoute($itemId, 'update') : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->submoduleParentId])
            ],
            'formStore' => [
                'inputs' => $schema,
                // 'inputs' => $this->repository->getFormFields($item, $schema),
            ]

            // 'editable' => !!$itemId,

            // 'moduleName' => $this->moduleName,
            // 'routeName' => $this->routeName,
            // 'routePrefix' => $this->routePrefix,
            // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,


            // 'publish' => $item->canPublish ?? true,
            // 'publishDate24Hr' => Config::get('twill.publish_date_24h') ?? false,
            // 'publishDateFormat' => Config::get('twill.publish_date_format') ?? null,
            // 'publishDateDisplayFormat' => Config::get('twill.publish_date_display_format') ?? null,
            // 'translate' => $this->routeHasTrait('translations'),
            // 'translateTitle' => $this->titleIsTranslatable(),
            // 'permalink' => $this->getIndexOption('permalink'),
            // 'createWithoutModal' => ! $itemId && $this->getIndexOption('skipCreateModal'),
            // 'form_fields' => $this->repository->getFormFields($item),
            // 'baseUrl' => $baseUrl ?? '',
            // 'localizedPermalinkBase'=>$localizedPermalinkBase ?? '',
            // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl ?? ''),

            // 'editor' => Config::get('twill.enabled.block-editor') && $this->routeHasTrait('blocks') && ! $this->disableEditor,
            // 'blockPreviewUrl' => Route::has('admin.blocks.preview') ? URL::route('admin.blocks.preview') : '#',
            // 'availableRepeaters' => $this->getRepeaterList()->toJson(),
            // 'revisions' => $this->routeHasTrait('revisions') ? $item->revisionsArray() : null,
        ] + (Route::has($previewRouteName) && $itemId ? [
            'previewUrl' => moduleRoute($this->moduleName, $this->routePrefix, 'preview', [$itemId]),
        ] : [])
             + (Route::has($restoreRouteName) && $itemId ? [
            'restoreUrl' => moduleRoute($this->moduleName, $this->routePrefix, 'restoreRevision', [$itemId]),
        ] : []);

        return array_replace_recursive($data, $this->formData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function formData($request)
    {
        return [];
    }

    /**
     * @param int $id
     * @return array
     */
    protected function getModalFormData($id)
    {
        $item = $this->repository->getById($id, $this->formWith, $this->formWithCount);
        $fields = $this->repository->getFormFields($item);
        $data = [];

        if ($this->routeHasTrait('translations') && isset($fields['translations'])) {
            foreach ($fields['translations'] as $fieldName => $fieldValue) {
                $data['fields'][] = [
                    'name' => $fieldName,
                    'value' => $fieldValue,
                ];
            }

            $data['languages'] = $item->getActiveLanguages();

            unset($fields['translations']);
        }

        foreach ($fields as $fieldName => $fieldValue) {
            $data['fields'][] = [
                'name' => $fieldName,
                'value' => $fieldValue,
            ];
        }

        return array_replace_recursive($data, $this->modalFormData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function modalFormData($request)
    {
        return [];
    }

    protected function createFormSchema($inputs)
    {
        return Collection::make( $inputs )->mapWithKeys(function($input, $key){
            return $this->getSchemaInput($input);
        })->toArray();
    }

    protected function getSchemaInput($input)
    {
        // $default_input = collect(Config::get(unusualBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        // $default_input = $this->configureInput(array2Object(Config::get(unusualBaseKey() . '.default_input')));
        $default_input = (array) Config::get(unusualBaseKey() . '.default_input');

        [$hydrated, $arrayable] = $this->hydrateInput(object2Array($input));

        if($arrayable){
            return $hydrated;
        }
        return isset($hydrated['name'])
            // ? [ $input->name => $default_input->union( $this->configureInput($input) ) ]
            // ? [ $input['name'] => array_merge_recursive_preserve( $default_input, $this->configureInput($input) ) ]
            ? [ $hydrated['name'] => $this->configureInput( array_merge_recursive_preserve( $default_input, $hydrated )) ]
            : [];
    }

    /**
     * @param Array|stdClass $input
     * @return Collection
     */
    protected function configureInput($input)
    {
        return collect($input)
            ->mapWithKeys(function($v, $k){
                if($k == 'label' && ___("form-labels.{$v}") !== "form-labels.{$v}")
                    $v = ___("form-labels.{$v}");
                // if($k == 'label')
                //     $v = ___("form-labels.{$v}");

                return is_numeric($k) ? [$v => true] : [$k => $v];
            })
            ->toArray();
    }

    /**
     * @param Array|stdClass $input
     * @return Collection
     */
    protected function hydrateInput($input)
    {
        $data = null;
        $arrayable = false;
        switch ($input['type']) {
            case 'custom-input-treeview':
            case 'treeview':
                $relation_class = null;

                // dd(
                //     Modularity::find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getModel($table);
                            }
                        }
                    }
                }

                $data = [];

                $_input = (array) $input;
                $data[$input->name] =   Arr::except($_input, ['route','model']) + [
                    'items' => [
                        [
                            'id' => -1,
                            'name' => 'Role Group',
                            'children' => $relation_class->all(['id', 'name'])->toArray()
                        ]
                    ]
                ];

            break;
            case 'checklist':
                // dd($input);
                $relation_class = null;

                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['type'] = 'custom-input-checklist';
                $input['default'] = [];
                $items = [];
                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                    $items = $relation_class->list($input['itemTitle'])->toArray();
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                    $items = $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray();
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                                $items = $relation_class->list($input['itemTitle'])->toArray();
                                break;
                            }
                        }
                    }
                }

                $data=  Arr::except($input, ['route','model', 'repository']) + [
                    'items' => $items
                ];

            break;
            case 'select':
            case 'combobox':
                // dd($input);
                $relation_class= null;
                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['default'] ??= [];
                // $input[] = 'multiple';
                if(isset($input['items'])) break;

                $items = [];
                $with = [];

                if(isset($input['cascades'])){
                    // [
                    //     'packageRegions:id,package_continent_id,name',
                    //     'packageRegions.packageCountries:id,package_region_id,name'
                    // ]
                    $with = $input['cascades'];
                }

                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                    $items = $relation_class->list($input['itemTitle'], $with)->toArray();

                    if(isset($input['cascades'])){
                        $patterns = [];
                        foreach ($input['cascades'] as $key => $cascade) {
                            $explodes = explode('.', explode(':', $cascade)[0]);
                            $patterns[] = "/{$this->getSnakeCase(
                                $explodes[count($explodes)-1]
                            )}/";
                        }
                        $flat = Arr::dot($items);
                        $newArray = [];
                        foreach ($flat as $key => $value) {
                            $newKey = preg_replace($patterns, 'items', $key);
                            Arr::set($newArray, $newKey, $value);
                        }

                        $items = $newArray;
                    }

                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                    $items = $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray();

                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                                $items = $relation_class->list($input['itemTitle'])->toArray();
                                break;
                            }
                        }
                    }
                }

                if(count($items) && isset($items[0][$input['itemValue']]) && $items[0][$input['itemValue']]){
                    array_unshift($items, [
                        $input['itemValue'] => 0,
                        $input['itemTitle'] => 'Please Select'
                    ]);
                }


                $data =  Arr::except($input, ['route','model', 'repository', 'cascades']) + [
                    'items' => $items
                ];

            break;
            case 'switch':
            case 'checkbox':
                $input['color'] ??= 'success';
                $input['trueValue'] ??= 1;
                $input['falseValue'] ??= 0;
                $input['hideDetails'] = true;
                $input['default'] = 0;

                $data = $input;
            break;
            case 'repeater':
            case 'custom-input-repeater':
                $relation_class= null;

                $input['type'] = 'custom-input-repeater';
                if( $input['orderable'] ?? false){
                    $input['orderKey'] ??= 'position';
                }
                $input['col'] ??= [
                    'cols' => 12,
                    'sm' => 12,
                    'md' => 12,
                    'lg' => 12,
                    'xl' => 12
                ];

                if(array_key_exists('schema', $input)){
                    $inputStudlyName = '';
                    $inputSnakeName = '';

                    if($input['repository']){
                        if( preg_match( '/(\w+)Repository/', get_class_short_name($input['repository']), $matches)){
                            $relation_class = App::make($input['repository']);
                            $inputStudlyName = $matches[1];
                            $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                            $inputCamelName = $this->getCamelCase($inputStudlyName);
                        }
                    } else if($input['model']){
                        if( preg_match( '/(\w+)/', get_class_short_name($input['model']), $matches)){
                            dd($matches);
                            $relation_class = App::make($input['model']);

                            $inputStudlyName = $matches[1];
                            $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                        }
                    }
                    foreach ($input['schema'] as $key => &$_input) {
                        switch ($_input['type']) {
                            case 'select':
                            case 'combobox':
                            case 'autocomplete':
                                if($inputSnakeName){

                                    if(preg_match("/{$inputSnakeName}_id/", $_input['name'])){ // it means foreign_id of pivot table
                                        if(isset($input['repository'])){
                                            $_input['repository'] ??= $input['repository'];
                                        } else if(isset($input['model'])){
                                            $_input['model'] ??= $input['model'];
                                        }
                                    }else {
                                        $_input['items'] ??= [];
                                    }
                                    break;
                                }
                            default:
                                # code...
                                break;
                        }
                    }

                    $input['schema'] = $this->createFormSchema($input['schema']);
                }

                $data = $input;
            break;
            case 'morphTo':

                if(isset($input['parents'])){
                    $data = [];
                    $arrayable = true;
                    $length = count($input['parents']);

                    $reversedParents = array_reverse($input['parents']);

                    foreach ($reversedParents as $index => $attachable) {
                        $attachable['ext'] = 'morphTo';

                        if($index == ($length-1)){
                            // 'packageRegions:id,package_continent_id,name',
                            // 'packageRegions.packageCountries:id,package_region_id,name'
                            $attachable['cascades'] = [];
                            $selectables = array_values(array_reverse($data));
                            $relationChain = '';
                            foreach($selectables as $j => $item){
                                $foreignKey = $item['name'];
                                $relationshipName = pluralize($this->getCamelNameFromForeignKey($foreignKey));
                                $relationChain .= !$relationChain ? $relationshipName : ".{$relationshipName}";
                                $ownerKey = $j == 0 ? $attachable['name'] : $selectables[$j-1]['name'];
                                $attachable['cascades'][] = $relationChain . ":{$item['itemValue']},{$ownerKey},{$item['itemTitle']}";
                                // $attachable['cascades'][$relationChain . " as {$relationChain}_items"] = [
                                //     ['select', $item['itemValue'] , $ownerKey, $item['itemTitle']]
                                // ];
                            }
                            $attachable['cascade'] = $reversedParents[$index-1]['name'];

                        }else if($index){
                            $attachable['cascade'] = $reversedParents[$index-1]['name'];
                        }

                        if($index !== ($length-1)){
                            $attachable['items'] = [];
                        }

                        $_input = $this->getSchemaInput($attachable);


                        $data += $_input;
                    }
                    $data = array_reverse($data);
                }
            break;
            default:

                break;
        }

        if(isset($this->repository)){

            if( method_exists($this->repository->getModel(), 'getTranslatedAttributes')
                && in_array($input['name'], $this->repository->getTranslatedAttributes())
            ){
                $input['translated'] ??= true;
                // $input['locale_input'] = $input['type'];
                // $input['type'] = 'custom-input-locale';
                $data = $input;
            }

        }

        return [
            $data ? $data : $input,
            $arrayable
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
            $header['width'] ??= '20px';
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
        }

        return $header;
    }

    public function getViewLayoutVariables() {
        return [
            'pageTitle' => $this->getHeadline($this->routeName) . " Module"
        ];
    }

    public function getNestedData() {

        $result = Collection::make($this->getConfigFieldsByRoute('modules', []))->map(function($context, $key){

            $context->title = isset($context->title) ? ___($context->title) : headline($context->title);

            if(isset($context->type)){
                if($context->type == 'formWrapper'){
                    $forms = Collection::make($context->elements)->map(function($element){

                        $schema = $this->createFormSchema(getInputDraft($element->draft));

                        $parameters = Collection::make(Route::getRoutes()->getByName($element->route)->parameterNames())->mapWithKeys(function($parameter, $j){
                            return [ $parameter => ":{$parameter}"];
                        })->toArray();

                        // $modelValueAbstract = $this->getSnakeCase($this->routeName);
                        // if(isset($element->relation)){
                        //     $modelValueAbstract = $element->relation;
                        //     // $this->indexWith[] = $element->relation;
                        // }
                        $modelValueAbstract = isset($element->relation) ? $element->relation : $this->getSnakeCase($this->routeName);
                        return [
                            'title' => isset($element->title) ? ___($element->title) : '',
                            'buttonText' => 'update',
                            'hasSubmit' => true,
                            'modelValue' => "\${$modelValueAbstract}",
                            // 'modelValue' => [],
                            'schema' => $schema,
                            'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                                return [ $item['name'] => $item['default'] ?? ''];
                                $carry[$key] = $item->default ?? '';
                            })->toArray(),
                            'actionUrl' => route($element->route, $parameters),
                        ];
                    })->toArray();

                    $context->elements = UWrapper::makeFormWrapper($forms);
                }
                unset($context->type);
            }


            return $context;
        })->toArray();

        return $result;
    }

    protected function addWithsSchema() : array
    {
        // $this->indexWith += collect($schema)->filter(function($item){

        return collect(array2Object($this->formSchema))->filter(function($input){
            // return $this->hasWithModel($item['type']);
            return in_array($input->type, [
                'treeview',
                'custom-input-treeview',
                // 'checklist',
                // 'custom-input-checklist',
                'select',
                'combobox',
                'autocomplete'
            ]) && !(isset($input->ext) && $input->ext == 'morphTo');
        })->mapWithKeys(function($input){

            $relationship = $this->getCamelNameFromForeignKey($input->name) ?: $input->name;

            // dd($input, $relationship);
            // return [
            //     $relationship
            // ];
            return [
                $relationship => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $input->itemValue ?? 'id'],
                    ['addSelect', $input->itemTitle ?? 'name']
                ]
            ];
        })->toArray();
    }

    protected function addIndexWithsNestedData() : array
    {
        $withs = [];

        foreach ($this->getConfigFieldsByRoute('modules', []) as $key => $item) {
            if(isset($item->type) && $item->type == 'formWrapper'){
                foreach($item->elements as $element){
                    if(isset($element->relation))
                        $withs[] = $element->relation;
                }
            }
        }

        return $withs;

        // return collect($this->getConfigFieldsByRoute('modules', []))->filter(function($item){
        //     return in_array(isset($item->type) ? $item->type : '', [
        //         'formWrapper'
        //     ]);
        // })->map(function($item){
        //     $item->elements;
        //     return [

        //     ];
        // })->toArray();
    }
}
