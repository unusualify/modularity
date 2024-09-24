<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Unusualify\Modularity\Generators\RouteGenerator;

class RouteGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected $routeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $module = $this->createMock(Module::class);
        $config = $this->createMock(Config::class);
        $filesystem = $this->createMock(Filesystem::class);
        $console = $this->createMock(Console::class);

        $this->routeGenerator = new RouteGenerator(
            'TestRoute',
            $config,
            $filesystem,
            $console,
            $module
        );
    }

    public function testGenerateRoute()
    {
        // Mock the necessary methods
        $this->routeGenerator->expects($this->once())
            ->method('generate')
            ->willReturn(0);

        // Call the generate method
        $result = $this->routeGenerator->generate();

        // Assert the result
        $this->assertEquals(0, $result);
    }

    public function testGenerateFolders()
    {
        // Mock the necessary methods
        $this->routeGenerator->expects($this->once())
            ->method('generateFolders')
            ->willReturn(true);

        // Call the generateFolders method
        $result = $this->routeGenerator->generateFolders();

        // Assert the result
        $this->assertTrue($result);
    }

    public function testGenerateFiles()
    {
        // Mock the necessary methods
        $this->routeGenerator->expects($this->once())
            ->method('generateFiles')
            ->willReturn(true);

        // Call the generateFiles method
        $result = $this->routeGenerator->generateFiles();

        // Assert the result
        $this->assertTrue($result);
    }

    public function testGenerateResources()
    {
        // Mock the necessary methods
        $this->routeGenerator->expects($this->once())
            ->method('generateResources')
            ->willReturn(true);

        // Call the generateResources method
        $result = $this->routeGenerator->generateResources();

        // Assert the result
        $this->assertTrue($result);
    }
}
