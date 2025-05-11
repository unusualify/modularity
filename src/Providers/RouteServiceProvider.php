<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularity\Facades\HostRoutingRegistrar;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityRoutes;
use Unusualify\Modularity\Http\Controllers\GlideController;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Unusualify\Modularity\Http\Controllers';

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

        ModularityRoutes::configureRoutePatterns();

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
        $groupOptions = ModularityRoutes::groupOptions();

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
                                    ...ModularityRoutes::defaultMiddlewares(),
                                    ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : []),
                                ],
                            ],
                            function ($router) {
                                require __DIR__ . '/../../routes/auth.php';
                            }
                        );

                        // internal auth web routes
                        $router->group(
                            [
                                // 'domain' => modularityConfig('admin_app_url'),
                            ],
                            function ($router) {

                                $router->group(
                                    [
                                        'middleware' => ModularityRoutes::webPanelMiddlewares(),
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
                                    ...ModularityRoutes::webPanelMiddlewares(),
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
                        //             'domain' => modularityConfig('admin_app_subdomain', 'admin') .
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
            modularityConfig('media_library.image_service') ===
            'Unusualify\Modularity\Services\MediaLibrary\Glide'
        ) {
            $router
                ->get(
                    '/' . modularityConfig('glide.base_path') . '/{path}',
                    GlideController::class
                )
                ->where('path', '.*');
        }
    }

    private function mapModuleRoutes(
        $router,
        $supportSubdomainRouting = false
    ) {
        $groupOptions = ModularityRoutes::groupOptions();
        $controller_namespace = GenerateConfigReader::read('controller')->getNamespace();
        $front_controller_namespace = $controller_namespace . '\\Front';
        $routes_folder = GenerateConfigReader::read('routes')->getPath();

        foreach (Modularity::allEnabled() as $module) {
            $_groupOptions = [
                'prefix' => $module->fullPrefix(),
                'as' => $module->fullRouteNamePrefix() . '.',
            ];
            // $_groupOptions['prefix'] = $module->fullPrefix();
            // $_groupOptions['as'] = $module->fullRouteNamePrefix() . '.';
            ModularityRoutes::registerRoutes(
                $router,
                [...$_groupOptions, ...(Arr::only($groupOptions, ['domain']))],
                ['web'], // $middlewares,
                $module->getClassNamespace("{$controller_namespace}"),
                $module->getDirectoryPath("{$routes_folder}/web.php"),
                true
            );
            ModularityRoutes::registerRoutes(
                $router,
                $_groupOptions,
                ['api'], // $middlewares,
                $module->getClassNamespace("{$controller_namespace}\API"),
                $module->getDirectoryPath("{$routes_folder}/api.php"),
                true
            );
            ModularityRoutes::registerRoutes(
                $router,
                [
                    'domain' => config('app.url'),
                ],
                ['web'], // $middlewares,
                $module->getClassNamespace("{$controller_namespace}\Front"),
                $module->getDirectoryPath("{$routes_folder}/front.php"),
                true
            );

            $router->group([
                ...$groupOptions,
                ...[
                    'middleware' => ModularityRoutes::webPanelMiddlewares(),
                    'namespace' => $module->getClassNamespace("{$controller_namespace}"),
                ],
            ],
                function ($router) use ($module) {
                    Route::moduleRoutes($module);
                }
            );

            $router->group([
                ...[
                    'domain' => config('app.url'),
                    'middleware' => ModularityRoutes::webMiddlewares(),
                    'namespace' => $module->getClassNamespace("{$front_controller_namespace}"),
                ],
            ],
                function ($router) use ($module) {
                    Route::moduleFrontRoutes($module);
                }
            );

        }
    }

    /**
     * Register Route middleware.
     *
     * @return void
     */
    private function bootRouteMiddlewares(Router $router)
    {
        ModularityRoutes::generateRouteMiddlewares();
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

            $config = $module->getConfig();
            $moduleName = $config['name'] ?? $module->getName();
            if ($moduleName) { // && getStringOrFalse($config['name'])
                $pr = $module->getParentRoute();

                $has_system_prefix = $module->hasSystemPrefix();
                $system_prefix = $has_system_prefix ? systemUrlPrefix() . '/' : '';
                $system_route_name = $has_system_prefix ? systemRouteNamePrefix() : '';

                $parentStudlyName = studlyName($moduleName); // UserCompany
                $parentCamelName = camelCase($moduleName); // userCompany
                $parentKebabName = kebabCase($moduleName); // user-company
                $parentSnakeName = snakeCase($moduleName);  // user_company

                $parentUrlSegment = $config['url'] ?? $pr['url'] ?? pluralize($parentKebabName);

                if (is_array($routes = $module->getRouteConfigs(valid: true))) {

                    /**
                     * the fix of route precedence
                     * to define parent route the most lastly
                     *
                     *  */
                    usort($routes, fn ($i, $j) => (isset($i['parent']) || isset($j['parent']))
                            ? ((isset($i['parent']) && $i['parent']) ?: false)
                            : false
                    );

                    foreach ($routes as $key => $item) {
                        $middlewares = $module->getRouteMiddlewareAliases($item['name']);
                        $isSingleton = $module->isSingleton($item['name']);

                        if (isset($item['name'])) { // && getStringOrFalse($item['name'])
                            $itemKebabName = kebabCase($item['name']);
                            $itemStudlyName = studlyName($item['name']);
                            $itemSnakeName = snakeCase($item['name']);

                            $routeUrlSegment = $item['url'] ?? pluralize($itemKebabName);
                            if ($isSingleton) {
                                $routeUrlSegment = Str::singular($routeUrlSegment);
                            }

                            $controllerName = $itemStudlyName . 'Controller';

                            $resourceOptionsNames = $item['route_name'] ?? $itemSnakeName;
                            $resourceOptionsAs = [];
                            $parameters = [];
                            $prefixes = [];

                            if ($system_prefix) {
                                $prefixes[] = rtrim($system_prefix, '//');
                            }

                            if ($system_route_name) {
                                $resourceOptionsAs[] = $system_route_name;
                            }

                            $parameters[$routeUrlSegment] = $itemSnakeName;
                            // $parameters[$routeUrlSegment] = snakeCase(studlyName(Str::singular($routeUrlSegment)));

                            if (isset($item['belongs']) && $item['belongs']) {

                                foreach ($item['belongs'] as $key => $belong) {
                                    $belongRoute = $module->getRouteConfigs($belong);
                                    if ($belongRoute) {
                                        $belongRouteName = $belongRoute['route_name'] ?? snakeCase($belongRoute['name']); // package_region
                                        $belongRouteUrl = $belongRoute['url'] ?? pluralize(kebabCase($belongRoute['name'])); // package-regions
                                        // Route::prefix('packages')->group(function(){
                                        //     Route::resource('package-regions.package-countries', 'PackageCountryController', [
                                        //         'as' => 'package_region',
                                        //         'names' => 'package_region.nested.package_country',
                                        //     ]);
                                        // });
                                        Route::prefix($parentUrlSegment)->group(function () use (
                                            $parentSnakeName,
                                            $routeUrlSegment,
                                            $itemSnakeName,
                                            $controllerName,
                                            $belongRouteUrl,
                                            $belongRouteName,
                                            $parameters
                                        ) {
                                            $resourceRegistrar = Route::resource("{$belongRouteUrl}.{$routeUrlSegment}", $controllerName, [
                                                'as' => $parentSnakeName,
                                                'names' => nestedRouteNameFormat($belongRouteName, $itemSnakeName),
                                            ])->parameters($parameters + [
                                                $belongRouteUrl => $belongRouteName,
                                            ]);
                                            $resourceRegistrar->only([
                                                'index',
                                                'create',
                                                'store',
                                            ]);
                                        });
                                    }
                                }

                            }

                            if (($isNotParent = ! (isset($item['parent']) && $item['parent'])) || $parentUrlSegment !== $routeUrlSegment) { // unless parent route
                                // $url = $parentUrlSegment . "/" . $url;
                                $prefixes[] = $parentUrlSegment;

                                if ($isNotParent) {
                                    $resourceOptionsAs[] = $parentSnakeName;
                                }

                            }

                            $resourceOptions = [
                                'as' => implode('.', $resourceOptionsAs),
                                'names' => $resourceOptionsNames,
                            ];

                            $resourceOptionsAs[] = $itemSnakeName;
                            Route::middleware($middlewares)->prefix(implode('/', $prefixes))->group(function () use (
                                $isSingleton,
                                $controllerName,
                                $routeUrlSegment,
                                $itemStudlyName,
                                $resourceOptionsAs,
                                $resourceOptions,
                                $parameters,
                            ) {

                                if ($isSingleton) {
                                    Route::singleton($routeUrlSegment, $controllerName, $resourceOptions);
                                    // Route::get($routeUrlSegment, $controllerName . '@editSingleton')->name($itemSnakeName);
                                } else {
                                    Route::additionalRoutes($routeUrlSegment, $itemStudlyName, [
                                        'as' => implode('.', $resourceOptionsAs),
                                    ]);

                                    Route::resource($routeUrlSegment, $controllerName, $resourceOptions)
                                        // ->scoped($scoped)
                                        ->parameters($parameters);
                                }

                            });
                        }

                    }
                }
            }

            // Route::group($options, function() use($module, $options){
            // });

        });

        Route::macro('moduleFrontRoutes', function ($module, $options = []) {

            $config = $module->getConfig();
            $moduleName = $config['name'] ?? $module->getName();
            if ($moduleName) { // && getStringOrFalse($config['name'])
                $pr = $module->getParentRoute();

                $has_system_prefix = $module->hasSystemPrefix();
                $system_prefix = $has_system_prefix ? systemUrlPrefix() . '/' : '';
                $system_route_name = $has_system_prefix ? systemRouteNamePrefix() : '';

                $parentStudlyName = studlyName($moduleName); // UserCompany
                $parentCamelName = camelCase($moduleName); // userCompany
                $parentKebabName = kebabCase($moduleName); // user-company
                $parentSnakeName = snakeCase($moduleName);  // user_company

                $parentUrlSegment = $config['url'] ?? $pr['url'] ?? pluralize($parentKebabName);

                if (is_array($routes = $module->getRouteConfigs(valid: true))) {

                    /**
                     * the fix of route precedence
                     * to define parent route the most lastly
                     *
                     *  */
                    usort($routes, fn ($i, $j) => (isset($i['parent']) || isset($j['parent']))
                            ? ((isset($i['parent']) && $i['parent']) ?: false)
                            : false
                    );

                    foreach ($routes as $key => $item) {
                        $hasFrontRoutes = $item['has_front_routes'] ?? false;

                        if(!$hasFrontRoutes) continue;

                        $isSingleton = $module->isSingleton($item['name']);

                        if (isset($item['name'])) { // && getStringOrFalse($item['name'])
                            $itemKebabName = kebabCase($item['name']);
                            $itemStudlyName = studlyName($item['name']);
                            $itemSnakeName = snakeCase($item['name']);
                            $routeUrlSegment = $item['url'] ?? pluralize($itemKebabName);
                            if ($isSingleton) {
                                $routeUrlSegment = Str::singular($routeUrlSegment);
                            }

                            $controllerName = $itemStudlyName . 'Controller';

                            $resourceOptionsNames = $item['route_name'] ?? $itemSnakeName;
                            $resourceOptionsAs = [];
                            $parameters = [];
                            $prefixes = [];

                            if ($system_prefix) {
                                $prefixes[] = rtrim($system_prefix, '//');
                            }

                            if ($system_route_name) {
                                $resourceOptionsAs[] = $system_route_name;
                            }

                            $parameters[$routeUrlSegment] = $itemSnakeName;

                            if (($isNotParent = ! (isset($item['parent']) && $item['parent'])) || $parentUrlSegment !== $routeUrlSegment) { // unless parent route
                                $prefixes[] = $parentUrlSegment;

                                if ($isNotParent) {
                                    $resourceOptionsAs[] = $parentSnakeName;
                                }

                            }

                            $resourceOptions = [
                                'as' => implode('.', $resourceOptionsAs),
                                'names' => $resourceOptionsNames,
                            ];

                            $resourceOptionsAs[] = $itemSnakeName;

                            Route::prefix(implode('/', $prefixes))->group(function () use (
                                $isSingleton,
                                $controllerName,
                                $routeUrlSegment,
                                $resourceOptions,
                                $parameters,
                            ) {
                                if ($isSingleton) {
                                    Route::singleton($routeUrlSegment, $controllerName, $resourceOptions);
                                } else {
                                    Route::resource($routeUrlSegment, $controllerName, $resourceOptions + ['only' => ['index', 'create', 'store', 'show']])
                                        // ->scoped($scoped)
                                        ->parameters($parameters);
                                }
                            });
                        }

                    }
                }
            }

            // Route::group($options, function() use($module, $options){
            // });

        });

        Route::macro('additionalRoutes', function ($url, $routeName, $options) {

            $customRoutes = $defaults = [
                'reorder',
                // 'publish',
                // 'bulkPublish',
                // 'browser',
                // 'feature',
                // 'preview',
                // 'bulkFeature',
                // 'restoreRevision',

                'restore',
                'bulkRestore',
                'forceDelete',
                'bulkForceDelete',
                'bulkDelete',
                'duplicate',

                'tags',
                'tagsUpdate',

                'assignments',
                'createAssignment',
            ];

            $controllerClass = "{$routeName}Controller";
            $snakeCase = snakeCase($routeName);
            // if (isset($options['only'])) {
            //     $customRoutes = array_intersect(
            //         $defaults,
            //         (array) $options['only']
            //     );
            // } elseif (isset($options['except'])) {
            //     $customRoutes = array_diff(
            //         $defaults,
            //         (array) $options['except']
            //     );
            // }
            foreach ($customRoutes as $customRoute) {
                $customRouteKebab = kebabCase($customRoute);
                $routeSlug = "{$url}/{$customRouteKebab}";

                $mapping = [
                    // 'as' => $customRoutePrefix . ".{$customRoute}",
                    'as' => $options['as'] . ".{$customRoute}",
                    'uses' => "{$controllerClass}@{$customRoute}",
                ];

                if($customRoute === 'assignments') {
                    Route::get("{$url}/{{$snakeCase}}/assignments", $mapping);
                }

                if($customRoute === 'createAssignment') {
                    Route::post("{$url}/{{$snakeCase}}/assignments", $mapping);
                }

                if (in_array($customRoute, ['browser', 'tags'])) {
                    Route::get($routeSlug, $mapping);
                }

                if (in_array($customRoute, ['restoreRevision'])) {
                    Route::get($routeSlug . "/{{$snakeCase}}", $mapping);
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

                if (in_array($customRoute, ['duplicate'])) {
                    Route::put($routeSlug . "/{{$snakeCase}}", $mapping);
                }

                if (in_array($customRoute, ['preview'])) {
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
                    ])
                ) {

                    Route::post($routeSlug, $mapping);
                }

            }

        });
    }

    public static function shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)
    {
        return ! empty($groupPrefix) && (blank($lastRouteGroupName) ||
            modularityConfig('allow_duplicates_on_route_names', true) ||
            (! Str::endsWith($lastRouteGroupName, ".{$groupPrefix}.")));
    }

    public static function getLastRouteGroupName()
    {
        // Get the current route groups
        $routeGroups = Route::getGroupStack() ?? [];

        // dd(Route::getGroupStack(), end($routeGroups));

        // Get the name prefix of the last group
        return end($routeGroups)['as'] ?? '';
    }

    public static function getGroupPrefix()
    {
        $groupPrefix = trim(
            str_replace('/', '.', Route::getLastGroupPrefix()),
            '.'
        );

        if (! empty(modularityConfig('admin_app_path'))) {
            $groupPrefix = ltrim(
                str_replace(
                    modularityConfig('admin_app_path'),
                    '',
                    $groupPrefix
                ),
                '.'
            );
        }

        return $groupPrefix;
    }
}
