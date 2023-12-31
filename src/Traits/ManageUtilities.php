<?php
namespace Unusualify\Modularity\Traits;

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

    protected function __afterConstructManageUtilities($app, $request) {

    }

    protected function __beforeConstructManageUtilities($app, $request) {
        // $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
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
            'duplicate'

            // 'show',
            // 'update',
            // 'destroy'

            // 'publish',
            // 'bulkPublish',
            // 'bulkRestore',
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
            if(!$optionIsActive && !preg_match('/edit|create|forceDelete|restore/', $action)){
                dd($action);
            }

            // if($action == 'duplicate'){
            //     dd(
            //         $this->routeName,
            //         $this->routePrefix,
            //         $action,
            //         $parameters,
            //         moduleRoute(
            //             $this->routeName,
            //             $this->routePrefix,
            //             $action,
            //             $parameters
            //         )
            //     );
            // }
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
            $item =
            $this->repository->newInstance();
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

        // dd(
        //     $this->repository->getFormFields($item, $schema),
        //     $schema
        // );

        $data = [
            'translate' => $this->routeHas('translations'),
            'formAttributes' => [
                'hasSubmit' => true,
                'stickyButton' => false,
                'modelValue' => $this->repository->getFormFields($item, $schema),
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
