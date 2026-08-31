<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Mockery;
use Unusualify\Modularous\Entities\RemoteApiSource;
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;
use Unusualify\Modularous\Observers\RemoteApiSourceableObserver;
use Unusualify\Modularous\Tests\TestCase;

class HasRemoteApiSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_boot_registers_configurable_observer(): void
    {
        $this->assertSame(
            RemoteApiSourceableObserver::class,
            RemoteApiSourceTestModel::resolveRemoteApiSourceableObserverForTest()
        );
    }

    public function test_hydrates_synced_attributes_as_virtual_fields(): void
    {
        $source = new RemoteApiSource([
            'remote_id' => 42,
            'synced_attributes' => [
                'synced_name' => 'Premium API',
                'price_formatted' => '€500',
            ],
            'remote_synced_at' => '2026-06-21 12:00:00',
        ]);

        $model = new RemoteApiSourceTestModel([
            'id' => 1,
            'name' => 'CMS Name',
        ]);
        $model->setRelation('remoteApiSource', $source);
        $model->hydrateRemoteApiAttributes();

        $this->assertSame('Premium API', $model->getAttribute('synced_name'));
        $this->assertSame('€500', $model->getAttribute('price_formatted'));
        $this->assertSame(42, $model->getRemoteApiId());
        $this->assertContains('synced_name', $model->getRemoteApiVirtualKeys());
    }

    public function test_saving_persists_dirty_virtual_attributes_to_morph_and_strips_them(): void
    {
        $source = new RecordingRemoteApiSource([
            'remote_id' => 42,
            'synced_attributes' => ['synced_name' => 'Old Name'],
        ]);

        $model = new RemoteApiSourceTestModel([
            'id' => 1,
            'name' => 'CMS Name',
        ]);
        $model->exists = true;
        $model->setRelation('remoteApiSource', $source);
        $model->hydrateRemoteApiAttributes();
        $model->syncOriginal();
        $model->setAttribute('synced_name', 'Updated Name');

        $model->persistRemoteApiVirtualAttributes();
        $model->stripRemoteApiVirtualAttributes();

        $this->assertSame(['synced_name' => 'Updated Name'], $source->lastUpdate['synced_attributes']);
        $this->assertFalse($model->offsetExists('synced_name'));
    }

    public function test_fill_registers_remote_id_as_virtual_attribute(): void
    {
        $model = new RemoteApiSourceTestModel([
            'name' => 'CMS Name',
        ]);

        $model->fill(['remote_id' => 99]);

        $this->assertContains('remote_id', $model->getRemoteApiVirtualKeys());
        $this->assertSame(99, $model->getAttribute('remote_id'));
    }

    public function test_virtual_attributes_are_excluded_from_dirty_parent_persistence(): void
    {
        $source = new RemoteApiSource([
            'remote_id' => 42,
            'synced_attributes' => [
                'synced_name' => 'Premium API',
            ],
        ]);

        $model = new RemoteApiSourceTestModel([
            'id' => 1,
            'name' => 'CMS Name',
        ]);
        $model->exists = true;
        $model->setRelation('remoteApiSource', $source);
        $model->hydrateRemoteApiAttributes();
        $model->syncOriginal();
        $model->setAttribute('synced_name', 'Updated Name');

        $this->assertSame('Updated Name', $model->getAttribute('synced_name'));
        $this->assertArrayNotHasKey('synced_name', $model->getDirty());
    }

    public function test_to_remote_api_array_merges_source_payload(): void
    {
        $source = new RemoteApiSource([
            'remote_id' => 11,
            'synced_attributes' => ['synced_name' => 'API'],
            'remote_payload' => ['raw' => 1],
            'remote_synced_at' => '2026-01-01 00:00:00',
        ]);

        $model = new RemoteApiSourceTestModel([
            'id' => 1,
            'name' => 'Local',
        ]);
        $model->setRelation('remoteApiSource', $source);

        $array = $model->toRemoteApiArray();
        $this->assertSame(11, $array['remote_id']);
        $this->assertSame('API', $array['synced_name']);
        $this->assertSame(['raw' => 1], $array['remote_payload']);
        $this->assertEquals($array, $model->toRemoteApiPresentationArray());
    }

    public function test_remote_api_last_sync_chip_never_and_synced_paths(): void
    {
        $model = new RemoteApiSourceTestModel(['id' => 1, 'name' => 'Local']);
        $model->setRelation('remoteApiSource', null);

        $never = $model->exposeFormatRemoteApiLastSyncChip(null);
        $this->assertStringContainsString('mdi-sync-off', $never);

        $synced = $model->exposeFormatRemoteApiLastSyncChip(Carbon::parse('2026-06-21 12:00:00'));
        $this->assertStringContainsString('mdi-sync', $synced);
        $this->assertStringContainsString('2026-06-21', $synced);

        session(['modularous_timezone' => 'Not/AZone']);
        $this->assertSame('UTC', $model->exposeResolveRemoteApiDisplayTimezone());

        session(['modularous_timezone' => 'Europe/Istanbul']);
        $this->assertSame('Europe/Istanbul', $model->exposeResolveRemoteApiDisplayTimezone());
    }

    public function test_get_remote_api_id_prefers_virtual_then_relation(): void
    {
        $model = new RemoteApiSourceTestModel(['name' => 'Local']);
        $model->fill(['remote_id' => 77]);
        $this->assertSame(77, $model->getRemoteApiId());
        $this->assertSame('remote_id', $model->getRemoteApiIdColumn());
    }
}

class RecordingRemoteApiSource extends RemoteApiSource
{
    /**
     * @var array<string, mixed>
     */
    public array $lastUpdate = [];

    public function update(array $attributes = [], array $options = [])
    {
        $this->lastUpdate = $attributes;

        return true;
    }

    public function updateQuietly(array $attributes = [], array $options = [])
    {
        return $this->update($attributes, $options);
    }
}

class RemoteApiSourceTestModel extends Model
{
    use HasRemoteApiSource;

    protected $table = 'packages';

    protected $guarded = [];

    public static function resolveRemoteApiSourceableObserverForTest(): string
    {
        return static::resolveRemoteApiSourceableObserver();
    }

    public function exposeFormatRemoteApiLastSyncChip(?Carbon $syncedAt): string
    {
        return $this->formatRemoteApiLastSyncChip($syncedAt);
    }

    public function exposeResolveRemoteApiDisplayTimezone(): string
    {
        return $this->resolveRemoteApiDisplayTimezone();
    }
}
