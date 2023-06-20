<?php
namespace OoBook\CRM\Base\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use OoBook\CRM\Base\Support\Finder;
use stdClass;

trait ConfigureViewFields {

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        // $scopes = $this->filterScope($prependScope);
        // $items = $this->getIndexItems($scopes);

        $data = [
            // 'hiddenFilters' => array_keys(Arr::except($this->filters, array_keys($this->defaultFilters))),
            // 'filterLinks' => $this->filterLinks ?? [],

            'initialResource' => $this->getJSONData(), //
            'tableMainFilters' => $this->getIndexTableMainFilters(),
            'filters' => json_decode($this->request->get('filter'), true) ?? [],

            'titleKey' => $this->titleColumnKey,
            'headers' => $this->getIndexTableColumns(), // headers to be used in unusual datatable component
            'formSchema'  => $this->getFormSchema($this->getConfigFieldsByRoute('inputs')), // input fields to be used in unusual datatable component

            'indexEndpoint' => route(
                ($this->isParentRoute() ? '' : $this->getSnakeCase($this->moduleName) . '.')
                    . $this->getSnakeCase($this->routeName)
                    . ".index"
            ), // basic laravel index url for create|edit|store|update|delete routes
            'searchText' =>  request()->has('search') ? request()->query('search') : "", // for current text of search parameter
            'requestFilter' => json_decode(request()->get('filter'), true) ?? [],
            'listOptions' => $this->getVuetifyDatatableOptions(), // options to be used in unusual datatable component

            /***
             * TODO variables to be assigned dynamically
             *
             * */
            // 'createOnModal' => $table_options['createOnModal'] ?? true,
            // 'editOnModal' => $table_options['editOnModal'] ?? true,
            // 'isRowEditing' => $table_options['isRowEditing'] ?? true, // whether row editing is active in unusual datatable component
            // 'actionsType' => "inline", // 'dropdown|inline' for actions of rows in unusual datatable
            'actions' => [
                [
                    'name' => 'edit',
                    'color' => 'green darken-2'
                ],
                [
                    'name' => 'delete',
                    'color' => 'red darken-2'
                ]
            ],

        ] + $this->getIndexUrls() + $this->tableOptions;
        // $baseUrl = $this->getPermalinkBaseUrl();

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'routeName' => $this->getHeadline($this->routeName),

            // 'skipCreateModal' => $this->getIndexOption('skipCreateModal'),
            // 'reorder' => $this->getIndexOption('reorder'),
            // 'create' => $this->getIndexOption('create'),
            // 'duplicate' => $this->getIndexOption('duplicate'),
            // 'translate' => $this->routeHasTrait('translations'),
            // 'translateTitle' => $this->titleIsTranslatable(),
            // 'permalink' => $this->getIndexOption('permalink'),
            // 'bulkEdit' => $this->getIndexOption('bulkEdit'),
            // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,
            // 'baseUrl' => $baseUrl,
            // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl),
            // 'additionalTableActions' => $this->additionalTableActions(),
        ];

        return array_replace_recursive($data + $options, $this->extraIndexData($this->request));
    }

    /** xx
     * @param Request $request
     * @return array
     */
    protected function extraIndexData($request)
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
            )->map(function($item){ return (array) $item;})
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
        try {
            return Collection::make(
                $this->getConfigFieldsByRoute('table_options') ?? $this->defaultTableOptions
            )->toArray();
        } catch (\Throwable $th) {
            return [];
        }
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
        return Collection::make([
            'create',
            'edit',

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

            return [
                $action . 'Endpoint' => $optionIsActive
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

        $data = [
            'editable' => !!$itemId,
            'item' => $item,
            'moduleName' => $this->moduleName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->routePrefix,
            'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,

            'formSchema'  => $schema, // input fields to be used in unusual datatable component
            'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                return [ $item['name'] => $item['default'] ?? ''];
                $carry[$key] = $item->default ?? '';
            })->toArray(),
            'actionUrl' => $itemId ? $this->getModuleRoute($itemId, 'update') : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->submoduleParentId]),
            'async' => true,

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

    public function getInputSchema(stdClass $input)
    {
        if($object = $this->generateCustomInput($input)){
            return $object;
        }

        $default_inputs = collect(Config::get(getUnusualBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        return isset($input->name)
            ? [ $input->name => $default_inputs->merge(collect($input)->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];})) ]
            : [];
    }

    public function generateCustomInput($input)
    {
        $object = null;

        switch ($input->type) {
            case 'custom-input-treeview':
            case 'treeview':
                $relation_model = null;

                // dd(
                //     $this->app['ue_modules']->find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if(!!$input->model){
                    $relation_model = App::make($input->model);
                }else if(!!$input->route){
                    $finder = new Finder();
                    $module = $this->app['ue_modules']->find($this->moduleName);

                    if( $module->isEnabledRoute($input->route) ){
                        if($this->config->parent_route->route_name == $input->route){
                            $table = Str::plural($input->route);
                            $relation_model = $finder->getModel($table);
                        }else if(!!$this->config->parent_route->sub_routes){

                            foreach ($this->config->parent_route->sub_routes as $sr) {
                                if($sr->route_name == $input->route){
                                    $table = Str::plural($input->route);
                                    $relation_model = $finder->getModel($table);
                                }
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
                            'children' => $relation_model->all(['id', 'name'])->toArray()
                        ]
                    ]
                ];

                break;

            default:
                # code...
                break;
        }
        return $object;
    }

    protected function getSchemaWiths($schema)
    {
        return collect($schema)->filter(function($item){
            return $this->hasWithModel($item['type']);
        })->map(function($item, $i){
            return $item['name'];
        })->values()->toArray();
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function hasWithModel($type)
    {
        $types = [
            'treeview',
            'custom-input-treeview'
        ];

        return in_array($type, $types);
    }

}
