<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiSyncExceptionTest extends TestCase
{
    public function test_missing_remote_id_factory_sets_message(): void
    {
        $exception = RemoteApiSyncException::missingRemoteId();

        $this->assertSame(
            'Remote API sync requires a remote id or a local record with remote_id.',
            $exception->getMessage()
        );
        $this->assertFalse($exception->isRateLimit);
        $this->assertNull($exception->retryAfterSeconds);
    }
}
