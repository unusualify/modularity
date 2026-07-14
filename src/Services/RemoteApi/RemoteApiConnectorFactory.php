<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;

class RemoteApiConnectorFactory
{
    public function __construct(
        private readonly RemoteApiConnectorResolver $resolver,
        private readonly RemoteApiRateLimiter $rateLimiter,
    ) {}

    public function make(string $moduleName, string $routeName): RemoteApiConnectorInterface
    {
        $module = Modularous::findOrFail($moduleName);
        $routeName = snakeCase($routeName);
        $configuration = $this->resolver->configuration($moduleName, $routeName);
        $client = new RemoteApiClient($configuration, $this->rateLimiter);
        $cache = new RemoteApiCache($configuration);
        $fieldMapper = new RemoteApiFieldMapper($configuration);
        $adapter = $this->resolver->resolveAdapter($module, $routeName, $configuration, $fieldMapper);
        $connectorClass = $this->resolver->resolveConnectorClass($module, $routeName);

        return new $connectorClass($configuration, $client, $cache, $adapter);
    }
}
