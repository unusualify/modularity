<?php

namespace Unusualify\Modularity;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module as NwidartModule;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularity\Activators\FileActivator;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Support\Finder;

class Module extends NwidartModule
{
    /**
     * @var ModuleActivatorInterface
     */
    private $moduleActivator;

    private $config;

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * The constructor.
     */
    public function __construct(string $name, $path)
    {
        $app = app();

        parent::__construct($app, $name, $path);

        try {
            $this->moduleActivator = (new FileActivator($app))->setModule($this->getName(), $path);
        } catch (\Throwable $th) {
            dd($path, $name, $this->getName());
        }

        $this->setMiddlewares();
        // $this->moduleActivator->setModule($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath(): string
    {
        // This checks if we are running on a Laravel Vapor managed instance
        // and sets the path to a writable one (services path is not on a writable storage in Vapor).
        if (! is_null(env('VAPOR_MAINTENANCE_MODE', null))) {
            return Str::replaceLast('config.php', $this->getSnakeName() . '_module.php', $this->app->getCachedConfigPath());
        }

        return Str::replaceLast('services.php', $this->getSnakeName() . '_module.php', $this->app->getCachedServicesPath());
    }

    /**
     * {@inheritdoc}
     */
    public function registerProviders(): void
    {
        (new ProviderRepository($this->app, new Filesystem, $this->getCachedServicesPath()))
            ->load($this->get('providers', []));
    }

    /**
     * {@inheritdoc}
     */
    public function registerAliases(): void
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
    }

    public function setMiddlewares()
    {
        $middleware_folder = GenerateConfigReader::read('filter')->getPath();
        $middleware_namespace = GenerateConfigReader::read('filter')->getNamespace();


        if (file_exists(($middlewareDir = $this->getDirectoryPath($middleware_folder)))) {
            foreach (glob($middlewareDir . '/*Middleware.php') as $middlewareFile) {
                $middlewareFileName = pathinfo($middlewareFile)['filename']; // $filename
                $middlewareClass = $this->getClassNamespace("{$middleware_namespace}\\" . $middlewareFileName);
                if (@class_exists($middlewareClass)) {

                    $name = implode('.', Arr::where(explode('_', snakeCase($middlewareFileName)), function ($value) {
                        return $value !== 'middleware';
                    }));
                    $aliasName = "modules." . $this->getSnakeName() . '.' . $name;

                    $this->middlewares[$name] = [
                        'alias' => $aliasName,
                        'class' => $middlewareClass,
                    ];
                }
            }
        }
    }

    public function setModuleActivator($name)
    {
        // Directory path fix for System Modules
        $this->moduleActivator->setModule($name, $this->getDirectoryPath());
    }

    /**
     * Enable the current module route.
     */
    public function enableRoute($route): void
    {
        $this->fireModuleEvent('enabling', $route);

        $this->moduleActivator->enable($route);

        $this->flushModuleCache();

        $this->fireModuleEvent('enabled', $route);
    }

    /**
     * Disable the current module route.
     */
    public function disableRoute($route): void
    {
        $this->fireModuleEvent('disabling', $route);

        $this->moduleActivator->disable($route);

        $this->flushModuleCache();

        $this->fireModuleEvent('disabled', $route);
    }

    public function getRoutes()
    {
        return $this->moduleActivator->getRoutes();
    }

    /**
     * Register the module's route event.
     *
     * @param string $event
     */
    protected function fireModuleEvent($event, $route): void
    {
        $this->app['events']->dispatch(sprintf('modules.%s.%s' . $event, $this->getLowerName(), $route), [$this]);
    }

    /**
     * Determine whether the current module route activated.
     */
    public function isEnabledRoute($route): bool
    {
        return $this->moduleActivator->hasStatus($route, true);
    }

    /**
     *  Determine whether the current module route not disabled.
     */
    public function isDisabledRoute($route): bool
    {
        return ! $this->isEnabledRoute($route);
    }

    /**
     * flushModuleCache
     */
    private function flushModuleCache(): void
    {

        if (modularityConfig('cache.enabled')) {
            // $this->cache->store()->flush();
        }
    }

