<?php

namespace Unusualify\Modularous\Support;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\ApiController;
use Unusualify\Modularous\Http\Middleware\AuthenticateMiddleware;
use Unusualify\Modularous\Http\Middleware\AuthorizationMiddleware;
use Unusualify\Modularous\Http\Middleware\CompanyRegistrationMiddleware;
use Unusualify\Modularous\Http\Middleware\HostableMiddleware;
use Unusualify\Modularous\Http\Middleware\ImpersonateMiddleware;
use Unusualify\Modularous\Http\Middleware\LanguageMiddleware;
use Unusualify\Modularous\Http\Middleware\LoadLocalizedConfig;
use Unusualify\Modularous\Http\Middleware\LogMiddleware;
use Unusualify\Modularous\Http\Middleware\NavigationMiddleware;
use Unusualify\Modularous\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Unusualify\Modularous\Http\Middleware\RedirectorMiddleware;
use Unusualify\Modularous\Http\Middleware\UtmMiddleware;
use Unusualify\Modularous\Module;

class ModularousRoutes
{
    private static array $dynamicDefaultMiddlewares = [];

    private static array $dynamicPanelMiddlewares = [];

    private array $defaultMiddlewares = [
        'modularous.log',
        'modularous.core',
    ];

    public function addDefaultMiddleware(string $middleware): void
    {
        $middleware = trim($middleware);
        if ($middleware === '') {
            return;
        }

        self::$dynamicDefaultMiddlewares[] = $middleware;
        self::$dynamicDefaultMiddlewares = array_values(array_unique(self::$dynamicDefaultMiddlewares));
    }

    public function addPanelMiddleware(string $middleware): void
    {
        $middleware = trim($middleware);
        if ($middleware === '') {
            return;
        }

        self::$dynamicPanelMiddlewares[] = $middleware;
        self::$dynamicPanelMiddlewares = array_values(array_unique(self::$dynamicPanelMiddlewares));
    }

