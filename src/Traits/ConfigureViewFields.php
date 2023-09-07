<?php
namespace OoBook\CRM\Base\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OoBook\CRM\Base\Support\Finder;
use stdClass;

trait ConfigureViewFields {

    /**
     * @var array
     */
    protected $defaultTableOptions = [
        'createOnModal' => true,
        'editOnModal' => true,
        'isRowEditing' => false,
        'actionsType' => 'inline'
    ];

    /**
     * Relations to eager load for the form view.
     *
     * @var array
     */
    protected $formWith = [];

    /**
     * Relation count to eager load for the form view.
     *
     * @var array
     */
    protected $formWithCount = [];

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        // $scopes = $this->filterScope($prependScope);
        // $items = $this->getIndexItems($scopes);

        $formSchema = $this->getFormSchema($this->getConfigFieldsByRoute('inputs'));

        // dd(
        //     $this->getSchemaWiths($formSchema),
        //     $this->getJSONData($this->getSchemaWiths($formSchema))
        // );
        $data = [
            // 'hiddenFilters' => array_keys(Arr::except($this->filters, array_keys($this->defaultFilters))),
            // 'filterLinks' => $this->filterLinks ?? [],

            'initialResource' => $this->getJSONData($this->getSchemaWiths($formSchema)), //
            'tableMainFilters' => $this->getIndexTableMainFilters(),
            'filters' => json_decode($this->request->get('filter'), true) ?? [],

            'titleKey' => $this->titleColumnKey,
            'headers' => $this->getIndexTableColumns(), // headers to be used in unusual datatable component
            'formSchema'  => $formSchema, // input fields to be used in unusual datatable component

            // 'indexEndpoint' => route(
            //     ($this->isParentRoute() ? '' : $this->getSnakeCase($this->moduleName) . '.')
            //         . $this->getSnakeCase($this->routeName)
            //         . ".index"
            // ), // basic laravel index url for create|edit|store|update|delete routes
            'searchText' =>  request()->has('search') ? request()->query('search') : "", // for current text of search parameter
            'requestFilter' => json_decode(request()->get('filter'), true) ?? [],

            /***
             * TODO variables to be assigned dynamically
             *
             * */
            'actions' => $this->getTableActions(),

            'endpoints' => $this->getIndexUrls()

        ] + $this->tableOptions + $this->getViewLayoutVariables();
        // $baseUrl = $this->getPermalinkBaseUrl();

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'routeName' => $this->getHeadline($this->routeName),
            'listOptions' => $this->getVuetifyDatatableOptions(), // options to be used in unusual datatable component

            'translate' => $this->routeHas('translations'),
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

