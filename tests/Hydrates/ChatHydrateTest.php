<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\ChatHydrate;
use Unusualify\Modularous\Tests\TestCase;

class ChatHydrateTest extends TestCase
{
    public function test_chat_hydrate_sets_type_and_endpoints()
    {
        $input = [
            'type' => 'chat',
            'name' => 'messages',
        ];

        $h = new ChatHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-chat', $result['type']);
        $this->assertArrayHasKey('endpoints', $result);
        $this->assertArrayHasKey('index', $result['endpoints']);
        $this->assertArrayHasKey('store', $result['endpoints']);
        $this->assertArrayHasKey('edit', $result['endpoints']);
        $this->assertArrayHasKey('readAll', $result['endpoints']);
        $this->assertArrayHasKey('pinnedMessages', $result['endpoints']);
        $this->assertArrayHasKey('starredMessages', $result['endpoints']);
        $this->assertArrayHasKey('unstarAll', $result['endpoints']);
        $this->assertSame([24, 168, 720], array_column($result['pinDurations'], 'value'));
        $this->assertSame(['24 hours', '7 days', '30 days'], array_column($result['pinDurations'], 'title'));
        $this->assertEquals(-1, $result['default']);
        $this->assertEquals('40vh', $result['height']);
        $this->assertEquals('26vh', $result['bodyHeight']);
    }
}
