<?php

namespace Unusualify\Modularous\Tests;

use Unusualify\Modularous\Activators\ModularousActivator;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

abstract class TestModulesCase extends TestCase
{
    protected $statusesFilePath;

    protected function setUp(): void
    {
        parent::setUp();

    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();

        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            $fixturesPath,
        ]);

        $app['config']->set('modules.paths.modules', $fixturesPath);

        $generatorPaths = [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => false],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'resource' => ['path' => 'Transformers', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
        ];

        $modularousGeneratorPaths = array_merge(config('modules.paths.generator'), $generatorPaths, [
            'route-controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
        ]);

        $app['config']->set('modules.paths.generator', $modularousGeneratorPaths);
        $app['config']->set('modularous.paths.generator', $modularousGeneratorPaths);
        $app['config']->set('modularous.base_key', 'modularous');
        $app['config']->set('modularous.stubs.path', realpath(__DIR__ . '/../src/Console/stubs'));

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
    }

    protected function writeModuleActivationStatuses(array $statuses): void
    {
        $this->app['files']->put($this->statusesFilePath, json_encode($statuses, JSON_PRETTY_PRINT));
    }
}