    public function addDefaultMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                $this->addDefaultMiddleware($middleware);
            }
        }
    }

    public function addPanelMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                $this->addPanelMiddleware($middleware);
            }
        }
    }

    public function configureRoutePatterns(): void
    {
        if (($patterns = modularousConfig('route_patterns')) != null) {
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
            'as' => Modularous::getAdminRouteNamePrefix() . '.',
            ...(Modularous::hasAdminAppUrl()
                ? ['domain' => Modularous::getAdminAppHost()]
                : ['prefix' => Modularous::getAdminUrlPrefix(), 'domain' => Modularous::getAppUrl()]
            ),

        ];
    }

    public function webMiddlewares(): array
    {
        return array_values(array_unique([
            'web',
            ...$this->defaultMiddlewares(),
        ]));
    }

    public function webPanelMiddlewares(): array
    {
        return array_values(array_unique([
            'web.auth',
            ...$this->defaultMiddlewares(),
            ...$this->defaultPanelMiddlewares(),
        ]));
    }

    public function apiMiddlewares(): array
    {
        return array_values(array_unique([
            'api',
            ...$this->defaultMiddlewares(),
        ]));
    }

    public function apiPanelMiddlewares(): array
    {
        return array_values(array_unique([
            'api.auth',
            ...$this->defaultMiddlewares(),
            ...$this->defaultPanelMiddlewares(),
        ]));
    }

    public function defaultMiddlewares(): array
    {
        return array_values(array_unique([
            ...$this->defaultMiddlewares,
            ...self::$dynamicDefaultMiddlewares,
        ]));
    }

    public function defaultPanelMiddlewares(): array
    {
        return array_values(array_unique([
            'modularous.panel',
            ...self::$dynamicPanelMiddlewares,
        ]));
    }

    public function generateRouteMiddlewares(): void
    {

        Route::aliasMiddleware('modularous.auth', AuthenticateMiddleware::class);
        Route::aliasMiddleware('modularous.guest', RedirectIfAuthenticatedMiddleware::class);

        $authGuardName = Modularous::getAuthGuardName();
        Route::middlewareGroup('web.auth', [
            'web',
            'modularous.auth:' . $authGuardName,
            // 'auth',
        ]);
        Route::middlewareGroup('api.auth', [
            'api',
            'throttle:api',
            'modularous.auth:' . $authGuardName,
            // 'auth',
        ]);

        Route::aliasMiddleware('modularous.utm', UtmMiddleware::class);
        Route::aliasMiddleware('modularous.log', LogMiddleware::class);

        Route::aliasMiddleware('modularous.language', LanguageMiddleware::class);
        Route::aliasMiddleware('modularous.impersonate', ImpersonateMiddleware::class);
        Route::aliasMiddleware('modularous.loadLocalizedConfig', LoadLocalizedConfig::class);
        Route::aliasMiddleware('modularous.navigation', NavigationMiddleware::class);
        Route::middlewareGroup('modularous.core', [
            'modularous.utm',
            'modularous.impersonate',
            'modularous.language',
            'modularous.loadLocalizedConfig',
            'modularous.navigation',
            'inertia.middleware',
        ]);

        Route::aliasMiddleware('authorization', AuthorizationMiddleware::class);
        Route::aliasMiddleware('modularous.company.registration', CompanyRegistrationMiddleware::class);
        Route::aliasMiddleware('modularous.redirector', RedirectorMiddleware::class);

        Route::middlewareGroup('modularous.panel', [
            // 'modularous.core',
            'authorization',
            'modularous.company.registration',
            'modularous.redirector',
        ]);

        // Optional Middlewares for features
        Route::aliasMiddleware('hostable', HostableMiddleware::class);

        /*
        * Define Spatie Laravel-Permission Middleware (https://github.com/spatie/laravel-permission)
        * See a typo? Note that since v6 the 'Middleware' namespace is singular. Prior to v6 it was 'Middlewares'. Time to upgrade your implementation!
        */
        Route::aliasMiddleware('role', RoleMiddleware::class);
        Route::aliasMiddleware('permission', PermissionMiddleware::class);
        Route::aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);

    }

    /**
     * Get API prefix
     */
    public function getApiPrefix(): string
    {
        return modularousConfig('api.prefix', 'api/v1');
    }

    /**
     * Get API domain
     */
    public function getApiDomain(): ?string
    {
        return modularousConfig('api.domain');
    }

    /**
     * Get API middlewares
     */
    public function getApiMiddlewares(): array
    {
        return array_values(array_unique(modularousConfig('api.middlewares', [
            'modularous.language',
            'api',
            'throttle:api',
        ])));
    }

    /**
     * Get public API middlewares
     */
    public function getPublicApiMiddlewares(): array
    {
        return array_values(array_unique(array_merge(modularousConfig('api.public_middlewares', []), $this->getApiMiddlewares())));
    }

    public function getApiAuthMiddlewares(): array
    {
        return array_values(array_unique(array_merge(modularousConfig('api.auth_middlewares', [
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
        return array_values(array_unique(array_merge(
            [
                'index',
                'store',
                'show',
                'update',
                'destroy',
            ],
            modularousConfig('api.routes', [])
        )));
    }

    /**
     * Register routes from a file within a group.
     *
     * @param Router $router
     */
    public function registerRoutes(
        $router,
        array $groupOptions,
        array $middlewares,
        string $namespace,
        string $routesFile,
        bool $instant = false
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
                        // 'domain' => modularousConfig('app_url', env('APP_URL')),
                    ],
                    $hostRoutes
                );

            } else {
                // Routes file not found - skip registration
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
     * Register module routes with shared logic for admin, front and api routes.
     *
     * @param string $type 'admin', 'front' or 'api'
     */
    public function registerModuleRoutes(Module $module, array $options, string $type): void
    {
        // $config = $module->getConfig();
        $config = $module->getRawConfig();

        $moduleName = $config['name'] ?? $module->getName();

        if (! $moduleName) {
            return;
        }

        $pr = $module->getParentRoute();
        $has_system_prefix = $module->hasSystemPrefix();
        $system_prefix = $has_system_prefix ? systemUrlPrefix() . '/' : '';
        $system_route_name = $has_system_prefix ? systemRouteNamePrefix() : '';

        $parentKebabName = kebabCase($moduleName);
        $parentSnakeName = snakeCase($moduleName);

        $parentUrlSegment = $config['url'] ?? $pr['url'] ?? pluralize($parentKebabName);

        $routes = $module->getRawRouteConfigs(valid: true);

        if (! is_array($routes)) {
            return;
        }

        // Fix route precedence - define parent route last
        usort($routes, function ($i, $j) {
            $iParent = isset($i['parent']) && $i['parent'];
            $jParent = isset($j['parent']) && $j['parent'];

            if ($iParent === $jParent) {
                return 0;
            }

            return $iParent ? 1 : -1;
        });

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

                if (! @class_exists($controllerNamespace) || ! is_subclass_of($controllerNamespace, ApiController::class)) {
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
     */
    private function registerBelongsRelationships(
        Module $module,
        array $item,
        string $parentUrlSegment,
        string $parentSnakeName,
        string $routeUrlSegment,
        string $itemSnakeName,
        string $controllerName,
        array $parameters
    ): void {
        foreach ($item['belongs'] as $key => $belong) {
            $belongRoute = $module->getRawRouteConfigs($belong);
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
                // Add additional routes based on type
                if ($type === 'admin') {
                    Route::additionalRoutes($routeUrlSegment, $itemStudlyName, [
                        'as' => implode('.', $resourceOptionsAs),
                    ]);
                } elseif ($type === 'api' && ! $isSingleton) {
                    Route::apiAdditionalRoutes($routeUrlSegment, $itemStudlyName, [
                        'as' => implode('.', $resourceOptionsAs),
                    ]);
                }

                if ($isSingleton) {
                    Route::singleton($routeUrlSegment, $controllerName, $resourceOptions);
                } else {

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
