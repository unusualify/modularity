<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache\Concerns;

use Illuminate\Queue\Middleware\WithoutOverlapping;

trait ModularousCacheJob
{
    public function configureModularousCacheQueue(): static
    {
        $queueName = config('modularous.cache.queue.name')
            ?? config('modularous.cache.observer.queue_name', 'modularous-cache');
        $this->onQueue($queueName);

        $connection = config('modularous.cache.queue.connection')
            ?? config('modularous.cache.observer.queue_connection');
        if (is_string($connection) && $connection !== '') {
            $this->onConnection($connection);
        }

        return $this;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->modularousCacheOverlapKey()))
                ->dontRelease()
                ->expireAfter(600),
        ];
    }

    abstract protected function modularousCacheOverlapKey(): string;
}
