<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Middleware\AuthenticateMiddleware;
use Unusualify\Modularity\Http\Middleware\AuthorizationMiddleware;
use Unusualify\Modularity\Http\Middleware\CompanyRegistrationMiddleware;
use Unusualify\Modularity\Http\Middleware\HostableMiddleware;
use Unusualify\Modularity\Http\Middleware\ImpersonateMiddleware;
use Unusualify\Modularity\Http\Middleware\LanguageMiddleware;
use Unusualify\Modularity\Http\Middleware\LogMiddleware;
use Unusualify\Modularity\Http\Middleware\NavigationMiddleware;
use Unusualify\Modularity\Http\Middleware\RedirectIfAuthenticatedMiddleware;

class ModularityRoutes
{
    public $counter = 1;

    private $defaultMiddlewares = [
        'modularity.log',
        'modularity.core',
    ];

    public function configureRoutePatterns(): void
    {
        if (($patterns = modularityConfig('route_patterns')) != null) {
            if (is_array($patterns)) {
                foreach ($patterns as $label => $pattern) {
                    Route::pattern($label, $pattern);
                }
            }
        }
    }

    public function groupOptions(): array
    {
        return [
            'as' => Modularity::getAdminRouteNamePrefix() . '.',
            ...(Modularity::hasAdminAppUrl()
                ? ['domain' => Modularity::getAdminAppHost()]
                : ['prefix' => Modularity::getAdminUrlPrefix(), 'domain' => Modularity::getAppUrl()]
            ),

        ];
    }

    public function webMiddlewares(): array
    {
        return [
            ...['web'],
            ...$this->defaultMiddlewares,
        ];
    }

    public function webPanelMiddlewares(): array
    {
        return [
            ...['web.auth'],
            ...$this->defaultMiddlewares,
            ...['modularity.panel'],
        ];
    }

    public function apiMiddlewares(): array
    {
        return [
            ...['api'],
            ...$this->defaultMiddlewares,
        ];
    }

    public function apiPanelMiddlewares(): array
    {
        return [
            ...['api.auth'],
            ...$this->defaultMiddlewares,
        ];
    }

    public function defaultMiddlewares(): array
    {
        return $this->defaultMiddlewares;
    }

    public function defaultPanelMiddlewares(): array
    {
        return [
            'modularity.panel',
        ];
    }

    public function generateRouteMiddlewares()
    {

        Route::aliasMiddleware('modularity.auth', AuthenticateMiddleware::class);
        Route::aliasMiddleware('modularity.guest', RedirectIfAuthenticatedMiddleware::class);

        $authGuardName = Modularity::getAuthGuardName();
        Route::middlewareGroup('web.auth', [
            'web',
            'modularity.auth:' . $authGuardName,
            // 'auth',
        ]);
        Route::middlewareGroup('api.auth', [
            'api',
            'throttle:api',
            'modularity.auth:' . $authGuardName,
            // 'auth',
        ]);

        Route::aliasMiddleware('modularity.log', LogMiddleware::class);

        Route::aliasMiddleware('language', LanguageMiddleware::class);
        Route::aliasMiddleware('impersonate', ImpersonateMiddleware::class);
        Route::aliasMiddleware('navigation', NavigationMiddleware::class);

        Route::middlewareGroup('modularity.core', [
            'impersonate',
            'language',
            'navigation',
            'inertia.middleware',
        ]);

        Route::aliasMiddleware('authorization', AuthorizationMiddleware::class);
        Route::aliasMiddleware('company_registration', CompanyRegistrationMiddleware::class);
        Route::aliasMiddleware('modularity.redirector', \Unusualify\Modularity\Http\Middleware\RedirectorMiddleware::class);

        Route::middlewareGroup('modularity.panel', [
            // 'modularity.core',
            'authorization',
            'company_registration',
            'modularity.redirector',
        ]);

        // Optional Middlewares for features
        Route::aliasMiddleware('hostable', HostableMiddleware::class);

    }

    /**
     * Get API prefix
     */
    public function getApiPrefix(): string
    {
        return modularityConfig('api.prefix', 'api/v1');
    }

