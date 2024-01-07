<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Unusualify\Modularity\Http\Controllers\GlideController;
use Unusualify\Modularity\Facades\UnusualRoutes;
use Unusualify\Modularity\Facades\Modularity;

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

                $router->group(
                    $groupOptions,
                    function ($router)  use($supportSubdomainRouting){
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
                                // 'domain' => config(unusualBaseKey() . '.admin_app_url'),
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
                        //             'domain' => config(unusualBaseKey() . '.admin_app_subdomain', 'admin') .
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
        $supportSubdomainRouting = false
    ) {
        $groupOptions = UnusualRoutes::groupOptions();
        // foreach(Module::allEnabled() as $module){
        foreach(Modularity::allEnabled() as $module){
            $router->group([
                    ...$groupOptions,
                    ...[
                        'middleware' => UnusualRoutes::webMiddlewares(),
                        'namespace' => $module->getClassNamespace('Http\Controllers'),
                    ]
                ],
                function ($router) use ($module) {
                    // $router->moduleRoutes($module);
                    Route::moduleRoutes($module);
                }
            );

            $has_system_prefix = $module->hasSystemPrefix();
            $system_prefix = $has_system_prefix ? unusualConfig('base_prefix', 'system-settings') . '/' : '';
            $system_route_name = $has_system_prefix ? snakeCase(studlyName(unusualConfig('base_prefix', 'system-settings'))) . '.' : '';

            $_groupOptions = [];

            $_groupOptions['prefix'] = (adminRoutePrefix() ? adminRoutePrefix() . '/' : '')
                . $system_prefix
                . kebabCase( $module->getName() );

            $_groupOptions['as'] = (adminRouteNamePrefix() ? adminRouteNamePrefix() . '.' : '')
                . $system_route_name
                . snakeCase( $module->getName() ) . '.';

            UnusualRoutes::registerRoutes(
                $router,
                $_groupOptions,
                ['web'],//$middlewares,
                $module->getClassNamespace('Http\Controllers'), //config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getDirectoryPath('Routes/web.php'), //$module->getPath()."/Routes/web.php",
                true
            );
            UnusualRoutes::registerRoutes(
                $router,
                $_groupOptions,
                ['api'],//$middlewares,
                $module->getClassNamespace('Http\Controllers\API'), //config('modules.namespace', 'Modules') . "\\" . $module->getStudlyName() . '\Http\Controllers',
                $module->getDirectoryPath('Routes/api.php'), //$module->getPath()."/Routes/web.php",
                true
            );
            UnusualRoutes::registerRoutes(
                $router,
                [],
                ['web'],//$middlewares,
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

            $pr = $module->getParentRoute();

            $has_system_prefix = $module->hasSystemPrefix();
            $system_prefix = $has_system_prefix ? unusualConfig('base_prefix', 'system-settings') . '/' : '';
            $system_route_name = $has_system_prefix ? snakeCase(studlyName(unusualConfig('base_prefix', 'system-settings'))) : '';

            $parentStudlyName = studlyName( $config['name'] ); // UserCompany
            $parentCamelName = camelCase( $config['name'] ); // userCompany
            $parentKebabName = kebabCase( $config['name'] ); // user-company
            $parentSnakeName = snakeCase( $config['name'] );  // user_company

            $parentUrlSegment = $pr['url'] ?? pluralize($parentKebabName);

            if( is_array( $routes = $module->getRouteConfigs() ) ){

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
                    $routeKebabName = kebabCase( $item['name'] );
                    $routeStudlyName = studlyName($item['name']);
                    $routeSnakeName = snakeCase($item['name']);

                    $routeUrlSegment = $item['url'] ?? pluralize($routeKebabName);
                    $url = $routeUrlSegment;
                    $controllerName = $routeStudlyName.'Controller';

                    $resourceOptionsNames = $item['route_name'] ?? $routeSnakeName;
                    $resourceOptionsAs = [];

                    if( $system_route_name ){
                        $resourceOptionsAs[] = $system_route_name;
                    }


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
                                Route::prefix($parentUrlSegment)->group(function() use($parentSnakeName, $routeUrlSegment, $routeSnakeName, $controllerName, $belongRouteUrl, $belongRouteName){
                                    $resourceRegistrar = Route::resource("{$belongRouteUrl}.{$routeUrlSegment}", $controllerName, [
                                        'as' => $parentSnakeName,
                                        'names' => "{$belongRouteName}.nested.{$routeSnakeName}",
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

                    if( !(isset($item['parent']) && $item['parent']) ){ // unless parent route
                        $url = $parentUrlSegment . "/" . $url;
                        $resourceOptionsAs[] = $parentSnakeName;

                    }else{ // if parent route

                    }

                    $url = $system_prefix . $url;
                    $resourceOptions = [
                        'as' => implode( '.', $resourceOptionsAs),
                        'names' => $resourceOptionsNames,
                    ];

                    $resourceOptionsAs[] = $routeSnakeName;

                    Route::additionalRoutes($url, $routeStudlyName, [
                        'as' => implode( '.', $resourceOptionsAs)
                    ]);

                    Route::resource($url, $controllerName, $resourceOptions)
                        ->parameters([
                            // $url => $sub_studly,
                        ]);
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
        // Route::macro('internalApiRoutes', function ($routeFile = null, $middlewares = []) {

        //     if(!$routeFile){
        //         $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';

        //         $routeFile = fileTrace($pattern);
        //     }

        //     $lowerModule  = getCurrentModuleLowerName($routeFile);
        //     $studlyModule = getCurrentModuleStudlyName($routeFile);

        //     if( empty($middlewares) )
        //         $middlewares = ['auth'];

        //     Route::middleware($middlewares)->group( function() use($lowerModule, $studlyModule){

        //         Route::prefix('api')
        //             ->name('api.')
        //             ->namespace('API')
        //             ->group(function() use($lowerModule, $studlyModule){

        //             Route::apiResource( $lowerModule ,$studlyModule.'Controller');
        //             if( is_array( $parent = config( $lowerModule.'.parent_route' ) ) ){
        //                 $url = $parent['url'] ?? lowerName($parent['name']);
        //                 $studlyName = studlyName($parent['name']);

        //                 Route::apiResource($url, $studlyName.'Controller');
        //             }
        //             Route::prefix( $lowerModule )
        //                 ->name( $lowerModule.'.' )
        //                 ->group(function() use($lowerModule, $studlyModule){

        //                 if( is_array( config( $lowerModule . '.sub_routes' ))){
        //                     foreach( config( $lowerModule . '.sub_routes' ) as $value) {
        //                         $url = $value['url'] ?? lowerName($value['name']);
        //                         $studlyName = studlyName($value['name']);
        //                         $names = $value['route_name'] ?? $url;

        //                         Route::apiResource($url, $studlyName.'Controller', ['names' => $names]);
        //                     }
        //                 }
        //             });

        //         });

        //     });
        // });
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
