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
    protected ?string $tag = 'modularity';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $adminUserTable = modularityConfig('tables.users');

        if (! Schema::hasTable($adminUserTable)) {

            Schema::rename('users', $adminUserTable);

            $this->output->writeln('');
            $this->output->writeln('');

            $this->info("\tusers table changed as " . $adminUserTable);

            $this->output->writeln('');
        }
    }
};
