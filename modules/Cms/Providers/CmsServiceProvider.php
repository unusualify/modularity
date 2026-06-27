<?php

namespace Modules\Cms\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Console\CacheCmsPublicUrlRegistryCommand;
use Modules\Cms\Console\ClearCmsPublicUrlRegistryCacheCommand;
use Modules\Cms\Console\OptimizeClearCmsPublicUrlRegistryCommand;
use Modules\Cms\Console\OptimizeCmsPublicUrlRegistryCommand;
use Modules\Cms\Console\PublishLayoutBuilderBladeCommand;
use Modules\Cms\Console\RebuildCmsSitemapCommand;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsLocalizationOverrideProviderInterface;
use Modules\Cms\Contracts\CmsPromotionScopeApplierInterface;
use Modules\Cms\Contracts\CmsSearchDriverInterface;
use Modules\Cms\Contracts\LeadDeliveryInterface;
use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Contracts\RedirectValidationServiceInterface;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\Front\PublicSitemapController;
use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Modules\Cms\Http\Controllers\PublicStyleSheetAssetController;
use Modules\Cms\Http\Middleware\CanonicalLocaleMiddleware;
use Modules\Cms\Http\Middleware\FallbackLocaleSluglessCanonicalMiddleware;
use Modules\Cms\Http\Middleware\LayoutBuilderMiddleware;
use Modules\Cms\Http\Middleware\VisitorRedirectMiddleware;
use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;
use Modules\Cms\Localization\DelegatingCmsLocalizationAdapter;
use Modules\Cms\Localization\McamaraCmsLocalizationAdapter;
use Modules\Cms\Localization\NullCmsLocalizationOverrideProvider;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Observers\ParentSegmentUrlRouteObserver;
use Modules\Cms\Support\CmsPublicUrlRegistryAboutReporter;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsAdminWarnings;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsPromotionService;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsSignedPreviewTargetResolver;
use Modules\Cms\Services\CmsSignedPreviewUrlGenerator;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Modules\Cms\Services\CmsSlugInputValidationService;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Modules\Cms\Services\DbFullTextSearchDriver;
use Modules\Cms\Services\DefaultCmsPromotionScopeApplier;
use Modules\Cms\Services\NullLeadDelivery;
use Modules\Cms\Services\RedirectValidationService;
use Modules\Cms\Services\Stylesheet\FrameworkArtifactResolver;
use Modules\Cms\Services\Stylesheet\FrameworkScriptResolver;
use Modules\Cms\Services\Stylesheet\RootVariablesEmitter;
use Modules\Cms\Services\Stylesheet\ScssStylesheetCompiler;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;
use Modules\Cms\Services\Stylesheet\UtilityCssGenerator;
use Unusualify\Modularous\Services\Security\SecurityService;
use Unusualify\Modularous\Services\SlugInputValidationService;

