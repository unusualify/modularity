<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Middleware\DevAutoLoginMiddleware;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;

class DevAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DevAutoLoginService::class);
    }

    public function boot(): void
    {
        $devAutoLogin = $this->app->make(DevAutoLoginService::class);
        $devAutoLogin->assertSafeEnvironment();
        Route::aliasMiddleware('modularous.dev_auto_login', DevAutoLoginMiddleware::class);

        if ($devAutoLogin->isEnabled()) {
            $this->prioritizeBeforeAuthentication($this->app->make(Router::class));
        }
    }

    /**
     * Router middleware sorting moves AuthenticatesRequests ahead of unlisted middleware,
     * so the auto-login must be listed right before it (and therefore after StartSession).
     */
    protected function prioritizeBeforeAuthentication(Router $router): void
    {
        $priority = $router->middlewarePriority;

        if (in_array(DevAutoLoginMiddleware::class, $priority, true)) {
            return;
        }

        $index = array_search(AuthenticatesRequests::class, $priority, true);

        array_splice($priority, $index === false ? count($priority) : $index, 0, [DevAutoLoginMiddleware::class]);

        $router->middlewarePriority = $priority;
    }
}
