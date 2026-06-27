<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
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
}
