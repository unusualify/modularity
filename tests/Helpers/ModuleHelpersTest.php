<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Exceptions\ModularousException;
use Unusualify\Modularous\Tests\TestCase;

class ModuleHelpersTest extends TestCase
{
    /** @test */
    public function test_modularous_base_key_returns_base_key()
    {
        $result = modularousBaseKey();

        $this->assertIsString($result);
    }

    /** @test */
    public function test_class_uses_deep_gets_all_traits()
    {
        $class = new class
        {
            use HasAttributes;
        };

        $result = classUsesDeep($class);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_class_has_trait_checks_for_trait()
    {
        $class = new class
        {
            use HasAttributes;
        };

        $result = classHasTrait($class, HasAttributes::class);

        $this->assertTrue($result);
    }

    /** @test */
    public function test_modularous_config_retrieves_config()
    {
        $result = modularousConfig();

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_config_with_key()
    {
        $result = modularousConfig('package_generator.default');

        // May return null if config not set
        $this->assertTrue(is_null($result) || is_string($result) || is_array($result));
    }

    /** @test */
    public function test_curt_module_name_from_explicit_path(): void
    {
        $this->assertSame('Blog', curtModuleName('/var/www/Modules/Blog/Entities/Post.php'));
        $this->assertSame('Cms', curtModuleName('packages/app/modules/Cms/Support/Helper.php'));
    }

    /** @test */
    public function test_backtrace_formatter_and_benchmark_paths(): void
    {
        $formatted = backtrace_formatter([], [
            'file' => __FILE__,
            'line' => 10,
            'function' => 'demo',
        ]);
        $this->assertSame(10, $formatted[__FILE__]['line']);

        $this->assertIsArray(backtrace_formatted());

        config(['modularous.benchmark_enabled' => false]);
        $this->assertSame(5, benchmark(static fn () => 5, 'label'));

        config(['modularous.benchmark_enabled' => true, 'benchmark_emergency_time' => 0, 'benchmark_log_level' => 'debug']);
        $elapsed = null;
        $this->assertSame(1, benchmark(static fn () => 1, 'fast', false, 'milliseconds', $elapsed));
        $this->assertNotNull($elapsed);

        $this->expectException(ModularousException::class);
        benchmark(static fn () => null, 'die', true);
    }

    /** @test */
    public function test_exceptional_running_in_console(): void
    {
        $this->assertIsBool(exceptionalRunningInConsole());
    }

    /** @test */
    public function test_find_parent_route_and_permission_helpers(): void
    {
        $parent = findParentRoute([
            'routes' => [
                ['name' => 'child'],
                ['name' => 'parent', 'parent' => true],
            ],
        ]);
        $this->assertSame('parent', $parent['name']);
        $this->assertSame([], findParentRoute(['routes' => [['name' => 'only']]]));

        $this->assertSame('item_create', formatPermissionName('Item', 'CREATE'));
        $this->assertSame(
            ['name' => 'item_view', 'guard_name' => 'modularous'],
            formatPermissionRecord('Item', 'VIEW', 'modularous')
        );

        $records = routePermissionRecords('Item', 'modularous', [
            Permission::CREATE,
            Permission::VIEW,
        ]);
        $this->assertCount(2, $records);
        $this->assertSame('item_create', $records[0]['name']);

        $fromRoutes = permissionRecordsFromRoutes(['Post'], 'web');
        $this->assertNotEmpty($fromRoutes);
        $this->assertSame('web', $fromRoutes[0]['guard_name']);
    }

    /** @test */
    public function test_ifdd_noop_when_condition_false_and_active_traits(): void
    {
        ifdd(false, 'should-not-dump');
        $this->assertTrue(true);

        config(['modularous.traits' => [
            'soft_delete' => ['command_option' => ['description' => 'Soft deletes']],
            'revisions' => ['command_option' => ['description' => 'Revisions']],
        ]]);

        $this->assertSame(['soft_delete', 'revisions'], getModularousTraits());

        $active = activeModularousTraits([
            'soft_delete' => true,
            'revisions' => false,
            'unrelated' => true,
        ]);
        $this->assertTrue($active->has('soft_delete'));
        $this->assertFalse($active->has('revisions'));

        $options = modularousTraitOptions();
        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
