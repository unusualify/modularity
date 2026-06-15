<?php

namespace Modules\Cms\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\Page;
use Modules\Cms\Routing\CmsFrontRouteLocalizationBinding;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Unusualify\Modularous\Http\Controllers\Controller;

/**
 * Read-only CMS front routing metadata for admin UI (slug path preview, headers, tooling).
 */
class CmsRoutingMetaController extends Controller
{
    public function __invoke(
        CmsParentSegmentResolver $parentSegmentResolver,
        CmsLocalizationContract $localization,
    ): JsonResponse {
        return response()->json([
            'front_route_prefix' => trim((string) modularousConfig('cms_routing.front_route_prefix', 'cms'), '/'),
            'localization_driver' => $localization->driver(),
            'default_locale' => $localization->defaultLocale(),
            'hide_default_locale_segment' => $localization->hideDefaultLocaleInUrl(),
            'locale_strategy' => (string) modularousConfig('cms_routing.locale_strategy', 'path'),
            'public_front_route_group_mode' => (string) modularousConfig('cms_routing.public_front_route_group_mode', 'catch_all'),
            'public_front_uses_locale_route_param' => CmsFrontRouteLocalizationBinding::shouldUseLocalePrefixRouteGroup(),
            'path_segment_locales' => $localization->pathSegmentLocales(),
            'supported_locales' => $localization->supportedLocalesMeta(),
            'admin' => [
                'slug_nested_path_warnings' => (bool) modularousConfig('cms_routing.admin.slug_nested_path_warnings', true),
                'slug_public_path_preview' => (bool) modularousConfig('cms_routing.admin.slug_public_path_preview', true),
                'slug_max_path_segments' => modularousConfig('cms_routing.admin.slug_max_path_segments'),
                'signed_preview_enabled' => (bool) modularousConfig('cms_routing.signed_preview.enabled', true),
                'signed_preview_ttl_minutes' => max(5, (int) modularousConfig('cms_routing.signed_preview.ttl_minutes', 60)),
            ],
            'parent_segment_prefixes' => [
                Page::class => $parentSegmentResolver->normalizedPrefixesMapForTargetClass(Page::class),
            ],
        ]);
    }
}
