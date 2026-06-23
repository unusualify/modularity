<?php

namespace Unusualify\Modularity\Tests\Transformers;

use Illuminate\Http\Request;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Transformers\Resource;

class ResourceTest extends TestCase
{
    public function test_merge_resource_preserves_parent_payload_for_arrays(): void
    {
        $resource = new class(['id' => 1, 'name' => 'Basic']) extends Resource
        {
            protected function mergeResource($request): array
            {
                return [
                    'extra' => 'value',
                ];
            }
        };

        $result = $resource->toArray(Request::create('/'));

        $this->assertSame(1, $result['id']);
        $this->assertSame('Basic', $result['name']);
        $this->assertSame('value', $result['extra']);
    }

    public function test_merge_resource_can_override_parent_keys(): void
    {
        $resource = new class(['id' => 1, 'name' => 'Basic']) extends Resource
        {
            protected function mergeResource($request): array
            {
                return [
                    'name' => 'Overridden',
                ];
            }
        };

        $result = $resource->toArray(Request::create('/'));

        $this->assertSame('Overridden', $result['name']);
    }
}
