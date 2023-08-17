<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;


class BuildCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Build Unusual assets with custom Vue components";

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        if ($this->option("copyOnly")) {
            return $this->copyCustoms();
        }

        return $this->fullBuild();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [

        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['noInstall', '--noInstall', InputOption::VALUE_NONE, 'No install npm packages'],
            ['hot', '--hot', InputOption::VALUE_NONE, 'Hot Reload'],
            ['watch', '--w', InputOption::VALUE_NONE, 'Watcher for dev'],
            ['copyOnly', '--c', InputOption::VALUE_NONE, 'Only copy assets'],
        ];
    }

        /*
     * @return void
     */
    private function fullBuild()
    {
        $progressBar = $this->output->createProgressBar(4);
        $progressBar->setFormat("%current%/%max% [%bar%] %message%");
        // dd( $this->option('noInstall') );
        $npmInstall = !$this->option('noInstall');

        $progressBar->setMessage(($npmInstall ? "Installing" : "Reusing") . " npm dependencies...\n\n");

        $progressBar->start();

        if ($npmInstall) {
            $this->runUnusualProcess(['npm', 'ci']);
        } else {
            sleep(1);
        }


        $this->info('');
        $progressBar->setMessage("Copying custom components...\n\n");
        $progressBar->advance();

        $this->copyComponents();
        sleep(1);

        $this->info('');
        $progressBar->setMessage("Building assets started...\n\n");
        $progressBar->advance();

        $resource_path = resource_path('js/unusual/*.vue');
        // $resource_path = base_path($this->baseConfig('vendor_path') . '/vue/src/**');

        if ($this->option('hot')) {
            $mode = env('NODE_ENV');
            $this->startWatcher( $resource_path, 'php artisan unusual:build --copyOnly');
            // $this->runUnusualProcess(['npm', 'run', 'serve', '--', "--mode={$mode}", "--port={$this->getDevPort()}"], true);
            $this->runUnusualProcess(['npm', 'run', 'serve', '--', "--mode={$mode}", '--source-map', '--inspect-loader ',"--port={$this->getDevPort()}"], true);
        } elseif ($this->option('watch')) {
            $this->startWatcher( $resource_path, 'php artisan unusual:build --copyOnly');
            $this->runUnusualProcess(['npm', 'run', 'watch'], true);
        } else {
            $this->runUnusualProcess(['npm', 'run', 'build']);

            $this->info('');
            $progressBar->setMessage("Publishing assets...\n\n");
            $progressBar->advance();
            $this->call('unusual:refresh');

            $this->info('');
            $progressBar->setMessage("Done. \n\n");
            $progressBar->finish();
            $progressBar->setMessage("\n\n");
        }

        return 0;
    }

    /**
     * @return string
     */
    private function getDevPort()
    {
        preg_match('/^.*:(\d+)/', $this->baseConfig('development_url'), $matches);

        return $matches[1] ?? '8080';
    }

    /**
     * @return void
     */
    private function startWatcher($pattern, $command)
    {
        if (empty($this->filesystem->glob($pattern))) {
            return;
        }

        $chokidarPath = base_path($this->baseConfig('vendor_path') . '/vue') . '/node_modules/.bin/chokidar';
        $chokidarCommand = [$chokidarPath, $pattern, "-c", $command];


        if ($this->filesystem->exists($chokidarPath)) {
            $process = new Process($chokidarCommand, base_path());
            $process->setTty(Process::isTtySupported());
            $process->setTimeout(null);

            try {
                $process->start();
            } catch(\Exception $e) {
                $this->warn("Could not start the chokidar watcher ({$e->getMessage()})\n");
            }
        } else {
            $this->warn("The `chokidar-cli` package was not found. It is required to watch custom blocks & components in development. You can install it by running:\n");
            $this->warn("    php artisan unusual:dev\n");
            $this->warn("without the `--noInstall` option.\n");
            sleep(2);
        }
    }

    /**
     * @return void
     */
    private function runUnusualProcess(array $command, $disableTimeout = false)
    {
        $process = new Process($command, base_path($this->baseConfig('vendor_path')) . '/vue' );
        $process->setTty(Process::isTtySupported());

        if ($disableTimeout) {
            $process->setTimeout(null);
        } else {
            $process->setTimeout($this->baseConfig('build_timeout', 300));
        }

        $process->mustRun();
    }

    /*
     * @return void
     */
    private function copyCustoms()
    {
        $this->info("Copying custom components...");
        $this->copyComponents();
        $this->info("Done.");

        return 1;
    }

    /**
     * @return void
     */
    private function copyComponents()
    {
        $localCustomComponentsPath = resource_path($this->baseConfig('custom_components_resource_path', 'js/unusual'));
        $unusualCustomComponentsPath = base_path($this->baseConfig('vendor_path')) . '/vue/src/js/components/customs';

        if (!$this->filesystem->exists($unusualCustomComponentsPath)) {
            $this->filesystem->makeDirectory($unusualCustomComponentsPath, 0755, true);
        }

        $this->filesystem->cleanDirectory($unusualCustomComponentsPath);
        $this->filesystem->put($unusualCustomComponentsPath . '/.keep', '');

        if ($this->filesystem->exists($localCustomComponentsPath)) {
            $this->filesystem->copyDirectory($localCustomComponentsPath, $unusualCustomComponentsPath);
        }
    }


}
