<?php

use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Modules\Cms\Http\Controllers\Front\PageController;
use Modules\Cms\Http\Middleware\FallbackLocaleSluglessCanonicalMiddleware;
use Modules\Cms\Http\Middleware\VisitorRedirectMiddleware;
use Modules\Cms\Providers\CmsRouteServiceProvider;
use Modules\Cms\Routing\CmsFrontRouteLocalizationBinding;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Routing\CmsPublicSystemRoutes;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsSignedPreviewUrlGenerator;
use Modules\Cms\Services\CmsSlugInputValidationService;
use Modules\Cms\Support\CmsPublicSiteUrl;
use Modules\Cms\Support\CmsSluglessFallbackLocale;
use Unusualify\Modularous\Hydrates\Inputs\SlugHydrate;
use Unusualify\Modularous\Module;

return [
    /**
     * CMS localization bridge: {@code auto} uses mcamara when installed, else translatable.
     *
     * @see CmsLocalizationContract
     */
    'localization_driver' => env('MODULAROUS_CMS_LOCALIZATION_DRIVER', 'auto'),

    /**
     * Public front catch-all shape: {@code catch_all} = single {@code {path}} (locale in first segment, resolved in PHP);
     * {@code locale_param} = {@code {locale}/{path}} route group (mcamara-friendly; requires mcamara stack).
     * Per-locale URL shapes are **not** duplicated via mcamara {@code transRoute('routes.*')} + lang files: they come
     * from {@see Modules\Cms\Entities\UrlRoute} + {@see ParentSegment} at runtime.
     *
     * @see CmsFrontRouteLocalizationBinding
     */
    'public_front_route_group_mode' => env('MODULAROUS_CMS_PUBLIC_FRONT_ROUTE_GROUP_MODE', 'catch_all'),

    /**
     * When {@see public_front_route_group_mode} is {@code locale_param}, append {@code LaravelLocalizationRoutes}.
     */
    'public_front_mcamara_middleware_with_locale_param' => env('MODULAROUS_CMS_PUBLIC_FRONT_MCAMARA_MW_LOCALE_PARAM', true),

    /**
     * When using {@code catch_all}, optionally append mcamara {@code LaravelLocalizationRoutes} (usually false).
     */
    'public_front_mcamara_middleware_with_catch_all' => env('MODULAROUS_CMS_PUBLIC_FRONT_MCAMARA_MW_CATCH_ALL', false),

    /**
     * When {@code mcamara}: read hide-default from {@code config('laravellocalization.hideDefaultLocaleInURL')}.
     * When {@code cms}: use {@see hide_default_locale_segment}.
     * When {@code both}: logical OR of mcamara + CMS flags.
     */
    'localization_hide_default_source' => env('MODULAROUS_CMS_LOCALIZATION_HIDE_DEFAULT_SOURCE', 'mcamara'),

    'locale_strategy' => env('MODULAROUS_CMS_LOCALE_STRATEGY', 'path'),
    'canonical_host' => env('MODULAROUS_CMS_CANONICAL_HOST', parse_url((string) env('APP_URL', ''), PHP_URL_HOST)),
    'default_locale' => env('MODULAROUS_CMS_DEFAULT_LOCALE', env('APP_LOCALE', 'en')),
    'hide_default_locale_segment' => env('MODULAROUS_CMS_HIDE_DEFAULT_LOCALE_SEGMENT', false),

    /**
     * When **true**, the single “slugless fallback” locale (see {@see fallback_locale_optional_path_segment_locale}) is used
     * as the implicit locale for URLs with no `/locale/` prefix — e.g. {@code /pages/test} resolves like {@code fallback} content —
     * and {@code GET /en/pages/test} redirects to strip {@code /en} when {@see UrlRoute} already serves {@code PAGE_PUBLIC}.
     *
     * @see CmsSluglessFallbackLocale
     * @see FallbackLocaleSluglessCanonicalMiddleware
     */
    'fallback_locale_optional_path_segment' => env('MODULAROUS_CMS_FALLBACK_LOCALE_OPTIONAL_PATH_SEGMENT', false),

    /**
     * Explicit locale override for slugless-canonical URLs. {@code null} / empty env → {@code config('translatable.fallback_locale')}
     * then {@see default_locale}.
     */
    'fallback_locale_optional_path_segment_locale' => env('MODULAROUS_CMS_FALLBACK_LOCALE_OPTIONAL_LOCALE') ?: null,

    /**
     * HTTP status for stripping {@code /{slugless}/…} duplicates (typically **301 Moved Permanently** for SEO; **308** preserves method).
     * Clamped to 301–308.
     */
    'fallback_locale_explicit_segment_redirect_status' => max(
        301,
        min(308, (int) env('MODULAROUS_CMS_FALLBACK_LOCALE_EXPLICIT_SEGMENT_REDIRECT_STATUS', 301)),
    ),
    'domain_per_locale' => [
        // Example: 'en' => 'example.com', 'tr' => 'example.com.tr'
    ],
    'redirect_to_canonical' => env('MODULAROUS_CMS_REDIRECT_TO_CANONICAL', false),

    /**
     * Optional: restrict public CMS catch-all routes to this host only (highest priority).
     * When unset, behaviour depends on {@see public_front_routes_allow_any_host}: if false, routes bind to the host
     * parsed from {@code config('app.url')}; if true, routes match any Host header.
     *
     * @see CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain
     * @see CmsPublicSiteUrl
     */
    'public_front_route_domain' => env('MODULAROUS_CMS_PUBLIC_FRONT_ROUTE_DOMAIN'),

    /**
     * When **true**, CMS public catch-all routes are registered **without** {@see Route::domain()} (every host matches).
     * When **false** (default), and {@see public_front_route_domain} is empty, routes bind to {@code parse_url(config('app.url'), PHP_URL_HOST)}.
     *
     * Env: {@code MODULAROUS_CMS_PUBLIC_FRONT_ROUTES_ALLOW_ANY_HOST}
     */
    'public_front_routes_allow_any_host' => env('MODULAROUS_CMS_PUBLIC_FRONT_ROUTES_ALLOW_ANY_HOST', false),

    /**
     * @deprecated Prefer {@see public_front_routes_allow_any_host}. When this key is still present in a published config,
     *             {@code false} behaves like {@code public_front_routes_allow_any_host=true} and {@code true} like {@code false}.
     *             Omit in new projects.
     */
    'bind_public_routes_to_app_url_host' => env('MODULAROUS_CMS_BIND_PUBLIC_ROUTES_TO_APP_URL_HOST'),

    /**
     * Explicit list of locale codes allowed as the first path segment ({@code /en/...}, {@code /tr/...}).
     * When empty, uses mcamara/laravel-localization supported keys if installed, else {@see getLocales()} / translatable.
     *
     * @var list<string>|null
     */
    'path_segment_locales' => null,

    /** First segment of public CMS routes; must match module `url` / Route::prefix (see `modules/Cms/Routes/front.php`). */
    'front_route_prefix' => env('MODULAROUS_CMS_FRONT_ROUTE_PREFIX', ''),

    /** Serve public pages when {@see CmsFrontRouteRegistrar} resolves a front controller (see {@see PageController}). */
    'public_pages_enabled' => env('MODULAROUS_CMS_PUBLIC_PAGES_ENABLED', true),

    /**
     * When true, {@see CmsRouteServiceProvider} registers one public catch-all ({@see CmsPublicFrontController}
     * by default) when the ParentSegment registry has enabled rows. Set {@see universal_cms_public_front} to
     * {@code false} for legacy per-module catch-alls.
     */
    'auto_register_public_front' => env('MODULAROUS_CMS_AUTO_REGISTER_PUBLIC_FRONT', true),

    /**
     * Cache {@see CmsFrontRouteRegistrar} module qualification scans across requests ({@see CmsFrontRouteRegistrationCache}).
     * Keys include {@see CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision()} and module route fingerprint.
     */
    'front_route_registration_cache_enabled' => env('MODULAROUS_CMS_FRONT_ROUTE_REGISTRATION_CACHE', true),

    /** Laravel cache key prefix for {@see CmsFrontRouteRegistrationCache} (revision + module fingerprint are appended). */
    'front_route_registration_cache_key' => env(
        'MODULAROUS_CMS_FRONT_ROUTE_REGISTRATION_CACHE_KEY',
        'modularous_cms.front_route_registration_v1'
    ),

    /**
     * Cache store for ParentSegment / UrlRoute registry revisions and front route registration snapshots.
     * Default {@code file} so boot-time route registration does not depend on the app default {@code CACHE_STORE}.
     */
    'public_url_registry_cache_store' => env('MODULAROUS_CMS_PUBLIC_URL_REGISTRY_CACHE_STORE', 'file'),

    /**
     * When true, {@see ParentSegmentUrlRouteObserver} rebuilds the registration cache immediately after invalidation
     * (useful after deploy; default false so admin saves stay fast).
     */
    'warm_front_route_registration_cache_on_invalidate' => env('MODULAROUS_CMS_WARM_FRONT_ROUTE_REGISTRATION_CACHE', false),

    /** Forget committed sitemap XML when ParentSegment registry is invalidated (admin prefix edits). */
    'forget_sitemap_on_parent_segment_registry_invalidate' => env('MODULAROUS_CMS_FORGET_SITEMAP_ON_PARENT_SEGMENT_INVALIDATE', true),

    /** Forget committed sitemap XML when UrlRoute rows are synced from entity saves (default false — use {@code cms:public-url-registry:cache --sitemap}). */
    'forget_sitemap_on_url_route_registry_touch' => env('MODULAROUS_CMS_FORGET_SITEMAP_ON_URL_ROUTE_TOUCH', false),

    /**
     * When true, the Cms public catch-all uses {@see CmsPublicFrontController}
     * and {@see CmsPublicModelResolver::resolveForParentSegmentRegistry()}: any
     * {@link \Modules\Cms\Entities\UrlRoute} line whose urlable is an enabled {@link \Modules\Cms\Entities\ParentSegment}
     * target with {@link \Unusualify\Modularous\Entities\Traits\HasParentSegment}. When false, the first
     * {@code front-controller/...} per submodule (legacy) is used. {@see public_front_handlers} is then honored
     * again for {@see CmsFrontRouteRegistrar::resolveFrontControllerForModelClass()}.
     */
    'universal_cms_public_front' => env('MODULAROUS_CMS_UNIVERSAL_PUBLIC_FRONT', true),

    /**
     * Optional: model FQCN => Blade name when the automatic {@code cms::{route}.custom} discovery is not enough.
     *
     * @var array<class-string, string>
     */
    'public_front_views_by_model' => [],

    /**
     * If submodule discovery and {@see public_front_views_by_model} do not match the resolved model.
     */
    'universal_public_front_fallback_view' => 'cms::page.custom',

    /**
     * Optional override: {@see ParentSegment} {@code target_model_class} FQCN → invokable front
     * controller (must extend {@see CmsController}). When empty, resolution uses
     * {@see Module::getTargetClassNamespace()} with {@code front-controller} + {@code {StudlyRoute}Controller}.
     * Ignored for the public catch-all when {@see universal_cms_public_front} is true.
     *
     * @var array<class-string, class-string>
     */
    'public_front_handlers' => [],

    /** Apply {@see VisitorRedirectMiddleware} on the CMS front stack. */
    'visitor_redirects_enabled' => env('MODULAROUS_CMS_VISITOR_REDIRECTS_ENABLED', true),

    /**
     * Admin panel (slug validation, path preview).
     *
     * @see CmsSlugInputValidationService
     * @see SlugHydrate
     */
    'admin' => [
        'slug_nested_path_warnings' => env('MODULAROUS_CMS_ADMIN_SLUG_NESTED_WARNINGS', true),
        'slug_public_path_preview' => env('MODULAROUS_CMS_ADMIN_SLUG_PUBLIC_PATH_PREVIEW', true),
        /**
         * Max number of URL path segments allowed in the slug field (split on `/`), after trimming.
         * `null` = no limit (default). Set to `1` to forbid nested paths such as `parent/child` in admin.
         *
         * @see CmsSlugInputValidationService
         */
        'slug_max_path_segments' => env('MODULAROUS_CMS_ADMIN_SLUG_MAX_PATH_SEGMENTS') !== null
            && env('MODULAROUS_CMS_ADMIN_SLUG_MAX_PATH_SEGMENTS') !== ''
            ? max(1, (int) env('MODULAROUS_CMS_ADMIN_SLUG_MAX_PATH_SEGMENTS'))
            : null,
    ],

    /**
     * When a {@see ParentSegment} row is created/updated/deleted, re-sync {@see Modules\Cms\Entities\UrlRoute}
     * for all instances of {@code target_model_class} ({@see CmsUrlRouteRegistry::syncPublicPageRoutesForAllModelsOfClass}).
     */
    'resync_registry_after_parent_segments_change' => env('MODULAROUS_CMS_RESYNC_URL_ROUTES_AFTER_PARENT_SEGMENTS_CHANGE', true),

    /**
     * Batch size when walking models after parent-segment edits (Slug / IsSingular targets only).
     */
    'parent_segment_change_resync_chunk_size' => max(1, (int) env('MODULAROUS_CMS_PARENT_SEGMENT_RESYNC_CHUNK', 100)),

    /**
     * Additional slash-trimmed path prefixes the CMS public catch-all `{path}` must ignore (merged with
     * enabled built-ins from {@see CmsPublicSystemRoutes}). Use for host-app paths
     * when a package registers routes before the late catch-all and registration order alone is not enough.
     *
     * @var list<string>
     *
     * @see CmsPublicSystemRoutes::reservedPathPrefixes()
     * @see CmsFrontRouteRegistrar::catchAllPathParameterPattern()
     */
    'public_front_catch_all_exclude_path_prefixes' => [],

    /**
     * Time-limited signed URLs for sharing CMS page preview without a panel session.
     *
     * @see CmsSignedPublicPreviewController
     * @see CmsSignedPreviewUrlGenerator
     */
    'signed_preview' => [
        'enabled' => env('MODULAROUS_CMS_SIGNED_PREVIEW_ENABLED', true),
        'path_prefix' => trim((string) env('MODULAROUS_CMS_SIGNED_PREVIEW_PATH_PREFIX', 'cms/preview'), '/'),
        'ttl_minutes' => max(5, (int) env('MODULAROUS_CMS_SIGNED_PREVIEW_TTL_MINUTES', 60)),
        /** Laravel throttle: max attempts per decay minutes (per IP) for the signed preview route. */
        'throttle_max_attempts' => max(1, (int) env('MODULAROUS_CMS_SIGNED_PREVIEW_THROTTLE_MAX', 120)),
        'throttle_decay_minutes' => max(1, (int) env('MODULAROUS_CMS_SIGNED_PREVIEW_THROTTLE_DECAY', 1)),
    ],
];
