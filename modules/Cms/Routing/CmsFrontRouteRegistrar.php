<?php

namespace Modules\Cms\Routing;

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Modules\Cms\Providers\CmsRouteServiceProvider;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsFrontRouteRegistrationCache;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Module;

/**
 * Registers the CMS public catch-all route when {@see ParentSegment} has enabled rows for a routable model.
 *
 * Front invokable controllers are resolved per module route via {@see Module::getTargetClassNamespace()} with
 * {@code front-controller} + {@code {StudlyRoute}Controller} (see {@code config/publishes/modules.php} generator map).
 * The class must exist and extend {@see CmsController}. Optional overrides:
 * {@see modularousConfig('cms_routing.public_front_handlers')} keyed by model FQCN.
 *
 * **Mcamara docs vs this stack:** mcamara often shows LaravelLocalization::setLocale() plus per-route
 * transRoute() keys backed by per-locale routes.php under resources/lang — one named route per translated URL shape.
 * Here we register GET catch-all(s); with {@see CmsFrontRouteLocalizationBinding} the route may use a real
 * locale segment plus path. The `{path}` wildcard intentionally **does not** match {@see modularousConfig('cms_routing.signed_preview.path_prefix')}
 * (and optional {@see modularousConfig('cms_routing.public_front_catch_all_exclude_path_prefixes')}) so signed preview URLs resolve to
 * {@see CmsSignedPublicPreviewController} instead of being eaten by the CMS page resolver. Resolution runs in {@see CmsPublicModelResolver} against
 * {@see UrlRoute} (per-locale normalized_path) and optional {@see ParentSegment}
 * prefixes. Translated segments and slug binding live in the CMS data model, not duplicated Route definitions or lang route files.
 *
 * Auto-registration: {@see registerAutoForQualifiedModules()} — {@see CmsRouteServiceProvider}.
 * With {@see modularousConfig('cms_routing.universal_cms_public_front')} (default), one host-level catch-all
 * ({@see CmsPublicFrontController}) resolves all {@see UrlRoute} lines via {@see CmsPublicModelResolver}.
 * Legacy mode registers per-module catch-alls when {@code universal_cms_public_front} is false.
 */
