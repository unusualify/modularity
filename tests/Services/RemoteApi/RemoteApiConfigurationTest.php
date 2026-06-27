<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Tests\TestCase;
use Mockery;
use Unusualify\Modularous\Module;

class RemoteApiConfigurationTest extends TestCase
{
    public function test_base_url_falls_back_to_b2press_config(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $configuration = $this->makeConfiguration([
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $this->assertSame('http://app.b2press.test/api/v1', $configuration->baseUrl());
    }

    public function test_route_config_overrides_global_base_url(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $configuration = $this->makeConfiguration([
            'enabled' => true,
            'endpoint' => 'packages',
            'base_url' => 'https://business.b2press.com/api/v1',
        ]);

        $this->assertSame('https://business.b2press.com/api/v1', $configuration->baseUrl());
    }

    public function test_from_route_config_validates_enabled_endpoint_and_base_url(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $configuration = RemoteApiConfiguration::fromRouteConfig(
            $this->makeModule(),
            'package',
            [
                'enabled' => true,
                'endpoint' => 'packages',
            ],
        );

        $this->assertSame('packages', $configuration->endpoint());
    }

    /**
     * @param array<string, mixed> $connectorConfig
     */
    private function makeConfiguration(array $connectorConfig): RemoteApiConfiguration
    {
        return new RemoteApiConfiguration($this->makeModule(), 'package', $connectorConfig);
    }

    private function makeModule(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return $module;
    }
}