    /**
     * Get directory path.
     */
    public function getDirectoryPath($directory = '', $relative = false): string
    {
        $path = $this->getPath();

        if ($relative) {
            $path = str_replace(base_path('/'), '', $path);
        }

        return $path . (empty($directory) ? '/' : "/$directory");
    }

    public function isModularityModule()
    {
        $modularityModulesPath = Modularity::getVendorPath('modules');

        return str_starts_with($this->getPath(), $modularityModulesPath);
    }

    /**
     * Get specific class namespace of module.
     */
    public function getClassNamespace($class): string
    {
        return $this->getBaseNamespace() . '\\' . $class;
    }

    /**
     * Get base namespace of the module.
     */
    public function getBaseNamespace(): string
    {
        return config('modules.namespace', 'Modules') . '\\' . $this->getStudlyName();
    }

    /**
     * getRouteConfigs
     *
     * @param mixed $notation
     */
    public function getRouteConfigs($notation = null, $valid = false): array
    {
        $notation = ! $notation ? $notation : ".{$notation}";

        return ($valid && ! $notation) ? Arr::where($this->getConfig('routes' . $notation), function ($item, $key) {
            // return !(!isset($item['name']) || !$this->routeHasTable($item['name'], $key));
            return ! (! isset($item['name']));
        }) : $this->getConfig('routes' . $notation);
    }

    /**
     * getRouteConfig
     *
     * @param mixed $route_name
     */
    public function getRouteConfig($route_name): array
    {
        return $this->getRouteConfigs(snakeCase($route_name));
    }

    /**
     * getRouteInput
     *
     * @param mixed $route_name
     * @param mixed $input_name
     */
    public function getRouteInput($route_name, $input_name = null): array
    {
        $inputs = $this->getRouteConfig($route_name)['inputs'];

        return $this->getRouteConfig($route_name)['inputs'];
    }

    /**
     * getConfig
     *
     * @param mixed $notation
     */
    public function getConfig($notation = null): mixed
    {
        $notation = ! $notation ? '' : ".{$notation}";

        if (! $this->app['config']->has($this->getSnakeName()) && $this->app->runningInConsole() && file_exists($this->getDirectoryPath('Config/config.php'))) {
            $this->app['config']->set("{$this->getSnakeName()}", include ($this->getDirectoryPath('Config/config.php')));
        }

        return $this->app['config']->get("{$this->getSnakeName()}{$notation}", []);
    }

    public function setConfig($newConfig, $notation = null): mixed
    {

        $notation = ! $notation ? '' : ".{$notation}";

        if (! $this->app['config']->has($this->getSnakeName()) && $this->app->runningInConsole() && file_exists($this->getDirectoryPath('Config/config.php'))) {
            $this->app['config']->set("{$this->getSnakeName()}", include ($this->getDirectoryPath('Config/config.php')));
        }

        return $this->app['config']->set("{$this->getSnakeName()}{$notation}", $newConfig);
    }

    /**
     * getParentRoute
     */
    public function getParentRoute(): array
    {
        return array_values(array_filter($this->getRouteConfigs(), function ($r) {
            return isset($r['parent']) && $r['parent'];
        }))[0] ?? [];
    }

    /**
     * hasParentRoute
     */
    public function hasParentRoute(): bool
    {
        return count($this->getParentRoute()) > 0;
    }

    public function isParentRoute($routeName): bool
    {
        return count(($pr = $this->getParentRoute())) > 0 && $pr['name'] == studlyName($routeName);
    }

    public function isSingleton($routeName)
    {
        $singularTrait = 'Unusualify\Modularity\Entities\Traits\IsSingular';
        $repository = $this->getRouteClass($routeName, 'repository', true);

        return classHasTrait(App::make($repository)->getModel(), $singularTrait);
    }

    /**
     * hasSystemPrefix
     */
    public function hasSystemPrefix(): mixed
    {
        return $this->getConfig('system_prefix') ?? $this->getConfig('base_prefix', false);
    }

    /**
     * systemPrefix
     */
    public function systemPrefix(): string
    {
        return systemUrlPrefix();
    }

