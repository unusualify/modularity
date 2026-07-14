<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiAdapterInterface;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiCache;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiClient;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiPreviewResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_configuration_preview_defaults_to_name_and_description(): void
    {
        $configuration = $this->makeConfiguration([]);

        $this->assertSame('fields', $configuration->previewResponseDisplay());
        $this->assertSame([
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
        ], $configuration->previewResponseFields());
    }

    public function test_connector_build_preview_response_display_resolves_nested_keys(): void
    {
        $connector = $this->makeConnector([
            'preview' => [
                'fields' => [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'packageable.name', 'label' => 'Region'],
                ],
            ],
        ]);

        $display = $connector->buildPreviewResponseDisplay([
            'name' => 'Starter',
            'packageable' => ['name' => 'Europe'],
        ]);

        $this->assertSame([
            ['key' => 'name', 'label' => 'Name', 'value' => 'Starter'],
            ['key' => 'packageable.name', 'label' => 'Region', 'value' => 'Europe'],
        ], $display);
    }

    public function test_connector_customize_preview_response_display_can_override_values(): void
    {
        $connector = new class($this->makeConfiguration(['preview' => ['fields' => [['key' => 'name', 'label' => 'Name']]]])) extends AbstractRemoteApiConnector
        {
            public function __construct(RemoteApiConfiguration $configuration)
            {
                parent::__construct(
                    $configuration,
                    Mockery::mock(RemoteApiClient::class),
                    Mockery::mock(RemoteApiCache::class),
                    Mockery::mock(RemoteApiAdapterInterface::class),
                );
            }

            public static function remoteApiConfiguration(): array
            {
                return ['enabled' => true, 'endpoint' => 'packages'];
            }

            protected function customizePreviewResponseDisplay(array $fields, array $payload): array
            {
                $fields[0]['value'] = 'Customized: ' . ($fields[0]['value'] ?? '');

                return $fields;
            }
        };

        $this->assertSame([
            ['key' => 'name', 'label' => 'Name', 'value' => 'Customized: Starter'],
        ], $connector->buildPreviewResponseDisplay(['name' => 'Starter']));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function makeConfiguration(array $config): RemoteApiConfiguration
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return new RemoteApiConfiguration($module, 'package', array_merge([
            'enabled' => true,
            'endpoint' => 'packages',
        ], $config));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function makeConnector(array $config): AbstractRemoteApiConnector
    {
        return new class($this->makeConfiguration($config)) extends AbstractRemoteApiConnector
        {
            public function __construct(RemoteApiConfiguration $configuration)
            {
                parent::__construct(
                    $configuration,
                    Mockery::mock(RemoteApiClient::class),
                    Mockery::mock(RemoteApiCache::class),
                    Mockery::mock(RemoteApiAdapterInterface::class),
                );
            }

            public static function remoteApiConfiguration(): array
            {
                return ['enabled' => true, 'endpoint' => 'packages'];
            }
        };
    }
}
