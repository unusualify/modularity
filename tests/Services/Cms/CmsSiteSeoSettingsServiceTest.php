<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\Cms\Support\CmsPublicSeo;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class CmsSiteSeoSettingsServiceTest extends ModelTestCase
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

    public function test_resolved_prefers_persisted_system_settings_value(): void
    {
        config([
            'modularous.cms_seo.robots.use_system_settings' => true,
            'modularous.cms_seo.robots.global_robots_txt' => 'User-agent: *\nAllow: /',
        ]);

        SystemSettings::set('seo.robots_txt', "User-agent: *\nDisallow: /staging");

        $service = $this->app->make(CmsSiteSeoSettingsService::class);
        $body = $service->resolvedRobotsTxtBody();

        $this->assertStringContainsString('Disallow: /staging', $body);
        $this->assertStringEndsWith("\n", $body);
    }

    public function test_resolved_body_static_accepts_injected_service(): void
    {
        config([
            'modularous.cms_seo.robots.use_system_settings' => true,
            'modularous.cms_seo.robots.global_robots_txt' => 'User-agent: *\nDisallow: /cfg',
        ]);

        $service = $this->app->make(CmsSiteSeoSettingsService::class);
        $body = RobotsTxtController::resolvedBody($service);

        $this->assertStringContainsString('/cfg', $body);
    }

    public function test_staging_force_noindex_overrides_persisted_robots_txt(): void
    {
        config([
            'modularous.cms_seo.staging.force_noindex' => true,
            'modularous.cms_seo.robots.use_system_settings' => true,
        ]);

        SystemSettings::set('seo.robots_txt', "User-agent: *\nAllow: /");

        $service = $this->app->make(CmsSiteSeoSettingsService::class);
        $body = $service->resolvedRobotsTxtBody();

        $this->assertSame(CmsPublicSeo::ROBOTS_TXT_STAGING_DISALLOW_ALL . "\n", $body);
    }
}
