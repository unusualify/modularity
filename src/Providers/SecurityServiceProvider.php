<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Middleware\RequireMfaMiddleware;
use Unusualify\Modularous\Http\Middleware\SessionSecurityMiddleware;
use Unusualify\Modularous\Http\Middleware\StepUpMiddleware;
use Unusualify\Modularous\Services\Security\SecurityService;

class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->app->singleton(SecurityService::class, fn () => new SecurityService);

        if (! modularousConfig('security.enabled', false)) {
            return;
        }

        ModularousRoutes::addDefaultMiddlewares([
            'modularous.security.session',
            'modularous.security.require_mfa',
            'modularous.security.step_up',
        ]);
    }

    public function boot(): void
    {
        Route::aliasMiddleware('modularous.security.session', SessionSecurityMiddleware::class);
        Route::aliasMiddleware('modularous.security.require_mfa', RequireMfaMiddleware::class);
        Route::aliasMiddleware('modularous.security.step_up', StepUpMiddleware::class);
    }
}
