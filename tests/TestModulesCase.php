<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularous\Activators\ModularousActivator;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Support\ModularousRoutes;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

abstract class TestModulesCase extends TestCase
{
    protected $statusesFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureModulePanelRoutesRegistered();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        IsolatedTestModules::sync();
        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();

        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.scan.paths', [
            $fixturesPath,
        ]);

        $app['config']->set('modules.paths.modules', $fixturesPath);

        $this->applyTestFixtureGeneratorPaths($app);

        $app['config']->set('modularous.base_key', 'modularous');
        $app['config']->set('modularous.stubs.path', realpath(__DIR__ . '/../src/Console/stubs'));
        $app['config']->set('modularous.define_panel_routes_on_frontend_requests', true);

        $this->statusesFilePath = base_path('modules_statuses_' . IsolatedTestModules::testTokenSuffix() . '.json');

        $app['files']->put($this->statusesFilePath, json_encode([
            'TestModule' => true,
            'SystemModule' => true,
        ]));
        $app['config']->set('modules.activators.modularous', [
            'class' => ModularousActivator::class,
            'statuses-file' => $this->statusesFilePath,
            'cache-key' => 'modularous.activator.installed.' . IsolatedTestModules::testTokenSuffix(),
            'cache-lifetime' => 604800,
        ]);
        $app['config']->set('modules.activator', 'modularous');
        $this->rebindModularousActivator($app);
    }

    protected function writeModuleActivationStatuses(array $statuses): void
    {
        $this->app['files']->put(
            $this->statusesFilePath,
            json_encode($statuses, JSON_PRETTY_PRINT)
        );

        $this->resetScannedModulesCache();

        $activator = $this->app->make(ActivatorInterface::class);

        foreach (Modularous::all() as $module) {
            $enabled = (bool) ($statuses[$module->getName()] ?? false);

            if ($enabled) {
                $activator->enable($module);
            } else {
                $activator->disable($module);
            }
        }
    }

    /**
     * @param list<string> $moduleNames
     */
    protected function enableOnlyModules(string ...$moduleNames): void
    {
        $statuses = [];

        foreach (Modularous::all() as $module) {
            $statuses[$module->getName()] = in_array($module->getName(), $moduleNames, true);
        }

        foreach ($moduleNames as $moduleName) {
            $statuses[$moduleName] = true;
        }

        $this->writeModuleActivationStatuses($statuses);
    }

    protected function resetScannedModulesCache(): void
    {
        $property = new \ReflectionProperty(FileRepository::class, 'modules');
        $property->setValue(null, null);
    }

    protected function ensureModulePanelRoutesRegistered(): void
    {
        foreach (['TestModule', 'SystemModule'] as $moduleName) {
            $module = Modularous::find($moduleName);

            if (! $module instanceof Module || ! $module->hasRoute('Item')) {
                continue;
            }

            if ($this->moduleRouteActionIsAvailable($module, 'Item', 'index')) {
                continue;
            }

            $this->registerModulePanelRoutes($module);

            if ($this->moduleRouteActionIsAvailable($module, 'Item', 'index')) {
                continue;
            }

            $this->registerStubModuleRouteAction($module, 'Item', 'index');
        }
    }

    protected function moduleRouteActionIsAvailable(Module $module, string $routeName, string $action): bool
    {
        try {
            $module->getRouteActionUrl($routeName, $action);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function registerModulePanelRoutes(Module $module): void
    {
        $modularousRoutes = app(ModularousRoutes::class);
        $groupOptions = $modularousRoutes->groupOptions();
        $controllerNamespace = GenerateConfigReader::read('controller')->getPath();

        Route::group(
            array_merge($groupOptions, [
                'middleware' => $modularousRoutes->webPanelMiddlewares(),
                'namespace' => $module->getClassNamespace($controllerNamespace),
            ]),
            function () use ($module) {
                Route::moduleRoutes($module);
            }
        );
    }

    protected function registerStubModuleRouteAction(Module $module, string $routeName, string $action): void
    {
        $routeFullName = $module->panelRouteNamePrefix() . '.' . snakeCase($routeName) . '.' . $action;

        if (Route::has($routeFullName)) {
            return;
        }

        $routeConfig = $module->getRawRouteConfig(snakeCase($routeName));
        $urlSegment = $routeConfig['url'] ?? pluralize(kebabCase($routeName));
        $uri = trim($module->fullPrefix() . '/' . $urlSegment, '/');

        Route::get($uri, static fn () => '')->name($routeFullName);
    }

    private function applyTestFixtureGeneratorPaths($app): void
    {
        $generatorPaths = [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => false],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'model' => ['path' => 'Entities', 'namespace' => 'Entities', 'generate' => false],
            'repository' => ['path' => 'Repositories', 'namespace' => 'Repositories', 'generate' => false],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Controllers', 'namespace' => 'Controllers', 'generate' => false],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'resource' => ['path' => 'Transformers', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
        ];

        $modularousGeneratorPaths = array_merge(config('modules.paths.generator'), $generatorPaths, [
            'route-controller' => ['path' => 'Controllers', 'namespace' => 'Controllers', 'generate' => false],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
        ]);

        $app['config']->set('modules.paths.generator', $modularousGeneratorPaths);
        $app['config']->set('modularous.paths.generator', $modularousGeneratorPaths);
    }
}
