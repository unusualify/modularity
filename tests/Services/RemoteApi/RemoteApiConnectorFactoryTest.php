<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\ConfigurableRemoteApiAdapter;
use Unusualify\Modularous\Services\RemoteApi\DefaultRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorFactory;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorResolver;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiConnectorFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['modularous.remote_api.base_url' => 'http://api.example.test/v1']);
    }

    public function test_make_builds_default_connector_with_resolver_dependencies(): void
    {
        $module = $this->makeModule([
            'enabled' => true,
            'endpoint' => 'packages',
            'classes' => [
                'adapter' => ConfigurableRemoteApiAdapter::class,
                'connector' => DefaultRemoteApiConnector::class,
            ],
        ]);

        Modularous::shouldReceive('findOrFail')
            ->twice()
            ->with('BusinessPackage')
            ->andReturn($module);

        $resolver = new RemoteApiConnectorResolver;
        $factory = new RemoteApiConnectorFactory($resolver, new RemoteApiRateLimiter);

        $connector = $factory->make('BusinessPackage', 'Package');

        $this->assertInstanceOf(DefaultRemoteApiConnector::class, $connector);
        $this->assertSame('packages', $connector->configuration()->endpoint());
    }

    /**
     * @param array<string, mixed> $apiConnectorConfig
     */
    private function makeModule(array $apiConnectorConfig = []): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getStudlyName')->andReturn('BusinessPackage');
        $module->shouldReceive('getName')->andReturn('BusinessPackage');
        $module->shouldReceive('getRouteConfig')->with('package')->andReturn([
            'api_connector' => $apiConnectorConfig,
        ]);

        return $module;
    }
}
