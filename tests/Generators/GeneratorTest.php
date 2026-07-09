<?php

namespace Unusualify\Modularous\Tests\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Unusualify\Modularous\Generators\Generator;
use Unusualify\Modularous\Tests\TestCase;

class GeneratorTest extends TestCase
{
    protected $generator;

    protected $config;

    protected $filesystem;

    protected $console;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = realpath(__DIR__ . '/../../test-modules');
        if ($fixturesPath) {
            $app['config']->set('modules.paths.modules', $fixturesPath);
            $app['config']->set('modules.scan.paths', [$fixturesPath]);
        }
        $app['config']->set('modules.namespace', 'TestModules');

        // Align generator paths with fixture layout (Entities, Repositories, Controllers at module root)
        $app['config']->set('modules.paths.generator.model', ['path' => 'Entities', 'namespace' => 'Entities', 'generate' => false]);
        $app['config']->set('modules.paths.generator.repository', ['path' => 'Repositories', 'namespace' => 'Repositories', 'generate' => false]);
        $app['config']->set('modules.paths.generator.controller', ['path' => 'Controllers', 'namespace' => 'Controllers', 'generate' => false]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')->with('modularous.paths.generator.model')->andReturn([
            'path' => 'Entities',
            'generate' => true,
        ]);
        $this->config = $config;
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->console = Mockery::mock(Console::class);

        $this->generator = new class('TestGenerator', $this->config, $this->filesystem, $this->console) extends Generator
        {
            public function generate(): int
            {
                return 0;
            }
        };
    }

    /** @test */
    public function it_can_set_and_get_config()
    {
        $newConfig = Mockery::mock(Config::class);
        $this->generator->setConfig($newConfig);
        $this->assertEquals($newConfig, $this->generator->getConfig());
    }

    /** @test */
    public function it_can_set_and_get_filesystem()
    {
        $newFilesystem = Mockery::mock(Filesystem::class);
        $this->generator->setFilesystem($newFilesystem);
        $this->assertEquals($newFilesystem, $this->generator->getFilesystem());
    }

    /** @test */
    public function it_can_set_and_get_console()
    {
        $newConsole = Mockery::mock(Console::class);
        $this->generator->setConsole($newConsole);
        $this->assertEquals($newConsole, $this->generator->getConsole());
    }

    /** @test */
    public function it_can_set_and_get_route()
    {
        $this->generator->setRoute('test-route');
        $this->assertEquals('test-route', $this->generator->getRoute());
    }

    /** @test */
    public function it_can_set_and_get_force()
    {
        $this->generator->setForce(true);
        // force is protected but let's check if there is a getter if needed or if we can test its effect in subclasses
        // Since there is no getter for force, we just verify the setter returns $this
        $this->assertEquals($this->generator, $this->generator->setForce(true));
    }

    /** @test */
    public function it_can_set_and_get_fix()
    {
        $this->generator->setFix(true);
        $this->assertTrue($this->generator->getFix());
    }

    /** @test */
    public function it_can_set_and_get_test()
    {
        $this->generator->setTest(true);
        $this->assertTrue($this->generator->getTest());
    }

    /** @test */
    public function it_returns_studly_name()
    {
        $this->assertEquals('TestGenerator', $this->generator->getName());
    }

    /** @test */
    public function it_can_set_name()
    {
        $this->generator->setName('RenamedGenerator');

        $this->assertSame('RenamedGenerator', $this->generator->getName());
    }

    /** @test */
    public function it_returns_modularous_generator_config_array()
    {
        $this->assertSame(
            ['path' => 'Entities', 'generate' => true],
            $this->generator->getModularousGeneratorConfig('model')
        );
    }

    /** @test */
    public function it_returns_target_path_as_false_when_no_module_is_set()
    {
        $this->assertFalse($this->generator->getTargetPath());
    }

    /** @test */
    public function it_module_is_set_and_get()
    {
        $this->generator->setModule('TestModule');
        $this->assertEquals('TestModule', $this->generator->getModule());
    }

    /** @test */
    public function it_generator_config()
    {
        $generatorConfig = $this->generator->generatorConfig('model');
        $this->assertInstanceOf(GeneratorPath::class, $generatorConfig);

        $this->assertEquals('Entities', $generatorConfig->getPath());
        $this->assertEquals('Entities', $generatorConfig->getNamespace());
    }
}
