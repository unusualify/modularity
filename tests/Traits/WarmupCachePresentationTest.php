<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Support\CmsFrontPath;
use Modules\Cms\Support\CmsPublicPresentationInnerData;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Cache\WarmupCache;

class WarmupCachePresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        if (interface_exists(CanonicalUrlResolverInterface::class) && ! $this->app->bound(CanonicalUrlResolverInterface::class)) {
            $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
        }

        if (interface_exists(CmsLocalizationContract::class) && ! $this->app->bound(CmsLocalizationContract::class)) {
            $this->app->singleton(
                CmsLocalizationContract::class,
                fn () => new TranslatableCmsLocalizationAdapter(app(CanonicalUrlResolverInterface::class)),
            );
        }

        Schema::dropIfExists('warmup_presentation_countries');
        Schema::create('warmup_presentation_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        $this->createUrlRoutesTable();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.modules.BusinessPackage.enabled', true);
        Config::set('modularous.cache.modules.BusinessPackage.routes.PackageCountry.enabled', true);
        Config::set('modularous.cache.modules.BusinessPackage.routes.PackageCountry.types.presentationItem', true);
        Config::set('modularous.cms_features.enabled', true);
        Config::set('modularous.cms_routing.public_pages_enabled', true);
        Config::set('modularous.cms_routing.front_route_prefix', 'cms');
        Config::set('modularous.cms_routing.default_locale', 'en');
        Config::set('modularous.cms_routing.hide_default_locale_segment', false);
        Config::set('modularous.cms_routing.canonical_host', 'frontend.test');
        Config::set('app.url', 'http://frontend.test');
        Config::set('translatable.locales', ['en', 'tr']);
        Config::set('modularous.cms_page_layouts.filesystem_segments_without_db_binding_enabled', false);
        Config::set('modularous.cms_page_layouts.public_presentation_informational_fallback_enabled', false);
        Config::set('modularous.cms_routing.public_front_views_by_model', [
            WarmupPresentationCountryStub::class => 'business_package::package_country.custom',
        ]);

        View::addNamespace('business_package', __DIR__ . '/../Support/views');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('warmup_presentation_countries');
        $t = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        Schema::dropIfExists($t);

        parent::tearDown();
    }

    /** @test */
    public function it_reloads_stale_relationship_models_before_presentation_warmup(): void
    {
        $stored = WarmupPresentationCountryStub::query()->create(['name' => 'Germany']);
        $stale = new WarmupPresentationCountryStub;
        $stale->setRawAttributes(['id' => $stored->getKey(), 'name' => 'stale-in-memory']);
        $stale->exists = true;

        $service = new WarmupPresentationCacheService;
        $fresh = $service->exposeResolvePresentationWarmupModel($stale);

        $this->assertSame('Germany', $fresh->name);
    }

    /** @test */
    public function it_stores_presentation_item_cache_after_warmup(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'France']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/france',
        ]);

        $service = new WarmupPresentationCacheService;

        $this->assertTrue($service->isEnabled('BusinessPackage', 'PackageCountry', 'presentationItem'));

        $warmed = $service->warmupPresentationItem('BusinessPackage', 'PackageCountry', $country);

        $this->assertTrue($warmed);

        $key = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            'BusinessPackage',
            'PackageCountry',
            $country,
            'business_package::package_country.custom',
            'en',
        );

        $this->assertTrue($service->has($key, 'BusinessPackage', 'PackageCountry'));
    }

    /** @test */
    public function it_warms_presentation_item_cache_for_each_public_locale(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Spain']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/spain',
            'tr' => '/ulkeler/ispanya',
        ]);

        $service = new WarmupPresentationCacheService;

        $this->assertTrue($service->warmupPresentationItem('BusinessPackage', 'PackageCountry', $country));

        foreach (['en', 'tr'] as $locale) {
            $key = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                'BusinessPackage',
                'PackageCountry',
                $country,
                'business_package::package_country.custom',
                $locale,
            );

            $this->assertTrue(
                $service->has($key, 'BusinessPackage', 'PackageCountry'),
                "Expected presentationItem cache for locale [{$locale}]",
            );
        }
    }

    /** @test */
    public function it_derives_browser_paths_from_registry_paths_without_request(): void
    {
        $canonical = app(CanonicalUrlResolverInterface::class);

        $enPath = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('en', '/countries/italy', $canonical);
        $trPath = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('tr', '/ulkeler/italya', $canonical);

        $this->assertSame('/cms/en/countries/italy', $enPath);
        $this->assertSame('/cms/tr/ulkeler/italya', $trPath);
    }

    /** @test */
    public function it_sets_application_locale_during_each_warmup_iteration_and_restores_after(): void
    {
        app()->setLocale('en');

        $service = new WarmupPresentationCacheService;
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Portugal']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/portugal',
            'tr' => '/ulkeler/portekiz',
        ]);

        $service->warmupPresentationItem('BusinessPackage', 'PackageCountry', $country);

        $this->assertSame(['en', 'tr'], $service->applicationLocalesDuringInnerDataBuild);
        $this->assertSame('en', app()->getLocale());
    }

    /** @test */
    public function it_caches_distinct_locale_specific_presentation_html_per_locale(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Greece']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/greece',
            'tr' => '/ulkeler/yunanistan',
        ]);

        $service = new WarmupPresentationCacheService;

        $this->assertTrue($service->warmupPresentationItem('BusinessPackage', 'PackageCountry', $country));

        $htmlByLocale = [];
        foreach (['en', 'tr'] as $locale) {
            $key = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                'BusinessPackage',
                'PackageCountry',
                $country,
                'business_package::package_country.custom',
                $locale,
            );

            $html = (string) $service->get($key, '', 'BusinessPackage', 'PackageCountry');
            $htmlByLocale[$locale] = $html;

            $this->assertStringContainsString(
                'data-locale="' . $locale . '"',
                $html,
                "Expected cached HTML for locale [{$locale}] to reflect application locale during warmup",
            );
        }

        $this->assertNotSame($htmlByLocale['en'], $htmlByLocale['tr']);
    }

    /** @test */
    public function it_prefers_url_route_rows_for_locale_path_discovery(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Belgium']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/belgium',
            'tr' => '/ulkeler/belcika',
        ]);

        $service = new WarmupPresentationCacheService;
        $paths = $service->exposeResolvePresentationWarmupLocalesAndPaths($country);

        $this->assertSame([
            'en' => '/countries/belgium',
            'tr' => '/ulkeler/belcika',
        ], $paths);
    }

    /** @test */
    public function it_builds_distinct_canonical_urls_per_locale_like_live_controller(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Italy']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/italy',
            'tr' => '/ulkeler/italya',
        ]);

        $service = new WarmupPresentationCacheService;
        $canonical = app(CanonicalUrlResolverInterface::class);

        $enItem = $service->exposeResolvePresentationWarmupModelForLocale($country, 'en');
        $trItem = $service->exposeResolvePresentationWarmupModelForLocale($country, 'tr');

        $this->assertNotNull($enItem);
        $this->assertNotNull($trItem);

        $enInner = CmsPublicPresentationInnerData::buildForCache('en', '/countries/italy', $enItem, $canonical);
        $trInner = CmsPublicPresentationInnerData::buildForCache('tr', '/ulkeler/italya', $trItem, $canonical);

        $this->assertSame('http://frontend.test/cms/en/countries/italy', $enInner['canonicalUrl']);
        $this->assertSame('http://frontend.test/cms/tr/ulkeler/italya', $trInner['canonicalUrl']);
        $this->assertNotSame($enInner['canonicalUrl'], $trInner['canonicalUrl']);
    }

    /** @test */
    public function warmup_cache_key_matches_live_controller_locale_for_same_item(): void
    {
        app()->setLocale('en');

        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Brazil']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/country-pr-packages/brazil',
        ]);

        $viewName = 'business_package::package_country.custom';
        $moduleName = 'BusinessPackage';
        $routeName = 'PackageCountry';

        $warmupKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $country,
            $viewName,
            'en',
        );

        $liveKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $country,
            $viewName,
            app()->getLocale(),
        );

        $this->assertSame($warmupKey, $liveKey);
    }

    /** @test */
    public function normalize_cache_locale_maps_regional_variants_to_base_language(): void
    {
        $this->assertSame('en', CmsPublicPresentationItemCache::normalizeCacheLocale('en_US'));
        $this->assertSame('en', CmsPublicPresentationItemCache::normalizeCacheLocale('EN-us'));
        $this->assertSame('en', CmsPublicPresentationItemCache::normalizeCacheLocale('en-GB'));
        $this->assertSame('tr', CmsPublicPresentationItemCache::normalizeCacheLocale('tr'));
        $this->assertSame('nl', CmsPublicPresentationItemCache::normalizeCacheLocale('nl'));
    }

    /** @test */
    public function warmup_and_live_cache_keys_match_when_application_locale_is_regional(): void
    {
        app()->setLocale('en_US');

        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Argentina']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/country-pr-packages/argentina',
        ]);

        $viewName = 'business_package::package_country.custom';

        $warmupKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            'BusinessPackage',
            'PackageCountry',
            $country,
            $viewName,
            'en',
        );

        $liveKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            'BusinessPackage',
            'PackageCountry',
            $country,
            $viewName,
            app()->getLocale(),
        );

        $this->assertSame($warmupKey, $liveKey);
    }

    /** @test */
    public function presentation_item_cache_survives_when_relation_tag_is_flushed_before_write(): void
    {
        $country = WarmupPresentationCountryStub::query()->create(['name' => 'Chile']);
        $this->seedUrlRoutesForCountry($country, [
            'en' => '/countries/chile',
        ]);

        $service = new WarmupPresentationCacheService;
        $moduleName = 'BusinessPackage';
        $routeName = 'PackageCountry';
        $viewName = 'business_package::package_country.custom';
        $key = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $country,
            $viewName,
            'en',
        );
        $relations = [WarmupPresentationCountryStub::class => $country->getKey()];
        $ttl = $service->getTtl('presentationItem', $moduleName, $routeName);

        $service->invalidateByRelatedModel(WarmupPresentationCountryStub::class, $country->getKey());

        $service->putWithRelations(
            $key,
            '<html>warm</html>',
            $ttl,
            $moduleName,
            $routeName,
            $relations,
            'presentationItem',
        );

        $cached = $service->getWithRelations(
            $key,
            null,
            $moduleName,
            $routeName,
            $relations,
            'presentationItem',
        );

        $this->assertSame('<html>warm</html>', $cached);
    }

    /**
     * @param  array<string, string>  $pathsByLocale
     */
    protected function seedUrlRoutesForCountry(WarmupPresentationCountryStub $country, array $pathsByLocale): void
    {
        foreach ($pathsByLocale as $locale => $path) {
            UrlRoute::query()->create([
                'locale' => $locale,
                'normalized_path' => $path,
                'urlable_type' => $country->getMorphClass(),
                'urlable_id' => $country->getKey(),
                'kind' => UrlRoute::KIND_PAGE_PUBLIC,
            ]);
        }
    }

    protected function createUrlRoutesTable(): void
    {
        $t = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'normalized_path']);
        });
    }
}

