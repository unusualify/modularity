<?php

namespace Unusualify\Modularity\Exceptions;

use Exception;

class AuthConfigurationException extends Exception
{
    const GUARD_MISSING = 1;

    const PROVIDER_MISSING = 2;

    const PASSWORD_MISSING = 3;

    public static function guardMissing(): self
    {
        return new self(
            "Modularity auth guard configuration is missing. Please run 'php artisan modularity:update:laravel:configs' to update your auth configuration.",
            self::GUARD_MISSING
        );
    }

    public static function providerMissing(): self
    {
        return new self(
            "Modularity auth provider configuration is missing. Please run 'php artisan modularity:update:laravel:configs' to update your auth configuration.",
            self::PROVIDER_MISSING
        );
    }

    public static function passwordMissing(): self
    {
        return new self(
            "Modularity auth password configuration is missing. Please run 'php artisan modularity:update:laravel:configs' to update your auth configuration.",
            self::PASSWORD_MISSING
        );
    }
}
