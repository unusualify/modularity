<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Middleware\AuthenticateMiddleware;
use Unusualify\Modularity\Http\Middleware\AuthorizationMiddleware;
use Unusualify\Modularity\Http\Middleware\CompanyRegistrationMiddleware;
use Unusualify\Modularity\Http\Middleware\HostableMiddleware;
use Unusualify\Modularity\Http\Middleware\ImpersonateMiddleware;
use Unusualify\Modularity\Http\Middleware\LanguageMiddleware;
use Unusualify\Modularity\Http\Middleware\NavigationMiddleware;
use Unusualify\Modularity\Http\Middleware\RedirectIfAuthenticatedMiddleware;

class ModularityRoutes
{
    public $counter = 1;

    private $defaultMiddlewares = [
        'modularity.core',
    ];

    public function registerRoutes(
        $router,
        $groupOptions,
        $middlewares,
        $namespace,
        $routesFile,
        $instant = false
    ): void {
        $callback = function () use ($router, $groupOptions, $middlewares, $namespace, $routesFile) {
            // dd(
            //     $router, $groupOptions, $middlewares, $namespace, $routesFile
            // );
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
            'as' => adminRouteNamePrefix() . '.',
            ...(modularityConfig('admin_app_url')
                ? ['domain' => modularityConfig('admin_app_url')]
                : ['prefix' => adminUrlPrefix()]
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
            'modularity.auth:' . $authGuardName,
            // 'auth',
        ]);

        Route::aliasMiddleware('language', LanguageMiddleware::class);
        Route::aliasMiddleware('impersonate', ImpersonateMiddleware::class);
        Route::aliasMiddleware('navigation', NavigationMiddleware::class);

        Route::middlewareGroup('modularity.core', [
            'impersonate',
            'language',
            'navigation',
        ]);

        Route::aliasMiddleware('authorization', AuthorizationMiddleware::class);
        Route::aliasMiddleware('company_registration', CompanyRegistrationMiddleware::class);

        Route::middlewareGroup('modularity.panel', [
            // 'modularity.core',
            'authorization',
            'company_registration',
        ]);

        // Optional Middlewares for features
        Route::aliasMiddleware('hostable', HostableMiddleware::class);

    }
}
