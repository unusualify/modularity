<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Mockery;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiAdapterInterface;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiCache;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiClient;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiSourceTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_form_actions_remote_api_source_trait_returns_record_level_actions(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['sync_record', 'sync_all', 'clear_cache', 'preview'],
            enabled: true,
            routePrefix: 'admin.business_package.package.',
        );

        $actions = $repository->getFormActionsRemoteApiSourceTrait(Mockery::mock(User::class));

        $this->assertArrayHasKey('syncRemote', $actions);
        $this->assertArrayHasKey('previewRemote', $actions);
        $this->assertArrayNotHasKey('syncRemoteAll', $actions);
        $this->assertArrayNotHasKey('clearRemoteCache', $actions);

        $this->assertSame('request', $actions['syncRemote']['type']);
        $this->assertSame('put', $actions['syncRemote']['method']);
        $this->assertSame('admin.business_package.package.syncRemote', $actions['syncRemote']['endpoint']);
        $this->assertFalse($actions['syncRemote']['creatable']);
        $this->assertTrue($actions['syncRemote']['editable']);
        $this->assertTrue($actions['syncRemote']['reloadOnSuccess']);
        $this->assertFalse($actions['syncRemote']['hasConfirmation']);
        $this->assertSame([['remote_id', 'exists']], $actions['syncRemote']['conditions']);

        $this->assertSame('request', $actions['previewRemote']['type']);
        $this->assertSame('put', $actions['previewRemote']['method']);
        $this->assertSame('admin.business_package.package.previewRemote', $actions['previewRemote']['endpoint']);
        $this->assertArrayHasKey('responseModalAttributes', $actions['previewRemote']);
        $this->assertSame('fields', $actions['previewRemote']['responseDisplay']);
        $this->assertSame([
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
        ], $actions['previewRemote']['responseFields']);
    }

    public function test_get_form_actions_remote_api_source_trait_uses_connector_preview_config(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['preview'],
            enabled: true,
            routePrefix: 'admin.business_package.package_region.',
            routeName: 'PackageRegion',
            connector: $this->makePreviewConnector(
                display: 'fields',
                fields: [
                    ['key' => 'name', 'label' => 'Region name'],
                    ['key' => 'description', 'label' => 'Region description'],
                ],
            ),
        );

        $actions = $repository->getFormActionsRemoteApiSourceTrait(Mockery::mock(User::class));

        $this->assertSame('fields', $actions['previewRemote']['responseDisplay']);
        $this->assertSame([
            ['key' => 'name', 'label' => 'Region name'],
            ['key' => 'description', 'label' => 'Region description'],
        ], $actions['previewRemote']['responseFields']);
    }

    public function test_get_form_actions_remote_api_source_trait_returns_empty_when_connector_disabled(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['sync_record', 'preview'],
            enabled: false,
            routePrefix: 'admin.business_package.package.',
        );

        $this->assertSame([], $repository->getFormActionsRemoteApiSourceTrait(Mockery::mock(User::class)));
    }

    public function test_get_remote_api_action_schema_supports_clear_cache_only(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['clear_cache'],
            enabled: true,
            routePrefix: 'admin.use_case.use_case.',
            moduleName: 'UseCase',
            routeName: 'UseCase',
        );

        $schema = $repository->getRemoteApiActionSchema();

        $this->assertCount(1, $schema);
        $this->assertSame('clearRemoteCache', $schema[0]['name']);
        $this->assertSame('table', $schema[0]['scope']);
        $this->assertSame([], $repository->getFormActionsRemoteApiSourceTrait(Mockery::mock(User::class)));
    }

    public function test_resolve_remote_api_route_prefix_appends_route_name_for_child_route(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['preview'],
            enabled: true,
            routePrefix: 'admin.business_package.package.',
        );

        $this->assertSame('admin.business_package.package.', $repository->resolveRemoteApiRoutePrefix());
    }

    public function test_resolve_remote_api_route_prefix_uses_panel_prefix_for_parent_route(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['preview'],
            enabled: true,
            routePrefix: 'admin.use_case.',
            moduleName: 'UseCase',
            routeName: 'UseCase',
            isParent: true,
        );

        $this->assertSame('admin.use_case.', $repository->resolveRemoteApiRoutePrefix());
    }

    public function test_get_form_actions_remote_api_source_trait_returns_empty_when_module_missing(): void
    {
        $repository = $this->makeRepositoryWithConnector(
            actions: ['sync_record', 'preview'],
            enabled: true,
            routePrefix: 'admin.business_package.package.',
            withModule: false,
        );

        $this->assertNull($repository->resolveRemoteApiRoutePrefix());
        $this->assertSame([], $repository->getFormActionsRemoteApiSourceTrait(Mockery::mock(User::class)));
    }

    /**
     * @param list<string> $actions
     */
    private function makeRepositoryWithConnector(
        array $actions,
        bool $enabled,
        string $routePrefix,
        ?RemoteApiConnectorInterface $connector = null,
        string $moduleName = 'BusinessPackage',
        string $routeName = 'Package',
        bool $isParent = false,
        bool $withModule = true,
    ): RemoteApiSourceTraitTestRepository {
        if ($connector === null) {
            $configuration = Mockery::mock(RemoteApiConfiguration::class);
            $configuration->shouldReceive('isEnabled')->andReturn($enabled);
            $configuration->shouldReceive('actions')->andReturn($actions);

            $connector = Mockery::mock(RemoteApiConnectorInterface::class);
            $connector->shouldReceive('configuration')->andReturn($configuration);
        }

        if ($withModule) {
            $this->mockRemoteApiModule($moduleName, $routeName, $routePrefix, $isParent);
        } else {
            Modularous::shouldReceive('find')->with($moduleName)->andReturn(null);
        }

        $repository = new RemoteApiSourceTraitTestRepository(new RemoteApiSourceTraitTestModel);
        $repository->setModuleName($moduleName);
        $repository->setRouteName($routeName);
        $repository->setRemoteApiConnector($connector);

        return $repository;
    }

    private function mockRemoteApiModule(
        string $moduleName,
        string $routeName,
        string $routePrefix,
        bool $isParent,
    ): void {
        $panelPrefix = $isParent
            ? $routePrefix
            : mb_substr($routePrefix, 0, -(mb_strlen(snakeCase($routeName)) + 1));

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('isParentRoute')->with($routeName)->andReturn($isParent);
        $module->shouldReceive('panelRouteNamePrefix')->andReturn($panelPrefix);

        Modularous::shouldReceive('find')->with($moduleName)->andReturn($module);
    }

    private function makePreviewConnector(string $display, array $fields): RemoteApiSourceTraitPreviewConnector
    {
        return new RemoteApiSourceTraitPreviewConnector($display, $fields);
    }
}

