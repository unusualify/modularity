<?php

namespace Unusualify\Modularity\Console;

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
                            {--self : modularity package}
    ';

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
        // $targets = $this->argument('targets');

        // if (count($targets) > 0) {
        //     // Build the Pint command with flags
        //     // $command = sprintf('pint --config=%s --stdin-filename=%s --write %s',
        //     //     config('pintCommand.config_file'),
        //     //     $targets[0], // Assuming first target for stdin-filename
        //     //     implode(' ', $targets)
        //     // );
        //     $command = sprintf('./vendor/bin/pint %s',
        //         $targets[0], // Assuming first target for stdin-filename
        //     );
        // } else {
        //     $command = sprintf('./vendor/bin/pint');
        // }

        $path = config('modules.paths.modules');

        if( $this->option('self') ){
            $path = unusualConfig('vendor_path');
        }

        $command = sprintf('./vendor/bin/pint %s', $path);

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
        $this->info('Pint '.$path.'...');

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
