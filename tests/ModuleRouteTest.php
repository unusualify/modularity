<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\ModuleRoute;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

class ModuleRouteTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        IsolatedTestModules::seedRoutesStatuses([
            'TestModule' => [
                'Item' => true,
            ],
            'SystemModule' => [
                'Item' => true,
            ],
        ]);
    }

    /** @test */
    public function it_reuses_cached_module_route_instances(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $first = $module->moduleRoute('Item');
        $second = $module->moduleRoute('item');
        $fromCollection = $module->moduleRoutes()->get('Item');

        $this->assertNotNull($first);
        $this->assertSame($first, $second);
        $this->assertSame($first, $fromCollection);

        $module->flushModuleRouteRegistry();
        $afterFlush = $module->moduleRoute('Item');
        $this->assertNotNull($afterFlush);
        $this->assertNotSame($first, $afterFlush);
    }

    /** @test */
    public function it_exposes_blueprint_config_fields_via_module_route(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->moduleRoute('Item');
        $this->assertNotNull($route);

        $this->assertIsArray($route->inputs());
        $this->assertIsArray($route->headers());
        $this->assertIsArray($route->tableOptions());
        $this->assertIsArray($route->tableFilters());
        $this->assertIsArray($route->advancedFilters());
        $this->assertIsArray($route->tableActions());
        $this->assertIsArray($route->tableRowActions());
        $this->assertIsArray($route->formActions());
    }

    /** @test */
    public function it_resolves_route_by_studly_or_snake_name(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $byStudly = $module->route('Item');
        $bySnake = $module->route('item');

        $this->assertNotNull($byStudly);
        $this->assertNotNull($bySnake);
        $this->assertSame('Item', $byStudly->name());
        $this->assertSame('item', $byStudly->snakeName());
        $this->assertSame($byStudly->name(), $bySnake->name());
    }

    /** @test */
    public function it_exposes_config_status_and_features(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->route('Item');

        $this->assertNotNull($route);
        $this->assertTrue($route->inConfig());
        $this->assertTrue($route->inStatuses());
        $this->assertTrue($route->isEnabled());
        $this->assertNotEmpty($route->config());
        $this->assertSame('Item', $route->config()['name'] ?? null);
        $this->assertIsArray($route->features());
        $this->assertArrayHasKey('model', $route->toArray());
    }

    /** @test */
    public function it_includes_orphan_status_only_routes_in_registry(): void
    {
        IsolatedTestModules::seedRoutesStatuses([
            'TestModule' => [
                'Item' => true,
                'Ghost' => false,
            ],
            'SystemModule' => [
                'Item' => true,
            ],
        ]);

        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $ghost = $module->route('Ghost');

        $this->assertNotNull($ghost);
        $this->assertFalse($ghost->inConfig());
        $this->assertTrue($ghost->inStatuses());
        $this->assertFalse($ghost->isEnabled());
    }

    /** @test */
    public function sidebar_routes_lists_config_routes_only_not_status_orphans(): void
    {
        IsolatedTestModules::seedRoutesStatuses([
            'TestModule' => [
                'Item' => true,
                'Ghost' => false,
            ],
            'SystemModule' => [
                'Item' => true,
            ],
        ]);

        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $sidebar = $module->sidebarRoutes();

        $this->assertTrue($sidebar->has('Item'));
        $this->assertFalse($sidebar->has('Ghost'));
        $this->assertInstanceOf(ModuleRoute::class, $sidebar->get('Item'));
        $this->assertSame($module->moduleRoute('Item'), $sidebar->get('Item'));
    }

    /** @test */
    public function module_route_is_singleton_shares_module_memo(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->moduleRoute('Item');
        $this->assertNotNull($route);

        $viaModule = $module->isSingleton('Item');
        $viaRoute = $route->isSingleton();

        $this->assertSame($viaModule, $viaRoute);
    }

    /** @test */
    public function it_filters_enabled_routes(): void
    {
        IsolatedTestModules::seedRoutesStatuses([
            'TestModule' => [
                'Item' => true,
                'Ghost' => false,
            ],
            'SystemModule' => [
                'Item' => true,
            ],
        ]);

        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $enabled = $module->enabledRoutes();

        $this->assertTrue($enabled->has('Item'));
        $this->assertFalse($enabled->has('Ghost'));
    }

    /** @test */
    public function module_facades_delegate_to_module_route(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $this->assertTrue($module->hasRoute('Item'));
        $this->assertTrue($module->hasRoute('item'));
        $this->assertContains('Item', $module->getRouteNames());
        $this->assertTrue($module->isEnabledRoute('Item'));
        $this->assertFalse($module->isDisabledRoute('Item'));

        $module->disableRoute('Item');
        $this->assertFalse($module->isEnabledRoute('Item'));
        $this->assertSame($module->route('Item')?->isEnabled(), false);

        $module->enableRoute('Item');
        $this->assertTrue($module->route('Item')?->isEnabled() ?? false);

        $this->assertIsBool($module->isParentRoute('Item'));
        $this->assertIsBool($module->isSingleton('Item'));
        $this->assertIsBool($module->routeHasTable('Item'));
        $this->assertIsArray($module->getRouteUrls('Item'));
        $this->assertIsArray($module->getRoutePanelUrls('Item'));
    }

    /** @test */
    public function it_generates_panel_route_prefixes(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->moduleRoute('Item');

        $this->assertNotNull($route);

        $isParent = $route->isParent();
        $this->assertSame(
            $module->fullRouteNamePrefix($isParent),
            $route->fullRouteNamePrefix()
        );
        $this->assertSame(
            $module->panelRouteNamePrefix($isParent),
            $route->panelRouteNamePrefix()
        );

        $base = $route->generateRoutePrefix();
        $this->assertIsString($base);
        $this->assertStringNotContainsString('nested', $base);

        $nested = $route->generateRoutePrefix(
            noNested: false,
            isNested: true,
            nestedParentName: 'item',
            isParent: false,
        );
        $this->assertStringEndsWith('.item.nested', $nested);

        $withoutNested = $route->generateRoutePrefix(
            noNested: true,
            isNested: true,
            nestedParentName: 'item',
            isParent: true,
        );
        $this->assertStringNotContainsString('nested', $withoutNested);
    }
}
