<?php

namespace Unusualify\Modularous\Entities\Traits;

trait Publishable
{
    public function initializePublishable()
    {
        $this->mergeFillable([
            'published',
            ...($this->hasPublishDates() ? ['publish_start_date', 'publish_end_date'] : []),
        ]);

        $this->mergeCasts([
            'published' => 'boolean',
            ...($this->hasPublishDates() ? ['publish_start_date' => 'datetime', 'publish_end_date' => 'datetime'] : []),
        ]);
    }

    public function hasPublishDates(): bool
    {
        return $this->usePublishDates ?? false;
    }
}
