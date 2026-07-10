<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Illuminate\Routing\Router;
use ReflectionClass;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Middleware\RequireMfaMiddleware;
use Unusualify\Modularous\Http\Middleware\SessionSecurityMiddleware;
use Unusualify\Modularous\Http\Middleware\StepUpMiddleware;
use Unusualify\Modularous\Providers\SecurityServiceProvider;
use Unusualify\Modularous\Support\ModularousRoutes as ModularousRoutesSupport;
use Unusualify\Modularous\Tests\TestCase;

class SecurityServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDynamicDefaultMiddlewares();
    }

    protected function tearDown(): void
    {
        $this->resetDynamicDefaultMiddlewares();

        parent::tearDown();
    }

    public function test_register_skips_middleware_registration_when_security_is_disabled(): void
    {
        config()->set('modularous.security.enabled', false);

        $provider = new SecurityServiceProvider($this->app);
        $provider->register();

        $middlewares = $this->app->make(ModularousRoutesSupport::class)->defaultMiddlewares();

        $this->assertNotContains('modularous.security.session', $middlewares);
        $this->assertNotContains('modularous.security.require_mfa', $middlewares);
        $this->assertNotContains('modularous.security.step_up', $middlewares);
    }

    public function test_register_adds_security_middlewares_when_security_is_enabled(): void
    {
        config()->set('modularous.security.enabled', true);

        $provider = new SecurityServiceProvider($this->app);
        $provider->register();

        $middlewares = ModularousRoutes::defaultMiddlewares();

        $this->assertContains('modularous.security.session', $middlewares);
        $this->assertContains('modularous.security.require_mfa', $middlewares);
        $this->assertContains('modularous.security.step_up', $middlewares);
    }

    public function test_boot_registers_security_middleware_aliases(): void
    {
        $provider = new SecurityServiceProvider($this->app);
        $provider->boot();

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $this->assertSame(SessionSecurityMiddleware::class, $router->getMiddleware()['modularous.security.session'] ?? null);
        $this->assertSame(RequireMfaMiddleware::class, $router->getMiddleware()['modularous.security.require_mfa'] ?? null);
        $this->assertSame(StepUpMiddleware::class, $router->getMiddleware()['modularous.security.step_up'] ?? null);
    }

    private function resetDynamicDefaultMiddlewares(): void
    {
        $property = (new ReflectionClass(ModularousRoutesSupport::class))->getProperty('dynamicDefaultMiddlewares');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}
