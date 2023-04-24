<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Container\Util;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

use OoBook\CRM\Base\Facades\UnusualRoutes;
use Nwidart\Modules\Facades\Module;

class RouteServiceProvider extends ServiceProvider
{

    protected $namespace = 'OoBook\CRM\Base\Http\Controllers';

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

    private function mapModuleRoutes(
        $router,
        $groupOptions,
        $middlewares
    ) {
        foreach(Module::all() as $module){
            if( $module->getName() != 'Base' && $module->isStatus(true)){
                UnusualRoutes::registerRoutes(
                    $router,
                    $groupOptions,
                    $middlewares,
                    'Modules' . "\\" . $module->getStudlyName() . '\Http\Controllers',
                    $module->getPath()."/Routes/web.php",
                    true
                );

            }
        }
    }

    private function mapBaseRoutes(
        $router,
        $groupOptions,
        $middlewares,
        $supportSubdomainRouting = false
    ) {

        $internalRoutes = function ($router) use (
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

        };

        $router->group(
            $groupOptions + [
                'namespace' => $this->namespace,
            ],
            function ($router) use ($internalRoutes, $supportSubdomainRouting) {
                $router->group(
                    [
                        // 'domain' => config('twill.admin_app_url'),
                    ],
                    $internalRoutes
                );

                // if ($supportSubdomainRouting) {
                //     $router->group(
                //         [
                //             'domain' => config('twill.admin_app_subdomain', 'admin') .
                //             '.{subdomain}.' .
                //             config('app.url'),
                //         ],
                //         $internalRoutes
                //     );
                // }
            }
        );

        if (config(getUnusualBaseKey() . '.templates_on_frontend_domain')) {
            $router->group(
                [
                    'namespace' => $this->namespace . '\Admin',
                    'domain' => config('app.url'),
                    'middleware' => [
                        config('twill.admin_middleware_group', 'web'),
                    ],
                ],
                function ($router) {
                    $router->group(
                        [
                            'middleware' => $this->app->environment(
                                'production'
                            )
                            ? ['twill_auth:twill_users']
                            : [],
                        ],
                        function ($router) {
                            require __DIR__ . '/../routes/templates.php';
                        }
                    );
                }
            );
        }

        if (
            config(getUnusualBaseKey() . '.media_library.image_service') ===
            'OoBook\CRM\Base\Services\MediaLibrary\Glide'
        ) {
            $router
                ->get(
                    '/' . config(getUnusualBaseKey() . '.glide.base_path') . '/{path}',
                    GlideController::class
                )
                ->where('path', '.*');
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
        Route::aliasMiddleware(
            'supportSubdomainRouting',
            SupportSubdomainRouting::class
        );
        Route::aliasMiddleware('impersonate', Impersonate::class);
        Route::aliasMiddleware('twill_auth', Authenticate::class);
        Route::aliasMiddleware('twill_guest', RedirectIfAuthenticated::class);
        Route::aliasMiddleware(
            'validateBackHistory',
            ValidateBackHistory::class
        );
        Route::aliasMiddleware('localization', Localization::class);
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
            if ($routePrefix === null) {
                $routePrefix = $moduleName;
            }

            if ($controllerName === null) {
                $controllerName = ucfirst(Str::plural($moduleName));
            }

            $routePrefix = empty($routePrefix)
            ? '/'
            : (Str::startsWith($routePrefix, '/')
                ? $routePrefix
                : '/' . $routePrefix);
            $routePrefix = Str::endsWith($routePrefix, '/')
            ? $routePrefix
            : $routePrefix . '/';

            Route::name($moduleName . '.show')->get(
                $routePrefix . '{slug}',
                $controllerName . 'Controller@show'
            );
            Route::name($moduleName . '.preview')
                ->get(
                    '/admin-preview' . $routePrefix . '{slug}',
                    $controllerName . 'Controller@show'
                )
                ->middleware(['web', 'twill_auth:twill_users', 'can:list']);
        });

