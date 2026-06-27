<?php

namespace Unusualify\Modularous\Tests\Support;

use TestModules\TestModule\Entities\Item;
use TestModules\TestModule\Repositories\ItemRepository;
use Unusualify\Modularous\Support\Finder;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestModulesCase;

class FinderTest extends TestModulesCase
{
    protected Finder $finder;

    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();

        // Only enable TestModule so getRouteModel/getRouteRepository return TestModule's Item (not SystemModule's)
        $this->enableOnlyModules('TestModule');

        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);

        $this->finder = new Finder;
    }

    public function test_it_can_find_classes_in_path(): void
    {
        $path = IsolatedTestModules::path() . '/TestModule/Entities';
        $classes = $this->finder->getClasses($path);

        $this->assertNotEmpty($classes);
    }

    public function test_get_classes_throws_for_non_existent_path(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->finder->getClasses(__DIR__ . '/non-existent-path-xyz');
    }

    public function test_get_classes_returns_array(): void
    {
        $path = realpath(__DIR__ . '/../..');
        $classes = $this->finder->getClasses($path);

        $this->assertIsArray($classes);
    }

    public function test_get_model_returns_false_for_unknown_table(): void
    {
        $result = $this->finder->getModel('non_existent_table_xyz');

        $this->assertFalse($result);
    }

    public function test_get_model_returns_class_for_test_module_items_table(): void
    {
        $result = $this->finder->getModel('test_module_items');

        $this->assertNotFalse($result);
        $this->assertSame('TestModules\TestModule\Entities\Item', $result);
    }

    public function test_get_repository_returns_false_for_unknown_table(): void
    {
        $result = $this->finder->getRepository('non_existent_table_xyz');

        $this->assertFalse($result);
    }

    public function test_get_repository_returns_class_for_test_module_items_table(): void
    {
        $result = $this->finder->getRepository('test_module_items');

        $this->assertNotFalse($result);
        $this->assertSame('TestModules\TestModule\Repositories\ItemRepository', $result);
    }

    public function test_get_route_model_returns_class_for_item_route(): void
    {
        $result = $this->finder->getRouteModel('Item', false);

        $this->assertNotFalse($result);
        $this->assertSame('TestModules\TestModule\Entities\Item', $result);
    }

    public function test_get_route_model_returns_instance_when_as_class_true(): void
    {
        $result = $this->finder->getRouteModel('Item', true);

        $this->assertNotFalse($result);
        $this->assertInstanceOf(Item::class, $result);
    }

    public function test_get_route_repository_returns_class_for_item_route(): void
    {
        $result = $this->finder->getRouteRepository('Item', false);

        $this->assertNotFalse($result);
        $this->assertSame('TestModules\TestModule\Repositories\ItemRepository', $result);
    }

    public function test_get_route_repository_returns_instance_when_as_class_true(): void
    {
        $result = $this->finder->getRouteRepository('Item', true);

        $this->assertNotFalse($result);
        $this->assertInstanceOf(ItemRepository::class, $result);
    }

    public function test_get_route_model_returns_false_for_unknown_route(): void
    {
        $result = $this->finder->getRouteModel('NonExistentRoute');

        $this->assertFalse($result);
    }

    public function test_get_route_repository_returns_false_for_unknown_route(): void
    {
        $result = $this->finder->getRouteRepository('NonExistentRoute');

        $this->assertFalse($result);
    }

    public function test_finder_uses_manage_names_trait(): void
    {
        $this->assertTrue(method_exists($this->finder, 'getStudlyName'));
    }
}
