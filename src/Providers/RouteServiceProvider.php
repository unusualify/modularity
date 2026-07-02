<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Facades\HostRoutingRegistrar;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Controllers\GlideController;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Unusualify\Modularous\Http\Controllers';

    /**
     * Bootstraps the package services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerMacros();

        $this->bootMacros();

        $this->bootRouteMiddlewares($this->app->get('router'));

        require __DIR__ . '/../../routes/channels.php';

        parent::boot();
    }

    /**
     * @return void
     */
    public function map(Router $router)
    {

        ModularousRoutes::configureRoutePatterns();

        $this->mapSystemRoutes(
            $router
        );

        $this->mapModuleRoutes(
            $router
        );
    }

    private function mapSystemRoutes(
        $router,
        $supportSubdomainRouting = false
    ) {
        $groupOptions = ModularousRoutes::groupOptions();

        $router->group(
            [
                'namespace' => $this->namespace,
            ],
            function ($router) use ($groupOptions, $supportSubdomainRouting) {
                $router->group(
                    $groupOptions,
                    function ($router) use ($supportSubdomainRouting) {
                        // internal authentication routes (login,register,forgot-password etc.)
                        $router->group(
                            [
                                'middleware' => [
                                    'web',
                                    ...ModularousRoutes::defaultMiddlewares(),
                                    ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : []),
                                ],
                                'namespace' => 'Auth',
                            ],
                            function ($router) {
                                require __DIR__ . '/../../routes/auth.php';
                            }
                        );

                        // internal auth web routes
                        $router->group(
                            [
                                // 'domain' => modularousConfig('admin_app_url'),
                                'domain' => Modularous::getAdminAppUrl(),
                            ],
                            function ($router) {

                                $router->group(
                                    [
                                        'middleware' => ModularousRoutes::webPanelMiddlewares(),
                                    ],
                                    function ($router) {
                                        require __DIR__ . '/../../routes/web.php';
                                    }
                                );

                            }
                        );

                        // internal auth api routes
                        $router->group(
                            [
                                'prefix' => 'api',
                                'middleware' => [
                                    ...ModularousRoutes::webPanelMiddlewares(),
                                    ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : []),
                                ],
                            ],
                            function ($router) {
                                require __DIR__ . '/../../routes/api.php';
                            }
                        );

                        // if ($supportSubdomainRouting) {
                        //     $router->group(
                        //         [
                        //             'domain' => modularousConfig('admin_app_subdomain', 'admin') .
                        //             '.{subdomain}.' .
                        //             config('app.url'),
                        //         ],
                        //         $internalRoutes
                        //     );
                        // }
                    }
                );

            }
        );

        $router->group(
            [
                'middleware' => ['web'],
                // 'namespace' => $this->namespace,
            ],
            function ($router) {
                require __DIR__ . '/../../routes/front.php';
            }
        );

        if (
            modularousConfig('media_library.image_service') ===
            'Unusualify\Modularous\Services\MediaLibrary\Glide'
        ) {
            $router
                ->get(
                    '/' . modularousConfig('glide.base_path') . '/{path}',
                    GlideController::class
                )
                ->where('path', '.*');
        }
    }

    private function mapModuleRoutes(
        $router,
        $supportSubdomainRouting = false
    ) {
        $groupOptions = ModularousRoutes::groupOptions();
        $controller_namespace = GenerateConfigReader::read('controller')->getNamespace();
        $front_controller_namespace = $controller_namespace . '\\Front';
        $routes_folder = GenerateConfigReader::read('routes')->getPath();

        $apiGroupOptions = ModularousRoutes::getApiGroupOptions();
        $apiController_namespace = GenerateConfigReader::read('controller')->getNamespace();
        $api_controller_namespace = $apiController_namespace . '\\API';

        if (modularousConfig('define_panel_routes_on_frontend_requests') || Modularous::isPanelUrl() || $this->app->runningInConsole()) {
            foreach (Modularous::allEnabled() as $module) {
                $_groupOptions = [
                    'prefix' => $module->fullPrefix(),
                    'as' => $module->panelRouteNamePrefix() . '.',
                ];

                ModularousRoutes::registerRoutes(
                    $router,
                    [...$_groupOptions, ...(Arr::only($groupOptions, ['domain']))],
                    ['web'],
                    $module->getClassNamespace("{$controller_namespace}"),
                    $module->getDirectoryPath("{$routes_folder}/web.php"),
                    true
                );

                $router->group([
                    ...$groupOptions,
                    'middleware' => ModularousRoutes::webPanelMiddlewares(),
                    'namespace' => $module->getClassNamespace("{$controller_namespace}"),
                ], function () use ($module) {
                    Route::moduleRoutes($module);
                });

                ModularousRoutes::registerRoutes(
                    $router,
                    ['domain' => config('app.url')],
                    ['web'],
                    $module->getClassNamespace("{$controller_namespace}\Front"),
                    $module->getDirectoryPath("{$routes_folder}/front.php"),
                    true
                );

                $router->group([
                    'domain' => config('app.url'),
                    'middleware' => ModularousRoutes::webMiddlewares(),
                    'namespace' => $module->getClassNamespace("{$front_controller_namespace}"),
                ], function () use ($module) {
                    Route::moduleFrontRoutes($module);
                });

                if (file_exists($module->getDirectoryPath("{$routes_folder}/public-api.php"))) {
                    ModularousRoutes::registerRoutes(
                        $router,
                        ModularousRoutes::getPublicApiGroupOptions(),
                        [],
                        $module->getClassNamespace("{$api_controller_namespace}"),
                        $module->getDirectoryPath("{$routes_folder}/public-api.php"),
                        true
                    );
                }

                if (file_exists($module->getDirectoryPath("{$routes_folder}/api.php"))) {
                    ModularousRoutes::registerRoutes(
                        $router,
                        ModularousRoutes::getAuthApiGroupOptions(),
                        [],
                        $module->getClassNamespace("{$api_controller_namespace}"),
                        $module->getDirectoryPath("{$routes_folder}/api.php"),
                        true
                    );
                }

                $router->group([
                    'prefix' => ModularousRoutes::getApiPrefix(),
                    'as' => 'api.',
                    'namespace' => $module->getClassNamespace("{$api_controller_namespace}"),
                ], function () use ($module) {
                    Route::moduleApiRoutes($module);
                });
            }
        }
    }

    /**
     * Register Route middleware.
     *
     * @return void
     */
    private function bootRouteMiddlewares(Router $router)
    {
        ModularousRoutes::generateRouteMiddlewares();
    }

    /**
     * Registers Route macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        Route::macro('moduleShowWithPreview', function (
            $moduleName,
            $routePrefix = null,
            $controllerName = null
        ) {
            // if ($routePrefix === null) {
            //     $routePrefix = $moduleName;
            // }

            // if ($controllerName === null) {
            //     $controllerName = ucfirst(Str::plural($moduleName));
            // }

            // $routePrefix = empty($routePrefix)
            // ? '/'
            // : (Str::startsWith($routePrefix, '/')
            //     ? $routePrefix
            //     : '/' . $routePrefix);
            // $routePrefix = Str::endsWith($routePrefix, '/')
            // ? $routePrefix
            // : $routePrefix . '/';

            // Route::name($moduleName . '.show')->get(
            //     $routePrefix . '{slug}',
            //     $controllerName . 'Controller@show'
            // );
            // Route::name($moduleName . '.preview')
            //     ->get(
            //         '/admin-preview' . $routePrefix . '{slug}',
            //         $controllerName . 'Controller@show'
            //     )
            //     ->middleware(['web', 'twill_auth:twill_users', 'can:list']);
        });
    }

    /**
     *  Boot Route macros.
     *
     * @return void
     */
    protected function bootMacros()
    {
        Route::macro('hasAdmin', function ($routeName) {
            if (Route::has($routeName)) {
                return $routeName;
            }

            $admin_route_prefix = adminRouteNamePrefix();

            if (explode('.', $routeName)[0] !== $admin_route_prefix && Route::has($admin_route_prefix . '.' . $routeName)) {
                return $admin_route_prefix . '.' . $routeName;
            } else {
                return false;
            }
        });

        Route::macro('host', function (...$models) {
            return HostRoutingRegistrar::host(...$models);
        });

        Route::macro('moduleRoutes', function ($module, $options = []) {
            ModularousRoutes::registerModuleRoutes($module, $options, 'admin');
        });

        Route::macro('moduleFrontRoutes', function ($module, $options = []) {
            ModularousRoutes::registerModuleRoutes($module, $options, 'front');
        });

        Route::macro('additionalRoutes', function ($url, $routeName, $options) {

            $groupStack = Route::getGroupStack();
            $namespace = $groupStack[count($groupStack) - 1]['namespace'] ?? null;
            $controllerFqcn = ($namespace && $routeName !== '')
                ? "{$namespace}\\{$routeName}Controller"
                : null;

            $controllerResolvable = $controllerFqcn !== null
                && class_exists($controllerFqcn);

            $controllerInstance = $controllerResolvable ? app()->make($controllerFqcn) : null;
            $module = $controllerInstance ? $controllerInstance->getModule() : null;
            $isSingleton = $module ? $module->isSingleton($routeName) : false;

            $customRoutes = [
                ...(!$isSingleton ? [
                    'reorder',
                    // 'publish',
                    // 'bulkPublish',
                    // 'browser',
                    // 'feature',
                    // 'preview',
                    // 'bulkFeature',
                    'showView',
                    'restore',
                    'bulkRestore',
                    'forceDelete',
                    'bulkForceDelete',
                    'bulkDelete',
                    'duplicate',
                ] : []),

                'listRevisions',
                'restoreRevision',
                'approveRevision',
                'rejectRevision',
                'tags',
                'tagsUpdate',
                'assignments',
                'createAssignment',
            ];

            $bulkSheetController = null;
            $bulkSheetStepUpMiddleware = null;
            if ($controllerResolvable && is_subclass_of($controllerFqcn, CanBulkSheet::class)) {
                try {
                    $bulkSheetController = app()->make($controllerFqcn);
                    $customRoutes = array_merge($customRoutes, [
                        'bulkSheetTool',
                        'bulkSheetDryRun',
                        'bulkSheetCommit',
                        'bulkSheetExport',
                    ]);
                    $ability = $bulkSheetController->bulkSheetStepUpAbility();
                    if (
                        $ability !== null && $ability !== ''
                        && modularousConfig('cms_features.register_middlewares', true)
                        && modularousConfig('security.enabled', false)
                    ) {
                        $bulkSheetStepUpMiddleware = 'modularous.security.step_up:' . $ability;
                    }
                } catch (\Throwable) {
                    // Submodule may omit this controller; skip bulk sheet routes.
                }
            }

            if($module && $module->isResourceCacheEnabled($routeName)) {
                $customRoutes = array_merge($customRoutes, [
                    'cachePurge',
                    'cacheWarm',
                    ...(!$isSingleton ? ['cachePurgeAll', 'cacheWarmAll'] : []),
                ]);
            }

            if($module && $module->hasRemoteApiSource($routeName)) {
                $customRoutes = array_merge($customRoutes, [
                    'syncRemote',
                    'syncRemoteAll',
                    'clearRemoteCache',
                    'previewRemote',
                    'listRemoteCatalog',
                ]);
            }

            $controllerName = "{$routeName}Controller";
            $snakeCase = snakeCase($routeName);

            foreach ($customRoutes as $customRoute) {
                $mapping = [
                    // 'as' => $customRoutePrefix . ".{$customRoute}",
                    'as' => $options['as'] . ".{$customRoute}",
                    'uses' => "{$controllerName}@{$customRoute}",
                ];

                if ($bulkSheetController instanceof CanBulkSheet && in_array($customRoute, [
                    'bulkSheetTool',
                    'bulkSheetDryRun',
                    'bulkSheetCommit',
                    'bulkSheetExport',
                ], true)) {
                    $names = $bulkSheetController->bulkSheetWebRouteNames();
                    $asKey = match ($customRoute) {
                        'bulkSheetTool' => $names['tool'],
                        'bulkSheetDryRun' => $names['dryRun'],
                        'bulkSheetCommit' => $names['commit'],
                        'bulkSheetExport' => $names['export'],
                        default => $customRoute,
                    };
                    $mapping['as'] = $options['as'] . '.' . $asKey;

                    if ($customRoute === 'bulkSheetTool') {
                        Route::get("{$url}/bulk", $mapping);
                    } elseif ($customRoute === 'bulkSheetDryRun') {
                        $r = Route::post("{$url}/bulk/dry-run", $mapping);
                        if ($bulkSheetStepUpMiddleware !== null) {
                            $r->middleware($bulkSheetStepUpMiddleware);
                        }
                    } elseif ($customRoute === 'bulkSheetCommit') {
                        $r = Route::post("{$url}/bulk/commit", $mapping);
                        if ($bulkSheetStepUpMiddleware !== null) {
                            $r->middleware($bulkSheetStepUpMiddleware);
                        }
                    } elseif ($customRoute === 'bulkSheetExport') {
                        Route::get("{$url}/bulk/export", $mapping);
                    }
                }

                if (! $controllerResolvable || ! method_exists($controllerFqcn, $customRoute)) {
                    continue;
                }

                $customRouteKebab = kebabCase($customRoute);
                $routeSlug = "{$url}/{$customRouteKebab}";

                if (in_array($customRoute, ['assignments', 'listRevisions'])) {
                    // dd($customRoute, $routeSlug, $mapping, $url, $snakeCase);
                    Route::get("{$url}/{{$snakeCase}}/{$customRouteKebab}", $mapping);
                }

                if ($customRoute === 'createAssignment') {
                    Route::post("{$url}/{{$snakeCase}}/assignments", $mapping);
                }

                if (in_array($customRoute, ['browser', 'tags'])) {
                    Route::get($routeSlug, $mapping);
                }

                if ($customRoute === 'restoreRevision') {
                    Route::get($routeSlug . "/{{$snakeCase}}", $mapping);
                    $putMapping = $mapping;
                    unset($putMapping['as']);
                    Route::put($routeSlug . "/{{$snakeCase}}", $putMapping);

                    continue;
                }

                if (
                    in_array($customRoute, [
                        'publish',
                        'feature',
                        'restore',
                        'forceDelete',
                        'tagsUpdate',
                    ])
                ) {

                    Route::put($routeSlug, $mapping);
                }

                if (in_array($customRoute, ['duplicate', 'preview', 'showView', 'approveRevision', 'rejectRevision', 'syncRemote', 'previewRemote'])) {
                    Route::put($routeSlug . "/{{$snakeCase}}", $mapping);
                }

                if (
                    in_array($customRoute, [
                        'reorder',
                        'bulkPublish',
                        'bulkFeature',
                        'bulkDelete',
                        'bulkRestore',
                        'bulkForceDelete',
                        'syncRemoteAll',
                        'clearRemoteCache',
                        'cachePurgeAll',
                        'cacheWarmAll',
                    ])
                ) {
                    Route::post($routeSlug, $mapping);
                }

                if (in_array($customRoute, ['cachePurge', 'cacheWarm'])) {
                    Route::post("{$url}/cache/" . ($customRoute === 'cachePurge' ? 'purge' : 'warm') . "/{{$snakeCase}}", $mapping);
                }

                if (in_array($customRoute, ['cachePurgeAll', 'cacheWarmAll'])) {
                    Route::post("{$url}/cache/" . ($customRoute === 'cachePurgeAll' ? 'purge-all' : 'warm-all'), $mapping);
                }

                if ($customRoute === 'listRemoteCatalog') {
                    Route::get($routeSlug, $mapping);
                }

            }

        });

        // API Route Macros
        Route::macro('moduleApiRoutes', function ($module, $options = []) {
            ModularousRoutes::registerModuleRoutes($module, $options, 'api');
        });

        Route::macro('apiAdditionalRoutes', function ($url, $routeName, $options, $customRoutes = null) {
            $customRoutes = $customRoutes ?? ModularousRoutes::getCustomApiRoutes();

            $controllerClass = "{$routeName}Controller";
            $snakeCase = snakeCase($routeName);

            foreach ($customRoutes as $customRoute) {
                $customRouteKebab = kebabCase($customRoute);
                $routeSlug = "{$url}/{$customRouteKebab}";

                $mapping = [
                    'as' => $options['as'] . ".{$customRoute}",
                    'uses' => "{$controllerClass}@{$customRoute}",
                ];

                if (in_array($customRoute, ['bulk', 'import'])) {
                    Route::post($routeSlug, $mapping);
                } else {
                    Route::get($routeSlug, $mapping);
                }
            }
        });
    }
}