class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->app->register(CmsRouteServiceProvider::class);

        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);

        $this->app->singleton(CmsLocalizationOverrideProviderInterface::class, NullCmsLocalizationOverrideProvider::class);
        $this->app->singleton(CmsLocalizationContract::class, function ($app) {
            $driver = (string) modularousConfig('cms_routing.localization_driver', 'auto');
            $canonical = $app->make(CanonicalUrlResolverInterface::class);

            $inner = match (true) {
                $driver === 'mcamara' => new McamaraCmsLocalizationAdapter($canonical),
                $driver === 'translatable' => new TranslatableCmsLocalizationAdapter($canonical),
                $driver === 'auto' && class_exists(LaravelLocalization::class) => new McamaraCmsLocalizationAdapter($canonical),
                default => new TranslatableCmsLocalizationAdapter($canonical),
            };

            return new DelegatingCmsLocalizationAdapter($inner, $app->make(CmsLocalizationOverrideProviderInterface::class));
        });

        $this->app->singleton(CmsParentSegmentResolver::class);
        $this->app->singleton(CmsPageLayoutResolver::class);
        $this->app->singleton(CmsVisitorRedirectResolver::class);
        $this->app->singleton(CmsPublicModelResolver::class);

        $this->app->singleton(CmsUrlRouteRegistry::class);
        $this->app->singleton(CmsPublicUrlRegistryCacheManager::class);
        $this->app->bind(PublicUrlRegistryContract::class, fn ($app) => $app->make(CmsUrlRouteRegistry::class));
        $this->app->singleton(CmsSitemapBuildService::class);
        $this->app->singleton(CmsSitemapCacheService::class);
        $this->app->singleton(CmsAdminWarnings::class);
        $this->app->singleton(CmsSiteSeoSettingsService::class);
        $this->app->singleton(SlugInputValidationService::class, CmsSlugInputValidationService::class);
        $this->app->singleton(CmsSignedPreviewUrlGenerator::class);
        $this->app->singleton(CmsSignedPreviewTargetResolver::class);

        $this->app->singleton(RootVariablesEmitter::class);
        $this->app->singleton(UtilityCssGenerator::class);
        $this->app->singleton(FrameworkArtifactResolver::class);
        $this->app->singleton(FrameworkScriptResolver::class);
        $this->app->singleton(ScssStylesheetCompiler::class);
        $this->app->singleton(StylesheetCompilerService::class);

        $this->app->singleton(RedirectValidationServiceInterface::class, RedirectValidationService::class);

        if (! modularousConfig('cms_features.register_contracts', true)) {
            return;
        }

        $this->app->singleton(CmsPromotionScopeApplierInterface::class, DefaultCmsPromotionScopeApplier::class);
        $this->app->singleton(CmsPromotionService::class, fn ($app) => new CmsPromotionService(
            $app->make(SecurityService::class),
            $app->make(CmsPromotionScopeApplierInterface::class),
        ));

        $this->app->singleton(LeadDeliveryInterface::class, NullLeadDelivery::class);
        $this->app->singleton(CmsSearchDriverInterface::class, DbFullTextSearchDriver::class);
    }

    public function boot(): void
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        if ($this->app->runningInConsole()) {
            AboutCommand::add(
                'Modularous:Cms',
                fn () => $this->app->make(CmsPublicUrlRegistryAboutReporter::class)->report(),
            );
        }

        if (modularousConfig('cms_features.register_commands', true)) {
            if ($this->app->runningInConsole()) {
                $this->commands([
                    RebuildCmsSitemapCommand::class,
                    PublishLayoutBuilderBladeCommand::class,
                    ClearCmsPublicUrlRegistryCacheCommand::class,
                    CacheCmsPublicUrlRegistryCommand::class,
                    OptimizeClearCmsPublicUrlRegistryCommand::class,
                    OptimizeCmsPublicUrlRegistryCommand::class,
                ]);
            }
        }

        if (modularousConfig('cms_features.register_middlewares', true)) {
            Route::aliasMiddleware('modules.cms.canonical.locale', CanonicalLocaleMiddleware::class);
            Route::aliasMiddleware('modules.cms.fallback.slugless.canonical', FallbackLocaleSluglessCanonicalMiddleware::class);
            Route::aliasMiddleware('modules.cms.visitor.redirect', VisitorRedirectMiddleware::class);
            Route::aliasMiddleware('modules.cms.layout_builder', LayoutBuilderMiddleware::class);
        }

        if (modularousConfig('cms_seo.robots.route_enabled', true)) {
            Route::middleware('web')->get('/robots.txt', RobotsTxtController::class)->name('cms.robots_txt');
        }

        if ((bool) modularousConfig('cms_sitemap.route_enabled', true)) {
            Route::middleware('web')->get('/sitemap.xml', PublicSitemapController::class)->name('cms.sitemap');
        }

        if ((bool) modularousConfig('cms_routing.resync_registry_after_parent_segments_change', true)) {
            ParentSegment::observe(ParentSegmentUrlRouteObserver::class);
        }

        $this->registerCmsSignedPreviewRoutes();
        $this->registerCmsPublicStylesheetRoutes();
        $this->registerCmsPublishSchedule();
    }

    private function registerCmsSignedPreviewRoutes(): void
    {
        if (! modularousConfig('cms_routing.signed_preview.enabled', true)) {
            return;
        }

        $prefix = trim((string) modularousConfig('cms_routing.signed_preview.path_prefix', 'cms/preview'), '/');
        if ($prefix === '') {
            return;
        }

        $max = (int) modularousConfig('cms_routing.signed_preview.throttle_max_attempts', 120);
        $decay = (int) modularousConfig('cms_routing.signed_preview.throttle_decay_minutes', 1);
        $throttle = 'throttle:' . max(1, $max) . ',' . max(1, $decay);

        $definition = static function () use ($prefix, $throttle): void {
            Route::middleware(['web', 'signed', $throttle])
                ->get($prefix . '/{module}/{route}/{id}/{locale?}', CmsSignedPublicPreviewController::class)
                ->where([
                    'module' => '[A-Za-z][A-Za-z0-9]*',
                    'route' => '[A-Za-z][A-Za-z0-9]*',
                    'id' => '[0-9]+',
                ])
                ->name('cms.signed_preview.show');
        };

        $domain = CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain();
        if ($domain !== null && $domain !== '') {
            Route::domain($domain)->group($definition);
        } else {
            $definition();
        }
    }

    private function registerCmsPublicStylesheetRoutes(): void
    {
        if (! (bool) modularousConfig('cms_stylesheets.public_route.enabled', true)) {
            return;
        }

        $prefix = trim((string) modularousConfig('cms_stylesheets.public_route.path_prefix', 'cms/stylesheets'), '/');
        if ($prefix === '') {
            return;
        }

        $definition = static function () use ($prefix): void {
            Route::middleware('web')
                ->get($prefix . '/{slug}.css', [PublicStyleSheetAssetController::class, 'show'])
                ->where('slug', '[A-Za-z0-9_-]+')
                ->name('cms.public.stylesheet');
        };

        $domain = CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain();
        if ($domain !== null && $domain !== '') {
            Route::domain($domain)->group($definition);
        } else {
            $definition();
        }
    }

    private function registerCmsPublishSchedule(): void
    {
        if (! modularousConfig('cms_schedule.register_with_laravel_schedule', false)) {
            return;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $job = $schedule->job(new ScanCmsPublishWindowBoundariesJob);
            $frequency = (string) modularousConfig('cms_schedule.frequency', 'everyFiveMinutes');

            match ($frequency) {
                'everyMinute' => $job->everyMinute(),
                'hourly' => $job->hourly(),
                default => $job->everyFiveMinutes(),
            };
        });
    }
}
