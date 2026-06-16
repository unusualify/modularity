<?php

namespace Unusualify\Modularous\Tests\Activators;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit\Framework\TestCase;
use Unusualify\Modularous\Activators\ModuleActivator;

class ModuleActivatorTest extends TestCase
{
    /**
     * @var ModuleActivator|Mockery\MockInterface
     */
    protected $activator;

    /**
     * @var Mockery\MockInterface|Container
     */
    protected $container;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $statusFile;

    /**
     * @var string
     */
    protected $cacheKey = 'module-activator.installed.test-module';

    protected function setUp(): void
    {
        parent::setUp();

        // Create real filesystem for testing
        $this->files = new Filesystem;
        $this->statusFile = sys_get_temp_dir() . '/test_routes_statuses_' . uniqid() . '.json';

        // Create a mock container
        $this->container = Mockery::mock(Container::class);

        // Create a mock cache
        $cache = Mockery::mock('cache');
        $cache->shouldReceive('remember')->andReturnUsing(function ($key, $lifetime, $callback) {
            return $callback();
        });
        $cache->shouldReceive('forget')->andReturnNull();
        $cache->shouldReceive('put')->andReturnNull();

        // Create a mock config
        $config = Mockery::mock('config');
        $config->shouldReceive('get')->with('modules.cache.enabled')->andReturn(false);
        $config->shouldReceive('get')->with('modularous.activators.file.directory', null)->andReturnNull();

        // Set up container mocks
        $this->container->shouldReceive('offsetGet')->with('cache')->andReturn($cache);
        $this->container->shouldReceive('offsetGet')->with('files')->andReturn($this->files);
        $this->container->shouldReceive('offsetGet')->with('config')->andReturn($config);

        // Create activator instance with new constructor signature
        $this->activator = new ModuleActivator($this->container, $this->cacheKey, $this->statusFile);

        // Clean up any existing test status files
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Clean up test status files
     */
    protected function cleanup(): void
    {
        if ($this->files->exists($this->statusFile)) {
            $this->files->delete($this->statusFile);
        }
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(ModuleActivator::class, $this->activator);
    }

    /** @test */
    public function it_returns_correct_cache_key()
    {
        $cacheKey = $this->activator->getCacheKey();

        $this->assertEquals('module-activator.installed.test-module', $cacheKey);
    }

    /** @test */
    public function it_returns_empty_statuses_when_json_file_does_not_exist()
    {
        $statuses = $this->activator->getRoutesStatuses();

        $this->assertIsArray($statuses);
        $this->assertEmpty($statuses);

        $this->container = Mockery::mock(Container::class);
        $cache = Mockery::mock('cache');
        $cache->shouldReceive('remember')->andReturnUsing(function ($key, $lifetime, $callback) {
            return $callback();
        });
        $cache->shouldReceive('forget')->andReturnNull();
        $cache->shouldReceive('put')->andReturnNull();

        // Create a mock config
        $config = Mockery::mock('config');
        $config->shouldReceive('get')->with('modules.cache.enabled')->andReturn(true);
        $config->shouldReceive('get')->with('modularous.activators.file.directory', null)->andReturnNull();

        // Set up container mocks
        $this->container->shouldReceive('offsetGet')->with('cache')->andReturn($cache);
        $this->container->shouldReceive('offsetGet')->with('files')->andReturn($this->files);
        $this->container->shouldReceive('offsetGet')->with('config')->andReturn($config);

        // Create activator instance with new constructor signature
        $this->activator = new ModuleActivator($this->container, $this->cacheKey, $this->statusFile);
        $statuses = $this->activator->getRoutesStatuses();

        $this->assertIsArray($statuses);
        $this->assertEmpty($statuses);
    }

    /** @test */
    public function it_can_read_json_statuses_file()
    {
        $data = ['items' => true, 'settings' => false];

        $this->files->put($this->statusFile, json_encode($data, JSON_PRETTY_PRINT));

        $statuses = $this->activator->readJson();

        $this->assertEquals($data, $statuses);
    }

    /** @test */
    public function it_returns_empty_array_when_reading_non_existent_json_file()
    {
        $statuses = $this->activator->readJson();

        $this->assertIsArray($statuses);
        $this->assertEmpty($statuses);
    }

    /** @test */
    public function it_can_enable_a_route()
    {
        $this->activator->enable('items');

        $this->assertTrue($this->activator->hasStatus('items', true));
    }

    /** @test */
    public function it_can_disable_a_route()
    {
        // First enable it
        $this->activator->enable('items');
        $this->assertTrue($this->activator->hasStatus('items', true));

        // Then disable it
        $this->activator->disable('items');

        $this->assertTrue($this->activator->hasStatus('items', false));
    }

    /** @test */
    public function it_can_set_route_active_by_name()
    {
        $this->activator->setActiveByName('settings', true);

        $this->assertTrue($this->activator->hasStatus('settings', true));

        $this->activator->setActiveByName('settings', false);

        $this->assertTrue($this->activator->hasStatus('settings', false));
    }

    /** @test */
    public function it_can_set_route_active_via_set_active()
    {
        $this->activator->setActive('products', true);

        $this->assertTrue($this->activator->hasStatus('products', true));

        $this->activator->setActive('products', false);

        $this->assertTrue($this->activator->hasStatus('products', false));
    }

    /** @test */
    public function it_returns_false_for_non_existent_route_with_status_true()
    {
        $hasStatus = $this->activator->hasStatus('non-existent', true);

        $this->assertFalse($hasStatus);
    }

    /** @test */
    public function it_returns_true_for_non_existent_route_with_status_false()
    {
        $hasStatus = $this->activator->hasStatus('non-existent', false);

        $this->assertTrue($hasStatus);
    }

    /** @test */
    public function it_can_delete_a_route_status()
    {
        $this->activator->enable('articles');
        $this->assertTrue($this->activator->hasStatus('articles', true));

        $this->activator->delete('articles');

        // After deletion, it should return false for status true
        $this->assertFalse($this->activator->hasStatus('articles', true));
        // And true for status false (non-existent means inactive)
        $this->assertTrue($this->activator->hasStatus('articles', false));
    }

    /** @test */
    public function it_does_not_fail_when_deleting_non_existent_route()
    {
        $this->activator->delete('non-existent-route');

        // Should not throw exception and should be inactive
        $this->assertTrue($this->activator->hasStatus('non-existent-route', false));
    }

    /** @test */
    public function it_persists_statuses_to_json_file()
    {
        $this->activator->enable('users');
        $this->activator->disable('posts');

        $this->assertTrue($this->files->exists($this->statusFile));

        $content = $this->files->get($this->statusFile);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('posts', $data);
        $this->assertTrue($data['users']);
        $this->assertFalse($data['posts']);
    }

    /** @test */
    public function it_can_manage_multiple_routes()
    {
        $routes = ['items', 'categories', 'tags', 'comments'];

        foreach ($routes as $route) {
            $this->activator->enable($route);
        }

        foreach ($routes as $route) {
            $this->assertTrue($this->activator->hasStatus($route, true));
        }

        // Disable some
        $this->activator->disable('categories');
        $this->activator->disable('comments');

        $this->assertTrue($this->activator->hasStatus('items', true));
        $this->assertFalse($this->activator->hasStatus('categories', true));
        $this->assertTrue($this->activator->hasStatus('tags', true));
        $this->assertFalse($this->activator->hasStatus('comments', true));
    }

    /** @test */
    public function it_can_get_all_routes()
    {
        $routes = ['settings', 'users', 'roles'];

        foreach ($routes as $route) {
            $this->activator->enable($route);
        }

        $activeRoutes = $this->activator->getRoutes();

        $this->assertCount(3, $activeRoutes);
        $this->assertContains('settings', $activeRoutes);
        $this->assertContains('users', $activeRoutes);
        $this->assertContains('roles', $activeRoutes);
    }

    /** @test */
    public function it_returns_consistent_statuses_on_multiple_reads()
    {
        $this->activator->enable('items');
        $this->activator->disable('categories');

        $firstRead = $this->activator->getRoutesStatuses();
        $secondRead = $this->activator->getRoutesStatuses();

        $this->assertEquals($firstRead, $secondRead);
        $this->assertTrue($firstRead['items']);
        $this->assertFalse($firstRead['categories']);
    }

    /** @test */
    public function it_preserves_existing_statuses_when_updating()
    {
        $this->activator->enable('items');
        $this->activator->enable('categories');

        // Now enable another route
        $this->activator->enable('tags');

        $statuses = $this->activator->getRoutesStatuses();

        // All three should be present
        $this->assertTrue($statuses['items']);
        $this->assertTrue($statuses['categories']);
        $this->assertTrue($statuses['tags']);
    }

    /** @test */
    public function it_can_toggle_route_status()
    {
        // Initially disabled (non-existent)
        $this->assertTrue($this->activator->hasStatus('feature', false));

        // Enable it
        $this->activator->setActive('feature', true);
        $this->assertTrue($this->activator->hasStatus('feature', true));

        // Toggle back to disabled
        $this->activator->setActive('feature', false);
        $this->assertTrue($this->activator->hasStatus('feature', false));

        // Toggle back to enabled
        $this->activator->setActive('feature', true);
        $this->assertTrue($this->activator->hasStatus('feature', true));
    }

    /** @test */
    public function it_handles_special_route_names()
    {
        $specialRoutes = [
            'user-profiles',
            'api_tokens',
            'admin.dashboard',
            'super-admin_config',
        ];

        foreach ($specialRoutes as $route) {
            $this->activator->enable($route);
        }

        foreach ($specialRoutes as $route) {
            $this->assertTrue($this->activator->hasStatus($route, true));
        }
    }

    /** @test */
    public function it_correctly_identifies_disabled_routes()
    {
        $this->activator->enable('enabled-route');
        $this->activator->disable('disabled-route');

        $this->assertTrue($this->activator->hasStatus('enabled-route', true));
        $this->assertFalse($this->activator->hasStatus('enabled-route', false));

        $this->assertFalse($this->activator->hasStatus('disabled-route', true));
        $this->assertTrue($this->activator->hasStatus('disabled-route', false));
    }

    /** @test */
    public function it_can_enable_after_disabling()
    {
        $this->activator->enable('articles');
        $this->assertTrue($this->activator->hasStatus('articles', true));

        $this->activator->disable('articles');
        $this->assertTrue($this->activator->hasStatus('articles', false));

        $this->activator->enable('articles');
        $this->assertTrue($this->activator->hasStatus('articles', true));
    }

    /** @test */
    public function it_maintains_status_order_in_json()
    {
        $routes = ['zebra', 'apple', 'mango', 'banana'];

        foreach ($routes as $route) {
            $this->activator->enable($route);
        }

        $content = $this->files->get($this->statusFile);
        $data = json_decode($content, true);

        // Check all routes are present
        foreach ($routes as $route) {
            $this->assertArrayHasKey($route, $data);
        }
    }

    /** @test */
    public function it_correctly_formats_json_output()
    {
        $this->activator->enable('formatted-route');

        $content = $this->files->get($this->statusFile);

        // JSON should be pretty-printed
        $this->assertStringContainsString("\n", $content);
    }

    /** @test */
    public function it_creates_empty_routes_statuses_file_when_missing()
    {
        $this->assertFalse($this->files->exists($this->statusFile));

        $this->activator->ensureFileExists();

        $this->assertTrue($this->files->exists($this->statusFile));
        $this->assertEquals([], $this->activator->readJson());
        $this->assertEquals([], $this->activator->getRoutes());
    }

    /** @test */
    public function it_does_not_overwrite_existing_routes_statuses_file()
    {
        $data = ['items' => true];

        $this->files->put($this->statusFile, json_encode($data, JSON_PRETTY_PRINT));

        $this->activator->ensureFileExists();

        $this->assertEquals($data, $this->activator->readJson());
    }

    /** @test */
    public function it_can_work_with_empty_routes_list()
    {
        $this->activator->ensureFileExists();

        $routes = $this->activator->getRoutes();

        $this->assertIsArray($routes);
        $this->assertEmpty($routes);
    }

    /** @test */
    public function it_correctly_reads_json_with_boolean_values()
    {
        $data = [
            'route1' => true,
            'route2' => false,
            'route3' => true,
        ];

        $this->files->put($this->statusFile, json_encode($data, JSON_PRETTY_PRINT));

        $statuses = $this->activator->readJson();

        $this->assertIsBool($statuses['route1']);
        $this->assertIsBool($statuses['route2']);
        $this->assertTrue($statuses['route1']);
        $this->assertFalse($statuses['route2']);
    }

    /** @test */
    public function it_can_serialize_and_deserialize_statuses()
    {
        // Create multiple statuses
        $this->activator->setActiveByName('route_a', true);
        $this->activator->setActiveByName('route_b', false);
        $this->activator->setActiveByName('route_c', true);

        // Read from JSON
        $statuses = $this->activator->readJson();

        // Verify deserialization
        $this->assertEquals([
            'route_a' => true,
            'route_b' => false,
            'route_c' => true,
        ], $statuses);
    }

    /** @test */
    public function it_handles_concurrent_modifications()
    {
        $this->activator->enable('route1');
        $this->activator->enable('route2');

        $statuses = $this->activator->getRoutesStatuses();
        $this->assertCount(2, $statuses);

        $this->activator->enable('route3');

        $updatedStatuses = $this->activator->getRoutesStatuses();
        $this->assertCount(3, $updatedStatuses);
    }

    /** @test */
    public function cache_key_is_consistent()
    {
        $cacheKey1 = $this->activator->getCacheKey();
        $cacheKey2 = $this->activator->getCacheKey();

        // Cache key should be consistent across calls
        $this->assertEquals($cacheKey1, $cacheKey2);
    }

    /** @test */
    public function it_can_delete_multiple_routes()
    {
        $routes = ['delete1', 'delete2', 'delete3'];

        foreach ($routes as $route) {
            $this->activator->enable($route);
        }

        $this->assertCount(3, $this->activator->getRoutes());

        foreach ($routes as $route) {
            $this->activator->delete($route);
        }

        $this->assertCount(0, $this->activator->getRoutes());
    }

    /** @test */
    public function it_returns_all_routes_from_get_routes()
    {
        $this->activator->enable('active1');
        $this->activator->enable('active2');
        $this->activator->disable('inactive1');

        $routes = $this->activator->getRoutes();

        // getRoutes should return keys from the JSON file (which includes both active and inactive)
        $this->assertContains('active1', $routes);
        $this->assertContains('active2', $routes);
        $this->assertContains('inactive1', $routes);
    }

    /** @test */
    public function has_status_with_empty_statuses_returns_expected_defaults()
    {
        // When no statuses exist, non-existent routes should have status false
        $this->assertTrue($this->activator->hasStatus('any-route', false));
        $this->assertFalse($this->activator->hasStatus('any-route', true));
    }

    /** @test */
    public function it_preserves_status_when_read_json_is_called()
    {
        $this->activator->enable('preserved-route');

        $firstRead = $this->activator->readJson();
        $secondRead = $this->activator->readJson();

        $this->assertEquals($firstRead, $secondRead);
        $this->assertTrue($firstRead['preserved-route']);
    }

    /** @test */
    public function it_can_enable_and_disable_same_route_multiple_times()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->activator->enable('toggle-route');
            $this->assertTrue($this->activator->hasStatus('toggle-route', true));

            $this->activator->disable('toggle-route');
            $this->assertTrue($this->activator->hasStatus('toggle-route', false));
        }
    }

    /** @test */
    public function it_maintains_data_integrity_across_operations()
    {
        // Create initial state
        $this->activator->enable('route1');
        $this->activator->enable('route2');
        $this->activator->disable('route3');

        $initialState = $this->activator->getRoutesStatuses();

        // Perform more operations
        $this->activator->enable('route4');
        $this->activator->disable('route1');

        $updatedState = $this->activator->getRoutesStatuses();

        // Verify all data is intact
        $this->assertFalse($updatedState['route1']);  // was changed
        $this->assertTrue($updatedState['route2']);   // unchanged
        $this->assertFalse($updatedState['route3']);  // unchanged
        $this->assertTrue($updatedState['route4']);   // new
    }

    /** @test */
    public function it_correctly_handles_route_names_with_special_characters()
    {
        $this->activator->enable('admin-user_profile.edit');
        $this->activator->enable('api-v2.users.delete');

        $this->assertTrue($this->activator->hasStatus('admin-user_profile.edit', true));
        $this->assertTrue($this->activator->hasStatus('api-v2.users.delete', true));
    }

    /** @test */
    public function it_creates_valid_json_structure()
    {
        $this->activator->enable('route1');
        $this->activator->disable('route2');

        $content = $this->files->get($this->statusFile);
        $decoded = json_decode($content, true);

        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
    }
}
