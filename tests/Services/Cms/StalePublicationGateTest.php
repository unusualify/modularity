<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Carbon\Carbon;
use Modules\Cms\Support\StalePublicationGate;
use Modules\Cms\Support\StalePublicationMeta;
use Unusualify\Modularous\Tests\TestCase;

class StalePublicationGateTest extends TestCase
{
    /** @test */
    public function it_rejects_unpublished_meta(): void
    {
        $meta = [
            'published' => false,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
        ];

        $this->assertFalse(StalePublicationGate::isVisible($meta, Carbon::parse('2026-07-06 12:00:00')));
    }

    /** @test */
    public function singular_profile_uses_day_bounds(): void
    {
        $now = Carbon::parse('2026-07-06 12:00:00');

        $visible = [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_SINGULAR,
            'publish_start_date' => '2026-07-01',
            'publish_end_date' => '2026-07-10',
        ];
        $this->assertTrue(StalePublicationGate::isVisible($visible, $now));

        $beforeStart = [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_SINGULAR,
            'publish_start_date' => '2026-07-07',
        ];
        $this->assertFalse(StalePublicationGate::isVisible($beforeStart, $now));

        $afterEnd = [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_SINGULAR,
            'publish_end_date' => '2026-07-05',
        ];
        $this->assertFalse(StalePublicationGate::isVisible($afterEnd, $now));
    }

    /** @test */
    public function standard_profile_uses_datetime_comparison(): void
    {
        $now = Carbon::parse('2026-07-06 15:00:00');

        $visible = [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            'publish_start_date' => '2026-07-06',
            'publish_end_date' => '2026-07-06',
        ];
        $this->assertTrue(StalePublicationGate::isVisible($visible, $now));

        $future = [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            'publish_start_date' => '2026-07-07 00:00:00',
        ];
        $this->assertFalse(StalePublicationGate::isVisible($future, $now));
    }
}
