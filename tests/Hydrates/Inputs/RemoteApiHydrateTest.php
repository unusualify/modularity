<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Hydrates\Inputs;

use Unusualify\Modularous\Hydrates\Inputs\RemoteApiHydrate;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiHydrateTest extends TestCase
{
    public function test_hydrate_outputs_remote_api_schema_with_catalog_items(): void
    {
        $hydrate = new RemoteApiHydrate(
            [
                'type' => 'remote-api',
                'name' => 'remote_id',
                'label' => 'Remote Package',
                'catalogEndpoint' => '/admin/business-package/packages/list-remote-catalog',
                'itemValue' => 'id',
                'itemTitle' => 'name',
                'items' => [
                    ['id' => 10, 'name' => 'Premium'],
                ],
            ],
            null,
            null,
            true,
        );

        $schema = $hydrate->render();

        $this->assertSame('input-remote-api', $schema['type']);
        $this->assertSame('remote_id', $schema['name']);
        $this->assertSame('id', $schema['itemValue']);
        $this->assertSame('/admin/business-package/packages/list-remote-catalog', $schema['catalogEndpoint']);
        $this->assertCount(2, $schema['items']);
        $this->assertSame('Premium', $schema['items'][1]['name']);
        $this->assertArrayNotHasKey('catalogDependsOn', $schema);
    }

    public function test_hydrate_passes_catalog_depends_on_and_skips_prefetch_without_sibling(): void
    {
        $hydrate = new RemoteApiHydrate(
            [
                'type' => 'remote-api',
                'name' => 'packageable_id',
                'label' => 'Packageable',
                'catalogEndpoint' => '/admin/use-case/use-cases/list-remote-catalog',
                'itemValue' => 'id',
                'itemTitle' => 'name',
                'catalogDependsOn' => [
                    'field' => 'packageable_type',
                    'map' => [
                        'region' => 'regions',
                        'country' => 'countries',
                    ],
                ],
            ],
            null,
            null,
            true,
        );

        $schema = $hydrate->render();

        $this->assertSame('input-remote-api', $schema['type']);
        $this->assertSame('packageable_id', $schema['name']);
        $this->assertSame([
            'field' => 'packageable_type',
            'map' => [
                'region' => 'regions',
                'country' => 'countries',
            ],
        ], $schema['catalogDependsOn']);
        $this->assertSame([], $schema['items'] ?? []);
    }

    public function test_hydrate_prefers_explicit_catalog_when_depends_on_has_sibling_value(): void
    {
        $hydrate = new RemoteApiHydrate(
            [
                'type' => 'remote-api',
                'name' => 'packageable_id',
                'label' => 'Packageable',
                'catalogEndpoint' => '/admin/use-case/use-cases/list-remote-catalog',
                'itemValue' => 'id',
                'itemTitle' => 'name',
                'catalog' => 'regions',
                'catalogDependsOnValue' => 'region',
                'catalogDependsOn' => [
                    'field' => 'packageable_type',
                    'map' => [
                        'region' => 'regions',
                        'country' => 'countries',
                    ],
                ],
                'items' => [
                    ['id' => 3, 'name' => 'EMEA'],
                ],
            ],
            null,
            null,
            true,
        );

        $schema = $hydrate->render();

        $this->assertSame('regions', $schema['catalog'] ?? null);
        $this->assertCount(2, $schema['items']);
        $this->assertSame('EMEA', $schema['items'][1]['name']);
        $this->assertArrayHasKey('catalogDependsOn', $schema);
    }
}