    /**
     * systemRouteNamePrefix
     */
    public function systemRouteNamePrefix(): string
    {
        return systemRouteNamePrefix();
    }

    /**
     * prefix
     */
    public function prefix(): string
    {
        $pr = $this->getParentRoute();
        $name = getValueOrNull($this->getConfig('name')) ?? $this->getName();

        return $this->hasParentRoute() && (isset($pr['url']) || isset($pr['name']))
            ? ($pr['url'] ?? pluralize(kebabCase($pr['name'])))
            : pluralize(kebabCase($name));
    }

    /**
     * fullPrefix
     */
    public function fullPrefix(): string
    {
        $prefixes = [];

        $adminUrlPrefix = adminUrlPrefix();

        if ($adminUrlPrefix) {
            $prefixes[] = $adminUrlPrefix;
        }

        if ($this->hasSystemPrefix()) {
            $prefixes[] = $this->systemPrefix();
        }

        $prefixes[] = $this->prefix();

        return implode('/', $prefixes);
    }

    /**
     * routeNamePrefix
     */
    public function routeNamePrefix(): string
    {
        return snakeCase(getValueOrNull($this->getConfig('name')) ?? $this->getName());

        return $this->hasParentRoute()
            ? ($this->getParentRoute()['route_name'] ?? $this->getSnakeName())
            : snakeCase(getValueOrNull($this->getConfig('name')) ?? $this->getName());
    }

    /**
     * fullRouteNamePrefix
     */
    public function fullRouteNamePrefix($isParent = false): string
    {
        $prefixes = [];

        if (($adminRouteNamePrefix = adminRouteNamePrefix())) {
            $prefixes[] = $adminRouteNamePrefix;
        }

        if ($this->hasSystemPrefix()) {
            $prefixes[] = $this->systemRouteNamePrefix();
        }

        if (! $isParent) {
            $prefixes[] = $this->routeNameprefix();
        }

        return implode('.', $prefixes);
    }

    public function getRepository($routeName, $asClass = true)
    {
        return (new Finder)->getRouteRepository($routeName, $asClass);
    }

    public function routeHasTable($routeName = null, $notation = null)
    {
        $tableName = $this->getRepository($routeName ?? $this->getStudlyName(), false)
            ? $this->getRepository($routeName ?? $this->getStudlyName())->getModel()->getTable()
            : $this->getRepository($notation)->getModel()->getTable();

        return Schema::hasTable($tableName);
    }

    public function getConfigPath()
    {
        $config_folder = GenerateConfigReader::read('config')->getPath();

        return "{$this->getPath()}/{$config_folder}/config.php";
    }

    /**
     * Check whether the file is presents
     *
     * @param string fileName
     * @return bool
     */
    public function isFileExists($fileName)
    {

        $pattern = $this->getDirectoryPath('**/*/*' . $fileName . '*');

        $search = glob($pattern);

        return ! empty($search);
    }

    public function getModuleUris(): array
    {
        $patterns = [$this->fullRouteNamePrefix()];

        $pr = $this->getParentRoute();

        if (isset($pr['route_name']) && $this->routeNamePrefix() != $pr['route_name']) {

            $prefixes = [];

            $adminRouteNamePrefix = adminRouteNamePrefix();

            if (($adminRouteNamePrefix = adminRouteNamePrefix())) {
                $prefixes[] = $adminRouteNamePrefix;
            }

            if ($this->hasSystemPrefix()) {
                $prefixes[] = $this->systemRouteNamePrefix();
            }

            $prefixes[] = $pr['route_name'];

            $patterns[] = implode('.', $prefixes);

        }

        $quote = implode('|', $patterns);
        $moduleRoutes = array_map(function ($r) {
            return $r->uri();

            return [
                'controller' => $r->getActionName(),
                'uri' => $r->uri(),
            ];
        }, array_filter(Route::getRoutes()->getRoutesByName(), fn ($r) => preg_match('/' . $quote . '/', $r->getName())));

        return $moduleRoutes;
    }

