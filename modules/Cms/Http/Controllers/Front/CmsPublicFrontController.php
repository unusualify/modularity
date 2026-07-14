<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsPublicFrontViewName;

/**
 * Single public catch-all invokable when {@see modularousConfig('cms_routing.universal_cms_public_front')} is on:
 * resolves any entity from {@see UrlRoute} for models on the {@see ParentSegment} registry via
 * {@see CmsPublicModelResolver::resolveForParentSegmentRegistry()}.
 */
final class CmsPublicFrontController extends CmsController
{
    /**
     * @var string
     */
    protected $moduleName = 'Cms';

    /**
     * @var string
     */
    protected $routeName = 'Public';

    /**
     * @see CmsController::resolvePublicItem()
     */
    protected function resolvePublicItem(Request $request): ?Model
    {
        $key = $this->publicCmsModuleRouteKey();
        $handler = data_get((array) modularousConfig('cms_routing.public_item_resolvers', []), $key);

        if (is_string($handler) && class_exists($handler)) {
            return app($handler)($request);
        }

        $kind = (string) modularousConfig(
            'cms_routing.public_url_route_kind.' . $key,
            UrlRoute::KIND_PAGE_PUBLIC
        );

        return app(CmsPublicModelResolver::class)->resolveForParentSegmentRegistry($request, $kind);
    }

    /**
     * Blade is chosen from the resolved model type (submodule) instead of a fixed per-controller
     * {@code module::route.custom}. PageLayout wrapping is handled by {@see CmsController::renderPublicCmsPresentation()}.
     */
    protected function resolvePublicPresentationViewName(Request $request, Model $item): string
    {
        return CmsPublicFrontViewName::forModel($item);
    }
}
