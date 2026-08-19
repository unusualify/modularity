<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Traits\ManageRemoteApiSync;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;
use Unusualify\Modularous\Tests\TestCase;

class ManageRemoteApiSyncCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController($repository = null, $module = null): object
    {
        return new class($repository, $module)
        {
            use ManageRemoteApiSync;

            public $repository;

            public $module;

            public string $routeName = 'Item';

            public $tableActions = [];

            public function __construct($repository, $module)
            {
                $this->repository = $repository;
                $this->module = $module;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    private function makeRepoWithTrait(array $overrides = []): object
    {
        return new class($overrides)
        {
            use RemoteApiSourceTrait {
                remoteApiConnector as protected traitRemoteApiConnector;
            }

            public array $overrides;

            public function __construct(array $overrides)
            {
                $this->overrides = $overrides;
            }

            public function remoteApiConnector()
            {
                if (isset($this->overrides['connector'])) {
                    return $this->overrides['connector'];
                }

                throw new RemoteApiConfigurationException('missing connector');
            }

            public function getRemoteApiActionSchema(): array
            {
                return $this->overrides['schema'] ?? [];
            }

            public function syncFromRemote($id, $remoteId = null): array
            {
                if (($this->overrides['throwSync'] ?? false) === true) {
                    throw new RemoteApiSyncException('sync failed');
                }

                return $this->overrides['syncFromRemote'] ?? ['created' => true, 'model' => ['id' => $id]];
            }

            public function syncAllFromRemote(): array
            {
                if (($this->overrides['throwSync'] ?? false) === true) {
                    throw new RemoteApiSyncException('sync all failed');
                }

                return $this->overrides['syncAll'] ?? [
                    'total' => 2,
                    'created' => 1,
                    'updated' => 1,
                    'skipped' => 1,
                    'http_requests' => ['total' => 1, 'by_url' => []],
                    'skipped_records' => [['id' => 9]],
                ];
            }

            public function clearRemoteApiCache(): void
            {
                $this->overrides['cleared'] = true;
            }

            public function getById($id)
            {
                return $this->overrides['record'] ?? (object) ['id' => $id];
            }

            public function previewRemote($remoteId)
            {
                return $this->overrides['preview'] ?? ['remote_id' => $remoteId];
            }

            public function listRemoteCatalog(?string $catalogKey = null): array
            {
                return $this->overrides['catalog'] ?? [['id' => 1]];
            }

            // Moduleable stubs required by RemoteApiSourceTrait composition
            public function resolveRemoteApiModuleName(): string
            {
                return 'Test';
            }

            public function resolveRemoteApiRouteName(): string
            {
                return 'Item';
            }
        };
    }

    /** @test */
    public function appends_withs_and_table_actions_guard_on_connector(): void
    {
        $controller = $this->makeController(null, null);
        $this->assertFalse($controller->call('repositoryUsesRemoteApiSource'));
        $controller->call('setTableActionsManageRemoteApiSync');
        $this->assertSame([], $controller->tableActions);
        $this->assertSame([], $controller->call('addWithsManageRemoteApiSync'));
        $this->assertSame([], $controller->addFormAppendsManageRemoteApiSync());
        $this->assertSame([], $controller->addIndexAppendsManageRemoteApiSync());

        $disabledConfig = Mockery::mock();
        $disabledConfig->shouldReceive('isEnabled')->andReturn(false);
        $disabledConnector = Mockery::mock();
        $disabledConnector->shouldReceive('configuration')->andReturn($disabledConfig);

        $repoDisabled = $this->makeRepoWithTrait(['connector' => $disabledConnector]);
        $module = Mockery::mock();
        $module->shouldReceive('panelRouteNamePrefix')->andReturn('admin.test.');

        $controller = $this->makeController($repoDisabled, $module);
        $this->assertTrue($controller->call('repositoryUsesRemoteApiSource'));
        $this->assertFalse($controller->call('remoteApiConnectorIsEnabled'));
        $controller->call('setTableActionsManageRemoteApiSync');
        $this->assertSame([], $controller->tableActions);
        $this->assertSame([], $controller->call('addWithsManageRemoteApiSync'));
        $this->assertSame([], $controller->addIndexAppendsManageRemoteApiSync());

        $throwingRepo = $this->makeRepoWithTrait([]);
        $controller = $this->makeController($throwingRepo, $module);
        $this->assertFalse($controller->call('remoteApiConnectorIsEnabled'));

        $enabledConfig = Mockery::mock();
        $enabledConfig->shouldReceive('isEnabled')->andReturn(true);
        $enabledConnector = Mockery::mock();
        $enabledConnector->shouldReceive('configuration')->andReturn($enabledConfig);

        $emptySchemaRepo = $this->makeRepoWithTrait([
            'connector' => $enabledConnector,
            'schema' => [],
        ]);
        $emptySchemaController = $this->makeController($emptySchemaRepo, $module);
        $emptySchemaController->call('setTableActionsManageRemoteApiSync');
        $this->assertSame([], $emptySchemaController->tableActions);

        $noActionsRepo = $this->makeRepoWithTrait([
            'connector' => $enabledConnector,
            'schema' => [
                ['scope' => 'table', 'name' => ''],
                ['scope' => 'row', 'name' => 'row_only'],
            ],
        ]);
        $noActionsController = $this->makeController($noActionsRepo, $module);
        $noActionsController->call('setTableActionsManageRemoteApiSync');
        $this->assertSame([], $noActionsController->tableActions);
    }

    /** @test */
    public function enabled_connector_merges_table_actions_and_appends(): void
    {
        $config = Mockery::mock();
        $config->shouldReceive('isEnabled')->andReturn(true);
        $connector = Mockery::mock();
        $connector->shouldReceive('configuration')->andReturn($config);

        $repo = $this->makeRepoWithTrait([
            'connector' => $connector,
            'schema' => [
                ['scope' => 'row', 'name' => 'ignored'],
                ['name' => ''],
                [
                    'scope' => 'table',
                    'name' => 'sync_all',
                    'label' => 'Sync all',
                    'color' => 'secondary',
                    'forceLabel' => true,
                    'table_action_extras' => ['reloadOnSuccess' => true],
                ],
            ],
        ]);

        $module = Mockery::mock();
        $module->shouldReceive('panelRouteNamePrefix')->andReturn('admin.test.');

        $controller = $this->makeController($repo, $module);
        $controller->tableActions = [['name' => 'existing']];
        $controller->call('setTableActionsManageRemoteApiSync');

        $this->assertCount(2, $controller->tableActions);
        $this->assertSame('sync_all', $controller->tableActions[1]['name']);
        $this->assertTrue($controller->tableActions[1]['forceLabel']);
        $this->assertTrue($controller->tableActions[1]['reloadOnSuccess']);
        $this->assertSame(['remoteApiSource'], $controller->call('addWithsManageRemoteApiSync'));
        $this->assertSame(['remote_api_last_sync'], $controller->addFormAppendsManageRemoteApiSync());
        $this->assertSame(['remote_api_last_sync'], $controller->addIndexAppendsManageRemoteApiSync());

        $mapped = $controller->call('mapRemoteApiTableAction', ['name' => 'x'], 'admin.test.item.');
        $this->assertSame('admin.test.item.x', $mapped['endpoint']);
        $this->assertNull($controller->call('mapRemoteApiTableAction', ['name' => ''], 'p.'));
    }

    /** @test */
    public function sync_preview_catalog_and_cache_endpoints(): void
    {
        $config = Mockery::mock();
        $config->shouldReceive('isEnabled')->andReturn(true);
        $connector = Mockery::mock(\Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector::class);
        $connector->shouldReceive('configuration')->andReturn($config);
        $connector->shouldReceive('previewResponseDisplay')->andReturn('json');
        $connector->shouldReceive('buildPreviewResponseDisplay')->andReturn(['pretty' => true]);

        $record = new class
        {
            public function getRemoteApiId()
            {
                return 'remote-9';
            }
        };

        $repo = $this->makeRepoWithTrait([
            'connector' => $connector,
            'record' => $record,
            'preview' => ['title' => 'Remote'],
            'catalog' => [['id' => 1], ['id' => 2]],
        ]);

        $controller = $this->makeController($repo);

        $created = $controller->syncRemote(Request::create('/', 'POST', ['remote_id' => 5]), 1);
        $this->assertStringContainsString('created', $created->getData(true)['message']);

        $all = $controller->syncRemoteAll();
        $this->assertStringContainsString('Skipped', $all->getData(true)['message']);
        $this->assertSame(2, $all->getData(true)['meta']['total'] ?? $all->getData(true)['data']['total']);

        $cache = $controller->clearRemoteCache();
        $this->assertSame('Remote API cache cleared.', $cache->getData(true)['message']);

        $preview = $controller->previewRemote(Request::create('/'), 3);
        $this->assertSame('json', $preview->getData(true)['display_mode']);
        $this->assertTrue($preview->getData(true)['display']['pretty']);

        $catalog = $controller->listRemoteCatalog(Request::create('/', 'GET', ['catalog' => 'main']));
        $this->assertSame(2, $catalog->getData(true)['meta']['total']);

        $failingRepo = $this->makeRepoWithTrait([
            'connector' => $connector,
            'throwSync' => true,
        ]);
        $failing = $this->makeController($failingRepo);

        try {
            $failing->syncRemote(Request::create('/'), 1);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('remote_api', $e->errors());
        }

        try {
            $failing->syncRemoteAll();
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('remote_api', $e->errors());
        }
    }
}
