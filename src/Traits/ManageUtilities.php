<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Services\View\UWrapper;
use Unusualify\Modularity\Support\Finder;
use stdClass;

trait ManageUtilities {

    use ManageTable, ManageForm;

    protected function __afterConstructManageUtilities($app, $request)
    {

    }

    protected function __beforeConstructManageUtilities($app, $request)
    {
        // $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
    }

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        $initialResource = $this->getJSONData();
        $filters = json_decode($this->request->get('filter'), true) ?? [];

        $_deprecated = [
            'initialResource' => $initialResource, //
            'tableMainFilters' => $this->getTableMainFilters(),
            'filters' => $filters,
            'requestFilter' => json_decode(request()->get('filter'), true) ?? [],
            'searchText' =>  request()->has('search') ? request()->query('search') : "", // for current text of search parameter
            'headers' => $this->getIndexTableColumns(), // headers to be used in unusual datatable component
            'formSchema'  => $this->formSchema, // input fields to be used in unusual datatable component
        ];
        $data = [
            ...$_deprecated,
            'endpoints' => $this->getIndexUrls() + $this->getUrls(),
        ] + $this->getViewLayoutVariables();

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'listOptions' => $this->getVuetifyDatatableOptions(), // options to be used in unusual table components in datatable store
            'tableAttributes' => array_merge(
                [
                    'rowActions' => $this->getTableActions(),
                    'bulkActions' => $this->getTableBulkActions(),
                    'nestedData' => $this->getNestedData()
                ],
                ($this->isNested ? ['titlePrefix' => $this->nestedParentModel->getTitleField() . ' \ ' ] : []),
                array_merge_recursive_preserve(
                    [
                        'name' => $this->getHeadline($this->routeName),
                        'titleKey' => $this->titleColumnKey,
                    ],
                    $this->tableAttributes,
                ),
                $this->getTableDraggableOptions(),

            ),
            'formStore' => [
                'inputs' => $this->formSchema,
                'fields' => []
            ],
            'tableStore' => [
                'baseUrl' => rtrim(config('app.url'), '/') . '/',
                'headers' => $this->getIndexTableColumns(),
                'searchText' => request()->has('search') ? request()->query('search') : "",
                'options' => $this->getVuetifyDatatableOptions(),
                'data' => $initialResource['data'],
                'total' => $initialResource['total'] ?? 0,
                'mainFilters' => $this->getTableMainFilters(),
                'filter' => ['status' =>  $filters['status'] ?? $defaultFilterSlug ?? 'all'],
                'advancedFilters' => $this->getTableAdvancedFilters(),
                // {{-- inputs: {!! json_encode($inputs) !!}, --}}
                // {{-- initialAsync: '{{ count($tableData['data']) ? true : false }}', --}}
                // {{-- name: '{{ $routeName}}', --}}
                // {{-- columns: {!! json_encode($tableColumns) !!}, --}}
            ],
            '__old' => [
                // 'hiddenFilters' => $this->filters(),
                // 'filterLinks' => $this->filterLinks ?? [],

                // 'routeName' => $this->getHeadline($this->routeName),
                // 'translateTitle' => $this->titleIsTranslatable(),
                // 'skipCreateModal' => $this->getIndexOption('skipCreateModal'),
                // 'reorder' => $this->getIndexOption('reorder'),
                // 'permalink' => $this->getIndexOption('permalink'),
                // 'bulkEdit' => $this->getIndexOption('bulkEdit'),
                // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,
                // 'baseUrl' => $baseUrl,
                // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl),
                // 'additionalTableActions' => $this->additionalTableActions(),
            ]


        ];

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

            'forceDelete',

            'restore',
            'duplicate',
            'reorder',
            // 'show',
            // 'update',
            // 'destroy'

            // 'publish',
            // 'bulkPublish',

            // 'reorder',
            // 'feature',
            // 'bulkFeature',
            'bulkForceDelete',
            'bulkRestore',
            'bulkDelete',
        ])->mapWithKeys(function ($action) {

            // $parameters = $this->submodule ? [$this->submoduleParentId] : [];
            $parameters = [];

            if($this->isNested){
                // $parameters[Str::camel($this->moduleName)] = $this->parentId;
                $parameters[$this->nestedParentName] = $this->nestedParentId;

            }
            $optionIsActive = $this->getIndexOption($action);

            // if(!$optionIsActive && !preg_match('/edit|create|forceDelete|restore/', $action)){
            //     dd($action);
            // }

            $prefix = $this->routePrefix;
            // dd(moduleRoute(
            //     $this->getConfigFieldsByRoute('route_name'),
            //     $prefix,
            //     'store',
            //     $parameters),
            //     $this->getConfigFieldsByRoute('route_name'),
            //     $action,
            //     );

            if(!in_array($action, ['index', 'create', 'store'])){
                $prefix = $this->generateRoutePrefix(noNested: true);
            }

            return [
                // $action . 'Endpoint' => $optionIsActive
                $action => $optionIsActive
                            ?   moduleRoute(
                                    $this->getConfigFieldsByRoute('route_name'),
                                    $prefix,
                                    $action,
                                    $parameters
                                )
                            :   null
            ];

        })->toArray();
        // + ['languages' => route(Route::hasAdminRoute(''))]

    }

    /**
     * @param string $moduleName
     * @param string $routePrefix
     * @return array
     */
    protected function getUrls()
    {
        return [
            'languages' => route(Route::hasAdmin('api.languages.index')),
            'base_permalinks' => Arr::mapWithKeys(getLocales(), function($locale, $key){
                extract(parse_url(config('app.url'))); // $scheme, $host
                return [$locale => $host ];
                dd(
                    parse_url(config('app.url')),
                    // config('app.url'),
                    // request()->getHost(),
                    // $locale, $key, getLocales()
                );
            }),
        ];
    }

    /**
     * @param int $id
     * @param \Unusualify\Modularity\Models\Model|null $item
     * @return array
     */
    protected function getFormData($id)
    {
        $schema = $this->formSchema;

        if ($id) {
            $item = $this->repository->getById(
                $id,
                $this->formWith,
                $this->formWithCount
            );
        } else {
            $item = $this->repository->newInstance();
        }

        $fullRoutePrefix = 'admin.' . ($this->routePrefix ? $this->routePrefix . '.' : '') . $this->moduleName . '.';
        $previewRouteName = $fullRoutePrefix . 'preview';
        $restoreRouteName = $fullRoutePrefix . 'restoreRevision';
        // $baseUrl = $item->urlWithoutSlug ?? $this->getPermalinkBaseUrl();
        // $localizedPermalinkBase = $this->getLocalizedPermalinkBase();

        $itemId = $this->getItemIdentifier($item);

        $data = [
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'formAttributes' => [
                'hasSubmit' => true,
                'stickyButton' => false,
                'modelValue' => $this->repository->getFormFields($item, $schema),
                'title' => ___((!!$itemId ? 'edit-item': 'new-item'), ['item' => trans_choice('modules.'. snakeCase($this->routeName), 0)]),
                '__removed' => [
                    // 'title' => ___((!!$itemId ? 'edit-item': 'new-item'), ['item' => $this->routeName]),
                    // 'schema'  => $schema, // input fields to be used in unusual datatable component
                    // 'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                    //     return [ $item['name'] => $item['default'] ?? ''];
                    //     $carry[$key] = $item->default ?? '';
                    // })->toArray(),
                    // 'actionUrl' => $itemId ? $this->getModuleRoute($itemId, 'update') : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->submoduleParentId]),
                ]
            ],
            'endpoints' => [
                (!!$itemId ? 'update' : 'store') => $itemId
                    ? $this->getModuleRoute($itemId, 'update')
                    : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->submoduleParentId])
            ] + $this->getUrls(),
            'formStore' => [
                'inputs' => $schema,
            ],

            '__old' => [
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
            ],

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

    public function getViewLayoutVariables()
    {
        return [
            'pageTitle' => $this->getHeadline($this->routeName) . " Module"
        ];
    }

    public function getNestedData()
    {

        $result = Collection::make($this->getConfigFieldsByRoute('modules', []))->map(function($context, $key){

            $context->title = isset($context->title) ? ___($context->title) : headline($context->title);

            if(isset($context->type)){
                if($context->type == 'formWrapper'){
                    $forms = Collection::make($context->elements)->map(function($element){

                        $routeName = Route::hasAdmin($element->route);

                        if(!$routeName){
                            return false;
                        }

                        $schema = $this->createFormSchema(getFormDraft($element->draft));

                        $parameters = Collection::make(Route::getRoutes()->getByName($routeName)->parameterNames())->mapWithKeys(function($parameter, $j){
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
                            'actionUrl' => route($routeName, $parameters),
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
