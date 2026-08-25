<?php

namespace Modules\Cms\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
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
use Modules\Cms\Contracts\CmsVisitorRequestContextResolverInterface;
use Modules\Cms\Contracts\LeadDeliveryInterface;
use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Contracts\RedirectValidationServiceInterface;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Http\Middleware\CanonicalLocaleMiddleware;
use Modules\Cms\Http\Middleware\FallbackLocaleSluglessCanonicalMiddleware;
use Modules\Cms\Http\Middleware\LayoutBuilderMiddleware;
use Modules\Cms\Http\Middleware\ServeUrlKeyedStaleMiddleware;
use Modules\Cms\Http\Middleware\VisitorRedirectMiddleware;
use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;
use Modules\Cms\Localization\DelegatingCmsLocalizationAdapter;
use Modules\Cms\Localization\McamaraCmsLocalizationAdapter;
use Modules\Cms\Localization\NullCmsLocalizationOverrideProvider;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Observers\ParentSegmentUrlRouteObserver;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Routing\CmsPublicSystemRouteRegistrar;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsAdminWarnings;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsPromotionService;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsSettingsService;
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
use Modules\Cms\Services\SiteSettingsService;
use Modules\Cms\Services\Stylesheet\FrameworkArtifactResolver;
use Modules\Cms\Services\Stylesheet\FrameworkScriptResolver;
use Modules\Cms\Services\Stylesheet\RootVariablesEmitter;
use Modules\Cms\Services\Stylesheet\ScssStylesheetCompiler;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;
use Modules\Cms\Services\Stylesheet\UtilityCssGenerator;
use Modules\Cms\Support\CmsPublicUrlRegistryAboutReporter;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;
use Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
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
        $this->app->singleton(CmsVisitorRequestContextResolverInterface::class, CmsVisitorRedirectResolver::class);
        $this->app->singleton(CmsPublicModelResolver::class);

        $this->app->singleton(CmsUrlRouteRegistry::class);
        $this->app->singleton(CmsPublicUrlRegistryCacheManager::class);
        $this->app->bind(PublicUrlRegistryContract::class, fn ($app) => $app->make(CmsUrlRouteRegistry::class));
        $this->app->singleton(CmsSitemapBuildService::class);
        $this->app->singleton(CmsSitemapCacheService::class);
        $this->app->singleton(CmsAdminWarnings::class);
        $this->app->singleton(CmsSiteSeoSettingsService::class);
        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');
        $this->app->singleton(DuplicateSystemSettingsToCmsSettings::class);
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
            Route::aliasMiddleware('modules.cms.url_stale.serve', ServeUrlKeyedStaleMiddleware::class);
            Route::aliasMiddleware('modules.cms.layout_builder', LayoutBuilderMiddleware::class);

            $this->registerUrlStaleServeMiddleware();
        }

        CmsPublicSystemRouteRegistrar::registerAll();

        if ((bool) modularousConfig('cms_routing.resync_registry_after_parent_segments_change', true)) {
            ParentSegment::observe(ParentSegmentUrlRouteObserver::class);
        }

        $this->registerCmsPublishSchedule();

        // One-time seed: skip public front and artisan so a down DB cannot
        // add 10–20s of PDO wait to every frontend.b2press.test request.
        if ($this->app->runningInConsole() || Modularous::isFrontUrl()) {
            return;
        }

        try {
            $this->app->make(DuplicateSystemSettingsToCmsSettings::class)->duplicateIfNeeded();
        } catch (\Throwable) {
            // Seed is best-effort when singletons / DB are unavailable.
        }
    }

    /**
     * URL-keyed stale HTML must run before route matching so cached pages work on hosts
     * that are not bound to {@see CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain()}.
     */
    private function registerUrlStaleServeMiddleware(): void
    {
        $prepend = static function (HttpKernel $kernel): void {
            if (! Modularous::isFrontUrl()) {
                return;
            }

            if (! ModularousCache::isUrlStaleServeFirst()) {
                return;
            }

            $ref = new \ReflectionClass($kernel);
            $prop = $ref->getProperty('middleware');
            $prop->setAccessible(true);
            $stack = $prop->getValue($kernel);
            if (in_array(ServeUrlKeyedStaleMiddleware::class, $stack, true)) {
                return;
            }

            $kernel->prependMiddleware(ServeUrlKeyedStaleMiddleware::class);
        };

        $this->app->afterResolving(HttpKernel::class, $prepend);

        if ($this->app->resolved(HttpKernel::class)) {
            $prepend($this->app->make(HttpKernel::class));
        }

        $this->app->booted(static function (): void {
            if (! Modularous::isFrontUrl() || ModularousCache::isUrlStaleServeFirst()) {
                return;
            }

            CmsFrontRouteRegistrar::syncUrlStaleServeMiddlewareOnRegisteredPublicFrontRoutes();
        });
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
