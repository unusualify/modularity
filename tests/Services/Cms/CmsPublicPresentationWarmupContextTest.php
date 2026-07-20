<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\Cms\Support\CmsPublicPresentationWarmupContext;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\SiteSettings;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPresentationWarmupContextTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationWarmupContext::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        if (interface_exists(CmsLocalizationContract::class) && ! $this->app->bound(CmsLocalizationContract::class)) {
            $this->app->singleton(
                CmsLocalizationContract::class,
                fn () => new TranslatableCmsLocalizationAdapter(new CanonicalUrlResolver),
            );
        }

        Config::set('modularous.cms_routing.canonical_host', 'frontend.test');
        Config::set('app.url', 'http://frontend.test');
        app()->setLocale('en');
        URL::defaults(['locale' => 'en']);

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');
        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');
    }

    /** @test */
    public function it_sets_application_locale_and_url_defaults_during_run(): void
    {
        /** @var UrlGenerator $url */
        $url = app(UrlGenerator::class);

        $observed = CmsPublicPresentationWarmupContext::run('tr', function () use ($url): array {
            return [
                'appLocale' => app()->getLocale(),
                'urlLocale' => $url->getDefaultParameters()['locale'] ?? null,
            ];
        });

        $this->assertSame('tr', $observed['appLocale']);
        $this->assertSame('tr', $observed['urlLocale']);
        $this->assertSame('en', app()->getLocale());
        $this->assertSame('en', $url->getDefaultParameters()['locale'] ?? null);
    }

    /** @test */
    public function it_restores_locale_after_nested_exception(): void
    {
        /** @var UrlGenerator $url */
        $url = app(UrlGenerator::class);

        try {
            CmsPublicPresentationWarmupContext::run('nl', function (): void {
                throw new \RuntimeException('render failed');
            });
        } catch (\RuntimeException) {
        }

        $this->assertSame('en', app()->getLocale());
        $this->assertSame('en', $url->getDefaultParameters()['locale'] ?? null);
    }

    /** @test */
    public function it_applies_lang_locale_and_url_defaults_together(): void
    {
        $observed = CmsPublicPresentationWarmupContext::run('tr', function (): array {
            return [
                'appLocale' => app()->getLocale(),
                'langLocale' => Lang::getLocale(),
            ];
        });

        $this->assertSame('tr', $observed['appLocale']);
        $this->assertSame('tr', $observed['langLocale']);
    }

    /** @test */
    public function it_forces_site_settings_cms_layer_during_run_in_console(): void
    {
        $this->assertTrue($this->app->runningInConsole());
        $this->assertFalse(SiteSettings::usesCmsLayer());

        $usesCms = CmsPublicPresentationWarmupContext::run('tr', function (): bool {
            return SiteSettings::usesCmsLayer();
        });

        $this->assertTrue($usesCms);
        $this->assertFalse(SiteSettings::usesCmsLayer());
    }
}
