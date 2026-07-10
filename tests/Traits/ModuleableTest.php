<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Moduleable;

class ModuleableTest extends TestCase
{
    public function test_get_module_name_from_explicit_setter(): void
    {
        $subject = new class
        {
            use Moduleable;
        };

        $this->assertSame('Blog', $subject->setModuleName('Blog')->getModuleName());
    }

    public function test_get_module_name_from_repository(): void
    {
        $model = new class extends Model
        {
            protected $table = 'items';
        };

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $subject = new class($repository)
        {
            use Moduleable;

            public function __construct(public Repository $repository) {}
        };

        $this->assertSame(class_basename($model), $subject->getModuleName());
    }

    public function test_get_module_name_from_model_property(): void
    {
        $model = new class extends Model
        {
            protected $table = 'articles';
        };

        $subject = new class($model)
        {
            use Moduleable;

            public function __construct(public Model $model) {}
        };

        $this->assertSame(class_basename($model), $subject->getModuleName());
    }

    public function test_get_route_name_from_request_suffix(): void
    {
        $request = new PostStoreRequest;

        $this->assertSame('PostStore', $request->getRouteName());
    }
}

class PostStoreRequest
{
    use Moduleable;
}