    /**
     * Get API domain
     */
    public function getApiDomain(): ?string
    {
        return modularityConfig('api.domain');
    }

    /**
     * Get API middlewares
     */
    public function getApiMiddlewares(): array
    {
        return array_values(array_unique(modularityConfig('api.middlewares', [
            'language',
            'api',
            'throttle:api',
        ])));
    }

    /**
     * Get public API middlewares
     */
    public function getPublicApiMiddlewares(): array
    {
        return array_values(array_unique(array_merge(modularityConfig('api.public_middlewares', []), $this->getApiMiddlewares())));
    }

    public function getApiAuthMiddlewares(): array
    {
        return array_values(array_unique(array_merge(modularityConfig('api.auth_middlewares', [
            'auth:sanctum',
        ]), $this->getApiMiddlewares())));
    }

    /**
     * Get API group options
     */
    public function getApiGroupOptions(): array
    {
        return [
            'as' => 'api.',
            'prefix' => $this->getApiPrefix(),
            'domain' => $this->getApiDomain(),
            // 'middleware' => $this->getApiMiddlewares(),
        ];
    }

    public function getAuthApiGroupOptions(): array
    {
        return array_merge($this->getApiGroupOptions(), [
            'middleware' => $this->getApiAuthMiddlewares(),
        ]);
    }

    public function getPublicApiGroupOptions(): array
    {
        return array_merge($this->getApiGroupOptions(), [
            'as' => 'api.public.',
            'prefix' => $this->getApiPrefix() . '/public',
            'middleware' => $this->getPublicApiMiddlewares(),
        ]);
    }

    public function getCustomApiRoutes(): array
    {
        return [
            'bulk',
            'export',
            'import',
            'search',
            'filters',
            'meta',
        ];
    }

