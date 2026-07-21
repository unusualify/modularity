<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Events\Cache;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Support\BroadcastAvailability;

final class CacheWarmProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const STATUS_STARTED = 'started';

    public const STATUS_PROGRESS = 'progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $status,
        public ?int $initiatorUserId = null,
        public array $payload = [],
    ) {}

    public function broadcastWhen(): bool
    {
        return BroadcastAvailability::isEnabled()
            && $this->initiatorUserId !== null
            && $this->initiatorUserId > 0;
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->initiatorUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.cache.warm.' . $this->status;
    }

    /**
     * @return array<string, mixed>
     *
     * Payload merges machine fields with an optional structured toast:
     * ```
     * 'toast' => [
     *   'title' => string,        // short headline
     *   'description' => string,  // human status text
     *   'detail' => ?string,      // ModuleName:RouteName[:id], omit/null when unavailable
     *   'variant' => 'info'|'success'|'error'|'warning',
     * ]
     * ```
     * Frontend displays toast fields as-is; Jobs compose title/description/detail.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'status' => $this->status,
            'initiator_user_id' => $this->initiatorUserId,
        ], $this->payload);
    }
}
