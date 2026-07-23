<?php

namespace Modules\Cms\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;

/**
 * CMS public front routing: {@see Route::cmsPublicFrontRoutes()} macro (legacy inner group) and
 * auto-registration via {@see CmsFrontRouteRegistrar::registerAutoForQualifiedModules()}.
 *
 * Catch-all registration runs on {@see Application::booted()} so host {@code routes/web.php}
 * (framework {@code withRouting}) is already loaded and wins by registration order.
 */
class CmsRouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * Legacy: inner catch-all only; wrap with {@code Route::prefix(...)} if needed.
         * Prefer {@see registerAutoForQualifiedModules()} via {@see boot()}.
         */
        Route::macro('cmsPublicFrontRoutes', function (): void {
            if (! class_exists(CmsFrontRouteRegistrar::class)) {
                return;
            }

            CmsFrontRouteRegistrar::register();
        });
    }

    public function boot(): void
    {
        $this->app->booted(static function (): void {
            if (! modularousConfig('cms_features.enabled', true)) {
                return;
            }

            if (! (bool) modularousConfig('cms_routing.auto_register_public_front', true)) {
                return;
            }

            CmsFrontRouteRegistrar::registerAutoForQualifiedModules();
        });
    }
}
