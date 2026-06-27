<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Contracts;

interface DefinesRemoteApiConfiguration
{
    /**
     * Full Remote API connector configuration for this route.
     *
     * @return array<string, mixed>
     */
    public static function remoteApiConfiguration(): array;
}
