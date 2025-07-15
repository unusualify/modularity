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
                                'domain' => Modularity::getAdminAppUrl(),
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
                'as' => $module->panelRouteNamePrefix() . '.',
            ];
            // $_groupOptions['prefix'] = $module->fullPrefix();
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
            ModularityRoutes::registerModuleRoutes($module, $options, 'admin');
        });

        Route::macro('moduleFrontRoutes', function ($module, $options = []) {
            ModularityRoutes::registerModuleRoutes($module, $options, 'front');
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

                if ($customRoute === 'assignments') {
                    Route::get("{$url}/{{$snakeCase}}/assignments", $mapping);
                }

                if ($customRoute === 'createAssignment') {
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
}
