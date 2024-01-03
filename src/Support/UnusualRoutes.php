<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Http\Middleware\{
    AuthenticateMiddleware,
    CompanyRegistrationMiddleware,
    LanguageMiddleware,
    ImpersonateMiddleware,
    NavigationMiddleware,
    RedirectIfAuthenticatedMiddleware,
    AuthorizationMiddleware
};
class UnusualRoutes
{

    public $counter = 1;

    private $defaultMiddlewares = [
        'unusual.core'
        // 'language',
        // 'impersonate',

        // 'unusual_auth:unusual_users',
        // 'auth',
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
                        // 'domain' => config(unusualBaseKey() . '.app_url', env('APP_URL')),
                    ],
                    $hostRoutes
                );

            } else{
                $routesFile;
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
        if (($patterns = unusualConfig('route_patterns')) != null) {
            if (is_array($patterns)) {
                foreach ($patterns as $label => $pattern) {
                    Route::pattern($label, $pattern);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function groupOptions(): array
    {
        return [
            'as'            => adminRouteNamePrefix() . '.',
            'prefix'        => rtrim(ltrim(unusualConfig('admin_app_path'), '/'), '/'),
        ];
    }

    public function webMiddlewares(): array
    {
        return [
            ...['web.auth'],
            ...$this->defaultMiddlewares,
            ...['unusual.panel']
        ];
    }

    public function apiMiddlewares(): array
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

    function generateRouteMiddlewares()  {

        Route::aliasMiddleware('unusual_auth', AuthenticateMiddleware::class);
        Route::aliasMiddleware('unusual_guest', RedirectIfAuthenticatedMiddleware::class);

        Route::middlewareGroup('web.auth', [
            'web',
            'unusual_auth:unusual_users',
            'auth',
        ]);
        Route::middlewareGroup('api.auth', [
            'api',
            'unusual_auth:unusual_users',
            'auth',
        ]);

        Route::aliasMiddleware('language', LanguageMiddleware::class);
        Route::aliasMiddleware('impersonate', ImpersonateMiddleware::class);
        Route::middlewareGroup('unusual.core', [
            'language',
            'impersonate'
        ]);

        Route::aliasMiddleware('navigation', NavigationMiddleware::class);
        Route::aliasMiddleware('authorization', AuthorizationMiddleware::class);
        Route::aliasMiddleware('company_registration', CompanyRegistrationMiddleware::class);
        Route::middlewareGroup('unusual.panel', [
            'navigation',
            'authorization',
            'company_registration'
        ]);
    }

}
