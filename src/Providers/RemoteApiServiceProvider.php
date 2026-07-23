<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorFactory;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorResolver;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiSynchronizer;

class RemoteApiServiceProvider extends ServiceProvider
{
    /**
     * Register RemoteApi feature bindings.
     */
    public function register(): void
    {
        $this->app->singleton(RemoteApiConnectorResolver::class);
        $this->app->singleton(RemoteApiRateLimiter::class);
        $this->app->singleton(RemoteApiConnectorFactory::class);
        $this->app->singleton(RemoteApiSynchronizer::class);
    }

    /**
     * Bootstrap RemoteApi feature services.
     */
    public function boot(): void
    {
        $this->app['config']->set('logging.channels.modularous-remote-api', [
            'driver' => 'daily',
            'path' => storage_path('logs/modularous-remote-api.log'),
            'level' => env('MODULAROUS_REMOTE_API_LOG_LEVEL', 'info'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ]);
    }

    /**
     * @return list<class-string>
     */
    public function provides(): array
    {
        return [
            RemoteApiConnectorResolver::class,
            RemoteApiRateLimiter::class,
            RemoteApiConnectorFactory::class,
            RemoteApiSynchronizer::class,
        ];
    }
}
