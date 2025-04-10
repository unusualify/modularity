<?php

namespace Unusualify\Modularity\Exceptions;

use Exception;

class ModuleNotFoundException extends Exception
{
    const MODULE_MISSING = 1;

    const ROUTE_MISSING = 2;

    const MODULE_NOT_FOUND = 3;

    const ROUTE_NOT_FOUND = 4;

    public static function moduleMissing($message = null): self
    {
        return new self(
            $message ?? "Missing module name",
            self::MODULE_MISSING
        );
    }

    public static function routeMissing($message = null): self
    {
        return new self(
            $message ?? "Missing route name",
            self::ROUTE_MISSING
        );
    }

    public static function moduleNotFound($message = null): self
    {
        return new self(
            $message ?? "Module not found",
            self::MODULE_NOT_FOUND
        );
    }

    public static function routeNotFound($message = null): self
    {
        return new self(
            $message ?? "Route not found",
            self::ROUTE_NOT_FOUND
        );
    }
}
