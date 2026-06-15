<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Modules\Cms\Support\CmsSluglessFallbackLocale;

/**
 * When {@see CmsSluglessFallbackLocale::enabled()}, redirects {@code GET /{sluglessLocale}/{rest}} to {@code GET /{rest}}
 * whenever {@see UrlRoute} already serves {@code PAGE_PUBLIC} for that locale + inner path.
 *
 * @see CmsSluglessFallbackLocale
 * @see CmsFrontRouteRegistrar::resolveMiddlewareStack
 */
final class FallbackLocaleSluglessCanonicalMiddleware
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonicalUrlResolver,
        private CmsVisitorRedirectResolver $visitorRedirectResolver,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (
            ! CmsSluglessFallbackLocale::enabled()
            || ! modularousConfig('cms_routing.public_pages_enabled', true)
        ) {
            return $next($request);
        }

        if (! in_array(mb_strtoupper($request->method()), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if ($this->visitorRedirectResolver->shouldExcludeRequest($request)) {
            return $next($request);
        }

        [$locale, $pathKey, $explicitLocalePrefix] = $this->visitorRedirectResolver->resolveLocalePathKeyAndExplicitFlag($request);

        if (! $explicitLocalePrefix) {
            return $next($request);
        }

        if (! CmsSluglessFallbackLocale::sameLocale($locale, CmsSluglessFallbackLocale::resolvedCode())) {
            return $next($request);
        }

        if (! $this->visitorRedirectResolver->isActivePagePath((string) $locale, $pathKey, true)) {
            return $next($request);
        }

        $destination = $this->canonicalUrlResolver->normalizePath($pathKey);
        $qs = $request->getQueryString();
        if (is_string($qs) && $qs !== '') {
            $destination .= '?' . $qs;
        }

        return redirect()->to($destination, CmsSluglessFallbackLocale::explicitSegmentRedirectStatus());
    }
}