        return array_replace_recursive($data + $options, $this->indexData($this->request));
    }

    /** xx
     * @param Request $request
     * @return array
     */
    protected function indexData($request)
    {
        return [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    protected function getIndexTableMainFilters($scopes = [])
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

        if ($this->getIndexOption('publish')) {
            $statusFilters[] = [
                'name' => unusualTrans("$this->baseKey::lang.listing.filter.published"),
                'slug' => 'published',
                'number' => $this->repository->getCountByStatusSlug('published', $scope),
            ];
            $statusFilters[] = [
                'name' => unusualTrans("$this->baseKey::lang.listing.filter.draft"),
                'slug' => 'draft',
                'number' => $this->repository->getCountByStatusSlug('draft', $scope),
            ];
        }

        if ($this->getIndexOption('restore')) {
            $statusFilters[] = [
                'name' => unusualTrans("$this->baseKey::lang.listing.filter.trash"),
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash', $scope),
            ];
        }

        return $statusFilters;
    }

    public function getIndexItemData($item)
    {
        # code...
    }
    /**
     * @param \OoBook\CRM\Base\Models\Model $item
     * @return array
     */
    protected function indexItemData($item)
    {
        return [];
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
    public function getTableOptions()
    {
        if(!!$this->config) {
            try {
                return Collection::make(
                    $this->getConfigFieldsByRoute('table_options') ?? $this->defaultTableOptions
                )->toArray();
            } catch (\Throwable $th) {
                return [];
            }
        }

        return [];
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
            'multiSort'     => true,
            'mustSort'      => false,
            'groupBy'       => [],
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
     * @param \OoBook\CRM\Base\Models\Model|null $item
     * @return array
     */
    protected function getFormData($id, $item = null, $nested=null)
    {
        $schema = $this->getFormSchema($this->getConfigFieldsByRoute('inputs'));

        if (!$item && $id) {
            $item = $this->repository->getById(
                $id,
                $this->formWith +  $this->getSchemaWiths($schema),
                $this->formWithCount
            );
        } elseif (! $item && ! $id) {
            $item = $this->repository->newInstance();
        }

        // dd($item);

        $fullRoutePrefix = 'admin.' . ($this->routePrefix ? $this->routePrefix . '.' : '') . $this->moduleName . '.';
        $previewRouteName = $fullRoutePrefix . 'preview';
        $restoreRouteName = $fullRoutePrefix . 'restoreRevision';
        // dd(
        //     $item
        // );
        // $baseUrl = $item->urlWithoutSlug ?? $this->getPermalinkBaseUrl();
        // $localizedPermalinkBase = $this->getLocalizedPermalinkBase();

        $itemId = $this->getItemIdentifier($item);
        // dd($item);
        $data = [
            'formAttributes' => [
                'hasSubmit' => true,
                'stickyButton' => false,
                'modelValue' => $this->repository->getFormFields($item, $schema),
                'title' => ___((!!$itemId ? 'edit-item': 'new-item'), ['item' => $this->routeName]),
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

        // dd(
        //     array_replace_recursive($data, $this->extraFormData($this->request))
        // );
        return array_replace_recursive($data, $this->extraFormData($this->request));
    }

    /**
     * @param int $id
     * @return array
     */
    protected function modalFormData($id)
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

        return array_replace_recursive($data, $this->extraFormData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function extraFormData($request)
    {
        return [];
    }

    public function getFormSchema($inputs)
    {
        return Collection::make( $inputs )->mapWithKeys(function($item, $key){
            return $this->getInputSchema($item);
        })->toArray();
    }

    public function getInputSchema($input)
    {
        // $default_input = collect(Config::get(unusualBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        // $default_input = $this->configureInput(array2Object(Config::get(unusualBaseKey() . '.default_input')));
        $default_input = (array) Config::get(unusualBaseKey() . '.default_input');
        // dd($default_input, $input);
        $input = object2Array($input);

        if($object = $this->hydrateCustomInput($input)){
            $input = $object;
        }

        // dd(
        //     $default_input,
        //     $input,
        //     array_merge_recursive_preserve( $this->configureInput($input), $default_input )
        // );

        return isset($input['name'])
            // ? [ $input->name => $default_input->union( $this->configureInput($input) ) ]
            // ? [ $input['name'] => array_merge_recursive_preserve( $default_input, $this->configureInput($input) ) ]
            ? [ $input['name'] => $this->configureInput( array_merge_recursive_preserve( $default_input, $input )) ]
            : [];
    }

    /**
     * @param Array|stdClass $input
     * @return Collection
     */
    public function configureInput($input)
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
    public function hydrateCustomInput($input)
    {
        $object = null;

        switch ($input['type']) {
            case 'custom-input-treeview':
            case 'treeview':
                $relation_class = null;

                // dd(
                //     $this->app['unusual.repository']->find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = $this->app['unusual.repository']->find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getModel($table);
                            }
                        }
                    }
                }

                $object = [];

                $_input = (array) $input;
                $object[$input->name] =   Arr::except($_input, ['route','model']) + [
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

                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = $this->app['unusual.repository']->find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                            }
                        }
                    }
                }

                // $object[$input['name']] =  Arr::except($input, ['route','model']) + [
                $object =  Arr::except($input, ['route','model', 'repository']) + [
                    'items' => $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray()
                ];

            break;
            case 'select':
            case 'combobox':
                // dd($input);
                $relation_class= null;

                if(isset($input['items'])) break;

                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['default'] = [];
                // $input[] = 'multiple';


                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = $this->app['unusual.repository']->find($this->moduleName);

                    if(!isset($module)){
                        $input['items'] = [];
                        break;
                    }

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                            }
                        }
                    }
                }

                // $object[$input['name']] =  Arr::except($input, ['route','model']) + [
                $object =  Arr::except($input, ['route','model', 'repository']) + [
                    'items' => $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray()
                ];

            break;
            default:

                break;
        }

        if(isset($this->repository)){

            if(method_exists($this->repository->getModel(), 'getTranslatedAttributes') && in_array($input['name'], $this->repository->getTranslatedAttributes()) ){
                $input['translated'] = true;

                // $input['locale_input'] = $input['type'];
                // $input['type'] = 'custom-input-locale';
                $object = $input;
            }

        }

        return $object;
    }

    protected function getSchemaWiths($schema)
    {
        // return collect($schema)->filter(function($item){
        //     return $this->hasWithModel($item['type']);
        // })->map(function($item){
        //     return "{$item['name']}:{$item['itemValue']}";
        // })->toArray();

        return collect($schema)->filter(function($item){
            return $this->hasWithModel($item['type']);
        })->mapWithKeys(function($item){
            $key = $item['name'];
            if(preg_match('/(.*)(_id)/', $key, $matches)){
                $key = $this->getCamelCase($matches[1]);
            }
            return [
                $key => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $item['itemValue']],
                    ['addSelect', $item['itemTitle']]
                ]
            ];
        })->toArray();
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function hasWithModel($type)
    {
        $types = [
            'treeview',
            'custom-input-treeview',
            'custom-input-checklist',
            'select',
        ];


        return in_array($type, $types);
    }

    public function getHeader($header)
    {
        return $this->hydrateCustomHeader($header + unusualConfig('default_header'));
    }

    public function hydrateCustomHeader($header)
    {
        if($this->isRelationField($header['key']))
            $header['key'] .= '_relation';

        return $header;
    }

    public function getTableActions()
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

    public function getViewLayoutVariables() {
        return [
            'pageTitle' => $this->getHeadline($this->routeName) . " Module"
        ];
    }
}
