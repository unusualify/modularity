<?php

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\Page;
use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;
use Modules\Cms\Providers\CmsServiceProvider;

return [
    /**
     * Optional scheduled scan for CMS page publish window boundaries ({@code publish_start_date} / {@code publish_end_date}).
     * Visibility for visitors is already enforced at query time via scopes; this is for side effects (cache, notifications).
     *
     * Host apps should register the scheduler (see {@code docs/CONFIG.md} “CMS publish window schedule”).
     */
    'enabled' => env('MODULAROUS_CMS_SCHEDULE_ENABLED', true),

    /**
     * When true, {@see CmsServiceProvider} registers
     * {@see ScanCmsPublishWindowBoundariesJob} with Laravel's schedule (opt-in to avoid surprise cron work).
     */
    'register_with_laravel_schedule' => env('MODULAROUS_CMS_SCHEDULE_REGISTER', false),

    /** How often the scan runs when registered with the schedule. */
    'frequency' => env('MODULAROUS_CMS_SCHEDULE_FREQUENCY', 'everyFiveMinutes'),

    /**
     * Look back window (minutes): rows whose {@code publish_*} timestamp fell in (now - window, now] are considered to have
     * just crossed the boundary. Should be ≥ scheduler interval to avoid gaps.
     */
    'boundary_window_minutes' => max(1, (int) env('MODULAROUS_CMS_SCHEDULE_BOUNDARY_WINDOW', 6)),

    /**
     * Eloquent models scanned for {@code publish_start_date} / {@code publish_end_date} (columns optional per table).
     * Override in host config; default is CMS {@see Page} only.
     *
     * @var list<class-string<Model>>
     */
    'publish_window_models' => (static function (): array {
        $raw = env('MODULAROUS_CMS_PUBLISH_WINDOW_MODELS');
        if (! is_string($raw) || $raw === '') {
            return [Page::class];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    })(),

    /** Log one info line per boundary event when true. */
    'log_events' => env('MODULAROUS_CMS_SCHEDULE_LOG', false),

    /**
     * Optional cache tag names to flush when any boundary is detected (Redis taggable cache only; ignored otherwise).
     *
     * @var list<string>|null
     */
    'cache_flush_tags' => env('MODULAROUS_CMS_SCHEDULE_CACHE_TAGS') !== null && env('MODULAROUS_CMS_SCHEDULE_CACHE_TAGS') !== ''
        ? array_values(array_filter(array_map('trim', explode(',', (string) env('MODULAROUS_CMS_SCHEDULE_CACHE_TAGS')))))
        : null,
];
