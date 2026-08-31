<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Support\Facades\Hash;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\ManageTraits;

class ManageTraitsTest extends TestCase
{
    protected $target;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target = new class
        {
            use ManageTraits;
        };

        $this->target->setModuleName('TestModule');
        $this->target->setModuleRouteName('TestRoute');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_can_prepare_fields_before_save()
    {
        $fields = [
            'name' => 'John',
            'password' => 'secret',
            'settings->theme' => 'dark',
            'profile.bio' => 'Hello',
        ];

        $object = (object) ['settings' => ['font' => 'sans']];

        $prepared = $this->target->prepareFieldsBeforeSaveManageTraits($object, $fields);

        $this->assertEquals('John', $prepared['name']);
        $this->assertTrue(Hash::check('secret', $prepared['password']));
        $this->assertEquals('dark', $prepared['settings']['theme']);
        $this->assertEquals('sans', $prepared['settings']['font']);
        $this->assertEquals('Hello', $prepared['profile']['bio']);
    }

    /** @test */
    public function it_can_detect_translated_inputs()
    {
        $schema = [
            ['name' => 'title', 'translated' => true],
            ['name' => 'description', 'translated' => false],
        ];

        $this->assertTrue($this->target->hasTranslatedInput($schema));
        $this->assertFalse($this->target->hasTranslatedInput([['name' => 'test']]));
    }

    /** @test */
    public function it_can_chunk_inputs()
    {
        $schema = [
            'title' => ['name' => 'title', 'type' => 'text'],
            'group1' => [
                'name' => 'group1',
                'type' => 'group',
                'schema' => [
                    ['name' => 'sub1', 'type' => 'text'],
                ],
            ],
        ];

        $chunked = $this->target->chunkInputs($schema);

        $this->assertArrayHasKey('title', $chunked);
        $this->assertArrayHasKey('group1.sub1', $chunked);
        $this->assertEquals('group1.sub1', $chunked['group1.sub1']['name']);
        $this->assertEquals('group1', $chunked['group1.sub1']['parentName']);
    }

    /** @test */
    public function it_can_resolve_model()
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $module->shouldReceive('getModel')->with('TestRoute')->once()->andReturn('TestModel');

        // isModuleRouteClass() and getModule() each resolve the module via Modularous::find().
        Modularous::shouldReceive('find')->with('TestModule')->twice()->andReturn($module);

        $this->assertEquals('TestModel', $this->target->model());
    }

    /** @test */
    public function it_resolves_inputs_via_blueprint_when_flat_inputs_absent()
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('resolveRouteBlueprintField')
            ->once()
            ->with('TestRoute', 'inputs')
            ->andReturn([
                ['name' => 'from_blueprint', 'type' => 'text'],
            ]);

        Modularous::shouldReceive('find')->with('TestModule')->once()->andReturn($module);

        $inputs = $this->target->inputs();

        $this->assertArrayHasKey('from_blueprint', $inputs);
        $this->assertEquals('text', $inputs['from_blueprint']['type']);
    }
}
