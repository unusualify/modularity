<?php

namespace Unusualify\Modularous;

use Illuminate\Console\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module as NwidartModule;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularous\Activators\ModuleActivator;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;
use Unusualify\Modularous\Exceptions\ModularousException;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Controllers\Traits\ManageResourceCache;
use Unusualify\Modularous\Repositories\Logic\ResourceCacheActionsTrait;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;
use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;

class Module extends NwidartModule
{
    /**
     * @var ModuleActivatorInterface
     */
    private $moduleActivator;

    /**
     * @var array
     */
    private $middlewares = [];

    /** @var array<string, mixed>|null */
    private ?array $rawConfigCache = null;

    private ?ModuleRouteRegistry $moduleRouteRegistryCache = null;

    /** @var array<string, bool> */
    private array $singletonMemo = [];

    /** @var list<string>|null */
    private ?array $routeNamesMemo = null;

    private static $routeActionLists = [
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
        'assignments',
        'createAssignment',
        'restoreRevision',
        'approveRevision',
        'rejectRevision',
        'showView',
        'listRevisions',
        'syncRemote',
        'syncRemoteAll',
        'clearRemoteCache',
        'previewRemote',
        'listRemoteCatalog',
        'cachePurge',
        'cacheWarm',
        'cachePurgeAll',
        'cacheWarmAll',
    ];

    /**
     * Regex alternation of known route action suffixes (for URL name matching).
     */
    public function routeActionPattern(): string
    {
        return '(' . implode('|', self::$routeActionLists) . ')';
    }

