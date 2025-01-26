<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Unusualify\Modularity\Facades\Modularity;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput;
    }

    /**
     * Determine if the operation is being processed asynchronously.
     */
    protected bool $async = true;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = 'modularity:local';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $this->output->writeln('');
        if (app()->environment('local')) {
            foreach (Modularity::getModules() as $key => $module) {
                Artisan::call('modularity:make:module', [
                    'module' => $module->getName(),
                    '--just-stubs' => true,
                    '--stubs-only' => 'views/index',
                ]);
            }
            $this->info("\tIndex.blade's store fields updated.");
        } else {
            $this->info("\tSkipping index.blade's store fields update in non-local environment.");
        }

        $this->output->writeln('');
    }
};
