<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Support\Facades\File;
use TestModules\TestModule\Repositories\ItemRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\TestModulesCase;
use Unusualify\Modularous\Traits\ResolveConnector;

class ResolveConnectorTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();

        // Only enable TestModule for fixture-based tests
        $statusFilePath = config('modules.activators.modularous.statuses-file');
        File::put($statusFilePath, json_encode(['TestModule' => true], JSON_PRETTY_PRINT));

        $module = MockModuleManager::getTestModule();
        $statusesFile = $module->getDirectoryPath('routes_statuses.json');
        if (! is_file($statusesFile)) {
            file_put_contents($statusesFile, '{}');
        }
        file_put_contents($statusesFile, json_encode(['Item' => true], JSON_PRETTY_PRINT));
    }

    protected function createTester(): object
    {
        return new class
        {
            use ResolveConnector;

            public function runFindConnectorRepository($connector)
            {
                return $this->findConnectorRepository($connector);
            }

            public function runFindNewConnectorRepository($connector)
            {
                return $this->findNewConnectorRepository($connector);
            }
        };
    }

    public function test_find_connector_repository_returns_repository_from_module(): void
    {
        $mockModule = \Mockery::mock(Module::class);
        $mockRepo = \Mockery::mock(Repository::class);
        $mockModule->shouldReceive('getRepository')->with('Payment')->once()->andReturn($mockRepo);

        Modularous::shouldReceive('findOrFail')->with('SystemPayment')->once()->andReturn($mockModule);

        $tester = $this->createTester();
        $result = $tester->runFindConnectorRepository('SystemPayment:Payment');

        $this->assertSame($mockRepo, $result);
    }

    public function test_find_new_connector_repository_returns_repository_via_connector(): void
    {
        $mockRepo = \Mockery::mock(Repository::class);
        Modularous::shouldReceive('hasModule')->with('SystemPayment')->andReturn(true);
        Modularous::shouldReceive('find')->with('SystemPayment')->andReturnUsing(function () use ($mockRepo) {
            $module = \Mockery::mock(Module::class);
            $module->shouldReceive('hasRoute')->with('Payment')->andReturn(true);
            $module->shouldReceive('getRepository')->with('Payment', true)->andReturn($mockRepo);

            return $module;
        });

        $tester = $this->createTester();
        $result = $tester->runFindNewConnectorRepository('SystemPayment|Payment');

        $this->assertSame($mockRepo, $result);
    }

    public function test_find_connector_repository_returns_item_repository_from_test_module(): void
    {
        $tester = $this->createTester();
        $result = $tester->runFindConnectorRepository('TestModule:Item');

        $this->assertInstanceOf(ItemRepository::class, $result);
    }

    public function test_find_new_connector_repository_returns_item_repository_from_test_module(): void
    {
        $tester = $this->createTester();
        $result = $tester->runFindNewConnectorRepository('TestModule|Item');

        $this->assertInstanceOf(ItemRepository::class, $result);
    }
}
