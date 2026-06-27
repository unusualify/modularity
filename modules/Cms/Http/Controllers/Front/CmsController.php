<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\PageController;
use Modules\Cms\Http\Controllers\Traits\ResolvesPublicPresentationView;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsPageLayoutPresentationWrapper;
use Modules\Cms\Support\CmsPublicSeo;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Http\Controllers\CoreController;
use Unusualify\Modularous\Http\Controllers\PanelController;
use Unusualify\Modularous\Traits\Moduleable;

/**
 * Public CMS front (non-Inertia) base controller: extends {@see CoreController} so {@see Moduleable}
 * {@code $moduleName} / {@code $routeName} match admin controllers (e.g. {@see PageController}).
 *
 * Presentation (aligned with {@see BaseController::getViewPrefix()} /
 * {@see PanelController::$routePrefix} semantics for the submodule):
 * - {@code $viewPrefix}: {@code snake(module)::snake(route)} (e.g. {@code cms::page})
 * - {@code $routePrefix}: {@code snake(module).snake(route)} (e.g. {@code cms.page})
 *
 * Default Blade view for {@see __invoke}: {@code {$viewPrefix}.custom}.
 *
 * @todo Wire {@code HasCms} when available.
 */
abstract class CmsController extends CoreController
{
    use ResolvesPublicPresentationView;

    /**
     * Blade view namespace fragment (e.g. {@code cms::page}), same pattern as admin {@see BaseController::$viewPrefix}.
     */
    protected $viewPrefix;

    /**
     * Dot-separated route-name prefix for this submodule (e.g. {@code cms.page}); mirrors admin {@see PanelController::$routePrefix} shape for public helpers.
     */
    protected $routePrefix;

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
        $this->bootstrapCmsPublicPresentation();
    }

    /**
     * Fills {@see $viewPrefix} and {@see $routePrefix} from {@see $moduleName} and {@see $routeName} (set on each concrete controller).
     */
    protected function bootstrapCmsPublicPresentation(): void
    {
        $this->viewPrefix = $this->presentationViewPrefix();
        $this->routePrefix = $this->presentationRoutePrefix();
    }

    /**
     * Resolves the published model: optional {@see modularousConfig('cms_routing.public_item_resolvers')} invokable override,
     * otherwise {@see CmsPublicModelResolver} with {@see getPublicCmsEntityClass()} and {@see publicCmsUrlRouteKind()}.
     */
    protected function resolvePublicItem(Request $request): ?Model
    {
        $key = $this->publicCmsModuleRouteKey();
        $handler = data_get((array) modularousConfig('cms_routing.public_item_resolvers', []), $key);
        if (is_string($handler) && class_exists($handler)) {
            return app($handler)($request);
        }

        return app(CmsPublicModelResolver::class)->resolve(
            $request,
            $this->getPublicCmsEntityClass(),
            $this->publicCmsUrlRouteKind()
        );
    }

    /**
     * Entity FQCN for this route (same as {@see CoreController::$repository} model when available).
     *
     * @return class-string<Model>
     */
    protected function getPublicCmsEntityClass(): string
    {
        if ($this->repository !== null) {
            return get_class($this->repository->getModel());
        }

        $name = (string) $this->getModelName();
        $class = "{$this->namespace}\\Entities\\{$name}";
        if (! class_exists($class)) {
            throw new \LogicException("CMS front: cannot resolve entity class for [{$name}] in {$this->namespace}.");
        }

        return $class;
    }

    /**
     * {@see UrlRoute::kind} for this public route (configurable per {@see publicCmsModuleRouteKey()}).
     */
    protected function publicCmsUrlRouteKind(): string
    {
        $key = $this->publicCmsModuleRouteKey();

        return (string) modularousConfig(
            'cms_routing.public_url_route_kind.' . $key,
            UrlRoute::KIND_PAGE_PUBLIC
        );
    }

    /**
     * Key for resolver map / URL-route kind config, e.g. {@code Cms::Page}.
     */
    protected function publicCmsModuleRouteKey(): string
    {
        return $this->getModuleName() . '::' . $this->getRouteName();
    }

    protected function publicCmsViewName(): string
    {
        return $this->presentationViewName();
    }

    public function __invoke(
        Request $request,
        CanonicalUrlResolverInterface $canonical,
    ) {
        /**
         * #TODO: performance optimization, only resolve the item if it qualifies for auto public front
         * it takes 50ms to resolve the item in local environment
         */
        $item = $this->resolvePublicItem($request);
        if ($item === null) {
            abort(404);
        }

        /**
         * #TODO: performance optimization, only render the presentation if it qualifies for auto public front
         * it takes up to 2500ms to render the presentation in local environment
         */
        return $this->renderPublicCmsPresentation($request, $item, $canonical);
    }

    /**
     * Entry point for signed public preview URLs (delegated from {@see CmsSignedPublicPreviewController}).
     */
    public function renderSignedPublicPreview(
        Request $request,
        CanonicalUrlResolverInterface $canonical,
        Model $item,
    ): View {
        return $this->renderPublicCmsPresentation($request, $item, $canonical, forcePreviewRobotsNoIndex: true);
    }

    /**
     * Shared Blade response for public catch-all routes and signed preview (non-Inertia).
     */
    protected function renderPublicCmsPresentation(
        Request $request,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
        bool $forcePreviewRobotsNoIndex = false,
    ) {
        $seo = CmsPublicSeo::build($request, $item, $canonical);
        $seo['robotsMeta'] = CmsPublicSeo::resolveRobotsMeta($seo['robotsMeta'], $forcePreviewRobotsNoIndex);

        $viewName = $this->resolvePublicPresentationViewName($request, $item);

        $innerData = [
            'item' => $item,
            'seoTitle' => $seo['title'],
            'seoDescription' => $seo['description'],
            'canonicalUrl' => $seo['canonicalUrl'],
            'robotsMeta' => $seo['robotsMeta'],
        ];

        $wrapped = CmsPageLayoutPresentationWrapper::documentOrNull($item, $viewName, $innerData);
        if ($wrapped !== null) {
            return view('cms::layout_builder.inline_document', ['document' => $wrapped]);
        }

        return view($viewName, $innerData);
    }

    /**
     * Blade view for public presentation (default: {@see publicCmsViewName()}). Override when the view depends on the
     * resolved model (e.g. {@see CmsPublicFrontController}).
     */
    protected function resolvePublicPresentationViewName(Request $request, Model $item): string
    {
        return $this->publicCmsViewName();
    }
}