        Route::macro('module', function (
            $slug,
            $options = [],
            $resource_options = [],
            $resource = true
        ) {
            $slugs = explode('.', $slug);
            $prefixSlug = str_replace('.', '/', $slug);
            $_slug = Arr::last($slugs);
            $className = implode(
                '',
                array_map(function ($s) {
                    return ucfirst(Str::singular($s));
                }, $slugs)
            );

            $customRoutes = $defaults = [
                'reorder',
                'publish',
                'bulkPublish',
                'browser',
                'feature',
                'bulkFeature',
                'tags',
                'preview',
                'restore',
                'bulkRestore',
                'forceDelete',
                'bulkForceDelete',
                'bulkDelete',
                'restoreRevision',
                'duplicate',
            ];

            if (isset($options['only'])) {
                $customRoutes = array_intersect(
                    $defaults,
                    (array) $options['only']
                );
            } elseif (isset($options['except'])) {
                $customRoutes = array_diff(
                    $defaults,
                    (array) $options['except']
                );
            }

            $lastRouteGroupName = RouteServiceProvider::getLastRouteGroupName();

            $groupPrefix = RouteServiceProvider::getGroupPrefix();

            // Check if name will be a duplicate, and prevent if needed/allowed
            if (RouteServiceProvider::shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)) {
                $customRoutePrefix = "{$groupPrefix}.{$slug}";
                $resourceCustomGroupPrefix = "{$groupPrefix}.";
            } else {
                $customRoutePrefix = $slug;

                // Prevent Laravel from generating route names with duplication
                $resourceCustomGroupPrefix = '';
            }

            foreach ($customRoutes as $route) {
                $routeSlug = "{$prefixSlug}/{$route}";
                $mapping = [
                    'as' => $customRoutePrefix . ".{$route}",
                    'uses' => "{$className}Controller@{$route}",
                ];

                if (in_array($route, ['browser', 'tags'])) {
                    Route::get($routeSlug, $mapping);
                }

                if (in_array($route, ['restoreRevision'])) {
                    Route::get($routeSlug . '/{id}', $mapping);
                }

                if (
                    in_array($route, [
                        'publish',
                        'feature',
                        'restore',
                        'forceDelete',
                    ])
                ) {
                    Route::put($routeSlug, $mapping);
                }

                if (in_array($route, ['duplicate'])) {
                    Route::put($routeSlug . '/{id}', $mapping);
                }

                if (in_array($route, ['preview'])) {
                    Route::put($routeSlug . '/{id}', $mapping);
                }

                if (
                    in_array($route, [
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

            if ($resource) {
                Route::group(
                    ['as' => $resourceCustomGroupPrefix],
                    function () use ($slug, $className, $resource_options) {
                        Route::resource(
                            $slug,
                            "{$className}Controller",
                            $resource_options
                        );
                    }
                );
            }
        });

        Route::macro('singleton', function (
            $slug,
            $options = [],
            $resource_options = [],
            $resource = true
        ) {
            $pluralSlug = Str::plural($slug);
            $modelName = Str::studly($slug);

            Route::module($pluralSlug, $options, $resource_options, $resource);

            $lastRouteGroupName = RouteServiceProvider::getLastRouteGroupName();

            $groupPrefix = RouteServiceProvider::getGroupPrefix();

            // Check if name will be a duplicate, and prevent if needed/allowed
            if (RouteServiceProvider::shouldPrefixRouteName($groupPrefix, $lastRouteGroupName)) {
                $singletonRouteName = "{$groupPrefix}.{$slug}";
            } else {
                $singletonRouteName = $slug;
            }

            Route::get($slug, $modelName . 'Controller@editSingleton')->name($singletonRouteName);
        });
    }

    /**
     *  Boot Route macros.
     *
     * @return void
     */
    protected function bootMacros()
    {
        Route::macro('configRoutes', function($config, $middlewares = []){

            Route::middleware($middlewares)->group( function() use($config){

                $parent = $config['parent_route'] ?? '';
                $parentStudly = studlyName( $config['name'] );
                $parent_camel = camelCase( $config['name'] );
                $parent_kebab = kebabCase( $config['name'] );
                $parent_snake = snakeCase( $config['name'] );

                if( is_array( $sub_routes = $config['sub_routes'] ) ){
                    foreach( $sub_routes as $key => $sub) {

                        $sub_camel = camelCase( $sub['name'] );
                        $subStudly = studlyName($sub['name']);

                        $url = $value['url'] ?? $sub_camel;

                        $names = $sub['route_name'] ?? $sub_camel;
                        // dd($sub, $config);
                        // Route::resource($url, $studlyName.'Controller' , ['names' => $names]);
                        if(isset($sub['nested']) && $sub['nested']){
                            Route::resource($parent_camel.".".$url, $parentStudly.$subStudly.'Controller',[
                                'names' => ($parent['route_name'] ?? $parent_camel) . "." . $names
                            ])->parameters([
                                // $parent_camel => $parentStudly,
                                // $url => $subStudly,
                            ]);
                        }else{
                            Route::resource($parent_camel."/".$url, $subStudly.'Controller',[
                                'as' => $parent_camel,
                                'names' => $names
                            ])->parameters([
                                // $url => $subStudly,
                            ]);
                        }

                        // inner nested for sub_route
                        if(isset($sub['nested_routes'])){

                            foreach ($sub['nested_routes'] as $nested) {
                                if(isset($sub_routes[$nested])){
                                    // dd(
                                    //     $parent_camel,
                                    //     $url,
                                    //     $subStudly,
                                    //     $parent_camel.".".$url.".".$nested
                                    // );
                                    // Route::prefix("{$parent_camel}")->name("{$parent_camel}.")->group(function() use($url, $nested, $subStudly){
                                    //     Route::resource("{$url}.{$nested}", "{$subStudly}Controller")->only([
                                    //         'store'
                                    //     ])->shallow();
                                    // });


                                    // Route::prefix("{$parent_camel}/{$url}/{{$url}}")->name("{$parent_camel}.{$url}.{$nested}.")->group(function() use($nested, $subStudly){
                                    //     Route::get("/{$nested}", $subStudly.'Controller@editNested' )->name("edit");
                                    //     Route::put("/{$nested}", $subStudly.'Controller@updateNested' )->name("update");
                                    // });
                                }
                            }

                        }
                    }
                }

                if( is_array( $parent = ($config['parent_route'] ?? '') ) ){

                    $url = $parent['url'] ?? $parent_kebab;
                    $name = $parent['route_name'] ?? $parent_snake;

                    if(preg_match('/press/', $url)){

                    }

                    Route::resource( $url, $parentStudly.'Controller', [
                        'names' => $name
                    ])->parameters([
                        // $url => $parentStudly
                    ]);
                    // Route::resource($url, $studlyName.'Controller', [
                    //     // 'parameters' => [
                    //     //     'payment' => 'payment'
                    //     // ]
                    // ]);
                }
            });
        });

        Route::macro('webRoutes', function ($routeFile = null, $middlewares = []) {

            if(!$routeFile){
                $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';
                $routeFile = fileTrace($pattern);
            }

            $kebabCase  = kebabCase(getCurrentModuleName($routeFile));
            $snakeCase  = snakeCase(getCurrentModuleName($routeFile));

            $config = config( $snakeCase );

            if( !!$config )
                Route::configRoutes($config, $middlewares);
            else
                dd(
                    $kebabCase,
                    $snakeCase,
                    $config
                );

        });


        Route::macro('unusualWebRoutes', function ($middlewares = []) {

            $config = config(
                lowerName(env('BASE_NAME', 'Base'))
            );

            if(isset($config['internal_modules'])){
                foreach ($config['internal_modules'] as $name => $_config) {
                    Route::configRoutes($_config, $middlewares);
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
            config('twill.allow_duplicates_on_route_names', true) ||
            (! Str::endsWith($lastRouteGroupName, ".{$groupPrefix}.")));
    }

    public static function getLastRouteGroupName()
    {
        // Get the current route groups
        $routeGroups = Route::getGroupStack() ?? [];

        // Get the name prefix of the last group
        return end($routeGroups)['as'] ?? '';
    }

    public static function getGroupPrefix()
    {
        $groupPrefix = trim(
            str_replace('/', '.', Route::getLastGroupPrefix()),
            '.'
        );

        if (! empty(config('twill.admin_app_path'))) {
            $groupPrefix = ltrim(
                str_replace(
                    config('twill.admin_app_path'),
                    '',
                    $groupPrefix
                ),
                '.'
            );
        }

        return $groupPrefix;
    }
}
