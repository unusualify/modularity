<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Transformers;

use Illuminate\Http\Request;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Transformers\Resource;

class ResourceTest extends TestCase
{
    public function test_to_array_merges_parent_payload_with_merge_resource(): void
    {
        $request = Request::create('/');
        $resource = new class(['id' => 1, 'name' => 'Item']) extends Resource
        {
            protected function mergeResource($request): array
            {
                return [
                    'slug' => $this->value('name'),
                    'missing' => $this->value('missing', 'default'),
                ];
            }
        };

        $array = $resource->toArray($request);

        $this->assertSame(1, $array['id']);
        $this->assertSame('Item', $array['name']);
        $this->assertSame('Item', $array['slug']);
        $this->assertSame('default', $array['missing']);
    }

    public function test_merge_resource_defaults_to_empty_array(): void
    {
        $request = Request::create('/');
        $resource = new class(['id' => 2]) extends Resource {};

        $array = $resource->toArray($request);

        $this->assertSame(['id' => 2], $array);
    }
}
