<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Unusualify\Modularity\Http\Controllers\GlideController;
use Unusualify\Modularity\Facades\UnusualRoutes;
use Unusualify\Modularity\Facades\Modularity;
use Illuminate\Support\Str;

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

        parent::boot();
    }

    /**
     * @param Router $router
     * @return void
     */
    public function map(Router $router)
    {


        UnusualRoutes::configureRoutePatterns();

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
        $groupOptions = UnusualRoutes::groupOptions();

        $router->group(
            [
                'namespace' => $this->namespace,
            ],
            function ($router) use ($groupOptions, $supportSubdomainRouting) {
                $router->group(
                    $groupOptions,
                    function ($router)  use($supportSubdomainRouting){
                        //auth routes (login,register,forgot-password etc.)
                        $router->group(
                            [
                                'middleware' => [
                                    'web',
                                    ...UnusualRoutes::defaultMiddlewares(),
                                    ...($supportSubdomainRouting ? ['supportSubdomainRouting'] : [])
                                ],
                            ],
                            function ($router) {
                                require __DIR__ . '/../Routes/auth.php';
                            }
                        );
                        // dd(
                        //    $groupOptions
                        // );
                        $router->group(
                            [
                                'prefix' => 'api',
                                'middleware' => [
                                    ...UnusualRoutes::webMiddlewares(),
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
                                // 'domain' => unusualConfig('admin_app_url'),
                            ],
                            function ($router) use (
                                $supportSubdomainRouting
                            ) {

                                $router->group(
                                    [
                                        'middleware' => UnusualRoutes::webMiddlewares()
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
                        //             'domain' => unusualConfig('admin_app_subdomain', 'admin') .
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

        if (
            unusualConfig('media_library.image_service') ===
            'Unusualify\Modularity\Services\MediaLibrary\Glide'
        ) {
            $router
                ->get(
                    '/' . unusualConfig('glide.base_path') . '/{path}',
                    GlideController::class
                )
                ->where('path', '.*');
        }
    }

    private function mapModuleRoutes(
        $router,
        $supportSubdomainRouting = false
    ) {
        $groupOptions = UnusualRoutes::groupOptions();

        foreach(Modularity::allEnabled() as $module){
            $router->group([
                    ...$groupOptions,
                    ...[
                        'middleware' => UnusualRoutes::webMiddlewares(),
                        'namespace' => $module->getClassNamespace('Http\Controllers'),
                    ]
                ],
                function ($router) use ($module) {
                    Route::moduleRoutes($module);
                }
            );

            $_groupOptions = [
                'prefix' => $module->fullPrefix(),
                'as' => $module->fullRouteNamePrefix() . '.'
            ];
            // $_groupOptions['prefix'] = $module->fullPrefix();
            // $_groupOptions['as'] = $module->fullRouteNamePrefix() . '.';

            UnusualRoutes::registerRoutes(
                $router,
                $_groupOptions,
                ['web'], //$middlewares,
                $module->getClassNamespace('Http\Controllers'), //config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getDirectoryPath('Routes/web.php'), //$module->getPath()."/Routes/web.php",
                true
            );
            UnusualRoutes::registerRoutes(
                $router,
                $_groupOptions,
                ['api'], //$middlewares,
                $module->getClassNamespace('Http\Controllers\API'), //config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getDirectoryPath('Routes/api.php'), //$module->getPath()."/Routes/web.php",
                true
            );
            UnusualRoutes::registerRoutes(
                $router,
                [],
                ['web'], //$middlewares,
                $module->getClassNamespace('Http\Controllers\Front'), //config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getDirectoryPath('Routes/front.php'), //$module->getPath()."/Routes/web.php",
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
    private function bootRouteMiddlewares(Router $router)
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

        UnusualRoutes::generateRouteMiddlewares();

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

        Route::macro('singleton', function (
            $slug,
            $options = [],
            $resource_options = [],
            $resource = true
        ) {
            // $pluralSlug = Str::plural($slug);
            // $modelName = Str::studly($slug);

            // Route::module($pluralSlug, $options, $resource_options, $resource);

            // $lastRouteGroupName = RouteServiceProvider::getLastRouteGroupName();

            // $groupPrefix = RouteServiceProvider::getGroupPrefix();

            // // Check if name will be a duplicate, and prevent if needed/allowed
            // if (RouteServiceProvider::shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)) {
            //     $singletonRouteName = "{$groupPrefix}.{$slug}";
            // } else {
            //     $singletonRouteName = $slug;
            // }

            // Route::get($slug, $modelName . 'Controller@editSingleton')->name($singletonRouteName);
        });
    }

    /**
     *  Boot Route macros.
     *
     * @return void
     */
    protected function bootMacros()
    {
        Route::macro('hasAdmin', function($routeName){
            if(Route::has($routeName)){
                return $routeName;
            }

            $admin_route_prefix = adminRouteNamePrefix();

            if( explode('.', $routeName)[0] !== $admin_route_prefix && Route::has($admin_route_prefix . '.' . $routeName)){
                return $admin_route_prefix . '.' . $routeName;
            }else{
                return false;
            }
        });

        Route::macro('moduleRoutes', function($module, $options = []){


            $config = $module->getConfig();
            $moduleName = $config['name'] ?? $module->getName();
            if($moduleName ){ //&& getStringOrFalse($config['name'])
                $pr = $module->getParentRoute();

                $has_system_prefix = $module->hasSystemPrefix();
                $system_prefix = $has_system_prefix ? systemUrlPrefix() . '/' : '';
                $system_route_name = $has_system_prefix ? systemRouteNamePrefix() : '';

                $parentStudlyName = studlyName( $moduleName ); // UserCompany
                $parentCamelName = camelCase( $moduleName ); // userCompany
                $parentKebabName = kebabCase( $moduleName ); // user-company
                $parentSnakeName = snakeCase( $moduleName );  // user_company

                $parentUrlSegment = $config['url'] ?? $pr['url'] ?? pluralize($parentKebabName);

                if( is_array( $routes = $module->getRouteConfigs(valid: true) ) ){

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

                        if(isset($item['name']) ){ //&& getStringOrFalse($item['name'])
                            $routeKebabName = kebabCase( $item['name'] );
                            $routeStudlyName = studlyName($item['name']);
                            $routeSnakeName = snakeCase($item['name']);
                            $routeUrlSegment = $item['url'] ?? pluralize($routeKebabName);
                            $url = $routeUrlSegment;

                            $controllerName = $routeStudlyName.'Controller';

                            $resourceOptionsNames = $item['route_name'] ?? $routeSnakeName;
                            $resourceOptionsAs = [];
                            $parameters = [];
                            $prefixes = [];

                            if($system_prefix){
                                $prefixes[] = rtrim($system_prefix, '//');
                            }

                            if( $system_route_name ){
                                $resourceOptionsAs[] = $system_route_name;
                            }

                            $parameters[$routeUrlSegment] = $routeSnakeName;

                            if(isset($item['belongs']) && $item['belongs']){

                                foreach ($item['belongs'] as $key => $belong) {
                                    $belongRoute = $module->getRouteConfigs($belong);
                                    if($belongRoute){
                                        $belongRouteName = $belongRoute['route_name'] ?? snakeCase($belongRoute['name']); // package_region
                                        $belongRouteUrl = $belongRoute['url'] ?? pluralize(kebabCase($belongRoute['name'])); // package-regions
                                        // Route::prefix('packages')->group(function(){
                                        //     Route::resource('package-regions.package-countries', 'PackageCountryController', [
                                        //         'as' => 'package_region',
                                        //         'names' => 'package_region.nested.package_country',
                                        //     ]);
                                        // });
                                        Route::prefix($parentUrlSegment)->group(function() use(
                                            $parentSnakeName,
                                            $routeUrlSegment,
                                            $routeSnakeName,
                                            $controllerName,
                                            $belongRouteUrl,
                                            $belongRouteName,
                                            $parameters
                                        ){
                                            $resourceRegistrar = Route::resource("{$belongRouteUrl}.{$routeUrlSegment}", $controllerName, [
                                                'as' => $parentSnakeName,
                                                'names' => "{$belongRouteName}.nested.{$routeSnakeName}",
                                            ])->parameters($parameters + [
                                                $belongRouteUrl => $belongRouteName
                                            ]);
                                            $resourceRegistrar->only([
                                                'index',
                                                'create',
                                                'store'
                                            ]);
                                        });
                                    }
                                }

                            }

                            if( ($isNotParent = !(isset($item['parent']) && $item['parent'])) || $parentUrlSegment !== $routeUrlSegment ){ // unless parent route
                                // $url = $parentUrlSegment . "/" . $url;
                                $prefixes[] = $parentUrlSegment;

                                if($isNotParent){
                                    $resourceOptionsAs[] = $parentSnakeName;
                                }

                            }

                            // $url = $system_prefix . $url;
                            $resourceOptions = [
                                'as' => implode( '.', $resourceOptionsAs),
                                'names' => $resourceOptionsNames,
                            ];

                            $resourceOptionsAs[] = $routeSnakeName;
                            // dd(
                            //     $url,
                            //     $prefixes,
                            //     get_defined_vars()
                            // );

                            Route::prefix(implode('/', $prefixes))->group(function() use(
                                $controllerName,
                                $routeUrlSegment,
                                $routeStudlyName,
                                $resourceOptionsAs,
                                $resourceOptions,
                                $parameters,
                            ){
                                // dd(
                                //     get_defined_vars()
                                // );

                                Route::additionalRoutes($routeUrlSegment, $routeStudlyName, [
                                    'as' => implode( '.', $resourceOptionsAs)
                                ]);


                                Route::resource($routeUrlSegment, $controllerName, $resourceOptions)
                                    // ->scoped($scoped)
                                    ->parameters($parameters);
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

                if (in_array($customRoute, ['browser', 'tags'])) Route::get($routeSlug, $mapping);


                if (in_array($customRoute, ['restoreRevision'])) Route::get($routeSlug . "/{{$snakeCase}}", $mapping);


                if (
                    in_array($customRoute, [
                        'publish',
                        'feature',
                        'restore',
                        'forceDelete',
                    ])
                ) Route::put($routeSlug, $mapping);


                if (in_array($customRoute, ['duplicate'])) Route::put($routeSlug . "/{{$snakeCase}}", $mapping);


                if (in_array($customRoute, ['preview'])) Route::put($routeSlug . "/{{$snakeCase}}", $mapping);


                if (
                    in_array($customRoute, [
                        'reorder',
                        'bulkPublish',
                        'bulkFeature',
                        'bulkDelete',
                        'bulkRestore',
                        'bulkForceDelete',
                    ])
                ) Route::post($routeSlug, $mapping);

            }


        });

        Route::macro('internalApiRoutes', function ($routeFile = null, $middlewares = [])
        {
            // if(!$routeFile){
            //     $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';

            //     $routeFile = fileTrace($pattern);
            // }

            // $lowerModule  = curtModuleLowerName($routeFile);
            // $studlyModule = curtModuleStudlyName($routeFile);

            // if( empty($middlewares) )
            //     $middlewares = ['auth'];

            // Route::middleware($middlewares)->group( function() use($lowerModule, $studlyModule){

            //     Route::prefix('api')
            //         ->name('api.')
            //         ->namespace('API')
            //         ->group(function() use($lowerModule, $studlyModule){

            //         Route::apiResource( $lowerModule ,$studlyModule.'Controller');
            //         if( is_array( $parent = config( $lowerModule.'.parent_route' ) ) ){
            //             $url = $parent['url'] ?? lowerName($parent['name']);
            //             $studlyName = studlyName($parent['name']);

            //             Route::apiResource($url, $studlyName.'Controller');
            //         }
            //         Route::prefix( $lowerModule )
            //             ->name( $lowerModule.'.' )
            //             ->group(function() use($lowerModule, $studlyModule){

            //             if( is_array( config( $lowerModule . '.sub_routes' ))){
            //                 foreach( config( $lowerModule . '.sub_routes' ) as $value) {
            //                     $url = $value['url'] ?? lowerName($value['name']);
            //                     $studlyName = studlyName($value['name']);
            //                     $names = $value['route_name'] ?? $url;

            //                     Route::apiResource($url, $studlyName.'Controller', ['names' => $names]);
            //                 }
            //             }
            //         });

            //     });

            // });
        });
    }

    public static function shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)
    {
        return ! empty($groupPrefix) && (blank($lastRouteGroupName) ||
            unusualConfig('allow_duplicates_on_route_names', true) ||
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

        if (! empty(unusualConfig('admin_app_path'))) {
            $groupPrefix = ltrim(
                str_replace(
                    unusualConfig('admin_app_path'),
                    '',
                    $groupPrefix
                ),
                '.'
            );
        }

        return $groupPrefix;
    }
}
