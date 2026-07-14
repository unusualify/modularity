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
    }
}
