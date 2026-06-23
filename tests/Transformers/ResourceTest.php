<?php

namespace Unusualify\Modularity\Tests\Transformers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function test_collection_response_keeps_flat_pagination_shape(): void
    {
        $items = collect([
            ['id' => 1, 'name' => 'Basic'],
            ['id' => 2, 'name' => 'Premium'],
        ]);

        $paginator = new LengthAwarePaginator($items, 275, 15, 1, [
            'path' => 'http://localhost/api/packages',
        ]);

        $resourceClass = new class(['id' => 1]) extends Resource {};

        $collection = $resourceClass::collection($paginator);

        $controller = new class extends \Unusualify\Modularity\Http\Controllers\ApiController
        {
            public function __construct() {}

            public function expose(mixed $data): array
            {
                $this->request = Request::create('/api/v1/packages');

                return $this->resolveApiResourcePayload($data);
            }
        };

        $payload = $controller->expose($collection);

        $this->assertCount(2, $payload['data']);
        $this->assertSame(275, $payload['total']);
        $this->assertSame(1, $payload['current_page']);
        $this->assertSame(15, $payload['per_page']);
        $this->assertArrayHasKey('first_page_url', $payload);
        $this->assertArrayNotHasKey('meta', $payload);
    }
}
