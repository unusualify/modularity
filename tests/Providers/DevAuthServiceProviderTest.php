<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use RuntimeException;
use Unusualify\Modularous\Http\Middleware\DevAutoLoginMiddleware;
use Unusualify\Modularous\Providers\DevAuthServiceProvider;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;
use Unusualify\Modularous\Support\ModularousRoutes;
use Unusualify\Modularous\Tests\TestCase;

class DevAuthServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        config()->set('modularous.dev_auth.enabled', false);

        parent::tearDown();
    }

    public function test_register_binds_service_as_singleton(): void
    {
        (new DevAuthServiceProvider($this->app))->register();

        $this->assertSame(
            $this->app->make(DevAutoLoginService::class),
            $this->app->make(DevAutoLoginService::class)
        );
    }

    public function test_boot_registers_middleware_alias(): void
    {
        config()->set('modularous.dev_auth.enabled', false);

        $provider = new DevAuthServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $router = $this->app->make(Router::class);

        $this->assertSame(DevAutoLoginMiddleware::class, $router->getMiddleware()['modularous.dev_auto_login'] ?? null);
    }

    public function test_boot_prioritizes_middleware_before_authentication_when_enabled(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        config()->set('modularous.dev_auth.allowed_environments', ['testing']);
        $this->app['env'] = 'testing';

        $router = $this->app->make(Router::class);
        $router->middlewarePriority = [StartSession::class, AuthenticatesRequests::class];

        $provider = new DevAuthServiceProvider($this->app);
        $provider->register();
        $provider->boot();
        $provider->boot();

        $priority = $router->middlewarePriority;
        $devIndex = array_search(DevAutoLoginMiddleware::class, $priority, true);

        $this->assertSame(1, count(array_keys($priority, DevAutoLoginMiddleware::class, true)));
        $this->assertLessThan(array_search(AuthenticatesRequests::class, $priority, true), $devIndex);
        $this->assertGreaterThan(array_search(StartSession::class, $priority, true), $devIndex);
    }

    public function test_boot_throws_when_enabled_in_production(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        config()->set('modularous.dev_auth.allowed_environments', ['local']);
        $this->app['env'] = 'production';

        $provider = new DevAuthServiceProvider($this->app);
        $provider->register();

        $this->expectException(RuntimeException::class);

        $provider->boot();
    }

    public function test_web_auth_group_includes_dev_auto_login_only_when_enabled(): void
    {
        $router = $this->app->make(Router::class);
        $provider = new DevAuthServiceProvider($this->app);
        $provider->register();

        config()->set('modularous.dev_auth.enabled', false);
        $this->app->make(ModularousRoutes::class)->generateRouteMiddlewares();
        $this->assertNotContains('modularous.dev_auto_login', $router->getMiddlewareGroups()['web.auth']);

        config()->set('modularous.dev_auth.enabled', true);
        config()->set('modularous.dev_auth.allowed_environments', ['testing']);
        $this->app['env'] = 'testing';
        $this->app->make(ModularousRoutes::class)->generateRouteMiddlewares();

        $group = $router->getMiddlewareGroups()['web.auth'];
        $this->assertSame('web', $group[0]);
        $this->assertSame('modularous.dev_auto_login', $group[1]);
        $this->assertStringStartsWith('modularous.auth:', $group[2]);
    }
}
