<?php

namespace Unusualify\Modularity\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Module;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Unusualify\Modularity\Activators\ModularityActivator;
use Unusualify\Modularity\Facades\Modularity;

class ModularityActivatorTest extends OrchestraTestCase
{
    private $activator;
    private $filesystem;
    private $config;
    private $cache;
    private $statusesFile;
    private $moduleStatuses;

    public function setUp(): void
    {
        parent::setUp();

        $this->statusesFile = base_path('modules_statuses.json');

        $this->moduleStatuses = [
            "SystemUser" => true,
            "SystemPricing" => true,
            "SystemPayment" => true,
            "SystemUtility" => true,
            "SystemNotification" => true,
            "SystemSetting" => true,
        ];

        // Mock filesystem
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instance('files', $this->filesystem);

        // Mock config
        $this->config = $this->createMock(Config::class);
        $this->config->method('get')
            ->willReturnMap([
                ['modules.activators.modularity.statuses-file', null, $this->statusesFile],
                ['modules.activators.modularity.cache-key', null, 'modules.statuses'],
                ['modules.activators.modularity.cache-lifetime', null, 3600],
                ['modules.cache.enabled', null, false],
                ['modules.cache.driver', null, 'file'],
            ]);
        $this->app->instance('config', $this->config);

        // Mock cache store
        $cacheStore = $this->createMock(\Illuminate\Contracts\Cache\Repository::class);
        $cacheStore->method('remember')
            ->with('modules.statuses', 3600, $this->anything())
            ->willReturn($this->moduleStatuses);

        // Mock cache manager
        $this->cache = $this->createMock(CacheManager::class);
        $this->cache->method('store')
            ->with('file')
            ->willReturn($cacheStore);

        $this->app->instance('cache', $this->cache);

        // Create activator instance
        $this->activator = new ModularityActivator($this->app);
    }

    public function test_activator_initialization()
    {
        $this->assertInstanceOf(\Nwidart\Modules\Contracts\ActivatorInterface::class, $this->activator);
    }

    public function test_get_modules_statuses_when_file_exists()
    {
        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($this->statusesFile)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('get')
            ->with($this->statusesFile)
            ->willReturn(json_encode($this->moduleStatuses));

        $statuses = $this->activator->getModulesStatuses();
        $this->assertEquals($this->moduleStatuses, $statuses);
    }

    public function test_get_modules_statuses_when_file_does_not_exist()
    {
        // Setup filesystem mock for this specific test
        $this->filesystem->expects($this->any())
            ->method('exists')
            ->with($this->statusesFile)
            ->willReturn(false);

        // Create activator instance after setting up the mock
        $this->activator = new ModularityActivator($this->app);

        $statuses = $this->activator->getModulesStatuses();
        $this->assertEquals([], $statuses);
    }

    public function test_enable_module()
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('TestModule');

        $this->filesystem->expects($this->once())
            ->method('put')
            ->with(
                $this->statusesFile,
                $this->callback(function ($content) {
                    $decoded = json_decode($content, true);
                    return isset($decoded['TestModule']) && $decoded['TestModule'] === true;
                })
            );

        $this->activator->enable($module);
    }

    public function test_disable_module()
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('TestModule');

        $this->filesystem->expects($this->once())
            ->method('put')
            ->with(
                $this->statusesFile,
                $this->callback(function ($content) {
                    $decoded = json_decode($content, true);
                    return isset($decoded['TestModule']) && $decoded['TestModule'] === false;
                })
            );

        $this->activator->disable($module);
    }

    public function test_has_status()
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('SystemUser');

        $this->filesystem->method('exists')->willReturn(true);
        $this->filesystem->method('get')->willReturn(json_encode($this->moduleStatuses));

        $this->assertTrue($this->activator->hasStatus($module, true));
        $this->assertFalse($this->activator->hasStatus($module, false));
    }

    public function test_delete_module()
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('SystemUser');

        $this->filesystem->method('exists')->willReturn(true);
        $this->filesystem->method('get')->willReturn(json_encode($this->moduleStatuses));
        $this->filesystem->expects($this->once())
            ->method('put')
            ->with(
                $this->statusesFile,
                $this->callback(function ($content) {
                    $decoded = json_decode($content, true);
                    return !isset($decoded['SystemUser']);
                })
            );

        $this->activator->delete($module);
    }

    public function test_reset()
    {
        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($this->statusesFile)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('delete')
            ->with($this->statusesFile);

        $this->activator->reset();
    }

    // public function test_flush_cache()
    // {
    //     // Mock cache store
    //     $cacheStore = $this->createMock(\Illuminate\Contracts\Cache\Repository::class);
    //     $cacheStore->expects($this->once())
    //         ->method('forget')
    //         ->with('modules.statuses');

    //     // Mock cache manager
    //     $this->cache->expects($this->once())
    //         ->method('store')
    //         ->with('file')
    //         ->willReturn($cacheStore);

    //     $this->activator->flushCache();
    // }

    public function test_cache_is_used_when_enabled()
    {
        // Override config mock to enable cache
        $this->config = $this->createMock(Config::class);
        $this->config->method('get')
            ->willReturnMap([
                ['modules.activators.modularity.statuses-file', null, $this->statusesFile],
                ['modules.activators.modularity.cache-key', null, 'modules.statuses'],
                ['modules.activators.modularity.cache-lifetime', null, 3600],
                ['modules.cache.enabled', null, true],
                ['modules.cache.driver', null, 'file'],
            ]);
        $this->app->instance('config', $this->config);

        // Mock cache store
        $cacheStore = $this->createMock(\Illuminate\Contracts\Cache\Repository::class);
        $cacheStore->expects($this->any())
            ->method('remember')
            ->with('modules.statuses', 3600, $this->anything())
            ->willReturn($this->moduleStatuses);

        // Mock cache manager
        $this->cache->expects($this->any())
            ->method('store')
            ->with('file')
            ->willReturn($cacheStore);

        // Create new instance with updated config
        $activator = new ModularityActivator($this->app);
        $result = $activator->getModulesStatuses();

        $this->assertEquals($this->moduleStatuses, $result);
    }
}