    public function getApiRoutes(): array
    {
        return array_merge([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);
    }

    /**
     * Register routes
     *
     * @param mixed $router
     * @param array $groupOptions
     * @param array $middlewares
     * @param string $namespace
     * @param string $routesFile
     * @param bool $instant
     */
    public function registerRoutes(
        $router,
        $groupOptions,
        $middlewares,
        $namespace,
        $routesFile,
        $instant = false
    ): void {
        $callback = function () use ($router, $groupOptions, $middlewares, $namespace, $routesFile) {
            if (file_exists($routesFile)) {
                $hostRoutes = function ($router) use (
                    $middlewares,
                    $namespace,
                    $routesFile
                ) {
                    $router->group(
                        [
                            'namespace' => $namespace,
                            'middleware' => $middlewares,
                        ],
                        function () use ($routesFile) {
                            require $routesFile;
                        }
                    );
                };

                $router->group(
                    $groupOptions + [
                        // 'domain' => modularityConfig('app_url', env('APP_URL')),
                    ],
                    $hostRoutes
                );

            } else {

            }
        };

        $callback();

        // if ($instant) {
        //     // For some reasone the afterResolving does not work for the core routes.
        //     // In other cases it is important to use the afterResolving because the routes are otherwise registered too
        //     // early.
        //     $callback();
        // } else {
        //     FacadesUnusualRoutes::resolved($callback);
        // }
    }

    /**
     * Register module routes with shared logic for admin and front routes.
     *
     * @param mixed $module
     * @param string $type 'admin' or 'front'
     */
    public function registerModuleRoutes($module, array $options, string $type): void
    {
        $config = $module->getConfig();
        $moduleName = $config['name'] ?? $module->getName();

        if (! $moduleName) {
            return;
        }

        $pr = $module->getParentRoute();
        $has_system_prefix = $module->hasSystemPrefix();
        $system_prefix = $has_system_prefix ? systemUrlPrefix() . '/' : '';
        $system_route_name = $has_system_prefix ? systemRouteNamePrefix() : '';

        $parentStudlyName = studlyName($moduleName);
        $parentCamelName = camelCase($moduleName);
        $parentKebabName = kebabCase($moduleName);
        $parentSnakeName = snakeCase($moduleName);

        $parentUrlSegment = $config['url'] ?? $pr['url'] ?? pluralize($parentKebabName);

        $routes = $module->getRouteConfigs(valid: true);
        if (! is_array($routes)) {
            return;
        }

        // Fix route precedence - define parent route last
        usort($routes, fn ($i, $j) => (isset($i['parent']) || isset($j['parent']))
                ? ((isset($i['parent']) && $i['parent']) ?: false)
                : false
        );

        foreach ($routes as $key => $item) {
            // Skip if front routes are required but not enabled
            if ($type === 'front') {
                $hasFrontRoutes = $item['has_front_routes'] ?? false;
                if (! $hasFrontRoutes) {
                    continue;
                }
            }

            // Skip if API routes are required but not enabled
            if ($type === 'api') {
                $hasApiRoutes = $item['has_api_routes'] ?? false;
                if (! $hasApiRoutes) {
                    continue;
                }
            }

            if (! isset($item['name'])) {
                continue;
            }

            $middlewares = $type === 'admin' ? $module->getRouteMiddlewareAliases($item['name']) : [];
            $isSingleton = $module->isSingleton($item['name']);

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

            // Handle belongs relationships (admin only)
            if ($type === 'admin' && isset($item['belongs']) && $item['belongs']) {
                $this->registerBelongsRelationships(
                    $module,
                    $item,
                    $parentUrlSegment,
                    $parentSnakeName,
                    $routeUrlSegment,
                    $itemSnakeName,
                    $controllerName,
                    $parameters
                );
            }

            // Handle parent route logic
            if (($isNotParent = ! (isset($item['parent']) && $item['parent'])) || $parentUrlSegment !== $routeUrlSegment) {
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

            if ($type === 'api') {
                $groupsStack = Route::getGroupStack();
                $lastGroup = array_pop($groupsStack);
                $namespace = $lastGroup['namespace'] ?? null;
                $controllerNamespace = concatenate_namespace($namespace, $controllerName);

                if (! @class_exists($controllerNamespace) || ! is_subclass_of($controllerNamespace, \Unusualify\Modularity\Http\Controllers\ApiController::class)) {
                    continue;
                }
            }

            // Register routes based on type
            $this->registerRouteGroup(
                $type,
                $middlewares,
                $prefixes,
                $isSingleton,
                $controllerName,
                $routeUrlSegment,
                $itemStudlyName,
                $resourceOptionsAs,
                $resourceOptions,
                $parameters,
                $item
            );
        }
    }

    /**
     * Register belongs relationships for admin routes.
     *
     * @param mixed $module
     */
    private function registerBelongsRelationships(
        $module,
        array $item,
        string $parentUrlSegment,
        string $parentSnakeName,
        string $routeUrlSegment,
        string $itemSnakeName,
        string $controllerName,
        array $parameters
    ): void {
        foreach ($item['belongs'] as $key => $belong) {
            $belongRoute = $module->getRouteConfigs($belong);
            if ($belongRoute) {
                $belongRouteName = $belongRoute['route_name'] ?? snakeCase($belongRoute['name']);
                $belongRouteUrl = $belongRoute['url'] ?? pluralize(kebabCase($belongRoute['name']));

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
                    $resourceRegistrar->only(['index', 'create', 'store']);
                });
            }
        }
    }

