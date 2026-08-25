<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\Cms;

use Mockery;
use Modules\Cms\Repositories\SiteSettingRepository;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings;
use ReflectionMethod;
use Unusualify\Modularous\Tests\TestCase;

class DuplicateSystemSettingsToCmsSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach ([
            DuplicateSystemSettingsToCmsSettings::settledMarkerPath(),
            DuplicateSystemSettingsToCmsSettings::legacySettledMarkerPath(),
        ] as $marker) {
            if (is_file($marker)) {
                @unlink($marker);
            }
        }

        parent::tearDown();
    }

    public function test_markers_are_disabled_during_unit_tests(): void
    {
        $service = new DuplicateSystemSettingsToCmsSettings(
            Mockery::mock(SiteSettingRepository::class),
            Mockery::mock(CmsSettingsService::class),
        );

        $markersEnabled = new ReflectionMethod($service, 'markersEnabled');
        $markersEnabled->setAccessible(true);

        $this->assertFalse($markersEnabled->invoke($service));
    }

    public function test_write_settled_marker_is_a_no_op_during_unit_tests(): void
    {
        $path = DuplicateSystemSettingsToCmsSettings::settledMarkerPath();
        if (is_file($path)) {
            @unlink($path);
        }

        $service = new DuplicateSystemSettingsToCmsSettings(
            Mockery::mock(SiteSettingRepository::class),
            Mockery::mock(CmsSettingsService::class),
        );

        $write = new ReflectionMethod($service, 'writeSettledMarker');
        $write->setAccessible(true);
        $write->invoke($service);

        $this->assertFileDoesNotExist($path);
    }
}
