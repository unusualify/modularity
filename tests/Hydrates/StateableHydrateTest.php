<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Illuminate\Support\Facades\App;
use Mockery as m;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Hydrates\Inputs\StateableHydrate;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;

class StateableHydrateTest extends TestCase
{
    public function test_stateable_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'stateable',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute',
        ];

        // Mock repository with getStateableList method
        $repositoryMock = m::mock();
        $repositoryMock->shouldReceive('getStateableList')->withAnyArgs()->andReturn([
            ['name' => 'active', 'id' => 1],
            ['name' => 'inactive', 'id' => 0],
        ]);

        // Mock module
        $moduleMock = m::mock(Module::class);
        $moduleMock->shouldReceive('getRouteClass')->with('testRoute', 'repository')->andReturn(get_class($repositoryMock));

        Modularous::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        App::shouldReceive('make')
            ->with(get_class($repositoryMock))
            ->andReturn($repositoryMock);

        $h = new StateableHydrate($input, null, null, false);
        $result = $h->render();

        $this->assertEquals('select', $result['type']);
        $this->assertEquals('stateable_id', $result['name']);
        $this->assertEquals('Status', $result['label']);
        $this->assertIsArray($result['items']);
    }
}