    public function getRouteUris($routeName): array
    {
        $actions = [
            'restore',
            'forceDelete',
            'duplicate',
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
            'bulkDelete',
            'bulkForceDelete',
            'bulkRestore',
            'tags',
            'tagsUpdate',
        ];

        $isParentRoute = $this->isParentRoute($routeName);

        $midQuote = '(.nested.[a-z|_]+)?.(';
        $quote = $this->fullRouteNamePrefix($isParentRoute) . '.' . snakeCase($routeName) . $midQuote . implode('|', $actions) . ')$';

        return Collection::make($this->getModuleUris())->filter(fn ($uri, $name) => preg_match('/' . $quote . '/', $name))->toArray();
    }

    public function getRouteActionUri($routeName, $action, $replacements = [], $absolute = false): string
    {
        $quote = preg_quote('.' . $action);

        $endpoint = '/' . Collection::make($this->getRouteUris($routeName))
            ->filter(fn ($uri, $name) => preg_match('/' . $quote . '/', $name))->first();

        $endpoint = replace_curly_braces($endpoint, $replacements);

        if($absolute){
            return url($endpoint);
        }

        return $endpoint;
    }

    public function getParentNamespace(string $target): string
    {
        return $this->getBaseNamespace() . '\\' . GenerateConfigReader::read(kebabCase($target))->getNamespace();
    }

    public function getTargetClassNamespace(string $target, $className = null): string
    {
        return $this->getBaseNamespace() . '\\' . GenerateConfigReader::read(kebabCase($target))->getNamespace() . ($className ? '\\' . $className : '');
    }

    public function getTargetClassPath(string $target, $className = null): string
    {
        return $this->getDirectoryPath(GenerateConfigReader::read(kebabCase($target))->getPath()) . ($className ? '/' . $className : '');
    }

    public function getRouteClass(string $routeName, string $target, $asClass = false): string
    {
        $className = studlyName($routeName);

        if (! preg_match('/model/', kebabCase($target))) {
            $className .= studlyName($target);
        }

        // if($asClass){
        //     return App::make($this->getParentNamespace($target) . '\\' . $className);
        // }

        return $this->getParentNamespace($target) . '\\' . $className;
    }

    public function getNavigationActions(string $routeName)
    {
        $routeName = snakeCase($routeName); // snake case

        $navigationActions = [];

        foreach ($this->getRouteConfigs() as $key => $routeConfig) {
            if (isset($routeConfig['belongs']) && in_array($routeName, $routeConfig['belongs'])) {
                $nestedRouteSnake = snakeCase($routeConfig['name']);
                $routeSnake = snakeCase($routeName);

                $uri = $this->getRouteActionUri(nestedRouteNameFormat($routeName, $routeConfig['name']), 'index');

                $pattern = "\{$routeSnake\}";

                $navigationActions[] = [
                    'name' => 'link',
                    // 'url' => moduleRoute($routeConfig['name'],  $this->fullRouteNamePrefix() . '.' . $routeName . '.nested', 'index', [
                    //     $routeName => ':id',
                    // ]),
                    'url' => preg_replace('/(' . $pattern . ')/', ':id', $uri),
                    'label' => 'modules.' . $nestedRouteSnake,
                    'icon' => '$modules',
                    'color' => 'green',
                ];

            }
            // code...
        }

        return $navigationActions;
    }

    public function createMiddlewareAliases()
    {
        foreach ($this->middlewares as $name => $middleware) {
            Route::aliasMiddleware($middleware['alias'], $middleware['class']);
        }
    }


    public function getRouteMiddlewareAliases($routeName)
    {
        $snakeName = snakeCase($routeName);

        $autoMiddlewares = [];

        if(isset($this->middlewares[$snakeName])){
            $noAutoMiddleware = $this->getRouteConfig($routeName)['noAutoMiddleware'] ?? false;

            if(!$noAutoMiddleware){
                $autoMiddlewares = [$this->middlewares[$snakeName]['alias']];
            }
        }

        return array_merge(
            $autoMiddlewares,
            $this->getRouteConfigs($routeName)['middleware'] ?? $this->getRouteConfigs($routeName)['middlewares'] ?? []
        );
    }

}
