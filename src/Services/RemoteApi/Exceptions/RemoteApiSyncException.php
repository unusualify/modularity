<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Exceptions;

use RuntimeException;

class RemoteApiSyncException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $retryAfterSeconds = null,
        public readonly bool $isRateLimit = false,
    ) {
        parent::__construct($message);
    }

    public static function recordNotFound(int|string $remoteId): self
    {
        return new self("Remote API record [{$remoteId}] was not found.");
    }

    public static function missingRemoteId(): self
    {
        return new self('Remote API sync requires a remote id or a local record with remote_id.');
    }

    public static function incompletePaginatedList(int $expectedTotal, int $fetchedCount): self
    {
        return new self(sprintf(
            'Remote API pagination returned %d of %d expected records.',
            $fetchedCount,
            $expectedTotal,
        ));
    }

    public static function rateLimitExceeded(
        string $url,
        int $retryAfterSeconds,
        string $window = 'minute',
        ?int $limit = null,
    ): self {
        $limitMessage = $limit !== null
            ? sprintf(' (%d requests per %s)', $limit, $window)
            : '';

        return new self(
            sprintf(
                'Remote API rate limit exceeded for %s%s. Retry after %d seconds.',
                $url,
                $limitMessage,
                max(1, $retryAfterSeconds),
            ),
            max(1, $retryAfterSeconds),
            true,
        );
    }
}
