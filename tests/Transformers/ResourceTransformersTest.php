<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Transformers;

use Illuminate\Http\Request;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Transformers\PermissionResource;
use Unusualify\Modularous\Transformers\Resource;
use Unusualify\Modularous\Transformers\RoleResource;

class ResourceTransformersTest extends TestCase
{
    /** @test */
    public function resource_merges_extra_fields_and_reads_values(): void
    {
        $resource = new class(['id' => 1, 'name' => 'Ada']) extends Resource
        {
            protected function mergeResource($request): array
            {
                return ['extra' => $this->value('name')];
            }
        };

        $payload = $resource->toArray(Request::create('/'));

        $this->assertSame(1, $payload['id']);
        $this->assertSame('Ada', $payload['extra']);
    }

    /** @test */
    public function permission_and_role_resources_delegate_to_parent(): void
    {
        $permission = (new PermissionResource(['id' => 2, 'name' => 'edit']))->toArray(Request::create('/'));
        $role = (new RoleResource(['id' => 3, 'name' => 'admin']))->toArray(Request::create('/'));

        $this->assertSame('edit', $permission['name']);
        $this->assertSame('admin', $role['name']);
    }
}
