<?php

use Unusualify\Modularous\Support\DatabaseExists;

if (! function_exists('database_exists')) {
    function database_exists(): bool
    {
        return DatabaseExists::check();
    }
}

if (! function_exists('forget_database_exists_cache')) {
    function forget_database_exists_cache(): void
    {
        DatabaseExists::flush();
    }
}
