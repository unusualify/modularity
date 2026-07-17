<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Services\SystemSettingsService;
use Modules\SystemSetting\Support\MigrateRobotsTxtFromSiteSetting;
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

    public function test_resolved_robots_txt_body_reads_from_system_settings(): void
    {
        SystemSettings::set('seo.robots_txt', "User-agent: *\nAllow: /cms");

        $service = $this->app->make(CmsSiteSeoSettingsService::class);

        $this->assertSame("User-agent: *\nAllow: /cms\n", $service->resolvedRobotsTxtBody());
    }

    public function test_migrate_robots_txt_from_legacy_kv_table(): void
    {
        $legacyTable = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');

        if (! Schema::hasTable($legacyTable)) {
            Schema::create($legacyTable, function (Blueprint $table) {
                $table->id();
                $table->string('group_key');
                $table->string('key');
                $table->string('locale', 12);
                $table->text('value')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        \Illuminate\Support\Facades\DB::table($legacyTable)->insert([
            'group_key' => 'seo',
            'key' => 'global_robots_txt',
            'locale' => '*',
            'value' => "User-agent: *\nDisallow: /old",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migrated = $this->app->make(MigrateRobotsTxtFromSiteSetting::class)->migrateIfNeeded();

        $this->assertTrue($migrated);
        $this->assertSame("User-agent: *\nDisallow: /old", SystemSettings::get('seo.robots_txt'));

        $general = General::single()->refresh();
        $this->assertSame("User-agent: *\nDisallow: /old", $general->seo['robots_txt'] ?? null);
    }
}
