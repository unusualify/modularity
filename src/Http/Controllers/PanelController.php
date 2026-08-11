<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\CacheableResponse;
use Unusualify\Modularous\Http\Controllers\Traits\MakesResponses;
use Unusualify\Modularous\Http\Controllers\Traits\ManageAppends;
use Unusualify\Modularous\Http\Controllers\Traits\ManageAuthorization;
use Unusualify\Modularous\Http\Controllers\Traits\ManageScopes;
use Unusualify\Modularous\Http\Controllers\Traits\ManageWiths;

abstract class PanelController extends CoreController implements CacheableInterface
{
    use MakesResponses, ManageScopes, ManageAuthorization, CacheableResponse, ManageWiths, ManageAppends;

    /**
     * @var Unusualify\Modularous\Entities\Model
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
     * @var Model
     */
    protected $nestedParentModel;

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
        // if (modularousConfig('bind_exception_handler', true)) {
        //     App::singleton(ExceptionHandler::class, ModularousHandler::class);
        // }
        parent::__construct($app, $request);

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            $this->preload();

            return $next($request);
        });

        // $this->setMiddlewareBasePermission();
        $this->setMiddlewarePermissions();

        // $this->titleColumnKey = $this->getConfigFieldsByRoute('title_column_key', 'name');

        $this->isParent = $this->isParentRoute();

        $this->checkNestedAttributes();

        // $this->routePrefix = $this->getRoutePrefix();

        $this->modelTitle = $this->getModelTitle();

        $this->__beforeConstruct($app, $request);

        /*
         * Apply any filters that are selected by default
         */
        $this->applyFiltersDefaultOptions();

        // $this->fixedFilters = array_merge((array) $this->getConfigFieldsByRoute('filters.fixed', []), $this->fixedFilters ?? []);

    }

    public function preload()
    {
        $rawRouteConfig = $this->module ? $this->module->getRawRouteConfig($this->routeName) : [];

        $this->titleColumnKey = $rawRouteConfig['title_column_key'] ?? 'name';

        $this->fixedFilters = array_merge((array) ($rawRouteConfig['filters']['fixed'] ?? []), $this->fixedFilters ?? []);

        parent::preload();

        $this->routePrefix = $this->getRoutePrefix();

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

    protected function addMiddlewarePermissions()
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method();
        }
    }

    protected function setMiddlewarePermission($permission, $options)
    {
        $this->middleware($this->module->generatePermissionMiddlewareDefinition($permission, $this->routeName), $options);
    }

    protected function setMiddlewarePermissions()
    {
        if ($this->isGateable() && $this->setDefaultPermissions) {

            if ($this->module) {
                $permissions = [
                    'VIEW' => ['only' => ['index', 'show']],
                    'CREATE' => ['only' => ['create', 'store']],
                    'EDIT' => ['only' => ['edit', 'update']],
                    'DELETE' => ['only' => ['delete']],
                    'FORCEDELETE' => ['only' => ['forceDelete']],
                    'RESTORE' => ['only' => ['restore']],
                    'DUPLICATE' => ['only' => ['duplicate']],
                    'REORDER' => ['only' => ['reorder']],

                    // 'LIST' => [ 'only' => ['index', 'show']],
                    // 'EDIT' => [ 'only' => ['edit', 'update']],
                    // 'DUPLICATE' => [ 'only' => ['duplicate']],
                    // 'PUBLISH' => [ 'only' => ['publish', 'feature', 'bulkPublish', 'bulkFeature']],
                    // 'REORDER' => [ 'only' => ['reorder']],
                    // 'DELETE' => [ 'only' => ['destroy', 'bulkDelete', 'restore', 'bulkRestore', 'forceDelete', 'bulkForceDelete', 'restoreRevision']],

                ];
                foreach ($permissions as $permission => $options) {
                    $this->setMiddlewarePermission($permission, $options);
                }
            }

            $this->addMiddlewarePermissions();
        }
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
            if ($this->module->hasRoute($nestedParentName)) {
                $nestedParentModel = $this->module->getRouteClass($nestedParentName, 'model');
                $nestedParentModel = $nestedParentModel::find($nestedParentId);

                return [true, $nestedParentId, $nestedParentName, $nestedParentModel];
            }
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
        $rawRouteConfig = $this->module ? $this->module->getRawRouteConfig($this->routeName) : [];

        return $this->isParent ?? ($rawRouteConfig['parent'] ?? false) ?: $this->moduleName == $this->routeName;

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
                'index' => $this->module->generatePermissionName(Permission::VIEW->value, $this->routeName),
                'create' => $this->module->generatePermissionName(Permission::CREATE->value, $this->routeName),
                'edit' => $this->module->generatePermissionName(Permission::EDIT->value, $this->routeName),
                'delete' => $this->module->generatePermissionName(Permission::DELETE->value, $this->routeName),
                'destroy' => $this->module->generatePermissionName(Permission::DELETE->value, $this->routeName),

                'restore' => $this->module->generatePermissionName(Permission::RESTORE->value, $this->routeName),
                'forceDelete' => $this->module->generatePermissionName(Permission::FORCEDELETE->value, $this->routeName),
                'duplicate' => $this->module->generatePermissionName(Permission::DUPLICATE->value, $this->routeName),
                'activity' => $this->module->generatePermissionName(Permission::ACTIVITY->value, $this->routeName),
                'show' => $this->module->generatePermissionName(Permission::SHOW->value, $this->routeName),
            /**
             * TODO #additionalRoutePermission
             */
                // 'duplicate' => $this->module->generatePermissionName(Permission::DUPLICATE->value, $this->routeName),

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
                ? (($guard = Auth::guard(Modularous::getAuthGuardName()))
                    ? (($user = $guard->user())
                        ? $user->can($authorizableOptions[$option])
                        : false)
                    : false)
                : true;

            return ($this->indexOptions[$option] ?? $this->defaultIndexOptions[$option] ?? false) && $authorized;
        });
    }

    /**
     * @param array $schema
     * @return \Unusualify\Modularous\Http\Requests\Admin\Request::class
     */
    protected function validateFormRequest($schema = [])
    {
        $unauthorizedFields = Collection::make($this->fieldsPermissions)->filter(function ($permission, $field) {
            return Auth::guard(Modularous::getAuthGuardName())->user()->cannot($permission);
        })->keys();

        $unauthorizedFields->each(function ($field) {
            $this->request->offsetUnset($field);
        });

        return $this->getFormRequestClass($schema);
    }

    protected function getJSONData($with = [])
    {
        $scopes = $this->filterScope($this->nestedParentScopes());

        $appends = $this->request->get('appends', []);

        if (is_string($appends)) {
            $appends = explode(',', $appends);
        }

        $noFormatted = $this->request->get('light', false);

        if ($noFormatted) {
            $with = $this->request->get('eager', []);
            $appends = $this->request->get('appends', []);
            $column = $this->request->get('columns', [$this->titleColumnKey]);
            $scopes = $this->request->get('scopes', []);
            $orders = $this->request->get('orders', []);
            $perPage = $this->request->get('itemsPerPage', $this->perPage);

            return $this->repository->list(
                column: $column,
                with: $with,
                scopes: $scopes,
                orders: $orders,
                perPage: $perPage,
                appends: $appends,
                forcePagination: true
            );
        }

        $this->addIndexAppends();
        $this->addFormAppends();
        $paginator = $this->getIndexItems(with: $with, scopes: $scopes, appends: $appends);

        return $this->getFormattedIndexItems($paginator);
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
                    Config::get(modularousBaseKey() . '.admin_app_path'), // TODO uri segment control
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
    protected function getModuleRouteUrl($id, $action, $singleton = false)
    {
        // Do not force ModuleRouteRegistry here — hot-path ADR. Use already-resolved
        // ModuleRoute when present; otherwise fall back to controller routeName.
        $routeName = $this->moduleRoute?->name()
            ?? $this->moduleRouteName
            ?? $this->routeName;
        $parameters = $singleton ? [] : [snakeCase((string) $routeName) => $id];

        if ($this->isNested) {
            $parameters[$this->nestedParentName] ??= $this->nestedParentId;
        }

        $prefix = $this->routePrefix;

        if (! in_array($action, ['index', 'create', 'store'])) {
            $prefix = $this->generateRoutePrefix(noNested: true);
        }

        return moduleRoute($routeName, $prefix, $action, $parameters, singleton: $singleton);
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

    public function isGateable()
    {
        return ! env('PERMISSION_GATES_DEACTIVATE', false);
    }

    public function isRelationField($key)
    {
        $model_relations = [];

        $exploded = explode('.', $key);

        $moduleModel = $this->repository->getModel();
        $isNestedKey = count($exploded) > 1;
        $lastIndex = count($exploded) - 1;

        foreach ($exploded as $i => $relation) {
            if (@method_exists($moduleModel, 'definedRelations')) {
                $relations = $moduleModel->definedRelations();

                if ($i == 0) {
                    $model_relations = $relations;
                }

                if ($isNestedKey) {
                    if (in_array($relation, $relations)) {
                        if ($i == $lastIndex) {
                            return true;
                        }

                        $moduleModel = $moduleModel->{$relation}()->getModel();
                    }
                }
            }
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

    protected function getReplaceUrl()
    {
        if ($this->request->has('replaceUrl')) {
            return $this->request->get('replaceUrl') === 'true';
        }

        return true;
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

    /**
     * @param array $scopes
     * @param bool $forcePagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIndexItems($with = [], $scopes = [], $appends = [], $forcePagination = false)
    {
        $perPage = method_exists($this, 'resolveIndexQueryPerPage')
            ? $this->resolveIndexQueryPerPage()
            : ($this->request->ajax()
                ? (int) ($this->request->get('itemsPerPage') ?? $this->perPage ?? 10)
                : 0);

        $exceptIds = $this->request->get('exceptIds') ?? [];

        if (is_string($exceptIds)) {
            $exceptIds = explode(',', $exceptIds);
        }

        return $this->transformIndexItems($this->repository->getPaginator(
            with: ($this->indexWith ?? []) + $with,
            scopes: $scopes,
            orders: $this->orderScope(),
            perPage: $perPage,
            forcePagination: $forcePagination,
            appends: $appends,
            id: $this->request->get('id') ?? null,
            exceptIds: $exceptIds
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
     * @param array $paginator
     * @return array
     */
    public function getFormattedIndexItems(AbstractPaginator $paginator) // getIndexTableItems
    {
        return $paginator;
    }
}
