<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\PageController;
use Modules\Cms\Http\Controllers\Traits\ResolvesPublicPresentationView;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Modules\Cms\Support\CmsPageLayoutPresentationWrapper;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Modules\Cms\Support\CmsPublicPresentationInnerData;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Unusualify\Modularous\Contracts\ModulePresentationAssetLoaderInterface;
use Unusualify\Modularous\Facades\ModularousCache;
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

        return $this->renderPublicCmsPresentation($request, $item, $canonical);
    }

    /**
     * Entry point for signed public preview URLs (delegated from {@see CmsSignedPublicPreviewController}).
     */
    public function renderSignedPublicPreview(
        Request $request,
        CanonicalUrlResolverInterface $canonical,
        Model $item,
    ): Response {
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
        app(ModulePresentationAssetLoaderInterface::class)->ensureLoaded();

        $viewName = $this->resolvePublicPresentationViewName($request, $item);

        $innerData = CmsPublicPresentationInnerData::build($request, $item, $canonical, $forcePreviewRobotsNoIndex);

        $cacheContext = $this->resolvePresentationItemCacheContext($item);
        $presentationItemCacheEnabled = ! $forcePreviewRobotsNoIndex
            && $cacheContext !== null
            && CmsPublicPresentationItemCache::isEnabled(
                $cacheContext['moduleName'],
                $cacheContext['moduleRouteName'],
            );

        $cacheHeader = null;
        $normalizedPath = $this->resolvePublicPresentationPathKey($request);
        $cacheLookupKey = $this->resolvePublicPresentationCacheLookupKey($request, $cacheContext, $normalizedPath);

        if ($presentationItemCacheEnabled) {
            $resolved = CmsPublicPresentationItemCache::resolvePresentationHtml(
                $cacheContext['moduleName'],
                $cacheContext['moduleRouteName'],
                $item,
                $viewName,
                $innerData,
                bypassSwr: $forcePreviewRobotsNoIndex,
                normalizedPath: $normalizedPath,
                cacheLookupKey: $cacheLookupKey,
            );

            $cacheHeader = CmsPublicPresentationItemCache::cacheHeaderValue($resolved['status']);
            $html = $resolved['html'];

            if (is_string($html) && $html !== '') {
                if (CmsPageLayoutPresentationWrapper::resolvesWithPageLayoutShell($item, $viewName)) {
                    return response(
                        view('cms::layout_builder.inline_document', ['document' => $html]),
                        200,
                        $this->presentationCacheHeaders($cacheHeader),
                    );
                }

                return response(
                    $html,
                    200,
                    array_merge(
                        ['Content-Type' => 'text/html; charset=UTF-8'],
                        $this->presentationCacheHeaders($cacheHeader),
                    ),
                );
            }
        }

        $wrapped = CmsPageLayoutPresentationWrapper::documentOrNull($item, $viewName, $innerData);
        if ($wrapped !== null) {
            return response(
                view('cms::layout_builder.inline_document', ['document' => $wrapped]),
                200,
                $this->presentationCacheHeaders($cacheHeader),
            );
        }

        return response(
            view($viewName, $innerData),
            200,
            $this->presentationCacheHeaders($cacheHeader),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function presentationCacheHeaders(?string $cacheHeader): array
    {
        if ($cacheHeader === null || $cacheHeader === '') {
            return [];
        }

        return ['X-Modularous-Cache' => $cacheHeader];
    }

    /**
     * Resolves StudlyCase module + route for {@see CmsPublicPresentationItemCache} config keys.
     *
     * Universal {@see CmsPublicFrontController} uses {@see CmsPublicFrontViewName} from the resolved model;
     * per-route front controllers fall back to {@see $moduleName} / {@see $routeName}.
     *
     * @return array{moduleName: string, moduleRouteName: string}|null
     */
    protected function resolvePresentationItemCacheContext(Model $item): ?array
    {
        $context = CmsPublicFrontViewName::presentationItemCacheContextForModel($item);
        if ($context !== null) {
            return $context;
        }

        $moduleName = $this->getModuleName();
        $routeName = $this->getRouteName();
        if (
            $moduleName === null || $routeName === null
            || $moduleName === '' || $routeName === ''
            || ($moduleName === 'Cms' && $routeName === 'Public')
        ) {
            return null;
        }

        return [
            'moduleName' => $moduleName,
            'moduleRouteName' => $routeName,
        ];
    }

    /**
     * Blade view for public presentation (default: {@see publicCmsViewName()}). Override when the view depends on the
     * resolved model (e.g. {@see CmsPublicFrontController}).
     */
    protected function resolvePublicPresentationViewName(Request $request, Model $item): string
    {
        return $this->publicCmsViewName();
    }

    protected function resolvePublicPresentationPathKey(Request $request): ?string
    {
        if (! class_exists(CmsVisitorRedirectResolver::class)) {
            return null;
        }

        try {
            [, $pathKey] = app(CmsVisitorRedirectResolver::class)->resolveLocalePathKeyAndExplicitFlag($request);

            return $pathKey !== '' ? $pathKey : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array{moduleName: string, moduleRouteName: string}|null $cacheContext
     */
    protected function resolvePublicPresentationCacheLookupKey(
        Request $request,
        ?array $cacheContext,
        ?string $normalizedPath,
    ): ?string {
        if ($normalizedPath === null || $normalizedPath === '') {
            return null;
        }

        $moduleName = $cacheContext['moduleName'] ?? null;
        $moduleRouteName = $cacheContext['moduleRouteName'] ?? null;

        return ModularousCache::getPresentationUrlCacheKeyResolver()->resolve(
            $request,
            $normalizedPath,
            $moduleName,
            $moduleRouteName,
        );
    }
}
