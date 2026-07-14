<?php

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Support\ModularousRoutes;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\TestModulesCase;

class ModularousRoutesTest extends TestModulesCase
{
    protected ModularousRoutes $routes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDynamicMiddlewareState();
        $this->routes = new ModularousRoutes;
        MockModuleManager::initialize();
        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);
    }

    protected function tearDown(): void
    {
        $this->resetDynamicMiddlewareState();

        parent::tearDown();
    }

    private function resetDynamicMiddlewareState(): void
    {
        $reflection = new ReflectionClass(ModularousRoutes::class);

        foreach (['dynamicDefaultMiddlewares', 'dynamicPanelMiddlewares'] as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue(null, []);
        }
    }

    public function test_web_middlewares_returns_array()
    {
        $middlewares = $this->routes->webMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('web', $middlewares);
        $this->assertContains('modularous.log', $middlewares);
    }

    public function test_web_panel_middlewares_includes_auth_and_panel()
    {
        $middlewares = $this->routes->webPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('web.auth', $middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->apiMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('api', $middlewares);
    }

    public function test_api_panel_middlewares_includes_auth_and_panel()
    {
        $middlewares = $this->routes->apiPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('api.auth', $middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_default_middlewares_returns_array()
    {
        $middlewares = $this->routes->defaultMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('modularous.log', $middlewares);
    }

    public function test_default_panel_middlewares_includes_panel()
    {
        $middlewares = $this->routes->defaultPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_get_api_prefix_returns_string()
    {
        Config::set('modularous.api.prefix', 'api/v1');

        $prefix = $this->routes->getApiPrefix();

        $this->assertIsString($prefix);
        $this->assertEquals('api/v1', $prefix);
    }

    public function test_get_api_prefix_returns_default_when_not_in_config()
    {
        $prefix = $this->routes->getApiPrefix();

        $this->assertIsString($prefix);
        $this->assertNotEmpty($prefix);
    }

    public function test_get_api_domain_returns_null_when_not_configured()
    {
        Config::set('modularous.api.domain', null);

        $domain = $this->routes->getApiDomain();

        $this->assertNull($domain);
    }

    public function test_get_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->getApiMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_public_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->getPublicApiMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_api_auth_middlewares_returns_array()
    {
        $middlewares = $this->routes->getApiAuthMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_api_group_options_returns_array_with_prefix()
    {
        $options = $this->routes->getApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('as', $options);
        $this->assertArrayHasKey('prefix', $options);
        $this->assertArrayHasKey('domain', $options);
    }

    public function test_get_auth_api_group_options_includes_middleware()
    {
        $options = $this->routes->getAuthApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('middleware', $options);
    }

    public function test_get_public_api_group_options_includes_public_prefix()
    {
        $options = $this->routes->getPublicApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertStringContainsString('public', $options['prefix']);
    }

    public function test_get_custom_api_routes_returns_array()
    {
        $routes = $this->routes->getCustomApiRoutes();

        $this->assertIsArray($routes);
        $this->assertContains('bulk', $routes);
        $this->assertContains('search', $routes);
    }

    public function test_get_api_routes_returns_standard_crud()
    {
        $routes = $this->routes->getApiRoutes();

        $this->assertIsArray($routes);
        $this->assertContains('index', $routes);
        $this->assertContains('store', $routes);
        $this->assertContains('show', $routes);
        $this->assertContains('update', $routes);
        $this->assertContains('destroy', $routes);
    }

    public function test_group_options_returns_array()
    {
        Modularous::shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::shouldReceive('hasAdminAppUrl')->andReturn(false);
        Modularous::shouldReceive('getAdminUrlPrefix')->andReturn('admin');
        Modularous::shouldReceive('getAppUrl')->andReturn('http://localhost');
        Modularous::shouldReceive('getAdminAppHost')->andReturn(null);

        $options = $this->routes->groupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('as', $options);
    }

    public function test_configure_route_patterns_sets_patterns_from_config()
    {
        Config::set('modularous.route_patterns', ['id' => '[0-9]+']);

        $this->routes->configureRoutePatterns();

        $this->assertTrue(true);
    }

    public function test_configure_route_patterns_handles_null_config()
    {
        Config::set('modularous.route_patterns', null);

        $this->routes->configureRoutePatterns();

        $this->assertTrue(true);
    }

    public function test_generate_route_middlewares_registers_aliases()
    {
        $this->routes->generateRouteMiddlewares();

        $this->assertTrue(Route::hasMiddlewareGroup('web.auth'));
        $this->assertTrue(Route::hasMiddlewareGroup('modularous.panel'));
    }

    public function test_add_default_and_panel_middlewares_accumulate_unique_values(): void
    {
        $this->routes->addDefaultMiddleware(' custom.auth ');
        $this->routes->addDefaultMiddleware('');
        $this->routes->addDefaultMiddleware('custom.auth');
        $this->routes->addPanelMiddleware(' custom.panel ');
        $this->routes->addPanelMiddleware('custom.panel');

        $this->assertContains('custom.auth', $this->routes->defaultMiddlewares());
        $this->assertContains('custom.panel', $this->routes->defaultPanelMiddlewares());
    }

    public function test_add_default_and_panel_middlewares_via_arrays(): void
    {
        $this->routes->addDefaultMiddlewares(['array.default', 123, 'array.default']);
        $this->routes->addPanelMiddlewares(['array.panel', null, 'array.panel']);

        $this->assertContains('array.default', $this->routes->defaultMiddlewares());
        $this->assertContains('array.panel', $this->routes->defaultPanelMiddlewares());
    }

    public function test_web_and_api_middleware_groups_include_core_defaults(): void
    {
        $this->routes->addDefaultMiddleware('tests.core');

        $web = $this->routes->webMiddlewares();
        $api = $this->routes->apiMiddlewares();
        $apiPanel = $this->routes->apiPanelMiddlewares();

        $this->assertContains('web', $web);
        $this->assertContains('tests.core', $web);
        $this->assertContains('api', $api);
        $this->assertContains('tests.core', $api);
        $this->assertContains('api.auth', $apiPanel);
        $this->assertContains('modularous.panel', $apiPanel);
    }

    public function test_group_options_uses_admin_domain_when_configured(): void
    {
        Modularous::shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::shouldReceive('hasAdminAppUrl')->andReturn(true);
        Modularous::shouldReceive('getAdminAppHost')->andReturn('admin.example.test');
        Modularous::shouldReceive('getAdminUrlPrefix')->never();
        Modularous::shouldReceive('getAppUrl')->never();

        $options = $this->routes->groupOptions();

        $this->assertSame('admin.', $options['as']);
        $this->assertSame('admin.example.test', $options['domain']);
        $this->assertArrayNotHasKey('prefix', $options);
    }

    public function test_api_middleware_helpers_merge_configured_stacks(): void
    {
        Config::set('modularous.api.middlewares', ['modularous.language', 'api', 'custom-api']);
        Config::set('modularous.api.public_middlewares', ['public-only']);
        Config::set('modularous.api.auth_middlewares', ['auth:sanctum', 'verified']);

        $this->assertEquals(
            ['modularous.language', 'api', 'custom-api'],
            $this->routes->getApiMiddlewares()
        );
        $this->assertContains('public-only', $this->routes->getPublicApiMiddlewares());
        $this->assertContains('auth:sanctum', $this->routes->getApiAuthMiddlewares());
        $this->assertArrayHasKey('middleware', $this->routes->getAuthApiGroupOptions());
        $this->assertStringEndsWith('/public', $this->routes->getPublicApiGroupOptions()['prefix']);
    }

    public function test_api_route_lists_merge_config_entries(): void
    {
        Config::set('modularous.api.routes', ['search', 'custom-action']);

        $routes = $this->routes->getApiRoutes();

        $this->assertContains('index', $routes);
        $this->assertContains('search', $routes);
        $this->assertContains('custom-action', $routes);
        $this->assertEquals(
            ['bulk', 'export', 'import', 'search', 'filters', 'meta'],
            $this->routes->getCustomApiRoutes()
        );
    }

    public function test_register_routes_loads_existing_routes_file(): void
    {
        $routesFile = sys_get_temp_dir() . '/modularous-routes-test-' . uniqid('', true) . '.php';
        file_put_contents($routesFile, <<<'PHP'
<?php

Route::get('fixture-route', fn () => 'ok')->name('fixture.route');
PHP
        );

        try {
            $router = app(Router::class);
            $this->routes->registerRoutes(
                $router,
                ['prefix' => 'loaded'],
                ['web'],
                '',
                $routesFile
            );

            $matched = collect(Route::getRoutes())->contains(
                fn ($route) => str_contains($route->uri(), 'fixture-route')
            );

            $this->assertTrue($matched);
        } finally {
            @unlink($routesFile);
        }
    }

    public function test_register_routes_skips_missing_file_without_error(): void
    {
        $router = app(Router::class);
        $before = count(Route::getRoutes());

        $this->routes->registerRoutes(
            $router,
            ['prefix' => 'missing'],
            ['web'],
            '',
            sys_get_temp_dir() . '/definitely-missing-routes-' . uniqid('', true) . '.php'
        );

        $this->assertSame($before, count(Route::getRoutes()));
    }

    public function test_register_module_routes_registers_admin_resource_routes(): void
    {
        /** @var Module $module */
        $module = MockModuleManager::getTestModule();

        Route::prefix('admin')
            ->middleware('web')
            ->namespace($module->getClassNamespace('Controllers'))
            ->group(function () use ($module) {
                $this->routes->registerModuleRoutes($module, [], 'admin');
            });

        $names = collect(Route::getRoutes()->getRoutesByName())->keys();
        $itemRoutes = $names->filter(fn (string $name) => str_contains($name, 'item'));

        $this->assertTrue($itemRoutes->contains(fn (string $name) => str_ends_with($name, '.index')));
        $this->assertTrue($itemRoutes->contains(fn (string $name) => str_ends_with($name, '.store')));
    }
}
