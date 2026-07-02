<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache\Concerns;

trait DispatchesOnModularousCacheQueue
{
    public function onModularousCacheQueue(): static
    {
        $queueName = config('modularous.cache.observer.queue_name', 'modularous-cache');
        $connection = config('modularous.cache.observer.queue_connection');

        $this->onQueue($queueName);

        if (is_string($connection) && $connection !== '') {
            $this->onConnection($connection);
        }

        return $this;
    }
}
