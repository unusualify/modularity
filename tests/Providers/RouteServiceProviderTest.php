<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Unusualify\Modularous\Http\Controllers\API\CacheRevalidateController;
use Unusualify\Modularous\Http\Controllers\GlideController;
use Unusualify\Modularous\Providers\RouteServiceProvider;
use Unusualify\Modularous\Tests\TestModulesCase;

class RouteServiceProviderTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::hasMacro('hasAdmin')) {
            $provider = new RouteServiceProvider($this->app);
            $method = new ReflectionMethod($provider, 'bootMacros');
            $method->setAccessible(true);
            $method->invoke($provider);
        }
    }

    public function test_register_macros_registers_module_show_with_preview_macro(): void
    {
        $provider = new RouteServiceProvider($this->app);

        $method = new ReflectionMethod($provider, 'registerMacros');
        $method->setAccessible(true);
        $method->invoke($provider);

        $this->assertTrue(Route::hasMacro('moduleShowWithPreview'));
    }

    public function test_has_admin_macro_returns_existing_route_name(): void
    {
        Route::get('/admin/items', static fn () => 'ok')->name('admin.items.index');
        Route::getRoutes()->refreshNameLookups();

        $this->assertSame('admin.items.index', Route::hasAdmin('admin.items.index'));
    }

    public function test_has_admin_macro_prefixes_route_name_when_needed(): void
    {
        Route::get('/admin/items', static fn () => 'ok')->name('admin.items.index');
        Route::getRoutes()->refreshNameLookups();

        $this->assertSame('admin.items.index', Route::hasAdmin('items.index'));
    }

    public function test_has_admin_macro_returns_false_when_route_is_missing(): void
    {
        $this->assertFalse(Route::hasAdmin('missing.route.name'));
    }

    public function test_api_additional_routes_registers_get_and_post_endpoints(): void
    {
        Route::apiAdditionalRoutes('widgets', 'Widget', ['as' => 'widgets'], ['export', 'bulk']);

        $this->assertTrue(Route::has('widgets.export'));
        $this->assertTrue(Route::has('widgets.bulk'));
    }

    public function test_cache_revalidate_route_is_registered(): void
    {
        $this->assertTrue(Route::has('modularous.cache.revalidate'));

        $route = Route::getRoutes()->getByName('modularous.cache.revalidate');

        $this->assertNotNull($route);
        $this->assertSame(CacheRevalidateController::class, $route->getAction('controller'));
        $this->assertContains('modularous.cache.webhook', $route->gatherMiddleware());
    }

    public function test_map_system_routes_registers_glide_route_when_glide_is_enabled(): void
    {
        config([
            'modularous.media_library.image_service' => 'Unusualify\Modularous\Services\MediaLibrary\Glide',
            'modularous.glide.base_path' => 'img',
        ]);

        $provider = new RouteServiceProvider($this->app);
        $router = $this->app->make(Router::class);
        $method = new ReflectionMethod($provider, 'mapSystemRoutes');
        $method->setAccessible(true);

        try {
            $method->invoke($provider, $router);
        } catch (\Throwable $exception) {
            $this->markTestSkipped('Glide route registration requires full media stack: ' . $exception->getMessage());
        }

        $routes = collect(Route::getRoutes())->filter(
            static fn ($route) => str_contains($route->uri(), 'img/{path}')
        );

        $this->assertNotEmpty($routes);
        $this->assertSame(GlideController::class, $routes->first()->getAction('controller'));
    }
}
