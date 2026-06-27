<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

class DefaultRemoteApiConnector extends AbstractRemoteApiConnector
{
    /**
     * @return array<string, mixed>
     */
    public static function remoteApiConfiguration(): array
    {
        return [
            'enabled' => false,
        ];
    }
}
