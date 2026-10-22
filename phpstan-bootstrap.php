<?php

use Astrotomic\Translatable\TranslatableServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use JoeDixon\Translation\TranslationServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Oobook\Database\Eloquent\ManageEloquentServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Unusualify\Modularous\LaravelServiceProvider;
use Unusualify\Modularous\Providers\ModularousProvider;

/*
 * Package helpers are required at runtime by BaseServiceProvider::registerHelpers(),
 * which PHPStan never executes. Load them here so global helper calls resolve.
 */
foreach (glob(__DIR__ . '/src/Helpers/*.php') as $helper) {
    require_once $helper;
}

/*
 * Larastan boots a bare Testbench app and only discovers vendor packages, so the
 * root package providers are missing. Register them like tests/TestCase.php does,
 * otherwise booting models (e.g. IsSingular) fails on unresolved bindings.
 */
if (function_exists('app') && app() instanceof Application) {
    foreach (['APP_URL' => 'http://localhost', 'CACHE_STORE' => 'array', 'CACHE_DRIVER' => 'array'] as $key => $value) {
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    app('config')->set([
        'app.url' => getenv('APP_URL'),
        'cache.default' => 'array',
        'database.default' => 'phpstan',
        'database.connections.phpstan' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        'queue.default' => 'sync',
        'broadcasting.default' => 'null',
    ]);

    $providers = [
        LaravelModulesServiceProvider::class,
        LaravelServiceProvider::class,
        ModularousProvider::class,
        PermissionServiceProvider::class,
        ManageEloquentServiceProvider::class,
        Oobook\Priceable\LaravelServiceProvider::class,
        TranslationServiceProvider::class,
        TranslatableServiceProvider::class,
    ];

    foreach ($providers as $provider) {
        if (class_exists($provider)) {
            app()->register($provider);
        }
    }
}
