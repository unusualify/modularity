<?php

namespace Modules\Cms\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;

/**
 * Fired when a scheduled scan detects that a row's {@code publish_start_date} or {@code publish_end_date}
 * has just been crossed (within the configured look-back window).
 *
 * @see ScanCmsPublishWindowBoundariesJob
 */
final class CmsPublishWindowBoundaryReached
{
    use Dispatchable;

    /**
     * @param class-string<Model> $modelClass
     * @param 'publish_start'|'publish_end' $boundary
     */
    public function __construct(
        public string $modelClass,
        public int|string $modelId,
        public string $boundary,
    ) {}
}
