<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Schema;
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
    protected ?string $tag = 'modularity';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $paymentsTable = config('payable.table');

        if (! Schema::hasTable($paymentsTable)) {

            if(Schema::hasTable('unfy_payments')) {
                Schema::rename('unfy_payments', $paymentsTable);

                $this->output->writeln('');
                $this->output->writeln('');

                $this->info("\tunfy_payments table changed as " . $paymentsTable);

                $this->output->writeln('');
            } else {
                Artisan::call('migrate', [
                    '--path' => Modularity::getVendorPath('database/migrations/default/2024_06_24_125121_create_payments_table.php'),
                    // '--path' => 'vendor/unusualify/modularity/database/migrations/default/2024_06_24_125121_create_payments_table.php'
                ]);

                $this->info("\t" . $paymentsTable . " created");
            }
        }
    }
};
