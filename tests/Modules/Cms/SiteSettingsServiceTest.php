<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\Cms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Entities\SiteSetting;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\CmsSettings;
use Unusualify\Modularous\Facades\SiteSettings;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class SiteSettingsServiceTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');
        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');
    }

    public function test_backend_context_reads_system_settings_only(): void
    {
        SystemSettings::set('site.name', 'System Name');
        CmsSettings::set('site.name', 'Cms Name');

        $this->assertSame('System Name', SiteSettings::forBackend()->get('site.name'));
    }

    public function test_frontend_context_prefers_cms_then_falls_back_to_system(): void
    {
        SystemSettings::set('site.name', 'System Name');
        SystemSettings::set('site.email', 'system@example.com');

        $this->assertSame('System Name', SiteSettings::forFrontend()->get('site.name'));
        $this->assertSame('system@example.com', SiteSettings::forFrontend()->get('site.email'));

        CmsSettings::set('site.name', 'Cms Name');

        $this->assertSame('Cms Name', SiteSettings::forFrontend()->get('site.name'));
        $this->assertSame('system@example.com', SiteSettings::forFrontend()->get('site.email'));
    }

    public function test_frontend_falls_back_to_system_for_analytics_section(): void
    {
        SystemSettings::set('analytics.gtm_id', 'GTM-SYSTEM01');
        SystemSettings::set('analytics.enabled', true);

        $this->assertSame(
            ['site', 'social', 'contact', 'seo'],
            SiteSetting::settingsSections()
        );
        $this->assertFalse(CmsSettings::filled('analytics.gtm_id'));
        $this->assertSame('GTM-SYSTEM01', SiteSettings::forFrontend()->get('analytics.gtm_id'));
        $this->assertTrue((bool) SiteSettings::forFrontend()->get('analytics.enabled'));
    }

    public function test_duplicate_copies_general_into_empty_site_setting(): void
    {
        SystemSettings::set('site.name', 'Copied Name');
        SystemSettings::set('contact.sales_email', 'sales@example.com');
        SystemSettings::set('analytics.gtm_id', 'GTM-SHOULD-NOT-COPY');

        $site = SiteSetting::single();
        $this->assertTrue($this->app->make(DuplicateSystemSettingsToCmsSettings::class)->duplicateIfNeeded());

        $site->refresh();
        $this->assertSame('Copied Name', data_get($site->site, 'name'));
        $this->assertSame('sales@example.com', data_get($site->contact, 'sales_email'));
        $this->assertNull(data_get($site->getAttributes(), 'analytics'));
        $this->assertSame('GTM-SHOULD-NOT-COPY', SiteSettings::forFrontend()->get('analytics.gtm_id'));

        $this->assertFalse($this->app->make(DuplicateSystemSettingsToCmsSettings::class)->duplicateIfNeeded());
    }

    public function test_site_setting_is_singular_like_general(): void
    {
        $a = SiteSetting::single();
        $b = SiteSetting::single();

        $this->assertSame($a->id, $b->id);
        $this->assertSame(General::single()->getTable(), $a->getTable());
    }
}
