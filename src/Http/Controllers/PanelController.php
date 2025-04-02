<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\MakesResponses;
use Unusualify\Modularity\Http\Controllers\Traits\ManageScopes;
use Unusualify\Modularity\Http\Controllers\Traits\ManageAuthorization;

abstract class PanelController extends CoreController
{
    use MakesResponses, ManageScopes, ManageAuthorization;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Unusualify\Modularity\Entities\Model
     */
    protected $user;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * whether route is parent or not
     *
     * @var string
     */
    protected $isParent;

    /**
     * integer if route is nested, or null
     *
     * @var int
     */
    protected $isNested;

    /**
     * integer if route is nested, nestedParentId
     *
     * @var int
     */
    protected $nestedParentId;

    /**
     * snake_case if route is nested, nestedParentRouteName
     *
     * @var string
     */
    protected $nestedParentName;

    /**
     * Model record if route is nested
     *
     * @var \Unusualify\Modularity\Entities\Model
     */
    protected $nestedParentModel;

    /**
     * @var object
     */
    protected $config;

    /**
     * @var string
     */
    protected $modelTitle;

    /**
     * Options of the index view.
     *
     * @var array
     */
    protected $defaultIndexOptions = [
        'activity' => true,
        'show' => true,
        'index' => true,
        'create' => true,
        'edit' => true,
        'destroy' => true,
        'publish' => false,
        'bulkPublish' => false,
        'feature' => false,
        'bulkFeature' => false,
        'restore' => true,
        'bulkRestore' => true,
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
     * formSchema
     *
     * @var array
     */
    protected $formSchema;

    /**
     * List of permissions keyed by a request field. Can be used to prevent unauthorized field updates.
     *
     * @var array
     */
    protected $fieldsPermissions = [];

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * Name of the index column to use as name column.
     *
     * @var string
     */
    protected $titleColumnKey = 'name';

    /**
     * Use default authorization permissions
     *
     * @var bool
     */
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
    ) {
        // if (modularityConfig('bind_exception_handler', true)) {
        //     App::singleton(ExceptionHandler::class, ModularityHandler::class);
        // }

        parent::__construct($request);

        $this->app = $app;

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });

        // $this->setMiddlewareBasePermission();
        $this->setMiddlewarePermission();

        $this->config = $this->getModuleConfig();

        $this->titleColumnKey = $this->getConfigFieldsByRoute('title_column_key', 'name');

        $this->isParent = $this->isParentRoute();

        $this->checkNestedAttributes();

        $this->routePrefix = $this->getRoutePrefix();

        $this->modelTitle = $this->getModelTitle();

        $this->__beforeConstruct($app, $request);

        /*
         * Apply any filters that are selected by default
         */
        $this->applyFiltersDefaultOptions();

        $this->fixedFilters = array_merge((array) $this->getConfigFieldsByRoute('filters.fixed', []), $this->fixedFilters ?? []);

        // $this->addWiths();

        // $this->addIndexWiths();

        // $this->addFormWiths();

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
            foreach ($this->middleware as $i => $array) {
                if ($array['middleware'] == $middleware) {
                    $order = $i;

                    break;
                }
            }
            if ($order !== false) {
                unset($this->middleware[$order]);
            }
            // unset($this->middleware[$key]);
        }
    }

    protected function permissionPrefix($permission = '')
    {
        return $this->getKebabCase($this->routeName) . ($permission != '' ? "_{$permission}" : '');
    }

    protected function setMiddlewarePermission()
    {

        // Permission::where('name', 'LIKE', "%{$this->getKebabCase($this->routeName)}%")->get(),

        $name = $this->getKebabCase($this->routeName);
        // foreach ( Permission::cases() as $permission) {
        //     // $this->middleware("can:{$name}_{$permission->value}", ['only' => ['index', 'show']]);
        // }

        // dd(Permission::ACCESS->value, $name);
        if ($this->isGateable() && $this->setDefaultPermissions) {
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

    protected function checkNestedAttributes()
    {
        [$this->isNested, $this->nestedParentId, $this->nestedParentName, $this->nestedParentModel] = $this->getNestedAttributes();
    }

    protected function getNestedAttributes()
    {
        $params = $this->request->route() ? $this->request->route()->parameters() : [];

        $parentParams = array_diff_key($params, array_flip([snakeCase($this->routeName)]));

        if (count($parentParams)) {
            $nestedParentName = array_key_last($parentParams); // snakecase;
            $nestedParentId = last($parentParams);
            $nestedParentModel = $this->module->getRouteClass($nestedParentName, 'model');
            $nestedParentModel = $nestedParentModel::find($nestedParentId);

            return [true, $nestedParentId, $nestedParentName, $nestedParentModel];
        }

        return [false, null, null, null];
        // if( $this->moduleName !== $this->routeName && $this->isNested ){

        //     $param = $this->getSnakeCase( Str::singular($this->moduleName) );

        //     return $this->request->route()->parameters()[$param];
        // }

        // return null;
    }

    /**
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

            $option = array_key_exists($option, $customOptionNamesMapping)
                ? $customOptionNamesMapping[$option]
                : $option;

            $authorizableOptions = [
                'index' => $this->permissionPrefix(Permission::VIEW->value),
                'create' => $this->permissionPrefix(Permission::CREATE->value),
                'edit' => $this->permissionPrefix(Permission::EDIT->value),
                'delete' => $this->permissionPrefix(Permission::DELETE->value),
                'destroy' => $this->permissionPrefix(Permission::DELETE->value),

                'restore' => $this->permissionPrefix(Permission::RESTORE->value),
                'forceDelete' => $this->permissionPrefix(Permission::FORCEDELETE->value),
                'duplicate' => $this->permissionPrefix(Permission::DUPLICATE->value),
                'activity' => $this->permissionPrefix(Permission::ACTIVITY->value),
                'show' => $this->permissionPrefix(Permission::SHOW->value),
            /**
             * TODO #additionalRoutePermission
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

            $authorized = ($this->isGateable() && array_key_exists($option, $authorizableOptions))
                ? Auth::guard(Modularity::getAuthGuardName())->user()->can($authorizableOptions[$option])
                : true;

            return ($this->indexOptions[$option] ?? $this->defaultIndexOptions[$option] ?? false) && $authorized;
        });
    }

    /**
     * @param array $schema
     * @return \Unusualify\Modularity\Http\Requests\Admin\Request::class
     */
    protected function validateFormRequest($schema = [])
    {
        $unauthorizedFields = Collection::make($this->fieldsPermissions)->filter(function ($permission, $field) {
            return Auth::guard(Modularity::getAuthGuardName())->user()->cannot($permission);
        })->keys();

        $unauthorizedFields->each(function ($field) {
            $this->request->offsetUnset($field);
        });

        return $this->getFormRequestClass($schema);
    }

    protected function getJSONData($with = [])
    {

        $scopes = $this->filterScope($this->nestedParentScopes());

        $paginator = $this->getIndexItems(with: $with, scopes: $scopes);

        return $this->getTransformer( $this->getFormattedIndexItems($paginator) );
        // return $this->getTransformer( $paginator->toArray() );
    }

    /**
     * getFormRequestClass
     *
     * @return void
     */
    public function getFormRequestClass($schema = null)
    {
        $formRequest = "$this->namespace\Http\Requests\\" . $this->modelName . 'Request';

        $chunkInputs = $this->chunkInputs(
            $schema ? $this->createFormSchema($schema) : $this->formSchema,
            true
        );

        if (@class_exists($formRequest)) {
            return App::makeWith($formRequest, [
                'rules' => Arr::mapWithKeys($chunkInputs, function ($input, $key) {

                    return isset($input['name']) && isset($input['rules']) && is_string($input['rules'])
                        ? [$input['name'] => $input['rules'] ?? []]
                        : [];
                }),
            ]);
        }

        return $this->request;
        // return TwillCapsules::getCapsuleForModel($this->modelName)->getFormRequestClass();
    }

    /**
     * getModuleConfig
     *
     * @return \StdClass::class
     */
    public function getModuleConfig()
    {
        $snakeCase = $this->getSnakeCase($this->moduleName);

        return array_to_object(Config::get(modularityBaseKey() . '.system_modules.' . $snakeCase) ?: Config::get($snakeCase));
    }

    /**
     * @return string
     */
    protected function getRoutePrefix()
    {
        if ($this->routePrefix !== null) {
            return $this->routePrefix;
        }

        return $this->generateRoutePrefix();

        if ($this->request->route() != null) {
            $routePrefix = ltrim(
                str_replace(
                    Config::get(modularityBaseKey() . '.admin_app_path'), // TODO uri segment control
                    '',
                    $this->request->route()->getPrefix()
                ),
                '/'
            );

            return str_replace('/', '.', $routePrefix);
        }

        return '';
    }

    protected function generateRoutePrefix($noNested = false)
    {
        $routePrefixes = [];

        $admin_route_prefix = adminRouteNamePrefix();

        if ($admin_route_prefix) {
            $routePrefixes[] = $admin_route_prefix;
        }

        if (isset($this->config->system_prefix)) {
            if ($this->config->system_prefix) {
                $routePrefixes[] = systemRouteNamePrefix();
            }

        } elseif (isset($this->config->base_prefix) && $this->config->base_prefix) {
            $routePrefixes[] = systemRouteNamePrefix();
        }

        if (! $this->isParent || ($this->isNested && ! $noNested)) {
            $routePrefixes[] = Str::snake($this->moduleName);
        }

        if ($this->isNested && ! $noNested) {
            $routePrefixes[] = $this->nestedParentName;
            $routePrefixes[] = 'nested';
        }

        return implode('.', $routePrefixes);
    }

    /**
     * @return \Unusualify\Modularity\Transformers\
     */
    protected function getTransformer($data = [])
    {

        if (! ($concrete = $this->getTransformerClass())) {
            return $data;
        }

        return App::makeWith($concrete, ['resource' => $data]);
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
        return Str::singular($this->nestedParentName) . '_id';

        $moduleParts = explode('.', $this->moduleName);

        return Str::singular($moduleParts[count($moduleParts) - 2]) . '_id';
    }

    /**
     * @return string
     */
    protected function nestedParentScopes()
    {
        if (! $this->isNested) {
            return [];
        }

        // for belongsTo relationship
        if ($this->repository->hasColumn($this->getParentModuleForeignKey())) {
            return [
                $this->getParentModuleForeignKey() => $this->nestedParentId,
            ];
        }

        // for morphTo relationship
        if (method_exists($this->repository->getModel(), ($morphToName = $this->getMorphToMethodName($this->routeName)))) {
            return [
                $morphToName . '_id' => $this->nestedParentId,
                $morphToName . '_type' => get_class($this->nestedParentModel),
            ];
        }

        // for hasOneThrough relationship
        if (method_exists($this->repository->getModel(), $this->getCamelCase($this->nestedParentName))) {
            return [
                'addRelation' . $this->getStudlyName($this->nestedParentName) => $this->nestedParentId,
            ];
        }

        dd(

            $this->nestedParentName,
            $this->nestedParentModel,
            $this->repository->getModel(),
            // get_class_methods($this->repository->getModel()),

        );

        return Str::singular($this->nestedParentName) . '_id';

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
        $parameters = $singleton ? [] : [snakeCase($this->routeName) => $id];

        if ($this->isNested) {
            $parameters[$this->nestedParentName] ??= $this->nestedParentId;
        }

        $prefix = $this->routePrefix;

        if (! in_array($action, ['index', 'create', 'store'])) {
            $prefix = $this->generateRoutePrefix(noNested: true);
        }

        return moduleRoute($this->routeName, $prefix, $action, $parameters, singleton: $singleton);
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
            return data_get($this->config->routes->{$this->getSnakeCase($this->routeName)}, $field_name) ?? $default;

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

    public function isGateable()
    {
        return ! env('PERMISSION_GATES_DEACTIVATE', false);
    }

    public function isRelationField($key)
    {
        $model_relations = [];

        // if(@method_exists($this->repository->getModel(), 'getDefinedRelations')){
        //     $model_relations = $this->repository->getDefinedRelations();
        // }

        if (@method_exists($this->repository->getModel(), 'definedRelations')) {
            $model_relations = $this->repository->definedRelations();
        }

        if (preg_match('/(.*)(_id)/', $key, $matches)) {
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
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addIndexWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexWith = array_merge($this->indexWith, $this->{$method}());
        }
    }

    protected function addFormWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addFormWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->formWith += $this->{$method}();
        }
    }

    protected function addWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexWith += $this->{$method}();
            $this->formWith += $this->{$method}();
        }
    }

    protected function getReplaceUrl()
    {
        if ($this->request->has('replaceUrl')) {
            return $this->request->get('replaceUrl') === 'true';
        }

        return true;
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
