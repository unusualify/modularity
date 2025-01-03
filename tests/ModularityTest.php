<?php

namespace Unusualify\Modularity\Tests;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Modularity;

class ModularityTest extends TestCase
{
    protected $modularity;
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Container();
        $this->app->instance('cache', app('cache'));
        $this->app->instance('config', app('config'));

        $path = base_path('packages/modularity');
        dd($path);

        $this->modularity = new Modularity($this->app);
    }

    public function _test_scan_paths_are_properly_formatted()
    {
        $paths = $this->modularity->getScanPaths();

        foreach ($paths as $path) {
            $this->assertTrue(str_ends_with($path, '/*'));
        }
    }

    public function _test_cache_can_be_disabled()
    {
        $this->modularity->disableCache();

        $this->assertFalse(config('modules.cache.enabled'));
    }

    public function _test_cache_can_be_cleared()
    {
        Config::set('modules.cache.enabled', true);
        Config::set('modules.cache.key', 'test-modules-cache');

        Cache::shouldReceive('forget')
            ->once()
            ->with('test-modules-cache');

        $this->modularity->clearCache();
    }

    public function _test_can_get_grouped_modules()
    {
        // Mock a system module
        $modules = [
            'test-module' => new \Unusualify\Modularity\Module('test-module', '/path/to/module'),
        ];

        $this->modularity->shouldReceive('allEnabled')
            ->once()
            ->andReturn($modules);

        $systemModules = $this->modularity->getSystemModules();

        $this->assertIsArray($systemModules);
    }

    public function _test_can_delete_module()
    {
        $moduleName = 'test-module';

        // Test non-existent module
        $this->assertFalse($this->modularity->deleteModule($moduleName));

        // Test existing module
        $module = new \Unusualify\Modularity\Module($moduleName, '/path/to/module');
        $this->modularity->shouldReceive('all')
            ->once()
            ->andReturn([$module]);

        $module->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->modularity->deleteModule($moduleName));
    }

    public function _test_can_get_vendor_path()
    {
        $path = $this->modularity->getVendorPath('test');
        $this->assertIsString($path);
        $this->assertTrue(str_contains($path, 'test'));
    }

    public function _test_can_get_vendor_namespace()
    {
        $namespace = $this->modularity->getVendorNamespace('Test');
        $this->assertIsString($namespace);
    }
}
