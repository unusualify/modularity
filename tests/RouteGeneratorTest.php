<?php

namespace Unusualify\Modularity\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Generators\RouteGenerator;

class RouteGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected $routeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeGenerator = with(new RouteGenerator('Test'))
            ->setFilesystem($this->app['files'])
            ->setConfig($this->app['config'])
            ->setModule('SystemPayment');
    }

    public function test_get_name_method()
    {
        $methodName = 'getName';
        $expectedResult = 'Test';

        // Mock the necessary methods
        // $this->routeGenerator->expects($this->once())
        //     ->method($methodName)
        //     ->willReturn($expectedResult);

        // Call the generate method
        $result = $this->routeGenerator->{$methodName}();

        // Assert the result
        $this->assertEquals($expectedResult, $result);
    }

    public function _testGenerateRoute()
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

    public function _testGenerateFolders()
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

    public function _testGenerateFiles()
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

    public function _testGenerateResources()
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
