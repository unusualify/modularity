<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Nwidart\Modules\Contracts\ActivatorInterface;
use TestModules\TestModule\Entities\Item as TestModuleItem;
use Unusualify\Modularous\Activators\ModularousActivator;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Exceptions\ModularousSystemPathException;
use Unusualify\Modularous\Modularous;

class ModularousTest extends TestModulesCase
{
    protected $modularous;

    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = app();

        $path = $this->app['config']->get('modules.paths.modules');

        $this->modularous = new Modularous($this->app, $path);
    }

    public function test_scan_paths_are_properly_formatted()
    {
        $paths = $this->modularous->getScanPaths();

        foreach ($paths as $path) {
            $this->assertTrue(str_ends_with($path, '/*'));
        }
    }

    public function test_format_cached_on_cache_enabled()
    {
        $app = app();
        $app['config']->set('modules.cache.enabled', true);
        $app['config']->set('modules.cache.driver', 'array');
        $app['config']->set('modules.cache.key', 'modularous-test-format-cache');
        $app['config']->set('modules.cache.lifetime', 600);

        $path = $app['config']->get('modules.paths.modules');

        $modularous = new Modularous($app, $path);

        $allModules = $modularous->all();

        $this->assertArrayHasKey('systemmodule', $allModules);
        $this->assertArrayHasKey('testmodule', $allModules);
    }

    public function test_get_cached_reads_from_cache_store_after_first_population(): void
    {
        $this->app['config']->set('modules.cache.enabled', true);
        $this->app['config']->set('modules.cache.driver', 'array');
        $this->app['config']->set('modules.cache.key', 'modularous-test-get-cached');
        $this->app['config']->set('modules.cache.lifetime', 600);

        $modularous = new Modularous($this->app, $this->app['config']->get('modules.paths.modules'));
        $method = new \ReflectionMethod($modularous, 'getCached');
        $method->setAccessible(true);

        $first = $method->invoke($modularous);
        $this->assertIsArray($first);
        $this->assertTrue($this->app['cache']->store('array')->has('modularous-test-get-cached'));

        $second = $method->invoke($modularous);
        $this->assertSame($first, $second);
    }

    public function test_format_cached_resets_when_cached_path_is_outside_application(): void
    {
        $modularous = new Modularous($this->app, $this->app['config']->get('modules.paths.modules'));
        $method = new \ReflectionMethod($modularous, 'formatCached');
        $method->setAccessible(true);

        $modules = $method->invoke($modularous, [
            'ghost' => ['path' => '/tmp/not-under-base-path/module'],
        ]);

        $this->assertArrayHasKey('testmodule', $modules);
        $this->assertArrayHasKey('systemmodule', $modules);
    }

    public function test_format_cached_reuses_valid_cached_module_paths(): void
    {
        $modularous = new Modularous($this->app, $this->app['config']->get('modules.paths.modules'));
        $method = new \ReflectionMethod($modularous, 'formatCached');
        $method->setAccessible(true);

        $testModulePath = $modularous->find('TestModule')->getPath();
        $modules = $method->invoke($modularous, [
            'testmodule' => ['path' => $testModulePath],
        ]);

        $this->assertArrayHasKey('testmodule', $modules);
        $this->assertSame($testModulePath, $modules['testmodule']->getPath());
    }

    public function test_all_uses_cached_modules_when_cache_enabled_and_not_in_console(): void
    {
        $this->app['config']->set('modules.cache.enabled', true);
        $this->app['config']->set('modules.cache.driver', 'array');
        $this->app['config']->set('modules.cache.key', 'modularous-test-all-cache');
        $this->app['config']->set('modules.cache.lifetime', 600);

        $app = Mockery::mock($this->app)->makePartial();
        $app->shouldReceive('runningInConsole')->andReturn(false);

        $modularous = new Modularous($app, $this->app['config']->get('modules.paths.modules'));
        $modules = $modularous->all();

        $this->assertArrayHasKey('testmodule', $modules);
        $this->assertArrayHasKey('systemmodule', $modules);
    }

    public function test_has_module()
    {
        $this->assertTrue($this->modularous->has('systemmodule'));
        $this->assertFalse($this->modularous->has('NonExistentModule'));
    }

    // public function test_get_by_status()
    // {
    //     $this->app['files']->put($this->statusesFilePath, json_encode([
    //         'TestModule' => false,
    //         'SystemModule' => true,
    //     ]));

    //     $activeModules = $this->modularous->getByStatus(true);

    //     $this->assertArrayHasKey('systemmodule', $activeModules);
    //     $this->assertArrayNotHasKey('testmodule', $activeModules);
    // }

    public function test_get_by_status_returns_only_matching_modules(): void
    {
        $this->writeModuleActivationStatuses([
            'TestModule' => false,
            'SystemModule' => true,
        ]);

        $activeModules = $this->modularous->getByStatus(true);
        $inactiveModules = $this->modularous->getByStatus(false);

        $this->assertArrayHasKey('systemmodule', $activeModules);
        $this->assertArrayNotHasKey('testmodule', $activeModules);
        $this->assertArrayHasKey('testmodule', $inactiveModules);
    }

    public function test_get_auth_guard_name(): void
    {
        $this->assertSame('modularous', Modularous::getAuthGuardName());
    }

    public function test_development_production()
    {
        $this->assertFalse($this->modularous->isDevelopment());
        $this->assertTrue($this->modularous->isProduction());
    }

    public function test_feature_methods()
    {
        $this->assertTrue($this->modularous->shouldUseInertia());

        $this->app['config']->set('modularous.use_collation_for_search', true);
        $this->app['config']->set('modularous.include_transaction_fee', true);
        $this->app['config']->set('modularous.use_country_based_vat_rates', true);

        $this->assertTrue($this->modularous->shouldUseCollationForSearch());
        $this->assertTrue($this->modularous->shouldIncludeTransactionFee());
        $this->assertTrue($this->modularous->shouldUseCountryBasedVatRates());

        $this->assertEquals(config('app.name'), $this->modularous->pageTitle());
        Modularous::createPageTitle(fn () => 'Test Page Title');
        $this->assertEquals('Test Page Title', $this->modularous->pageTitle());
    }

    public function test_get_auth_provider_name()
    {
        $providerName = Modularous::getAuthProviderName();
        $this->assertEquals('modularous_users', $providerName);
        $this->assertIsString($providerName);
    }

    public function test_clear_cache()
    {
        $this->app['config']->set('modules.cache.enabled', true);
        $this->app['config']->set('modules.cache.key', 'test-modules-cache');

        // Populate cache first
        $this->modularous->all();

        // Clear cache
        $this->modularous->clearCache();

        // Verify cache is cleared
        $this->assertFalse($this->app['cache']->has('test-modules-cache'));
    }

    public function test_clear_cache_flushes_activator_cache_when_supported(): void
    {
        $activator = new class($this->app) extends ModularousActivator
        {
            public int $flushCount = 0;

            public function flushCache(): void
            {
                $this->flushCount++;
            }
        };

        $this->app->instance(ActivatorInterface::class, $activator);

        $modularous = new Modularous($this->app, $this->app['config']->get('modules.paths.modules'));
        $modularous->clearCache();

        $this->assertSame(1, $activator->flushCount);
    }

    public function test_disable_cache()
    {
        $this->modularous->disableCache();
        $this->assertFalse(config('modules.cache.enabled'));
    }

    public function test_has_module_returns_true_for_existing_module()
    {
        $this->assertTrue($this->modularous->hasModule('SystemModule'));
    }

    public function test_has_module_returns_false_for_non_existing_module()
    {
        $this->assertFalse($this->modularous->hasModule('NonExistentModule'));
    }

    public function test_get_modules_path()
    {
        $modulesPath = $this->modularous->getModulesPath();
        $this->assertStringContainsString('modules', $modulesPath);

        $subPath = $this->modularous->getModulesPath('TestModule');
        $this->assertStringContainsString('modules', $subPath);
        $this->assertStringContainsString('TestModule', $subPath);
    }

    public function test_set_and_revert_system_modules_path()
    {
        $this->expectException(ModularousSystemPathException::class);
        $this->modularous->setSystemModulesPath();
    }

    public function test_revert_system_modules_path_restores_retained_modules_path(): void
    {
        $retainedPath = config('modules.paths.modules');

        config(['modules.paths.modules' => '/tmp/changed-modules-path']);

        $this->modularous->revertSystemModulesPath();

        $this->assertSame($retainedPath, config('modules.paths.modules'));
    }

    public function test_get_app_url(): void
    {
        $this->app['config']->set('modularous.app_url', 'http://example.test');

        $this->assertSame('http://example.test', $this->modularous->getAppUrl());
    }

    public function test_get_app_host()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost:8080');
        $host = $this->modularous->getAppHost();
        $this->assertEquals('localhost', $host);
    }

    public function test_get_admin_app_host()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost:8080');
        $this->app['config']->set('modularous.admin_app_url', 'http://admin.localhost:8080');

        $adminHost = $this->modularous->getAdminAppHost();
        $this->assertEquals('admin.localhost', $adminHost);
    }

    public function test_is_panel_url_with_admin_app_url()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost');
        $this->app['config']->set('modularous.admin_app_url', 'http://admin.localhost');

        $this->assertTrue($this->modularous->isPanelUrl('http://admin.localhost/dashboard'));
        $this->assertFalse($this->modularous->isPanelUrl('http://localhost/home'));
    }

    public function test_is_panel_url_with_admin_path()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost');
        $this->app['config']->set('modularous.admin_app_url', '');
        $this->app['config']->set('modularous.admin_app_path', 'admin');

        // Create a mock request to provide default values for request()->getHost() and request()->segment(1)
        $request = Request::create('http://localhost/admin', 'GET');
        $this->app->instance('request', $request);

        $this->assertTrue($this->modularous->isPanelUrl('http://localhost/admin/dashboard'));
        $this->assertFalse($this->modularous->isPanelUrl('http://localhost/home'));
    }

    public function test_is_panel_url_returns_false_when_request_has_no_segment_and_url_is_provided(): void
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost');
        $this->app['config']->set('modularous.admin_app_url', '');
        $this->app['config']->set('modularous.admin_app_path', 'admin');

        $request = Request::create('http://localhost', 'GET');
        $this->app->instance('request', $request);

        $this->assertFalse($this->modularous->isPanelUrl('http://localhost/admin/dashboard'));
    }

    public function test_get_admin_url_prefix_returns_false_when_using_admin_subdomain(): void
    {
        $this->app['config']->set('modularous.admin_app_url', 'http://admin.localhost');

        $this->assertFalse($this->modularous->getAdminUrlPrefix());
    }

    public function test_is_modularous_route()
    {
        $this->app['config']->set('modularous.admin_route_name_prefix', 'admin');

        $this->assertTrue($this->modularous->isModularousRoute('admin.dashboard.index'));
        $this->assertTrue($this->modularous->isModularousRoute('admin.users.create'));
        $this->assertFalse($this->modularous->isModularousRoute('public.home'));
    }

    public function test_get_system_url_prefix()
    {
        $this->app['config']->set('modularous.system_prefix', 'system-settings');
        $prefix = $this->modularous->getSystemUrlPrefix();
        $this->assertEquals('system-settings', $prefix);
    }

    public function test_get_system_route_name_prefix()
    {
        $this->app['config']->set('modularous.system_prefix', 'system-settings');
        $prefix = $this->modularous->getSystemRouteNamePrefix();
        $this->assertEquals('system_settings', $prefix);
    }

    public function test_get_translations()
    {
        // Use a real translator binding (instance, not facade mock) so the
        // assertion is deterministic without fragile facade expectations that
        // break when the full suite runs.
        $translator = Mockery::mock(Translator::class);
        $translator->shouldReceive('getTranslations')->andReturn(['en' => ['greeting' => 'Hello']]);
        $this->app->instance('translator', $translator);

        Cache::store('file')->forget('modularous-languages');

        $translations = $this->modularous->getTranslations();

        $this->assertSame(['en' => ['greeting' => 'Hello']], $translations);
    }

    public function test_clear_translations()
    {
        Cache::put('modularous-languages', ['cached'], 600);

        $this->modularous->clearTranslations();

        $this->assertFalse(Cache::has('modularous-languages'));
    }

    public function test_get_grouped_modules()
    {
        // Create a test module with group
        $testModule = $this->modularous->find('SystemModule');

        $groupedModules = $this->modularous->getGroupedModules('system');
        $this->assertIsArray($groupedModules);
    }

    public function test_get_system_modules()
    {
        $systemModules = $this->modularous->getSystemModules();
        $this->assertIsArray($systemModules);
    }

    public function test_get_modules()
    {
        $modules = $this->modularous->getModules();
        $this->assertIsArray($modules);
    }

    public function test_delete_module()
    {
        // Test with a non-existent module to verify method executes
        $result = $this->modularous->deleteModule('NonExistentTestModule');

        // Should return false for non-existent module
        $this->assertFalse($result);

        // Verify method doesn't throw exceptions
        $this->assertIsBool($result);
    }

    public function test_delete_module_returns_false_for_non_existent()
    {
        $result = $this->modularous->deleteModule('NonExistentModule');
        $this->assertFalse($result);
    }

    public function test_get_classes()
    {
        $testPath = $this->modularous->find('SystemModule')->getPath() . '/Entities';

        if (file_exists($testPath)) {
            $classes = $this->modularous->getClasses($testPath);
            $this->assertIsArray($classes);
        } else {
            $this->assertTrue(true, 'Entities directory not found');
        }
    }

    public function test_get_vendor_dir()
    {
        $vendorDir = $this->modularous->getVendorDir();
        $this->assertIsString($vendorDir);

        $subDir = $this->modularous->getVendorDir('modules');
        $this->assertStringContainsString('modules', $subDir);
    }

    public function test_get_theme_path()
    {
        $this->app['config']->set('modularous.app_theme', 'default');
        $themePath = $this->modularous->getThemePath();
        $this->assertIsString($themePath);

        $subPath = $this->modularous->getThemePath('variables');
        $this->assertStringContainsString('variables', $subPath);
    }

    public function test_get_vendor_namespace()
    {
        // Default config has 'Unusualify\Modularous' with trailing backslash
        $namespace = $this->modularous->getVendorNamespace();
        // Should include trailing backslash from config default
        $this->assertStringEndsWith('\\', $namespace);
        $this->assertStringContainsString('Modularous', $namespace);

        $appendedNamespace = $this->modularous->getVendorNamespace('Services');
        $this->assertStringContainsString('Modularous', $appendedNamespace);
        $this->assertStringContainsString('Services', $appendedNamespace);
    }

    public function test_create_disable_language_based_prices()
    {
        Modularous::createDisableLanguageBasedPrices(fn () => true);

        $this->app['config']->set('modularous.use_language_based_prices', true);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }

    public function test_should_use_language_based_prices_without_callback()
    {
        // Reset callback
        Modularous::createDisableLanguageBasedPrices(null);

        $this->app['config']->set('modularous.use_language_based_prices', true);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertTrue($shouldUse);

        $this->app['config']->set('modularous.use_language_based_prices', false);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }

    public function test_get_currency_for_language_based_prices_returns_false_when_disabled(): void
    {
        Modularous::createDisableLanguageBasedPrices(null);
        $this->app['config']->set('modularous.use_language_based_prices', false);

        $this->assertFalse($this->modularous->getCurrencyForLanguageBasedPrices());
    }

    public function test_get_currency_for_language_based_prices_returns_false_when_provider_unavailable(): void
    {
        $this->app['config']->set('modularous.use_language_based_prices', true);

        $provider = Mockery::mock(CurrencyProviderInterface::class);
        $provider->shouldReceive('isAvailable')->once()->andReturn(false);
        $this->app->instance(CurrencyProviderInterface::class, $provider);

        $this->assertFalse($this->modularous->getCurrencyForLanguageBasedPrices());
    }

    public function test_get_currency_for_language_based_prices_returns_currency_for_locale(): void
    {
        $this->app['config']->set('modularous.use_language_based_prices', true);
        $this->app['config']->set('modularous.language_currencies', ['en' => 'USD']);
        $this->app->setLocale('en');

        $currency = (object) ['id' => 1, 'iso_4217' => 'USD'];
        $provider = Mockery::mock(CurrencyProviderInterface::class);
        $provider->shouldReceive('isAvailable')->once()->andReturn(true);
        $provider->shouldReceive('findByIso4217')->once()->with('USD')->andReturn($currency);
        $this->app->instance(CurrencyProviderInterface::class, $provider);

        $this->assertSame($currency, $this->modularous->getCurrencyForLanguageBasedPrices());
    }

    public function test_get_currency_for_language_based_prices_returns_false_without_locale_mapping(): void
    {
        $this->app['config']->set('modularous.use_language_based_prices', true);
        $this->app['config']->set('modularous.language_currencies', []);
        $this->app->setLocale('en');

        $provider = Mockery::mock(CurrencyProviderInterface::class);
        $provider->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->app->instance(CurrencyProviderInterface::class, $provider);

        $this->assertFalse($this->modularous->getCurrencyForLanguageBasedPrices());
    }

    public function test_get_models_finds_enabled_module_entities(): void
    {
        $models = $this->modularous->getModels('Item');

        $this->assertContains(TestModuleItem::class, $models);
    }

    public function test_get_module_route_model_select_items_and_resolve_target(): void
    {
        $items = $this->modularous->getModuleRouteModelSelectItems();

        $this->assertNotEmpty($items);

        $testModuleItem = collect($items)->first(
            fn (array $item): bool => $item['value'] === TestModuleItem::class
        );

        $this->assertNotNull($testModuleItem);
        $this->assertSame('TestModule - Item', $testModuleItem['title']);

        $this->assertSame(
            'TestModule::Item',
            $this->modularous->resolveTargetModuleRouteForModelClass(TestModuleItem::class)
        );
        $this->assertNull($this->modularous->resolveTargetModuleRouteForModelClass('Unknown\\Model'));
    }

    public function test_get_module_route_model_select_items_supports_trait_filters(): void
    {
        $parentSegmentOnly = $this->modularous->getModuleRouteModelSelectItems(true, false);
        $pageLayoutOnly = $this->modularous->getModuleRouteModelSelectItems(false, true);
        $both = $this->modularous->getModuleRouteModelSelectItems(true, true);

        $this->assertIsArray($parentSegmentOnly);
        $this->assertIsArray($pageLayoutOnly);
        $this->assertIsArray($both);
    }

    public function test_is_front_url_with_subdomain_admin(): void
    {
        $this->setUrlHosts('http://frontend.b2press.test', 'http://cms.b2press.test', '');

        $this->assertTrue($this->modularous->isFrontUrl('http://frontend.b2press.test/en/pages/about'));
        $this->assertFalse($this->modularous->isFrontUrl('http://cms.b2press.test/system/dashboard'));
        $this->assertTrue($this->modularous->isPanelUrl('http://cms.b2press.test/system/dashboard'));
    }

    public function test_is_front_url_with_path_admin(): void
    {
        $this->setUrlHosts('http://localhost', '', 'admin');

        $this->assertTrue($this->modularous->isFrontUrl('http://localhost/en/pages/about'));
        $this->assertFalse($this->modularous->isFrontUrl('http://localhost/admin/dashboard'));
    }

    public function test_is_non_http_console_context_is_false_during_phpunit(): void
    {
        $this->assertFalse($this->modularous->isNonHttpConsoleContext());
    }

    private function setUrlHosts(string $appUrl, string $adminAppUrl, string $adminAppPath): void
    {
        foreach (['modularous', 'modules'] as $namespace) {
            $this->app['config']->set("{$namespace}.app_url", $appUrl);
            $this->app['config']->set("{$namespace}.admin_app_url", $adminAppUrl);
            $this->app['config']->set("{$namespace}.admin_app_path", $adminAppPath);
        }
    }
}