final class CmsFrontRouteRegistrar
{
    /**
     * Hostname for {@see Route::domain()} when public routes must not respond on every incoming Host header.
     *
     * Priority: explicit {@see modularousConfig('cms_routing.public_front_route_domain')} → else {@code null} only when
     * {@see publicFrontRoutesAllowAnyHost()} is true ({@see modularousConfig('cms_routing.public_front_routes_allow_any_host')}
     * or deprecated inverted {@see modularousConfig('cms_routing.bind_public_routes_to_app_url_host')} when set).
     * Otherwise reads the host from {@code config('app.url')}.
     *
     * Laravel expects only the host, not a full URL.
     */
    public static function resolvePublicFrontRouteDomain(): ?string
    {
        $configured = modularousConfig('cms_routing.public_front_route_domain');
        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        if (self::publicFrontRoutesAllowAnyHost()) {
            return null;
        }

        $appUrl = config('app.url');
        if (! is_string($appUrl) || $appUrl === '') {
            return null;
        }

        $host = parse_url($appUrl, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    /**
     * True when CMS catch-all routes should match any incoming Host header (no {@see Route::domain()}).
     *
     * {@see modularousConfig('cms_routing.public_front_routes_allow_any_host')} wins when {@code true}.
     * Deprecated: when {@see modularousConfig('cms_routing.bind_public_routes_to_app_url_host')} is non-null,
     * {@code false} means “match any host” (legacy default) and {@code true} binds to {@code APP_URL} host — inverted.
     */
    private static function publicFrontRoutesAllowAnyHost(): bool
    {
        if ((bool) modularousConfig('cms_routing.public_front_routes_allow_any_host', false)) {
            return true;
        }

        $bindLegacy = config('modularous.cms_routing.bind_public_routes_to_app_url_host');
        if ($bindLegacy !== null) {
            return ! (bool) $bindLegacy;
        }

        return false;
    }

    /**
     * Registers the CMS public catch-all when the ParentSegment registry has enabled rows.
     *
     * Universal (default): one {@see CmsPublicFrontController} on the public host — paths come from {@see UrlRoute}.
     * Legacy: one catch-all per qualifying module ({@see registerLegacyModuleCatchAlls()}).
     */
    public static function registerAutoForQualifiedModules(): void
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        if (Modularous::isPanelUrl()) {
            return;
        }

        $controller = self::resolveControllerClassOrNull();
        if ($controller === null) {
            return;
        }

        if (CmsFrontRouteRegistrationCache::usesUniversalPublicFront()) {
            self::registerPublicFrontCatchAll($controller, self::cmsRouteNamePrefix());

            return;
        }

        self::registerLegacyModuleCatchAlls();
    }

    /**
     * @return non-empty-string
     */
    private static function cmsRouteNamePrefix(): string
    {
        $cmsModule = Modularous::find('Cms') ?? Modularous::find('cms');

        if ($cmsModule instanceof Module) {
            return $cmsModule->routeNamePrefix() . '.';
        }

        return 'cms.';
    }

    /**
     * Single host-level catch-all used in universal mode.
     *
     * @param class-string $controller
     * @param non-empty-string $routeNamePrefix
     */
    private static function registerPublicFrontCatchAll(string $controller, string $routeNamePrefix): void
    {
        $group = ['as' => $routeNamePrefix];

        $domain = self::resolvePublicFrontRouteDomain();
        if ($domain !== null) {
            $group['domain'] = $domain;
        }

        Route::group($group, static function () use ($controller): void {
            self::registerInnerCatchAll($controller);
        });
    }

    /**
     * Legacy: register a catch-all per module whose front controller resolves (see {@see registerUnderModulePrefix()}).
     */
    private static function registerLegacyModuleCatchAlls(): void
    {
        foreach (CmsFrontRouteRegistrationCache::legacyQualifiedModules() as $moduleName => $controller) {
            $module = Modularous::find($moduleName);
            if (! $module instanceof Module) {
                continue;
            }

            self::registerUnderModulePrefix($module, $controller);
        }
    }

    /**
     * True when this module has at least one enabled route whose model uses {@see HasParentSegment} and whose
     * generated front controller exists and extends {@see CmsController}.
     */
    public static function moduleQualifiesForAutoPublicFront(Module $module): bool
    {
        if (CmsFrontRouteRegistrationCache::usesUniversalPublicFront()) {
            return mb_strtolower($module->getName()) === 'cms'
                && self::resolveControllerClassOrNull() !== null;
        }

        return CmsFrontRouteRegistrationCache::frontControllerForModule($module) !== null;
    }

    /**
     * First invokable front controller for this module (CMS public stack), or null.
     *
     * @return class-string|null
     */
    public static function resolveFrontControllerForModule(Module $module): ?string
    {
        return CmsFrontRouteRegistrationCache::frontControllerForModule($module);
    }

    private static function resolveModelClassForRoute(Module $module, string $routeName): ?string
    {
        if (! $module->isEnabledRoute($routeName)) {
            return null;
        }

        try {
            $modelClass = $module->getModel($routeName, false);

            return is_string($modelClass) && $modelClass !== '' ? $modelClass : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Invokable {@see CmsController} for a specific enabled submodule (signed preview, legacy per-route).
     *
     * @return class-string<CmsController>|null
     */
    public static function resolveFrontControllerForModuleRoute(Module $module, string $routeName): ?string
    {
        if (! $module->isEnabledRoute($routeName)) {
            return null;
        }

        $modelClass = self::resolveModelClassForRoute($module, $routeName);
        if ($modelClass === null || ! classHasTrait($modelClass, HasParentSegment::class)) {
            return null;
        }

        return self::resolveFrontControllerForModelClass($modelClass);
    }

    /**
     * {@code GET {modulePrefix}/{path?}} with CMS middleware stack.
     *
     * @param class-string|null $controller Pre-resolved invokable controller (skips another qualification scan).
     */
    public static function registerUnderModulePrefix(Module $module, ?string $controller = null): void
    {
        $controller ??= self::resolveFrontControllerForModule($module);
        if ($controller === null) {
            return;
        }

        $routeNamePrefix = $module->routeNamePrefix() . '.';

        $group = [
            // 'prefix' => $module->prefix(),
            'as' => $routeNamePrefix,
        ];

        $domain = self::resolvePublicFrontRouteDomain();
        if ($domain !== null) {
            $group['domain'] = $domain;
        }

        Route::group($group, function () use ($controller): void {
            self::registerInnerCatchAll($controller);
        });
    }

    /**
     * Register middleware + optional `GET /{path?}` in the current route group (relative path).
     *
     * Used by {@see registerUnderModulePrefix()} and legacy {@see Route::cmsPublicFrontRoutes()} macro.
     */
    public static function register(): void
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        $controller = self::resolveControllerClassOrNull();
        if ($controller === null) {
            return;
        }

        self::registerInnerCatchAll($controller);
    }

    /**
     * @param class-string $controller Invokable front controller FQCN
     */
    private static function registerInnerCatchAll(string $controller): void
    {
        $middlewares = self::resolveMiddlewareStack();

        /*
         * `locale_param` mode wraps routes as `{locale}/{path}` — URLs without a leading locale (slugless canonical)
         * never match unless we also register `{path}` on the same host (see fallback_locale_optional_path_segment).
         * `catch_all` keeps a single `{path}`; locale lives inside `{path}` and is parsed in PHP.
         */
        if (! CmsFrontRouteLocalizationBinding::shouldUseLocalePrefixRouteGroup()) {
            self::registerPathCatchAllRoute($controller, $middlewares, 'page');

            return;
        }

        CmsFrontRouteLocalizationBinding::wrapLocalizedRouteGroupIfEnabled(static function () use ($controller, $middlewares): void {
            self::registerPathCatchAllRoute($controller, $middlewares, 'page.locale');
        });

        if ((bool) modularousConfig('cms_routing.fallback_locale_optional_path_segment', false)) {
            self::registerPathCatchAllRoute($controller, $middlewares, 'page');
        }
    }

    /**
     * @param string $suffix Route name suffix after the owning module prefix (typically {@code cms.} → {@code cms.page}).
     */
    private static function registerPathCatchAllRoute(string $controller, array $middlewares, string $suffix): void
    {
        $pathPattern = self::catchAllPathParameterPattern();

        Route::middleware($middlewares)->group(static function () use ($controller, $suffix, $pathPattern): void {
            Route::get('{path}', $controller)
                ->where('path', $pathPattern)
                ->name($suffix);
        });
    }

    /**
     * {@code {path}} constraint so reserved top-level segments (signed public preview, etc.) reach their own routes.
     */
    private static function catchAllPathParameterPattern(): string
    {
        $blocked = [];

        if (modularousConfig('cms_routing.signed_preview.enabled', true)) {
            $preview = trim((string) modularousConfig('cms_routing.signed_preview.path_prefix', 'cms/preview'), '/');
            if ($preview !== '') {
                $blocked[] = preg_quote($preview, '/');
            }
        }

        foreach ((array) modularousConfig('cms_routing.public_front_catch_all_exclude_path_prefixes', []) as $raw) {
            if (! is_string($raw)) {
                continue;
            }
            $p = trim($raw, '/');
            if ($p !== '') {
                $blocked[] = preg_quote($p, '/');
            }
        }

        if ((bool) modularousConfig('cms_stylesheets.public_route.enabled', true)) {
            $ss = trim((string) modularousConfig('cms_stylesheets.public_route.path_prefix', 'cms/stylesheets'), '/');
            if ($ss !== '') {
                $blocked[] = preg_quote($ss, '/');
            }
        }

        $blocked = array_values(array_unique($blocked));
        if ($blocked === []) {
            return '.*';
        }

        $alt = implode('|', $blocked);

        return '^(?!(?:' . $alt . ')(?:/|$)).*$';
    }

    /**
     * @return list<string>
     */
    private static function resolveMiddlewareStack(): array
    {
        $register = modularousConfig('cms_features.register_middlewares', true);

        $useCanonicalLocaleMiddleware = $register
            && modularousConfig('cms_routing.redirect_to_canonical', false);

        $useFallbackSluglessCanonicalMiddleware = $register
            && (bool) modularousConfig('cms_routing.fallback_locale_optional_path_segment', false);

        $useVisitorRedirect = $register
            && modularousConfig('cms_routing.visitor_redirects_enabled', true);

        $useMcamaraRoutesMiddleware = $register && self::shouldAppendMcamaraRoutesMiddleware();

        return array_values(array_filter([
            'web',
            $register ? 'modules.cms.url_stale.serve' : null,
            $useFallbackSluglessCanonicalMiddleware ? 'modules.cms.fallback.slugless.canonical' : null,
            $useMcamaraRoutesMiddleware ? LaravelLocalizationRoutes::class : null,
            $useCanonicalLocaleMiddleware ? 'modules.cms.canonical.locale' : null,
            $useVisitorRedirect ? 'modules.cms.visitor.redirect' : null,
        ]));
    }

    private static function shouldAppendMcamaraRoutesMiddleware(): bool
    {
        if (! class_exists(LaravelLocalization::class)) {
            return false;
        }

        if (CmsFrontRouteLocalizationBinding::shouldUseLocalePrefixRouteGroup()) {
            return (bool) modularousConfig('cms_routing.public_front_mcamara_middleware_with_locale_param', true);
        }

        return (bool) modularousConfig('cms_routing.public_front_mcamara_middleware_with_catch_all', false);
    }

    /**
     * Ensure URL stale middleware is on CMS public catch-alls (e.g. route cache built before it existed).
     */
    public static function syncUrlStaleServeMiddlewareOnRegisteredPublicFrontRoutes(): void
    {
        if (! modularousConfig('cms_features.register_middlewares', true)) {
            return;
        }

        if (! ModularousCache::isUrlStaleServeFirst()) {
            return;
        }

        /** @var array<string, true> $routeNames */
        $routeNames = [];

        foreach (Route::getRoutes() as $route) {
            if (! $route instanceof \Illuminate\Routing\Route) {
                continue;
            }

            if (! self::routeUsesCmsPublicFrontController($route)) {
                continue;
            }

            $name = $route->getName();
            if (is_string($name) && $name !== '') {
                $routeNames[$name] = true;

                continue;
            }

            self::patchUrlStaleMiddlewareOnRoute($route);
        }

        foreach (array_keys($routeNames) as $name) {
            $target = Route::getRoutes()->getByName($name);
            if ($target instanceof \Illuminate\Routing\Route) {
                self::patchUrlStaleMiddlewareOnRoute($target);
            }
        }
    }

    private static function patchUrlStaleMiddlewareOnRoute(\Illuminate\Routing\Route $route): void
    {
        $alias = 'modules.cms.url_stale.serve';
        $middleware = array_values($route->middleware());
        if (in_array($alias, $middleware, true)) {
            return;
        }

        $webIndex = array_search('web', $middleware, true);
        if ($webIndex === false) {
            array_unshift($middleware, $alias);
        } else {
            array_splice($middleware, $webIndex + 1, 0, [$alias]);
        }

        $action = $route->getAction();
        $action['middleware'] = array_values($middleware);
        $route->setAction($action);
    }

    private static function routeUsesCmsPublicFrontController(\Illuminate\Routing\Route $route): bool
    {
        $controller = $route->getAction('controller');
        if (is_string($controller) && $controller !== '' && is_subclass_of($controller, CmsController::class, true)) {
            return true;
        }

        $uses = $route->getAction('uses');
        if (! is_string($uses) || $uses === '') {
            return false;
        }

        $class = str_contains($uses, '@') ? strstr($uses, '@', true) : $uses;

        return is_string($class) && $class !== '' && is_subclass_of($class, CmsController::class, true);
    }

    /**
     * First resolvable front controller for any enabled {@see ParentSegment} target (global gate + legacy macro).
     *
     * @return class-string|null
     */
    public static function resolveControllerClassOrNull(): ?string
    {
        if (! modularousConfig('cms_routing.public_pages_enabled', true)) {
            return null;
        }

        if (! database_exists()) {
            return self::resolveControllerForUrlStaleResilienceOrNull();
        }

        if (! CmsFrontRouteRegistrationCache::parentSegmentTableReady()) {
            return null;
        }

        if (! CmsFrontRouteRegistrationCache::hasEnabledParentSegments()) {
            return null;
        }

        return CmsFrontRouteRegistrationCache::publicFrontCatchAllControllerOrNull();
    }

    /**
     * Keep the public catch-all routable when URL-keyed stale serve-first is on but the DB is unavailable.
     *
     * @return class-string|null
     */
    private static function resolveControllerForUrlStaleResilienceOrNull(): ?string
    {
        if (! ModularousCache::isUrlStaleServeFirst()) {
            return null;
        }

        $fromSnapshot = CmsFrontRouteRegistrationCache::publicFrontCatchAllControllerOrNull();
        if ($fromSnapshot !== null) {
            return $fromSnapshot;
        }

        if (CmsFrontRouteRegistrationCache::usesUniversalPublicFront() && class_exists(CmsPublicFrontController::class)) {
            return CmsPublicFrontController::class;
        }

        return null;
    }

    /**
     * Resolve invokable front controller for a concrete model class (ParentSegment {@code target_model_class}).
     * Uses {@see modularousConfig('cms_routing.public_front_handlers')} override when set, otherwise
     * {@see Module::getTargetClassNamespace()} + {@see CmsController} subclass check.
     *
     * @param class-string $modelClass
     * @return class-string|null
     */
    public static function resolveFrontControllerForModelClass(string $modelClass): ?string
    {
        return CmsFrontRouteRegistrationCache::frontControllerForModelClass($modelClass);
    }
}
