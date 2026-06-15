<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsLocalizationOverrideProviderInterface;
use Modules\Cms\Localization\DelegatingCmsLocalizationAdapter;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Unusualify\Modularous\Tests\TestCase;

class CmsLocalizationAdapterTest extends TestCase
{
    public function test_translatable_adapter_reads_path_segment_locales_from_config(): void
    {
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', ['de', 'en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        $adapter = new TranslatableCmsLocalizationAdapter(new CanonicalUrlResolver);

        $this->assertEqualsCanonicalizing(['de', 'en'], $adapter->pathSegmentLocales());
        $this->assertSame('en', $adapter->defaultLocale());
        $this->assertSame('translatable', $adapter->driver());
    }

    public function test_delegating_merges_path_segment_overrides(): void
    {
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', ['de', 'en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        $inner = new TranslatableCmsLocalizationAdapter(new CanonicalUrlResolver);
        $overrides = new class implements CmsLocalizationOverrideProviderInterface
        {
            public function pathSegmentLocales(): ?array
            {
                return ['xx', 'yy'];
            }

            public function defaultLocale(): ?string
            {
                return null;
            }

            public function hideDefaultLocaleInUrl(): ?bool
            {
                return null;
            }

            public function supportedLocalesMeta(): ?array
            {
                return null;
            }
        };

        $delegating = new DelegatingCmsLocalizationAdapter($inner, $overrides);

        $this->assertSame(['xx', 'yy'], $delegating->pathSegmentLocales());
        $this->assertSame('en', $delegating->defaultLocale());
    }

    public function test_cms_localization_contract_is_registered_when_cms_enabled(): void
    {
        $this->app['config']->set('modularous.cms_features.enabled', true);
        $this->app['config']->set('modularous.cms_routing.localization_driver', 'translatable');
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', ['en']);
        $this->app['config']->set('modularous.cms_routing.resync_registry_after_parent_segments_change', false);

        $this->app->register(\Modules\Cms\Providers\CmsServiceProvider::class);

        $this->assertInstanceOf(CmsLocalizationContract::class, $this->app->make(CmsLocalizationContract::class));
        $this->assertSame('translatable', $this->app->make(CmsLocalizationContract::class)->driver());
    }
}
