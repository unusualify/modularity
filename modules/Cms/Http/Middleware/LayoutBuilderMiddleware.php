<?php

declare(strict_types=1);

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Support\StylesheetManager;

class LayoutBuilderMiddleware
{
    /**
     * Optionally shares the active layout (by slug from query string) for front Blade composition.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! modularousConfig('cms_layout_builder.middleware_enabled', false)) {
            return $next($request);
        }

        $key = trim((string) modularousConfig('cms_layout_builder.middleware_query_slug_key', 'cms_layout'));
        $fallback = trim((string) modularousConfig('cms_layout_builder.default_layout_slug', ''));

        $slugRaw = $key !== '' ? $request->query($key) : '';
        $slug = is_scalar($slugRaw) || $slugRaw === null ? trim((string) $slugRaw) : '';
        if ($slug === '') {
            $slug = $fallback;
        }

        if ($slug === '') {
            View::share([
                'cmsLayoutBuilder' => null,
                'cmsLayoutStylesheetHrefs' => [],
                'cmsLayoutStylesheetScriptSrcs' => [],
            ]);

            return $next($request);
        }

        /** @var LayoutBuilder|null $layout */
        $layout = LayoutBuilder::query()
            ->where('slug', $slug)
            ->with(['styleSheet'])
            ->first();

        $assets = StylesheetManager::frameworkAssetsForLayoutBuilder($layout);

        View::share([
            'cmsLayoutBuilder' => $layout,
            'cmsLayoutStylesheetHrefs' => $assets['linkHrefs'],
            'cmsLayoutStylesheetScriptSrcs' => $assets['scriptSrcs'],
        ]);

        return $next($request);
    }
}
