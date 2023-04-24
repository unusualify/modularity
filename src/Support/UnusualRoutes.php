<?php

namespace OoBook\CRM\Base\Support;

use OoBook\CRM\Base\Facades\UnusualRoutes as FacadesUnusualRoutes;
use Illuminate\Support\Facades\Route;

class UnusualRoutes
{

    public $counter = 1;

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
                        'domain' => config(getUnusualBaseKey() . '.app_url', env('APP_URL')),
                    ],
                    $hostRoutes
                );

            }else{
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
        if (($patterns = config(getUnusualBaseKey() . '.route_patterns')) != null) {
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
            // 'as'            => config('twill.admin_route_name_prefix', 'admin.'),
            'middleware'    => [config(getUnusualBaseKey() . '.admin_middleware_group', 'web')],
            // 'prefix'        => rtrim(ltrim(config('twill.admin_app_path'), '/'), '/'),
        ];
    }

    public function moduleGroupOptions(): array
    {
        return array_merge([

        ], $this->groupOptions());
    }

    public function internalGroupOptions(): array
    {
        return array_merge([
            // 'as' => strtolower( config(getUnusualBaseKey() . '.name') ).".",
        ], $this->groupOptions());
    }

    public function middlewares($middleware = null): array
    {
        if (is_array($middleware)) {
            return $middleware;
        }

        $middleware = [
            // 'twill_auth:twill_users',
            // 'impersonate',
            // 'validateBackHistory',
            // 'localization',
            'auth'
        ];

        // if ($this->supportSubdomainRouting()) {
        //     array_unshift($middleware, 'supportSubdomainRouting');
        // }

        return $middleware;
    }

}
