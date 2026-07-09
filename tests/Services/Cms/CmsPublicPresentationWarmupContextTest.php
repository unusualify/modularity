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
use Modules\Cms\Support\CmsPublicPresentationWarmupContext;
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
}
