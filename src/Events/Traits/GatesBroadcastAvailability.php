<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Events\Traits;

use Unusualify\Modularous\Support\BroadcastAvailability;

/**
 * Shared {@see broadcastWhen()} for ShouldBroadcast events that do not extend ModelEvent.
 */
trait GatesBroadcastAvailability
{
    public function broadcastWhen(): bool
    {
        return BroadcastAvailability::isEnabled();
    }
}
