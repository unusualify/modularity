<?php

namespace OoBook\CRM\Base\Http\Controllers;

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

use Illuminate\Support\Str;

use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use OoBook\CRM\Base\Traits\{MakesResponses, ManagesNames, ManagesScopes};


abstract class CoreController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,
        ManagesNames,
        MakesResponses,
        ManagesScopes;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * baseKey
     *
     * @var string snake_case
     */
    protected $baseKey;

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
     * whether route is parent or not
     *
     * @var string
     */
    protected $isParent;

    /**
     * whether route is nested or not
     *
     * @var string
     */
    protected $isNested;

    /**
     * integer if route is nested, or null
     *
     * @var integer
     */
    protected $parentId;

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
     * @var \OoBook\CRM\Base\Repositories\ModuleRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $viewPrefix;

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
     * @var array
     */
    protected $defaultTableOptions = [
        'createOnModal' => true,
        'editOnModal' => true,
        'isRowEditing' => false,
        'actionsType' => 'inline'
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
     * List of permissions keyed by a request field. Can be used to prevent unauthorized field updates.
     *
     * @var array
     */
    protected $fieldsPermissions = [];

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

    /**
     * @var array
     */
    protected $indexOptions;

    /**
     * @var array
     */
    protected $indexTableColumns;

    /**
     * @var array
     */
    protected $defaultFilters;


    protected $tableOptions = [];

    protected $childrenTree = [];


    /**
     * @var array
     */
    // protected $browserColumns;

    /**
     * @var string
     */
    // protected $permalinkBase;

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
     * @var string
     */
    // protected $previewView;

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
        $this->baseKey = getUnusualBaseKey();

        $this->setMiddlewarePermission();

        $this->moduleName = $this->getModuleName();
        $this->routeName = $this->getRouteName();
        $this->config = $this->getModuleConfig();

        $this->isParent = $this->isParentRoute();
        $this->isNested = $this->isNestedRoute();
        $this->parentId = $this->getParentId();

        $this->modelName = $this->getModelName();
        $this->routePrefix = $this->getRoutePrefix();
        $this->namespace = $this->getNamespace();
        $this->repository = $this->getRepository();
        $this->modelTitle = $this->getModelTitle();
        /*
         * Apply any filters that are selected by default
         */
        $this->applyFiltersDefaultOptions();

        // /*
        //  * Default filters for the index view
        //  * By default, the search field will run a like query on the title field
        //  */
        // if (! isset($this->defaultFilters)) {
        //     // $this->defaultFilters = [
        //     //     // 'search' => ($this->routeHasTrait('translations') ? '' : '%') . $this->titleColumnKey,
        //     // ];
        //     $this->defaultFilters = [
        //         'search' => collect( $this->indexTableColumns ?? [] )->filter(function ($item) {
        //             return isset($item['searchable']) ? $item['searchable'] : false;
        //         })->map(function($item){
        //             return $item['key'];
        //         })->implode('|')
        //     ];
        // }
        // dd($this->defaultFilters, $this);

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

    protected function getParentId()
    {
        if( $this->moduleName !== $this->routeName && $this->isNested ){
            $param = $this->getSnakeCase( Str::singular($this->moduleName) );

            return $this->request->route()->parameters()[$param];
        }

        return null;
    }

    /**
     * @param Request $request
     * @return string|int|null
     */
    protected function getParentModuleIdFromRequest(Request $request)
    {


        return null;


        $moduleParts = explode('.', $this->moduleName);

        if (count($moduleParts) > 1) {
            $parentModule = Str::singular($moduleParts[count($moduleParts) - 2]);

            return $request->route()->parameters()[$parentModule];
        }

        return null;
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
        return $this->config->sub_routes->{$this->routeName}->nested ?? false;
    }

    /**
     * @param \OoBook\CRM\Base\Models\Model $item
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
     * @return \OoBook\CRM\Base\Http\Requests\Admin\Request
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
     * getJSONData
     *
     * @return
     */
    protected function getJSONData(){

        $scopes = $this->filterScope($this->isNested ? [
            $this->getParentModuleForeignKey() => $this->parentId,
        ] : []);

        $items = $this->getIndexItems($scopes);

        return $this->getTransformer( $items->toArray() );
    }

    /**
     * getFormRequestClass
     *
     * @return void
     */
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
        try {
            return $this->namespace ?? 'Modules'."\\{$this->moduleName}";
        } catch (\Throwable $th) {
            dd($th);
        }
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

    /**
     * getModuleConfig
     *
     * @return void
     */
    public function getModuleConfig()
    {
        $snakeCase = $this->getSnakeCase($this->moduleName);

        return arrayToObject(
            Config::get( getUnusualBaseKey() . '.internal_modules.' . $snakeCase)
            ?: Config::get( $snakeCase )
        );

        // return Collection::make(
        //     Config::get( $this->getCamelCase( env('BASE_NAME', 'Base') ) . '.internal_modules.' . $kebabCase)
        //     ?: Config::get( $kebabCase )
        // )->recursive();
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
                    Config::get(getUnusualBaseKey() . '.admin_app_path'), // TODO uri segment control
                    '',
                    $this->request->route()->getPrefix()
                ),
                '/'
            );
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
     * @return \OoBook\CRM\Base\Repositories\ModuleRepository
     */
    protected function getRepository()
    {
        try {
            return $this->getRepositoryClass($this->modelName) ? App::make($this->getRepositoryClass($this->modelName)) : null;
            //code...
        } catch (\Throwable $th) {
            dd(
                "repositoryClass not exists for {$this->moduleName}",
            );
            throw $th;
        }
    }

    /**
     * getRepositoryClass
     *
     * @param  mixed $model
     * @return void
     */
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
     * @return \OoBook\CRM\Base\Transformers\
     */
    protected function getTransformer($data = [])
    {
        // dd($this->getTransformerClass());
        if( !($concrete = $this->getTransformerClass()))
            return $data;

        return App::makeWith( $concrete, ['resource' => $data] );
    }

    /**
     * @return \OoBook\CRM\Base\Transformers
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
        return $this->getHeadline($this->modelName);
    }

    /**
     * @return string
     */
    protected function getParentModuleForeignKey()
    {
        return Str::singular( $this->getCamelCase(($this->moduleName)) ) . '_id';

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
        return moduleRoute($this->routeName, $this->routePrefix, $action, [ camelCase($this->routeName) => $id]);
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
     * @param array $input
     * @return void
     */
    protected function fireEvent($input = [])
    {
        fireCmsEvent('cms-module.saved', $input);
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

    protected function getConfigFieldsByRoute($field_name)
    {
        return $this->isParentRoute()
            ? $this->config->parent_route->{$field_name}
            : $this->config->sub_routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
    }
}
