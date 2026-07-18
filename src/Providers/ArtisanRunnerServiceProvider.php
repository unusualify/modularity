<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Unusualify\Modularous\Services\ArtisanRunner\ArtisanRunner;
use Unusualify\Modularous\Services\ArtisanRunner\CommandCatalog;
use Unusualify\Modularous\Services\ArtisanRunner\CommandDefinitionInspector;
use Unusualify\Modularous\Services\ArtisanRunner\CommandExecutor;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\CommandCatalogInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\InteractiveQuestionBroker;
use Unusualify\Modularous\Services\ArtisanRunner\Support\AllowlistMatcher;

class ArtisanRunnerServiceProvider extends ServiceProvider
{
    /**
     * Register ArtisanRunner feature bindings.
     */
    public function register(): void
    {
        $this->app->singleton(AllowlistMatcher::class);
        $this->app->singleton(CommandCatalogInterface::class, CommandCatalog::class);
        $this->app->singleton(CommandDefinitionInspector::class);
        $this->app->singleton(InteractiveQuestionBroker::class, function () {
            return new InteractiveQuestionBroker(
                (int) modularousConfig('artisan_runner.prompt_timeout', 60)
            );
        });
        $this->app->singleton(CommandExecutor::class);
        $this->app->singleton(ArtisanRunnerInterface::class, ArtisanRunner::class);
    }

    /**
     * @return list<class-string>
     */
    public function provides(): array
    {
        return [
            AllowlistMatcher::class,
            CommandCatalogInterface::class,
            CommandDefinitionInspector::class,
            InteractiveQuestionBroker::class,
            CommandExecutor::class,
            ArtisanRunnerInterface::class,
        ];
    }
}
