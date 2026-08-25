<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $this->app->forgetInstance(SystemSettingsService::class);
        $this->app->forgetInstance('system.settings');

        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');

        if (function_exists('forget_database_exists_cache')) {
            forget_database_exists_cache();
        }

        $this->forgetRobotsTxtMigrationMarkers();
    }

    protected function tearDown(): void
    {
        $this->forgetRobotsTxtMigrationMarkers();

        parent::tearDown();
    }

    protected function forgetRobotsTxtMigrationMarkers(): void
    {
        foreach ([
            MigrateRobotsTxtFromSiteSetting::settledMarkerPath(),
            MigrateRobotsTxtFromSiteSetting::legacySettledMarkerPath(),
        ] as $marker) {
            if (is_file($marker)) {
                @unlink($marker);
            }
        }
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

        DB::table($legacyTable)->insert([
            'group_key' => 'seo',
            'key' => 'global_robots_txt',
            'locale' => '*',
            'value' => "User-agent: *\nDisallow: /old",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ensure a clean General singleton so "already migrated" short-circuit cannot fire.
        General::query()->delete();
        $this->app->make(SystemSettingsService::class)->forgetCache();

        $this->assertNotNull(
            DB::table($legacyTable)
                ->where('group_key', 'seo')
                ->where('key', 'global_robots_txt')
                ->where('locale', '*')
                ->first()
        );

        $migrated = $this->app->make(MigrateRobotsTxtFromSiteSetting::class)->migrateIfNeeded();

        $this->assertTrue($migrated, 'migrateIfNeeded() returned false; legacy row was present and General had no robots_txt');
        $this->assertSame("User-agent: *\nDisallow: /old", SystemSettings::get('seo.robots_txt'));

        $general = General::single()->refresh();
        $this->assertSame("User-agent: *\nDisallow: /old", $general->seo['robots_txt'] ?? null);
    }
}
