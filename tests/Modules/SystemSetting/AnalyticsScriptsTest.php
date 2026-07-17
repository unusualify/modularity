<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Services\SystemSettingsService;
use Modules\SystemSetting\Support\AnalyticsScripts;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class AnalyticsScriptsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected AnalyticsScripts $analytics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');
        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');
        $this->app->singleton(AnalyticsScripts::class);

        config(['modularous.system_settings.analytics_enabled_default' => false]);

        $this->analytics = $this->app->make(AnalyticsScripts::class);
    }

    public function test_disabled_emits_empty_snippets(): void
    {
        SystemSettings::set('analytics.enabled', false);
        SystemSettings::set('analytics.gtm_id', 'GTM-MHL7RP8');
        SystemSettings::set('analytics.ga_measurement_id', 'G-ABCDEF123');

        $this->assertFalse($this->analytics->isEnabled());
        $this->assertSame('', $this->analytics->headHtml());
        $this->assertSame('', $this->analytics->bodyOpenHtml());
    }

    public function test_unset_enabled_falls_back_to_config_default(): void
    {
        $this->assertFalse($this->analytics->isEnabled());

        config(['modularous.system_settings.analytics_enabled_default' => true]);
        SystemSettings::set('analytics.gtm_id', 'GTM-MHL7RP8');

        $this->assertTrue($this->analytics->isEnabled());
        $this->assertStringContainsString('GTM-MHL7RP8', $this->analytics->headHtml());
    }

    public function test_enabled_with_gtm_emits_head_and_body_snippets(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', 'GTM-MHL7RP8');

        $head = $this->analytics->headHtml();
        $body = $this->analytics->bodyOpenHtml();

        $this->assertStringContainsString('Google Tag Manager', $head);
        $this->assertStringContainsString('GTM-MHL7RP8', $head);
        $this->assertStringContainsString("dataLayer','GTM-MHL7RP8'", $head);

        $this->assertStringContainsString('googletagmanager.com/ns.html?id=GTM-MHL7RP8', $body);
        $this->assertStringContainsString('<noscript>', $body);
        $this->assertStringNotContainsString('gtag/js', $head);
    }

    public function test_enabled_with_gtm_and_ga_emits_gtag(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', 'GTM-MHL7RP8');
        SystemSettings::set('analytics.ga_measurement_id', 'G-ABCDEF123');

        $head = $this->analytics->headHtml();

        $this->assertStringContainsString('GTM-MHL7RP8', $head);
        $this->assertStringContainsString('gtag/js?id=G-ABCDEF123', $head);
        $this->assertStringContainsString("gtag('config', 'G-ABCDEF123')", $head);
        $this->assertStringContainsString('GTM-MHL7RP8', $this->analytics->bodyOpenHtml());
    }

    public function test_enabled_without_gtm_emits_only_ga_when_set(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', '');
        SystemSettings::set('analytics.ga_measurement_id', 'G-ONLYGA999');

        $head = $this->analytics->headHtml();

        $this->assertStringNotContainsString('gtm.js', $head);
        $this->assertStringContainsString('gtag/js?id=G-ONLYGA999', $head);
        $this->assertSame('', $this->analytics->bodyOpenHtml());
    }

    public function test_invalid_and_empty_ids_are_ignored(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', 'not-a-gtm-id');
        SystemSettings::set('analytics.ga_measurement_id', 'UA-12345-1');

        $this->assertNull($this->analytics->gtmId());
        $this->assertNull($this->analytics->gaMeasurementId());
        $this->assertSame('', $this->analytics->headHtml());
        $this->assertSame('', $this->analytics->bodyOpenHtml());
    }

    public function test_whitespace_ids_are_trimmed_and_validated(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', '  gtm-mhl7rp8  ');

        $this->assertSame('GTM-MHL7RP8', $this->analytics->gtmId());
        $this->assertStringContainsString('GTM-MHL7RP8', $this->analytics->headHtml());
    }

    public function test_ids_persist_on_general_analytics_section(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', 'GTM-MHL7RP8');

        $general = General::single()->fresh();

        $this->assertTrue((bool) data_get($general->analytics, 'enabled'));
        $this->assertSame('GTM-MHL7RP8', data_get($general->analytics, 'gtm_id'));
    }

    public function test_resolves_analytics_from_general_via_site_settings_fallback(): void
    {
        SystemSettings::set('analytics.enabled', true);
        SystemSettings::set('analytics.gtm_id', 'GTM-FALLBACK1');

        $this->assertTrue($this->analytics->isEnabled());
        $this->assertSame('GTM-FALLBACK1', $this->analytics->gtmId());
        $this->assertStringContainsString('GTM-FALLBACK1', $this->analytics->headHtml());
    }
}
