<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Exceptions;

use RuntimeException;

class RemoteApiConfigurationException extends RuntimeException
{
    public static function disabled(string $moduleName, string $routeName): self
    {
        return new self("Remote API connector is disabled for {$moduleName}::{$routeName}.");
    }

    public static function missingEndpoint(string $moduleName, string $routeName): self
    {
        return new self("Remote API endpoint is not configured for {$moduleName}::{$routeName}.");
    }

    public static function missingBaseUrl(string $moduleName, string $routeName): self
    {
        return new self("Remote API base URL is not configured for {$moduleName}::{$routeName}.");
    }

    public static function invalidCatalog(string $catalogKey, string $moduleName, string $routeName): self
    {
        return new self("Remote API catalog [{$catalogKey}] is not configured for {$moduleName}::{$routeName}.");
    }

    public static function invalidConnectorClass(string $connectorClass, string $moduleName, string $routeName): self
    {
        return new self("Remote API connector class [{$connectorClass}] is invalid for {$moduleName}::{$routeName}.");
    }
}