class WarmupPresentationCacheService extends ModularousCacheService
{
    use WarmupCache {
        resolvePresentationWarmupModel as public exposeResolvePresentationWarmupModel;
        buildPresentationWarmupInnerData as public exposeBuildPresentationWarmupInnerData;
        resolvePresentationWarmupLocalesAndPaths as public exposeResolvePresentationWarmupLocalesAndPaths;
        resolvePresentationWarmupModelForLocale as public exposeResolvePresentationWarmupModelForLocale;
    }

    /** @var list<string> */
    public array $applicationLocalesDuringInnerDataBuild = [];

    /** @var array<string, string>|null */
    public ?array $stubPathsByLocale = null;

    protected function buildPresentationWarmupInnerData(string $locale, string $registryPath, Model $item): array
    {
        $this->applicationLocalesDuringInnerDataBuild[] = app()->getLocale();

        return parent::buildPresentationWarmupInnerData($locale, $registryPath, $item);
    }

    protected function resolvePresentationWarmupLocalesAndPaths(Model $item): array
    {
        if ($this->stubPathsByLocale !== null) {
            return $this->stubPathsByLocale;
        }

        return parent::resolvePresentationWarmupLocalesAndPaths($item);
    }
}

class WarmupPresentationCountryStub extends Model
{
    protected $table = 'warmup_presentation_countries';

    public $timestamps = false;

    protected $fillable = ['name'];
}
