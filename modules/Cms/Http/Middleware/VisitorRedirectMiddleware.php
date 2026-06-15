<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Redirect;
use Modules\Cms\Services\CmsVisitorRedirectResolver;

/**
 * Applies CMS {@see Redirect} rules to public (front) requests.
 *
 * Run after {@see CanonicalLocaleMiddleware} when canonical URL enforcement is enabled,
 * so the path shape is stable before matching stored rules.
 */
class VisitorRedirectMiddleware
{
    public function __construct(
        private CmsVisitorRedirectResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! modularousConfig('cms_routing.visitor_redirects_enabled', true)) {
            return $next($request);
        }

        $response = $this->resolver->resolveRedirectResponse($request);
        if ($response !== null) {
            return $response;
        }

        return $next($request);
    }
}
