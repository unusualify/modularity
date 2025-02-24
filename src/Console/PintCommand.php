<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\Modularity;

class PintCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:pint
        {--test : Check if files need fixing}
        {--dirty : Only fix files that have been modified}
        {--repair : Repair the code}
        {--s|self : Lint modularity sources}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format code with Pint for the specified targets.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = config('modules.paths.modules');

        if ($this->option('self')) {
            if (Modularity::isProduction()) {
                throw new \Exception('Pint\'s self argument is not allowed in production mode.');
            }
            $dir = Modularity::getVendorDir();
            $path = Modularity::getVendorPath();
            $dir = "\"{$path}\" --config \"{$dir}/pint.json\"";
        }

        $command = sprintf('./vendor/bin/pint %s', $dir);

        if ($this->option('test')) {
            $command .= ' --test';
        }
        if ($this->option('dirty')) {
            $command .= ' --dirty';
        }
        if ($this->option('verbose')) {
            $command .= ' -v';
        }
        if ($this->option('repair')) {
            $command .= ' --repair';
        }
        // Run the command and log output
        $this->info('Pint ' . $dir . '...');

        // $exitCode = shell_exec($command);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            echo implode("\n", $output);
            $this->info("\n");
            // $this->error('Error formatting');
        } else {
            echo implode("\n", $output);
            $this->info("\n");
            // $this->info('Pint run successfully.');
        }

        return 0;
    }
}
