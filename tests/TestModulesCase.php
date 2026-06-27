<?php

namespace Unusualify\Modularous\Tests;

use Unusualify\Modularous\Activators\ModularousActivator;

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

        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            realpath(__DIR__ . '/../test-modules'),
        ]);

        $app['config']->set('modules.paths.modules', realpath(__DIR__ . '/../test-modules') ?: __DIR__ . '/../test-modules');

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

        $statusesFile = 'modules_statuses.json';
        if (getenv('TEST_TOKEN')) {
            $statusesFile = 'modules_statuses_' . getenv('TEST_TOKEN') . '.json';
        } elseif (function_exists('getmypid')) {
            $statusesFile = 'modules_statuses_' . getmypid() . '.json';
        }

        $this->statusesFilePath = base_path($statusesFile);

        $app['files']->put($this->statusesFilePath, json_encode([
            'TestModule' => true,
            'SystemModule' => true,
        ]));
        $app['config']->set('modules.activators.modularous', [
            'class' => ModularousActivator::class,
            'statuses-file' => $this->statusesFilePath,
            'cache-key' => 'modularous.activator.installed',
            'cache-lifetime' => 604800,
        ]);
        // $app['config']->set('modules.activator', 'modularous');
    }
}
