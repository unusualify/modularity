<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\RelationshipsHydrate;
use Unusualify\Modularous\Tests\TestCase;

class RelationshipsHydrateTest extends TestCase
{
    public function test_relationships_hydrate_instantiation()
    {
        $input = [
            'type' => 'relationships',
            'name' => 'relationships',
        ];

        $h = new RelationshipsHydrate($input, null, null, true);

        $this->assertInstanceOf(RelationshipsHydrate::class, $h);
        $result = $h->render();
        $this->assertSame('relationships', $result['type']);
        $this->assertSame('grey', $result['color']);
    }

    public function test_relationships_hydrate_has_requirements()
    {
        $input = [
            'type' => 'relationships',
            'name' => 'relationships',
        ];

        $h = new RelationshipsHydrate($input, null, null, true);

        // Verify it has the required properties set
        $this->assertEquals('grey', $h->requirements['color']);
        $this->assertEquals('outlined', $h->requirements['cardVariant']);
        $this->assertEquals('name', $h->requirements['processableTitle']);
    }
}