    /**
     * Register route group based on type.
     */
    private function registerRouteGroup(
        string $type,
        array $middlewares,
        array $prefixes,
        bool $isSingleton,
        string $controllerName,
        string $routeUrlSegment,
        string $itemStudlyName,
        array $resourceOptionsAs,
        array $resourceOptions,
        array $parameters,
        array $item = []
    ): void {
        // Handle API routes with public/authenticated separation
        if ($type === 'api' && isset($item['public_api_routes']) && is_array($item['public_api_routes'])) {
            $publicRoutes = $item['public_api_routes'];
            $apiRoutes = $this->getApiRoutes();
            $customRoutes = $this->getCustomApiRoutes();
            $customPublicRoutes = [];
            $customAuthenticatedRoutes = [];

            if (in_array('index', $publicRoutes)) {
                $customPublicRoutes = array_values(array_intersect($customRoutes, ['search', 'filters', 'meta']));
            }

            $authenticatedRoutes = array_values(array_diff($apiRoutes, $publicRoutes));
            $customAuthenticatedRoutes = array_values(array_diff($customRoutes, $customPublicRoutes));

            // Register public routes if any
            if (! empty($publicRoutes)) {
                $this->registerApiRouteGroup(
                    $this->getPublicApiMiddlewares(),
                    $prefixes,
                    $isSingleton,
                    $controllerName,
                    $routeUrlSegment,
                    $itemStudlyName,
                    $resourceOptionsAs,
                    $resourceOptions,
                    $parameters,
                    $publicRoutes,
                    $customPublicRoutes
                );
            }

            // Register authenticated routes if any
            if (! empty($authenticatedRoutes)) {
                $this->registerApiRouteGroup(
                    $this->getApiAuthMiddlewares(),
                    $prefixes,
                    $isSingleton,
                    $controllerName,
                    $routeUrlSegment,
                    $itemStudlyName,
                    $resourceOptionsAs,
                    $resourceOptions,
                    $parameters,
                    $authenticatedRoutes,
                    $customAuthenticatedRoutes
                );
            }

        } else {
            // Standard route registration
            $routeGroup = match ($type) {
                'admin' => Route::middleware($middlewares)->prefix(implode('/', $prefixes)),
                'api' => Route::middleware($middlewares)->prefix(implode('/', $prefixes)),
                default => Route::prefix(implode('/', $prefixes))
            };

            $routeGroup->group(function () use (
                $type,
                $isSingleton,
                $controllerName,
                $routeUrlSegment,
                $itemStudlyName,
                $resourceOptionsAs,
                $resourceOptions,
                $parameters
            ) {
                if ($isSingleton) {
                    Route::singleton($routeUrlSegment, $controllerName, $resourceOptions);
                } else {
                    // Add additional routes based on type
                    if ($type === 'admin') {
                        Route::additionalRoutes($routeUrlSegment, $itemStudlyName, [
                            'as' => implode('.', $resourceOptionsAs),
                        ]);
                    } elseif ($type === 'api') {
                        Route::apiAdditionalRoutes($routeUrlSegment, $itemStudlyName, [
                            'as' => implode('.', $resourceOptionsAs),
                        ]);
                    }

                    // Configure resource options based on type
                    $finalResourceOptions = match ($type) {
                        'front' => $resourceOptions + ['only' => ['index', 'create', 'store', 'show']],
                        'api' => $resourceOptions + ['only' => ['index', 'store', 'show', 'update', 'destroy']],
                        default => $resourceOptions
                    };

                    $routeMethod = $type === 'api' ? 'apiResource' : 'resource';
                    Route::$routeMethod($routeUrlSegment, $controllerName, $finalResourceOptions)
                        ->parameters($parameters);
                }
            });
        }
    }

    /**
     * Register API route group with specific middlewares and allowed routes.
     */
    private function registerApiRouteGroup(
        array $middlewares,
        array $prefixes,
        bool $isSingleton,
        string $controllerName,
        string $routeUrlSegment,
        string $itemStudlyName,
        array $resourceOptionsAs,
        array $resourceOptions,
        array $parameters,
        array $allowedRoutes,
        array $customRoutes
    ): void {
        Route::middleware($middlewares)
            ->prefix(implode('/', $prefixes))
            ->group(function () use (
                $isSingleton,
                $controllerName,
                $routeUrlSegment,
                $itemStudlyName,
                $resourceOptionsAs,
                $resourceOptions,
                $parameters,
                $allowedRoutes,
                $customRoutes
            ) {
                if ($isSingleton) {
                    Route::singleton($routeUrlSegment, $controllerName, $resourceOptions);
                } else {
                    // Add additional routes for API
                    Route::apiAdditionalRoutes($routeUrlSegment, $itemStudlyName, [
                        'as' => implode('.', $resourceOptionsAs),
                    ], $customRoutes);

                    $finalResourceOptions = $resourceOptions + ['only' => $allowedRoutes];
                    Route::apiResource($routeUrlSegment, $controllerName, $finalResourceOptions)
                        ->parameters($parameters);
                }
            });
    }
}
