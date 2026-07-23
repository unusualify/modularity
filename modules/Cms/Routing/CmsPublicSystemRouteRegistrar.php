<?php

namespace Modules\Cms\Routing;

use Illuminate\Support\Facades\Route;

/**
 * Registers built-in CMS public system routes from {@see CmsPublicSystemRoutes}.
 *
 * Runs during {@see \Modules\Cms\Providers\CmsServiceProvider::boot()} so these endpoints are in the
 * route collection before host {@code routes/web.php} and before the late CMS catch-all.
 */
final class CmsPublicSystemRouteRegistrar
{
    public static function registerAll(): void
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        foreach (CmsPublicSystemRoutes::definitions() as $def) {
            if (! $def['enabled']) {
                continue;
            }

            $prefix = trim((string) $def['exclude_prefix'], '/');
            if ($prefix === '') {
                continue;
            }

            $register = $def['register'];

            if ($def['bind_public_domain']) {
                $domain = CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain();
                if ($domain !== null && $domain !== '') {
                    Route::domain($domain)->group($register);

                    continue;
                }
            }

            $register();
        }
    }
}
