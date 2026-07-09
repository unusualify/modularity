<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\DefaultRemoteApiConnector;
use Unusualify\Modularous\Tests\TestCase;

class DefaultRemoteApiConnectorTest extends TestCase
{
    public function test_remote_api_configuration_is_disabled_by_default(): void
    {
        $this->assertSame(['enabled' => false], DefaultRemoteApiConnector::remoteApiConfiguration());
    }
}
