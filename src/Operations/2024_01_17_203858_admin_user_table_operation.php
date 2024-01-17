<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
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
    protected ?string $tag = null;

    /**
     * Process the operation.
     */
    public function process(): void
    {

        if (!Schema::hasTable(unusualConfig('tables.users'))) {

            Schema::rename('users', unusualConfig('tables.users'));

            $this->output->writeln('');
            $this->output->writeln('');

            $this->info("\tusers table changed as " . unusualConfig('tables.users'));

            $this->output->writeln('');
        }
    }
};