class RemoteApiSourceTraitPreviewConnector extends AbstractRemoteApiConnector
{
    public function __construct(string $display, array $fields)
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package_region', [
            'enabled' => true,
            'endpoint' => 'packages/package-regions',
            'actions' => ['preview'],
            'preview' => [
                'display' => $display,
                'fields' => $fields,
            ],
        ]);

        parent::__construct(
            $configuration,
            Mockery::mock(RemoteApiClient::class),
            Mockery::mock(RemoteApiCache::class),
            Mockery::mock(RemoteApiAdapterInterface::class),
        );
    }

    public static function remoteApiConfiguration(): array
    {
        return [
            'enabled' => true,
            'endpoint' => 'packages/package-regions',
        ];
    }
}

class RemoteApiSourceTraitTestRepository extends Repository
{
    use RemoteApiSourceTrait;

    private ?RemoteApiConnectorInterface $remoteApiConnector = null;

    public function __construct(RemoteApiSourceTraitTestModel $model)
    {
        $this->model = $model;
    }

    public function setRemoteApiConnector(RemoteApiConnectorInterface $connector): void
    {
        $this->remoteApiConnector = $connector;
    }

    public function remoteApiConnector(): RemoteApiConnectorInterface
    {
        if ($this->remoteApiConnector === null) {
            throw new \RuntimeException('Remote API connector not configured for test.');
        }

        return $this->remoteApiConnector;
    }
}

class RemoteApiSourceTraitTestModel extends Model
{
    protected $table = 'remote_api_source_trait_test_models';

    protected $guarded = [];
}
