<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;

class ModuleRoutePresentationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRoutePresentationResolver::class);
    }

    /**
     * @return list<class-string>
     */
    public function provides(): array
    {
        return [
            ModuleRoutePresentationResolver::class,
        ];
    }
}
