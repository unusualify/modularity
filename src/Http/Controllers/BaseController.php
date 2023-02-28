<?php

namespace Unusual\CRM\Base\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
// use Modules\Payment\Repositories\PaymentRepository;
// use Modules\Payment\Http\Requests\PaymentRequest;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Unusual\CRM\Base\Services\MessageStage;
use Unusual\CRM\Base\Transformers\RoleResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Nwidart\Modules\Facades\Module;

abstract class BaseController extends CoreController
{

    public function __construct(
        Application $app,
        Request $request
    )
    {
        parent::__construct($app,$request);

        // $this->setMiddlewarePermission();

        $this->viewPrefix = $this->getViewPrefix();

    }

    public function index($parentModuleId = null)
    {
        $parentModuleId = $this->getParentModuleIdFromRequest($this->request) ?? $parentModuleId;

        $this->submodule = isset($parentModuleId);
        $this->nested = isset($parentModuleId);
        $this->submoduleParentId = $parentModuleId;

        $indexData = $this->getIndexData($this->submodule ? [
            $this->getParentModuleForeignKey() => $this->submoduleParentId,
        ] : []);

        if ($this->request->ajax()) {
            // dd($this->getJSONData());
            return [
                'resource' => $this->getJSONData(),
                'replaceUrl' => true
            ];
            // return $indexData + ['replaceUrl' => true];
        }

        if ($this->request->has('openCreate') && $this->request->get('openCreate')) {
            $indexData += ['openCreate' => true];
        }

        // dd($indexData);
        // dd(
        //     $this,

        //     Collection::make([
        //         "$this->viewPrefix.index",
        //         "base::$this->routeName.index",
        //         "base::layouts.listing",
        //     ])->first(function ($view) {
        //         return View::exists($view);
        //     })
        // );
        $view = Collection::make([
            "$this->viewPrefix.index",
            // "base::$this->routeName.index",
            "base::layouts.index",
            "base::layouts.listing",
        ])->first(function ($view) {
            return View::exists($view);
        });
        // dd($view, $indexData);
        return View::make($view, $indexData);
    }

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        $scopes = $this->filterScope($prependScope);

        $items = $this->getIndexItems($scopes);

