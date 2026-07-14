<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiFieldMapper;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiFieldMapperTest extends TestCase
{
    public function test_maps_dot_paths_and_transformers(): void
    {
        $mapper = $this->makeMapper([
            'remote_id' => 'id',
            'synced_name' => 'name',
            'packageable_type' => 'packageable.type',
            'remote_payload' => '@raw',
        ]);

        $row = [
            'id' => 42,
            'name' => 'Premium',
            'packageable' => ['type' => 'country', 'id' => 7],
        ];

        $attributes = $mapper->map($row);

        $this->assertSame(42, $attributes['remote_id']);
        $this->assertSame('Premium', $attributes['synced_name']);
        $this->assertSame('country', $attributes['packageable_type']);
        $this->assertSame($row, $attributes['remote_payload']);
    }

    public function test_preserves_local_fields_from_existing_attributes(): void
    {
        $mapper = $this->makeMapper(
            [
                'remote_id' => 'id',
                'synced_name' => 'name',
                'is_featured' => 'is_featured',
            ],
            [
                'is_featured' => ['local' => true, 'sync' => false],
            ],
            ['is_featured']
        );

        $attributes = $mapper->map(
            ['id' => 1, 'name' => 'API Name', 'is_featured' => false],
            ['is_featured' => true]
        );

        $this->assertTrue($attributes['is_featured']);
        $this->assertSame('API Name', $attributes['synced_name']);
    }

    /**
     * @param array<string, string> $mapping
     * @param array<string, array<string, mixed>> $fields
     * @param array<int, string> $preserve
     */
    private function makeMapper(array $mapping, array $fields = [], array $preserve = []): RemoteApiFieldMapper
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        $configuration = new RemoteApiConfiguration($module, 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'mapping' => $mapping,
            'fields' => $fields,
            'sync' => ['preserve_local_fields' => $preserve],
        ]);

        return new RemoteApiFieldMapper($configuration);
    }
}
