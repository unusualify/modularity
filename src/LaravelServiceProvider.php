<?php


namespace Unusual\CRM\Base;

use Illuminate\Support\ServiceProvider;

final class LaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishMigrations();

    }

    private function publishMigrations(): void
    {
        // $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        if (config('unusual.load_default_migrations', true)) {

        }


        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations/default'),
        ], 'migrations');

    }

    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../vue/dist' => public_path(),
        ], 'assets');
    }
}
