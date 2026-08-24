<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\Cms;

use Mockery;
use Modules\Cms\Repositories\SiteSettingRepository;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings;
use Unusualify\Modularous\Tests\TestCase;

class DuplicateSystemSettingsToCmsSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        $marker = DuplicateSystemSettingsToCmsSettings::settledMarkerPath();
        if (is_file($marker)) {
            @unlink($marker);
        }

        parent::tearDown();
    }

    public function test_duplicate_if_needed_is_a_no_op_when_settled_marker_exists(): void
    {
        $path = DuplicateSystemSettingsToCmsSettings::settledMarkerPath();
        @mkdir(dirname($path), 0755, true);
        touch($path);

        $service = new DuplicateSystemSettingsToCmsSettings(
            Mockery::mock(SiteSettingRepository::class),
            Mockery::mock(CmsSettingsService::class),
        );

        $this->assertFalse($service->duplicateIfNeeded());
    }
}
