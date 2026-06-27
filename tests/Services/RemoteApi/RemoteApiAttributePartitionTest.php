<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiAttributePartition;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiAttributePartitionTest extends TestCase
{
    public function test_partitions_mapped_attributes_into_local_and_remote_payloads(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'mapping' => [
                'remote_id' => 'id',
                'synced_name' => 'name',
                'price_formatted' => 'price_formatted',
                'remote_payload' => '@raw',
                'remote_synced_at' => '@now',
            ],
            'fields' => [
                'is_featured' => ['local' => true, 'sync' => false],
                'name' => ['local' => true, 'sync' => false],
            ],
            'sync' => [
                'preserve_local_fields' => ['is_featured', 'name'],
            ],
        ]);

        $model = new class extends Model
        {
            protected $guarded = [];

            public function getFillable(): array
            {
                return ['name', 'published', 'is_featured', 'position', 'legacy_id', 'package_country_id'];
            }
        };

        $partitioner = new RemoteApiAttributePartition();
        $result = $partitioner->partition($model, $configuration, [
            'remote_id' => 42,
            'synced_name' => 'Premium API',
            'price_formatted' => '€500',
            'is_featured' => false,
            'name' => 'CMS Name',
            'remote_payload' => ['id' => 42],
            'remote_synced_at' => '2026-06-21 12:00:00',
        ], [
            'id' => 1,
            'is_featured' => true,
            'name' => 'CMS Name',
        ]);

        $this->assertSame([
            'is_featured' => true,
            'name' => 'CMS Name',
        ], $result['local']);
        $this->assertSame(42, $result['remote']['remote_id']);
        $this->assertSame('Premium API', $result['remote']['synced_attributes']['synced_name']);
        $this->assertSame('€500', $result['remote']['synced_attributes']['price_formatted']);
        $this->assertSame(['id' => 42], $result['remote']['remote_payload']);
    }
}
