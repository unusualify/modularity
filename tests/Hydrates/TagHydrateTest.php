<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Illuminate\Support\Facades\App;
use Mockery as m;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Hydrates\Inputs\TagHydrate;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;

class TagHydrateTest extends TestCase
{
    public function test_tag_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'tag',
            'name' => 'tags',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute',
        ];

        $repositoryMock = m::mock();
        $repositoryMock->shouldReceive('getTags')->andReturn(
            collect([['id' => 1, 'name' => 'tag1']])
        );
        $repositoryMock->shouldReceive('getModel')->andReturn(new class
        {
            public function __toString()
            {
                return 'TagModel';
            }
        });

        $moduleMock = m::mock(Module::class);
        $moduleMock->shouldReceive('getRouteClass')->with('testRoute', 'repository')->andReturn(get_class($repositoryMock));
        $moduleMock->shouldReceive('getRouteActionUrl')->andReturn('/tags');

        Modularous::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        App::shouldReceive('make')
            ->andReturn($repositoryMock);

        $h = new TagHydrate($input, null, null, false);
        $result = $h->render();

        $this->assertEquals('input-tag', $result['type']);
        $this->assertFalse($result['returnObject']);
        $this->assertFalse($result['chips']);
    }
}
