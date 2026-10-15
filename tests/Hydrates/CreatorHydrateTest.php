<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\CreatorHydrate;
use Unusualify\Modularous\Tests\TestCase;

class CreatorHydrateTest extends TestCase
{
    public function test_creator_hydrate_instantiation()
    {
        $input = [
            'type' => 'creator',
            'name' => 'created_by',
        ];

        $h = new CreatorHydrate($input, null, null, true);

        $this->assertInstanceOf(CreatorHydrate::class, $h);
    }

    public function test_creator_hydrate_has_requirements()
    {
        $input = [
            'type' => 'creator',
            'name' => 'created_by',
        ];

        $h = new CreatorHydrate($input, null, null, true);

        // CreatorHydrate has specific requirements set
        $this->assertIsArray($h->requirements);
        $this->assertArrayHasKey('itemTitle', $h->requirements);
        $this->assertArrayHasKey('label', $h->requirements);
    }

    public function test_creator_hydrate_is_impersonatable_by_default()
    {
        $h = new CreatorHydrate(['type' => 'creator', 'name' => 'created_by'], null, null, true);

        $this->assertArrayHasKey('impersonatable', $h->requirements);
        $this->assertTrue($h->requirements['impersonatable']);
    }

    public function test_creator_hydrate_skips_impersonate_action_for_guests()
    {
        $h = new CreatorHydrate(['type' => 'creator', 'name' => 'created_by'], null, null, true);

        $method = new \ReflectionMethod($h, 'getImpersonateAction');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($h));
    }
}