        // dd($this->getIndexUrls());
        $data = [
            // 'tableData' => $this->getIndexTableData($items),
            // 'tableColumns' => $this->getIndexTableColumns($items),
            // 'hiddenFilters' => array_keys(Arr::except($this->filters, array_keys($this->defaultFilters))),
            // 'filterLinks' => $this->filterLinks ?? [],

            'initialResource' => $this->getJSONData(), //

            'tableMainFilters' => $this->getIndexTableMainFilters($items),
            'filters' => json_decode($this->request->get('filter'), true) ?? [],

            'titleKey' => $this->titleColumnKey,

            'headers' => $this->getIndexColumns(), // headers to be used in unusual datatable component
            // 'inputs'  => $this->getFormInputs(), // input fields to be used in unusual datatable component
            'formSchema'  => $this->getFormSchema(), // input fields to be used in unusual datatable component

            'indexEndpoint' => route(
                ($this->isParentRoute() ? '' : camelName($this->moduleName) . '.')
                    . camelName($this->routeName)
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
            // 'isRowEditing' => $table_options['editOnModal'] ?? true, // whether row editing is active in unusual datatable component
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

        ] + $this->getIndexUrls() + $this->getTableOptions();

        // $baseUrl = $this->getPermalinkBaseUrl();

        $options = [
            'moduleName' => $this->moduleName,
            'routeName' => $this->routeName,

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

        return array_replace_recursive($data + $options, $this->customIndexData($this->request));
    }

    /**
     * @param array $scopes
     * @param bool $forcePagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIndexItems($scopes = [], $forcePagination = false)
    {
        // dd(
        //     $this->orderScope()
        // );
        return $this->transformIndexItems($this->repository->get(
            $this->indexWith,
            $scopes,
            $this->orderScope(),
            $this->request->get('itemsPerPage') ?? $this->perPage ?? 50,
            $forcePagination
        ));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function transformIndexItems($items)
    {
        return $items;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function getIndexTableData($items)
    {
        $translated = $this->routeHasTrait('translations');

        return $items->map(function ($item) use ($translated) {
            $columnsData = Collection::make($this->indexColumns)->mapWithKeys(function ($column) use ($item) {
                return $this->getItemColumnData($item, $column);
            })->toArray();

            $name = $columnsData[$this->titleColumnKey];

            if (empty($name)) {
                if ($this->routeHasTrait('translations')) {
                    $fallBackTranslation = $item->translations()->where('active', true)->first();

                    if (isset($fallBackTranslation->{$this->titleColumnKey})) {
                        $name = $fallBackTranslation->{$this->titleColumnKey};
                    }
                }

                $name = $name ?? ('Missing ' . $this->titleColumnKey);
            }

            unset($columnsData[$this->titleColumnKey]);

            $itemIsTrashed = method_exists($item, 'trashed') && $item->trashed();
            $itemCanDelete = $this->getIndexOption('delete') && ($item->canDelete ?? true);
            $canEdit = $this->getIndexOption('edit');
            $canDuplicate = $this->getIndexOption('duplicate');

            $itemId = $this->getItemIdentifier($item);

            return array_replace([
                'id' => $itemId,
                'name' => $name,
                'publish_start_date' => $item->publish_start_date,
                'publish_end_date' => $item->publish_end_date,
                'edit' => $canEdit ? $this->getModuleRoute($itemId, 'edit') : null,
                'duplicate' => $canDuplicate ? $this->getModuleRoute($itemId, 'duplicate') : null,
                'delete' => $itemCanDelete ? $this->getModuleRoute($itemId, 'destroy') : null,
            ] + ($this->getIndexOption('editInModal') ? [
                'editInModal' => $this->getModuleRoute($itemId, 'edit'),
                'updateUrl' => $this->getModuleRoute($itemId, 'update'),
            ] : []) + ($this->getIndexOption('publish') && ($item->canPublish ?? true) ? [
                'published' => $item->published,
            ] : []) + ($this->getIndexOption('feature') && ($item->canFeature ?? true) ? [
                'featured' => $item->{$this->featureField},
            ] : []) + (($this->getIndexOption('restore') && $itemIsTrashed) ? [
                'deleted' => true,
            ] : []) + (($this->getIndexOption('forceDelete') && $itemIsTrashed) ? [
                'destroyable' => true,
            ] : []) + ($translated ? [
                'languages' => $item->getActiveLanguages(),
            ] : []) + $columnsData, $this->indexItemData($item));
        })->toArray();
    }

    public function getVuetifyDatatableOptions()
    {
        return [
            'page'          => request()->has('page') ? intval(request()->query('page')) : 1,
            'itemsPerPage'  => request()->has('itemsPerPage') ? intval(request()->query('itemsPerPage')) : ($this->perPage ?? 10),
            'sortBy'        => request()->has('sortBy') ? [request()->get('sortBy')] : [],
            'sortDesc'      => request()->has('sortDesc') ? [request()->get('sortDesc')] : [],
            'groupBy'       => [],
            'groupDesc'     => [],
            'multiSort'     => false,
            'mustSort'      => false,
        ];
    }

    /**
     * @param \Unusual\CRM\Base\Models\Model $item
     * @return array
     */
    protected function indexItemData($item)
    {
        return [];
    }

    /**
     * @param \Unusual\CRM\Base\Models\Model $item
     * @param array $column
     * @return array
     */
    protected function getItemColumnData($item, $column)
    {
        if (isset($column['thumb']) && $column['thumb']) {
            if (isset($column['present']) && $column['present']) {
                return [
                    'thumbnail' => $item->presentAdmin()->{$column['presenter']},
                ];
            } else {
                $variant = isset($column['variant']);
                $role = $variant ? $column['variant']['role'] : head(array_keys($item->mediasParams));
                $crop = $variant ? $column['variant']['crop'] : head(array_keys(head($item->mediasParams)));
                $params = $variant && isset($column['variant']['params'])
                ? $column['variant']['params']
                : ['w' => 80, 'h' => 80, 'fit' => 'crop'];

                return [
                    'thumbnail' => $item->cmsImage($role, $crop, $params),
                ];
            }
        }

        if (isset($column['nested']) && $column['nested']) {
            $field = $column['nested'];
            $nestedCount = $item->{$column['nested']}->count();
            $module = Str::singular(last(explode('.', $this->moduleName)));
            $value = '<a href="';
            $value .= moduleRoute("$this->moduleName.$field", $this->routePrefix, 'index', [$module => $this->getItemIdentifier($item)]);
            $value .= '">' . $nestedCount . ' ' . (strtolower(Str::plural($column['title'], $nestedCount))) . '</a>';
        } else {
            // dd($column);
            // $field = $column['field'];
            $field = $column['value'];
            // dd($item->$field);
            $value = $item->$field;
        }

        if (isset($column['relationship'])) {
            $field = $column['relationship'] . ucfirst($column['field']);

            $relation = $item->{$column['relationship']}();

            $value = collect($relation->get())
                ->pluck($column['field'])
                ->join(', ');
        } elseif (isset($column['present']) && $column['present']) {
            $value = $item->presentAdmin()->{$column['field']};
        }

        if (isset($column['relatedBrowser']) && $column['relatedBrowser']) {
            $field = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            $value = $item->getRelated($column['relatedBrowser'])
                ->pluck($column['field'])
                ->join(', ');
        }

        return [
            "$field" => $value,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function getIndexTableColumns($items)
    {
        $tableColumns = [];
        $visibleColumns = $this->request->get('columns') ?? false;
        $indexColumnCopy = $this->indexColumns;

        if (isset(Arr::first($indexColumnCopy)['thumb'])
            && Arr::first($indexColumnCopy)['thumb']
        ) {
            $tableColumns[] = [
                'name' => 'thumbnail',
                'label' => unusualTrans('base::lang.listing.columns.thumbnail'),
                'visible' => $visibleColumns ? in_array('thumbnail', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
            array_shift($indexColumnCopy);
        }

        if ($this->getIndexOption('feature')) {
            $tableColumns[] = [
                'name' => 'featured',
                'label' => unusualTrans('base::lang.listing.columns.featured'),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }
        if ($this->getIndexOption('publish')) {
            $tableColumns[] = [
                'name' => 'published',
                'label' => unusualTrans('base::lang.listing.columns.published'),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }

        $tableColumns[] = [
            'name' => 'name',
            'label' => $indexColumnCopy[$this->titleColumnKey]['title'] ?? unusualTrans('base::lang.listing.columns.name'),
            'visible' => true,
            'optional' => false,
            'sortable' => $this->getIndexOption('reorder') ? false : ($indexColumnCopy[$this->titleColumnKey]['sort'] ?? false),
        ];

        unset($indexColumnCopy[$this->titleColumnKey]);

        foreach ($indexColumnCopy as $column) {
            if (isset($column['relationship'])) {
                $columnName = $column['relationship'] . ucfirst($column['field']);
            } elseif (isset($column['nested'])) {
                $columnName = $column['nested'];
            } elseif (isset($column['relatedBrowser'])) {
                $columnName = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            } else {
                $columnName = $column['value'];
                // $columnName = $column['field'];
            }

            $tableColumns[] = [
                'name' => $columnName,
                'label' => $column['text'],
                // 'label' => $column['title'],
                'visible' => $visibleColumns ? in_array($columnName, $visibleColumns) : ($column['visible'] ?? true),
                'optional' => $column['optional'] ?? true,
                'sortable' => $this->getIndexOption('reorder') ? false : ($column['sort'] ?? false),
                'html' => $column['html'] ?? false,
            ];
        }

        if ($this->getIndexOption('includeScheduledInList') && $this->repository->isFillable('publish_start_date')) {
            $tableColumns[] = [
                'name' => 'publish_start_date',
                'label' => unusualTrans('base::lang.listing.columns.published'),
                'visible' => true,
                'optional' => true,
                'sortable' => true,
            ];
        }

        if ($this->routeHasTrait('translations') && count(getLocales()) > 1) {
            $tableColumns[] = [
                'name' => 'languages',
                'label' => unusualTrans('base::lang.listing.languages'),
                'visible' => $visibleColumns ? in_array('languages', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
        }

        return $tableColumns;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    protected function getIndexTableMainFilters($items, $scopes = [])
    {
        $statusFilters = [];

        $scope = ($this->submodule ? [
            $this->getParentModuleForeignKey() => $this->submoduleParentId,
        ] : []) + $scopes;

        $statusFilters[] = [
            'name' => unusualTrans('base::lang.listing.filter.all-items'),
            'slug' => 'all',
            'number' => $this->repository->getCountByStatusSlug('all', $scope),
        ];

        // if ($this->routeHasTrait('revisions') && $this->getIndexOption('create')) {
        //     $statusFilters[] = [
        //         'name' => unusualTrans('base::lang.listing.filter.mine'),
        //         'slug' => 'mine',
        //         'number' => $this->repository->getCountByStatusSlug('mine', $scope),
        //     ];
        // }

        if ($this->getIndexOption('publish')) {
            $statusFilters[] = [
                'name' => unusualTrans('base::lang.listing.filter.published'),
                'slug' => 'published',
                'number' => $this->repository->getCountByStatusSlug('published', $scope),
            ];
            $statusFilters[] = [
                'name' => unusualTrans('base::lang.listing.filter.draft'),
                'slug' => 'draft',
                'number' => $this->repository->getCountByStatusSlug('draft', $scope),
            ];
        }

        if ($this->getIndexOption('restore')) {
            $statusFilters[] = [
                'name' => unusualTrans('base::lang.listing.filter.trash'),
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash', $scope),
            ];
        }

        return $statusFilters;
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

            $parameters = $this->submodule ? [$this->submoduleParentId] : [];

            if($this->isNested){
                $parameters[Str::camel($this->moduleName)] = $this->submoduleParentId;
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
            // return [
            //     $endpoint . 'Url' => $this->getIndexOption($endpoint) ? moduleRoute(
            //         $this->routeName,
            //         $this->routePrefix,
            //         $endpoint,
            //         $this->submodule ? [$this->submoduleParentId] : []
            //     ) : null,
            // ];
        })->toArray();
    }

    /**
     * @param int|null $parentModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($parentModuleId = null)
    {
        $parentModuleId = $this->getParentModuleIdFromRequest($this->request) ?? $parentModuleId;

        $input = $this->validateFormRequest()->all();
        $optionalParent = $parentModuleId ? [$this->getParentModuleForeignKey() => $parentModuleId] : [];

        // if (isset($input['cmsSaveType']) && $input['cmsSaveType'] === 'cancel') {
        //     return $this->respondWithRedirect(moduleRoute(
        //         $this->moduleName,
        //         $this->routePrefix,
        //         'create'
        //     ));
        // }

        $item = $this->repository->create($input + $optionalParent);

        activity()->performedOn($item)->log('created');

        // $this->fireEvent($input);

        dd(
            $parentModuleId,
            $input
        );
        Session::put($this->moduleName . '_retain', true);

        if ($this->getIndexOption('editInModal')) {
            return $this->respondWithSuccess(unusualTrans('base::lang.publisher.save-success'));
        }

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-close')) {
            return $this->respondWithRedirect($this->getBackLink());
        }

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-new')) {
            return $this->respondWithRedirect(moduleRoute(
                $this->moduleName,
                $this->routePrefix,
                'create'
            ));
        }

        return $this->respondWithRedirect(moduleRoute(
            $this->moduleName,
            $this->routePrefix,
            'edit',
            [Str::singular(last(explode('.', $this->moduleName))) => $this->getItemIdentifier($item)]
        ));
    }

    /**
     * @param Request $request
     * @param int|$id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id, $submoduleId = null)
    {
        if ($this->getIndexOption('editInModal')) {
            return Redirect::to(moduleRoute($this->moduleName, $this->routePrefix, 'index'));
        }

        return $this->redirectToForm($this->getParentModuleIdFromRequest($this->request) ?? $submoduleId ?? $id);
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $this->submodule = count($params) > 1;
        $this->submoduleParentId = $this->submodule
        ? $this->getParentModuleIdFromRequest($this->request) ?? $id
        : head($params);

        $id = last($params);

        if ($this->getIndexOption('editInModal')) {
            return $this->request->ajax()
            ? Response::json($this->modalFormData($id))
            : Redirect::to(moduleRoute($this->moduleName, $this->routePrefix, 'index'));
        }

        $this->setBackLink();

        $view = Collection::make([
            "$this->viewPrefix.form",
            "base::$this->routeName.form",
            'base::layouts.form',
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->form($id));
    }

    public function editNested($id)
    {
        $params = $this->request->route()->parameters();

        $this->submodule = count($params) > 1;
        $this->submoduleParentId = $this->submodule
        ? $this->getParentModuleIdFromRequest($this->request) ?? $id
        : head($params);

        $id = last($params);

        if ($this->getIndexOption('editInModal')) {
            return $this->request->ajax()
            ? Response::json($this->modalFormData($id))
            : Redirect::to(moduleRoute($this->moduleName, $this->routePrefix, 'index'));
        }

        $this->setBackLink();

        $view = Collection::make([
            "$this->viewPrefix.form",
            "base::$this->routeName.form",
            'base::layouts.form',
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->form($id));
    }

    /**
     * @param int $parentModuleId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create($parentModuleId = null)
    {

        if (! $this->getIndexOption('skipCreateModal') && false) {
            return Redirect::to(moduleRoute(
                $this->routeName,
                $this->routePrefix,
                'index',
                ['openCreate' => true]
            ));
        }

        $parentModuleId = $this->getParentModuleIdFromRequest($this->request) ?? $parentModuleId;

        $this->submodule = isset($parentModuleId);
        $this->submoduleParentId = $parentModuleId;

        $view = Collection::make([
            "$this->viewPrefix.form",
            "base::$this->routeName.form",
            'base::layouts.form',
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->form(null));
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $submoduleParentId = $this->getParentModuleIdFromRequest($this->request) ?? $id;
        $this->submodule = isset($submoduleParentId);
        $this->submoduleParentId = $submoduleParentId;

        $id = last($params);

        $item = $this->repository->getById($id);
        $input = $this->request->all();

        if (isset($input['cmsSaveType']) && $input['cmsSaveType'] === 'cancel') {
            return $this->respondWithRedirect(moduleRoute(
                $this->moduleName,
                $this->routePrefix,
                'edit',
                [Str::singular($this->moduleName) => $id]
            ));
        } else {
            $formRequest = $this->validateFormRequest();
            // dd(
            //     $this->submodule,
            //     $this->submoduleParentId,
            //     $params,
            //     $item,
            //     $input,
            //     $formRequest->all()
            // );
            $this->repository->update($id, $formRequest->all());

            activity()->performedOn($item)->log('updated');

            // $this->fireEvent();

            if (isset($input['cmsSaveType'])) {
                if (Str::endsWith($input['cmsSaveType'], '-close')) {
                    return $this->respondWithRedirect($this->getBackLink());
                } elseif (Str::endsWith($input['cmsSaveType'], '-new')) {
                    if ($this->getIndexOption('skipCreateModal')) {
                        return $this->respondWithRedirect(moduleRoute(
                            $this->moduleName,
                            $this->routePrefix,
                            'create'
                        ));
                    }

                    return $this->respondWithRedirect(moduleRoute(
                        $this->moduleName,
                        $this->routePrefix,
                        'index',
                        ['openCreate' => true]
                    ));
                } elseif ($input['cmsSaveType'] === 'restore') {
                    Session::flash('status', unusualTrans('base::lang.publisher.restore-success'));

                    return $this->respondWithRedirect(moduleRoute(
                        $this->moduleName,
                        $this->routePrefix,
                        'edit',
                        [Str::singular($this->moduleName) => $id]
                    ));
                }
            }

            if ($this->routeHasTrait('revisions')) {
                return Response::json([
                    'message' => unusualTrans('base::lang.publisher.save-success'),
                    'variant' => MessageStage::SUCCESS,
                    'revisions' => $item->revisionsArray(),
                ]);
            }

            return $this->respondWithSuccess(unusualTrans('base::lang.publisher.save-success'));
        }
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);

        $item = $this->repository->getById($id);

        return $this->respondWithSuccess(unusualTrans('base::lang.listing.delete.success', ['modelTitle' => $this->modelTitle]));

        if ($this->repository->delete($id)) {
            $this->fireEvent();
            activity()->performedOn($item)->log('deleted');

            return $this->respondWithSuccess(unusualTrans('base::lang.listing.delete.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(unusualTrans('base::lang.listing.delete.error', ['modelTitle' => $this->modelTitle]));
    }

    /** xx
     * @param Request $request
     * @return array
     */
    protected function customIndexData($request)
    {
        return [];
    }

    /**
     * @param int $id
     * @param \Unusual\CRM\Base\Models\Model|null $item
     * @return array
     */
    protected function form($id, $item = null, $nested=null)
    {
        $schema = $this->getFormSchema();

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
            'editable' => !!$itemId,
            'item' => $item,
            'moduleName' => $this->moduleName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->routePrefix,
            'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,

            // 'input_schema'  => ($inputSchema = $this->getFormSchema()), // input fields to be used in unusual datatable component
            // 'inputs'  => ($inputs = $this->getFormInputs()), // input fields to be used in unusual datatable component
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

        return array_replace_recursive($data, $this->formData($this->request));
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

        return array_replace_recursive($data, $this->formData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function formData($request)
    {
        return [];
    }

    protected function getViewPrefix(): ?string
    {
        $module_prefix =  Str::snake( $this->moduleName );

        $route_prefix = Str::snake($this->routeName);

        // dd($module_prefix, $route_prefix);

        return "$module_prefix::$route_prefix";

        $prefix = "admin.$this->moduleName";

        if (view()->exists("$prefix.form")) {
            return $prefix;
        }

        // try {
        //     return TwillCapsules::getCapsuleForModel($this->modelName)->getViewPrefix();
        // } catch (NoCapsuleFoundException $e) {
        //     return null;
        // }
    }
}
