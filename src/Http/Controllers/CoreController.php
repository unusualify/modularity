<?php

namespace Unusualify\Modularity\Http\Controllers;

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
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Traits\{MakesResponses, ManageNames, ManageScopes, ManageTraits};

abstract class CoreController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,
        ManageTraits,
        ManageNames,
        MakesResponses,
        ManageScopes;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Unusualify\Modularity\Entities\Model
     */
    protected $user;

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
     * @var \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected $repository;

    /**
     * Options of the index view.
     *
     * @var array
     */
    protected $defaultIndexOptions = [
        'index' => true,
        'create' => true,
        'edit' => true,
        'destroy' => true,

        'publish' => false,
        'bulkPublish' => false,
        'feature' => false,
        'bulkFeature' => false,
        'restore' => true,
        'bulkRestore' => false,
        'forceDelete' => true,
        'bulkForceDelete' => true,
        'delete' => true,
        'duplicate' => true,
        'bulkDelete' => true,
        'reorder' => true,
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
    protected $indexOptions;

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
    protected $titleColumnKey = 'name';

    protected $setDefaultPermissions = true;


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
        $this->baseKey = unusualBaseKey();

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });

        // $this->setMiddlewareBasePermission();
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

        $this->__beforeConstruct($app, $request);

        /*
         * Apply any filters that are selected by default
         */
        $this->applyFiltersDefaultOptions();

        $this->addWiths();
        $this->addIndexWiths();
        $this->addFormWiths();

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
            $order = false;
            foreach($this->middleware as $i => $array){
                if($array['middleware'] == $middleware){
                    $order = $i;
                    break;
                }
            }
            if($order !== false){
                unset($this->middleware[$order]);
            }
            // unset($this->middleware[$key]);
        }
    }

    protected function permissionPrefix($permission = '') {
        return $this->getKebabCase($this->routeName) . ($permission != '' ? "_{$permission}" : '') ;
    }

    protected function setMiddlewarePermission()
    {

        // dd('setMiddlewarePermission', $this->getSnakeCase($this->routeName), $this->user );
        // Permission::where('name', 'LIKE', "%{$this->getKebabCase($this->routeName)}%")->get(),

        $name = $this->getKebabCase($this->routeName);
        // foreach ( Permission::cases() as $permission) {
        //     // $this->middleware("can:{$name}_{$permission->value}", ['only' => ['index', 'show']]);
        // }

        // dd(Permission::ACCESS->value, $name);
        if($this->isGateable() && $this->setDefaultPermissions){
            $this->middleware("can:{$this->permissionPrefix(Permission::VIEW->value)}", ['only' => ['index', 'show']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::CREATE->value)}", ['only' => ['create', 'store']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::EDIT->value)}", ['only' => ['edit', 'update']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::DELETE->value)}", ['only' => ['delete']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::FORCEDELETE->value)}", ['only' => ['forceDelete']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::RESTORE->value)}", ['only' => ['restore']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::DUPLICATE->value)}", ['only' => ['duplicate']]);
            $this->middleware("can:{$this->permissionPrefix(Permission::REORDER->value)}", ['only' => ['reorder']]);
        }

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
        return $this->isParent ?? $this->getConfigFieldsByRoute('parent') ?: $this->moduleName == $this->routeName;
    }

    /**
     * @return bool
     */
    protected function isNestedRoute()
    {
        return $this->config->sub_routes->{$this->routeName}->nested ?? false;
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function getIndexOption($option)
    {
        return once(function () use ($option) {
            $customOptionNamesMapping = [
                'store' => 'create',
                'update' => 'edit',
                // 'store' => Permission::CREATE->value,
                // 'update' => Permission::EDIT->value,
                // 'show' => Permission::EDIT->value,
                // 'delete' => Permission::DELETE->value,
            ];
            // dd($option, $customOptionNamesMapping);

            $option = array_key_exists($option, $customOptionNamesMapping) ? $customOptionNamesMapping[$option] : $option;

            $authorizableOptions = [
                'index' => $this->permissionPrefix(Permission::VIEW->value),
                'create' => $this->permissionPrefix(Permission::CREATE->value),
                'edit' => $this->permissionPrefix(Permission::EDIT->value),
                'delete' => $this->permissionPrefix(Permission::DELETE->value),
                'destroy' => $this->permissionPrefix(Permission::DELETE->value),

                // 'restore' => 'restore',
                'restore' => $this->permissionPrefix(Permission::RESTORE->value),
                'forceDelete' => $this->permissionPrefix(Permission::FORCEDELETE->value),
                'duplicate' => $this->permissionPrefix(Permission::DUPLICATE->value),


                /**
                 * TODO #additionalRoutePermission
                 *
                 */
                // 'duplicate' => $this->permissionPrefix(Permission::DUPLICATE->value),

                // 'index' => 'access',
                // 'create' => 'edit',
                // 'edit' => 'edit',
                // 'publish' => 'publish',
                // 'feature' => 'feature',
                // 'reorder' => 'reorder',
                // 'delete' => 'delete',
                // 'duplicate' => 'duplicate',
                // 'restore' => 'delete',
                // 'forceDelete' => 'delete',
                // 'bulkForceDelete' => 'delete',
                // 'bulkPublish' => 'publish',
                // 'bulkRestore' => 'delete',
                // 'bulkFeature' => 'feature',
                // 'bulkDelete' => 'delete',
                // 'bulkEdit' => 'edit',
                // 'editInModal' => 'edit',
                // 'skipCreateModal' => 'edit',
            ];

            /**
             * TODO #guard
             *
             */
            // dd(
            //     $authorizableOptions,
            //     $option,
            //     Auth::guard('unusual_users')->user(),
            //     debug_backtrace(),
            // );
            $authorized = ( $this->isGateable() && array_key_exists($option, $authorizableOptions))
                ? Auth::guard('unusual_users')->user()->can($authorizableOptions[$option])
                : true;
            // $authorized = true;

            return ($this->indexOptions[$option] ?? $this->defaultIndexOptions[$option] ?? false) && $authorized;
        });
    }

    /**
     * @return \Unusualify\Modularity\Http\Requests\Admin\Request
     */
    protected function validateFormRequest()
    {
        $unauthorizedFields = Collection::make($this->fieldsPermissions)->filter(function ($permission, $field) {
            return Auth::guard('unusual_users')->user()->cannot($permission);
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
    protected function getIndexItems($with=[], $scopes = [], $forcePagination = false, )
    {
        return $this->transformIndexItems($this->repository->get(
            $this->indexWith + $with,
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
     *
     *
     * @return
     */
    protected function getJSONData($with = []){

        $scopes = $this->filterScope($this->isNested ? [
            $this->getParentModuleForeignKey() => $this->parentId,
        ] : []);

        $paginator = $this->getIndexItems($with, $scopes);

        return $this->getTransformer( $this->getFormattedIndexItems($paginator) );
        // return $this->getTransformer( $paginator->toArray() );
    }

    /**
     *
     *
     * @param  array $paginator
     * @return array
     */
    public function getFormattedIndexItems($paginator) // getIndexTableItems
    {
        return $paginator;
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

        return array2Object(
            Config::get( unusualBaseKey() . '.system_modules.' . $snakeCase)
            ?: Config::get( $snakeCase )
        );

        // return Collection::make(
        //     Config::get( $this->getCamelCase( env('UNUSUAL_BASE_NAME', 'Unusual') ) . '.system_modules.' . $kebabCase)
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

        $routePrefixes = [];

        if( isset($this->config->base_prefix) && $this->config->base_prefix)
            $routePrefixes[] = snakeCase(studlyName(unusualConfig('base_prefix', 'system-settings')));

        if( !$this->isParent )
            $routePrefixes[] = Str::snake($this->moduleName);

        return implode('.', $routePrefixes);

        if ($this->request->route() != null) {
            $routePrefix = ltrim(
                str_replace(
                    Config::get(unusualBaseKey() . '.admin_app_path'), // TODO uri segment control
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
     * @return \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected function getRepository()
    {
        try {
            return $this->getRepositoryClass($this->modelName) ? App::make($this->getRepositoryClass($this->modelName)) : null;
            //code...
        } catch (\Throwable $th) {
            dd(
                "repositoryClass not exists for {$this->routeName} in {$this->moduleName}",
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
     * @return \Unusualify\Modularity\Transformers\
     */
    protected function getTransformer($data = [])
    {

        if( !($concrete = $this->getTransformerClass()))
            return $data;

        return App::makeWith( $concrete, ['resource' => $data] );
    }

    /**
     * @return \Unusualify\Modularity\Transformers
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
    protected function getModuleRoute($id, $action, $singleton = false)
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
        $parameters = $singleton ? [] : [ camelCase($this->routeName) => $id];

        return moduleRoute($this->routeName, $this->routePrefix, $action, $parameters, singleton: $singleton);
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

    protected function getConfigFieldsByRoute($field_name, $default = null)
    {
        try {
            return $this->config->routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
        } catch (\Throwable $th) {
            return $default;
            dd(
                // $th,
                $this,
                debug_backtrace()
            );
        }
        return $this->config->routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
        // return $this->isParentRoute()
        //     ? $this->config->parent_route->{$field_name}
        //     : $this->config->sub_routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHas($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    public function isGateable()
    {
        return !env('PERMISSION_GATES_DEACTIVATE', false);
    }

    public function isRelationField($key) {
        $model_relations = [];

        if(@method_exists($this->repository->getModel(), 'definedRelations')){
            $model_relations = $this->repository->definedRelations();
        }

        if(preg_match('/(.*)(_id)/', $key, $matches)){
            $key = pluralize($matches[1]);
        }


        return in_array($key, $model_relations);
        // if(in_array($key, $model_relations)){

        // }


        // return false;
        // return in_array($key, $model_relations);
    }

    protected function addIndexWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function($method){
            return preg_match('/addIndexWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexWith += $this->{$method}();
        }
    }

    protected function addFormWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function($method){
            return preg_match('/addFormWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->formWith += $this->{$method}();
        }
    }

    protected function addWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function($method){
            return preg_match('/addWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexWith += $this->{$method}();
            $this->formWith += $this->{$method}();
        }
    }

    /**
     * @return void
     */
    public function __afterConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }

    /**
     * @return void
     */
    public function __beforeConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }

    /**
     * @param array $input
     * @return void
     */
    protected function fireEvent__($input = [])
    {
        fireCmsEvent('cms-module.saved', $input);
    }

    /**
     * @return Collection|Block[]
     */
    public function getRepeaterList__()
    {
        return TwillBlocks::getBlockCollection()->getRepeaters()->mapWithKeys(function (Block $repeater) {
            return [$repeater->name => $repeater->toList()];
        });
    }
}
