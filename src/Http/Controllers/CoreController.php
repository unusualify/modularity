<?php

namespace Unusual\CRM\Base\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

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
use Unusual\CRM\Base\Transformers\RoleResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Illuminate\Routing\Controller;
use Unusual\CRM\Base\Facades\Module as FacadesModule;
use Unusual\CRM\Base\Services\MessageStage;
use Unusual\CRM\Base\Support\Finder;
use Nwidart\Modules\Facades\Module;
use stdClass;

abstract class CoreController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var
     */
    protected $baseModule;

    /**
     * @var object
     */
    protected $config;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $modelTitle;

    /**
     * @var \Unusual\CRM\Base\Repositories\ModuleRepository
     */
    protected $repository;

    /**
     * Options of the index view.
     *
     * @var array
     */
    protected $defaultIndexOptions = [
        'create' => true,
        'edit' => true,
        'destroy' => true,

        'publish' => false,
        'bulkPublish' => false,
        'feature' => false,
        'bulkFeature' => false,
        'restore' => false,
        'bulkRestore' => false,
        'forceDelete' => true,
        'bulkForceDelete' => true,
        'delete' => true,
        'duplicate' => false,
        'bulkDelete' => true,
        'reorder' => false,
        'permalink' => true,
        'bulkEdit' => true,

        'editInModal' => false,
        'skipCreateModal' => false,
        // @todo(3.x): Default to true.
        'includeScheduledInList' => false,
    ];

    /**
     * Relations to eager load for the index view.
     *
     * @var array
     */
    protected $indexWith = [];

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
     * Additional filters for the index view.
     *
     * To automatically have your filter added to the index view use the following convention:
     * suffix the key containing the list of items to show in the filter by 'List' and
     * name it the same as the filter you defined in this array.
     *
     * Example: 'fCategory' => 'category_id' here and 'fCategoryList' in indexData()
     * By default, this will run a where query on the category_id column with the value
     * of fCategory if found in current request parameters. You can intercept this behavior
     * from your repository in the filter() function.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Additional links to display in the listing filter.
     *
     * @var array
     */
    protected $filterLinks = [];

    /**
     * Filters that are selected by default in the index view.
     *
     * Example: 'filter_key' => 'default_filter_value'
     *
     * @var array
     */
    protected $filtersDefaultOptions = [];

    /**
     * Default orders for the index view.
     *
     * @var array
     */
    protected $defaultOrders = [
        'created_at' => 'desc',
    ];

    /**
     * @var int
     */
    protected $perPage = 20;

    /**
     * Name of the index column to use as name column.
     *
     * @var string
     */
    protected $titleColumnKey = 'title';

    /**
     * Name of the index column to use as identifier column.
     *
     * @var string
     */
    protected $identifierColumnKey = 'id';

    /**
     * Attribute to use as title in forms.
     *
     * @var string
     */
    protected $titleFormKey;

    protected $isNested;

    /**
     * Feature field name if the controller is using the feature route (defaults to "featured").
     *
     * @var string
     */
    // protected $featureField = 'featured';

    /**
     * Indicates if this module is edited through a parent module.
     *
     * @var bool
     */
    // protected $submodule = false;

    /**
     * @var int|null
     */
    // protected $submoduleParentId = null;

    /**
     * Can be used in child classes to disable the content editor (full screen block editor).
     *
     * @var bool
     */
    // protected $disableEditor = false;

    /**
     * @var array
     */
    protected $indexOptions;

    /**
     * @var array
     */
    protected $indexColumns;

    /**
     * @var array
     */
    // protected $browserColumns;

    /**
     * @var string
     */
    // protected $permalinkBase;

    /**
     * @var array
     */
    protected $defaultFilters;

    /**
     * @var string
     */
    protected $viewPrefix;

    /**
     * @var string
     */
    // protected $previewView;

    /**
     * List of permissions keyed by a request field. Can be used to prevent unauthorized field updates.
     *
     * @var array
     */
    protected $fieldsPermissions = [];

    protected $childrenTree = [];

    public function __construct(
        Application $app,
        Request $request
    )
    {
        // if (Config::get('twill.bind_exception_handler', true)) {
        //     App::singleton(ExceptionHandler::class, TwillHandler::class);
        // }

        $this->app = $app;
        $this->request = $request;



        $this->setMiddlewarePermission();

        $this->baseModule = Module::find('Base');
        $this->moduleName = $this->getModuleName();
        $this->routeName = $this->getRouteName();
        $this->config = $this->getModuleConfig();

        // dd(
        //     $this->moduleName,
        //     $this->app['ue_modules']->find($this->moduleName),
        //     $this->config->parent_route,
        //     Module::find($this->moduleName)
        //     // FacadesModule::find('Base')
        // );
        $this->isParent = $this->isParentRoute();

        $this->nested = $this->isNestedRoute();

        $this->modelName = $this->getModelName();
        $this->routePrefix = $this->getRoutePrefix();
        $this->namespace = $this->getNamespace();
        $this->repository = $this->getRepository();
        $this->viewPrefix = $this->getViewPrefix();
        $this->modelTitle = $this->getModelTitle();

        /*
         * Apply any filters that are selected by default
         */
        $this->applyFiltersDefaultOptions();

        /*
         * Available columns of the index view
         */

        $this->indexColumns = $this->getIndexColumns();

        if (! isset($this->indexColumns) ) {
            // $this->indexColumns = [
            //     $this->titleColumnKey => [
            //         'title' => ucfirst($this->titleColumnKey),
            //         'field' => $this->titleColumnKey,
            //         'sort' => true,
            //     ],
            // ];
        }

        /*
         * Default filters for the index view
         * By default, the search field will run a like query on the title field
         */
        if (! isset($this->defaultFilters)) {
            // $this->defaultFilters = [
            //     // 'search' => ($this->routeHasTrait('translations') ? '' : '%') . $this->titleColumnKey,
            // ];
            $this->defaultFilters = [
                'search' => collect( $this->indexColumns ?? [] )->filter(function ($item) {
                    return isset($item['searchable']) ? $item['searchable'] : false;
                })->map(function($item){
                    return $item['value'];
                })->implode('|')
            ];
        }



    }

    /**
     * Attempts to unset the given middleware.
     *
     * @param string $middleware
     * @return void
     */
    public function removeMiddleware($middleware)
    {
        if (($key = array_search($middleware, Arr::pluck($this->middleware, 'middleware'))) !== false) {
            unset($this->middleware[$key]);
        }
    }

    protected function setMiddlewarePermission()
    {
        // $this->middleware('can:list', ['only' => ['index', 'show']]);
        // $this->middleware('can:edit', ['only' => ['store', 'edit', 'update']]);
        // $this->middleware('can:duplicate', ['only' => ['duplicate']]);
        // $this->middleware('can:publish', ['only' => ['publish', 'feature', 'bulkPublish', 'bulkFeature']]);
        // $this->middleware('can:reorder', ['only' => ['reorder']]);
        // $this->middleware('can:delete', ['only' => ['destroy', 'bulkDelete', 'restore', 'bulkRestore', 'forceDelete', 'bulkForceDelete', 'restoreRevision']]);
    }

    /**
     * @param Request $request
     * @return string|int|null
     */
    protected function getParentModuleIdFromRequest(Request $request)
    {
        // dd(
        //     $request,
        //     $this->moduleName,
        //     $request->route()->parameters()
        // );
        return null;

        $moduleParts = explode('.', $this->moduleName);

        if (count($moduleParts) > 1) {
            $parentModule = Str::singular($moduleParts[count($moduleParts) - 2]);

            return $request->route()->parameters()[$parentModule];
        }

        return null;
    }

    /**
     * @param \Unusual\CRM\Base\Models\Model $item
     * @return int|string
     */
    protected function getItemIdentifier($item)
    {
        return $item->{$this->identifierColumnKey};
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function getIndexOption($option)
    {
        return once(function () use ($option) {
            $customOptionNamesMapping = [
                'index' => 'index',
                'store' => 'create',
                'update' => 'edit',
                'show' => 'edit',
                'delete' => 'destroy',
            ];

            $option = array_key_exists($option, $customOptionNamesMapping) ? $customOptionNamesMapping[$option] : $option;

            $authorizableOptions = [
                'create' => 'edit',
                'edit' => 'edit',
                'publish' => 'publish',
                'feature' => 'feature',
                'reorder' => 'reorder',
                'delete' => 'delete',
                'duplicate' => 'duplicate',
                'restore' => 'delete',
                'forceDelete' => 'delete',
                'bulkForceDelete' => 'delete',
                'bulkPublish' => 'publish',
                'bulkRestore' => 'delete',
                'bulkFeature' => 'feature',
                'bulkDelete' => 'delete',
                'bulkEdit' => 'edit',
                'editInModal' => 'edit',
                'skipCreateModal' => 'edit',
            ];
            /**
             * TODO #guard
             *
             */
            // $authorized = array_key_exists($option, $authorizableOptions) ? Auth::guard('twill_users')->user()->can($authorizableOptions[$option]) : true;
            $authorized = true;

            return ($this->indexOptions[$option] ?? $this->defaultIndexOptions[$option] ?? false) && $authorized;
        });
    }

    /**
     * @param array $prepend
     * @return array
     */
    protected function filterScope($prepend = [])
    {
        $scope = [];

        $requestFilters = $this->getRequestFilters();

        $this->filters = array_merge($this->defaultFilters, $this->filters);

        if (array_key_exists('status', $requestFilters)) {
            switch ($requestFilters['status']) {
                case 'published':
                    $scope['published'] = true;
                    break;
                case 'draft':
                    $scope['draft'] = true;
                    break;
                case 'trash':
                    $scope['onlyTrashed'] = true;
                    break;
                case 'mine':
                    $scope['mine'] = true;
                    break;
            }

            unset($requestFilters['status']);
        }
        foreach ($this->filters as $key => $field) {
            if (array_key_exists($key, $requestFilters)) {
                $value = $requestFilters[$key];
                if ($value == 0 || ! empty($value)) {
                    // add some syntaxic sugar to scope the same filter on multiple columns

                    $fieldSplitted = explode('|', $field);

                    if( $key == 'search' && $field != 'search'){
                        $fieldSplitted = explode('|', $field);

                        $scope['searches'] = $fieldSplitted;

                        $scope[$key] = $requestFilters[$key]; // search
                    }

                    if (count($fieldSplitted) > 1) {
                        $requestValue = $requestFilters[$key];

                        // $scope[$scopeKey] =
                        Collection::make($fieldSplitted)->each(function ($scopeKey) use (&$scope, $requestValue) {
                            $scope[$scopeKey] = $requestValue;
                        });
                    } else {
                        $scope[$field] = $requestFilters[$key];
                    }
                }
            }
        }

        return $prepend + $scope;
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        if ($this->request->has('search')) {
            return ['search' => $this->request->get('search')];
        }

        return json_decode($this->request->get('filter'), true) ?? [];
    }

    /**
     * @return void
     */
    protected function applyFiltersDefaultOptions()
    {
        if (! count($this->filtersDefaultOptions) || $this->request->has('search')) {
            return;
        }

        $filters = $this->getRequestFilters();

        foreach ($this->filtersDefaultOptions as $filterName => $defaultOption) {
            if (! isset($filters[$filterName])) {
                $filters[$filterName] = $defaultOption;
            }
        }

        $this->request->merge(['filter' => json_encode($filters)]);
    }

    /**
     * @return array
     */
    protected function orderScope()
    {
        $orders = [];

        if ($this->request->has('sortBy') && $this->request->has('sortDesc')) {
            if (($key = $this->request->get('sortBy')) == 'name') {
                $sortBy = $this->titleColumnKey;
            } elseif (! empty($key)) {
                $sortBy = $key;
            }
            // dd(
            //     $sortBy,
            //     $this->request->get('sortDesc'),
            // );
            // dd($sortBy, $this->request->all());
            if (isset($sortBy)) {
                $orders[$this->indexColumns[$sortBy]['sortBy'] ?? $sortBy] = $this->request->get('sortDesc') == "true" ? 'desc' : 'asc';
            }
        }

        // don't apply default orders if reorder is enabled
        $reorder = $this->getIndexOption('reorder');
        $defaultOrders = ($reorder ? [] : ($this->defaultOrders ?? []));

        return $orders + $defaultOrders;
    }

    /**
     * @return \Unusual\CRM\Base\Http\Requests\Admin\Request
     */
    protected function validateFormRequest()
    {
        $unauthorizedFields = Collection::make($this->fieldsPermissions)->filter(function ($permission, $field) {
            return Auth::guard('twill_users')->user()->cannot($permission);
        })->keys();

        $unauthorizedFields->each(function ($field) {
            $this->request->offsetUnset($field);
        });

        return $this->getFormRequestClass();
    }

    protected function getJSONData(){

        $scopes = $this->filterScope($this->submodule ? [
            $this->getParentModuleForeignKey() => $this->submoduleParentId,
        ] : []);

        $items = $this->getIndexItems($scopes);

        return $this->getTransformer( $items->toArray() );
    }

    public function getFormRequestClass()
    {
        $formRequest = "$this->namespace\Http\Requests\\" . $this->modelName . 'Request';

        if (@class_exists($formRequest)) {
            return App::make( $formRequest );
        }
        return $this->request;
        // return TwillCapsules::getCapsuleForModel($this->modelName)->getFormRequestClass();
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        return $this->namespace ?? Config::get("{$this->baseModule->getLowerName()}.namespace")."\\{$this->moduleName}";
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        return $this->moduleName ?? getCurrentModuleName();
    }

    /**
     * @return string
     */
    protected function getRouteName()
    {
        return $this->routeName ?? $this->moduleName;
    }

    public function getModuleConfig()
    {
        $camel = camelName($this->moduleName);

        return arrayToObject(
            Config::get( camelName( env('BASE_NAME', 'Base') ) . '.internal_modules.' . $camel)
            ?: Config::get( $camel )
        );

        return Collection::make(
            Config::get( camelName( env('BASE_NAME', 'Base') ) . '.internal_modules.' . $camel)
            ?: Config::get( $camel )
        )->recursive();
    }

    /**
     * @return string
     */
    protected function getRoutePrefix()
    {
        if( $this->routePrefix !== null )
            return $this->routePrefix;

        if( $this->routeName == $this->moduleName )
            return '';
        else
            return Str::camel($this->moduleName);


        if ($this->request->route() != null) {
            $routePrefix = ltrim(
                str_replace(
                    Config::get('base.admin_app_path'), // TODO uri segment control
                    '',
                    $this->request->route()->getPrefix()
                ),
                '/'
            );
            // dd(
            //     // $this->request,
            //     // $this->request->route(),
            //     // get_class_methods($this->request->route()),
            //     $this->request->route()->getPrefix(),
            //     $routePrefix
            // );

            return str_replace('/', '.', $routePrefix);
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getModelName()
    {
        return $this->modelName ?? ucfirst(Str::singular($this->routeName));
    }

    /**
     * @return \Unusual\CRM\Base\Repositories\ModuleRepository
     */
    protected function getRepository()
    {
        return App::make($this->getRepositoryClass($this->modelName));
    }

    public function getRepositoryClass($model)
    {
        if (@class_exists($class = "$this->namespace\Repositories\\" . $model . 'Repository')) {
            return $class;
        }
        return null;
        // TODO if repository is not exists
        return TwillCapsules::getCapsuleForModel($model)->getRepositoryClass();
    }

    /**
     * @return \Unusual\CRM\Base\Transformers\
     */
    protected function getTransformer($data = [])
    {
        // dd($this->getTransformerClass());
        if( !($concrete = $this->getTransformerClass()))
            return $data;

        return App::makeWith( $concrete, ['resource' => $data] );
    }

    /**
     * @return \Unusual\CRM\Base\Transformers
     */
    protected function getTransformerClass()
    {
        if (@class_exists($class = "$this->namespace\Transformers\\" . $this->modelName . 'Resource')) {
            return $class;
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getModelTitle()
    {
        return camelCaseToWords($this->modelName);
    }

    public function getIndexColumns()
    {
        if(!!$this->indexColumns)
            return $this->indexColumns;
        else if(!$this->config)
            return [];
        else{
            return $this->indexColumns = Collection::make(
                $this->isParentRoute()
                ? $this->config->parent_route->headers
                : $this->config->sub_routes->{camelName($this->routeName)}->headers
            )->map(function($item){ return (array) $item;})
            ->toArray();
        }

    }

    public function getFormInputs()
    {

        return Collection::make(
            $this->isParentRoute()
            ? $this->config->parent_route->inputs
            : $this->config->sub_routes->{camelName($this->routeName)}->inputs
        )->map(function($item){ return (array) $item;})
        ->toArray();
    }

    public function getFormSchema()
    {
        return Collection::make(
            $this->isParentRoute()
            ? $this->config->parent_route->input_schema
            : $this->config->sub_routes->{camelName($this->routeName)}->inputs
        )->mapWithKeys(function($item, $key){
            return $this->getInputSchema($item);
        })->toArray();
    }

    public function getInputSchema($input)
    {
        if($object = $this->generateCustomInput($input)){
            return $object;
        }
        return isset($input->name)
            ? [ $input->name => collect($input)->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];})]
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


    public function getTableOptions()
    {
        return Collection::make(
            $this->isParentRoute()
            ? $this->config->parent_route->table_option
            : $this->config->sub_routes->{camelName($this->routeName)}->table_options
        )->toArray();
    }

    /**
     * @return string
     */
    protected function getParentModuleForeignKey()
    {
        return Str::singular( camelName($this->moduleName) ) . '_id';

        $moduleParts = explode('.', $this->moduleName);

        return Str::singular($moduleParts[count($moduleParts) - 2]) . '_id';
    }

    /** 1
     * @param int $id
     * @param string $action
     * @return string
     */
    protected function getModuleRoute($id, $action)
    {
        // dd(
        //     $id,
        //     $action,
        //     // strtolower($this->moduleName),
        //     $this->routeName,
        //     $this->routePrefix,
        //     moduleRoute($this->routeName, $this->routePrefix, $action, [$id])
        //     // moduleRoute(strtolower($this->moduleName), $this->routePrefix, $action, [$id])
        //     // moduleRoute($this->moduleName, $this->routePrefix, $action, [$id])
        // );
        return moduleRoute($this->routeName, $this->routePrefix, $action, [ camelName($this->routeName) => $id]);
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHasTrait($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    /**
     * @return bool
     */
    protected function titleIsTranslatable()
    {
        return $this->repository->isTranslatable(
            $this->titleColumnKey
        );
    }


    /**
     * @return bool
     */
    protected function isParentRoute()
    {
        return $this->isParent ?? $this->moduleName == $this->routeName;
    }

    /**
     * @return bool
     */
    protected function isNestedRoute()
    {
        return $this->config->sub_routes->{lowerName($this->routeName)}->nested
            ?? false;
    }

    /**
     * @param string|null $back_link
     * @param array $params
     * @return void
     */
    protected function setBackLink($back_link = null, $params = [])
    {
        if (! isset($back_link)) {
            if (($back_link = Session::get($this->getBackLinkSessionKey())) == null) {
                $back_link = $this->request->headers->get('referer') ?? moduleRoute(
                    $this->moduleName,
                    $this->routePrefix,
                    'index',
                    $params
                );
            }
        }

        if (! Session::get($this->moduleName . '_retain')) {
            Session::put($this->getBackLinkSessionKey(), $back_link);
        } else {
            Session::put($this->moduleName . '_retain', false);
        }
    }

    /**
     * @param string|null $fallback
     * @param array $params
     * @return string
     */
    protected function getBackLink($fallback = null, $params = [])
    {
        $back_link = Session::get($this->getBackLinkSessionKey(), $fallback);

        return $back_link ?? moduleRoute($this->moduleName, $this->routePrefix, 'index', $params);
    }

    /**
     * @return string
     */
    protected function getBackLinkSessionKey()
    {
        return $this->moduleName . ($this->nested ? $this->submoduleParentId ?? '' : '') . '_back_link';
        return $this->moduleName . ($this->submodule ? $this->submoduleParentId ?? '' : '') . '_back_link';
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToForm($id, $params = [])
    {
        Session::put($this->moduleName . '_retain', true);
        dd(
            $this->moduleName,
            $this->routePrefix,
            'edit',
            array_filter($params) + [Str::singular($this->moduleName) => $id],
        );
        return Redirect::to(moduleRoute(
            $this->moduleName,
            $this->routePrefix,
            'edit',
            array_filter($params) + [Str::singular($this->moduleName) => $id]
        ));
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithSuccess($message)
    {
        return $this->respondWithJson($message, MessageStage::SUCCESS);
    }

    /**
     * @param string $redirectUrl
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithRedirect($redirectUrl)
    {
        return Response::json([
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message)
    {
        return $this->respondWithJson($message, MessageStage::ERROR);
    }

    /**
     * @param string $message
     * @param mixed $variant
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithJson($message, $variant)
    {
        return Response::json([
            'message' => $message,
            'variant' => $variant,
        ]);
    }

    /**
     * @param array $input
     * @return void
     */
    protected function fireEvent($input = [])
    {
        fireCmsEvent('cms-module.saved', $input);
    }

    protected function getSchemaWiths($schema)
    {
        return collect($schema)->filter(function($item){
            return $this->hasWithModel($item['type']);
        })->map(function($item, $i){
            return $item['name'];
        })
        ->values()
        ->toArray();
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

    /**
     * @return Collection|Block[]
     */
    public function getRepeaterList()
    {
        return TwillBlocks::getBlockCollection()->getRepeaters()->mapWithKeys(function (Block $repeater) {
            return [$repeater->name => $repeater->toList()];
        });
    }

    public function paginate()
    {
        // return $this->repository
        $request = $this->request;

        $search = $request->query('search') ?? "";

        $perPage = $request->query('per-page') ?? $this->perPage;

        $model = $this->repository->getModel();


        // dd( app($this->model), app($this->model)->getAttributes() );

        if($search != ""){

            $search_columns = collect( $this->indexColumns ?? [] )->filter(function ($value) {
                return isset($value['searchable']) ? $value['searchable'] : false;
            })->map(function($value){
                return $value['value'];
            })->toArray();
            // dd($search_columns);

            $items = $model::where(function($query) use($search, $search_columns){
                foreach ($search_columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
                // dd($query->toSql());
            })->paginate($perPage);
            // dd($items);
        }else{
            $items =  $model::paginate($perPage);
        }

        return $items;

    }
}
