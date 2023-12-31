<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Container\Util;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Unusualify\Modularity\Http\Controllers\GlideController;
use Unusualify\Modularity\Facades\UnusualRoutes;
use Nwidart\Modules\Facades\Module;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Middleware\{
    AuthenticateMiddleware,
    CompanyRegistrationMiddleware,
    LanguageMiddleware,
    ImpersonateMiddleware,
    NavigationMiddleware,
    RedirectIfAuthenticatedMiddleware,
    AuthorizationMiddleware
};

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

        $this->registerRouteMiddlewares($this->app->get('router'));

        parent::boot();
    }

    /**
     * @param Router $router
     * @return void
     */
    public function map(Router $router)
    {
        UnusualRoutes::configureRoutePatterns();

        $this->mapBaseRoutes(
            $router,
            UnusualRoutes::internalGroupOptions(),
            UnusualRoutes::middlewares(),
        );

        $this->mapModuleRoutes(
            $router,
            UnusualRoutes::moduleGroupOptions(),
            UnusualRoutes::middlewares()
        );
    }

    private function mapBaseRoutes(
        $router,
        $groupOptions,
        $middlewares,
        $supportSubdomainRouting = false
    ) {
        $router->group(
            $groupOptions + [
                'namespace' => $this->namespace,
            ],
            function ($router) use ($middlewares, $supportSubdomainRouting) {

                $router->group(
                    [
                        // 'middleware' => $supportSubdomainRouting ? ['supportSubdomainRouting'] : [],
                        'middleware' => [
                            'language',
                            ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : [])
                        ],
                    ],
                    function ($router) {
                        require __DIR__ . '/../Routes/auth.php';
                    }
                );

                $router->group(
                    [
                        'prefix' => 'api',
                        // 'middleware' => $supportSubdomainRouting ? ['supportSubdomainRouting'] : [],
                        'middleware' => [
                            'unusual_auth:unusual_users',
                            'language',
                            ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : [])
                        ],
                    ],
                    function ($router) {
                        require __DIR__ . '/../Routes/api.php';
                    }
                );
                // internal routes web.php
                $router->group(
                    [
                        // 'domain' => config(unusualBaseKey() . '.admin_app_url'),
                    ],
                    function ($router) use (
                        $middlewares,
                        $supportSubdomainRouting
                    ) {

                        $router->group(
                            [
                                'middleware' => $middlewares
                            ],
                            function ($router) {
                                require __DIR__ . '/../Routes/web.php';
                            }
                        );

                    }
                );

                // if ($supportSubdomainRouting) {
                //     $router->group(
                //         [
                //             'domain' => config(unusualBaseKey() . '.admin_app_subdomain', 'admin') .
                //             '.{subdomain}.' .
                //             config('app.url'),
                //         ],
                //         $internalRoutes
                //     );
                // }
            }
        );

        if (
            config(unusualBaseKey() . '.media_library.image_service') ===
            'Unusualify\Modularity\Services\MediaLibrary\Glide'
        ) {
            $router
                ->get(
                    '/' . config(unusualBaseKey() . '.glide.base_path') . '/{path}',
                    GlideController::class
                )
                ->where('path', '.*');
        }
    }

    private function mapModuleRoutes(
        $router,
        $groupOptions,
        $middlewares
    ) {
        // foreach(Module::allEnabled() as $module){
        foreach(Modularity::allEnabled() as $module){
            UnusualRoutes::registerRoutes(
                $router,
                $groupOptions,
                $middlewares,
                config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getPath()."/Routes/web.php",
                true
            );
        }
    }

    /**
     * Register Route middleware.
     *
     * @param Router $router
     * @return void
     */
    private function registerRouteMiddlewares(Router $router)
    {
        // Route::aliasMiddleware(
        //     'supportSubdomainRouting',
        //     SupportSubdomainRouting::class
        // );
        // Route::aliasMiddleware('twill_auth', Authenticate::class);
        // Route::aliasMiddleware('twill_guest', RedirectIfAuthenticated::class);
        // Route::aliasMiddleware(
            //     'validateBackHistory',
            //     ValidateBackHistory::class
            // );

        Route::aliasMiddleware('unusual_auth', AuthenticateMiddleware::class);
        Route::aliasMiddleware('unusual_guest', RedirectIfAuthenticatedMiddleware::class);

        Route::aliasMiddleware('impersonate', ImpersonateMiddleware::class);
        Route::aliasMiddleware('language', LanguageMiddleware::class);
        Route::aliasMiddleware('navigation', NavigationMiddleware::class);
        Route::aliasMiddleware('company_registration', CompanyRegistrationMiddleware::class);
        Route::aliasMiddleware('authorization', AuthorizationMiddleware::class);

        // Route::aliasMiddleware('role', \Spatie\Permission\Middlewares\RoleMiddleware::class);
        // Route::aliasMiddleware('permission', \Spatie\Permission\Middlewares\PermissionMiddleware::class);
        // Route::aliasMiddleware('role_or_permission', \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class);

        // Route::aliasMiddleware('teams_permission', TeamsPermissionMiddleware::class);

    }

    /**
     * Registers Route macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        // Route::macro('moduleShowWithPreview', function (
        //     $moduleName,
        //     $routePrefix = null,
        //     $controllerName = null
        // ) {
        //     if ($routePrefix === null) {
        //         $routePrefix = $moduleName;
        //     }

        //     if ($controllerName === null) {
        //         $controllerName = ucfirst(Str::plural($moduleName));
        //     }

        //     $routePrefix = empty($routePrefix)
        //     ? '/'
        //     : (Str::startsWith($routePrefix, '/')
        //         ? $routePrefix
        //         : '/' . $routePrefix);
        //     $routePrefix = Str::endsWith($routePrefix, '/')
        //     ? $routePrefix
        //     : $routePrefix . '/';

        //     Route::name($moduleName . '.show')->get(
        //         $routePrefix . '{slug}',
        //         $controllerName . 'Controller@show'
        //     );
        //     Route::name($moduleName . '.preview')
        //         ->get(
        //             '/admin-preview' . $routePrefix . '{slug}',
        //             $controllerName . 'Controller@show'
        //         )
        //         ->middleware(['web', 'twill_auth:twill_users', 'can:list']);
        // });

        // Route::macro('module', function (
        //     $slug,
        //     $options = [],
        //     $resource_options = [],
        //     $resource = true
        // ) {
        //     $slugs = explode('.', $slug);
        //     $prefixSlug = str_replace('.', '/', $slug);
        //     $_slug = Arr::last($slugs);
        //     $className = implode(
        //         '',
        //         array_map(function ($s) {
        //             return ucfirst(Str::singular($s));
        //         }, $slugs)
        //     );

        //     $customRoutes = $defaults = [
        //         'reorder',
        //         'publish',
        //         'bulkPublish',
        //         'browser',
        //         'feature',
        //         'bulkFeature',
        //         'tags',
        //         'preview',
        //         'restore',
        //         'bulkRestore',
        //         'forceDelete',
        //         'bulkForceDelete',
        //         'bulkDelete',
        //         'restoreRevision',
        //         'duplicate',
        //     ];

        //     if (isset($options['only'])) {
        //         $customRoutes = array_intersect(
        //             $defaults,
        //             (array) $options['only']
        //         );
        //     } elseif (isset($options['except'])) {
        //         $customRoutes = array_diff(
        //             $defaults,
        //             (array) $options['except']
        //         );
        //     }

        //     $lastRouteGroupName = RouteServiceProvider::getLastRouteGroupName();

        //     $groupPrefix = RouteServiceProvider::getGroupPrefix();

        //     // Check if name will be a duplicate, and prevent if needed/allowed
        //     if (RouteServiceProvider::shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)) {
        //         $customRoutePrefix = "{$groupPrefix}.{$slug}";
        //         $resourceCustomGroupPrefix = "{$groupPrefix}.";
        //     } else {
        //         $customRoutePrefix = $slug;

        //         // Prevent Laravel from generating route names with duplication
        //         $resourceCustomGroupPrefix = '';
        //     }

        //     foreach ($customRoutes as $route) {
        //         $routeSlug = "{$prefixSlug}/{$route}";
        //         $mapping = [
        //             'as' => $customRoutePrefix . ".{$route}",
        //             'uses' => "{$className}Controller@{$route}",
        //         ];

        //         if (in_array($route, ['browser', 'tags'])) {
        //             Route::get($routeSlug, $mapping);
        //         }

        //         if (in_array($route, ['restoreRevision'])) {
        //             Route::get($routeSlug . '/{id}', $mapping);
        //         }

        //         if (
        //             in_array($route, [
        //                 'publish',
        //                 'feature',
        //                 'restore',
        //                 'forceDelete',
        //             ])
        //         ) {
        //             Route::put($routeSlug, $mapping);
        //         }

        //         if (in_array($route, ['duplicate'])) {
        //             Route::put($routeSlug . '/{id}', $mapping);
        //         }

        //         if (in_array($route, ['preview'])) {
        //             Route::put($routeSlug . '/{id}', $mapping);
        //         }

        //         if (
        //             in_array($route, [
        //                 'reorder',
        //                 'bulkPublish',
        //                 'bulkFeature',
        //                 'bulkDelete',
        //                 'bulkRestore',
        //                 'bulkForceDelete',
        //             ])
        //         ) {
        //             Route::post($routeSlug, $mapping);
        //         }
        //     }

        //     if ($resource) {
        //         Route::group(
        //             ['as' => $resourceCustomGroupPrefix],
        //             function () use ($slug, $className, $resource_options) {
        //                 Route::resource(
        //                     $slug,
        //                     "{$className}Controller",
        //                     $resource_options
        //                 );
        //             }
        //         );
        //     }
        // });

        // Route::macro('singleton', function (
        //     $slug,
        //     $options = [],
        //     $resource_options = [],
        //     $resource = true
        // ) {
        //     $pluralSlug = Str::plural($slug);
        //     $modelName = Str::studly($slug);

        //     Route::module($pluralSlug, $options, $resource_options, $resource);

        //     $lastRouteGroupName = RouteServiceProvider::getLastRouteGroupName();

        //     $groupPrefix = RouteServiceProvider::getGroupPrefix();

        //     // Check if name will be a duplicate, and prevent if needed/allowed
        //     if (RouteServiceProvider::shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)) {
        //         $singletonRouteName = "{$groupPrefix}.{$slug}";
        //     } else {
        //         $singletonRouteName = $slug;
        //     }

        //     Route::get($slug, $modelName . 'Controller@editSingleton')->name($singletonRouteName);
        // });
    }

    /**
     *  Boot Route macros.
     *
     * @return void
     */
    protected function bootMacros()
    {
        Route::macro('configRoutes', function($config, $middlewares = [], $options = []){
            Route::middleware($middlewares)->group( function() use($config, $options){

                $pr = findParentRoute($config);

                $system_prefix = (isset($config['base_prefix']) && $config['base_prefix']) ? unusualConfig('base_prefix', 'system-settings') . '/' : '';
                $system_route_name = (isset($config['base_prefix']) && $config['base_prefix']) ? snakeCase(studlyName(unusualConfig('base_prefix', 'system-settings'))) : '';

                $parent_studly = studlyName( $config['name'] ); // UserCompany
                $parent_camel = camelCase( $config['name'] ); // userCompany
                $parent_kebab = kebabCase( $config['name'] ); // user-company
                $parent_snake = snakeCase( $config['name'] );  // user_company

                $parent_url = $pr['url'] ?? $parent_kebab;

                if( is_array( $routes = $config['routes'] ) ){

                    /**
                     * the fix of route precedence
                     * to define parent route the most lastly
                     *
                     *  */
                    usort($routes, fn($i, $j) =>
                        (isset($i['parent']) || isset($j['parent']))
                            ? ((isset($i['parent']) && $i['parent']) ?: false)
                            : false
                    );

                    foreach( $routes as $key => $item) {
                        $route_camel = camelCase( $item['name'] );
                        $route_studly = studlyName($item['name']);
                        $route_snake = snakeCase($item['name']);

                        $url = $item['url'] ?? $route_camel;
                        $controller = $route_studly.'Controller';

                        $resource_options_names = $item['route_name'] ?? $route_snake;
                        $resource_options_as = [];

                        if( $system_route_name ){
                            $resource_options_as[] = $system_route_name;
                        }

                        if(isset($sub['nested']) && $item['nested']){
                            $url =  $parent_camel . "." . $url;

                            $controller = $parent_studly . $controller;

                            $resource_options_names = ($parent['route_name'] ?? $parent_snake) . "." . $resource_options_names;

                        }else if( isset($item['parent']) && $item['parent'] ){
                            // dd(
                            //     $url,
                            //     $system_prefix,
                            //     $system_route_name,
                            //     $resource_options_as,
                            //     $resource_options_names,
                            // );
                        }else{
                            $url = $parent_url . "/" . $url;

                            $resource_options_as[] = $parent_snake;
                        }

                        $url = $system_prefix . $url;

                        $resource_options = [
                            'names' => $resource_options_names,
                            'as' => implode( '.', $resource_options_as)
                        ];

                        $resource_options_as[] = $route_snake;


                        Route::additionalRoutes($url, $route_studly, [
                            'as' => implode( '.', $resource_options_as)
                        ]);

                        Route::resource($url, $controller, $resource_options)->parameters([
                            // $url => $sub_studly,
                        ]);

                        // dd(get_defined_vars(), Route::getRoutes());


                    }
                }

            });
        });

        Route::macro('webRoutes', function ($routeFile = null, $middlewares = [], $options = []) {

            if(!$routeFile){
                $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';
                $routeFile = fileTrace($pattern);
            }
            $kebabCase  = kebabCase(getCurrentModuleName($routeFile));
            $snakeCase  = snakeCase(getCurrentModuleName($routeFile));

            $config = config( $snakeCase );

            if( !!$config )
                Route::configRoutes($config, $middlewares, $options);
            else
                dd(
                    $kebabCase,
                    $snakeCase,
                    $config
                );

        });

        Route::macro('additionalRoutes', function ($url, $routeName, $options) {

            $customRoutes = $defaults = [
                // 'reorder',
                // 'publish',
                // 'bulkPublish',
                // 'browser',
                // 'feature',
                // 'bulkFeature',
                // 'tags',
                // 'preview',
                'restore',
                // 'bulkRestore',
                'forceDelete',
                // 'bulkForceDelete',
                // 'bulkDelete',
                // 'restoreRevision',
                'duplicate',
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
                $routeSlug = "{$url}/{$customRoute}";

                $mapping = [
                    // 'as' => $customRoutePrefix . ".{$customRoute}",
                    'as' => $options['as'] . ".{$customRoute}",
                    'uses' => "{$controllerClass}@{$customRoute}",
                ];

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

        Route::macro('internalApiRoutes', function ($routeFile = null, $middlewares = []) {

            if(!$routeFile){
                $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';

                $routeFile = fileTrace($pattern);
            }

            $lowerModule  = getCurrentModuleLowerName($routeFile);
            $studlyModule = getCurrentModuleStudlyName($routeFile);

            if( empty($middlewares) )
                $middlewares = ['auth'];

            Route::middleware($middlewares)->group( function() use($lowerModule, $studlyModule){

                Route::prefix('api')
                    ->name('api.')
                    ->namespace('API')
                    ->group(function() use($lowerModule, $studlyModule){

                    Route::apiResource( $lowerModule ,$studlyModule.'Controller');
                    if( is_array( $parent = config( $lowerModule.'.parent_route' ) ) ){
                        $url = $parent['url'] ?? lowerName($parent['name']);
                        $studlyName = studlyName($parent['name']);

                        Route::apiResource($url, $studlyName.'Controller');
                    }
                    Route::prefix( $lowerModule )
                        ->name( $lowerModule.'.' )
                        ->group(function() use($lowerModule, $studlyModule){

                        if( is_array( config( $lowerModule . '.sub_routes' ))){
                            foreach( config( $lowerModule . '.sub_routes' ) as $value) {
                                $url = $value['url'] ?? lowerName($value['name']);
                                $studlyName = studlyName($value['name']);
                                $names = $value['route_name'] ?? $url;

                                Route::apiResource($url, $studlyName.'Controller', ['names' => $names]);
                            }
                        }
                    });

                });

            });
        });
    }

    public static function shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)
    {
        return ! empty($groupPrefix) && (blank($lastRouteGroupName) ||
            config(unusualBaseKey() . '.allow_duplicates_on_route_names', true) ||
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

        if (! empty(config(unusualBaseKey() . '.admin_app_path'))) {
            $groupPrefix = ltrim(
                str_replace(
                    config(unusualBaseKey() . '.admin_app_path'),
                    '',
                    $groupPrefix
                ),
                '.'
            );
        }

        return $groupPrefix;
    }
}
