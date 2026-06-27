<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mockery;
use Unusualify\Modularous\Entities\RemoteApiSource;
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\RemoteApi\ConfigurableRemoteApiAdapter;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiAttributePartition;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiFieldMapper;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiSynchronizer;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiSynchronizerTest extends TestCase
{
    public function test_sync_record_updates_local_and_remote_partitions(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'remote_id_column' => 'remote_id',
            'mapping' => [
                'remote_id' => 'id',
                'synced_name' => 'name',
                'is_featured' => 'is_featured',
                'remote_payload' => '@raw',
                'remote_synced_at' => '@now',
            ],
            'fields' => [
                'is_featured' => ['local' => true, 'sync' => false],
            ],
            'sync' => [
                'preserve_local_fields' => ['is_featured'],
            ],
        ]);

        $adapter = new ConfigurableRemoteApiAdapter($configuration, new RemoteApiFieldMapper($configuration));

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldReceive('fetchOne')->with(42)->andReturn([
            'id' => 42,
            'name' => 'Premium API',
            'is_featured' => false,
        ]);
        $connector->shouldReceive('mapRow')->andReturnUsing(
            fn (array $row, array $existing = []) => $adapter->mapToAttributes($row, $existing)
        );

        $remoteSource = Mockery::mock(RemoteApiSource::class)->makePartial();
        $remoteSource->remote_id = 42;
        $remoteSource->synced_attributes = ['synced_name' => 'Old'];
        $remoteSource->shouldReceive('toMergedAttributes')->andReturn([
            'remote_id' => 42,
            'synced_name' => 'Old',
        ]);
        $remoteSource->shouldReceive('updateQuietly')->once()->with(Mockery::on(function (array $attributes) {
            return $attributes['remote_id'] === 42
                && ($attributes['synced_attributes']['synced_name'] ?? null) === 'Premium API';
        }));

        $morphRelation = Mockery::mock(MorphOne::class);

        $existing = Mockery::mock(SyncTestModel::class)->makePartial();
        $existing->forceFill([
            'id' => 1,
            'is_featured' => true,
        ]);
        $existing->setRelation('remoteApiSource', $remoteSource);
        $existing->shouldReceive('remoteApiSource')->andReturn($morphRelation);
        $existing->shouldReceive('fresh')->with(['remoteApiSource'])->andReturnSelf();

        $query = Mockery::mock();
        $query->shouldReceive('whereHas')->once()->andReturnSelf();
        $query->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $query->shouldReceive('first')->andReturn($existing);

        $model = Mockery::mock(SyncTestModel::class);
        $model->shouldReceive('newQuery')->andReturn($query);
        $model->shouldReceive('getFillable')->andReturn(['name', 'published', 'is_featured']);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);
        $repository->shouldReceive('update')->once()->with(1, ['is_featured' => true])->andReturn($existing);

        $result = (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->syncRecord($connector, $repository, 42);

        $this->assertFalse($result['created']);
    }

    public function test_preview_sync_all_reports_local_linked_records_without_http(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'remote_id_column' => 'remote_id',
        ]);

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldNotReceive('fetchList');
        $connector->shouldNotReceive('fetchOne');

        $remoteSource = Mockery::mock(RemoteApiSource::class)->makePartial();
        $remoteSource->remote_id = 42;

        $linked = Mockery::mock(SyncTestModel::class)->makePartial();
        $linked->forceFill(['id' => 1, 'name' => 'Premium']);
        $linked->setRelation('remoteApiSource', $remoteSource);
        $linked->shouldReceive('getRemoteApiId')->andReturn(42);
        $linked->shouldReceive('getKey')->andReturn(1);

        $unlinked = Mockery::mock(SyncTestModel::class)->makePartial();
        $unlinked->forceFill(['id' => 2, 'name' => 'Draft']);
        $unlinked->setRelation('remoteApiSource', null);
        $unlinked->shouldReceive('getRemoteApiId')->andReturn(null);
        $unlinked->shouldReceive('getKey')->andReturn(2);

        $query = Mockery::mock();
        $query->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $query->shouldReceive('get')->andReturn(collect([$linked, $unlinked]));

        $model = Mockery::mock(SyncTestModel::class);
        $model->shouldReceive('newQuery')->andReturn($query);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $preview = (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->previewSyncAll($connector, $repository);

        $this->assertSame('GET http://app.b2press.test/api/v1/packages (paginated list)', $preview['would_fetch']);
        $this->assertCount(1, $preview['records']);
        $this->assertSame(42, $preview['records'][0]['remote_id']);
        $this->assertSame('update', $preview['records'][0]['action']);
        $this->assertCount(1, $preview['without_remote_id']);
        $this->assertSame(1, $preview['summary']['would_update']);
        $this->assertSame(1, $preview['summary']['without_remote_id']);
    }

    public function test_preview_sync_record_reports_create_when_no_local_match(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'show_endpoint' => 'packages/{id}',
            'remote_id_column' => 'remote_id',
        ]);

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldNotReceive('fetchOne');

        $query = Mockery::mock();
        $query->shouldReceive('whereHas')->once()->andReturnSelf();
        $query->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $query->shouldReceive('first')->andReturn(null);

        $model = Mockery::mock(SyncTestModel::class);
        $model->shouldReceive('newQuery')->andReturn($query);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $preview = (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->previewSyncRecord($connector, $repository, 99);

        $this->assertSame('create', $preview['action']);
        $this->assertSame(99, $preview['remote_id']);
        $this->assertNull($preview['local_id']);
        $this->assertSame('GET http://app.b2press.test/api/v1/packages/99', $preview['would_fetch']);
    }

    public function test_sync_all_hydrates_local_linked_records_from_paginated_list(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'remote_id_column' => 'remote_id',
            'mapping' => [
                'remote_id' => 'id',
                'remote_payload' => '@raw',
                'remote_synced_at' => '@now',
            ],
            'sync' => [
                'import_new_from_list' => false,
            ],
        ]);

        $adapter = new ConfigurableRemoteApiAdapter($configuration, new RemoteApiFieldMapper($configuration));

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldReceive('resetRequestStats')->once();
        $connector->shouldReceive('flushRequestStats')->once()->andReturn([
            'total' => 1,
            'by_url' => ['http://app.b2press.test/api/v1/packages' => 1],
        ]);
        $connector->shouldReceive('fetchList')->once()->andReturn([
            ['id' => 42, 'name' => 'Premium API'],
        ]);
        $connector->shouldNotReceive('fetchOne');
        $connector->shouldReceive('mapRow')->andReturnUsing(
            fn (array $row, array $existing = []) => $adapter->mapToAttributes($row, $existing)
        );

        $remoteSource = Mockery::mock(RemoteApiSource::class)->makePartial();
        $remoteSource->remote_id = 42;
        $remoteSource->shouldReceive('toMergedAttributes')->andReturn(['remote_id' => 42]);
        $remoteSource->shouldReceive('updateQuietly')->once();

        $linked = Mockery::mock(SyncTestModel::class)->makePartial();
        $linked->forceFill(['id' => 1, 'name' => 'Premium']);
        $linked->setRelation('remoteApiSource', $remoteSource);
        $linked->shouldReceive('getRemoteApiId')->andReturn(42);
        $linked->shouldReceive('getKey')->andReturn(1);
        $linked->shouldReceive('fresh')->with(['remoteApiSource'])->andReturnSelf();

        $existingQuery = Mockery::mock();
        $existingQuery->shouldReceive('whereHas')->once()->andReturnSelf();
        $existingQuery->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $existingQuery->shouldReceive('first')->andReturn($linked);

        $allQuery = Mockery::mock();
        $allQuery->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $allQuery->shouldReceive('get')->andReturn(collect([$linked]));

        $model = Mockery::mock(SyncTestModel::class);
        $model->shouldReceive('newQuery')->andReturn($allQuery, $existingQuery);
        $model->shouldReceive('getFillable')->andReturn(['name', 'published']);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $result = (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->syncAll($connector, $repository);

        $this->assertSame(1, $result['total']);
        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame([], $result['skipped_records']);
        $this->assertSame(1, $result['http_requests']['total']);
    }

    public function test_sync_all_skips_stale_linked_records_without_fetch_one(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'remote_id_column' => 'remote_id',
            'mapping' => [
                'remote_id' => 'id',
                'remote_payload' => '@raw',
                'remote_synced_at' => '@now',
            ],
            'sync' => [
                'import_new_from_list' => false,
            ],
        ]);

        $adapter = new ConfigurableRemoteApiAdapter($configuration, new RemoteApiFieldMapper($configuration));

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldReceive('resetRequestStats')->once();
        $connector->shouldReceive('flushRequestStats')->once()->andReturn([
            'total' => 1,
            'by_url' => ['http://app.b2press.test/api/v1/packages' => 1],
        ]);
        $connector->shouldReceive('fetchList')->once()->andReturn([
            ['id' => 42, 'name' => 'Premium API'],
        ]);
        $connector->shouldNotReceive('fetchOne');
        $connector->shouldReceive('mapRow')->andReturnUsing(
            fn (array $row, array $existing = []) => $adapter->mapToAttributes($row, $existing)
        );

        $staleRemoteSource = Mockery::mock(RemoteApiSource::class)->makePartial();
        $staleRemoteSource->remote_id = 274;

        $staleLinked = Mockery::mock(SyncTestModel::class)->makePartial();
        $staleLinked->forceFill(['id' => 2, 'name' => 'Stale']);
        $staleLinked->setRelation('remoteApiSource', $staleRemoteSource);
        $staleLinked->shouldReceive('getRemoteApiId')->andReturn(274);
        $staleLinked->shouldReceive('getKey')->andReturn(2);

        $remoteSource = Mockery::mock(RemoteApiSource::class)->makePartial();
        $remoteSource->remote_id = 42;
        $remoteSource->shouldReceive('toMergedAttributes')->andReturn(['remote_id' => 42]);
        $remoteSource->shouldReceive('updateQuietly')->once();

        $linked = Mockery::mock(SyncTestModel::class)->makePartial();
        $linked->forceFill(['id' => 1, 'name' => 'Premium']);
        $linked->setRelation('remoteApiSource', $remoteSource);
        $linked->shouldReceive('getRemoteApiId')->andReturn(42);
        $linked->shouldReceive('getKey')->andReturn(1);
        $linked->shouldReceive('fresh')->with(['remoteApiSource'])->andReturnSelf();

        $existingQuery = Mockery::mock();
        $existingQuery->shouldReceive('whereHas')->once()->andReturnSelf();
        $existingQuery->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $existingQuery->shouldReceive('first')->andReturn($linked);

        $allQuery = Mockery::mock();
        $allQuery->shouldReceive('with')->with('remoteApiSource')->andReturnSelf();
        $allQuery->shouldReceive('get')->andReturn(collect([$linked, $staleLinked]));

        $model = Mockery::mock(SyncTestModel::class);
        $model->shouldReceive('newQuery')->andReturn($allQuery, $existingQuery);
        $model->shouldReceive('getFillable')->andReturn(['name', 'published']);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $result = (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->syncAll($connector, $repository);

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(274, $result['skipped_records'][0]['remote_id']);
        $this->assertSame('not_in_remote_list', $result['skipped_records'][0]['reason']);
    }

    public function test_sync_record_throws_when_remote_record_is_missing(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'remote_id_column' => 'remote_id',
        ]);

        $connector = Mockery::mock(RemoteApiConnectorInterface::class);
        $connector->shouldReceive('configuration')->andReturn($configuration);
        $connector->shouldReceive('fetchOne')->with(274)->andReturn(null);

        $repository = Mockery::mock(Repository::class);

        $this->expectException(\Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API record [274] was not found.');

        (new RemoteApiSynchronizer(new RemoteApiAttributePartition()))->syncRecord($connector, $repository, 274);
    }
}

class SyncTestModel extends Model
{
    use HasRemoteApiSource;

    protected $table = 'packages';

    protected $guarded = [];
}
