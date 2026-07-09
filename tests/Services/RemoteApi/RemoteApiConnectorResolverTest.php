<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\ConfigurableRemoteApiAdapter;
use Unusualify\Modularous\Services\RemoteApi\DefaultRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorFactory;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorResolver;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiFieldMapper;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRecordDto;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiConnectorResolverTest extends TestCase
{
    private RemoteApiConnectorResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        config(['modularous.remote_api.base_url' => 'http://api.example.test/v1']);
        $this->resolver = new RemoteApiConnectorResolver;
    }

    public function test_resolve_class_returns_route_configured_classes(): void
    {
        $module = $this->makeModule([
            'api_connector' => [
                'classes' => [
                    'adapter' => ConfigurableRemoteApiAdapter::class,
                    'dto' => RemoteApiRecordDto::class,
                ],
            ],
        ]);

        $this->assertSame(
            ConfigurableRemoteApiAdapter::class,
            $this->resolver->resolveClass($module, 'package', 'adapter'),
        );
        $this->assertSame(
            RemoteApiRecordDto::class,
            $this->resolver->resolveClass($module, 'package', 'dto'),
        );
    }

    public function test_resolve_connector_class_falls_back_to_default_for_invalid_connector(): void
    {
        $module = $this->makeModule([
            'api_connector' => [
                'classes' => [
                    'connector' => RemoteApiRecordDto::class,
                ],
            ],
        ]);

        $this->assertSame(
            DefaultRemoteApiConnector::class,
            $this->resolver->resolveConnectorClass($module, 'package'),
        );
    }

    public function test_configuration_uses_route_config_for_default_connector(): void
    {
        $module = $this->makeModule([
            'api_connector' => [
                'enabled' => true,
                'endpoint' => 'packages',
            ],
        ]);

        Modularous::shouldReceive('findOrFail')
            ->once()
            ->with('BusinessPackage')
            ->andReturn($module);

        $configuration = $this->resolver->configuration('BusinessPackage', 'Package');

        $this->assertInstanceOf(RemoteApiConfiguration::class, $configuration);
        $this->assertSame('packages', $configuration->endpoint());
    }

    public function test_resolve_adapter_instantiates_configured_adapter(): void
    {
        $module = $this->makeModule();
        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'classes' => [
                'adapter' => ConfigurableRemoteApiAdapter::class,
            ],
        ]);

        $adapter = $this->resolver->resolveAdapter(
            $module,
            'package',
            $configuration,
            new RemoteApiFieldMapper($configuration),
        );

        $this->assertInstanceOf(ConfigurableRemoteApiAdapter::class, $adapter);
    }

    public function test_resolve_delegates_to_factory(): void
    {
        $connector = Mockery::mock(DefaultRemoteApiConnector::class);

        $factory = Mockery::mock(RemoteApiConnectorFactory::class);
        $factory->shouldReceive('make')
            ->once()
            ->with('BusinessPackage', 'Package')
            ->andReturn($connector);

        $this->app->instance(RemoteApiConnectorFactory::class, $factory);

        $this->assertSame($connector, $this->resolver->resolve('BusinessPackage', 'Package'));
    }

    /**
     * @param array<string, mixed> $routeConfig
     */
    private function makeModule(array $routeConfig = []): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getStudlyName')->andReturn('BusinessPackage');
        $module->shouldReceive('getRouteConfig')->with('package')->andReturn($routeConfig);

        return $module;
    }
}