    /**
     * The constructor.
     */
    public function __construct($app, string $name, $path)
    {
        parent::__construct($app, $name, $path);
        $this->app = $app;
        $this->moduleActivator = App::make(ModuleActivator::class, [
            'app' => $app,
            'cacheKey' => 'module-activator.installed.' . kebabCase($this->getName()),
            'statusesFile' => $this->getDirectoryPath('routes_statuses.json'),
        ]);

        $this->setMiddlewares();
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath(): string
    {
        // This checks if we are running on a Laravel Vapor managed instance
        // and sets the path to a writable one (services path is not on a writable storage in Vapor).
        if (! is_null(env('VAPOR_MAINTENANCE_MODE', null))) {
            $basePath = $this->app->getCachedConfigPath();
            $target = 'config.php';
        } else {
            $basePath = $this->app->getCachedServicesPath();
            $target = 'services.php';
        }

        $filename = $this->getSnakeName() . '_module.php';

        // Add process isolation for tests to prevent race conditions in parallel
        if (app()->environment() === 'testing') {
            $token = getenv('TEST_TOKEN') ?: (function_exists('getmypid') ? getmypid() : null);
            if ($token) {
                $filename = $this->getSnakeName() . '_module_' . $token . '.php';
            }
        }

        return dirname($basePath) . '/' . $filename;
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

    /**
     * Determine whether the given status same with the current module status.
     */
    public function isStatus(bool $status): bool
    {
        try {
            return $this->moduleActivator->hasStatus($this, $status);
        } catch (\Throwable $th) {
            Log::error('Modularous module status check failed', [
                'module' => $this->getName(),
                'status' => $status,
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            throw new ModularousException(
                "Failed to check module status for {$this->getName()}: {$th->getMessage()}",
                (int) $th->getCode(),
                $th
            );
        }
    }

    public function getActivator()
    {
        return $this->moduleActivator;
    }

    public function clearCache()
    {
        $this->moduleActivator->reset();
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
                    $aliasName = 'modules.' . $this->getSnakeName() . '.' . $name;

                    $this->middlewares[$name] = [
                        'alias' => $aliasName,
                        'class' => $middlewareClass,
                    ];
                }
            }
        }
    }

    /**
     * Ensure the routes statuses backing store exists for this module.
     */
    public function ensureRoutesStatusesFile(): void
    {
        if ($this->usesFilesystemRouteStatusDriver()) {
            $this->moduleActivator->ensureFileExists();

            return;
        }

        $this->moduleRouteStatusStore()->ensureExists($this->getStudlyName());
    }

    /**
     * Enable the current module route.
     */
    public function enableModuleRoute($route): void
    {
        $route = studlyName((string) $route);

        $this->fireModuleEvent('enabling', $route);

        if ($this->usesFilesystemRouteStatusDriver()) {
            $this->moduleActivator->enable($route);
        } else {
            $this->moduleRouteStatusStore()->setEnabled($this->getStudlyName(), $route, true);
        }

        $this->flushModuleRouteRegistry();
        $this->flushModuleCache();

        $this->fireModuleEvent('enabled', $route);
    }

    /**
     * @deprecated Use {@see enableModuleRoute()} instead.
     */
    public function enableRoute($route): void
    {
        $this->enableModuleRoute($route);
    }

    /**
     * Disable the current module route.
     */
    public function disableModuleRoute($route): void
    {
        $route = studlyName((string) $route);

        $this->fireModuleEvent('disabling', $route);

        if ($this->usesFilesystemRouteStatusDriver()) {
            $this->moduleActivator->disable($route);
        } else {
            $this->moduleRouteStatusStore()->setEnabled($this->getStudlyName(), $route, false);
        }

        $this->flushModuleRouteRegistry();
        $this->flushModuleCache();

        $this->fireModuleEvent('disabled', $route);
    }

    /**
     * @deprecated Use {@see disableModuleRoute()} instead.
     */
    public function disableRoute($route): void
    {
        $this->disableModuleRoute($route);
    }

    /**
     * Get all routes of the module.
     *
     * @deprecated Use getRouteNames() instead
     */
    public function getRoutes(): array
    {
        return $this->getRouteNames();
    }

    /**
     * Studly route names from the status store (hot path — no ModuleRoute registry).
     *
     * Uses in-memory activator statuses when available; avoids ModuleRouteRegistry
     * and avoids {@see ModuleActivator::getRoutes()}
     * which re-reads JSON from disk on every call.
     *
     * @return list<string>
     */
    public function getRouteNames(): array
    {
        if ($this->routeNamesMemo !== null) {
            return $this->routeNamesMemo;
        }

        $statuses = $this->moduleActivator->readJson();
        if (! is_array($statuses)) {
            return $this->routeNamesMemo = [];
        }

        return $this->routeNamesMemo = array_values(array_map(
            static fn (string|int $key): string => studlyName((string) $key),
            array_keys($statuses)
        ));
    }

    /**
     * Check if a route exists in the module (hot path — no ModuleRoute registry).
     */
    public function hasRoute(string $routeName): bool
    {
        $wanted = studlyName($routeName);

        return in_array($wanted, $this->getRouteNames(), true)
            || in_array($routeName, $this->getRouteNames(), true);
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
     *
     * Filesystem driver (default): direct activator lookup — same cost as pre-ModuleRoute.
     * Database driver: status store.
     */
    public function isEnabledModuleRoute(string $route): bool
    {
        $route = studlyName($route);

        if ($this->usesFilesystemRouteStatusDriver()) {
            return $this->moduleActivator->hasStatus($route, true);
        }

        return $this->moduleRouteStatusStore()->isEnabled($this->getStudlyName(), $route);
    }

    /**
     * @deprecated Use {@see isEnabledModuleRoute()} instead.
     */
    public function isEnabledRoute(string $route): bool
    {
        return $this->isEnabledModuleRoute($route);
    }

    /**
     *  Determine whether the current module route not disabled.
     */
    public function isDisabledModuleRoute($route): bool
    {
        return ! $this->isEnabledModuleRoute((string) $route);
    }

    /**
     * @deprecated Use {@see isDisabledModuleRoute()} instead.
     */
    public function isDisabledRoute($route): bool
    {
        return $this->isDisabledModuleRoute($route);
    }

    /**
     * Default status driver is filesystem (activator JSON). Database uses the store adapter.
     */
    private function usesFilesystemRouteStatusDriver(): bool
    {
        $driver = mb_strtolower((string) modularousConfig('module_route_inspect.driver', 'filesystem'));

        return $driver === 'filesystem' || $driver === '';
    }

    /**
     * Resolved route status persistence adapter (filesystem or database).
     */
    private function moduleRouteStatusStore(): ModuleRouteStatusStoreInterface
    {
        return $this->app->make(ModuleRouteStatusStoreInterface::class);
    }

    /**
     * Registry of first-class {@see ModuleRoute} instances (config ∪ status keys).
     * Cached per Module instance for the request lifetime.
     */
    public function moduleRouteRegistry(): ModuleRouteRegistry
    {
        return $this->moduleRouteRegistryCache ??= new ModuleRouteRegistry(
            $this,
            $this->moduleRouteStatusStore(),
            $this->app->make(FeatureDetector::class),
        );
    }

    /**
     * Drop cached ModuleRoute registry (after enable/disable or config mutation).
     */
    public function flushModuleRouteRegistry(): void
    {
        $this->moduleRouteRegistryCache = null;
        $this->singletonMemo = [];
        $this->routeNamesMemo = null;
    }

    /**
     * @return Collection<string, ModuleRoute>
     */
    public function moduleRoutes()
    {
        return $this->moduleRouteRegistry()->all();
    }

    /**
     * ModuleRoute instances for config-listed routes (not status∪config union).
     *
     * Still builds {@see ModuleRouteRegistry} — do **not** use from admin sidebar
     * hot path; use {@see getRawRouteConfigs()} + {@see isSingleton()} instead.
     *
     * @see docs/src/pages/system-reference/adr-module-route-hot-path.md
     *
     * @return Collection<string, ModuleRoute>
     */
    public function sidebarRoutes()
    {
        $routes = collect();

        foreach ($this->getRawRouteConfigs(null, true) ?: [] as $key => $config) {
            if (! is_array($config)) {
                continue;
            }

            $name = $config['name'] ?? (is_string($key) ? $key : null);
            if (! is_string($name) || $name === '') {
                continue;
            }

            $route = $this->moduleRoute($name);
            if ($route !== null) {
                $routes->put($route->name(), $route);
            }
        }

        return $routes;
    }

    /**
     * Resolve a single module route by Studly or snake name.
     */
    public function moduleRoute(string $routeName): ?ModuleRoute
    {
        return $this->moduleRouteRegistry()->find($routeName);
    }

    /**
     * @deprecated Use {@see moduleRoute()} instead.
     */
    public function route(string $routeName): ?ModuleRoute
    {
        return $this->moduleRoute($routeName);
    }

    /**
     * Resolve a single module route by Studly or snake name.
     */
    public function find(string $routeName): ?ModuleRoute
    {
        return $this->moduleRouteRegistry()->find($routeName);
    }

    /**
     * @return Collection<string, ModuleRoute>
     */
    public function enabledModuleRoutes()
    {
        return $this->moduleRouteRegistry()->enabled();
    }

    /**
     * @deprecated Use {@see enabledModuleRoutes()} instead.
     *
     * @return Collection<string, ModuleRoute>
     */
    public function enabledRoutes()
    {
        return $this->enabledModuleRoutes();
    }

    /**
     * @deprecated Use {@see moduleRouteRegistry()} instead.
     */
    public function routeRegistry(): ModuleRouteRegistry
    {
        return $this->moduleRouteRegistry();
    }

    /**
     * @deprecated Use {@see moduleRoutes()} instead.
     *
     * @return Collection<string, ModuleRoute>
     */
    public function routes()
    {
        return $this->moduleRoutes();
    }

    /**
     * flushModuleCache
     */
    private function flushModuleCache(): void
    {

        if (modularousConfig('cache.enabled')) {
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

    /**
     * isModularousModule
     */
    public function isModularousModule(): bool
    {
        $modularousModulesPath = Modularous::getVendorPath('modules');

        return str_starts_with($this->getPath(), $modularousModulesPath);
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
     * getRawRouteConfigs
     *
     * @param mixed $notation
     * @param bool $valid
     */
    public function getRawRouteConfigs($notation = null, $valid = false): array
    {
        $notation = ! $notation ? $notation : ".{$notation}";

        return ($valid && ! $notation) ? Arr::where($this->getRawConfig('routes' . $notation), function ($item, $key) {
            return ! (! isset($item['name']));
        }) : $this->getRawConfig('routes' . $notation);
    }

    /**
     * getRawRouteConfig
     *
     * @param mixed $route_name
     */
    public function getRawRouteConfig($route_name): array
    {
        return $this->getRawRouteConfigs(snakeCase($route_name));
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
    public function getRouteInputs($route_name, $input_name = null): array
    {
        return $this->resolveRouteBlueprintField($route_name, 'inputs');
    }

    /**
     * getRouteInput
     *
     * @param mixed $route_name
     * @param mixed $input_name
     */
    public function getRouteInput($route_name, $input_name, string $field = 'name'): array
    {
        $inputs = $this->getRouteInputs($route_name);

        return Arr::first($inputs, fn ($item) => $item[$field] == $input_name);
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

    /**
     * getRawConfig
     *
     * @param mixed $notation
     */
    public function getRawConfig($notation = null, $default = []): mixed
    {
        if ($this->rawConfigCache === null) {
            if ($this->app['config']->has($this->getSnakeName())) {
                $fromRepository = $this->app['config']->get($this->getSnakeName());
                $this->rawConfigCache = is_array($fromRepository) ? $fromRepository : [];
            } else {
                $configFolder = GenerateConfigReader::read('config')->getPath();
                $configPath = $this->getDirectoryPath("{$configFolder}/config.php");

                $this->rawConfigCache = file_exists($configPath)
                    ? (include $configPath)
                    : [];
                if (! is_array($this->rawConfigCache)) {
                    $this->rawConfigCache = [];
                }
            }
        }

        return $notation ? data_get($this->rawConfigCache, $notation, $default) : $this->rawConfigCache;
    }

    /**
     * setConfig
     *
     * @param mixed $newConfig
     * @param mixed $notation
     */
    public function setConfig($newConfigValue, $notation = null): mixed
    {
        $notation = ! $notation ? '' : ".{$notation}";

        if (! $this->app['config']->has($this->getSnakeName()) && $this->app->runningInConsole() && file_exists($this->getDirectoryPath('Config/config.php'))) {
            $this->app['config']->set("{$this->getSnakeName()}", include ($this->getDirectoryPath('Config/config.php')));
        }

        return $this->app['config']->set("{$this->getSnakeName()}{$notation}", $newConfigValue);
    }

    /**
     * load  module config to the laravel config with the module snake name as the key
     */
    public function loadConfig(): void
    {
        $config_folder = GenerateConfigReader::read('config')->getPath();
        $configPath = $this->getDirectoryPath("{$config_folder}/config.php");

        if (file_exists($configPath)) {
            $config = include $configPath;
            $this->rawConfigCache = is_array($config) ? $config : [];
            $this->app['config']->set("{$this->getSnakeName()}", $this->rawConfigCache);
        }
    }

    public function loadCommands(): void
    {
        $command_folder = GenerateConfigReader::read('command')->getPath();
        $command_path = $this->getDirectoryPath("{$command_folder}/*.php");

        $cmds = [];

        foreach (glob($command_path) as $commandFile) {
            $filePath = realpath($commandFile);
            $fileContents = file_get_contents($filePath);
            // Extract namespace using regex
            if (preg_match('/namespace\s+([^;]+);/', $fileContents, $matches)) {
                $namespace = $matches[1];
                $className = basename($filePath, '.php');
                $cmds[] = $namespace . '\\' . $className;
            }
        }

        if (count($cmds) > 0) {
            Application::starting(function ($artisan) use ($cmds) {
                $artisan->resolveCommands($cmds);
            });
        }
    }

    /**
     * resetConfig
     */
    public function resetConfig(): void
    {
        $this->app['config']->set("{$this->getSnakeName()}", $this->getRawConfig());
    }

    /**
     * getParentRoute
     */
    public function getParentRoute(): array
    {
        return array_values(array_filter($this->getRawRouteConfigs(), function ($r) {
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

    /**
     * isParentRoute
     *
     * @param string $routeName
     */
    public function isParentRoute($routeName): bool
    {
        $parent = $this->getParentRoute();

        return $parent !== [] && ($parent['name'] ?? null) === studlyName((string) $routeName);
    }

    /**
     * isSingleton — class-string trait check only (no ModuleRoute registry, no App::make).
     *
     * Hot path: route registration, sidebar, controllers. Memoized per Module instance.
     *
     * @param string $routeName
     *
     * @deprecated Prefer ModuleRoute::isSingleton() when you already have a ModuleRoute
     */
    public function isSingleton($routeName): bool
    {
        $key = studlyName((string) $routeName);

        if (array_key_exists($key, $this->singletonMemo)) {
            return $this->singletonMemo[$key];
        }

        try {
            $modelClass = $this->getRouteClass($key, 'model');
            if (! is_string($modelClass) || $modelClass === '' || ! class_exists($modelClass)) {
                return $this->singletonMemo[$key] = false;
            }

            return $this->singletonMemo[$key] = classHasTrait(
                $modelClass,
                'Unusualify\Modularous\Entities\Traits\IsSingular'
            );
        } catch (\Throwable) {
            return $this->singletonMemo[$key] = false;
        }
    }

    /**
     * check if the route has remote api source (class-string only — hot path safe)
     */
    public function hasRemoteApiSource(string $routeName): bool
    {
        try {
            $repositoryClass = $this->getRouteClass($routeName, 'repository');
            $modelClass = $this->getRouteClass($routeName, 'model');
        } catch (\Throwable) {
            return false;
        }

        if (! is_string($repositoryClass) || ! is_string($modelClass)) {
            return false;
        }

        if (! class_exists($repositoryClass) || ! class_exists($modelClass)) {
            return false;
        }

        return classHasTrait($repositoryClass, RemoteApiSourceTrait::class)
            && classHasTrait($modelClass, HasRemoteApiSource::class);
    }

    /**
     * isResourceCacheEnabled (class-string only — hot path safe)
     */
    public function isResourceCacheEnabled(string $routeName): bool
    {
        try {
            $repositoryClass = $this->getRouteClass($routeName, 'repository');
            $controllerClass = $this->getTargetClassNamespace(
                'controller',
                studlyName($routeName) . 'Controller'
            );
        } catch (\Throwable) {
            return false;
        }

        if (! is_string($repositoryClass) || ! is_string($controllerClass)) {
            return false;
        }

        if (! class_exists($repositoryClass) || ! class_exists($controllerClass)) {
            return false;
        }

        if (! in_array(ResourceCacheActionsTrait::class, class_uses_recursive($repositoryClass), true)) {
            return false;
        }

        if (! in_array(ManageResourceCache::class, class_uses_recursive($controllerClass), true)) {
            return false;
        }

        return ModularousCache::hasAdminCacheActions($this->getName(), studlyName($routeName));
    }

    /**
     * hasSystemPrefix
     */
    public function hasSystemPrefix(): mixed
    {
        return $this->getRawConfig('system_prefix', false) ?? $this->getRawConfig('base_prefix', false);
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
        $name = getValueOrNull($this->getRawConfig('name')) ?? $this->getName();

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
        return snakeCase(getValueOrNull($this->getRawConfig('name')) ?? $this->getName());

        return $this->hasParentRoute()
            ? ($this->getParentRoute()['route_name'] ?? $this->getSnakeName())
            : snakeCase(getValueOrNull($this->getRawConfig('name')) ?? $this->getName());
    }

    /**
     * Route name prefix with system prefix
     *
     * @param bool $isParent
     */
    public function fullRouteNamePrefix($isParent = false): string
    {
        $prefixes = [];

        // if (($adminRouteNamePrefix = adminRouteNamePrefix())) {
        //     $prefixes[] = $adminRouteNamePrefix;
        // }

        if ($this->hasSystemPrefix()) {
            $prefixes[] = $this->systemRouteNamePrefix();
        }

        if (! $isParent) {
            $prefixes[] = $this->routeNamePrefix();
        }

        return implode('.', $prefixes);
    }

    /**
     * Route name prefix with panel prefix (admin)
     *
     * @param bool $isParent
     */
    public function panelRouteNamePrefix($isParent = false): string
    {
        $prefixes = [];

        if (($adminRouteNamePrefix = adminRouteNamePrefix())) {
            $prefixes[] = $adminRouteNamePrefix;
        }

        if ($fullRouteNamePrefix = $this->fullRouteNamePrefix($isParent)) {
            $prefixes[] = $fullRouteNamePrefix;
        }

        return implode('.', $prefixes) . '.';
    }

    /**
     * routeHasTable
     *
     * @param mixed $routeName
     * @param mixed $notation
     */
    public function routeHasTable($routeName = null, $notation = null): bool
    {
        $candidates = array_values(array_filter([
            $routeName !== null ? (string) $routeName : null,
            $notation !== null ? (string) $notation : null,
            $this->getStudlyName(),
        ], static fn ($value) => $value !== null && $value !== ''));

        foreach ($candidates as $candidate) {
            $route = $this->route($candidate);
            if ($route !== null) {
                return $route->hasTable();
            }
        }

        return false;
    }

    /**
     * Generate permission name from permission and route name
     *
     * @param mixed $permission
     * @param mixed $routeName
     */
    public function generatePermissionName(string $permission, string $routeName): string
    {
        return Permission::generatePermissionName($permission, $routeName);
    }

    /**
     * Generate permission middleware definition
     *
     * @param mixed $permission
     * @param mixed $routeName
     */
    public function generatePermissionMiddlewareDefinition(string $permission, string $routeName): string
    {
        return Permission::generatePermissionMiddlewareDefinition($permission, $routeName);
    }

    /**
     * Check if the user has the permission
     *
     * @param mixed $permissionName
     * @param mixed $routeName
     */
    public function userHasPermission(string $permissionName, string $routeName): bool
    {
        $user = Auth::guard(Modularous::getAuthGuardName())->user();

        if (! $user) {
            return false;
        }

        $permissionName = $this->generatePermissionName($permissionName, $routeName);

        return $user->hasPermission($permissionName);
    }

    /**
     * Check if the user is allowed to perform the permission
     *
     * @param mixed $permission
     * @param mixed $routeName
     */
    public function allowedPermission(string $permission, string $routeName)
    {
        $permissionName = $this->generatePermissionName($permission, $routeName);

        return Gate::allows($permissionName);
    }

    /**
     * getConfigPath
     */
    public function getConfigPath(): string
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

    /**
     * get all module urls
     */
    public function getModuleUrls(): array
    {
        $patterns = [$this->fullRouteNamePrefix()];

        $pr = $this->getParentRoute();

        if (isset($pr['route_name']) && $this->routeNamePrefix() != $pr['route_name']) {

            $prefixes = [];

            // $adminRouteNamePrefix = adminRouteNamePrefix();

            // if (($adminRouteNamePrefix = adminRouteNamePrefix())) {
            //     $prefixes[] = $adminRouteNamePrefix;
            // }

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

    /**
     * get all module route urls
     *
     * @param string $routeName
     * @param bool $panel
     */
    public function getRouteUrls($routeName): array
    {
        $isParentRoute = $this->isParentRoute($routeName);

        $mainQuoteParts = [];

        if (! $isParentRoute) {
            $mainQuoteParts[] = $this->fullRouteNamePrefix($isParentRoute);
        }

        $mainQuoteParts[] = snakeCase($routeName);

        $mainQuote = implode('.', $mainQuoteParts);

        $actionsQuote = $this->routeActionPattern();

        $quoteParts = [$mainQuote, $actionsQuote];

        $quote = implode('.', $quoteParts) . '$';

        $urls = Collection::make($this->getModuleUrls())->filter(fn ($uri, $name) => preg_match('/' . $quote . '/', $name));

        return $urls->toArray();
    }

    /**
     * Get the main URLs of the route.
     *
     * @param string $routeName
     * @param bool $withoutNamePrefix
     * @param string|null $modelBindingValue
     * @return array
     */
    public function getRoutePanelUrls($routeName, $withoutNamePrefix = false, $modelBindingValue = null)
    {
        $isParentRoute = $this->isParentRoute($routeName);

        $mainQuoteParts = [adminRouteNamePrefix()];

        if (! $isParentRoute) {
            $mainQuoteParts[] = $this->fullRouteNamePrefix($isParentRoute);
        }

        $mainQuoteParts[] = snakeCase($routeName);

        $mainQuote = implode('.', $mainQuoteParts);

        $actionsQuote = $this->routeActionPattern();

        $quoteParts = [$mainQuote, $actionsQuote];

        $quote = implode('.', $quoteParts) . '$';

        $urls = Collection::make($this->getModuleUrls())->filter(fn ($uri, $name) => preg_match('/' . $quote . '/', $name));

        if ($withoutNamePrefix) {
            $urls = $urls->mapWithKeys(function ($uri, $name) use ($routeName, $modelBindingValue) {
                $parts = explode('.', $name);
                $key = array_pop($parts);

                if ($modelBindingValue) {
                    $uri = str_replace('{' . Str::snake($routeName) . '}', $modelBindingValue, $uri);
                }

                return [$key => $uri];
            });
        }

        return $urls->toArray();
    }

    /**
     * getRouteActionUri
     */
    public function getRouteActionUrl(string $routeName, string $action, array $replacements = [], bool $absolute = false, bool $isPanel = true): string
    {
        $quote = '';

        if ($isPanel) {
            $quote = preg_quote(adminRouteNamePrefix() . '.');
        }
        $quote .= '([a-zA-Z_\.]+)';

        $quote .= preg_quote('.' . $action) . '$';

        $routes = Collection::make($this->getRouteUrls($routeName))->filter(fn ($url, $name) => preg_match('/' . $quote . '/', $name));
        $name = $routes->keys()->first();

        if (! $name) {
            throw new \Exception('Route not found for ' . $routeName . ' with action "' . $action . '" on module ' . $this->getName());
        }

        try {
            return route(name: $name, parameters: $replacements, absolute: $absolute);
        } catch (UrlGenerationException $e) {
            $relativeUrl = replace_curly_braces($routes->first(), $replacements);

            if ($absolute) {
                return url($relativeUrl);
            }

            return (str_starts_with($relativeUrl, '/')
                ? $relativeUrl
                : '/' . $relativeUrl) . (count($replacements) > 0 ? '?' . http_build_query($replacements) : '');
        } catch (\Throwable $th) {
            Log::error('Modularous route generation failed', [
                'module' => $this->getName(),
                'routeName' => $name ?? null,
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            throw new ModularousException(
                "Failed to generate route: {$th->getMessage()}",
                (int) $th->getCode(),
                $th
            );
        }
    }

    public function getParentNamespace(string $target): string
    {
        return $this->getBaseNamespace() . '\\' . GenerateConfigReader::read(kebabCase($target))->getNamespace();
    }

    /**
     * getTargetClassNamespace
     *
     * @param string|null $className
     */
    public function getTargetClassNamespace(string $target, $className = null): string
    {
        return $this->getBaseNamespace() . '\\' . GenerateConfigReader::read(kebabCase($target))->getNamespace() . ($className ? '\\' . $className : '');
    }

    /**
     * getTargetClassPath
     *
     * @param string|null $className
     */
    public function getTargetClassPath(string $target, $className = null): string
    {
        return $this->getDirectoryPath(GenerateConfigReader::read(kebabCase($target))->getPath()) . ($className ? '/' . $className : '');
    }

    /**
     * @param mixed $routeName
     * @param bool $asClass
     */
    public function getRepository($routeName, $asClass = true): Repository|string
    {
        $classNamespace = $this->getRouteClass($routeName, 'repository');

        if (! class_exists($classNamespace)) {
            return false;
        }

        return $asClass ? App::make($classNamespace) : $classNamespace;
    }

    /**
     * getModel
     *
     * @param mixed $routeName
     * @param bool $asClass
     */
    public function getModel($routeName, $asClass = true): Model|string
    {
        $repository = $this->getRepository($routeName);

        if (is_null($repository) || empty($repository) || ! class_exists(get_class($repository))) {
            throw new \Exception('Repository not found for ' . $routeName . ' on module ' . $this->getName());
        }

        $model = $repository->getModel();

        return $asClass ? $model : get_class($model);
    }

    /**
     * get Main Route Controller
     *
     * @param string $routeName
     * @param bool $asClass
     */
    public function getController($routeName, $asClass = true): Controller|string
    {
        $classNamespace = $this->getTargetClassNamespace('controller', Str::studly($routeName) . 'Controller');

        if (! class_exists($classNamespace)) {
            throw new \Exception('Controller not found for ' . $routeName . ' on module ' . $this->getName());
        }

        return $asClass ? App::make($classNamespace) : $classNamespace;
    }

    /**
     * getInertiaPagesPath
     *
     * @param string $routeName
     */
    public function getInertiaPagesPath($routeName): string
    {
        return $this->getDirectoryPath('Resources/assets/Pages/' . $routeName);
    }

    /**
     * hasInertiaPagesType
     *
     * @param string $routeName
     * @param string $type
     */
    public function hasInertiaPagesType($routeName, $type): bool
    {
        return file_exists($this->getInertiaPagesPath($routeName) . '/' . $type . '.vue');
    }

    /**
     * getInertiaPagesTypeName
     *
     * @param string $routeName
     * @param string $type
     */
    public function getInertiaPagesTypeName($routeName, $type): string
    {
        return $this->getName() . '/' . $routeName . '/' . $type;
    }

    /**
     * getRouteClass
     */
    public function getRouteClass(string $routeName, string $target, bool $asClass = false): string
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

    /**
     * getNavigationActions
     *
     * Custom row actions resolve nested-first (`index.row_actions`) / Blueprint when needed,
     * then legacy flat `table_row_actions`. Belongs-to nested link actions are appended.
     */
    public function getNavigationActions(string $routeName): array
    {
        $routeName = snakeCase($routeName); // snake case

        $navigationActions = [];

        $customActions = $this->resolveRouteBlueprintField($routeName, 'table_row_actions');

        foreach ($customActions as $customAction) {
            $navigationActions[] = $customAction;
        }

        foreach ($this->getRouteConfigs() as $key => $routeConfig) {
            if (isset($routeConfig['belongs']) && in_array($routeName, $routeConfig['belongs'])) {
                $nestedRouteSnake = snakeCase($routeConfig['name']);
                $routeSnake = snakeCase($routeName);

                $url = $this->getRouteActionUrl(nestedRouteNameFormat($routeName, $routeConfig['name']), 'index');

                $pattern = "\{$routeSnake\}";

                $navigationActions[] = [
                    'name' => 'link',
                    // 'url' => moduleRoute($routeConfig['name'],  $this->fullRouteNamePrefix() . '.' . $routeName . '.nested', 'index', [
                    //     $routeName => ':id',
                    // ]),
                    'url' => preg_replace('/(' . $pattern . ')/', ':id', $url),
                    'label' => 'modules.' . $nestedRouteSnake,
                    'icon' => '$modules',
                    'color' => 'green',
                ];

            }
        }

        return $navigationActions;
    }

    /**
     * Resolve a Blueprint presentation field for a route without forcing ModuleRoute
     * unless class/meta (or non-config global driver) requires it.
     *
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function resolveRouteBlueprintField(string $routeName, string $field): array
    {
        $snake = snakeCase($routeName);
        $raw = $this->getRawRouteConfig($snake);

        if ($raw === []) {
            return [];
        }

        if ($this->routeBlueprintFieldNeedsModuleRoute($raw, $field)) {
            $route = $this->moduleRoute(studlyName($routeName));
            if ($route === null) {
                return [];
            }

            return match ($field) {
                'table_row_actions' => $route->tableRowActions(),
                'filters' => $route->advancedFilters(),
                'table_filters' => $route->tableFilters(),
                'table_actions' => $route->tableActions(),
                'form_actions' => $route->formActions(),
                default => $route->presentation($field),
            };
        }

        return ModuleRoutePresentationResolver::readConfigPayloadFromArray($raw, $field);
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function routeBlueprintFieldNeedsModuleRoute(array $raw, string $field): bool
    {
        $globalDriver = mb_strtolower((string) modularousConfig('module_route_presentation.driver', 'config'));
        if ($globalDriver !== 'config' && $globalDriver !== '') {
            return true;
        }

        if (isset($raw['blueprint']) || isset($raw['presentation'])) {
            return true;
        }

        try {
            $nestedKey = ModuleRoutePresentationResolver::nestedConfigKey($field);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return ModuleRoutePresentationResolver::looksLikeProviderMeta(data_get($raw, $nestedKey));
    }

    /**
     * createMiddlewareAliases
     */
    public function createMiddlewareAliases()
    {
        foreach ($this->middlewares as $name => $middleware) {
            Route::aliasMiddleware($middleware['alias'], $middleware['class']);
        }
    }

    /**
     * getRouteMiddlewareAliases
     */
    public function getRouteMiddlewareAliases(string $routeName): array
    {
        $snakeName = snakeCase($routeName);

        $autoMiddlewares = [];

        if (isset($this->middlewares[$snakeName])) {
            $noAutoMiddleware = $this->getRawRouteConfig($routeName)['noAutoMiddleware'] ?? false;

            if (! $noAutoMiddleware) {
                $autoMiddlewares = [$this->middlewares[$snakeName]['alias']];
            }
        }

        $middlewares = $this->getRawRouteConfig($routeName)['middleware'] ?? [];

        return array_merge(
            $autoMiddlewares,
            $middlewares ?? $middlewares['middlewares'] ?? []
        );
    }
}
